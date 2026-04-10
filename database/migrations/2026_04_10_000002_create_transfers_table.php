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
        Schema::create('transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('reference')->unique();
            $table->string('status')->default('CREATED')->index();
            $table->string('provider')->default('ORANGE_MONEY');
            $table->decimal('sender_amount_cad', 12, 2);
            $table->decimal('fee_cad', 12, 2);
            $table->decimal('exchange_rate', 12, 4);
            $table->decimal('recipient_amount_xaf', 14, 2);
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('idempotency_key')->nullable()->unique();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
