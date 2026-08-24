import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

const getPermission = () => (
  typeof window !== 'undefined' && 'Notification' in window
    ? window.Notification.permission
    : 'unsupported'
)

const base64UrlToUint8Array = (value) => {
  const padding = '='.repeat((4 - (value.length % 4)) % 4)
  const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/')
  const raw = window.atob(base64)
  return Uint8Array.from(raw, char => char.charCodeAt(0))
}

export const useBrowserNotificationStore = defineStore('browserNotifications', () => {
  const permission = ref(getPermission())
  const appEnabled = ref(localStorage.getItem('browser-notifications-enabled') !== 'false')
  const isSupported = computed(() => permission.value !== 'unsupported'
    && 'serviceWorker' in navigator && 'PushManager' in window)
  const isEnabled = computed(() => permission.value === 'granted' && appEnabled.value)

  const refreshPermission = () => {
    permission.value = getPermission()
  }

  const registerPushSubscription = async () => {
    const { data } = await axios.get('/api/push/public-key')
    const registration = await navigator.serviceWorker.register('/push-sw.js', { scope: '/' })
    const readyRegistration = await navigator.serviceWorker.ready
    let subscription = await readyRegistration.pushManager.getSubscription()

    if (!subscription) {
      subscription = await readyRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: base64UrlToUint8Array(data.public_key),
      })
    }

    const serialized = subscription.toJSON()
    await axios.post('/api/push/subscriptions', {
      endpoint: serialized.endpoint,
      keys: serialized.keys,
      content_encoding: 'aes128gcm',
    })
  }

  const requestPermission = async () => {
    if (!isSupported.value) return 'unsupported'
    if (permission.value === 'denied') return 'denied'

    if (permission.value === 'default') {
      permission.value = await window.Notification.requestPermission()
    }
    if (permission.value !== 'granted') return permission.value

    try {
      await registerPushSubscription()
      appEnabled.value = true
      localStorage.setItem('browser-notifications-enabled', 'true')
      return 'granted'
    } catch (error) {
      console.error('Failed to register Web Push subscription:', error)
      return 'error'
    }
  }

  const setEnabled = async (enabled) => {
    if (enabled) return requestPermission()

    try {
      const registration = await navigator.serviceWorker.getRegistration('/push-sw.js')
      const subscription = await registration?.pushManager.getSubscription()
      if (subscription) {
        await axios.delete('/api/push/subscriptions', { data: { endpoint: subscription.endpoint } })
        await subscription.unsubscribe()
      }
    } catch (error) {
      console.error('Failed to remove Web Push subscription:', error)
    }

    appEnabled.value = false
    localStorage.setItem('browser-notifications-enabled', 'false')
    return 'disabled'
  }

  return { permission, appEnabled, isSupported, isEnabled, refreshPermission, requestPermission, setEnabled }
})
