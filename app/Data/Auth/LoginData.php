<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

final class LoginData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false,
    ) {}

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'email.required' => __('Please provide your email address.'),
            'password.required' => __('Please provide your password.'),
        ];
    }

    /**
     * @param  array{email: mixed, password: mixed, remember?: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: Str::lower(TypeAs::string($data['email'])),
            password: TypeAs::string($data['password']),
            remember: (bool) ($data['remember'] ?? false),
        );
    }
}
