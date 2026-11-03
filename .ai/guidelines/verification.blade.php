# Verification and quality guidelines

Every change must pass the following checks before finalizing:

1. **Wayfinder route actions**: Run `php artisan wayfinder:generate --no-interaction` whenever routes or controllers change.
2. **Architecture tests**: Run `./vendor/bin/pest tests/Unit/ArchTest.php` and ensure it passes. Inform and ask for updating `tests/Unit/ArchTest.php` whenever a guideline adds or changes an enforceable rule.
3. **Pest test suite**: Run `XDEBUG_MODE=coverage php -d memory_limit=512M ./vendor/bin/pest --parallel --coverage --exactly=100.0` and ensure 100.0% coverage.
4. **PHPStan analysis**: Run `vendor/bin/phpstan analyse --memory-limit=512M` with zero errors.
5. **Code formatting and linting**: Run `vendor/bin/pint --dirty --format agent` and `npm run format && npm run lint`.
