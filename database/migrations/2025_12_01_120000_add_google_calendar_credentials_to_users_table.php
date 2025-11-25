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
            $table->string('google_calendar_email')->nullable()->after('google_email');
            $table->string('google_calendar_id')->nullable()->after('google_calendar_email');
            $table->text('google_calendar_access_token')->nullable()->after('provider_token');
            $table->text('google_calendar_refresh_token')->nullable()->after('google_calendar_access_token');
            $table->timestamp('google_calendar_token_expires_at')->nullable()->after('google_calendar_refresh_token');
            $table->text('google_calendar_scopes')->nullable()->after('google_calendar_token_expires_at');

            $table->index('google_calendar_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_calendar_email']);

            $table->dropColumn([
                'google_calendar_email',
                'google_calendar_id',
                'google_calendar_access_token',
                'google_calendar_refresh_token',
                'google_calendar_token_expires_at',
                'google_calendar_scopes',
            ]);
        });
    }
};
