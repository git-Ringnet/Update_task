self.addEventListener('install', () => self.skipWaiting())
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()))

self.addEventListener('push', (event) => {
  const payload = event.data ? event.data.json() : {}
  const options = {
    body: payload.body || 'Có cập nhật mới trong dự án.',
    icon: payload.icon || '/cactus-logo-square.png',
    badge: '/cactus-logo-square.png',
    tag: payload.tag || 'project-update',
    renotify: true,
    data: { url: payload.url || '/views', payload },
  }

  // 1. Broadcast via BroadcastChannel
  try {
    const channel = new BroadcastChannel('project_realtime_channel')
    channel.postMessage({ type: 'PUSH_RECEIVED', payload })
    channel.close()
  } catch (e) { }

  // 2. Post message to all window clients
  const notifyClientsPromise = self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
    clients.forEach((client) => {
      client.postMessage({
        type: 'PUSH_RECEIVED',
        payload
      })
    })
  })

  event.waitUntil(
    Promise.all([
      self.registration.showNotification(payload.title || 'Xương Rồng', options),
      notifyClientsPromise
    ])
  )
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  const targetUrl = new URL(event.notification.data?.url || '/views', self.location.origin).href

  event.waitUntil((async () => {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
    const existing = clients.find(client => client.url.startsWith(self.location.origin))

    try {
      const channel = new BroadcastChannel('project_realtime_channel')
      channel.postMessage({ type: 'NOTIFICATION_CLICKED', url: targetUrl, data: event.notification.data })
      channel.close()
    } catch (e) { }

    if (existing) {
      existing.postMessage({
        type: 'NOTIFICATION_CLICKED',
        url: targetUrl,
        data: event.notification.data
      })
      if ('navigate' in existing && existing.url !== targetUrl) {
        await existing.navigate(targetUrl)
      }
      return existing.focus()
    }
    return self.clients.openWindow(targetUrl)
  })())
})
