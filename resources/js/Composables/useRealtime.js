import { onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for Echo/Reverb real-time channel subscriptions.
 *
 * Usage:
 *   const { subscribe, unsubscribe } = useRealtime();
 *   subscribe('my-channel', 'MyEvent', (data) => { ... });
 *
 * Automatically subscribes to the company-wide channel on setup and
 * cleans up all subscriptions when the component unmounts.
 */
export function useRealtime() {
    const page = usePage();
    const subscriptions = [];

    /**
     * Subscribe to a private channel event.
     * @param {string} channel - Channel name (e.g. 'interviews.5')
     * @param {string} event - Event name (e.g. 'InterviewScheduled')
     * @param {Function} callback - Handler function
     */
    function subscribe(channel, event, callback) {
        if (typeof window.Echo === 'undefined') {
            console.warn('useRealtime: Laravel Echo is not available. Skipping subscription.');
            return;
        }

        const subscription = window.Echo.private(channel).listen(event, callback);
        subscriptions.push({ channel, event, subscription });
    }

    /**
     * Unsubscribe from a specific channel.
     * @param {string} channel - Channel name to leave
     */
    function unsubscribe(channel) {
        if (typeof window.Echo === 'undefined') return;

        window.Echo.leave(channel);
        const idx = subscriptions.findIndex((s) => s.channel === channel);
        if (idx > -1) {
            subscriptions.splice(idx, 1);
        }
    }

    /**
     * Clean up all subscriptions.
     */
    function cleanup() {
        if (typeof window.Echo === 'undefined') return;

        subscriptions.forEach((s) => {
            window.Echo.leave(s.channel);
        });
        subscriptions.length = 0;
    }

    onMounted(() => {
        if (typeof window.Echo === 'undefined') return;

        // Auto-subscribe to company-wide channel
        const companyId = page.props.auth?.company?.id;
        if (companyId) {
            const companyChannel = `company.${companyId}`;

            subscribe(companyChannel, 'application.stage.changed', (data) => {
                // Dispatch a custom browser event for other components to react to
                window.dispatchEvent(
                    new CustomEvent('realtime:application.stage.changed', { detail: data }),
                );
            });

            subscribe(companyChannel, 'comment.added', (data) => {
                window.dispatchEvent(
                    new CustomEvent('realtime:comment.added', { detail: data }),
                );
            });

            subscribe(companyChannel, 'interview.scheduled', (data) => {
                window.dispatchEvent(
                    new CustomEvent('realtime:interview.scheduled', { detail: data }),
                );
            });
        }
    });

    onUnmounted(() => {
        cleanup();
    });

    return {
        subscribe,
        unsubscribe,
    };
}
