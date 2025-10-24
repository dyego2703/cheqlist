<?php

namespace Cheqlist\Auth;

use Cheqlist\Exceptions\ValidationException;
use Cheqlist\Services\ValidationTrait;
use Respect\Validation\Validator as v;

class AuthService
{
    use ValidationTrait;

    private array $users = [
        'admin@cheqlist.test' => [
            'password' => 'secret123',
            'roles' => ['admin'],
        ],
    ];

    public function authenticate(string $username, string $password): array
    {
        $this->assert(['username' => $username, 'password' => $password], static function () {
            return v::arrayType()
                ->key('username', v::email())
                ->key('password', v::stringType()->length(6, null));
        });

        if (!isset($this->users[$username]) || $this->users[$username]['password'] !== $password) {
            throw new ValidationException([
                'credentials' => ['Invalid username or password'],
            ], 'Invalid credentials', 401);
        }

        return [
            'sub' => $username,
            'roles' => $this->users[$username]['roles'],
        ];
    }
}
