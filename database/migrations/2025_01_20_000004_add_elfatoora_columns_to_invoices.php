<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // El Fatoora - Signature électronique
            $table->string('elfatoora_id')->nullable()->unique()->after('payment_link_expires_at');
            $table->text('elfatoora_signature')->nullable();
            $table->string('elfatoora_hash')->nullable();
            $table->text('elfatoora_qr_code')->nullable();
            $table->timestamp('elfatoora_signed_at')->nullable();

            // El Fatoora - Transmission
            $table->string('elfatoora_transmission_id')->nullable();
            $table->string('elfatoora_validation_code')->nullable();
            $table->timestamp('elfatoora_transmitted_at')->nullable();

            // El Fatoora - Statut
            $table->enum('elfatoora_status', [
                'draft',
                'signed',
                'transmitted',
                'validated',
                'cancelled',
                'archived'
            ])->nullable();
            $table->timestamp('elfatoora_validated_at')->nullable();
            $table->timestamp('elfatoora_cancelled_at')->nullable();
            $table->string('elfatoora_cancellation_reason')->nullable();

            // El Fatoora - Archivage
            $table->timestamp('elfatoora_archived_at')->nullable();
            $table->string('elfatoora_archive_url')->nullable();

            // Index
            $table->index('elfatoora_status');
            $table->index('elfatoora_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'elfatoora_id',
                'elfatoora_signature',
                'elfatoora_hash',
                'elfatoora_qr_code',
                'elfatoora_signed_at',
                'elfatoora_transmission_id',
                'elfatoora_validation_code',
                'elfatoora_transmitted_at',
                'elfatoora_status',
                'elfatoora_validated_at',
                'elfatoora_cancelled_at',
                'elfatoora_cancellation_reason',
                'elfatoora_archived_at',
                'elfatoora_archive_url',
            ]);
        });
    }
};
