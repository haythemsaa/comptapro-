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
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['first_reminder', 'second_reminder', 'final_notice'])->default('first_reminder');
            $table->integer('days_overdue'); // Nombre de jours de retard lors de l'envoi
            $table->timestamp('sent_at');
            $table->string('sent_to'); // Email destinataire
            $table->text('message')->nullable();
            $table->boolean('opened')->default(false); // Email ouvert?
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
