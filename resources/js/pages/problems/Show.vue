<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ContentBlockDialog from '@/components/ContentBlockDialog.vue';
import RelatedProblems from '@/components/RelatedProblems.vue';
import { Button } from '@/components/ui/button';
import {
    convert,
    destroy as blockDestroy,
} from '@/routes/vaults/sections/problems/blocks';
import { compare } from '@/routes/vaults/sections/problems';
import AttemptDialog from '@/components/AttemptDialog.vue';
import CodeBlock from '@/components/CodeBlock.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeading from '@/components/PageHeading.vue';
import ProblemDialog from '@/components/ProblemDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show as vaultShow } from '@/routes/vaults';
import { show as sectionShow } from '@/routes/vaults/sections';
import { destroy } from '@/routes/vaults/sections/problems';
import { destroy as attemptDestroy } from '@/routes/vaults/sections/problems/attempts';
import type {
    Problem,
    Vault,
    Section,
    RelatedProblem,
} from '@/types/knowledge';
defineProps<{
    vault: Vault;
    section: Section;
    problem: Problem;
    related: RelatedProblem[];
    linkCandidates: { id: number; title: string }[];
}>();
const conversion = useForm({});
</script>
<template>
    <Head :title="problem.title" />
    <AppLayout
        :title="problem.title"
        :breadcrumbs="[
            { label: 'Vaults', href: index.url() },
            { label: vault.name, href: vaultShow.url(vault.id) },
            {
                label: section.name,
                href: sectionShow.url({ vault: vault.id, section: section.id }),
            },
        ]"
    >
        <PageHeading :title="problem.title" :description="problem.description">
            <Button as-child variant="outline"
                ><Link
                    :href="
                        compare({
                            vault: vault.id,
                            section: section.id,
                            problem: problem.id,
                        })
                    "
                    >Compare code</Link
                ></Button
            >
            <ProblemDialog
                :vault="vault"
                :section="section"
                :problem="problem"
            />
            <DeleteDialog
                :action="
                    destroy({
                        vault: vault.id,
                        section: section.id,
                        problem: problem.id,
                    })
                "
                title="Delete problem?"
                description="All attempts for this problem will also be deleted."
            />
        </PageHeading>
        <div class="flex flex-wrap gap-2">
            <Badge>{{ problem.programming_language }}</Badge
            ><Badge
                v-for="tag in problem.tags"
                :key="tag"
                variant="secondary"
                >{{ tag }}</Badge
            >
        </div>

        <section
            aria-labelledby="solution-heading"
            class="flex min-w-0 flex-col gap-4 rounded-xl border border-primary/30 bg-primary/5 p-5"
        >
            <div class="flex items-center gap-3">
                <h2 id="solution-heading" class="text-xl font-semibold">
                    Corrected solution
                </h2>
                <Badge variant="outline">Reference</Badge>
            </div>
            <CodeBlock
                v-if="problem.corrected_solution"
                :code="problem.corrected_solution"
                :language="problem.programming_language"
            />
            <p v-else class="text-sm text-muted-foreground">
                No corrected solution yet. Edit this problem when you find the
                fix.
            </p>
            <h3 class="font-semibold">Why it works</h3>
            <p class="wrap-anywhere whitespace-pre-wrap">
                {{
                    problem.explanation ||
                    'Add a detailed explanation when you understand the solution.'
                }}
            </p>
        </section>

        <section
            class="flex min-w-0 flex-col gap-4"
            aria-labelledby="blocks-heading"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="blocks-heading" class="text-xl font-semibold">
                    Text and code blocks
                </h2>
                <ContentBlockDialog
                    :vault="vault"
                    :section="section"
                    :problem="problem"
                />
            </div>
            <p class="text-sm text-muted-foreground">
                Blocks supplement the original fields. Copying legacy content
                keeps the original description, solution, and explanation
                intact.
            </p>
            <Button
                variant="outline"
                class="self-start"
                :disabled="conversion.processing"
                @click="
                    conversion.post(
                        convert.url({
                            vault: vault.id,
                            section: section.id,
                            problem: problem.id,
                        }),
                        { preserveScroll: true },
                    )
                "
                >{{
                    conversion.processing
                        ? 'Copying…'
                        : 'Copy legacy fields into blocks'
                }}</Button
            >
            <p
                v-for="(message, key) in conversion.errors"
                :key="key"
                role="alert"
                class="text-sm text-destructive"
            >
                {{ message }}
            </p>
            <Card v-for="block in problem.blocks" :key="block.id"
                ><CardHeader
                    class="flex flex-wrap items-center justify-between gap-3"
                    ><CardTitle class="text-base"
                        >Block {{ block.position }} ·
                        {{ block.type }}</CardTitle
                    >
                    <div class="flex gap-2">
                        <ContentBlockDialog
                            :vault="vault"
                            :section="section"
                            :problem="problem"
                            :block="block"
                        /><DeleteDialog
                            :action="
                                blockDestroy({
                                    vault: vault.id,
                                    section: section.id,
                                    problem: problem.id,
                                    block: block.id,
                                })
                            "
                            title="Delete block?"
                            description="Only this block will be removed. Original legacy fields remain unchanged."
                        /></div></CardHeader
                ><CardContent
                    ><CodeBlock
                        v-if="block.type === 'code'"
                        :code="block.content"
                        :language="
                            block.language || problem.programming_language
                        "
                    />
                    <p v-else class="wrap-anywhere whitespace-pre-wrap">
                        {{ block.content }}
                    </p></CardContent
                ></Card
            >
            <p
                v-if="!problem.blocks?.length"
                class="text-sm text-muted-foreground"
            >
                No blocks yet.
            </p>
        </section>
        <RelatedProblems
            :vault="vault"
            :section="section"
            :problem="problem"
            :related="related"
            :candidates="linkCandidates"
        />
        <section
            aria-labelledby="attempts-heading"
            class="flex min-w-0 flex-col gap-5"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="attempts-heading" class="text-xl font-semibold">
                    Code attempts ({{ problem.attempts?.length ?? 0 }})
                </h2>
                <AttemptDialog
                    :vault="vault"
                    :section="section"
                    :problem="problem"
                />
            </div>
            <Card
                v-for="attempt in problem.attempts"
                :key="attempt.id"
                class="min-w-0"
            >
                <CardHeader
                    class="flex flex-wrap items-center justify-between gap-3"
                >
                    <CardTitle>Attempt {{ attempt.position }}</CardTitle>
                    <div class="flex gap-2">
                        <AttemptDialog
                            :vault="vault"
                            :section="section"
                            :problem="problem"
                            :attempt="attempt"
                        /><DeleteDialog
                            :action="
                                attemptDestroy({
                                    vault: vault.id,
                                    section: section.id,
                                    problem: problem.id,
                                    attempt: attempt.id,
                                })
                            "
                            title="Delete attempt?"
                            :description="
                                'Delete attempt ' +
                                attempt.position +
                                ' and its notes.'
                            "
                        />
                    </div>
                </CardHeader>
                <CardContent class="flex min-w-0 flex-col gap-4"
                    ><CodeBlock
                        :code="attempt.code"
                        :language="problem.programming_language"
                    />
                    <p
                        v-if="attempt.notes"
                        class="text-sm wrap-anywhere whitespace-pre-wrap text-muted-foreground"
                    >
                        {{ attempt.notes }}
                    </p></CardContent
                >
            </Card>
            <EmptyState
                v-if="!problem.attempts?.length"
                title="Every attempt has something to teach"
                description="Save your first approach, even if it does not work yet."
            />
        </section>
    </AppLayout>
</template>
