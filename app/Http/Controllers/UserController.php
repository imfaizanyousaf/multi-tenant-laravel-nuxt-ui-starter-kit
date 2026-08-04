<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of real users from DB.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Users', [
            'users' => UserResource::collection(User::query()->latest()->get())->resolve(),
        ]);
    }
}
