<?php

return [
    'title' => 'Dashboard',

    'meta' => [
        'title' => ':name profile',
    ],

    'defaults' => [
        'bio' => 'A culinary expert sharing signature recipes with the Peahskill community.',
        'specialty_with_area' => 'Specialized in :area',
        'specialty_generic' => 'Member of the Peahskill community',
    ],

    'formats' => [
        'date_time' => 'j F Y • h:i a',
        'table_date_time' => 'j M Y · h:i a',
        'date' => 'j F Y',
    ],

    'status' => [
        'labels' => [
            'draft' => 'Draft',
            'pending' => 'Pending review',
            'approved' => 'Approved',
            'rejected' => 'Needs updates',
        ],
        'descriptions' => [
            'draft' => 'Keep editing privately before you submit the recipe.',
            'pending' => 'Our editorial team is reviewing the recipe details.',
            'approved' => 'Published on Peahskill and ready to share.',
            'rejected' => 'Review the comments, update the recipe, and resubmit.',
        ],
    ],

    'visibility' => [
        'public' => [
            'label' => 'Public',
            'hint' => 'Visible to everyone on Peahskill.',
        ],
        'private' => [
            'label' => 'Private link',
            'hint' => 'Only guests with the private link can view it.',
        ],
    ],

    'hero' => [
        'badge' => 'Dashboard tools',
        'heading' => 'Welcome, :name',
        'description' => 'Manage your recipes, workshops, and payouts in one dashboard.',
        'avatar_alt' => 'Photo of :name',
        'stats' => [
            'wasfah_followers' => 'Peahskill subscribers',
            'other_platform_followers' => 'Other platform followers',
            'recipes' => 'Published recipes',
            'average_rating' => 'Average rating',
        ],
        'buttons' => [
            'follow' => 'Follow',
            'following' => 'Following',
        ],
        'actions' => [
            'public_profile' => 'View public profile',
            'workshops' => 'Manage workshops',
            'earnings' => 'View earnings',
            'new_recipe' => 'New recipe',
            'new_workshop' => 'Create workshop',
        ],
    ],

    'follow' => [
        'errors' => [
            'self_follow' => 'You cannot follow your own profile.',
            'generic' => 'We couldn’t update the follow status. Please try again.',
        ],
    ],

    'impact' => [
        'badge' => 'Community pulse',
        'title' => 'Impact snapshot',
        'description' => ':name\'s recipes inspire Peahskill members. Here is how the community responds.',
        'highlight_label' => 'Published recipes',
        'highlight_hint' => 'Unique dishes shared on Peahskill',
        'cards' => [
            'saves' => [
                'title' => 'Saved recipes',
                'hint' => 'Times members bookmarked these dishes.',
            ],
            'made' => [
                'title' => 'Tried it',
                'hint' => 'Members who marked the recipe as made.',
            ],
            'reviews' => [
                'title' => 'Ratings & reviews',
                'hint' => 'Total feedback shared by the community.',
            ],
        ],
    ],

    'dashboard' => [
        'workshops' => [
            'meta_title' => 'Online workshops',
            'hero' => [
                'eyebrow' => 'Workshops area',
                'title' => 'My workshops',
                'description' => 'Create online sessions easily and share the Google Meet link once people book.',
            ],
            'ctas' => [
                'new' => 'Add new workshop',
                'earnings' => 'Workshop earnings',
                'recipes' => 'Back to recipes',
            ],
            'device_reset' => [
                'badge' => 'Trusted device verification',
                'title' => 'Reopen workshop room: :title',
                'description' => 'To protect attendees, only one trusted device can open the workshop room. Enter your password to assign this browser.',
                'password_label' => 'Password',
                'password_placeholder' => 'Enter current password',
                'confirm' => 'Confirm reset',
                'retry' => 'Retry after reset',
                'footnote' => 'Your current device becomes the trusted host and other devices are blocked until you reset again.',
                'reasons' => [
                    'missing_cookie' => 'The trusted device profile was not found in this browser. Cookies may have been cleared or a new device is being used.',
                    'cookie_mismatch' => 'The current device code does not match the previously trusted one.',
                    'fingerprint_mismatch' => 'We detected a different device or browser than the trusted one.',
                    'manual_reset_validation_failed' => 'Password verification failed. Please try again so we can confirm it is you.',
                ],
            ],
            'host_room' => [
                'meta_title' => 'Host room – :title',
                'eyebrow' => 'Host room',
                'description' => 'This panel is dedicated to the host to manage the online workshop. Open Google Meet using the button below and attendees will be notified automatically once the session goes live.',
                'stats' => [
                    'schedule' => 'Workshop schedule',
                    'attendance' => 'Confirmed attendees',
                    'status' => 'Status',
                ],
                'no_schedule' => 'Not scheduled',
                'attendance_value' => '{0} :count attendees|{1} :count attendee|[2,*] :count attendees',
                'status_badges' => [
                    'locked' => 'Locked',
                    'live' => 'Session is live',
                    'pending' => 'Waiting to start',
                ],
                'meeting' => [
                    'label' => 'Google Meet link',
                    'open' => 'Open Google Meet now',
                    'refresh' => 'Refresh status',
                    'secure_label' => 'Secure Peahskill link',
                    'secure_help' => 'Opens Google Meet using your verified Google account so you skip waiting rooms.',
                    'secure_missing' => 'Add your Google Meet email from your profile to enable this shortcut.',
                ],
                'participants' => [
                    'eyebrow' => 'Latest confirmed attendees',
                    'title' => 'Ready to join',
                    'summary' => ':current of :total',
                    'fallback_name' => 'Attendee',
                    'fallback_email' => 'Email not provided',
                    'last_update' => 'Last update: :time',
                    'empty' => 'No confirmed attendees yet. Once bookings are confirmed they will appear here.',
                ],
            ],
            'stats' => [
                'total' => 'Total workshops',
                'active' => 'Active workshops',
                'online' => 'Online workshops',
                'drafts' => 'Drafts',
            ],
            'badges' => [
                'delivery' => [
                    'online' => 'Online',
                    'in_person' => 'In person',
                ],
                'status' => [
                    'published' => 'Published',
                    'draft' => 'Draft',
                ],
            ],
            'duration' => '• Lasts :minutes minutes',
            'capacity' => ':current / :max participants',
            'google_card' => [
                'title' => 'Online via Google Meet',
                'description' => 'The link stays hidden for everyone. Only the button below opens your Google Meet room.',
                'launch' => 'Open Google Meet',
                'launched' => 'Room opened :time',
                'not_ready' => 'Participants cannot join until you open the Google Meet room.',
                'pending_link' => 'Generate the meeting link to enable this button.',
            ],
            'actions' => [
                'edit' => 'Edit',
                'delete' => 'Delete',
                'delete_confirm' => 'Are you sure you want to delete this workshop?',
                'view_public' => 'View workshop page',
                'pending_public' => 'The link will appear once the workshop is active.',
            ],
            'empty' => [
                'title' => 'No workshops yet',
            'description' => 'Create your first workshop now and we will generate a Google Meet link instantly.',
                'cta' => 'Create workshop',
            ],
        ],


    ],

    'workshops' => [
        'eyebrow' => 'Workshops hub',
        'title' => 'Workshops',
        'description' => 'Discover the learning experiences led by :name and explore upcoming and past workshops.',
        'view_all' => 'Browse all workshops',
        'upcoming' => 'Upcoming workshops',
        'past' => 'Past workshops',
        'latest_workshop' => 'Latest workshop: :title',
        'placeholder_text' => 'Workshop',
        'tbd_time' => 'To be announced',
        'unscheduled_time' => 'Unscheduled date',
        'location_tbd' => 'Location to be announced',
        'online_live' => 'Live online',
        'delivery' => [
            'online_short' => 'Online',
            'in_person_short' => 'In-person',
        ],
        'levels' => [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
        ],
        'level_label' => 'Level :level',
        'capacity_with_limit' => ':current / :max attendees',
        'capacity_open' => ':count attendees',
        'date_format' => 'j F Y',
        'datetime_format' => 'j F Y • h:i a',
        'badges' => [
            'open' => 'Available to book',
            'closed' => 'Registration closed',
            'completed' => 'Completed',
        ],
        'image_alt' => 'Workshop :title',
        'with_instructor' => 'With :name',
        'delivered_by' => 'Delivered by :name',
        'register_until' => 'Registration open until :date',
        'book_now' => 'Book your seat',
        'registration_closed' => 'Registration closed',
        'view_details' => 'View details',
        'stats' => [
            'total' => [
                'title' => 'Total workshops',
                'hint' => 'All sessions you have published.',
            ],
            'active' => [
                'title' => 'Active sessions',
                'hint' => 'Live now or open for bookings.',
            ],
            'online' => [
                'title' => 'Online workshops',
                'hint' => 'Hosted virtually through Peahskill.',
            ],
            'upcoming' => [
                'title' => 'Upcoming',
                'hint' => 'Confirmed sessions scheduled next.',
            ],
        ],
        'buttons' => [
            'create' => 'Create workshop',
            'manage' => 'Manage workshops',
        ],
        'next' => [
            'heading' => 'Next workshops',
            'limit' => 'Showing up to :count workshops',
            'quick_join' => 'Quick join',
            'fallback_time' => 'Time to be scheduled',
            'open_room' => 'Open live room',
        ],
        'labels' => [
            'online' => 'Online',
            'onsite' => 'Onsite',
        ],
        'host_status' => [
            'live' => 'Live now',
            'online_upcoming' => 'Virtual room ready',
            'onsite' => 'Onsite session',
            'live_since' => 'Live for :time',
        ],
        'sections' => [
            'host' => 'Host status',
            'participants' => 'Participants',
        ],
        'participant_status' => [
            'online_live' => 'Participants live',
            'online_waiting' => 'Participants waiting',
            'onsite' => 'Onsite attendees',
        ],
        'participant_hints' => [
            'online_live' => 'Participants are in the room now; keep the link handy.',
            'online_waiting' => 'Participants can join once you open the room.',
            'onsite' => 'Share arrival instructions and venue updates.',
        ],
        'items' => [
            'participants_count' => '{0} No participants yet|{1} :count participant|[2,*] :count participants',
            'capacity_count' => '{1} :count seat total|[2,*] :count seats total',
            'capacity_unlimited' => 'Unlimited capacity',
            'participant_link' => 'Open meeting link',
            'participant_link_restricted' => 'Link hidden for privacy',
            'location_value' => 'Location: :location',
            'location_missing' => 'Location shared after confirmation.',
            'edit_details' => 'Edit workshop details',
            'no_upcoming' => 'No upcoming workshops yet.',
            'plan_next' => 'Plan the next workshop',
        ],
    ],
    'recordings' => [
        'eyebrow' => 'Workshop library',
        'title' => 'Recorded workshops',
        'description' => 'Catch up on :name\'s completed sessions and watch every recording from here.',
        'view_all' => 'Open recordings page',
        'fallback_drive' => 'Recording available via Google Drive',
        'badges' => [
            'available' => 'Playable now',
            'drive' => 'External recording',
        ],
        'cta' => [
            'watch' => 'Watch the recording',
        ],
        'library_label' => 'Google Drive Library',
        'drive_default_description' => 'A recorded workshop available in the archive.',
        'updated_unknown' => 'Last update unknown',
        'untitled' => 'Untitled recording',
    ],

    'workshops_create' => [
        'page_title' => 'Launch a new workshop',
        'hero_badge' => 'Workshop area',
        'hero_heading' => 'Launch a new online workshop',
        'hero_description' => 'Once you save, we will prepare a shareable Google Meet link for your attendees.',
        'back_to_list' => 'Back to workshops list',
        'validation_heading' => 'Please review the following fields:',
        'draft_notice' => 'The workshop will be saved under your account so you can edit or publish it whenever you are ready.',
        'submit_label' => 'Save workshop',
    ],

    'workshop_form' => [
        'currencies' => [
            'usd' => 'US Dollar',
        ],
        'sections' => [
            'basics' => [
                'eyebrow' => 'Basic information',
                'title' => 'Workshop details',
                'description' => 'Introduce participants to the workshop topic and why it stands out.',
            ],
            'pricing' => [
                'eyebrow' => 'Pricing & capacity',
                'title' => 'Financial settings',
                'description' => 'Let participants know how many seats are available to encourage early registrations.',
            ],
            'schedule' => [
                'eyebrow' => 'Scheduling',
                'title' => 'Dates & times',
                'description' => 'These dates appear to learners as soon as the workshop is published.',
            ],
            'delivery' => [
                'eyebrow' => 'Delivery method',
                'title' => 'Online via Google Meet',
                'description' => 'We can automatically generate a secured Google Meet link for you.',
                'highlight' => 'We create the Google Meet room directly from your Google account so you stay the host.',
                'host_account_label' => 'Host account',
                'host_account_missing' => 'Add your Google account to host the meeting from your calendar.',
            ],
            'host' => [
                'eyebrow' => 'Your info',
                'title' => 'Introduce the instructor',
            ],
            'image' => [
                'eyebrow' => 'Workshop image',
                'title' => 'Visual impact',
                'description' => 'Upload sharp imagery (up to 5 MB) to encourage bookings.',
                'preview_placeholder' => 'The preview will appear here after you upload an image.',
                'preview_alt' => 'Workshop cover preview',
            ],
            'publish' => [
                'eyebrow' => 'Publish workshop',
                'title' => 'Ready to go live?',
                'auto_activate' => 'Activate the workshop immediately after saving',
            ],
        ],
        'fields' => [
            'title' => [
                'label' => 'Workshop title *',
            ],
            'category' => [
                'label' => 'Workshop category *',
            ],
            'level' => [
                'label' => 'Level *',
            ],
            'duration' => [
                'label' => 'Duration (minutes) *',
            ],
            'description' => [
                'label' => 'Inspiring description *',
            ],
            'content' => [
                'label' => 'Content outline (optional)',
            ],
            'learning_points' => [
                'label' => 'What will participants learn?',
            ],
            'requirements' => [
                'label' => 'Prerequisites',
            ],
            'price' => [
                'label' => 'Workshop price *',
            ],
            'currency' => [
                'label' => 'Currency *',
            ],
            'max_participants' => [
                'label' => 'Maximum participants *',
            ],
            'start_date' => [
                'label' => 'Start date *',
            ],
            'end_date' => [
                'label' => 'End date *',
            ],
            'registration_deadline' => [
                'label' => 'Registration deadline',
            ],
            'host_timezone' => [
                'label' => 'What timezone are you in? *',
                'helper' => 'We detected :detected on this device. Your workshop dates will be converted from this timezone.',
                'unknown' => 'your current timezone',
            ],
            'meeting_link' => [
                'label' => 'Meeting link',
            ],
            'recording_url' => [
                'label' => 'Google Drive recording link',
                'helper' => 'Paste the share link in the /file/d/.../view format so we can convert it to an embeddable player on your public page.',
            ],
            'location' => [
                'label' => 'Location *',
            ],
            'address' => [
                'label' => 'Full address',
            ],
            'instructor' => [
                'label' => 'Instructor name',
            ],
            'instructor_bio' => [
                'label' => 'Short bio',
            ],
            'image' => [
                'label' => 'Upload cover image',
            ],
            'passcode' => [
                'label' => 'Passcode:',
            ],
        ],
        'placeholders' => [
            'category' => 'e.g., Baking, main dishes, desserts...',
            'description' => 'Share the goals, your teaching style, and the value participants will take away.',
            'content' => 'Lay out the detailed content or session agenda in a clear order.',
            'learning_points' => 'List 3–5 takeaways participants will gain.',
            'requirements' => 'Call out any skills or tools needed before joining.',
        ],
        'options' => [
            'online_label' => 'This workshop is online',
            'online_hint' => 'This workshop is online-only and cannot be switched to in-person.',
            'auto_generate_label' => 'Auto-generate a Google Meet link on save',
        ],
        'buttons' => [
            'generate_link' => 'Generate link now',
            'remove_image' => 'Remove current image',
        ],
        'messages' => [
            'pricing_notice_title' => 'Important:',
            'pricing_notice_body' => 'Peahskill retains a :fee_range service fee to cover payment gateways, technical support, and marketing.',
            'pricing_notice_followup' => 'After this fee is deducted, the net payout is transferred to you within 7 business days of the workshop.',
            'meeting_hint_auto' => 'We provide automatic Google Meet links for you from your account—add the host Gmail and we will attach it.',
            'meeting_hint_manual' => 'You can paste an existing meeting link if you prefer.',
            'google_ready' => 'A Google Meet link is ready:',
            'managed_link_title' => 'Link managed by the Peahskill team',
            'managed_link_description' => 'We will generate and secure the meeting link automatically after saving, and the raw link stays hidden for privacy.',
            'calendar_connect_required_title' => 'Please connect Google Calendar first',
            'calendar_connect_required_body' => 'Please link your Google Calendar account so we can create the meeting link from your own calendar.',
            'calendar_connect_button' => 'Connect Google Calendar',
            'image_max_size' => 'You cannot upload an image larger than 5 MB.',
        ],
        'js' => [
            'title_required' => 'Please enter the workshop title first.',
            'generate_failed' => 'Unable to generate the link right now.',
            'generic_error' => 'An unexpected error occurred.',
        ],
    ],

    'workshops_earnings' => [
        'title' => 'Workshop earnings',
        'hero' => [
            'eyebrow' => 'Dashboard area',
            'heading' => 'Workshop earnings',
            'description' => 'Review the financial performance of your online workshops and track the net payout that was already allocated to you.',
            'cta' => [
                'new' => 'Launch new workshop',
                'back' => 'Back to workshops list',
            ],
        ],
        'notice' => [
            'eyebrow' => 'Important note',
            'title' => 'Peahskill retains a 15%–20% platform commission',
            'description' => 'Whenever you launch a new workshop we withhold 15% to 20% to cover payments, technical support, and marketing operations. The remaining balance is transferred within a week after the workshop ends once payouts are settled.',
        ],
        'stats' => [
            'gross' => [
                'label' => 'Total collected amount',
                'hint' => 'From all paid bookings',
            ],
            'net' => [
                'label' => 'Net payout released',
                'hint' => 'What the finance split already assigned to you',
            ],
            'paid_seats' => [
                'label' => 'Paid seats',
                'hint' => 'Includes all completed workshops',
            ],
            'average' => [
                'label' => 'Average net per attendee',
                'hint' => 'Based on confirmed payouts only',
            ],
        ],
        'monthly' => [
            'current' => [
                'label' => 'This month\'s earnings',
                'delta' => ':amount vs last month',
                'hint' => 'Updates as soon as participants\' payments are confirmed',
                'net_label' => 'Net payout',
            ],
            'previous' => [
                'label' => 'Last month\'s earnings',
                'hint' => 'For historical comparison only',
                'net_label' => 'Net payout',
            ],
        ],
        'leaderboard' => [
            'eyebrow' => 'Top-performing workshops',
            'title' => 'Workshop-by-workshop breakdown',
            'description' => 'Actual net amounts after the automated split.',
            'button' => 'Manage workshops',
            'table' => [
                'workshop' => 'Workshop',
                'start_date' => 'Start date',
                'paid' => 'Paid participants',
                'gross' => 'Total paid',
                'net' => 'Net payout',
            ],
            'capacity' => 'Capacity :capacity',
            'capacity_unknown' => 'Not set yet',
            'date_pending' => 'To be scheduled',
            'empty' => 'No financial data yet. Create a workshop and confirm paid bookings.',
        ],
    ],

    'recipes_create' => [
        'page_title' => 'Create Recipe | Peahskill Studio',
        'hero_badge' => 'Peahskill Studio',
        'hero_heading' => 'Share a new Peahskill recipe',
        'hero_description' => 'Shape the story, visuals, and steps so we can publish it with your signature on Peahskill.',
        'back_to_index' => 'Back to my recipes',
        'validation_heading' => 'Please double-check these fields before sending:',
        'actions' => [
            'save_draft' => 'Save as draft',
            'submit_review' => 'Send to Peahskill review',
        ],
    ],

    'recipe_form' => [
        'sections' => [
            'basics' => [
                'title' => 'Recipe essentials',
                'description' => 'Provide the information our editors need to approve your recipe quickly.',
            ],
            'steps' => [
                'title' => 'Preparation steps',
                'description' => 'Add clear, sequential steps so the recipe is easy to follow.',
                'add_button' => 'Add step',
                'placeholder' => 'Step description',
            ],
            'ingredients' => [
                'title' => 'Ingredients',
                'description' => 'List each ingredient with its exact quantity.',
                'add_button' => 'Add ingredient',
                'name_label' => 'Ingredient name',
                'name_placeholder' => 'Example: All-purpose flour',
                'amount_label' => 'Quantity',
                'amount_placeholder' => 'Example: 2 cups',
                'remove_button' => 'Remove',
            ],
            'tools' => [
                'title' => 'Suggested tools (optional)',
            ],
            'media' => [
                'title' => 'Recipe photos',
                'description' => 'Upload up to 5 high-quality images to showcase your dish.',
                'primary' => 'Main image',
                'additional' => 'Additional image :number',
                'max_size_message' => 'Images must be 5 MB or less.',
                'max_size_hint' => 'Maximum file size per image is 5 MB.',
                'current_alt' => 'Recipe image',
                'remove_current' => 'Remove this image',
            ],
            'external_image' => [
                'title' => 'External image link (optional)',
                'description' => 'Paste a direct image link (Google Drive, Unsplash, etc.) if you have one.',
                'placeholder' => 'https://example.com/your-image.jpg',
            ],
            'video' => [
                'title' => 'YouTube video link (optional)',
                'description' => 'Paste any YouTube link (Watch, share, or Shorts) and we\'ll turn it into an embeddable player on your recipe page.',
                'placeholder' => 'https://www.youtube.com/watch?v=example',
                'hint' => 'Viewers will see the video near the top of your recipe after it is saved.',
            ],
        ],
        'fields' => [
            'title' => [
                'label' => 'Recipe title *',
            ],
            'description' => [
                'label' => 'Short recipe description',
                'placeholder' => 'Share the story or any serving tips.',
            ],
            'category' => [
                'label' => 'Category',
                'placeholder' => 'Select a category',
            ],
            'difficulty' => [
                'label' => 'Difficulty level',
                'placeholder' => 'Select a level',
                'options' => [
                    'easy' => 'Easy',
                    'medium' => 'Intermediate',
                    'hard' => 'Advanced',
                ],
            ],
            'visibility' => [
                'label' => 'Visibility',
                'options' => [
                    'public' => [
                        'label' => 'Public',
                        'description' => 'Visible to everyone once approved.',
                    ],
                    'private' => [
                        'label' => 'Private',
                        'description' => 'Stays hidden even after approval.',
                    ],
                ],
            ],
            'prep_time' => [
                'label' => 'Prep time (minutes)',
            ],
            'cook_time' => [
                'label' => 'Cook time (minutes)',
            ],
            'servings' => [
                'label' => 'Servings',
            ],
        ],
    ],

    'recipes' => [
        'tabs' => [
            'public' => 'Public recipes',
            'exclusive' => 'Exclusive recipes',
        ],
        'public_empty' => 'No public recipes yet. Stay tuned for the upcoming creations!',
        'exclusive_empty' => 'No exclusive recipes yet. Share your premium creations here.',
        'category_fallback' => 'Recipe',
        'no_rating' => 'No rating yet',
        'saves' => ':count saves',
        'likes' => ':count likes',
        'private_tag' => 'Exclusive recipe',
        'private_details' => 'Detailed step-by-step guidance',
        'private_access' => 'Exclusive view',
        'view_recipe' => 'View recipe',
    ],

    'link_page' => [
        'title' => 'Peahskill link page',
        'description' => 'Share one branded URL that gathers your recipes, workshops, and booking links.',
        'features' => [
            'customize' => 'Custom branding',
            'instant_updates' => 'Instant updates',
            'shareable' => 'Share anywhere',
        ],
        'actions' => [
            'manage' => 'Customize page',
            'view' => 'View public link',
        ],
    ],

    'empty_state' => [
        'title' => 'No recipes yet',
        'description' => 'Create your first recipe to populate this list.',
        'cta' => 'Create a recipe',
    ],

    'table' => [
        'headers' => [
            'recipe' => 'Recipe',
            'status' => 'Status',
            'visibility' => 'Visibility',
            'updated_at' => 'Last updated',
            'category' => 'Category',
            'actions' => 'Actions',
        ],
        'approved_on' => 'Approved on :date',
        'no_category' => 'Uncategorized',
        'actions' => [
            'edit' => 'Edit',
            'submit_for_review' => 'Submit for review',
            'delete_confirm' => 'Are you sure you want to delete this recipe? This action cannot be undone.',
            'delete' => 'Delete',
        ],
    ],

    'popular' => [
        'title' => 'Most-viewed recipes',
        'subtitle' => 'Explore the standout dishes winning over food lovers and followers.',
        'view_details' => 'Discover the details',
    ],
];
