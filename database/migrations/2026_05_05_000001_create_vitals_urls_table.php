<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('vitals.database'))->create('vitals_urls', function (Blueprint $table): void {
            $table->id();
            $table->string('label')->unique();
            $table->string('path');
            $table->enum('device', ['mobile', 'desktop', 'both'])->default('both');
            $table->json('options')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection(config('vitals.database'))->dropIfExists('vitals_urls');
    }
};
