<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('vitals.database'))->create('vitals_rum_events', function (Blueprint $table): void {
            $table->id();
            $table->string('url', 2048);
            $table->string('metric', 8);   // LCP, INP, CLS, TTFB, FCP
            $table->decimal('value', 12, 4);
            $table->string('rating', 24)->nullable();
            $table->string('device', 16);
            $table->string('navigation_type', 32)->nullable();
            $table->string('connection', 32)->nullable();
            $table->json('attribution')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['metric', 'occurred_at']);
        });

        // Add partial index on url prefix for MySQL/MariaDB; skip for SQLite
        $driver = DB::connection(config('vitals.database'))->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::connection(config('vitals.database'))
                ->statement('ALTER TABLE vitals_rum_events ADD INDEX vitals_rum_url_metric_occurred (url(255), metric, occurred_at)');
        } else {
            Schema::connection(config('vitals.database'))->table('vitals_rum_events', function (Blueprint $table): void {
                $table->index(['metric', 'occurred_at'], 'vitals_rum_url_metric_occurred');
            });
        }
    }

    public function down(): void
    {
        Schema::connection(config('vitals.database'))->dropIfExists('vitals_rum_events');
    }
};
