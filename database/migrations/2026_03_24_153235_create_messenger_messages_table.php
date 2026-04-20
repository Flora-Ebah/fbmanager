<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messenger_messages', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id');
            $table->string('nom_expediteur')->nullable();
            $table->text('message');
            $table->string('temps_envoi')->nullable();
            $table->string('type_message', 50)->default('text');
            $table->enum('statut', ['lu', 'non_lu'])->default('non_lu');
            $table->timestamps();

            $table->foreign('conversation_id')->references('conversation_id')->on('messenger_conversations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messenger_messages');
    }
};
