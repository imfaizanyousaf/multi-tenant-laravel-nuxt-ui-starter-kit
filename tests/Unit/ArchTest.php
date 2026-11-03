<?php

declare(strict_types=1);

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

arch()->preset()->php();
arch()->preset()->strict();
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('policies are the single source of truth for authorization')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy')
    ->not->toUse([
        Validator::class,
        ValidationException::class,
    ]);

arch('authorization is delegated to policies')
    ->expect([
        'App\Http\Controllers',
        'App\Http\Requests',
        'App\Actions',
        'App\Models',
        'App\Policies',
    ])
    ->not->toUse([
        Gate::class,
        Illuminate\Contracts\Auth\Access\Gate::class,
        HandlesAuthorization::class,
        AuthorizesRequests::class,
    ]);

arch('controllers leave validation to form requests')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'abort',
        'abort_if',
        'abort_unless',
        'throw_if',
        'throw_unless',
        'validator',
        Validator::class,
        ValidationException::class,
    ]);

arch('actions leave validation to form requests')
    ->expect('App\Actions')
    ->not->toUse([
        'abort',
        'abort_if',
        'abort_unless',
        'throw_if',
        'throw_unless',
        'validator',
        Validator::class,
        ValidationException::class,
    ]);

test('actions only create, update, or delete', function (): void {
    foreach (File::allFiles(app_path('Actions')) as $file) {
        expect($file->getFilenameWithoutExtension())->toMatch('/^(Create|Update|Delete)[A-Z]/');
    }
});

test('every model has a policy', function (): void {
    foreach (File::files(app_path('Models')) as $file) {
        $model = $file->getFilenameWithoutExtension();
        $policy = 'App\\Policies\\'.$model.'Policy';

        expect(class_exists($policy))->toBeTrue("App\\Models\\{$model} must have a policy.");
    }
});

test('policies use the project permissions', function (): void {
    foreach (File::files(app_path('Policies')) as $file) {
        expect($file->getContents())->toContain('->can(');
    }
});

test('form requests authorize every endpoint through policies', function (): void {
    foreach (File::allFiles(app_path('Http/Requests')) as $file) {
        $request = 'App\\Http\\Requests\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

        $authorize = new ReflectionMethod($request, 'authorize');

        expect($authorize->getReturnType())
            ->toBeInstanceOf(ReflectionNamedType::class)
            ->and($authorize->getReturnType()?->getName())
            ->toBe(Response::class);
    }
});

test('role and user form requests delegate authorization to policies', function (): void {
    foreach ([app_path('Http/Requests/Role'), app_path('Http/Requests/User')] as $directory) {
        foreach (File::allFiles($directory) as $file) {
            expect($file->getContents())->toContain('can(');
        }
    }
});

test('controllers are cruddy by design, or invokable', function (): void {
    $resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'middleware'];

    foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
        $controller = 'App\\Http\\Controllers\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

        $methods = array_values(array_map(
            fn (ReflectionMethod $method): string => $method->getName(),
            array_filter(
                new ReflectionClass($controller)->getMethods(ReflectionMethod::IS_PUBLIC),
                fn (ReflectionMethod $method): bool => ! $method->isConstructor(),
            ),
        ));

        $isInvokable = in_array('__invoke', $methods, true)
            && array_diff($methods, ['__invoke', 'middleware']) === [];
        $isCrud = array_diff($methods, $resourceMethods) === [];

        expect($isInvokable || $isCrud)
            ->toBeTrue("{$controller} may only have the resource methods, or a single __invoke method.");
    }
});

test('form requests are named after the controller method they serve', function (): void {
    foreach (File::allFiles(app_path('Http/Requests')) as $file) {
        expect($file->getFilenameWithoutExtension())->toMatch('/^(List|Show|Create|Update|Delete|Role|User)[A-Z]\w*Request$/');
    }
});
