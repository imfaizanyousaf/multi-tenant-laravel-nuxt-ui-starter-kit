# Frontend Domain Component Standards

Target Globs: `resources/js/components/**`

## Rules

- **Nuxt UI First**: Always utilize Nuxt UI components (e.g. `UButton`, `UInput`, `UFormField`, `UBadge`, `UDashboardPanel`, `UDashboardNavbar`) on the frontend. If a needed UI component does not exist in Nuxt UI, build a custom reusable component under `resources/js/components/`.
- **Nuxt UI Documentation Route & Web Search**:
  - Official Docs Base Route: `https://ui.nuxt.com/components/<kebab-case-component>` (e.g. `https://ui.nuxt.com/components/button`, `https://ui.nuxt.com/components/form-field`, `https://ui.nuxt.com/components/select-menu`, `https://ui.nuxt.com/components/modal`).
  - Web Search Query Strategy: To verify props, events, slots, or usage, search `site:ui.nuxt.com/components/<component-name>` or `nuxt ui v3 <component-name>` using `search_web` / `read_url_content`.
- **Domain Folder Structure**: Organize all domain-specific UI components within `resources/js/components/<entities>/`.
- **Data Module (`data.ts`)**: Every domain that uses Datatable Component MUST define `data.ts` exporting column definitions (`columns`), base row actions (`baseRowActions`), base bulk actions (`baseBulkActions`), and datatable configuration (`config = createDataTableConfig()`).
- **Unified Form Modal (`<Entity>FormModal.vue`)**: Use a single modal component for both Create and Edit operations, leveraging `DialogForm.vue` and `createReusableTemplate` from `@vueuse/core`.
