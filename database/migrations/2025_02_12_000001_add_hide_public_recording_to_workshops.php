<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('workshops', 'hide_public_recording')) {
            return;
        }

        Schema::table('workshops', function (Blueprint $table) {
            $table->boolean('hide_public_recording')
                ->default(false)
                ->after('recording_url');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('workshops', 'hide_public_recording')) {
            return;
        }

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('hide_public_recording');
        });
    }
};
