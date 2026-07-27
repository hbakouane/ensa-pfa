<script setup>
import CommentInput from '@/Components/Comments/CommentInput.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    comments: {
        type: Array,
        default: () => [],
    },
    commentableType: {
        type: String,
        required: true,
    },
    commentableId: {
        type: [String, Number],
        required: true,
    },
});

const replyingTo = ref(null);

function formatTimeAgo(dateString) {
    if (!dateString) return '';
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now - date;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSecs < 60) return 'à l\'instant';
    if (diffMins < 60) return `il y a ${diffMins}m`;
    if (diffHours < 24) return `il y a ${diffHours}h`;
    if (diffDays < 7) return `il y a ${diffDays}j`;

    return date.toLocaleDateString('fr-FR', {
        month: 'short',
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
    });
}

function getInitials(name) {
    if (!name) return '?';
    return name
        .split(' ')
        .map((n) => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function toggleReply(commentId) {
    replyingTo.value = replyingTo.value === commentId ? null : commentId;
}

function deleteComment(comment) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) return;
    router.delete(route('comments.destroy', comment.id), {
        preserveScroll: true,
    });
}

function onCommentAdded() {
    replyingTo.value = null;
}
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
            Commentaires
            <span v-if="comments.length > 0" class="ml-1 text-slate-400">({{ comments.length }})</span>
        </h3>

        <!-- Comments list -->
        <div v-if="comments.length > 0" class="space-y-4">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="group"
            >
                <!-- Parent comment -->
                <div class="flex gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                        {{ getInitials(comment.user?.name) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-900">
                                {{ comment.user?.name ?? 'Inconnu' }}
                            </span>
                            <span class="text-xs text-slate-400">
                                {{ formatTimeAgo(comment.created_at) }}
                            </span>
                        </div>
                        <div class="mt-1 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">{{ comment.body }}</div>
                        <div class="mt-1.5 flex items-center gap-3 opacity-0 transition-opacity group-hover:opacity-100">
                            <button
                                class="text-xs font-medium text-slate-500 transition-colors hover:text-indigo-600"
                                @click="toggleReply(comment.id)"
                            >
                                Répondre
                            </button>
                            <button
                                v-if="comment.can_delete"
                                class="text-xs font-medium text-slate-500 transition-colors hover:text-red-600"
                                @click="deleteComment(comment)"
                            >
                                Supprimer
                            </button>
                        </div>

                        <!-- Reply input -->
                        <div v-if="replyingTo === comment.id" class="mt-3">
                            <CommentInput
                                :commentable-type="commentableType"
                                :commentable-id="commentableId"
                                :parent-id="comment.id"
                                @submitted="onCommentAdded"
                            />
                        </div>

                        <!-- Nested replies -->
                        <div
                            v-if="comment.replies && comment.replies.length > 0"
                            class="mt-3 space-y-3 border-l-2 border-slate-100 pl-4"
                        >
                            <div
                                v-for="reply in comment.replies"
                                :key="reply.id"
                                class="group/reply flex gap-3"
                            >
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-200 text-[10px] font-semibold text-slate-600">
                                    {{ getInitials(reply.user?.name) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-slate-900">
                                            {{ reply.user?.name ?? 'Inconnu' }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            {{ formatTimeAgo(reply.created_at) }}
                                        </span>
                                    </div>
                                    <div class="mt-0.5 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">{{ reply.body }}</div>
                                    <div class="mt-1 opacity-0 transition-opacity group-hover/reply:opacity-100">
                                        <button
                                            v-if="reply.can_delete"
                                            class="text-xs font-medium text-slate-500 transition-colors hover:text-red-600"
                                            @click="deleteComment(reply)"
                                        >
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center py-6 text-center">
            <svg class="h-8 w-8 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            <p class="mt-2 text-sm text-slate-400">Aucun commentaire pour le moment. Lancez la conversation.</p>
        </div>

        <!-- New comment input -->
        <div class="border-t border-slate-100 pt-4">
            <CommentInput
                :commentable-type="commentableType"
                :commentable-id="commentableId"
            />
        </div>
    </div>
</template>
