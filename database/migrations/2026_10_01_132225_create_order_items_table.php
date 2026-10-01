<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 180);
            $table->string('product_slug', 200);
            $table->string('sku', 80);
            $table->string('size', 30)->nullable();
            $table->string('color', 80)->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->timestampsTz();
            $table->index(['order_id', 'product_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
