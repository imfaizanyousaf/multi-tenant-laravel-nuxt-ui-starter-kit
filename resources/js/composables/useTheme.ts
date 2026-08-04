import { useLocalStorage } from '@vueuse/core'
import { computed, watchEffect } from 'vue'

const _primary = useLocalStorage('nuxt-ui-primary', 'green')
const _neutral = useLocalStorage('nuxt-ui-neutral', 'zinc')
const _radius = useLocalStorage('nuxt-ui-radius', 0.25)
const _blackAsPrimary = useLocalStorage('nuxt-ui-black-as-primary', false)

// Module-level watcher to update DOM styles once
if (typeof window !== 'undefined') {
  watchEffect(() => {
    if (typeof document === 'undefined') return

    document.documentElement.style.setProperty('--ui-radius', `${_radius.value}rem`)

    let styleEl = document.getElementById('nuxt-ui-black-as-primary')
    if (_blackAsPrimary.value) {
      if (!styleEl) {
        styleEl = document.createElement('style')
        styleEl.id = 'nuxt-ui-black-as-primary'
        document.head.appendChild(styleEl)
      }
      styleEl.innerHTML = `:root { --ui-primary: black; } .dark { --ui-primary: white; }`
    } else if (styleEl) {
      styleEl.remove()
    }
  })
}

export function useTheme() {
  const appConfig = useAppConfig()

  // Keep appConfig in sync with persistent refs
  watchEffect(() => {
    if (appConfig?.ui?.colors) {
      appConfig.ui.colors.primary = _primary.value
      appConfig.ui.colors.neutral = _neutral.value
    }
  })

  const neutralColors = ['slate', 'gray', 'zinc', 'neutral', 'stone', 'taupe', 'mauve', 'mist', 'olive']
  const neutral = computed({
    get() {
      return _neutral.value
    },
    set(option: string) {
      _neutral.value = option
    },
  })

  const primaryColors = [
    'red',
    'orange',
    'amber',
    'yellow',
    'lime',
    'green',
    'emerald',
    'teal',
    'cyan',
    'sky',
    'blue',
    'indigo',
    'violet',
    'purple',
    'fuchsia',
    'pink',
    'rose',
  ]
  const primary = computed({
    get() {
      return _primary.value
    },
    set(option: string) {
      _primary.value = option
      setBlackAsPrimary(false)
    },
  })

  const radiuses = [0, 0.125, 0.25, 0.375, 0.5]
  const radius = computed({
    get() {
      return _radius.value
    },
    set(option: number) {
      _radius.value = option
    },
  })

  const blackAsPrimary = computed(() => _blackAsPrimary.value)

  function setBlackAsPrimary(value: boolean) {
    _blackAsPrimary.value = value
  }

  function resetTheme() {
    _primary.value = 'green'
    _neutral.value = 'zinc'
    _radius.value = 0.25
    _blackAsPrimary.value = false
  }

  const isCustomized = computed(() => {
    return _primary.value !== 'green' || _neutral.value !== 'zinc' || _radius.value !== 0.25 || _blackAsPrimary.value
  })

  return {
    neutralColors,
    neutral,
    primaryColors,
    primary,
    blackAsPrimary,
    setBlackAsPrimary,
    radiuses,
    radius,
    resetTheme,
    isCustomized,
  }
}
