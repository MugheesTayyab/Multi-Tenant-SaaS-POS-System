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
    Schema::create('shops', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('owner_name');
        $table->string('email')->unique();
        $table->string('phone');
        $table->text('address');
        // Foreign Key link back to the subscription tiers
        $table->foreignId('subscription_id')->constrained()->onDelete('restrict');
        $table->string('status')->default('pending'); // pending, approved, rejected, suspended
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
