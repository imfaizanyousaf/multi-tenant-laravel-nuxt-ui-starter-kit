<?php

use Illuminate\Support\Facades\Validator;

it('checks for missing debug statements', function (): void {
    expect(['dd', 'dump', 'ray'])
        ->not->toBeUsed();
});

it('checks the validator facade is not used', function (): void {
    expect(Validator::class)
        ->not->toBeUsed()
        ->ignoring('App\Actions\Fortify');
});
