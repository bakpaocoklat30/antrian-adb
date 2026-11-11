<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

class DatabaseBackupService
{
    /**
     * Tulis dump SQL lengkap database aktif ke output stream PHP.
     *
     * @param resource $stream
     */
    public function streamSqlDump($stream): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $pdo = $connection->getPdo();
        $dbName = (string) $connection->getDatabaseName();

        fwrite($stream, "-- di-SAPA Database Backup\n");
        fwrite($stream, "-- Generated at: " . now('Asia/Jakarta')->toDateTimeString() . " WIB\n");
        fwrite($stream, "-- Driver: {$driver} | Database: {$dbName}\n\n");

        if ($driver === 'mysql') {
            fwrite($stream, "SET NAMES utf8mb4;\n");
            fwrite($stream, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($stream, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");
        } elseif ($driver === 'sqlite') {
            fwrite($stream, "PRAGMA foreign_keys = OFF;\n\n");
        }

        foreach ($this->fetchTables($driver) as $tableInfo) {
            $table = $tableInfo['name'];
            $createSql = $tableInfo['create'];
            $quotedTable = $this->quoteIdentifier($table, $driver);

            fwrite($stream, "-- ----------------------------\n");
            fwrite($stream, "-- Table: {$table}\n");
            fwrite($stream, "-- ----------------------------\n");
            fwrite($stream, "DROP TABLE IF EXISTS {$quotedTable};\n");
            fwrite($stream, rtrim($createSql, ';') . ";\n\n");

            $this->writeTableRows($stream, $table, $quotedTable, $driver, $pdo);
        }

        if ($driver === 'mysql') {
            fwrite($stream, "SET FOREIGN_KEY_CHECKS = 1;\n");
        } elseif ($driver === 'sqlite') {
            fwrite($stream, "PRAGMA foreign_keys = ON;\n");
        }
    }

    /**
     * Ambil daftar tabel base beserta DDL CREATE TABLE masing-masing.
     *
     * @return array<int, array{name: string, create: string}>
     */
    private function fetchTables(string $driver): array
    {
        $tables = [];

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC");
            foreach ($rows as $row) {
                if (!empty($row->sql)) {
                    $tables[] = ['name' => $row->name, 'create' => $row->sql];
                }
            }

            return $tables;
        }

        $rows = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $tableName = (string) ($values[0] ?? '');
            if ($tableName === '') {
                continue;
            }

            $createRow = DB::selectOne('SHOW CREATE TABLE ' . $this->quoteIdentifier($tableName, $driver));
            $createArr = (array) $createRow;
            $createSql = (string) ($createArr['Create Table'] ?? end($createArr));

            $tables[] = ['name' => $tableName, 'create' => $createSql];
        }

        return $tables;
    }

    /**
     * Stream seluruh baris tabel ke dalam batch INSERT SQL.
     *
     * @param resource $stream
     */
    private function writeTableRows($stream, string $table, string $quotedTable, string $driver, PDO $pdo): void
    {
        $batch = [];
        $columnsSql = null;

        foreach (DB::table($table)->cursor() as $row) {
            $rowArr = (array) $row;
            if ($columnsSql === null) {
                $quotedCols = array_map(fn ($col) => $this->quoteIdentifier((string) $col, $driver), array_keys($rowArr));
                $columnsSql = implode(', ', $quotedCols);
            }

            $escapedValues = array_map(fn ($val) => $this->quoteValue($val, $pdo), array_values($rowArr));
            $batch[] = '(' . implode(', ', $escapedValues) . ')';

            if (count($batch) >= 100) {
                fwrite($stream, "INSERT INTO {$quotedTable} ({$columnsSql}) VALUES\n  " . implode(",\n  ", $batch) . ";\n");
                $batch = [];
            }
        }

        if (!empty($batch) && $columnsSql !== null) {
            fwrite($stream, "INSERT INTO {$quotedTable} ({$columnsSql}) VALUES\n  " . implode(",\n  ", $batch) . ";\n");
        }

        fwrite($stream, "\n");
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        $escaped = str_replace($driver === 'mysql' ? '`' : '"', $driver === 'mysql' ? '``' : '""', $identifier);

        return $driver === 'mysql' ? "`{$escaped}`" : "\"{$escaped}\"";
    }

    private function quoteValue(mixed $value, PDO $pdo): string
    {
        if (is_null($value)) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }
}
