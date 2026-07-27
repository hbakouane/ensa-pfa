<script setup>
import Modal from '@/Components/UI/Modal.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    application: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const teamMembers = computed(() => page.props.teamMembers ?? []);

const form = useForm({
    application_id: null,
    title: '',
    type: 'video',
    scheduled_at: '',
    duration_minutes: 60,
    location: '',
    meeting_url: '',
    interviewer_ids: [],
    notes: '',
});

const typeOptions = [
    { value: 'phone', label: 'Entretien téléphonique' },
    { value: 'video', label: 'Appel vidéo' },
    { value: 'onsite', label: 'En personne' },
    { value: 'technical', label: 'Technique' },
    { value: 'panel', label: 'Panel' },
];

const durationOptions = [
    { value: 15, label: '15 minutes' },
    { value: 30, label: '30 minutes' },
    { value: 45, label: '45 minutes' },
    { value: 60, label: '1 heure' },
    { value: 90, label: '1h30' },
    { value: 120, label: '2 heures' },
];

function toggleInterviewer(memberId) {
    const idx = form.interviewer_ids.indexOf(memberId);
    if (idx > -1) {
        form.interviewer_ids.splice(idx, 1);
    } else {
        form.interviewer_ids.push(memberId);
    }
}

function isSelected(memberId) {
    return form.interviewer_ids.includes(memberId);
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

function submit() {
    form.application_id = props.application?.id ?? null;
    form.post(route('interviews.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
}

function close() {
    form.reset();
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="close">
        <template #title>
            <h2 class="text-lg font-semibold text-slate-900">Planifier un entretien</h2>
        </template>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Title -->
            <Input
                v-model="form.title"
                label="Titre de l'entretien"
                placeholder="ex. Premier entretien technique"
                :error="form.errors.title"
                required
            />

            <!-- Type and Duration -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Select
                    v-model="form.type"
                    label="Type d'entretien"
                    :options="typeOptions"
                    :error="form.errors.type"
                    required
                />
                <Select
                    v-model="form.duration_minutes"
                    label="Durée"
                    :options="durationOptions"
                    :error="form.errors.duration_minutes"
                    required
                />
            </div>

            <!-- Date/Time -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    Date et heure
                    <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.scheduled_at"
                    type="datetime-local"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': form.errors.scheduled_at }"
                    required
                />
                <p v-if="form.errors.scheduled_at" class="mt-1.5 text-xs text-red-600">
                    {{ form.errors.scheduled_at }}
                </p>
            </div>

            <!-- Location -->
            <Input
                v-model="form.location"
                label="Lieu"
                placeholder="ex. Salle de réunion A, 3e étage"
                :error="form.errors.location"
            />

            <!-- Meeting URL -->
            <Input
                v-model="form.meeting_url"
                label="URL de la réunion"
                type="url"
                placeholder="e.g. https://meet.google.com/abc-defg-hij"
                :error="form.errors.meeting_url"
            />

            <!-- Interviewers -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                    Intervieweurs
                </label>
                <div v-if="teamMembers.length > 0" class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3">
                    <label
                        v-for="member in teamMembers"
                        :key="member.id"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 transition-colors hover:bg-slate-50"
                        :class="{ 'bg-indigo-50': isSelected(member.id) }"
                    >
                        <input
                            type="checkbox"
                            :checked="isSelected(member.id)"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            @change="toggleInterviewer(member.id)"
                        />
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[10px] font-semibold text-slate-600">
                            {{ getInitials(member.name) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ member.name }}</p>
                            <p class="text-xs text-slate-500">{{ member.email }}</p>
                        </div>
                    </label>
                </div>
                <p v-else class="text-sm text-slate-500">
                    Aucun membre d'équipe disponible.
                </p>
                <p v-if="form.errors.interviewer_ids" class="mt-1.5 text-xs text-red-600">
                    {{ form.errors.interviewer_ids }}
                </p>
            </div>

            <!-- Notes -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Notes</label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Notes ou instructions supplémentaires pour l'entretien..."
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                />
                <p v-if="form.errors.notes" class="mt-1.5 text-xs text-red-600">
                    {{ form.errors.notes }}
                </p>
            </div>
        </form>

        <template #footer>
            <div class="flex items-center justify-end gap-3">
                <Button variant="secondary" @click="close">
                    Annuler
                </Button>
                <Button
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="submit"
                >
                    Planifier l'entretien
                </Button>
            </div>
        </template>
    </Modal>
</template>
