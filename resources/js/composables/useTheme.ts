import type { SelectItem, TabsItem } from '@nuxt/ui'
import { useColorMode, useLocalStorage } from '@vueuse/core'
import { computed, watchEffect } from 'vue'

export const themePresets = [
  { label: 'Forest', value: 'forest', primary: 'green', neutral: 'zinc', icon: 'i-lucide-tree-deciduous' },
  { label: 'Ocean', value: 'ocean', primary: 'blue', neutral: 'slate', icon: 'i-lucide-ship' },
  { label: 'Sunset', value: 'sunset', primary: 'orange', neutral: 'stone', icon: 'i-lucide-sun-dim' },
  { label: 'Berry', value: 'berry', primary: 'violet', neutral: 'mauve', icon: 'i-lucide-candy' },
] satisfies SelectItem[]

const themeModes: TabsItem[] = [
  { label: 'Light', value: 'light', icon: 'i-lucide-sun', ui: { label: 'hidden' } },
  { label: 'Dark', value: 'dark', icon: 'i-lucide-moon', ui: { label: 'hidden' } },
  { label: 'System', value: 'auto', icon: 'i-lucide-monitor', ui: { label: 'hidden' } },
]

const radiusLabels: Record<number, string> = {
  0: 'None',
  0.125: 'Extra Small',
  0.25: 'Small',
  0.375: 'Medium',
  0.5: 'Large',
}

const presetIconColors: Record<string, string> = {
  amber: 'text-amber-500 dark:text-amber-400',
  blue: 'text-blue-500 dark:text-blue-400',
  cyan: 'text-cyan-500 dark:text-cyan-400',
  emerald: 'text-emerald-500 dark:text-emerald-400',
  fuchsia: 'text-fuchsia-500 dark:text-fuchsia-400',
  gray: 'text-gray-500 dark:text-gray-400',
  indigo: 'text-indigo-500 dark:text-indigo-400',
  green: 'text-green-500 dark:text-green-400',
  lime: 'text-lime-500 dark:text-lime-400',
  orange: 'text-orange-500 dark:text-orange-400',
  pink: 'text-pink-500 dark:text-pink-400',
  purple: 'text-purple-500 dark:text-purple-400',
  red: 'text-red-500 dark:text-red-400',
  rose: 'text-rose-500 dark:text-rose-400',
  sky: 'text-sky-500 dark:text-sky-400',
  teal: 'text-teal-500 dark:text-teal-400',
  violet: 'text-violet-500 dark:text-violet-400',
  yellow: 'text-yellow-500 dark:text-yellow-400',
  black: 'text-black dark:text-white',
}

const _primary = useLocalStorage('nuxt-ui-primary', 'green')
const _neutral = useLocalStorage('nuxt-ui-neutral', 'zinc')
const _radius = useLocalStorage('nuxt-ui-radius', 0.25)
const _blackAsPrimary = useLocalStorage('nuxt-ui-black-as-primary', false)
const _themePreset = useLocalStorage<string | null>('nuxt-ui-theme-preset', null)

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

let _syncedAppConfig: ReturnType<typeof useAppConfig> | null = null

export function useTheme() {
  const appConfig = useAppConfig()
  const colorMode = useColorMode({ emitAuto: true })

  // Keep appConfig in sync with persistent refs
  if (_syncedAppConfig !== appConfig) {
    _syncedAppConfig = appConfig
    watchEffect(() => {
      if (appConfig?.ui?.colors) {
        appConfig.ui.colors.primary = _primary.value
        appConfig.ui.colors.neutral = _neutral.value
      }
    })
  }

  const neutralColors = ['slate', 'gray', 'zinc', 'neutral', 'stone', 'taupe', 'mauve', 'mist', 'olive']
  const neutral = computed({
    get() {
      return _neutral.value
    },
    set(option: string) {
      _neutral.value = option
      _themePreset.value = 'custom'
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
      _themePreset.value = 'custom'
    },
  })

  const radiuses = [0, 0.125, 0.25, 0.375, 0.5]
  const radiusOptions = radiuses.map((value) => ({
    label: radiusLabels[value] || String(value),
    value,
  }))
  const radius = computed({
    get() {
      return _radius.value
    },
    set(option: number) {
      _radius.value = option
    },
  })

  const blackAsPrimary = computed(() => _blackAsPrimary.value)

  const themePreset = computed({
    get() {
      if (_themePreset.value === 'custom') {
        return 'custom'
      }

      const selectedPreset = themePresets.find((preset) => preset.value === _themePreset.value)

      if (selectedPreset && selectedPreset.primary === _primary.value && selectedPreset.neutral === _neutral.value) {
        return selectedPreset.value
      }

      if (_blackAsPrimary.value) {
        return 'custom'
      }

      return themePresets.find((preset) => preset.primary === _primary.value && preset.neutral === _neutral.value)?.value ?? 'custom'
    },
    set(value: string) {
      if (value === 'custom') {
        _themePreset.value = 'custom'
        return
      }

      const preset = themePresets.find((option) => option.value === value)

      if (!preset) {
        return
      }

      _primary.value = preset.primary
      _neutral.value = preset.neutral
      _blackAsPrimary.value = false
      _themePreset.value = preset.value
    },
  })

  const themePresetOptions = computed<SelectItem[]>(() => [
    ...themePresets.map((preset) => ({
      ...preset,
      ui: { itemLeadingIcon: presetIconColors[preset.primary] },
    })),
    {
      label: 'Custom',
      value: 'custom',
      icon: 'i-lucide-settings-2',
      ui: { itemLeadingIcon: 'text-dimmed' },
    },
  ])
  const themePresetIcon = computed(() =>
    themePreset.value === 'custom' ? 'i-lucide-settings-2' : themePresets.find((item) => item.value === themePreset.value)?.icon,
  )
  const themePresetIconClass = computed(() =>
    themePreset.value === 'custom' ? 'text-dimmed' : presetIconColors[themePresets.find((item) => item.value === themePreset.value)?.primary ?? ''],
  )

  function setBlackAsPrimary(value: boolean) {
    _blackAsPrimary.value = value
    _themePreset.value = 'custom'
  }

  function resetTheme() {
    _primary.value = 'green'
    _neutral.value = 'zinc'
    _radius.value = 0.25
    _blackAsPrimary.value = false
    _themePreset.value = 'forest'
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
    themePresets,
    themePreset,
    radiuses,
    radius,
    resetTheme,
    isCustomized,
    colorMode,
    themeModes,
    radiusOptions,
    themePresetOptions,
    themePresetIcon,
    themePresetIconClass,
  }
}
