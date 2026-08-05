# Thin Controller Standards

Target Globs: `app/Http/Controllers/**`

## Rules

- **HTTP Concerns Only**: Controllers MUST remain thin. Delegate request validation to Form Requests and business logic execution to injected Action classes.
- **No Inline Validation**: Never run `$request->validate()` inline inside controller actions.
- **Datatable Endpoint**: Include a `table(Request $request, GetPaginated<Entities> $getPaginated)` action returning `DatatableResourceCollection`, mapped to route `GET /<entities>/table` (`name('<entities>.table')`).
- **Strict Typing**: Controllers MUST use `declare(strict_types=1);` and explicit return type declarations (`Response`, `RedirectResponse`, `DatatableResourceCollection`).
