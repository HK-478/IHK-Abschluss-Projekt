<?php

namespace App\Contracts;

interface IdentityProviderInterface
{
    // Aus dem UML: +authenticate()
    public function authenticate(string $credentials);

    // Aus dem UML: +refreshToken()
    public function refreshToken();

    // Aus dem UML: +findUser()
    public function findUser(string $identifier);

    // Aus dem UML: +syncUser() - Hier läuft später dein Python-Script-Aufruf
    public function syncUser(): void;
}
