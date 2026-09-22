/**
 * Synchronizes the current user login session to the Cache API ('auth-session')
 * so that Service Workers (which cannot access localStorage) can verify whether
 * a user is currently logged in, and who that user is.
 */

export async function syncAuthSessionToCache(isLoggedIn, userId = null) {
  if (typeof window === 'undefined' || !('caches' in window)) return
  try {
    const cache = await caches.open('auth-session')
    const data = {
      is_logged_in: Boolean(isLoggedIn),
      user_id: userId ? Number(userId) : null,
      updated_at: Date.now(),
    }
    await cache.put(
      '/auth-user',
      new Response(JSON.stringify(data), {
        headers: { 'Content-Type': 'application/json' },
      })
    )
  } catch (err) {
    console.warn('Failed to sync auth session to cache:', err)
  }
}

export async function clearAuthSessionFromCache() {
  if (typeof window === 'undefined' || !('caches' in window)) return
  try {
    const cache = await caches.open('auth-session')
    const data = {
      is_logged_in: false,
      user_id: null,
      updated_at: Date.now(),
    }
    await cache.put(
      '/auth-user',
      new Response(JSON.stringify(data), {
        headers: { 'Content-Type': 'application/json' },
      })
    )
  } catch (err) {
    console.warn('Failed to clear auth session in cache:', err)
  }
}
