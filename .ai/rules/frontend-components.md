# Frontend Domain Component Standards

Target Globs: `resources/js/components/**`

## Rules

- **Domain Folder Structure**: Organize all domain-specific UI components within `resources/js/components/<entities>/`.
- **Data Module (`data.ts`)**: Every domain that uses Datatable Component MUST define `data.ts` exporting column definitions (`columns`), base row actions (`baseRowActions`), base bulk actions (`baseBulkActions`), and datatable configuration (`config = createDataTableConfig()`).
- **Unified Form Modal (`<Entity>FormModal.vue`)**: Use a single modal component for both Create and Edit operations, leveraging `DialogForm.vue` and `createReusableTemplate` from `@vueuse/core`.
