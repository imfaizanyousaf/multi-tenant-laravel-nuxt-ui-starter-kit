# Rule Index

This index maps file glob patterns to area-grouped rules derived from the application's `ARCHITECTURE.md`.

| Glob Pattern | Rule File | Description |
| --- | --- | --- |
| `app/Http/Requests/**` | [form-requests.md](form-requests.md) | Form Request Validation & Strict Typing Standards |
| `app/Actions/**` | [domain-actions.md](domain-actions.md) | Single-Responsibility Domain Action Class Standards |
| `app/Http/Resources/**` | [api-resources.md](api-resources.md) | Eloquent API Resources & Datatable Collections |
| `app/Http/Controllers/**` | [thin-controllers.md](thin-controllers.md) | Thin Controller & Datatable Endpoint Architecture |
| `resources/js/components/**` | [frontend-components.md](frontend-components.md) | Domain Component Structure (`data.ts`, `FormModal.vue`) |
| `resources/js/pages/**` | [frontend-pages.md](frontend-pages.md) | Single-Page vs Multi-Page Page Component Hierarchy |
| `resources/js/**` | [frontend-confirmations.md](frontend-confirmations.md) | Programmatic Deletion Confirmations via `useConfirm()` |
| `app/**`, `resources/js/**`, `tests/**` | [verification-quality.md](verification-quality.md) | Wayfinder, Pest (100% coverage), PHPStan, Pint & ESLint rules |
