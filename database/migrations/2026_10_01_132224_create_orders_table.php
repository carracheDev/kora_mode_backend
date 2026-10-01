<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30);
            $table->string('customer_city', 100);
            $table->text('customer_address');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('delivery_fee');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->string('promo_code', 40)->nullable();
            $table->unsignedBigInteger('total');
            $table->string('status', 30)->default('pending')->index();
            $table->string('payment_method', 30);
            $table->string('payment_status', 30)->default('pending')->index();
            $table->string('fedapay_transaction_id', 100)->nullable()->unique();
            $table->text('payment_url')->nullable();
            $table->timestampTz('stock_reserved_at')->nullable();
            $table->timestampTz('stock_released_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
