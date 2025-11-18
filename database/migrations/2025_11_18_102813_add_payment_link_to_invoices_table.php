<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_token')->unique()->nullable()->after('paid_amount');
            $table->boolean('payment_link_enabled')->default(false)->after('payment_token');
            $table->timestamp('payment_link_expires_at')->nullable()->after('payment_link_enabled');
            $table->string('payment_method')->nullable()->after('payment_link_expires_at'); // stripe, paypal, bank_transfer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_token', 'payment_link_enabled', 'payment_link_expires_at', 'payment_method']);
        });
    }
};
