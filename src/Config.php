<?php

namespace App;

class Config
{
    // Used by task 6 (API key auth). Not wired up to anything yet.
    public static function apiKey(): string
    {
        return getenv('API_KEY') ?: 'dev-secret-key';
    }
}
