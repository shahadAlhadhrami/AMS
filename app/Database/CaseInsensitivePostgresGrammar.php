<?php

namespace App\Database;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

class CaseInsensitivePostgresGrammar extends PostgresGrammar
{
    protected function whereBasic(Builder $query, $where): string
    {
        if (str_contains(strtolower($where['operator']), 'like')) {
            $where['operator'] = str_ireplace('like', 'ilike', $where['operator']);
        }

        return parent::whereBasic($query, $where);
    }
}
