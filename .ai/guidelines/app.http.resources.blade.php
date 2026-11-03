# App/Http/Resources guidelines

- Create a dedicated `<Entity>Resource` extending `JsonResource` for consistent Eloquent object serialization.
- Wrap datatable specific paginated queries with `DatatableResourceCollection`:

```php
return new DatatableResourceCollection($paginatedData, <Entity>Resource::class);
```

- Preserve the standardized client-side pagination metadata:
  `{ data: [...], meta: { current_page, last_page, per_page, total, from, to } }`.
