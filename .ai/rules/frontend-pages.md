# Page Component Structure Standards

Target Globs: `resources/js/pages/**`

## Rules

- **Single-Page Models**: If a model has only a single page view (e.g. datatable listing), place it as `resources/js/pages/<Entities>.vue`.
- **Multi-Page Models**: If a model requires multiple frontend pages (e.g., listing, specific view/profile, detailed analytics), create a dedicated folder under `resources/js/pages/<entities>/`:
  - `resources/js/pages/<entities>/Index.vue`: Main listing page rendering `<DataTable>`.
  - `resources/js/pages/<entities>/View.vue`: Detailed view page for a specific resource instance.
  - `resources/js/pages/<entities>/Edit.vue`: Full-page editor (if complex full-page editing is needed beyond modals).
