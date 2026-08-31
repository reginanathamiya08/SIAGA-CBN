// Dummy Service Worker to prevent 404 latency on local dev
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', () => self.clients.claim());
