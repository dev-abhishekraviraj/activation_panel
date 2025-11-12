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
        Schema::create('tbl_playlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mac_id');
            $table->foreign('mac_id')->references('id')->on('tbl_mac_devices')->onDelete('cascade');
            $table->string('playlist_name');
            $table->string('stream_line');
            $table->string('filepath');
            $table->string('epg')->nullable();
            $table->string('type');
            $table->string('epg_countries')->nullable();
            $table->string('logos')->nullable();
            $table->tinyInteger('save_online')->nullable();
            $table->tinyInteger('detect_epg')->nullable();
            $table->tinyInteger('disable_groups')->nullable();
            $table->tinyInteger('is_protected')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
