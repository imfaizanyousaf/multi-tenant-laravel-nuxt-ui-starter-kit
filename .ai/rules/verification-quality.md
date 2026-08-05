# Verification & Quality Control Standards

Target Globs: `app/**`, `resources/js/**`, `tests/**`

## Rules

Every change MUST pass the following checks before finalizing:

1. **Wayfinder Route Actions**: Run `php artisan wayfinder:generate --no-interaction` whenever routes or controllers change.
2. **Pest Test Suite**: Run `XDEBUG_MODE=coverage php -d memory_limit=512M ./vendor/bin/pest --parallel --coverage --exactly=100.0` ensuring 100.0% coverage.
3. **PHPStan Analysis**: Run `vendor/bin/phpstan analyse --memory-limit=512M` with 0 errors.
4. **Code Formatting & Linting**: Run `vendor/bin/pint --dirty --format agent` and `npm run format && npm run lint`.
