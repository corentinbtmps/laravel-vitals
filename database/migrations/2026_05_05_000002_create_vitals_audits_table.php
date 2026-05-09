<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('vitals.database'))->create('vitals_audits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('url_id')->constrained('vitals_urls')->cascadeOnDelete();
            $table->uuid('batch_id')->nullable()->index();
            $table->string('driver');
            $table->enum('device', ['mobile', 'desktop']);
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending')->index();
            $table->unsignedTinyInteger('score_performance')->nullable();
            $table->unsignedTinyInteger('score_accessibility')->nullable();
            $table->unsignedTinyInteger('score_best_practices')->nullable();
            $table->unsignedTinyInteger('score_seo')->nullable();
            $table->decimal('lcp_ms', 10, 2)->nullable();
            $table->decimal('cls', 6, 4)->nullable();
            $table->decimal('inp_ms', 10, 2)->nullable();
            $table->decimal('ttfb_ms', 10, 2)->nullable();
            $table->decimal('fcp_ms', 10, 2)->nullable();
            $table->decimal('si_ms', 10, 2)->nullable();
            $table->decimal('tbt_ms', 10, 2)->nullable();
            $table->string('report_path')->nullable();
            $table->json('details')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->string('slack_message_ts')->nullable()->comment('Slack message timestamp — used to post follow-ups as thread replies');
            $table->timestamps();

            $table->index(['url_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::connection(config('vitals.database'))->dropIfExists('vitals_audits');
    }
};
