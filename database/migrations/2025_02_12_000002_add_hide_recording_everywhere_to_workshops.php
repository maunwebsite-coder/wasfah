<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('workshops', 'hide_recording_everywhere')) {
            return;
        }

        Schema::table('workshops', function (Blueprint $table) {
            $table->boolean('hide_recording_everywhere')
                ->default(false)
                ->after('hide_public_recording');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('workshops', 'hide_recording_everywhere')) {
            return;
        }

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('hide_recording_everywhere');
        });
    }
};
