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
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->index();
            $table->string('provider', 50)->index();
            $table->decimal('amount_cad', 10, 2);
            $table->decimal('fee_cad', 10, 2)->nullable();
            $table->decimal('rate', 12, 6)->nullable();
            $table->decimal('amount_xaf', 12, 2)->nullable();
            $table->string('recipient_name', 120);
            $table->string('recipient_phone', 30)->index();
            $table->string('external_ref')->nullable()->index();
            $table->string('failure_reason')->nullable();
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
