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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_drive_email')) {
                $table->string('google_drive_email')->nullable()->after('google_calendar_scopes');
            }

            if (! Schema::hasColumn('users', 'google_access_token')) {
                $table->text('google_access_token')->nullable()->after('google_drive_email');
            }

            if (! Schema::hasColumn('users', 'google_refresh_token')) {
                $table->text('google_refresh_token')->nullable()->after('google_access_token');
            }

            if (! Schema::hasColumn('users', 'google_expires_at')) {
                $table->dateTime('google_expires_at')->nullable()->after('google_refresh_token');
            }

            if (! Schema::hasColumn('users', 'google_drive_scopes')) {
                $table->text('google_drive_scopes')->nullable()->after('google_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_drive_scopes')) {
                $table->dropColumn('google_drive_scopes');
            }

            if (Schema::hasColumn('users', 'google_expires_at')) {
                $table->dropColumn('google_expires_at');
            }

            if (Schema::hasColumn('users', 'google_refresh_token')) {
                $table->dropColumn('google_refresh_token');
            }

            if (Schema::hasColumn('users', 'google_access_token')) {
                $table->dropColumn('google_access_token');
            }

            if (Schema::hasColumn('users', 'google_drive_email')) {
                $table->dropColumn('google_drive_email');
            }
        });
    }
};
