<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedEmailDomain extends Model
{
    protected $fillable = ['domain', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function isEmailAllowed(string $email): bool
    {
        $emailDomain = strtolower(substr(strrchr($email, '@'), 1));

        return static::where('is_active', true)
            ->where('domain', $emailDomain)
            ->exists();
    }

    public static function getActiveDomains(): array
    {
        return static::where('is_active', true)
            ->pluck('domain')
            ->toArray();
    }
}
