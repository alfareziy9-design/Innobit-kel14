<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class MigrateSqliteToMysql extends Command
{
    protected $signature = 'db:migrate-sqlite-to-mysql
                            {--source=database/database.sqlite : Path to the source SQLite database}
                            {--chunk=500 : Number of records inserted per batch}';

    protected $description = 'Copy all application data from SQLite to an empty MySQL database';

    public function handle(): int
    {
        $target = DB::connection();

        if (! in_array($target->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error('The default database connection must use MySQL or MariaDB.');

            return self::FAILURE;
        }

        $sourcePath = $this->resolveSourcePath((string) $this->option('source'));
        $chunkSize = filter_var($this->option('chunk'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($chunkSize === false) {
            $this->error('The --chunk option must be a positive integer.');

            return self::FAILURE;
        }

        if (! is_file($sourcePath)) {
            $this->error("SQLite source not found: {$sourcePath}");

            return self::FAILURE;
        }

        Config::set('database.connections.sqlite_transfer', [
            'driver' => 'sqlite',
            'database' => $sourcePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite_transfer');

        $source = DB::connection('sqlite_transfer');

        try {
            $tables = $this->sourceTables($source);
            $this->validateTarget($tables);
            $this->copyTables($source, $target, $tables, $chunkSize);
        } catch (Throwable $exception) {
            $this->newLine();
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::disconnect('sqlite_transfer');
        }

        $this->newLine();
        $this->info('SQLite data was migrated to MySQL successfully.');

        return self::SUCCESS;
    }

    private function resolveSourcePath(string $source): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/]{2})/', $source) === 1) {
            return $source;
        }

        return base_path($source);
    }

    /**
     * @return list<string>
     */
    private function sourceTables(ConnectionInterface $source): array
    {
        return collect($source->select(
            "SELECT name FROM sqlite_master
             WHERE type = 'table'
               AND name NOT LIKE 'sqlite_%'
               AND name != 'migrations'
             ORDER BY name"
        ))->pluck('name')->all();
    }

    /**
     * @param  list<string>  $tables
     */
    private function validateTarget(array $tables): void
    {
        $missingTables = collect($tables)
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values();

        if ($missingTables->isNotEmpty()) {
            throw new RuntimeException(
                'Run MySQL migrations first. Missing target tables: '.$missingTables->join(', ')
            );
        }

        $nonEmptyTables = collect($tables)
            ->filter(fn (string $table): bool => DB::table($table)->exists())
            ->values();

        if ($nonEmptyTables->all() === ['roles'] && $this->hasMigrationBaselineRoles()) {
            return;
        }

        if ($nonEmptyTables->isNotEmpty()) {
            throw new RuntimeException(
                'The MySQL target must be empty. Data found in: '.$nonEmptyTables->join(', ')
            );
        }
    }

    private function hasMigrationBaselineRoles(): bool
    {
        return DB::table('roles')
            ->orderBy('id')
            ->get(['id', 'name', 'label'])
            ->map(fn (object $role): array => (array) $role)
            ->all() === [
                ['id' => 1, 'name' => 'admin', 'label' => 'Administrator'],
                ['id' => 2, 'name' => 'author', 'label' => 'Author'],
                ['id' => 3, 'name' => 'user', 'label' => 'User'],
            ];
    }

    /**
     * @param  list<string>  $tables
     */
    private function copyTables(
        ConnectionInterface $source,
        ConnectionInterface $target,
        array $tables,
        int $chunkSize
    ): void {
        $target->statement('SET FOREIGN_KEY_CHECKS=0');
        $target->beginTransaction();

        try {
            if ($target->table('roles')->exists()) {
                $target->table('roles')->delete();
            }

            foreach ($tables as $table) {
                $sourceColumns = $source->getSchemaBuilder()->getColumnListing($table);
                $targetColumns = $target->getSchemaBuilder()->getColumnListing($table);
                $sortedSourceColumns = $sourceColumns;
                $sortedTargetColumns = $targetColumns;
                sort($sortedSourceColumns);
                sort($sortedTargetColumns);

                if ($sortedSourceColumns !== $sortedTargetColumns) {
                    throw new RuntimeException("Column mismatch for table [{$table}].");
                }

                $sourceCount = $source->table($table)->count();
                $copiedCount = 0;

                $source->table($table)
                    ->orderBy($sourceColumns[0])
                    ->chunk($chunkSize, function ($rows) use (
                        $target,
                        $table,
                        $sourceColumns,
                        &$copiedCount
                    ): void {
                        $records = $rows->map(
                            fn (object $row): array => array_intersect_key(
                                (array) $row,
                                array_flip($sourceColumns)
                            )
                        )->all();

                        if ($records !== []) {
                            $target->table($table)->insert($records);
                            $copiedCount += count($records);
                        }
                    });

                if ($copiedCount !== $sourceCount) {
                    throw new RuntimeException(
                        "Row count mismatch for [{$table}]: expected {$sourceCount}, copied {$copiedCount}."
                    );
                }

                $this->line(sprintf('  %-30s %d rows', $table, $copiedCount));
            }

            $target->commit();
            $this->resetAutoIncrementValues($target, $tables);
        } catch (Throwable $exception) {
            $target->rollBack();

            throw $exception;
        } finally {
            $target->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * @param  list<string>  $tables
     */
    private function resetAutoIncrementValues(ConnectionInterface $target, array $tables): void
    {
        foreach ($tables as $table) {
            if (! in_array('id', $target->getSchemaBuilder()->getColumnListing($table), true)) {
                continue;
            }

            $nextId = ((int) $target->table($table)->max('id')) + 1;
            $quotedTable = str_replace('`', '``', $table);

            $target->statement("ALTER TABLE `{$quotedTable}` AUTO_INCREMENT = {$nextId}");
        }
    }
}
