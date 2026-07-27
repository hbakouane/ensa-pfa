<script setup>
defineProps({
    content: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <div
            class="rounded-lg border border-slate-200 bg-white p-8 shadow-sm"
            style="min-height: 400px;"
        >
            <!-- Print-like header decoration -->
            <div class="mb-6 border-b-2 border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-400">
                        Aperçu de la lettre d'offre
                    </span>
                </div>
            </div>

            <!-- Rendered content -->
            <div
                v-if="content"
                class="prose prose-slate prose-sm max-w-none leading-relaxed"
                v-html="content"
            />

            <!-- Empty state -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <svg class="h-10 w-10 text-slate-200" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-3 text-sm text-slate-400">
                    Aucun contenu à prévisualiser. Sélectionnez un modèle ou rédigez le contenu de l'offre.
                </p>
            </div>
        </div>
    </div>
</template>
