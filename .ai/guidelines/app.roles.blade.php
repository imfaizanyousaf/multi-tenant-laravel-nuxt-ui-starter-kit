# App roles and permissions guidelines

- Do not hardcode the `'Super Admin'` string literal across PHP classes.
- On the PHP backend, use the `App\Models\Role::SUPER_ADMIN` constant defined on `App\Models\Role`.
