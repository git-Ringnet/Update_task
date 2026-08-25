self.addEventListener('push', (event) => {
  const payload = event.data ? event.data.json() : {}
  const options = {
    body: payload.body || 'C\u00f3 c\u1eadp nh\u1eadt m\u1edbi trong d\u1ef1 \u00e1n.',
    icon: payload.icon || '/cactus-logo-square.png',
    badge: '/cactus-logo-square.png',
    tag: payload.tag || 'project-update',
    renotify: true,
    data: { url: payload.url || '/views' },
  }

  event.waitUntil(self.registration.showNotification(payload.title || 'X\u01b0\u1edfng R\u1ed3ng', options))
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  const targetUrl = new URL(event.notification.data?.url || '/views', self.location.origin).href

  event.waitUntil((async () => {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
    const existing = clients.find(client => client.url.startsWith(self.location.origin))
    if (existing) return existing.focus()
    return self.clients.openWindow(targetUrl)
  })())
})
