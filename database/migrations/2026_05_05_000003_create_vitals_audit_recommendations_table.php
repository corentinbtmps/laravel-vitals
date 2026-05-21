<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('vitals.database'))->create('vitals_audit_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('audit_id')->constrained('vitals_audits')->cascadeOnDelete();
            $table->enum('source', ['lighthouse', 'backend', 'static', 'config', 'seo']);
            $table->string('audit_key');
            $table->enum('category', ['performance', 'accessibility', 'seo', 'best_practices']);
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->string('title_key');
            $table->string('description_key');
            $table->json('translation_params')->nullable();
            $table->json('metrics')->nullable();
            $table->json('code_references')->nullable();
            $table->json('detail_items')->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();

            $table->index(['audit_id', 'severity']);
            $table->index(['audit_key']);
        });
    }

    public function down(): void
    {
        Schema::connection(config('vitals.database'))->dropIfExists('vitals_audit_recommendations');
    }
};
