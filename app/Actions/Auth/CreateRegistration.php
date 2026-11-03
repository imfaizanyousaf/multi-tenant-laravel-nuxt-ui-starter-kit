<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\User\CreateUser;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

use function event;

final readonly class CreateRegistration
{
    public function __construct(private CreateUser $createUser)
    {
        //
    }

    /**
     * Register a new user and dispatch the registered event.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): User
    {
        $user = $this->createUser->handle($data);

        event(new Registered($user));

        return $user;
    }
}
