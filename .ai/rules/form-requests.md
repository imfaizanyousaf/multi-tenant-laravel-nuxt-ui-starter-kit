# Form Request Validation Standards

Target Globs: `app/Http/Requests/**`

## Rules

- **Single Responsibility**: ALL HTTP request validation and authorization MUST be encapsulated within dedicated `FormRequest` classes under `app/Http/Requests/<Domain>/`.
- **No Inline Validation**: Controllers MUST NOT run inline `$request->validate()` calls.
- **Strict Typing**: Form Requests MUST include `declare(strict_types=1);`, explicit return type declarations (`rules(): array`, `authorize(): bool`), and parameter type hints.
- **Rules Format**: Return rules as structured arrays (not pipe-separated strings). Use PHPDoc `@return array<string, ValidationRule|array<mixed>|string>` for PHPStan compatibility.
