<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const notifications = ref([]);
const isOpen = ref(false);
const loading = ref(false);

const unreadCount = computed(() => {
    return notifications.value.filter((n) => !n.read_at).length;
});

const dropdownRef = ref(null);

function toggle() {
    isOpen.value = !isOpen.value;
    if (isOpen.value && notifications.value.length === 0) {
        fetchNotifications();
    }
}

function close() {
    isOpen.value = false;
}

function onClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        close();
    }
}

async function fetchNotifications() {
    loading.value = true;
    try {
        const response = await axios.get(route('notifications.index'));
        notifications.value = response.data.data ?? response.data ?? [];
    } catch (err) {
        console.error('NotificationDropdown: Error fetching notifications', err);
    } finally {
        loading.value = false;
    }
}

async function markAsRead(notification) {
    if (notification.read_at) return;
    try {
        await axios.patch(route('notifications.read', notification.id));
        notification.read_at = new Date().toISOString();
    } catch (err) {
        console.error('NotificationDropdown: Error marking as read', err);
    }
}

async function markAllAsRead() {
    try {
        await axios.post(route('notifications.read-all'));
        notifications.value.forEach((n) => {
            if (!n.read_at) {
                n.read_at = new Date().toISOString();
            }
        });
    } catch (err) {
        console.error('NotificationDropdown: Error marking all as read', err);
    }
}

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
    });
}

function getNotificationIcon(type) {
    const icons = {
        'application.stage.changed': 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'interview.scheduled': 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'offer.sent': 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'comment.added': 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z',
    };
    return icons[type] ?? 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
}

onMounted(() => {
    document.addEventListener('click', onClickOutside);
    fetchNotifications();
});

onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Bell icon trigger -->
        <button
            class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
            @click="toggle"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>

            <!-- Unread badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown panel -->
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 top-full z-50 mt-2 w-96 origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        class="text-xs font-medium text-indigo-600 transition-colors hover:text-indigo-800"
                        @click="markAllAsRead"
                    >
                        Tout marquer comme lu
                    </button>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <svg class="h-5 w-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                </div>

                <!-- Notification list -->
                <div v-else-if="notifications.length > 0" class="max-h-96 divide-y divide-slate-50 overflow-y-auto">
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="flex w-full items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50"
                        :class="{ 'bg-indigo-50/50': !notification.read_at }"
                        @click="markAsRead(notification)"
                    >
                        <!-- Unread dot -->
                        <div class="mt-2 flex-shrink-0">
                            <div
                                class="h-2 w-2 rounded-full"
                                :class="notification.read_at ? 'bg-transparent' : 'bg-indigo-500'"
                            />
                        </div>

                        <!-- Icon -->
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-slate-100">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="getNotificationIcon(notification.type)" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-900" :class="{ 'font-medium': !notification.read_at }">
                                {{ notification.data?.message ?? notification.message ?? 'Notification' }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ formatTimeAgo(notification.created_at) }}
                            </p>
                        </div>
                    </button>
                </div>

                <!-- Empty state -->
                <div v-else class="flex flex-col items-center py-8 text-center">
                    <svg class="h-8 w-8 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="mt-2 text-sm text-slate-400">Aucune notification</p>
                </div>
            </div>
        </Transition>
    </div>
</template>
