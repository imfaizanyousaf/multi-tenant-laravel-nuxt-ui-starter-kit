# API Resources & Datatable Collections

Target Globs: `app/Http/Resources/**`

## Rules

- **Eloquent API Resource**: Create a dedicated `<Entity>Resource` extending `JsonResource` for consistent object serialization.
- **Datatable Pagination Response**: Use `DatatableResourceCollection` to wrap paginated queries (`return new DatatableResourceCollection($paginatedData, <Entity>Resource::class);`).
- **Standardized Response Structure**: Ensures standard client-side pagination metadata (`{ data: [...], meta: { current_page, last_page, per_page, total, from, to } }`).
