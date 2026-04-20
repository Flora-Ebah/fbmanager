<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messenger_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id')->unique();
            $table->string('nom_expediteur')->nullable();
            $table->text('dernier_message')->nullable();
            $table->string('temps_dernier_message')->nullable();
            $table->integer('nombre_messages')->default(0);
            $table->enum('statut', ['lu', 'non_lu'])->default('non_lu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messenger_conversations');
    }
};
