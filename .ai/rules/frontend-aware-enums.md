# Frontend-Aware Enums

When creating enums that will be used on the frontend, you must create `label()` and `color()` helper functions directly on the enum. When passing the enum to the frontend (e.g. via API Resources), pass a structured object containing `value`, `label`, and `color` rather than raw values to prevent hardcoded ternary checks or duplicate logic in Vue/JS components.

Example Enum:
```php
enum TenantStatus: string {
    case CREATING = 'creating';
    case ACTIVE = 'active';

    public function label(): string { return ucfirst($this->value); }
    public function color(): string {
        return match ($this) {
            self::CREATING => 'warning',
            self::ACTIVE => 'success',
        };
    }
}
```

Example Resource output:
```php
'status' => [
    'value' => $this->status->value,
    'label' => $this->status->label(),
    'color' => $this->status->color(),
],
```
