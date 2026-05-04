<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

/**
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository
{
    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    /**
     * @param  array{name: string, email: string, password: string, user_type: string}  $attributes
     */
    public function create(array $attributes): User
    {
        /** @var User $user */
        $user = parent::create($attributes);

        return $user;
    }

    protected function model(): User
    {
        return new User;
    }
}
