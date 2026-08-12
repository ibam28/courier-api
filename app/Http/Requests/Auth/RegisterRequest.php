<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Endpoint publik — tidak butuh authentication.
     * Untuk production, bisa dilindungi middleware throttle + reCAPTCHA.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8)],
            // Role dibatasi ke admin/staff. Default staff supaya endpoint aman dipanggil publik.
            'role' => ['nullable', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
        ];
    }
}
