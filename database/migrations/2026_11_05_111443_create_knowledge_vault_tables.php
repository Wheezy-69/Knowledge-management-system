<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaults', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vault_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['vault_id', 'name']);
        });

        Schema::create('problems', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('programming_language', 40);
            $table->json('tags')->nullable();
            $table->text('corrected_solution')->nullable();
            $table->text('explanation')->nullable();
            $table->timestamps();
            $table->index(['section_id', 'updated_at']);
        });

        Schema::create('attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->text('code');
            $table->text('notes')->nullable();
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['problem_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('problems');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('vaults');
    }
};
