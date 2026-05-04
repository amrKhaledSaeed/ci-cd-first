<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Models\User;
use Illuminate\Validation\Rules;
use Smpita\TypeAs\TypeAs;

final class RegisterData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'name.required' => __('Please provide your name.'),
            'email.required' => __('Please provide your email address.'),
            'email.unique' => __('This email address is already registered.'),
            'password.required' => __('Please provide a password.'),
            'password.confirmed' => __('The password confirmation does not match.'),
        ];
    }

    /**
     * @param  array{name: mixed, email: mixed, password: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: TypeAs::string($data['name']),
            email: TypeAs::string($data['email']),
            password: TypeAs::string($data['password']),
        );
    }

    /**
     * @return array{name: string, email: string, password: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
