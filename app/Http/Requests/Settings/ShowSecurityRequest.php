<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\Features;
use Laravel\Fortify\InteractsWithTwoFactorState;

final class ShowSecurityRequest extends FormRequest
{
    use InteractsWithTwoFactorState;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Features::enabled(Features::twoFactorAuthentication())
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
