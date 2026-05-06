<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('vitals.database'))->table('vitals_audits', function (Blueprint $table): void {
            $table->json('details')->nullable()->after('report_path');
        });
    }

    public function down(): void
    {
        $connection = config('vitals.database');

        if (! Schema::connection($connection)->hasTable('vitals_audits')) {
            return;
        }

        Schema::connection($connection)->table('vitals_audits', function (Blueprint $table): void {
            $table->dropColumn('details');
        });
    }
};
