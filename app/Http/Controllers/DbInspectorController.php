<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbInspectorController extends Controller
{
    /**
     * Read-only database inspector at /admin/db.
     * Lists all tables, schema columns, and first 50 sample rows.
     * Read-only — no INSERT/UPDATE/DELETE endpoints.
     */
    public function index()
    {
        $driver = DB::connection()->getDriverName();
        $database = DB::connection()->getDatabaseName();

        $tables = $this->listTables($driver);
        $inspect = [];
        foreach ($tables as $name) {
            $inspect[] = [
                'name'      => $name,
                'columns'   => $this->describeTable($driver, $name),
                'row_count' => DB::table($name)->count(),
                'sample'    => DB::table($name)->limit(50)->get()
                                    ->map(fn ($r) => (array) $r)->all(),
            ];
        }

        return view('admin.db', [
            'driver'   => $driver,
            'database' => $database,
            'tables'   => $inspect,
        ]);
    }

    /** @return string[] */
    private function listTables(string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            return array_map(fn ($r) => $r->name, $rows);
        }
        if ($driver === 'mysql') {
            $rows = DB::select('SHOW TABLES');
            return array_map(fn ($r) => array_values((array) $r)[0], $rows);
        }
        if ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename");
            return array_map(fn ($r) => $r->tablename, $rows);
        }
        return Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
    }

    /** @return array<int, array<string, mixed>> */
    private function describeTable(string $driver, string $name): array
    {
        if ($driver === 'sqlite') {
            // PRAGMA table_info returns: cid, name, type, notnull, dflt_value, pk
            $rows = DB::select("PRAGMA table_info(" . $name . ")");
            $pkCols = DB::select("PRAGMA table_info(" . $name . ")");
            $pkNames = array_filter(array_map(fn ($r) => $r->pk ? $r->name : null, $pkCols));
            return array_map(fn ($r) => [
                'name'     => $r->name,
                'type'     => $r->type,
                'nullable' => !$r->notnull,
                'default'  => $r->dflt_value,
                'primary'  => (bool) $r->pk,
            ], $rows);
        }

        if ($driver === 'mysql') {
            $db = DB::connection()->getDatabaseName();
            $rows = DB::select(
                "SELECT COLUMN_NAME as name, DATA_TYPE as type, IS_NULLABLE as is_null, COLUMN_DEFAULT as def, COLUMN_KEY as key_kind
                 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION",
                [$db, $name]
            );
            return array_map(fn ($r) => [
                'name'     => $r->name,
                'type'     => $r->type,
                'nullable' => $r->is_null === 'YES',
                'default'  => $r->def,
                'primary'  => $r->key_kind === 'PRI',
            ], $rows);
        }

        // Generic fallback: column names only via Schema
        return array_map(fn ($c) => [
            'name'     => $c,
            'type'     => 'unknown',
            'nullable' => true,
            'default'  => null,
            'primary'  => false,
        ], Schema::getColumnListing($name));
    }
}