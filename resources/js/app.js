import './bootstrap';
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

window.Alpine = Alpine;
Alpine.plugin(intersect);

// ── Infinite scroll helper ──
window.infiniteScroll = (callback) => ({
    loading: false,
    async loadMore() {
        if (this.loading) return;
        this.loading = true;
        await callback();
        this.loading = false;
    }
});

// ── Image lightbox ──
window.lightbox = (images) => ({
    open: false,
    current: 0,
    images,
    show(i) { this.current = i; this.open = true; },
    close() { this.open = false; },
    prev() { this.current = (this.current - 1 + this.images.length) % this.images.length; },
    next() { this.current = (this.current + 1) % this.images.length; },
});

// ── Global event bus (cross-component communication) ──
window.bus = {
    listeners: {},
    on(event, cb) { (this.listeners[event] = this.listeners[event] || []).push(cb); },
    emit(event, data) { (this.listeners[event] || []).forEach(cb => cb(data)); },
};

// ── Laravel Echo / Reverb WebSocket setup ──
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

Alpine.start();