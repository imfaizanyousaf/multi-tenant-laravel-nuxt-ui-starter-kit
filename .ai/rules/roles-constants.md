# Roles & Permissions Constants Rule

## Rules
- Do NOT hardcode the `'Super Admin'` string literal across PHP classes or Vue components.
- On the PHP backend, use `App\Models\Role::SUPER_ADMIN` constant (defined on `App\Models\Role`).
- On the frontend TypeScript, import `ROLE_SUPER_ADMIN` from `@/constants/roles`.
