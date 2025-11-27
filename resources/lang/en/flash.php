<?php

$brand = config('app.name', 'Peahskill');

return [
    'success' => [
        'contact' => [
            'message_submitted' => "Thanks! Your message has been recorded and the {$brand} team will get back to you after reviewing it.",
        ],
        'workshop' => [
            'created_for_chef' => 'Workshop created successfully! You can track bookings from the dashboard.',
            'created_basic' => 'Workshop created successfully!',
            'updated' => 'Workshop updated successfully!',
            'deleted' => 'Workshop deleted successfully.',
            'room_already_started' => 'The meeting was already started.',
            'room_opened' => 'The room is open and participants can join now.',
            'device_reset' => 'The trusted device has been reset. You can now open the workshop room from this device.',
            'activated' => 'Workshop activated successfully!',
            'deactivated' => 'Workshop deactivated successfully!',
            'set_as_upcoming' => '":title" has been set as the upcoming workshop successfully!',
        ],
        'recipe' => [
            'deleted' => 'Recipe deleted successfully.',
            'submitted_for_review' => 'Recipe sent for review. We will notify you once it\'s approved.',
            'created' => 'Recipe added successfully!',
            'approved' => 'Recipe approved and published successfully.',
            'rejected' => 'Recipe rejected. Please notify the chef about the required changes.',
            'updated' => 'Recipe updated successfully!',
        ],
        'links' => [
            'page_updated' => "Your {$brand} Links page was updated successfully.",
            'added' => 'Link added successfully.',
            'updated' => 'Link updated successfully.',
            'deleted' => 'Link deleted.',
        ],
        'profile' => [
            'updated' => 'Profile updated successfully.',
        ],
        'auth' => [
            'social' => [
                'onboarding_required' => "Welcome! We need a few more details to verify you as a chef on {$brand}.",
                'pending_workshop' => [
                    'new_chef' => 'New chef account created successfully! You can now follow up on the selected workshop.',
                    'new_customer' => 'New account created successfully! You can now book the workshop.',
                    'existing_chef' => 'Signed in successfully as a chef! You can now follow up on the selected workshop.',
                    'existing_customer' => 'Signed in successfully! You can now book the workshop.',
                ],
                'new_chef' => "New chef account created successfully! Welcome to {$brand}.",
                'new_customer' => "New customer account created successfully! Welcome to {$brand}.",
                'new_generic' => "New account created successfully! Welcome to {$brand}.",
                'existing_chef' => "Signed in successfully! Your account has been updated to chef on {$brand}.",
                'existing_customer' => "Signed in successfully! Your {$brand} account is ready to use.",
                'existing_generic' => "Signed in successfully! Welcome back to {$brand}.",
            ],
            'register' => [
                'email_verified' => 'Email verified and account created successfully!',
                'complete_chef_profile' => 'Account created successfully! Please complete your details to finish the chef approval.',
                'workshop_flow' => 'Account created successfully! You can now complete your workshop booking.',
                'welcome' => "Account created successfully! Welcome to {$brand} 🎉",
            ],
            'policy' => [
                'default' => "Signed in successfully! Enjoy your {$brand} experience.",
            ],
            'onboarding' => [
                'instant_approval' => 'Congratulations! You were instantly approved as a chef and can start sharing your recipes and workshops.',
            ],
            'logout' => 'You have signed out successfully.',
        ],
        'referral' => [
            'partner_enabled' => 'User was enabled as a referral partner successfully.',
            'notes_updated' => 'Commission notes updated.',
            'status_unchanged' => 'This record already has the selected status.',
            'marked_paid' => 'Commission marked as paid/transferred.',
            'reset_ready' => 'Commission was moved back to the ready state.',
            'profile_updated' => 'Referral program settings for this user have been updated.',
        ],
        'hero_slide' => [
            'created' => 'Hero slide created successfully.',
            'imported_defaults' => 'Default slides imported successfully and are ready to edit.',
            'updated' => 'Hero slide updated successfully.',
            'deleted' => 'Slide deleted successfully.',
            'status_updated' => 'Slide status updated.',
            'order_updated' => 'Slide order updated.',
        ],
        'chef' => [
            'approved' => 'Chef approved successfully and their permissions are now active.',
            'rejected' => 'Chef request rejected. Please reach out with the review notes.',
        ],
        'tool' => [
            'created' => 'Tool added successfully!',
            'updated' => 'Tool updated successfully!',
            'deleted' => 'Tool deleted successfully!',
            'activated' => 'Tool activated successfully!',
            'deactivated' => 'Tool deactivated successfully!',
        ],
    ],
    'error' => [
        'auth' => [
            'chef_only' => 'This page is only for approved chefs. Please complete your profile to continue.',
            'login_required' => 'You need to sign in first.',
            'social' => [
                'account_not_found' => "We couldn't find a {$brand} account linked to your email. Please choose the create account option.",
                'login_failed' => 'Something went wrong while signing in. Please try again.',
            ],
            'register' => [
                'missing_data' => 'Please submit your registration details first.',
                'session_expired' => 'The registration request has expired. Please try again.',
                'code_expired' => 'The verification code has expired. Please register again.',
                'max_attempts' => 'You exceeded the maximum verification attempts. Please start over.',
                'wait_before_retry' => 'Please wait a minute before requesting a new code.',
            ],
        ],
        'cart' => [
            'empty' => 'Your cart is empty.',
            'no_amazon_items' => 'There are no Amazon products in the cart.',
        ],
        'workshop' => [
            'login_required_for_room' => 'You need to sign in to access the workshop room.',
            'booking_not_confirmed' => 'You cannot join the workshop before your booking is confirmed.',
            'room_unavailable' => 'This workshop is not online or the meeting link is currently unavailable.',
            'room_config_issue' => "The meeting room can't be opened right now because of a configuration issue. Please contact support or try again later.",
            'room_prepare_failed' => 'Something went wrong while preparing the meeting room. Please try again later or contact support.',
            'device_mismatch' => "You can't open the workshop link from a different device. Please contact support to update your access.",
            'room_loading_failed' => "We couldn't load the meeting room because of an unexpected error. Please try again later or contact support.",
            'no_active_link' => 'This workshop does not have an active meeting link yet.',
            'invalid_meeting_type' => "The current meeting link isn't a Google Meet URL, so the admin room can't be opened here.",
        ],
        'referral' => [
            'partner_only' => 'This page is available only to referral partners.',
            'cannot_update_cancelled' => "You can't update the status of a cancelled commission.",
        ],
        'recipe' => [
            'cannot_delete_reviewed' => "You can't delete approved or in-review recipes.",
            'cannot_resubmit' => "This recipe can't be resubmitted right now.",
        ],
        'hero_slide' => [
            'already_exists' => 'Slides already exist. Delete them first if you need to re-import.',
        ],
        'tool' => [
            'save_failed_with_message' => 'Something went wrong while saving the tool: :message',
        ],
    ],
    'validation' => [
        'onboarding' => [
            'phone_invalid' => 'Please enter a valid mobile number.',
        ],
        'register' => [
            'email_send_failed' => "We couldn't send the verification email. Please try again later or contact us for assistance.",
            'code_incorrect_attempts' => 'The verification code is incorrect. You have :attempts attempt(s) left.',
            'code_incorrect_request_new' => 'The verification code is incorrect. Please request a new code.',
        ],
        'profile' => [
            'name_profanity' => 'The name contains inappropriate words.',
            'avatar_name_profanity' => 'The file name contains inappropriate words.',
            'avatar_professional' => 'Please choose a professional, suitable photo.',
        ],
        'admin' => [
            'referral' => [
                'user_lookup_failed' => 'No user matches the information you entered.',
            ],
        ],
        'recipe' => [
            'update_failed_with_message' => 'Something went wrong while updating the recipe: :message',
        ],
        'policy' => [
            'name_required' => 'Please enter your full name.',
            'accept_terms' => 'You must accept the terms and privacy policy to continue.',
        ],
    ],
];
