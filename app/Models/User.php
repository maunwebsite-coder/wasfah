<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Recipe;
use App\Models\ChefLinkPage;
use App\Models\Recording;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens; // ✅ تم إضافة هذا السطر

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens; // ✅ تم إضافة HasApiTokens هنا

    protected static bool $googleEmailColumnChecked = false;
    protected static bool $googleEmailColumnExists = false;
    protected static bool $googleCalendarColumnChecked = false;
    protected static bool $googleCalendarColumnExists = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_email',
        'google_calendar_email',
        'google_calendar_id',
        'google_calendar_access_token',
        'google_calendar_refresh_token',
        'google_calendar_token_expires_at',
        'google_calendar_scopes',
        'google_drive_email',
        'google_access_token',
        'google_refresh_token',
        'google_expires_at',
        'google_drive_scopes',
        'google_id_token',
        'password',
        'phone',
        'timezone',
        'country_code',
        'phone_country_code',
        'provider',
        'provider_id',
        'provider_token',
        'avatar',
        'is_admin',
        'role',
        'chef_status',
        'chef_approved_at',
        'instagram_url',
        'instagram_followers',
        'youtube_url',
        'youtube_followers',
        'chef_specialty_area',
        'chef_specialty_description',
        'last_login_at',
        'last_login_ip',
        'remember_me',
        'remember_me_expires_at',
        'is_referral_partner',
        'referral_code',
        'referrer_id',
        'referral_commission_rate',
        'referral_commission_currency',
        'referral_partner_since_at',
        'referral_admin_notes',
        'referral_skip_platform_fee',
        'policies_accepted_at',
        'policies_accepted_ip',
        'policies_version',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'google_calendar_access_token',
        'google_calendar_refresh_token',
        'google_access_token',
        'google_refresh_token',
        'google_id_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'remember_me' => 'boolean',
            'remember_me_expires_at' => 'datetime',
            'instagram_followers' => 'integer',
            'youtube_followers' => 'integer',
            'chef_approved_at' => 'datetime',
            'is_referral_partner' => 'boolean',
            'referral_partner_since_at' => 'datetime',
            'referral_commission_rate' => 'decimal:2',
            'referral_skip_platform_fee' => 'boolean',
            'policies_accepted_at' => 'datetime',
            'google_calendar_token_expires_at' => 'datetime',
            'google_expires_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the users table actually has the google_email column.
     */
    public static function hasGoogleEmailColumn(): bool
    {
        if (! static::$googleEmailColumnChecked) {
            try {
                $model = new static();
                static::$googleEmailColumnExists = Schema::connection($model->getConnectionName())
                    ->hasColumn($model->getTable(), 'google_email');
            } catch (\Throwable $exception) {
                static::$googleEmailColumnExists = false;
            }

            static::$googleEmailColumnChecked = true;
        }

        return static::$googleEmailColumnExists;
    }

    /**
     * Determine whether the users table has the google_calendar_email column.
     */
    public static function hasGoogleCalendarColumn(): bool
    {
        if (! static::$googleCalendarColumnChecked) {
            try {
                $model = new static();
                static::$googleCalendarColumnExists = Schema::connection($model->getConnectionName())
                    ->hasColumn($model->getTable(), 'google_calendar_email');
            } catch (\Throwable $exception) {
                static::$googleCalendarColumnExists = false;
            }

            static::$googleCalendarColumnChecked = true;
        }

        return static::$googleCalendarColumnExists;
    }

    /**
     * Columns needed when eager loading a chef for hosting flows.
     *
     * @return array<int, string>
     */
    public static function columnsForHostContext(): array
    {
        $columns = ['id', 'name', 'email'];

        if (static::hasGoogleCalendarColumn()) {
            $columns[] = 'google_calendar_email';
        }

        if (static::hasGoogleEmailColumn()) {
            $columns[] = 'google_email';
        }

        return $columns;
    }

    public function requiresPolicyConsent(): bool
    {
        return $this->provider === 'google'
            && is_null($this->policies_accepted_at);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (blank($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });

        static::saving(function (User $user) {
            if ($user->is_referral_partner && blank($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }

            if ($user->isDirty('is_referral_partner') && $user->is_referral_partner && blank($user->referral_partner_since_at)) {
                $user->referral_partner_since_at = now();
            }
        });
    }

    public static function generateUniqueReferralCode(int $length = 10): string
    {
        $length = max(6, $length);

        do {
            $code = Str::upper(Str::random($length));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function ensureReferralCode(bool $persist = true): string
    {
        if (filled($this->referral_code)) {
            return $this->referral_code;
        }

        $this->referral_code = static::generateUniqueReferralCode();

        if ($persist && $this->exists) {
            $this->saveQuietly();
        }

        return $this->referral_code;
    }

    /**
     * Constant roles.
     */
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_CHEF = 'chef';
    public const ROLE_ADMIN = 'admin';

    /**
     * Chef status constants.
     */
    public const CHEF_STATUS_NEEDS_PROFILE = 'needs_profile';
    public const CHEF_STATUS_PENDING = 'pending';
    public const CHEF_STATUS_APPROVED = 'approved';
    public const CHEF_STATUS_REJECTED = 'rejected';

    /**
     * Determine if the user is a chef.
     */
    public function isChef(): bool
    {
        return $this->role === self::ROLE_CHEF && $this->chef_status === self::CHEF_STATUS_APPROVED;
    }

    /**
     * Determine if user needs to complete onboarding.
     */
    public function needsChefProfile(): bool
    {
        return $this->role === self::ROLE_CHEF
            && $this->chef_status !== self::CHEF_STATUS_APPROVED;
    }

    /**
     * Determine whether all chef profile requirements are completed.
     */
    public function hasCompletedChefProfile(): bool
    {
        $hasPhone = filled($this->phone) && filled($this->phone_country_code) && filled($this->country_code);

        $hasGoogleEmail = filled($this->google_email)
            && filter_var($this->google_email, FILTER_VALIDATE_EMAIL);

        return $hasPhone && $hasGoogleEmail;
    }

    /**
     * Retrieve the preferred Google account email for Meet.
     */
    public function preferredGoogleEmail(): ?string
    {
        $email = $this->google_calendar_email
            ?: $this->google_email
            ?: $this->email;

        if (!is_string($email)) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }

    /**
     * Determine if the user has connected Google Drive.
     */
    public function hasGoogleDriveCredentials(): bool
    {
        return (bool) ($this->google_refresh_token ?? $this->google_calendar_refresh_token ?? $this->google_access_token);
    }

    /**
     * Retrieve the preferred email associated with Google Drive.
     */
    public function preferredGoogleDriveEmail(): ?string
    {
        $email = $this->google_drive_email
            ?: $this->google_email
            ?: $this->email;

        if (!is_string($email)) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }

    /**
     * Determine if the user has connected a Google Calendar account with a refresh token.
     */
    public function hasGoogleCalendarCredentials(): bool
    {
        $refreshToken = $this->google_calendar_refresh_token ?: $this->google_refresh_token;

        return !empty($refreshToken)
            && (bool) ($this->google_calendar_email ?? $this->google_calendar_id ?? $this->google_email ?? $this->email);
    }

    /**
     * Build a credentials array for using the Google Meet service on behalf of this user.
     */
    public function googleMeetCredentials(?string $calendarId = null): ?array
    {
        if (! $this->hasGoogleCalendarCredentials()) {
            return null;
        }

        $resolvedCalendarId = $calendarId
            ?: $this->google_calendar_id
            ?: $this->google_calendar_email
            ?: $this->preferredGoogleEmail();

        $organizerEmail = $this->google_calendar_email
            ?: $this->google_email
            ?: $this->email;

        if (!$resolvedCalendarId || !$organizerEmail) {
            return null;
        }

        // Use the same client ID/Secret that Socialite uses for the user flow
        $clientId = config('services.google.client_id') ?? config('services.google_meet.client_id');
        $clientSecret = config('services.google.client_secret') ?? config('services.google_meet.client_secret');
        $refreshToken = $this->google_calendar_refresh_token ?: $this->google_refresh_token;

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'calendar_id' => $resolvedCalendarId,
            'organizer_email' => $organizerEmail,
            'timezone' => $this->timezone ?: config('services.google_meet.timezone'),
            'default_duration' => config('services.google_meet.default_duration'),
        ];
    }

    /**
     * Disconnect Google Calendar by clearing stored credentials.
     */
    public function disconnectGoogleCalendar(): void
    {
        $this->forceFill([
            'google_calendar_access_token' => null,
            'google_calendar_refresh_token' => null,
            'google_calendar_token_expires_at' => null,
            'google_calendar_id' => null,
            'google_calendar_email' => null,
        ])->save();
    }

    /**
     * Normalize stored Google email addresses.
     */
    protected function googleEmail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: function ($value) {
                if (!is_string($value)) {
                    return null;
                }

                $email = strtolower(trim($value));

                if ($email === '') {
                    return null;
                }

                return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
            }
        );
    }

    /**
     * Determine if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine if the user can share referral links.
     */
    public function isReferralPartner(): bool
    {
        return (bool) $this->is_referral_partner;
    }

    /**
     * Determine if the user can access record/booking management pages.
     */
    public function canAccessRecordManagement(): bool
    {
        return $this->isChef() || $this->isAdmin();
    }

    /**
     * Determine if platform fees should be waived for this chef's own workshops.
     */
    public function shouldWaivePlatformFeeForOwnWorkshops(): bool
    {
        return $this->role === self::ROLE_CHEF
            && $this->isReferralPartner()
            && (bool) $this->referral_skip_platform_fee;
    }

    /**
     * علاقة مع الوصفات الخاصة بالشيف.
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'user_id', 'id');
    }

    /**
     * صفحة روابط Peahskill الخاصة بالشيف.
     */
    public function linkPage()
    {
        return $this->hasOne(ChefLinkPage::class);
    }

    /**
     * تسجيلات الفيديو الخاصة بالمستخدم.
     */
    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    /**
     * التأكد من إنشاء صفحة الروابط وتعبئتها بقيم افتراضية عند الحاجة.
     */
    public function ensureLinkPage(): ChefLinkPage
    {
        $existing = $this->linkPage()->with('items')->first();

        if ($existing) {
            return $existing;
        }

        $brandName = config('app.name', 'Peahskill');

        $page = $this->linkPage()->create([
            'headline' => 'روابط ' . $brandName . ' الخاصة بـ ' . ($this->name ?? 'الشيف'),
            'subheadline' => 'كل الروابط الهامة في مكان واحد.',
            'bio' => null,
            'cta_label' => 'تصفح وصفاتي',
            'cta_url' => route('chefs.show', ['chef' => $this->id]),
            'accent_color' => '#0819ff',
        ]);

        $defaultLinks = collect([
            [
                'title' => 'صفحتي على ' . $brandName,
                'subtitle' => 'اكتشف كل وصفاتي المنشورة',
                'url' => route('chefs.show', ['chef' => $this->id]),
                'icon' => 'fas fa-utensils',
            ],
            $this->instagram_url ? [
                'title' => 'حسابي على إنستغرام',
                'subtitle' => null,
                'url' => $this->instagram_url,
                'icon' => 'fab fa-instagram',
            ] : null,
            $this->youtube_url ? [
                'title' => 'قناتي على يوتيوب',
                'subtitle' => null,
                'url' => $this->youtube_url,
                'icon' => 'fab fa-youtube',
            ] : null,
        ])->filter();

        foreach ($defaultLinks as $index => $link) {
            $page->items()->create(array_merge($link, [
                'position' => $index + 1,
            ]));
        }

        return $page->fresh('items');
    }

    public function getReferralCurrencyCodeAttribute(): string
    {
        return $this->referral_commission_currency ?: (string) config('referrals.default_currency', 'USD');
    }

    public function getReferralCurrencySymbolAttribute(): string
    {
        $currency = $this->referral_currency_code;

        return data_get(config('referrals.currencies', []), "{$currency}.symbol", $currency);
    }

    public function getReferralCurrencyLabelAttribute(): string
    {
        $currency = $this->referral_currency_code;

        return data_get(config('referrals.currencies', []), "{$currency}.label", $currency);
    }

    /**
     * الرابط القابل للمشاركة لطلبات الدعوة.
     */
    public function getReferralLinkAttribute(): ?string
    {
        if (!$this->isReferralPartner()) {
            return null;
        }

        $code = $this->ensureReferralCode();

        return route('register', ['ref' => $code]);
    }

    /**
     * العلاقات
     */
    
    // المستخدم الذي قام بدعوة هذا الحساب
    public function referralPartner()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    // المستخدمون الذين تم تسجيلهم عبر هذا الحساب
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    // العمولات المكتسبة كشريك إحالة
    public function referralCommissions()
    {
        return $this->hasMany(ReferralCommission::class, 'referral_partner_id');
    }

    // العمولات الناتجة عن نشاط هذا المستخدم (كمُحال)
    public function generatedReferralCommissions()
    {
        return $this->hasMany(ReferralCommission::class, 'referred_user_id');
    }
    
    // علاقة مع حجوزات الورشات
    public function workshopBookings()
    {
        return $this->hasMany(WorkshopBooking::class);
    }

    public function bookingRevenueShares()
    {
        return $this->hasMany(BookingRevenueShare::class, 'recipient_id');
    }

    // علاقة مع تقييمات الورشات
    public function workshopReviews()
    {
        return $this->hasMany(WorkshopReview::class);
    }

    // علاقة مع التفاعلات مع الوصفات
    public function interactions()
    {
        return $this->hasMany(UserInteraction::class);
    }

    /**
     * المستخدمون الذين يتابعون هذا الشيف.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'chef_followers',
            'chef_id',
            'follower_id'
        )->withTimestamps();
    }

    /**
     * الشيفات الذين يتابعهم هذا المستخدم.
     */
    public function followingChefs(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'chef_followers',
            'follower_id',
            'chef_id'
        )->withTimestamps();
    }

    /**
     * تحقّق ما إذا كان المستخدم يتابع شيفاً محدداً.
     */
    public function isFollowingChef(User $chef): bool
    {
        if (! $this->exists || $this->id === $chef->id) {
            return false;
        }

        if ($this->relationLoaded('followingChefs')) {
            return $this->followingChefs->contains('id', $chef->id);
        }

        return $this->followingChefs()
            ->where('chef_id', $chef->id)
            ->exists();
    }

    /**
     * الورش التي ينشئها الشيف.
     */
    public function workshops()
    {
        return $this->hasMany(Workshop::class);
    }

    /**
     * علاقات حضور الورشات (pivot) لتتبع الوصول إلى التسجيلات.
     */
    public function workshopsAttended(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'workshop_user')
            ->withPivot([
                'status',
                'attendance_status',
                'has_recording_access',
                'attended_at',
                'recording_unlocked_at',
                'notes',
            ])
            ->withTimestamps();
    }

    /**
     * Retrieve the next upcoming (or most recent active) workshop for this chef.
     */
    public function nextUpcomingWorkshop(): ?Workshop
    {
        $baseQuery = $this->workshops()
            ->active()
            ->withCount('bookings');

        $upcoming = (clone $baseQuery)
            ->whereNotNull('start_date')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->first();

        if ($upcoming) {
            return $upcoming;
        }

        return (clone $baseQuery)
            ->orderBy('start_date')
            ->orderByDesc('created_at')
            ->first();
    }

    // الورشات المحجوزة والمؤكدة
    public function confirmedWorkshops()
    {
        return $this->belongsToMany(Workshop::class, 'workshop_bookings')
                    ->wherePivot('status', 'confirmed');
    }

    // الإشعارات
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // الإشعارات غير المقروءة
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    // عدد الإشعارات غير المقروءة
    public function unreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }
}

