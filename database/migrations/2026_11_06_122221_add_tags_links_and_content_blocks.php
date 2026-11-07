<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 40);
            $table->string('key', 40)->unique();
            $table->timestamps();
        });
        Schema::create('problem_tag', function (Blueprint $table): void {
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['problem_id', 'tag_id']);
            $table->index(['tag_id', 'problem_id']);
        });
        Schema::create('problem_links', function (Blueprint $table): void {
            $table->foreignId('problem_id')->constrained('problems')->cascadeOnDelete();
            $table->foreignId('related_problem_id')->constrained('problems')->cascadeOnDelete();
            $table->primary(['problem_id', 'related_problem_id']);
            $table->index(['related_problem_id', 'problem_id']);
        });
        Schema::create('content_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10);
            $table->text('content');
            $table->string('language', 40)->nullable();
            $table->unsignedInteger('position');
            $table->string('source', 30)->nullable();
            $table->timestamps();
            $table->unique(['problem_id', 'position']);
            $table->unique(['problem_id', 'source']);
        });
        // Preserve the original JSON tags; add catalog and pivot data alongside them.
        DB::table('problems')->orderBy('id')->chunkById(100, function ($problems): void {
            foreach ($problems as $problem) {
                foreach (json_decode($problem->tags ?? '[]', true, 512, JSON_THROW_ON_ERROR) ?? [] as $name) {
                    $name = trim($name);
                    $key = mb_strtolower(trim(preg_replace('/\\s+/u', ' ', $name) ?? $name));
                    if ($key === '') {
                        continue;
                    }
                    DB::table('tags')->insertOrIgnore(['name' => $name, 'key' => $key, 'created_at' => now(), 'updated_at' => now()]);
                    $tag = DB::table('tags')->where('key', $key)->value('id');
                    DB::table('problem_tag')->insertOrIgnore(['problem_id' => $problem->id, 'tag_id' => $tag]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('problem_links');
        Schema::dropIfExists('problem_tag');
        Schema::dropIfExists('tags');
    }
};
