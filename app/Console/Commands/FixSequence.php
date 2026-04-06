<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixSequence extends Command
{
    /**
     * Nama & signature command.
     *
     * php artisan db:fix-sequence users
     */
    protected $signature = 'db:fix-sequence {table}';

    protected $description = 'Perbaiki sequence PostgreSQL agar sinkron dengan data terakhir di tabel';

    public function handle()
    {
        $table = $this->argument('table');

        if (!Schema::hasTable($table)) {
            $this->error("Tabel '{$table}' tidak ditemukan.");
            return Command::FAILURE;
        }

        // Ambil nama primary key (biasanya 'id')
        $primaryKey = 'id';

        // Ambil nilai max id
        $maxId = DB::table($table)->max($primaryKey) ?? 0;

        // Nama sequence biasanya {table}_{column}_seq
        $sequence = "{$table}_{$primaryKey}_seq";

        try {
            DB::statement("SELECT setval('{$sequence}', {$maxId} + 1)");
            $this->info("✅ Sequence '{$sequence}' berhasil diset ke " . ($maxId + 1));
        } catch (\Exception $e) {
            $this->error("Gagal memperbaiki sequence: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
