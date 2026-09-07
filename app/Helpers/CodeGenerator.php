<?php

namespace App\Helpers;

use InvalidArgumentException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CodeGenerator
{
    /**
     * Generate sequential, collision-free code (e.g. ABS-001, FP-001, INV-001).
     * Fully compatible with MySQL, MariaDB, PostgreSQL, and SQLite.
     * Protected by atomic cache lock, SQL-injection whitelist, and database uniqueness check.
     *
     * @param string $modelClass Eloquent Model class (e.g. AbstractSubmission::class)
     * @param string $column Database column name (e.g. 'abstract_code')
     * @param string $prefix Prefix string (e.g. 'ABS', 'FP', 'INV')
     * @param int $digits Minimum number of digits (default: 3)
     * @return string
     */
    public static function next(
        string $modelClass,
        string $column,
        string $prefix,
        int $digits = 3
    ): string {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException('Invalid column name.');
        }

        $lockKey = sprintf(
            'code-lock:%s:%s',
            class_basename($modelClass),
            $prefix
        );

        return Cache::lock($lockKey, 10)->block(5, function () use (
            $modelClass,
            $column,
            $prefix,
            $digits
        ) {
            $wrapped = DB::getQueryGrammar()->wrap($column);
            $driver = DB::getDriverName();

            $rawSql = match ($driver) {
                'pgsql' =>
                    "MAX(CAST(substring($wrapped from '([0-9]+)$') AS INTEGER)) AS max_num",

                'sqlite' =>
                    "MAX(CAST(substr($wrapped, instr($wrapped, '-') + 1) AS INTEGER)) AS max_num",

                default =>
                    "MAX(CAST(SUBSTRING_INDEX($wrapped, '-', -1) AS UNSIGNED)) AS max_num",
            };

            // Support models with and without SoftDeletes trait
            $baseQuery = method_exists($modelClass, 'withTrashed')
                ? $modelClass::withTrashed()
                : $modelClass::query();

            $next = (int) (
                $baseQuery
                    ->where($column, 'like', "{$prefix}-%")
                    ->selectRaw($rawSql)
                    ->value('max_num') ?? 0
            ) + 1;

            do {
                $code = sprintf(
                    '%s-%s',
                    $prefix,
                    str_pad((string) $next, $digits, '0', STR_PAD_LEFT)
                );

                $checkQuery = method_exists($modelClass, 'withTrashed')
                    ? $modelClass::withTrashed()
                    : $modelClass::query();

                $next++;
            } while ($checkQuery->where($column, $code)->exists());

            return $code;
        });
    }
}
