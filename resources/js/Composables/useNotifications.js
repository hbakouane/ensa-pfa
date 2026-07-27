import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

/**
 * Composable for notification management.
 *
 * Usage:
 *   const { notifications, unreadCount, markAsRead, markAllAsRead, fetchNotifications } = useNotifications();
 *
 * Automatically fetches notifications on mount and listens for
 * real-time notification events via Echo.
 */
export function useNotifications() {
    const page = usePage();
    const notifications = ref([]);
    const loading = ref(false);

    const unreadCount = computed(() => {
        return notifications.value.filter((n) => !n.read_at).length;
    });

    let echoChannel = null;

    /**
     * Fetch notifications from the API.
     */
    async function fetchNotifications() {
        loading.value = true;
        try {
            const response = await axios.get(route('notifications.index'));
            notifications.value = response.data.data ?? response.data ?? [];
        } catch (err) {
            console.error('useNotifications: Error fetching notifications', err);
        } finally {
            loading.value = false;
        }
    }

    /**
     * Mark a single notification as read.
     * @param {string|number} id - Notification ID
     */
    async function markAsRead(id) {
        try {
            await axios.patch(route('notifications.read', id));
            const notification = notifications.value.find((n) => n.id === id);
            if (notification) {
                notification.read_at = new Date().toISOString();
            }
        } catch (err) {
            console.error('useNotifications: Error marking notification as read', err);
        }
    }

    /**
     * Mark all notifications as read.
     */
    async function markAllAsRead() {
        try {
            await axios.post(route('notifications.read-all'));
            notifications.value.forEach((n) => {
                if (!n.read_at) {
                    n.read_at = new Date().toISOString();
                }
            });
        } catch (err) {
            console.error('useNotifications: Error marking all notifications as read', err);
        }
    }

    /**
     * Set up real-time listener for incoming notifications.
     */
    function setupRealtimeListener() {
        if (typeof window.Echo === 'undefined') return;

        const userId = page.props.auth?.user?.id;
        if (!userId) return;

        echoChannel = window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                // Prepend the new notification to the list
                notifications.value.unshift({
                    id: notification.id,
                    type: notification.type,
                    data: notification,
                    message: notification.message,
                    read_at: null,
                    created_at: new Date().toISOString(),
                });
            });
    }

    /**
     * Clean up the real-time listener.
     */
    function cleanupRealtimeListener() {
        if (typeof window.Echo === 'undefined' || !echoChannel) return;

        const userId = page.props.auth?.user?.id;
        if (userId) {
            window.Echo.leave(`App.Models.User.${userId}`);
        }
        echoChannel = null;
    }

    onMounted(() => {
        fetchNotifications();
        setupRealtimeListener();
    });

    onUnmounted(() => {
        cleanupRealtimeListener();
    });

    return {
        notifications,
        unreadCount,
        loading,
        markAsRead,
        markAllAsRead,
        fetchNotifications,
    };
}
