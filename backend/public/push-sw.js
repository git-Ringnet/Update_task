self.addEventListener('push', (event) => {
  const payload = event.data ? event.data.json() : {}
  const options = {
    body: payload.body || 'Có cập nhật mới trong dự án.',
    icon: payload.icon || '/cactus-logo.png',
    badge: '/cactus-logo.png',
    tag: payload.tag || 'project-update',
    renotify: true,
    data: { url: payload.url || '/views' },
  }

  event.waitUntil(self.registration.showNotification(payload.title || 'Xưởng Rồng', options))
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
