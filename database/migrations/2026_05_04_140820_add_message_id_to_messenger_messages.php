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
        Schema::table('messenger_messages', function (Blueprint $table) {
            $table->string('fb_message_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messenger_messages', function (Blueprint $table) {
            $table->dropUnique(['fb_message_id']);
            $table->dropColumn('fb_message_id');
        });
    }
};
