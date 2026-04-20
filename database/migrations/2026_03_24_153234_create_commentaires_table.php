<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commentaires', function (Blueprint $table) {
            $table->id();
            $table->string('post_id');
            $table->string('id_commentaire')->unique();
            $table->text('message_commentaire')->nullable();
            $table->string('nom_auteur')->nullable();
            $table->string('temps_creer')->nullable();
            $table->timestamps();

            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commentaires');
    }
};
