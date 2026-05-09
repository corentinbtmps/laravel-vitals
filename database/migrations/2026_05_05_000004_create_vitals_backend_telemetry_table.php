<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('vitals.database'))->create('vitals_backend_telemetry', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('audit_id')->nullable()->constrained('vitals_audits')->cascadeOnDelete();
            $table->boolean('sampled_request')->default(false);
            $table->string('route_name')->nullable();
            $table->unsignedSmallInteger('http_status');
            $table->decimal('duration_ms', 10, 2);
            $table->unsignedInteger('memory_peak_kb');
            $table->unsignedInteger('queries_count');
            $table->decimal('queries_time_ms', 10, 2);
            $table->unsignedInteger('queries_unique');
            $table->boolean('n_plus_one_suspect')->default(false);
            $table->unsignedInteger('views_rendered');
            $table->decimal('views_time_ms', 10, 2);
            $table->unsignedInteger('jobs_dispatched');
            $table->unsignedInteger('events_fired');
            $table->unsignedInteger('cache_hits');
            $table->unsignedInteger('cache_misses');
            $table->json('slow_queries')->nullable();
            $table->unsignedBigInteger('peak_memory_bytes')->nullable();
            $table->boolean('truncated')->default(false);
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();

            $table->index(['audit_id']);
            $table->index(['route_name']);
        });
    }

    public function down(): void
    {
        Schema::connection(config('vitals.database'))->dropIfExists('vitals_backend_telemetry');
    }
};
