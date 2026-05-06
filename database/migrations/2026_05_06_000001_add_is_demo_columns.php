<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('vitals.database');

        foreach (['vitals_urls', 'vitals_audits', 'vitals_audit_recommendations', 'vitals_backend_telemetry'] as $table) {
            Schema::connection($connection)->table($table, function (Blueprint $blueprint): void {
                $blueprint->boolean('is_demo')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        $connection = config('vitals.database');

        foreach (['vitals_urls', 'vitals_audits', 'vitals_audit_recommendations', 'vitals_backend_telemetry'] as $table) {
            Schema::connection($connection)->table($table, function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['is_demo']);
                $blueprint->dropColumn('is_demo');
            });
        }
    }
};
