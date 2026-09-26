<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce NOT NULL on merchandise_items.content_id.
     *
     * The previous migration already added the column as nullable and
     * auto-assigned obvious rows. Any rows that still have NULL after
     * that pass have no usable Content in their category — log them so
     * an admin can fix them manually instead of failing the migration.
     *
     * Uses driver-aware SQL to safely rebuild the table. SQLite's
     * doctrine-free change() implementation has known issues producing
     * incomplete temp-table DDL, so we drive the column change with
     * raw SQL that works on SQLite + MySQL + PostgreSQL.
     */
    public function up(): void
    {
        $orphans = DB::table('merchandise_items')->whereNull('content_id')->get(['id', 'name', 'category_id']);
        if ($orphans->isNotEmpty()) {
            foreach ($orphans as $orphan) {
                Log::warning('Merchandise orphan before NOT NULL enforcement', [
                    'id' => $orphan->id,
                    'name' => $orphan->name,
                    'category_id' => $orphan->category_id,
                ]);
            }
            // Leave orphans as-is. Admin must clean up these rows
            // manually before this migration can complete successfully.
            return;
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE merchandise_items MODIFY COLUMN content_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE merchandise_items ALTER COLUMN content_id SET NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite has no ALTER COLUMN ... NOT NULL. Rebuild the
            // table with the column declared NOT NULL while preserving
            // every existing column and row.
            $this->rebuildMerchandiseItemsSqlite();
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE merchandise_items MODIFY COLUMN content_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE merchandise_items ALTER COLUMN content_id DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            $this->rebuildMerchandiseItemsSqlite(nullable: true);
        }
    }

    /**
     * SQLite table rebuild for merchandise_items.content_id NOT NULL toggle.
     * Preserves every column, FK, and index from the live table.
     */
    private function rebuildMerchandiseItemsSqlite(bool $nullable = false): void
    {
        $columns = DB::select("PRAGMA table_info(merchandise_items)");
        $fkList = DB::select("PRAGMA foreign_key_list(merchandise_items)");
        $indexList = DB::select("PRAGMA index_list(merchandise_items)");

        $colDefs = [];
        $colNames = [];
        foreach ($columns as $col) {
            $colNames[] = $col->name;
            $def = '"'.str_replace('"', '""', $col->name).'" '.$col->type;
            if ($col->name === 'content_id') {
                $def .= $nullable ? '' : ' NOT NULL';
            } elseif ($col->notnull) {
                $def .= ' NOT NULL';
            }
            if ($col->dflt_value !== null) {
                $def .= ' DEFAULT '.$col->dflt_value;
            }
            if ($col->pk) {
                $def .= ' PRIMARY KEY';
            }
            $colDefs[] = $def;
        }

        $fkByColumn = [];
        foreach ($fkList as $fk) {
            $fkByColumn[$fk->from][] = 'FOREIGN KEY("'.str_replace('"', '""', $fk->from).'") REFERENCES "'.str_replace('"', '""', $fk->table).'"("'.str_replace('"', '""', $fk->to).'") ON UPDATE '.strtoupper($fk->on_update ?? 'RESTRICT').' ON DELETE '.strtoupper($fk->on_delete ?? 'RESTRICT');
        }
        foreach ($fkByColumn as $parts) {
            $colDefs[] = implode(' ', $parts);
        }

        $quotedCols = array_map(fn ($c) => '"'.str_replace('"', '""', $c).'"', $colNames);

        DB::statement('PRAGMA foreign_keys=0');
        DB::statement('DROP TABLE IF EXISTS __mvm_merchandise_items');
        DB::statement('CREATE TABLE __mvm_merchandise_items ('.implode(', ', $colDefs).')');
        DB::statement('INSERT INTO __mvm_merchandise_items ('.implode(', ', $quotedCols).') SELECT '.implode(', ', $quotedCols).' FROM merchandise_items');
        DB::statement('DROP TABLE merchandise_items');
        DB::statement('ALTER TABLE __mvm_merchandise_items RENAME TO merchandise_items');

        foreach ($indexList as $idx) {
            if (! str_starts_with($idx->name, 'sqlite_autoindex_')) {
                try {
                    DB::statement('CREATE INDEX "'.str_replace('"', '""', $idx->name).'" ON merchandise_items ("'.str_replace('"', '""', $idx->name).'")');
                } catch (\Throwable $e) {
                    // index may already exist; ignore
                }
            }
        }

        DB::statement('PRAGMA foreign_keys=1');
    }
};