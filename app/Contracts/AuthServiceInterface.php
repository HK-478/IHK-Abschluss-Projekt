<?php

namespace App\Contracts;

interface AuthServiceInterface
{
    // Aus dem UML: +login(username: String, password: String)
    public function login(string $username, string $password): bool;

    // Aus dem UML: +logout(username: String)
    public function logout(string $username): void;

    // Aus dem UML: +verifyPasswordHash()
    public function verifyPasswordHash(string $password, string $hash): bool;
}
