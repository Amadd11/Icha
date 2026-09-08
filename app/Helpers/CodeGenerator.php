<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CodeGenerator
{
    /**
     * Calculate the next sequential code, and optionally execute a creator callback while holding the atomic lock.
     * Fully compatible with MySQL, MariaDB, PostgreSQL, and SQLite.
     * Protected by atomic cache lock, SQL-injection whitelist, and database uniqueness check.
     *
     * @template T of Model
     * @param class-string<T> $modelClass Eloquent Model class (e.g. AbstractSubmission::class)
     * @param string $column Database column name (e.g. 'abstract_code')
     * @param string $prefix Prefix string (e.g. 'ABS', 'FP', 'INV')
     * @param int $digits Minimum number of digits (default: 3)
     * @param (callable(string): mixed)|null $callback Optional callback to execute (e.g. Model::create) while the lock is held
     * @return mixed Returns the result of $callback if provided, otherwise the generated string code
     */
    public static function next(
        string $modelClass,
        string $column,
        string $prefix,
        int $digits = 3,
        ?callable $callback = null
    ): mixed {
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
            $digits,
            $callback
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

            // Query raw database table directly to include all existing records (including soft-deleted),
            // guaranteeing that database UNIQUE constraints will never collide.
            $tableName = (new $modelClass)->getTable();

            $next = (int) (
                DB::table($tableName)
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

                $exists = DB::table($tableName)->where($column, $code)->exists();
                $next++;
            } while ($exists);

            // If a callback is provided (e.g. Model::create), execute it INSIDE the lock
            if ($callback !== null) {
                return $callback($code);
            }

            return $code;
        });
    }

    /**
     * Atomically generate the code AND insert the model record inside the lock.
     * Eliminates the Time-of-Check to Time-of-Use (TOCTOU) race condition completely.
     *
     * @template T of Model
     * @param class-string<T> $modelClass
     * @param string $column
     * @param string $prefix
     * @param array<string, mixed> $attributes
     * @param int $digits
     * @return T
     */
    public static function create(
        string $modelClass,
        string $column,
        string $prefix,
        array $attributes,
        int $digits = 3
    ): Model {
        return self::next(
            $modelClass,
            $column,
            $prefix,
            $digits,
            function (string $code) use ($modelClass, $column, $attributes) {
                $attributes[$column] = $code;
                return $modelClass::create($attributes);
            }
        );
    }
}
