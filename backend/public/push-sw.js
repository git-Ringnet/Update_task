self.addEventListener('install', () => self.skipWaiting())
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()))

self.addEventListener('push', (event) => {
  event.waitUntil((async () => {
    let payload = {}
    try {
      payload = event.data ? event.data.json() : {}
    } catch (e) {
      return
    }

    // 1. Verify user authentication status from Cache API
    let authUser = null
    try {
      if ('caches' in self) {
        const cache = await caches.open('auth-session')
        const match = await cache.match('/auth-user')
        if (match) {
          authUser = await match.json()
        }
      }
    } catch (e) {}

    // If browser is NOT logged in, do NOT show notification and unsubscribe orphan subscription
    if (!authUser || !authUser.is_logged_in || !authUser.user_id) {
      try {
        const sub = await self.registration.pushManager.getSubscription()
        if (sub) {
          await sub.unsubscribe()
        }
      } catch (e) {}
      return
    }

    // 2. If it is a private message, ONLY show if current logged-in user is in private_user_ids
    if (payload.is_private) {
      const allowedUserIds = Array.isArray(payload.private_user_ids)
        ? payload.private_user_ids.map((id) => Number(id))
        : []
      const currentUserId = Number(authUser.user_id)

      if (!allowedUserIds.includes(currentUserId)) {
        // Logged-in user is not among the designated private recipients
        return
      }
    }

    const options = {
      body: payload.body || 'Có cập nhật mới trong dự án.',
      icon: payload.icon || '/cactus-logo-square.png',
      badge: '/cactus-logo-square.png',
      tag: payload.tag || 'project-update',
      renotify: true,
      data: { url: payload.url || '/views', payload },
    }

    // Broadcast via BroadcastChannel
    try {
      const channel = new BroadcastChannel('project_realtime_channel')
      channel.postMessage({ type: 'PUSH_RECEIVED', payload })
      channel.close()
    } catch (e) {}

    // Post message to all window clients
    try {
      const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
      clients.forEach((client) => {
        client.postMessage({
          type: 'PUSH_RECEIVED',
          payload,
        })
      })
    } catch (e) {}

    return self.registration.showNotification(payload.title || 'Xương Rồng', options)
  })())
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  const targetUrl = new URL(event.notification.data?.url || '/views', self.location.origin).href

  event.waitUntil((async () => {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
    const existing = clients.find((client) => client.url.startsWith(self.location.origin))

    try {
      const channel = new BroadcastChannel('project_realtime_channel')
      channel.postMessage({ type: 'NOTIFICATION_CLICKED', url: targetUrl, data: event.notification.data })
      channel.close()
    } catch (e) {}

    if (existing) {
      existing.postMessage({
        type: 'NOTIFICATION_CLICKED',
        url: targetUrl,
        data: event.notification.data,
      })
      if ('navigate' in existing && existing.url !== targetUrl) {
        await existing.navigate(targetUrl)
      }
      return existing.focus()
    }
    return self.clients.openWindow(targetUrl)
  })())
})
