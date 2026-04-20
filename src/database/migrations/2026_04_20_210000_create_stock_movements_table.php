<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('variant_id')->constrained('product_variants');
            $table->enum('type', ['ENTRY', 'EXIT', 'REVERSAL']);
            $table->unsignedInteger('quantity');
            $table->string('reason')->nullable();
            $table->foreignUuid('referenced_movement_id')
                ->nullable()
                ->constrained('stock_movements');
            $table->timestamps();

            $table->index('variant_id');
            $table->index('referenced_movement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
