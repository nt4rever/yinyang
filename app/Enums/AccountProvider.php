<?php

namespace App\Enums;

enum AccountProvider: string
{
    case LOCAL = 'local';
    case GOOGLE = 'google';
    case KEYCLOAK = 'keycloak';

    /**
     * Check if provider requires OAuth
     */
    public function isOAuth(): bool
    {
        return $this !== self::LOCAL;
    }
}
