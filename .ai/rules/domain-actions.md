# Domain Action Standards

Target Globs: `app/Actions/**`

## Rules

- **Single Business Operation**: Every business mutation or query operation (Create, Update, Delete, Bulk Operations, Paginated Queries) MUST be implemented in dedicated Action classes under `app/Actions/<Domain>/`.
- **Single Public Method**: Action classes MUST expose a single public `handle(...)` method.
- **Strict Typing & PHPStan**: Classes MUST include `declare(strict_types=1);`. Array parameter annotations MUST use PHPDoc `@param array<string, mixed> $data` so PHPStan passes without errors.
