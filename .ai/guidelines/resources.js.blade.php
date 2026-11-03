# Resources/JS guidelines

## UI stability

- Preserve the existing layout during initial rendering, navigation, polling, form submission, and deletion. Avoid flashes, jumps, clipping, and avoidable layout shifts.
- Reuse existing layouts, shared components, Nuxt UI primitives, and established spacing, color, radius, and typography patterns before adding local styles.
- When changing a shared component, verify its default, hover, focus, selected, disabled, loading, empty, and error states.
- Keep conditional content from unexpectedly moving surrounding controls. Reserve space for validation messages and status indicators, or place transient feedback in an appropriate overlay.
- Keep loading and loaded states the same approximate shape. Skeletons must match the content they replace at the relevant breakpoint and must not replace data that is already visible during a refresh.

## Loading and deferred data

- Show a loading state only while data is genuinely unavailable or an action is processing. Keep existing content visible during refreshes whenever possible.
- Use Inertia deferred props and `WhenVisible` for expensive or below-the-fold data, with an explicit empty or skeleton state.
- Use `usePoll` only for data that needs periodic refresh, scope polling to the page props it needs, and cleanly handle inactive or stopped states.
- Loading, disabled, and enabled controls must retain stable dimensions. Prefer Nuxt UI loading states over replacing the entire control.

## Navigation and requests

- Use Wayfinder-generated route and controller functions instead of hardcoded backend URLs.
- Use Inertia `<Link>` and `router` for Inertia navigation. Use `<Form>` or the project’s existing form helpers for submissions rather than native navigation.
- Prefetch only safe GET Inertia pages. Never prefetch mutating requests, external URLs, downloads, JSON endpoints, or routes whose GET request has side effects.
- Keep one user intent to one request. Use client-side state or `router.replace()` for local URL-only filters when no server data changes; do not fetch in a mount hook just to obtain data required for the first render.
- Use partial reloads (`only`) for filters, polling, refresh buttons, and actions that need updated page props. Keep existing data visible until replacement data arrives.
- Use Inertia v3 cancellation APIs and current event names when handling request lifecycle errors; do not use removed APIs such as `router.cancel()` or the old `invalid` and `exception` events.

## Actions and forms

- Use `useConfirm()` for single and bulk deletion confirmations; do not create one-off delete modals.
- Disable or show loading state on submitted controls, prevent duplicate submissions, and preserve the control’s layout while processing.
- Use optimistic updates only when the UI can safely roll back on failure. Wait for the server when the response provides a new identifier, validation result, authorization result, or other required state.
- Surface validation and request errors through the existing form and alert components. Do not silently swallow failed requests or show success UI for an unconfirmed action.
- After a mutation, refresh only the affected props or use the existing page action pattern so tables, counts, and navigation state remain consistent.

## Rendering and responsive behavior

- Derive display values from props and composables during render. Do not copy props into local state with an effect unless the component intentionally owns an editable draft.
- Use stable, unique keys for `v-for` lists. Never use an array index for rows that can be inserted, removed, or reordered.
- Keep long names, URLs, messages, and table values from forcing layout overflow. Use `min-w-0`, truncation or line clamping, and provide the full value through an accessible label or tooltip where appropriate.
- Dialogs, popovers, dropdowns, and tables must remain usable at narrow widths. Constrain overlays to the viewport, allow internal scrolling, and avoid introducing a second page scrollbar.
- Preserve keyboard focus and clear dismissal behavior for modals, menus, popovers, and confirmation flows. Use Nuxt UI accessibility behavior instead of replacing it with custom event handling.
- Verify responsive states at narrow and wide breakpoints, in light and dark themes, including empty, loading, error, and long-content states.

## Frontend confirmations

- Do not build separate delete modal components for models.
- Use the programmatic `useConfirm()` composable for single and bulk deletion confirmations across all frontend components.

## Frontend pages

- If a model has only a single page view, such as a datatable listing, place it at `resources/js/pages/<Entities>.vue`.
- If a model requires multiple frontend pages, create a dedicated folder under `resources/js/pages/<entities>/`:
  - `resources/js/pages/<entities>/Index.vue`: Main listing page rendering `<DataTable>`.
  - `resources/js/pages/<entities>/View.vue`: Detailed view page for a specific resource instance.
  - `resources/js/pages/<entities>/Edit.vue`: Full-page editor when complex editing is needed beyond modals.

## Frontend domain components

- Always use Nuxt UI components such as `UButton`, `UInput`, `UFormField`, `UBadge`, `UDashboardPanel`, and `UDashboardNavbar`.
- If a needed UI component does not exist in Nuxt UI, build a custom reusable component under `resources/js/components/`.
- Organize all domain-specific UI components under `resources/js/components/<entities>/`.
- Every domain that uses the Datatable component must define `data.ts` exporting `columns`, `baseRowActions`, `baseBulkActions`, and `config = createDataTableConfig()`.
- Use one `<Entity>FormModal.vue` component for both create and edit operations, leveraging `DialogForm.vue` and `createReusableTemplate` from `@vueuse/core`.

### Nuxt UI documentation

- Official documentation route: `https://ui.nuxt.com/components/<kebab-case-component>`.
- To verify props, events, slots, or usage, search `site:ui.nuxt.com/components/<component-name>` or `nuxt ui v3 <component-name>`.

## Roles and permissions

- Do not hardcode the `'Super Admin'` string literal across Vue components.
- Import `ROLE_SUPER_ADMIN` from `@/constants/roles`.