<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import TopNav from '@/Components/TopNav.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import { ref } from 'vue';

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}

function toggleMobileSidebar() {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-slate-50">
        <!-- Mobile sidebar overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                @click="mobileSidebarOpen = false"
            />
        </Transition>

        <!-- Mobile sidebar -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-y-0 left-0 z-50 lg:hidden"
            >
                <Sidebar
                    :collapsed="false"
                    @toggle="mobileSidebarOpen = false"
                />
            </div>
        </Transition>

        <!-- Desktop sidebar -->
        <div class="hidden lg:flex">
            <Sidebar
                :collapsed="sidebarCollapsed"
                @toggle="toggleSidebar"
            />
        </div>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <TopNav @toggle-sidebar="toggleMobileSidebar" />

            <main class="flex-1 overflow-y-auto px-4 py-6 lg:px-8">
                <slot />
            </main>
        </div>

        <!-- Global search (Cmd+K / Ctrl+K) -->
        <GlobalSearch />

        <!-- Flash messages -->
        <FlashMessages />
    </div>
</template>
