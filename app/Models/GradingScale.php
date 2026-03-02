<?php

namespace App\Models;

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

    public static function getLetterGrade(float $score): ?string
    {
        return static::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->value('letter_grade');
    }

    public static function getGpa(float $score): ?float
    {
        $gpa = static::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->value('gpa_equivalent');

        return $gpa !== null ? (float) $gpa : null;
    }
}
