<?php

namespace App\Models;

use App\Support\FilamentLookupCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradingScale extends Model
{
    use SoftDeletes;

    protected $fillable = ['min_score', 'max_score', 'letter_grade', 'gpa_equivalent'];

    protected function casts(): array
    {
        return [
            'min_score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'gpa_equivalent' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            FilamentLookupCache::forgetGradingScales();
        });

        static::deleted(function (): void {
            FilamentLookupCache::forgetGradingScales();
        });

        static::restored(function (): void {
            FilamentLookupCache::forgetGradingScales();
        });
    }

    public static function getLetterGrade(float $score): ?string
    {
        $scale = static::findCachedScale($score);

        return $scale['letter_grade'] ?? null;
    }

    public static function getGpa(float $score): ?float
    {
        $scale = static::findCachedScale($score);
        $gpa = $scale['gpa_equivalent'] ?? null;

        return $gpa !== null ? (float) $gpa : null;
    }

    /**
     * @return null|array{min_score: float, max_score: float, letter_grade: string, gpa_equivalent: float}
     */
    private static function findCachedScale(float $score): ?array
    {
        foreach (FilamentLookupCache::gradingScales() as $scale) {
            if ($scale['min_score'] <= $score && $scale['max_score'] >= $score) {
                return $scale;
            }
        }

        return null;
    }
}
