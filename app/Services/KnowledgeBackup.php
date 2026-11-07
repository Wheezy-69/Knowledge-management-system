<?php

namespace App\Services;

use App\Models\Problem;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class KnowledgeBackup
{
    public const MAX_BYTES = 20971520;

    /** @var array<string, list<string>> */
    private const FIELDS = [
        'vaults' => ['id', 'name', 'description', 'cover_path', 'created_at', 'updated_at'],
        'sections' => ['id', 'vault_id', 'name', 'description', 'created_at', 'updated_at'],
        'problems' => ['id', 'section_id', 'title', 'description', 'programming_language', 'tags', 'corrected_solution', 'explanation', 'created_at', 'updated_at'],
        'attempts' => ['id', 'problem_id', 'code', 'notes', 'position', 'created_at', 'updated_at'],
        'tags' => ['id', 'name', 'key', 'created_at', 'updated_at'],
        'problem_tag' => ['problem_id', 'tag_id'],
        'problem_links' => ['problem_id', 'related_problem_id'],
        'content_blocks' => ['id', 'problem_id', 'type', 'content', 'language', 'position', 'source', 'created_at', 'updated_at'],
    ];

    public function export(): string
    {
        $data = DB::transaction(function (): array {
            $data = [];
            foreach (self::FIELDS as $table => $fields) {
                $data[$table] = Schema::hasTable($table)
                    ? DB::table($table)->get($fields)->map(fn ($row): array => (array) $row)->all()
                    : [];
            }
            foreach ($data['problems'] as &$problem) {
                $problem['tags'] = $problem['tags'] === null ? null : json_decode($problem['tags'], true, 512, JSON_THROW_ON_ERROR);
            }
            unset($problem);
            foreach ($data['vaults'] as &$vault) {
                $vault['cover'] = null;
                if ($vault['cover_path']) {
                    if (! Storage::disk('local')->exists($vault['cover_path'])) {
                        throw ValidationException::withMessages(['backup' => 'A cover file is missing. Repair it before exporting a complete backup.']);
                    }
                    $bytes = Storage::disk('local')->get($vault['cover_path']);
                    $vault['cover'] = ['sha256' => hash('sha256', $bytes), 'data' => base64_encode($bytes)];
                }
                unset($vault['cover_path']);
            }
            unset($vault);

            return $data;
        });
        $json = json_encode([
            'format' => 'knowledge-vault', 'version' => 1, 'created_at' => now()->toIso8601String(),
            'counts' => array_map(count(...), $data),
            'sha256' => hash('sha256', $this->encode($data)),
            'data' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (strlen($json) > self::MAX_BYTES) {
            $this->invalid('This workspace exceeds the 20 MB portable-backup limit. Use a database and cover-directory backup instead.');
        }

        return $json;
    }

    /** @return array<string, int> */
    public function inspect(string $json): array
    {
        return array_map(count(...), $this->decode($json));
    }

    /** @return array<string, int> */
    public function restore(string $json): array
    {
        $data = $this->decode($json);
        $written = [];
        try {
            DB::transaction(function () use ($data, &$written): void {
                $maps = ['vaults' => [], 'sections' => [], 'problems' => [], 'attempts' => [], 'tags' => [], 'content_blocks' => []];
                foreach (['vaults', 'sections', 'problems', 'attempts', 'tags', 'content_blocks'] as $table) {
                    $maps[$table] = [];
                    foreach ($data[$table] as $row) {
                        $oldId = $row['id'];
                        $attributes = array_intersect_key($row, array_flip(self::FIELDS[$table]));
                        unset($attributes['id']);
                        foreach (['vault_id' => 'vaults', 'section_id' => 'sections', 'problem_id' => 'problems'] as $key => $parent) {
                            if (isset($attributes[$key])) {
                                $attributes[$key] = $maps[$parent][$attributes[$key]];
                            }
                        }
                        if ($table === 'vaults') {
                            $attributes['cover_path'] = null;
                            if ($row['cover']) {
                                $bytes = base64_decode($row['cover']['data'], true);
                                if ($bytes === false) {
                                    $this->invalid('Invalid attachment encoding.');
                                }
                                $info = getimagesizefromstring($bytes);
                                if (! is_array($info)) {
                                    $this->invalid('Invalid cover image.');
                                }
                                $extension = match ($info[2]) {
                                    IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png',
                                    IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp',
                                    default => $this->invalid('Unsupported cover image.'),
                                };
                                $path = 'vault-covers/'.Str::uuid().'.'.$extension;
                                $written[] = $path;
                                if (! Storage::disk('local')->put($path, $bytes)) {
                                    $this->invalid('Unable to save restored attachments.');
                                }
                                $attributes['cover_path'] = $path;
                            }
                        }
                        if ($table === 'problems') {
                            $attributes['tags'] = $row['tags'] === null ? null : $this->encode($row['tags']);
                        }
                        if ($table === 'tags') {
                            $existing = DB::table('tags')->where('key', $attributes['key'])->value('id');
                            if ($existing !== null) {
                                $maps[$table][$oldId] = $existing;

                                continue;
                            }
                        }
                        $maps[$table][$oldId] = DB::table($table)->insertGetId($attributes);
                    }
                }
                foreach ($data['problem_tag'] as $row) {
                    DB::table('problem_tag')->insertOrIgnore([
                        'problem_id' => $maps['problems'][$row['problem_id']], 'tag_id' => $maps['tags'][$row['tag_id']],
                    ]);
                }
                foreach ($data['problem_links'] as $row) {
                    $a = $maps['problems'][$row['problem_id']];
                    $b = $maps['problems'][$row['related_problem_id']];
                    DB::table('problem_links')->insert(['problem_id' => min($a, $b), 'related_problem_id' => max($a, $b)]);
                }
                // Archives made before tag normalization still carry the original JSON tags.
                foreach ($maps['problems'] as $id) {
                    $problem = Problem::query()->findOrFail($id);
                    app(ProblemTags::class)->sync($problem, $problem->tags ?? [], true);
                }
            });
        } catch (Throwable $exception) {
            foreach ($written as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        return array_map(count(...), $data);
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function decode(string $json): array
    {
        if (strlen($json) > self::MAX_BYTES) {
            $this->invalid('Backup exceeds the 20 MB limit.');
        }
        try {
            $archive = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->invalid('Backup is not valid JSON.');
        }
        if (! is_array($archive) || ($archive['format'] ?? null) !== 'knowledge-vault' || ($archive['version'] ?? null) !== 1) {
            $this->invalid('Unsupported backup format or version.');
        }
        $data = $archive['data'] ?? null;
        if (! is_array($archive['counts'] ?? null)) {
            $this->invalid('Backup record counts are missing.');
        }
        if (! is_array($data) || ! is_string($archive['sha256'] ?? null)
            || ! hash_equals($archive['sha256'], hash('sha256', $this->encode($data)))) {
            $this->invalid('Backup checksum failed. The file may be incomplete or modified.');
        }
        $rules = [];
        foreach (self::FIELDS as $table => $fields) {
            $rules[$table] = ['present', 'array', 'list', 'max:10000'];
            if (in_array('id', $fields)) {
                $rules[$table.'.*.id'] = ['required', 'integer', 'min:1', 'distinct'];
            }
            foreach (array_intersect($fields, ['vault_id', 'section_id', 'problem_id', 'tag_id', 'related_problem_id']) as $field) {
                $rules[$table.'.*.'.$field] = ['required', 'integer', 'min:1'];
            }
            if (in_array('created_at', $fields)) {
                $rules[$table.'.*.created_at'] = ['present', 'nullable', 'date'];
                $rules[$table.'.*.updated_at'] = ['present', 'nullable', 'date'];
            }
        }
        $rules += [
            'vaults.*.name' => ['required', 'string', 'max:120'],
            'vaults.*.description' => ['present', 'nullable', 'string', 'max:10000'],
            'vaults.*.cover' => ['present', 'nullable', 'array'],
            'vaults.*.cover.sha256' => ['required_with:vaults.*.cover.data', 'string', 'size:64'],
            'vaults.*.cover.data' => ['required_with:vaults.*.cover.sha256', 'string', 'max:2800000'],
            'sections.*.name' => ['required', 'string', 'max:120'],
            'sections.*.description' => ['present', 'nullable', 'string', 'max:10000'],
            'problems.*.title' => ['required', 'string', 'max:200'],
            'problems.*.description' => ['present', 'nullable', 'string', 'max:50000'],
            'problems.*.programming_language' => ['required', 'string', 'max:40'],
            'problems.*.tags' => ['present', 'nullable', 'array', 'max:12'],
            'problems.*.tags.*' => ['required', 'string', 'max:40'],
            'problems.*.corrected_solution' => ['present', 'nullable', 'string', 'max:100000'],
            'problems.*.explanation' => ['present', 'nullable', 'string', 'max:50000'],
            'attempts.*.code' => ['required', 'string', 'max:100000'],
            'attempts.*.notes' => ['present', 'nullable', 'string', 'max:10000'],
            'attempts.*.position' => ['required', 'integer', 'between:1,1000000'],
            'tags.*.name' => ['required', 'string', 'max:40'],
            'tags.*.key' => ['required', 'string', 'max:40', 'distinct'],
            'content_blocks.*.type' => ['required', 'in:text,code'],
            'content_blocks.*.content' => ['required', 'string', 'max:100000'],
            'content_blocks.*.language' => ['present', 'nullable', 'string', 'max:40'],
            'content_blocks.*.position' => ['required', 'integer', 'between:1,1000000'],
            'content_blocks.*.source' => ['present', 'nullable', 'in:description,corrected_solution,explanation'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $this->invalid('Backup data is incomplete or invalid: '.$validator->errors()->first());
        }
        foreach (self::FIELDS as $table => $fields) {
            if (($archive['counts'][$table] ?? null) !== count($data[$table])) {
                $this->invalid('Backup record counts do not match.');
            }
        }
        $ids = [];
        foreach (['vaults', 'sections', 'problems', 'tags'] as $table) {
            $ids[$table] = array_fill_keys(array_column($data[$table], 'id'), true);
        }
        foreach (self::FIELDS as $table => $fields) {
            $seen = [];
            $sources = [];
            foreach ($data[$table] as $row) {
                if ($table === 'tags' && $row['key'] !== Tag::keyFor($row['name'])) {
                    $this->invalid('Backup contains an invalid normalized tag name.');
                }
                if ($table === 'content_blocks' && $row['source'] !== null) {
                    $sourceKey = $row['problem_id'].':'.$row['source'];
                    if (isset($sources[$sourceKey])) {
                        $this->invalid('Backup contains duplicate legacy block sources.');
                    }
                    $sources[$sourceKey] = true;
                }
                foreach (['vault_id' => 'vaults', 'section_id' => 'sections', 'problem_id' => 'problems', 'tag_id' => 'tags', 'related_problem_id' => 'problems'] as $key => $parent) {
                    if (in_array($key, $fields) && ! isset($ids[$parent][$row[$key]])) {
                        $this->invalid('Backup contains a missing parent relationship.');
                    }
                }
                $unique = match ($table) {
                    'attempts', 'content_blocks' => $row['problem_id'].':'.$row['position'],
                    'problem_tag' => $row['problem_id'].':'.$row['tag_id'],
                    'problem_links' => min($row['problem_id'], $row['related_problem_id']).':'.max($row['problem_id'], $row['related_problem_id']),
                    default => null,
                };
                if ($unique !== null && isset($seen[$unique])) {
                    $this->invalid('Backup contains duplicate relationships or order numbers.');
                }
                if ($unique !== null) {
                    $seen[$unique] = true;
                }
                if ($table === 'problem_links' && $row['problem_id'] === $row['related_problem_id']) {
                    $this->invalid('A problem cannot link to itself.');
                }
            }
        }
        if ($data['tags'] !== [] || $data['problem_tag'] !== []) {
            $tagKeys = array_column($data['tags'], 'key', 'id');
            $assigned = [];
            foreach ($data['problem_tag'] as $row) {
                $assigned[$row['problem_id']][] = $tagKeys[$row['tag_id']];
            }
            foreach ($data['problems'] as $problem) {
                $labels = array_values(array_unique(array_map(Tag::keyFor(...), $problem['tags'] ?? [])));
                $keys = array_values(array_unique($assigned[$problem['id']] ?? []));
                sort($labels);
                sort($keys);
                if ($labels !== $keys) {
                    $this->invalid('Backup tag labels and assignments do not agree.');
                }
            }
        }
        foreach ($data['vaults'] as $vault) {
            if ($vault['cover'] === null) {
                continue;
            }
            $cover = $vault['cover'];
            $bytes = base64_decode($cover['data'] ?? '', true);
            $info = $bytes === false ? false : @getimagesizefromstring($bytes);
            if ($bytes === false || strlen($bytes) > 2097152 || ! is_array($info)
                || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)
                || ! hash_equals($cover['sha256'] ?? '', hash('sha256', $bytes))) {
                $this->invalid('Backup contains a corrupt or unsupported cover image.');
            }
        }

        return $data;
    }

    /** @param array<mixed> $data */
    private function encode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['backup' => $message]);
    }
}
