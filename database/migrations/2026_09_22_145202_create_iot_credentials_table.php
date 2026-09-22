<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iot_credentials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipement_id')
                ->constrained('equipements')
                ->cascadeOnDelete();

            $table->string('device_id')->unique();

            $table->string('api_key_hash');

            $table->boolean('actif')->default(true);

            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_credentials');
    }
};