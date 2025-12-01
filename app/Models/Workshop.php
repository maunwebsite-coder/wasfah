<?php

namespace App\Models;

use App\Support\ImageUploadConstraints;
use App\Support\Currency;
use App\Support\Timezones;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

class Workshop extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'content',
        'instructor',
        'instructor_avatar',
        'instructor_bio',
        'category',
        'level',
        'duration',
        'max_participants',
        'price',
        'currency',
        'image',
        'images',
        'location',
        'address',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'registration_deadline',
        'host_timezone',
        'is_online',
        'meeting_link',
        'meeting_code',
        'meeting_event_id',
        'meeting_calendar_id',
        'meeting_conference_id',
        'recording_url',
        'meeting_provider',
        'meeting_started_at',
        'meeting_started_by',
        'meeting_locked_at',
        'host_first_joined_at',
        'host_join_device_token',
        'host_join_device_fingerprint',
        'host_join_device_ip',
        'host_join_device_user_agent',
        'requirements',
        'what_you_will_learn',
        'materials_needed',
        'is_active',
        'is_featured',
        'views_count',
        'bookings_count',
        'rating',
        'reviews_count',
    ];

    protected $casts = [
        'images' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'meeting_started_at' => 'datetime',
        'meeting_locked_at' => 'datetime',
        'host_first_joined_at' => 'datetime',
        'is_online' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $hidden = [
        'meeting_link_cipher',
    ];

    protected static bool $descriptionFullTextChecked = false;
    protected static bool $descriptionFullTextExists = false;

    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->convertDateToHostTimezone($value)
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->convertDateToHostTimezone($value)
        );
    }

    protected function registrationDeadline(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->convertDateToHostTimezone($value)
        );
    }

    public function getStartDateUtcAttribute(): ?Carbon
    {
        return $this->utcDateFromRaw('start_date');
    }

    public function getEndDateUtcAttribute(): ?Carbon
    {
        return $this->utcDateFromRaw('end_date');
    }

    public function getRegistrationDeadlineUtcAttribute(): ?Carbon
    {
        return $this->utcDateFromRaw('registration_deadline');
    }

    protected function convertDateToHostTimezone(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof Carbon) {
                $date = $value->copy();
            } elseif ($value instanceof \DateTimeInterface) {
                $date = Carbon::instance($value);
            } elseif (is_numeric($value)) {
                $date = Carbon::createFromTimestamp((int) $value, 'UTC');
            } else {
                $date = $this->asDateTime($value);
            }
        } catch (\Throwable $exception) {
            Log::warning('Invalid workshop datetime encountered.', [
                'workshop_id' => $this->getKey(),
                'raw_value' => $value,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        $timezone = $this->resolveHostTimezone();

        try {
            return $date->clone()->setTimezone($timezone);
        } catch (\Throwable $exception) {
            return $date;
        }
    }

    protected function resolveHostTimezone(): string
    {
        $timezone = $this->getAttributeFromArray('host_timezone')
            ?? $this->attributes['host_timezone'] ?? $this->host_timezone ?? null;

        if (! Timezones::isValid($timezone)) {
            $timezone = config('app.timezone', 'UTC');
        }

        return $timezone ?: 'UTC';
    }

    protected function utcDateFromRaw(string $key): ?Carbon
    {
        $raw = $this->getRawOriginal($key);

        if (! $raw) {
            return null;
        }

        $date = $this->asDateTime($raw);

        return $date->clone()->setTimezone('UTC');
    }

    protected function meetingLink(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $cipher = $attributes['meeting_link_cipher'] ?? null;

                if (is_string($cipher) && $cipher !== '') {
                    try {
                        return Crypt::decryptString($cipher);
                    } catch (\Throwable $exception) {
                        Log::warning('Failed to decrypt workshop meeting link.', [
                            'workshop_id' => $attributes['id'] ?? null,
                            'exception_message' => $exception->getMessage(),
                        ]);
                    }
                }

                if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }

                return null;
            },
            set: function ($value) {
                if (blank($value)) {
                    return [
                        'meeting_link' => null,
                        'meeting_link_cipher' => null,
                    ];
                }

                $normalized = trim((string) $value);

                if ($normalized === '') {
                    return [
                        'meeting_link' => null,
                        'meeting_link_cipher' => null,
                    ];
                }

                try {
                    $encrypted = Crypt::encryptString($normalized);
                } catch (\Throwable $exception) {
                    Log::error('Failed to encrypt workshop meeting link.', [
                        'exception_message' => $exception->getMessage(),
                    ]);

                    return [
                        'meeting_link' => $normalized,
                        'meeting_link_cipher' => null,
                    ];
                }

                $hashKey = config('app.key', 'wasfah-workshop');
                $hashedIndicator = hash_hmac('sha256', $normalized, (string) $hashKey);

                return [
                    'meeting_link' => $hashedIndicator,
                    'meeting_link_cipher' => $encrypted,
                ];
            }
        );
    }

    // العلاقات
    public function bookings()
    {
        return $this->hasMany(WorkshopBooking::class);
    }

    public function confirmedBookings()
    {
        return $this->bookings()->where('status', 'confirmed');
    }

    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'workshop_user')
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

    public function participantsWithRecordingAccess()
    {
        return $this->participants()->wherePivot('has_recording_access', true);
    }

    public function reviews()
    {
        return $this->hasMany(WorkshopReview::class);
    }

    public function views()
    {
        return $this->hasMany(WorkshopView::class);
    }

    /**
     * الشيف المسؤول عن الورشة (إن وجد).
     */
    public function chef()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retrieve the Google account email for the workshop host when available.
     */
    public function hostGoogleEmail(): ?string
    {
        $columns = User::columnsForHostContext();

        $this->loadMissing([
            'chef' => fn ($query) => $query->select($columns),
        ]);

        return $this->chef?->preferredGoogleEmail();
    }

    /**
     * Build an attendee payload for the workshop host.
     *
     * @return array{email:string,displayName?:string}|null
     */
    public function hostAttendeePayload(): ?array
    {
        $email = $this->hostGoogleEmail();

        if (!$email) {
            return null;
        }

        $displayName = optional($this->chef)->name ?: ($this->instructor ?: 'Wasfah Chef');

        return [
            'email' => $email,
            'displayName' => $displayName,
        ];
    }

    /**
     * Resolve the calendar identifier for the workshop host.
     */
    public function hostCalendarId(): ?string
    {
        return $this->meeting_calendar_id ?: $this->hostGoogleEmail();
    }

    /**
     * Build Google Meet credentials for the workshop host when available.
     */
    public function hostGoogleMeetCredentials(): ?array
    {
        $this->loadMissing('chef');

        return $this->chef?->googleMeetCredentials($this->hostCalendarId());
    }

    public function meetingStarter()
    {
        return $this->belongsTo(User::class, 'meeting_started_by');
    }

    // العلاقة مع الوصفات (many-to-many)
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'workshop_recipes', 'workshop_id', 'recipe_id')
                    ->withPivot('order')
                    ->orderBy('workshop_recipes.order');
    }

    public function revenueShares()
    {
        return $this->hasManyThrough(
            BookingRevenueShare::class,
            WorkshopBooking::class,
            'workshop_id',
            'workshop_booking_id'
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        $currencyCode = $this->currency ?: 'USD';

        return number_format($this->price, 2) . ' ' . $currencyCode;
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return $hours . ' ساعة و ' . $minutes . ' دقيقة';
        } elseif ($hours > 0) {
            return $hours . ' ساعة';
        } else {
            return $minutes . ' دقيقة';
        }
    }

    public function getIsUpcomingAttribute()
    {
        return $this->start_date > now();
    }

    public function getIsRegistrationOpenAttribute()
    {
        if (!$this->registration_deadline) {
            return $this->is_upcoming;
        }
        
        return $this->is_upcoming && now() < $this->registration_deadline;
    }

    public function getAvailableSpotsAttribute()
    {
        return $this->max_participants - $this->bookings_count;
    }

    public function getIsFullyBookedAttribute()
    {
        return $this->available_spots <= 0;
    }

    public function getIsCompletedAttribute()
    {
        return $this->end_date && $this->end_date < now();
    }

    public function reviewUnlockAt(): ?Carbon
    {
        if (! $this->start_date) {
            return null;
        }

        return $this->start_date->copy()->addMinutes(15);
    }

    public function reviewWindowOpen(): bool
    {
        if ($this->is_completed) {
            return true;
        }

        $unlockAt = $this->reviewUnlockAt();

        return $unlockAt !== null && now()->greaterThanOrEqualTo($unlockAt);
    }

    public function hasEligibleBookingForReview(int $userId): bool
    {
        return $this->bookings()
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->whereIn('status', ['confirmed', 'completed'])
                    ->orWhere('payment_status', 'paid');
            })
            ->exists();
    }

    public function canBeReviewedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        if (! $this->hasEligibleBookingForReview($userId)) {
            return false;
        }

        return $this->reviewWindowOpen();
    }

    /**
     * الحصول على عدد المشاهدات الفريدة (من IPs مختلفة)
     */
    public function getUniqueViewsCountAttribute()
    {
        return $this->views()->count();
    }

    /**
     * Validation rules للورشات
     */
    public static function validationRules($workshopId = null)
    {
        $currencyCodes = Currency::codes();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructor' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'duration' => 'required|integer|min:30',
            'max_participants' => 'required|integer|min:1|max:1000',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|in:' . implode(',', $currencyCodes),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => ['required_unless:is_online,1', 'nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_online' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        'host_timezone' => [
            'nullable',
            'string',
            Rule::in(array_keys(Timezones::hostOptions())),
        ],
            'meeting_link' => [
                'nullable',
                'url',
                'max:255',
                Rule::requiredIf(function () {
                    $request = request();
                    return $request->boolean('is_online') && !$request->boolean('auto_generate_meeting');
                }),
            ],
            'recording_url' => ['nullable', 'url', 'max:512'],
            'meeting_provider' => ['nullable', 'string', 'max:50'],
            'image' => array_merge(['nullable'], ImageUploadConstraints::rules()),
        ];

        // إضافة validation خاص للورشة المميزة
        if ($workshopId) {
            $rules['is_featured'] = [
                'boolean',
                function ($attribute, $value, $fail) use ($workshopId) {
                    if ($value === true) {
                        $existingFeatured = static::where('is_featured', true)
                            ->where('id', '!=', $workshopId)
                            ->exists();
                        
                        if ($existingFeatured) {
                            $fail('يمكن أن تكون ورشة واحدة فقط مميزة في نفس الوقت.');
                        }
                    }
                },
            ];
        } else {
            $rules['is_featured'] = [
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($value === true) {
                        $existingFeatured = static::where('is_featured', true)->exists();
                        
                        if ($existingFeatured) {
                            $fail('يمكن أن تكون ورشة واحدة فقط مميزة في نفس الوقت.');
                        }
                    }
                },
            ];
        }

        return $rules;
    }

    /**
     * جعل هذه الورشة مميزة (مع إلغاء تمييز الورشات الأخرى)
     */
    public function makeFeatured()
    {
        // إلغاء تمييز جميع الورشات الأخرى
        static::where('is_featured', true)->update(['is_featured' => false]);
        
        // جعل هذه الورشة مميزة
        $this->is_featured = true;
        $this->save();
        
        return $this;
    }

    /**
     * إلغاء تمييز هذه الورشة
     */
    public function removeFeatured()
    {
        $this->is_featured = false;
        $this->save();
        
        return $this;
    }

    /**
     * التحقق من وجود ورشة مميزة أخرى
     */
    public function hasOtherFeaturedWorkshop()
    {
        return static::where('is_featured', true)
            ->where('id', '!=', $this->id)
            ->exists();
    }

    /**
     * Generate slug from title
     */
    public function generateSlug()
    {
        $title = (string) $this->title;
        $slug = \Str::slug($title, '-', 'ar');

        if ($slug === '' || $slug === null) {
            $asciiTitle = \Str::ascii($title) ?: 'workshop';
            $slug = \Str::slug($asciiTitle);
        }

        if ($slug === '' || $slug === null) {
            $slug = 'workshop-'.\Str::lower(\Str::random(8));
        }
        
        $originalSlug = $slug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($this->id, fn ($query) => $query->where('id', '!=', $this->id))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($workshop) {
            if (empty($workshop->slug)) {
                $workshop->slug = $workshop->generateSlug();
            }
        });
        
        static::updating(function ($workshop) {
            if ($workshop->isDirty('title') && empty($workshop->slug)) {
                $workshop->slug = $workshop->generateSlug();
            }
        });

        static::saving(function (self $workshop) {
            if (blank($workshop->slug) && filled($workshop->title)) {
                $workshop->slug = $workshop->generateSlug();
            }

            $workshop->meeting_code = static::extractMeetingCode($workshop->meeting_link);
        });
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Return a slug for URLs but gracefully fall back to the numeric ID when the slug is missing.
     */
    public function getRouteKey()
    {
        $keyName = $this->getRouteKeyName();
        $routeKey = $this->getAttribute($keyName);

        return filled($routeKey) ? $routeKey : $this->getKey();
    }

    /**
     * Allow resolving a workshop by slug primarily, but fall back to the ID when needed.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();
        $query = $this->newQuery();

        if ($field === 'slug') {
            return $query
                ->where(function ($query) use ($value) {
                    $query->where('slug', $value);

                    if (is_numeric($value)) {
                        $query->orWhere('id', $value);
                    }
                })
                ->first();
        }

        return $query->where($field, $value)->first();
    }

    public static function extractMeetingCode(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $normalized = strtolower($trimmed);

        if (static::isLikelyMeetingCode($normalized)) {
            return $normalized;
        }

        if (! str_starts_with($trimmed, 'http://') && ! str_starts_with($trimmed, 'https://')) {
            return null;
        }

        $host = strtolower(parse_url($trimmed, PHP_URL_HOST) ?? '');

        if ($host !== '' && ! str_contains($host, 'meet.google.com')) {
            return null;
        }

        $codeFromPath = static::extractMeetingCodeFromPath(parse_url($trimmed, PHP_URL_PATH));

        if ($codeFromPath) {
            return $codeFromPath;
        }

        $query = parse_url($trimmed, PHP_URL_QUERY);

        if ($query) {
            parse_str($query, $params);

            foreach (['meeting_code', 'meetingcode', 'meetingid', 'meetingId', 'code'] as $key) {
                if (! empty($params[$key]) && is_string($params[$key])) {
                    $candidate = strtolower(trim($params[$key]));

                    if (static::isLikelyMeetingCode($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    protected static function extractMeetingCodeFromPath(null|string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        foreach ($segments as $index => $segment) {
            $normalized = strtolower($segment);

            if (static::isLikelyMeetingCode($normalized)) {
                return $normalized;
            }

            if ($normalized === 'lookup' && isset($segments[$index + 1])) {
                $candidate = strtolower($segments[$index + 1]);

                return $candidate !== '' ? $candidate : null;
            }

            if ($normalized === 'v' && isset($segments[$index + 1])) {
                $candidate = strtolower($segments[$index + 1]);

                if (static::isLikelyMeetingCode($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    protected static function isLikelyMeetingCode(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9]{3}-[a-z0-9]{4}-[a-z0-9]{3}$/', $value);
    }

    /**
     * Determine if the workshops table has the description full-text index.
     */
    public static function hasDescriptionFullTextIndex(): bool
    {
        if (! static::$descriptionFullTextChecked) {
            static::$descriptionFullTextExists = static::fullTextIndexExists('workshops_description_fulltext');
            static::$descriptionFullTextChecked = true;
        }

        return static::$descriptionFullTextExists;
    }

    /**
     * Mark the description full-text index as unavailable for the current request.
     */
    public static function markDescriptionFullTextUnavailable(): void
    {
        static::$descriptionFullTextChecked = true;
        static::$descriptionFullTextExists = false;
    }

    /**
     * Inspect information_schema to check for a full-text index.
     */
    protected static function fullTextIndexExists(string $indexName): bool
    {
        try {
            $instance = new static();
            $connection = $instance->getConnection();
            $database = $connection->getDatabaseName();
            $table = $connection->getTablePrefix().$instance->getTable();

            $count = $connection->table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->where('index_type', 'FULLTEXT')
                ->count();

            return $count > 0;
        } catch (\Throwable $exception) {
            Log::warning('Failed to detect workshops full-text index.', [
                'index' => $indexName,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    #[SearchUsingFullText(['description'])]
    #[SearchUsingPrefix(['slug'])]
    public function toSearchableArray(): array
    {
        return [
            'title' => (string) $this->title,
            'slug' => (string) $this->slug,
            'description' => (string) $this->description,
            'instructor' => (string) $this->instructor,
            'category' => (string) $this->category,
            'location' => (string) $this->location,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }
}
