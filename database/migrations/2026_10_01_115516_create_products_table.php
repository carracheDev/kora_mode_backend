<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('sku', 80)->unique();
            $table->text('description');
            $table->string('gender', 30)->nullable();
            $table->string('subcategory', 100)->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('old_price')->nullable();
            $table->jsonb('images')->default('[]');
            $table->jsonb('sizes')->default('[]');
            $table->jsonb('colors')->default('[]');
            $table->unsignedInteger('stock')->default(0);
            $table->jsonb('tags')->default('[]');
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('popularity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_demo_data')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'category_id']);
            $table->index(['is_active', 'price']);
            $table->index(['is_active', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
