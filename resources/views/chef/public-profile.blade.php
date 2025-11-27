@extends('layouts.app')

@section('title', __('chef.meta.title', ['name' => $chef->name ?? '']))

@php
    $locale = app()->getLocale();
    $carbonLocale = $locale === 'ar' ? 'ar' : 'en';

    $totalFollowers = max(0, (int) ($chef->instagram_followers ?? 0)) + max(0, (int) ($chef->youtube_followers ?? 0));
    $bio = $chef->chef_specialty_description
        ?: __('chef.defaults.bio');
    $specialty = $chef->chef_specialty_area
        ? __('chef.defaults.specialty_with_area', ['area' => $chef->chef_specialty_area])
        : __('chef.defaults.specialty_generic');

    $statsAverage = $stats['average_rating']
        ? number_format($stats['average_rating'], 1)
        : '—';
    $wasfahFollowers = max(
        0,
        (int) data_get(
            $chef,
            'followers_count',
            data_get(
                $chef,
                'wasfah_followers',
                data_get($chef, 'subscribers_count', data_get($chef, 'wasfah_subscribers', 0))
            )
        )
    );

    if ($wasfahFollowers === 0) {
        $maybeFollowersRelation = data_get($chef, 'followers');

        if (is_countable($maybeFollowersRelation)) {
            $wasfahFollowers = count($maybeFollowersRelation);
        }
    }

    $platformFollowers = $totalFollowers;
    $recipesCount = max(0, (int) $stats['recipes_count']);
    $workshopPlaceholderText = rawurlencode(__('chef.workshops.placeholder_text'));
    $workshopDateTimeFormat = __('chef.workshops.datetime_format');
    $workshopDateFormat = __('chef.workshops.date_format');
    $isRtl = $locale === 'ar';
    $arrowIcon = $isRtl ? 'fa-arrow-left' : 'fa-arrow-right';
    $arrowLongIcon = $isRtl ? 'fa-arrow-left-long' : 'fa-arrow-right-long';
    $publicWorkshopsIdentifier = $chef->username
        ?? $chef->slug
        ?? $chef->handle
        ?? $chef->referral_code
        ?? $chef->id;
    $publicWorkshopsUrl = route('chef.public.workshops', ['username' => $publicWorkshopsIdentifier]);
@endphp

@push('styles')
    <style>
        .chef-profile-page {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(180deg, rgba(234, 243, 246, 0.75), rgba(255, 255, 255, 0.9) 55%, #fdfcfb 100%);
            overflow-x: hidden;
            padding-block: clamp(3.5rem, 7vw, 6rem) clamp(4rem, 8vw, 6.5rem);
            --chef-primary: #0f4c73;
            --chef-primary-strong: #0a8070;
            --chef-neutral-900: #111827;
            --chef-neutral-700: #374151;
            --chef-neutral-500: #6b7280;
            --chef-neutral-200: #e5e7eb;
            --chef-card-shadow: 0 35px 60px -28px rgba(15, 23, 42, 0.22);
        }

        .chef-profile-page::before,
        .chef-profile-page::after {
            content: "";
            position: absolute;
            inset-inline-start: -170px;
            inset-block-start: -150px;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(234, 88, 12, 0.18), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .chef-profile-page::after {
            inset-inline-start: unset;
            inset-inline-end: -140px;
            inset-block-start: 55%;
            background: radial-gradient(circle, rgba(15, 76, 115, 0.14), transparent 72%);
        }

        .chef-profile-shell {
            position: relative;
            z-index: 1;
            max-width: 1180px;
            margin-inline: auto;
            padding-inline: clamp(1.25rem, 4vw, 2.75rem);
        }

        .chef-profile-hero {
            position: relative;
            border-radius: 2.75rem;
            padding: clamp(2.5rem, 5vw, 3.5rem);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(255, 248, 238, 0.98));
            border: 1px solid rgba(15, 76, 115, 0.18);
            box-shadow: var(--chef-card-shadow);
            overflow: hidden;
        }

        .chef-profile-hero::before,
        .chef-profile-hero::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            background: linear-gradient(160deg, rgba(15, 76, 115, 0.2), rgba(15, 76, 115, 0));
            pointer-events: none;
        }

        .chef-profile-hero::before {
            width: 220px;
            height: 220px;
            inset-inline-end: -60px;
            inset-block-start: -70px;
            opacity: 0.55;
        }

        .chef-profile-hero::after {
            width: 360px;
            height: 360px;
            inset-inline-start: -150px;
            inset-block-end: -200px;
            opacity: 0.35;
        }

        .chef-profile-hero__grid {
            position: relative;
            display: grid;
            gap: clamp(2rem, 4vw, 3.2rem);
            grid-template-columns: minmax(0, 280px) minmax(0, 1fr);
            align-items: center;
        }

        .chef-profile-hero__avatar {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chef-profile-hero__avatar::before {
            content: "";
            position: absolute;
            inset: clamp(-1.8rem, -3vw, -1.3rem);
            border-radius: 50%;
            background: conic-gradient(from 90deg, rgba(15, 76, 115, 0.24), rgba(15, 76, 115, 0));
            z-index: 0;
        }

        .chef-profile-hero__avatar img {
            position: relative;
            width: clamp(115px, 18vw, 170px);
            height: clamp(115px, 18vw, 170px);
            border-radius: 50%;
            border: 5px solid #ffffff;
            object-fit: cover;
            box-shadow: 0 20px 34px -30px rgba(15, 23, 42, 0.5);
            z-index: 1;
        }

        .chef-profile-hero__details {
            display: flex;
            flex-direction: column;
            gap: clamp(1.5rem, 3vw, 2.25rem);
        }

        .chef-profile-hero__identity {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            align-items: flex-end;
            text-align: right;
        }

        .chef-profile-hero__identity h1 {
            margin: 0;
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
            font-weight: 800;
            color: var(--chef-neutral-900);
        }

        .chef-profile-hero__bio-block {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .chef-profile-hero__bio {
            margin: 0;
            color: var(--chef-neutral-500);
            font-size: clamp(1.05rem, 2.25vw, 1.18rem);
            line-height: 1.9;
        }

        .chef-profile-hero__subtitle {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--chef-primary-strong);
        }

        .chef-profile-hero__meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.15rem;
        }

        .chef-meta-card {
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 0.85rem 1.1rem;
            border: none;
            box-shadow: 0 16px 35px -32px rgba(15, 23, 42, 0.55);
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .chef-meta-card span {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--chef-neutral-500);
            letter-spacing: 0.02em;
        }

        .chef-meta-card strong {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--chef-neutral-900);
            line-height: 1.1;
        }

        .chef-profile-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .chef-follow-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 2.3rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            background: linear-gradient(135deg, var(--chef-primary), var(--chef-primary-strong));
            border: none;
            cursor: pointer;
            box-shadow: 0 20px 45px rgba(15, 76, 115, 0.26);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .chef-follow-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 50px rgba(234, 88, 12, 0.28);
            filter: brightness(1.05);
        }

        .chef-follow-btn.is-following {
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
            box-shadow: 0 20px 45px rgba(29, 78, 216, 0.24);
        }

        .chef-profile-hero__social {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .chef-profile-hero__social li {
            margin: 0;
        }

        .chef-profile-hero__social .social-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.2rem;
            border-radius: 999px;
            border: 1px solid rgba(15, 76, 115, 0.18);
            color: var(--chef-primary-strong);
            background: rgba(15, 76, 115, 0.08);
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .chef-profile-hero__social .social-link:hover {
            background: rgba(15, 76, 115, 0.16);
            transform: translateY(-2px);
        }

        .chef-profile-tabs {
            margin-top: clamp(3rem, 7vw, 4.5rem);
        }

        .chef-tab-nav {
            display: inline-flex;
            gap: 0.6rem;
            padding: 0.4rem;
            border-radius: 999px;
            background: rgba(15, 76, 115, 0.08);
            border: 1px solid rgba(15, 76, 115, 0.1);
            margin-bottom: 2.2rem;
        }

        .chef-tab-btn {
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1.75rem;
            font-weight: 700;
            font-size: 1rem;
            color: var(--chef-neutral-500);
            background: transparent;
            cursor: pointer;
            transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .chef-tab-btn.is-active {
            color: var(--chef-neutral-900);
            background: #ffffff;
            box-shadow: 0 20px 45px rgba(15, 76, 115, 0.16);
        }

        .chef-tab-panel {
            display: none;
            animation: fadeIn 0.35s ease forwards;
        }

        .chef-tab-panel.is-active {
            display: block;
        }

        .chef-recipes-grid {
            display: grid;
            gap: 1.75rem;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        }

        .chef-recipe-card {
            position: relative;
            border-radius: 1.85rem;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid rgba(15, 76, 115, 0.1);
            box-shadow: 0 28px 55px -35px rgba(15, 23, 42, 0.45);
            display: flex;
            flex-direction: column;
            min-height: 100%;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .chef-recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 32px 60px -32px rgba(15, 76, 115, 0.48);
        }

        .chef-recipe-card__cover {
            position: relative;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: #eaf3f6;
        }

        .chef-recipe-card__cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .chef-recipe-card:hover .chef-recipe-card__cover img {
            transform: scale(1.05);
        }

        .chef-recipe-card__tag {
            position: absolute;
            inset-inline-end: 1.1rem;
            inset-block-start: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(17, 24, 39, 0.78);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .chef-recipe-card__body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
            flex: 1;
        }

        .chef-recipe-card__title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--chef-neutral-900);
        }

        .chef-recipe-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            align-items: center;
        }

        .chef-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--chef-neutral-500);
            background: rgba(15, 76, 115, 0.08);
        }

        .chef-recipe-card__footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chef-recipe-card__cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--chef-primary-strong);
            text-decoration: none;
            transition: gap 0.2s ease, transform 0.2s ease;
        }

        .chef-recipe-card__cta:hover {
            gap: 0.7rem;
            transform: translateX(-2px);
        }

        .chef-empty-state {
            border-radius: 2rem;
            border: 1px dashed rgba(15, 76, 115, 0.24);
            padding: 3rem 2rem;
            background: rgba(255, 248, 238, 0.65);
            text-align: center;
            color: var(--chef-neutral-500);
            font-size: 1.05rem;
            font-weight: 600;
        }

        .chef-impact {
            margin-top: clamp(2.2rem, 5vw, 4rem);
            padding: clamp(2rem, 4vw, 3rem);
            border-radius: 2.5rem;
            background: linear-gradient(135deg, rgba(15, 76, 115, 0.08), rgba(15, 76, 115, 0));
            border: 1px solid rgba(15, 76, 115, 0.12);
            box-shadow: 0 26px 48px -38px rgba(15, 23, 42, 0.3);
        }

        .chef-impact__header {
            display: flex;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: clamp(1.5rem, 3vw, 2.5rem);
        }

        .chef-impact__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--chef-primary-strong);
            background: rgba(15, 76, 115, 0.12);
            margin-bottom: 0.8rem;
        }

        .chef-impact__header h2 {
            margin: 0;
            font-size: clamp(1.85rem, 3.2vw, 2.4rem);
            color: var(--chef-neutral-900);
        }

        .chef-impact__header p {
            margin: 0.25rem 0 0;
            color: var(--chef-neutral-600, #4b5563);
            line-height: 1.8;
            max-width: 520px;
        }

        .chef-impact__highlight {
            min-width: 220px;
            padding: 1.25rem 1.5rem;
            border-radius: 1.75rem;
            background: #fff;
            border: 1px solid rgba(15, 76, 115, 0.1);
            box-shadow: inset 0 0 0 1px rgba(15, 76, 115, 0.05);
            text-align: center;
        }

        .chef-impact__highlight span {
            display: block;
            font-size: 0.9rem;
            color: var(--chef-neutral-500);
        }

        .chef-impact__highlight strong {
            display: block;
            font-size: 2rem;
            font-weight: 800;
            margin: 0.25rem 0;
            color: var(--chef-primary-strong);
        }

        .chef-impact__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .chef-impact-card {
            position: relative;
            border-radius: 1.75rem;
            padding: 1.5rem;
            background: #fff;
            border: 1px solid rgba(15, 76, 115, 0.08);
            box-shadow: 0 18px 32px -34px rgba(15, 23, 42, 0.45);
            display: flex;
            gap: 1rem;
        }

        .chef-impact-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
        }

        .chef-impact-card__icon.is-saves {
            background: linear-gradient(135deg, #0f4c73, #0fa289);
        }

        .chef-impact-card__icon.is-made {
            background: linear-gradient(135deg, #34d399, #10b981);
        }

        .chef-impact-card__icon.is-reviews {
            background: linear-gradient(135deg, #f43f5e, #0f4c73);
        }

        .chef-impact-card__content {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .chef-impact-card__eyebrow {
            font-size: 0.85rem;
            color: var(--chef-neutral-500);
            margin: 0;
        }

        .chef-impact-card__value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--chef-neutral-900);
            margin: 0;
        }

        .chef-impact-card__hint {
            margin: 0;
            font-size: 0.92rem;
            color: var(--chef-neutral-600, #4b5563);
        }

        .chef-recordings-section {
            margin-top: clamp(3rem, 6vw, 4.5rem);
            padding: clamp(2rem, 5vw, 3.5rem);
            border-radius: 2.75rem;
            background: linear-gradient(140deg, rgba(15, 23, 42, 0.04), rgba(15, 76, 115, 0.08));
            border: 1px solid rgba(15, 76, 115, 0.12);
            box-shadow: 0 32px 60px -35px rgba(15, 23, 42, 0.28);
        }

        .chef-recordings-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: clamp(1.75rem, 3vw, 2.5rem);
        }

        .chef-recordings-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            background: rgba(15, 76, 115, 0.15);
            color: var(--chef-primary-strong);
        }

        .chef-recordings-header h2 {
            margin: 0.6rem 0 0;
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
            color: var(--chef-neutral-900);
        }

        .chef-recordings-header p {
            margin: 0.5rem 0 0;
            color: var(--chef-neutral-600, #4b5563);
            max-width: 560px;
            line-height: 1.8;
        }

        .chef-recordings-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1.6rem;
            border-radius: 999px;
            background: #fff;
            color: var(--chef-primary-strong);
            font-weight: 700;
            text-decoration: none;
            border: 1px solid rgba(15, 76, 115, 0.2);
            box-shadow: 0 18px 32px -24px rgba(15, 76, 115, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .chef-recordings-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 36px -24px rgba(15, 76, 115, 0.42);
        }

        .chef-recordings-grid {
            display: grid;
            gap: clamp(1.25rem, 3vw, 2rem);
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .chef-recording-card {
            display: flex;
            flex-direction: column;
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(15, 76, 115, 0.1);
            overflow: hidden;
            box-shadow: 0 28px 45px -38px rgba(15, 23, 42, 0.35);
            min-height: 100%;
        }

        .chef-recording-card__media {
            position: relative;
            aspect-ratio: 16 / 9;
            background: rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .chef-recording-card__media iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .chef-recording-card__placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--chef-neutral-600, #4b5563);
            text-align: center;
            padding: 1.5rem;
            background: rgba(15, 23, 42, 0.05);
        }

        .chef-recording-card__placeholder i {
            font-size: 1.75rem;
            color: var(--chef-primary-strong);
        }

        .chef-recording-card__badge {
            position: absolute;
            inset-inline-start: 1rem;
            inset-block-start: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.95rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.85);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .chef-recording-card__body {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            padding: 1.5rem;
        }

        .chef-recording-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--chef-neutral-600, #4b5563);
        }

        .chef-recording-card__meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .chef-recording-card__title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--chef-neutral-900);
        }

        .chef-recording-card__excerpt {
            margin: 0;
            color: var(--chef-neutral-500);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .chef-recording-card__actions {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .chef-recording-card__cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .chef-recording-card__cta.is-primary {
            background: linear-gradient(135deg, var(--chef-primary), var(--chef-primary-strong));
            color: #fff;
            box-shadow: 0 18px 32px -20px rgba(15, 76, 115, 0.35);
        }

        .chef-recording-card__cta.is-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 34px -20px rgba(234, 88, 12, 0.4);
        }

        .chef-recording-card__cta.is-muted {
            background: rgba(15, 23, 42, 0.06);
            color: var(--chef-neutral-800, #1f2937);
        }

        .chef-recording-card__cta.is-muted:hover {
            transform: translateY(-2px);
            background: rgba(15, 23, 42, 0.1);
        }

        .chef-workshops-section {
            margin-block: clamp(3rem, 7vw, 4.5rem);
            padding: clamp(2rem, 5vw, 3.5rem);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(255, 245, 235, 0.92));
            border: 1px solid rgba(15, 76, 115, 0.14);
            border-radius: 2.75rem;
            box-shadow: 0 32px 46px -34px rgba(15, 23, 42, 0.25);
        }

        .chef-workshops-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: clamp(1.75rem, 3vw, 2.5rem);
        }

        .chef-workshops-header__meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .chef-workshops-header__meta h2 {
            font-size: clamp(1.75rem, 4vw, 2.4rem);
            color: var(--chef-neutral-900);
            margin-bottom: 0.35rem;
        }

        .chef-workshops-header__meta p {
            max-width: 540px;
            color: var(--chef-neutral-700);
            line-height: 1.7;
        }

        .chef-workshops-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(15, 76, 115, 0.1);
            color: var(--chef-primary-strong);
            border-radius: 999px;
            font-weight: 600;
            transition: background 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
        }

        .chef-workshops-link:hover {
            background: rgba(15, 76, 115, 0.18);
            transform: translateY(-2px);
        }

        .chef-workshops-group + .chef-workshops-group {
            margin-top: clamp(2rem, 4vw, 3rem);
            padding-top: clamp(1.5rem, 3vw, 2.5rem);
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .chef-workshops-group > h3 {
            font-size: clamp(1.4rem, 3vw, 1.75rem);
            color: var(--chef-neutral-900);
            margin-bottom: clamp(1.2rem, 3vw, 1.75rem);
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }

        .chef-workshops-grid {
            display: grid;
            gap: clamp(1.5rem, 3vw, 2.25rem);
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .chef-workshop-card {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border-radius: 1.75rem;
            border: 1px solid rgba(15, 76, 115, 0.12);
            box-shadow: 0 22px 38px -26px rgba(15, 23, 42, 0.16);
            overflow: hidden;
            position: relative;
            min-height: 100%;
        }

        .chef-workshop-card__media {
            position: relative;
            aspect-ratio: 4 / 3;
            overflow: hidden;
        }

        .chef-workshop-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .chef-workshop-card:hover .chef-workshop-card__media img {
            transform: scale(1.05);
        }

        .chef-workshop-card__badge {
            position: absolute;
            inset-inline-start: 1.25rem;
            inset-block-start: 1.25rem;
            background: var(--chef-primary);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            box-shadow: 0 12px 24px -16px rgba(15, 76, 115, 0.45);
        }

        .chef-workshop-card__badge.is-closed {
            background: rgba(15, 23, 42, 0.7);
        }

        .chef-workshop-card__price {
            position: absolute;
            inset-block-start: 1.1rem;
            inset-inline-end: 1.2rem;
            background: linear-gradient(135deg, #f97316, #f59e0b);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.4rem 0.95rem;
            border-radius: 999px;
            box-shadow: 0 12px 24px -18px rgba(15, 23, 42, 0.25);
        }

        .chef-workshop-card__overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.25), rgba(15, 23, 42, 0.5));
            backdrop-filter: blur(2px);
        }

        .chef-workshop-card__overlay span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #fbbf24;
            color: #92400e;
            font-weight: 800;
            padding: 0.55rem 1.15rem;
            border-radius: 999px;
            box-shadow: 0 10px 22px -18px rgba(0, 0, 0, 0.28);
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .chef-workshop-card__body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1.75rem;
        }

        .chef-workshop-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .chef-workshop-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            font-size: 0.82rem;
            background: rgba(15, 23, 42, 0.05);
            color: var(--chef-neutral-700);
            font-weight: 600;
        }

        .chef-workshop-chip.is-online {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .chef-workshop-chip.is-offline {
            background: rgba(16, 185, 129, 0.12);
            color: #047857;
        }

        .chef-workshop-card__title {
            font-size: clamp(1.15rem, 2.8vw, 1.35rem);
            color: var(--chef-neutral-900);
            font-weight: 700;
            line-height: 1.5;
        }

        .chef-workshop-card__lead {
            color: var(--chef-neutral-500);
            line-height: 1.65;
        }

        .chef-workshop-card__details {
            display: grid;
            gap: 0.75rem;
            color: var(--chef-neutral-700);
            font-size: 0.95rem;
        }

        .chef-workshop-card__details li {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .chef-workshop-card__footer {
            margin-top: auto;
        }

        .chef-workshop-card__cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: auto;
            padding: 0.75rem 1.25rem;
            background: var(--chef-primary);
            color: #ffffff;
            font-weight: 600;
            border-radius: 999px;
            transition: background 0.2s ease, transform 0.2s ease;
            width: 100%;
        }

        .chef-workshop-card__cta:hover {
            background: var(--chef-primary-strong);
            transform: translateY(-2px);
        }

        .chef-workshop-card__cta.is-closed {
            background: rgba(15, 23, 42, 0.08);
            color: var(--chef-neutral-700);
            cursor: default;
        }

        .chef-workshop-card__cta.is-closed:hover {
            background: rgba(15, 23, 42, 0.08);
            transform: none;
        }

        .chef-workshop-card__cta.is-muted {
            background: rgba(15, 23, 42, 0.08);
            color: var(--chef-neutral-700);
        }

        .chef-workshop-card__cta.is-muted:hover {
            background: rgba(15, 23, 42, 0.12);
            transform: none;
        }

        .chef-carousel-section {
            margin-top: clamp(3.5rem, 7vw, 5.2rem);
            padding: clamp(2.6rem, 5vw, 3.6rem);
            border-radius: 2.5rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(255, 243, 221, 0.88));
            border: 1px solid rgba(15, 76, 115, 0.12);
            box-shadow: 0 30px 60px -40px rgba(15, 23, 42, 0.42);
        }

        .chef-carousel-header {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            margin-bottom: 2.1rem;
        }

        .chef-carousel-header h2 {
            margin: 0;
            font-size: clamp(1.9rem, 3vw, 2.4rem);
            font-weight: 800;
            color: var(--chef-neutral-900);
        }

        .chef-carousel-header span {
            color: var(--chef-neutral-500);
            font-size: 1rem;
            line-height: 1.7;
        }

        .chef-popular-swiper {
            padding-bottom: 3.1rem;
        }

        .chef-popular-card {
            border-radius: 2rem;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid rgba(15, 76, 115, 0.12);
            box-shadow: 0 28px 55px -35px rgba(15, 23, 42, 0.4);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chef-popular-card__cover {
            position: relative;
            aspect-ratio: 16 / 11;
            background: #fff1e6;
            overflow: hidden;
        }

        .chef-popular-card__cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .chef-popular-card:hover .chef-popular-card__cover img {
            transform: scale(1.04);
        }

        .chef-popular-card__body {
            padding: 1.6rem;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
            flex: 1;
        }

        .chef-popular-card__title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--chef-neutral-900);
        }

        .chef-popular-card__stats {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .chef-carousel-pagination {
            position: static;
            margin-top: 1.5rem;
        }

        .chef-carousel-pagination .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: rgba(15, 76, 115, 0.35);
            opacity: 1;
        }

        .chef-carousel-pagination .swiper-pagination-bullet-active {
            background: var(--chef-primary-strong);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .chef-profile-hero__grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .chef-profile-hero__avatar::before {
                inset: clamp(-1.3rem, -3vw, -0.9rem);
            }

            .chef-profile-hero__meta {
                justify-items: center;
            }

            .chef-profile-hero__actions {
                justify-content: center;
            }

            .chef-profile-hero__social {
                justify-content: center;
            }

            .chef-impact__header {
                flex-direction: column;
                text-align: center;
            }

            .chef-impact__highlight {
                width: 100%;
            }

            .chef-recordings-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .chef-recording-card__actions {
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .chef-profile-page {
                padding-inline: 0.9rem;
            }

            .chef-profile-hero {
                padding: 1.75rem 1.35rem;
                border-radius: 2rem;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(255, 248, 238, 0.98));
                border: 1px solid rgba(15, 76, 115, 0.18);
                color: var(--chef-neutral-900);
            }

            .chef-profile-hero__grid {
                gap: 1.15rem 1rem;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "stats avatar"
                    "name name"
                    "bio bio"
                    "actions actions"
                    "social social";
                align-items: flex-start;
            }

            .chef-profile-hero__details {
                display: contents;
            }

            .chef-profile-hero__avatar {
                grid-area: avatar;
                justify-self: end;
            }

            .chef-profile-hero__avatar img {
                width: 95px;
                height: 95px;
                border-width: 3px;
                box-shadow: 0 18px 26px -24px rgba(15, 23, 42, 0.85);
            }

            .chef-profile-hero__meta {
                grid-area: stats;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.85rem;
                align-items: stretch;
            }

            .chef-meta-card {
                padding: 0.75rem 0.85rem;
                background: #ffffff;
                border-radius: 1rem;
                text-align: start;
                box-shadow: 0 12px 30px -28px rgba(15, 23, 42, 0.25);
            }

            .chef-meta-card--rating {
                display: none;
            }

            .chef-meta-card span {
                font-size: 0.68rem;
                color: var(--chef-neutral-500);
            }

            .chef-meta-card strong {
                font-size: 1.15rem;
                color: var(--chef-neutral-900);
            }

            .chef-profile-hero__identity {
                grid-area: name;
                align-items: flex-end;
                text-align: start;
                gap: 0.45rem;
            }

            .chef-profile-hero__identity h1 {
                font-size: 1.2rem;
                letter-spacing: 0.01em;
                color: var(--chef-neutral-900);
            }

            .chef-profile-hero__subtitle {
                font-size: 0.82rem;
                color: var(--chef-primary-strong);
            }

            .chef-profile-hero__bio-block {
                grid-area: bio;
                text-align: start;
                gap: 0.35rem;
            }

            .chef-profile-hero__bio {
                font-size: 0.95rem;
                line-height: 1.6;
                max-width: 100%;
                color: var(--chef-neutral-500);
            }

            .chef-profile-hero__actions {
                grid-area: actions;
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
                width: 100%;
            }

            .chef-follow-btn {
                width: 100%;
                justify-content: center;
                padding: 0.9rem 1.1rem;
                font-size: 0.95rem;
                border-radius: 999px;
                box-shadow: 0 18px 30px -18px rgba(234, 88, 12, 0.25);
            }

            .chef-profile-hero__social {
                grid-area: social;
                width: 100%;
                justify-content: center;
            }

            .chef-profile-hero__social .social-link {
                flex: 1 1 auto;
                justify-content: center;
                padding-inline: 1rem;
                min-width: 0;
                background: #ffffff;
                border-color: rgba(15, 76, 115, 0.2);
                color: var(--chef-primary-strong);
                box-shadow: 0 12px 24px -20px rgba(15, 23, 42, 0.25);
            }

            .chef-profile-hero__social .social-link span.text-xs {
                color: var(--chef-primary-strong);
            }

            .chef-impact {
                padding: 1.5rem;
                border-radius: 1.5rem;
            }

            .chef-impact__header {
                text-align: center;
            }

            .chef-impact-card {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }

            .chef-impact-card__content {
                align-items: center;
            }

            .chef-workshops-section {
                border-radius: 1.75rem;
                padding: 1.75rem;
            }

            .chef-workshops-header__meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.25rem;
            }

            .chef-workshops-link {
                width: 100%;
                justify-content: center;
            }

            .chef-workshops-group > h3 {
                font-size: 1.35rem;
            }

            .chef-workshops-grid {
                grid-template-columns: 1fr;
            }

            .chef-workshop-card {
                border-radius: 1.35rem;
                box-shadow: 0 18px 32px -26px rgba(15, 23, 42, 0.22);
            }

            .chef-workshop-card__media {
                aspect-ratio: 16 / 10;
            }

            .chef-workshop-card__badge {
                font-size: 0.78rem;
                padding: 0.32rem 0.75rem;
                inset-block-start: 0.95rem;
                inset-inline-start: 0.95rem;
            }

            .chef-workshop-card__price {
                inset-block-start: 0.9rem;
                inset-inline-end: 0.95rem;
                font-size: 0.82rem;
                padding: 0.35rem 0.85rem;
            }

            .chef-workshop-card__overlay span {
                font-size: 0.9rem;
                padding: 0.5rem 0.9rem;
            }

            .chef-workshop-card__body {
                padding: 1.25rem;
                gap: 0.85rem;
            }

            .chef-workshop-card__meta {
                gap: 0.45rem;
            }

            .chef-workshop-chip {
                font-size: 0.78rem;
                padding: 0.35rem 0.7rem;
            }

            .chef-workshop-card__title {
                font-size: 1.05rem;
            }

            .chef-workshop-card__details {
                font-size: 0.9rem;
                gap: 0.6rem;
            }

            .chef-workshop-card__cta {
                padding: 0.95rem 1.1rem;
                font-size: 0.95rem;
            }

            .chef-tab-btn {
                padding-inline: 1.2rem;
                font-size: 0.92rem;
            }

            .chef-recipe-card__body {
                padding: 1.2rem;
            }

            .chef-recipe-card__title {
                font-size: 1.05rem;
            }

            .chef-popular-card__body {
                padding: 1.25rem;
            }

            .chef-recordings-section {
                padding: 1.75rem;
            }

            .chef-recordings-link {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .chef-recording-card__body {
                padding: 1.35rem;
            }

            .chef-recording-card__actions {
                width: 100%;
            }

            .chef-recording-card__cta {
                width: 100%;
                justify-content: center;
            }
        }

        [dir='ltr'] .chef-profile-hero__identity,
        [dir='ltr'] .chef-profile-hero__bio-block {
            align-items: flex-start;
            text-align: left;
        }

        [dir='ltr'] .chef-profile-hero__actions {
            justify-content: flex-start;
        }

        [dir='ltr'] .chef-profile-hero__social {
            justify-content: flex-start;
        }

        [dir='ltr'] .chef-impact__header {
            text-align: left;
        }

        [dir='ltr'] .chef-impact-card,
        [dir='ltr'] .chef-impact-card__content {
            align-items: flex-start;
            text-align: left;
        }

        [dir='ltr'] .chef-recipe-card__cta:hover {
            transform: translateX(2px);
        }

        @media (max-width: 640px) {
            [dir='ltr'] .chef-profile-hero__avatar {
                justify-self: start;
            }

            [dir='ltr'] .chef-profile-hero__identity,
            [dir='ltr'] .chef-profile-hero__bio-block {
                text-align: left;
            }

            [dir='ltr'] .chef-impact__header {
                text-align: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="chef-profile-page">
        <div class="chef-profile-shell">
            <section class="chef-profile-hero">
                    <div class="chef-profile-hero__grid">
                    <div class="chef-profile-hero__avatar">
                        <img src="{{ $avatarUrl }}" alt="{{ __('chef.hero.avatar_alt', ['name' => $chef->name]) }}" loading="lazy">
                    </div>
                    <div class="chef-profile-hero__details">
                        <div class="chef-profile-hero__identity">
                            <h1>{{ __('chef.hero.heading', ['name' => $chef->name]) }}</h1>
                        </div>
                        <div class="chef-profile-hero__bio-block">
                            <p class="chef-profile-hero__bio">{{ $bio }}</p>
                            <span class="chef-profile-hero__subtitle">{{ $specialty }}</span>
                        </div>
                        <div class="chef-profile-hero__meta">
                            <div class="chef-meta-card chef-meta-card--followers">
                                <strong
                                    data-followers-count
                                    data-followers-value="{{ $wasfahFollowers }}"
                                >{{ number_format($wasfahFollowers) }}</strong>
                                <span>{{ __('chef.hero.stats.wasfah_followers') }}</span>
                            </div>
                            <div class="chef-meta-card chef-meta-card--platforms">
                                <strong>{{ number_format($platformFollowers) }}</strong>
                                <span>{{ __('chef.hero.stats.other_platform_followers') }}</span>
                            </div>
                            <div class="chef-meta-card chef-meta-card--recipes">
                                <strong>{{ number_format($recipesCount) }}</strong>
                                <span>{{ __('chef.hero.stats.recipes') }}</span>
                            </div>
                            <div class="chef-meta-card chef-meta-card--rating">
                                <strong>{{ $statsAverage }}</strong>
                                <span>{{ __('chef.hero.stats.average_rating') }}</span>
                            </div>
                        </div>
                        <div class="chef-profile-hero__actions">
                            @if (! $isOwner)
                                @auth
                                    <button
                                        type="button"
                                        class="chef-follow-btn{{ $isFollowing ? ' is-following' : '' }}"
                                        data-follow-button
                                        data-follow-url="{{ $followRoutes['follow'] }}"
                                        data-unfollow-url="{{ $followRoutes['unfollow'] }}"
                                    >
                                        <i
                                            class="fa-solid {{ $isFollowing ? 'fa-check' : 'fa-plus' }}"
                                            data-follow-icon
                                        ></i>
                                        <span data-follow-label>
                                            {{ $isFollowing ? __('chef.hero.buttons.following') : __('chef.hero.buttons.follow') }}
                                        </span>
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="chef-follow-btn">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>{{ __('chef.hero.buttons.follow') }}</span>
                                    </a>
                                @endauth
                            @endif
                        </div>

                        @if ($socialLinks->isNotEmpty())
                            <ul class="chef-profile-hero__social">
                                @foreach ($socialLinks as $link)
                                    <li>
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="social-link">
                                            <i class="{{ $link['icon'] }}"></i>
                                            <span>{{ $link['label'] }}</span>
                                            @if (! empty($link['followers']))
                                                <span class="text-xs font-semibold text-orange-500">
                                                    {{ number_format($link['followers']) }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

            @if ($recordingEntries->isNotEmpty())
                <section class="chef-recordings-section">
                    <div class="chef-recordings-header">
                        <div>
                            <span class="chef-recordings-eyebrow">
                                <i class="fa-solid fa-video"></i>
                                {{ __('chef.recordings.eyebrow') }}
                            </span>
                            <h2>{{ __('chef.recordings.title') }}</h2>
                            <p>{{ __('chef.recordings.description', ['name' => $chef->name]) }}</p>
                        </div>
                        <a href="{{ $publicWorkshopsUrl }}" class="chef-recordings-link">
                            {{ __('chef.recordings.view_all') }}
                            <i class="fa-solid {{ $arrowLongIcon }}"></i>
                        </a>
                    </div>
                    <div class="chef-recordings-grid">
                        @foreach ($recordingEntries as $entry)
                            @php
                                $previewUrl = $entry['preview_url'] ?? null;
                                $watchUrl = $entry['watch_url'] ?? null;
                                $isDirectVideo = $entry['is_direct_video'] ?? false;
                                $poster = $entry['poster'] ?? \App\Support\BrandAssets::logoAsset('webp');
                                $badgeLabel = $entry['badge'] ?? __('chef.recordings.badges.available');
                            @endphp
                            <article class="chef-recording-card">
                                <div class="chef-recording-card__media">
                                    @if ($previewUrl)
                                        <iframe
                                            src="{{ $previewUrl }}"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                            title="{{ $entry['title'] }}"
                                        ></iframe>
                                    @elseif ($isDirectVideo)
                                        <video controls preload="metadata" playsinline poster="{{ $poster }}">
                                            <source src="{{ $watchUrl }}">
                                        </video>
                                    @else
                                        <div class="chef-recording-card__placeholder">
                                            <i class="fa-solid fa-cloud-arrow-down"></i>
                                            <span>{{ __('chef.recordings.fallback_drive') }}</span>
                                        </div>
                                    @endif
                                    <span class="chef-recording-card__badge">
                                        <i class="fa-solid fa-circle-play"></i>
                                        {{ $badgeLabel }}
                                    </span>
                                </div>
                                <div class="chef-recording-card__body">
                                    <div class="chef-recording-card__meta">
                                        <span>
                                            <i class="fa-solid fa-calendar-day"></i>
                                            {{ $entry['date_label'] ?? '' }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ $entry['location_label'] ?? '' }}
                                        </span>
                                    </div>
                                    <h3 class="chef-recording-card__title">{{ $entry['title'] }}</h3>
                                    @if (! empty($entry['excerpt']))
                                        <p class="chef-recording-card__excerpt">{{ $entry['excerpt'] }}</p>
                                    @endif
                                    <div class="chef-recording-card__actions">
                                        @if ($watchUrl)
                                            <a
                                                href="{{ $watchUrl }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="chef-recording-card__cta is-primary"
                                            >
                                                <i class="fa-solid fa-play"></i>
                                                {{ __('chef.recordings.cta.watch') }}
                                            </a>
                                        @endif
                                        @if (! empty($entry['details_url']))
                                            <a
                                                href="{{ $entry['details_url'] }}"
                                                class="chef-recording-card__cta is-muted"
                                            >
                                                {{ __('chef.workshops.view_details') }}
                                                <i class="fa-solid {{ $arrowIcon }}"></i>
                                            </a>
                                        @else
                                            <span class="chef-recording-card__cta is-muted">
                                                {{ __('chef.workshops.view_details') }}
                                                <i class="fa-solid {{ $arrowIcon }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="chef-impact">
                <div class="chef-impact__header">
                    <div>
                        <span class="chef-impact__badge">
                            <i class="fa-solid fa-chart-line"></i>
                            {{ __('chef.impact.badge') }}
                        </span>
                        <h2>{{ __('chef.impact.title') }}</h2>
                        <p>{{ __('chef.impact.description', ['name' => $chef->name]) }}</p>
                    </div>
                    <div class="chef-impact__highlight">
                        <span>{{ __('chef.impact.highlight_label') }}</span>
                        <strong>{{ number_format($recipesCount) }}</strong>
                        <span>{{ __('chef.impact.highlight_hint') }}</span>
                    </div>
                </div>
                <div class="chef-impact__grid">
                    <article class="chef-impact-card">
                        <span class="chef-impact-card__icon is-saves">
                            <i class="fa-solid fa-bookmark"></i>
                        </span>
                        <div class="chef-impact-card__content">
                            <p class="chef-impact-card__eyebrow">{{ __('chef.impact.cards.saves.title') }}</p>
                            <p class="chef-impact-card__value">{{ number_format((int) ($stats['total_saves'] ?? 0)) }}</p>
                            <p class="chef-impact-card__hint">{{ __('chef.impact.cards.saves.hint') }}</p>
                        </div>
                    </article>
                    <article class="chef-impact-card">
                        <span class="chef-impact-card__icon is-made">
                            <i class="fa-solid fa-utensils"></i>
                        </span>
                        <div class="chef-impact-card__content">
                            <p class="chef-impact-card__eyebrow">{{ __('chef.impact.cards.made.title') }}</p>
                            <p class="chef-impact-card__value">{{ number_format((int) ($stats['total_made'] ?? 0)) }}</p>
                            <p class="chef-impact-card__hint">{{ __('chef.impact.cards.made.hint') }}</p>
                        </div>
                    </article>
                    <article class="chef-impact-card">
                        <span class="chef-impact-card__icon is-reviews">
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <div class="chef-impact-card__content">
                            <p class="chef-impact-card__eyebrow">{{ __('chef.impact.cards.reviews.title') }}</p>
                            <p class="chef-impact-card__value">{{ number_format((int) ($stats['rating_count'] ?? 0)) }}</p>
                            <p class="chef-impact-card__hint">{{ __('chef.impact.cards.reviews.hint') }}</p>
                        </div>
                    </article>
                </div>
            </section>

            @if ($upcomingWorkshops->isNotEmpty() || $pastWorkshops->isNotEmpty())
                <section class="chef-workshops-section">
                    <div class="chef-workshops-header">
                        <div class="chef-workshops-header__meta">
                            <div>
                                <h2>{{ __('chef.workshops.title') }}</h2>
                                <p>
                                    {{ __('chef.workshops.description', ['name' => $chef->name]) }}
                                </p>
                            </div>
                            <a href="{{ $publicWorkshopsUrl }}" class="chef-workshops-link">
                                {{ __('chef.workshops.view_all') }}
                                <i class="fa-solid {{ $arrowLongIcon }}"></i>
                            </a>
                        </div>
                    </div>

                    @if ($upcomingWorkshops->isNotEmpty())
                        <div class="chef-workshops-group">
                            <h3>
                                <i class="fa-solid fa-calendar-check text-emerald-500"></i>
                                {{ __('chef.workshops.upcoming') }}
                            </h3>
                            <div class="chef-workshops-grid">
                                @foreach ($upcomingWorkshops as $workshop)
                                    @php
                                        $coverImage = $workshop->image
                                            ? asset('storage/' . ltrim($workshop->image, '/'))
                                            : "https://placehold.co/600x400/0f4c73/FFFFFF?text={$workshopPlaceholderText}";
                                        $startDateLabel = $workshop->start_date
                                            ? $workshop->start_date->copy()->locale($carbonLocale)->translatedFormat($workshopDateTimeFormat)
                                            : __('chef.workshops.tbd_time');
                                        $locationLabel = $workshop->is_online
                                            ? __('chef.workshops.online_live')
                                            : ($workshop->location ?: __('chef.workshops.location_tbd'));
                                        $priceLabel = $workshop->formatted_price
                                            ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? 'USD'));
                                        $levelLabels = [
                                            'beginner' => __('chef.workshops.levels.beginner'),
                                            'intermediate' => __('chef.workshops.levels.intermediate'),
                                            'advanced' => __('chef.workshops.levels.advanced'),
                                        ];
                                        $levelLabel = $levelLabels[$workshop->level] ?? null;
                                        $currentBookings = number_format((int) ($workshop->bookings_count ?? 0));
                                        $maxParticipants = $workshop->max_participants ? number_format((int) $workshop->max_participants) : null;
                                        $capacityLabel = $workshop->max_participants
                                            ? __('chef.workshops.capacity_with_limit', ['current' => $currentBookings, 'max' => $maxParticipants])
                                            : __('chef.workshops.capacity_open', ['count' => $currentBookings]);
                                        $deadlineLabel = $workshop->registration_deadline
                                            ? $workshop->registration_deadline->copy()->locale($carbonLocale)->translatedFormat($workshopDateFormat)
                                            : null;
                                        $isRegistrationOpen = (bool) $workshop->is_registration_open;
                                        $badgeClass = $isRegistrationOpen ? 'chef-workshop-card__badge' : 'chef-workshop-card__badge is-closed';
                                        $badgeLabel = $isRegistrationOpen
                                            ? __('chef.workshops.badges.open')
                                            : __('chef.workshops.badges.closed');
                                    @endphp
                                    <article class="chef-workshop-card">
                                        <div class="chef-workshop-card__media">
                                            <img src="{{ $coverImage }}" alt="{{ __('chef.workshops.image_alt', ['title' => $workshop->title]) }}" loading="lazy">
                                            <span class="chef-workshop-card__price">{{ $priceLabel }}</span>
                                            <span class="{{ $badgeClass }}">{{ $badgeLabel }}</span>
                                            @if (! $isRegistrationOpen)
                                                <div class="chef-workshop-card__overlay">
                                                    <span>{{ __('chef.workshops.registration_closed') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="chef-workshop-card__body">
                                            <div class="chef-workshop-card__meta">
                                                <span class="chef-workshop-chip {{ $workshop->is_online ? 'is-online' : 'is-offline' }}">
                                                    <i class="fa-solid {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                                    {{ $workshop->is_online ? __('chef.workshops.delivery.online_short') : __('chef.workshops.delivery.in_person_short') }}
                                                </span>
                                                @if ($workshop->max_participants || $workshop->bookings_count)
                                                    <span class="chef-workshop-chip">
                                                        <i class="fa-solid fa-users"></i>
                                                        {{ $capacityLabel }}
                                                    </span>
                                                @endif
                                                @if ($levelLabel)
                                                    <span class="chef-workshop-chip">
                                                        <i class="fa-solid fa-signal"></i>
                                                        {{ __('chef.workshops.level_label', ['level' => $levelLabel]) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="chef-workshop-card__title">{{ $workshop->title }}</h3>
                                            @if (! empty($workshop->instructor))
                                                <p class="chef-workshop-card__lead">{{ __('chef.workshops.with_instructor', ['name' => $workshop->instructor]) }}</p>
                                            @endif
                                            <ul class="chef-workshop-card__details">
                                                <li>
                                                    <i class="fa-solid fa-calendar-day text-amber-500"></i>
                                                    {{ $startDateLabel }}
                                                </li>
                                                <li>
                                                    <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                                    {{ $locationLabel }}
                                                </li>
                                                <li>
                                                    <i class="fa-solid fa-money-bill-wave text-orange-500"></i>
                                                    {{ $priceLabel }}
                                                </li>
                                                @if ($deadlineLabel)
                                                    <li>
                                                        <i class="fa-solid fa-hourglass-half text-slate-500"></i>
                                                        {{ __('chef.workshops.register_until', ['date' => $deadlineLabel]) }}
                                                    </li>
                                                @endif
                                            </ul>
                                            <div class="chef-workshop-card__footer">
                                                @if ($isRegistrationOpen)
                                                    <a href="{{ route('workshop.show', ['workshop' => $workshop->slug]) }}" class="chef-workshop-card__cta">
                                                        {{ __('chef.workshops.book_now') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                                @else
                                                    <span class="chef-workshop-card__cta is-closed">
                                                        {{ __('chef.workshops.registration_closed') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($pastWorkshops->isNotEmpty())
                        <div class="chef-workshops-group">
                            <h3>
                                <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                                {{ __('chef.workshops.past') }}
                            </h3>
                            <div class="chef-workshops-grid">
                                @foreach ($pastWorkshops as $workshop)
                                    @php
                                        $coverImage = $workshop->image
                                            ? asset('storage/' . ltrim($workshop->image, '/'))
                                            : "https://placehold.co/600x400/0f4c73/FFFFFF?text={$workshopPlaceholderText}";
                                        $startDateLabel = $workshop->start_date
                                            ? $workshop->start_date->copy()->locale($carbonLocale)->translatedFormat($workshopDateTimeFormat)
                                            : __('chef.workshops.unscheduled_time');
                                        $locationLabel = $workshop->is_online
                                            ? __('chef.workshops.online_live')
                                            : ($workshop->location ?: __('chef.workshops.location_tbd'));
                                        $priceLabel = $workshop->formatted_price
                                            ?? (number_format((float) ($workshop->price ?? 0), 2) . ' ' . ($workshop->currency ?? 'USD'));
                                        $levelLabels = [
                                            'beginner' => __('chef.workshops.levels.beginner'),
                                            'intermediate' => __('chef.workshops.levels.intermediate'),
                                            'advanced' => __('chef.workshops.levels.advanced'),
                                        ];
                                        $levelLabel = $levelLabels[$workshop->level] ?? null;
                                    @endphp
                                    <article class="chef-workshop-card">
                                        <div class="chef-workshop-card__media">
                                            <img src="{{ $coverImage }}" alt="{{ __('chef.workshops.image_alt', ['title' => $workshop->title]) }}" loading="lazy">
                                            <span class="chef-workshop-card__price">{{ $priceLabel }}</span>
                                            <span class="chef-workshop-card__badge is-closed">{{ __('chef.workshops.badges.completed') }}</span>
                                        </div>
                                        <div class="chef-workshop-card__body">
                                            <div class="chef-workshop-card__meta">
                                                <span class="chef-workshop-chip {{ $workshop->is_online ? 'is-online' : 'is-offline' }}">
                                                    <i class="fa-solid {{ $workshop->is_online ? 'fa-globe' : 'fa-location-dot' }}"></i>
                                                    {{ $workshop->is_online ? __('chef.workshops.delivery.online_short') : __('chef.workshops.delivery.in_person_short') }}
                                                </span>
                                                @if ($levelLabel)
                                                    <span class="chef-workshop-chip">
                                                        <i class="fa-solid fa-signal"></i>
                                                        {{ __('chef.workshops.level_label', ['level' => $levelLabel]) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="chef-workshop-card__title">{{ $workshop->title }}</h3>
                                            @if (! empty($workshop->instructor))
                                                <p class="chef-workshop-card__lead">{{ __('chef.workshops.delivered_by', ['name' => $workshop->instructor]) }}</p>
                                            @endif
                                            <ul class="chef-workshop-card__details">
                                                <li>
                                                    <i class="fa-solid fa-calendar-day text-slate-500"></i>
                                                    {{ $startDateLabel }}
                                                </li>
                                                <li>
                                                    <i class="fa-solid fa-location-dot text-slate-500"></i>
                                                    {{ $locationLabel }}
                                                </li>
                                                <li>
                                                    <i class="fa-solid fa-money-bill-wave text-slate-500"></i>
                                                    {{ $priceLabel }}
                                                </li>
                                            </ul>
                                            <div class="chef-workshop-card__footer">
                                                    <a href="{{ route('workshop.show', ['workshop' => $workshop->slug]) }}" class="chef-workshop-card__cta is-muted">
                                                        {{ __('chef.workshops.view_details') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            <section class="chef-profile-tabs">
                <div class="chef-tab-nav" role="tablist">
                    <button class="chef-tab-btn is-active" data-tab="public" type="button" role="tab" aria-selected="true">
                        {{ __('chef.recipes.tabs.public') }}
                    </button>
                    @if ($canViewExclusive)
                        <button class="chef-tab-btn" data-tab="exclusive" type="button" role="tab" aria-selected="false">
                            {{ __('chef.recipes.tabs.exclusive') }}
                        </button>
                    @endif
                </div>

                <div class="chef-tab-panels">
                    <div class="chef-tab-panel is-active" data-panel="public" role="tabpanel">
                        @if ($publicRecipes->isEmpty())
                            <div class="chef-empty-state">
                                {{ __('chef.recipes.public_empty') }}
                            </div>
                        @else
                            <div class="chef-recipes-grid">
                                @foreach ($publicRecipes as $recipe)
                                    <article class="chef-recipe-card">
                                        <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="chef-recipe-card__cover">
                                            <img src="{{ $recipe->image_url ?? asset('image/brownies.webp') }}" alt="{{ $recipe->title }}" loading="lazy">
                                            @if ($recipe->category)
                                                <span class="chef-recipe-card__tag">
                                                    <i class="fa-solid fa-tag"></i>
                                                    {{ $recipe->category->category_name ?? __('chef.recipes.category_fallback') }}
                                                </span>
                                            @endif
                                        </a>
                                        <div class="chef-recipe-card__body">
                                            <h3 class="chef-recipe-card__title">{{ $recipe->title }}</h3>
                                            <div class="chef-recipe-card__meta">
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-star text-amber-400"></i>
                                                    {{ $recipe->interactions_avg_rating ? number_format($recipe->interactions_avg_rating, 1) : __('chef.recipes.no_rating') }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-bookmark"></i>
                                                    {{ __('chef.recipes.saves', ['count' => number_format($recipe->saved_count ?? 0)]) }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-heart"></i>
                                                    {{ __('chef.recipes.likes', ['count' => number_format($recipe->rating_count ?? 0)]) }}
                                                </span>
                                            </div>
                                            <div class="chef-recipe-card__footer">
                                                <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="chef-recipe-card__cta">
                                                    {{ __('chef.recipes.view_recipe') }}
                                                    <i class="fa-solid {{ $arrowIcon }}"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($canViewExclusive)
                        <div class="chef-tab-panel" data-panel="exclusive" role="tabpanel">
                            @if ($exclusiveRecipes->isEmpty())
                                <div class="chef-empty-state">
                                    {{ __('chef.recipes.exclusive_empty') }}
                                </div>
                            @else
                                <div class="chef-recipes-grid">
                                    @foreach ($exclusiveRecipes as $recipe)
                                        <article class="chef-recipe-card">
                                            <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="chef-recipe-card__cover">
                                                <img src="{{ $recipe->image_url ?? asset('image/brownies.webp') }}" alt="{{ $recipe->title }}" loading="lazy">
                                                <span class="chef-recipe-card__tag">
                                                    <i class="fa-solid fa-lock"></i>
                                                    {{ __('chef.recipes.private_tag') }}
                                                </span>
                                            </a>
                                            <div class="chef-recipe-card__body">
                                                <h3 class="chef-recipe-card__title">{{ $recipe->title }}</h3>
                                                <div class="chef-recipe-card__meta">
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-star text-amber-400"></i>
                                                        {{ $recipe->interactions_avg_rating ? number_format($recipe->interactions_avg_rating, 1) : __('chef.recipes.no_rating') }}
                                                    </span>
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-book-open"></i>
                                                        {{ __('chef.recipes.private_details') }}
                                                    </span>
                                                    <span class="chef-chip">
                                                        <i class="fa-solid fa-shield-halved"></i>
                                                        {{ __('chef.recipes.private_access') }}
                                                    </span>
                                                </div>
                                                <div class="chef-recipe-card__footer">
                                                    <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="chef-recipe-card__cta">
                                                        {{ __('chef.recipes.view_recipe') }}
                                                        <i class="fa-solid {{ $arrowIcon }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            @if ($popularRecipes->isNotEmpty())
                <section class="chef-carousel-section">
                    <div class="chef-carousel-header">
                        <h2>{{ __('chef.popular.title') }}</h2>
                        <span>{{ __('chef.popular.subtitle') }}</span>
                    </div>
                    <div class="swiper chef-popular-swiper">
                        <div class="swiper-wrapper">
                            @foreach ($popularRecipes as $recipe)
                                <div class="swiper-slide">
                                    <article class="chef-popular-card">
                                        <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="chef-popular-card__cover">
                                            <img src="{{ $recipe->image_url ?? asset('image/brownies.webp') }}" alt="{{ $recipe->title }}" loading="lazy">
                                        </a>
                                        <div class="chef-popular-card__body">
                                            <h3 class="chef-popular-card__title">{{ $recipe->title }}</h3>
                                            <div class="chef-popular-card__stats">
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-star text-amber-400"></i>
                                                    {{ $recipe->interactions_avg_rating ? number_format($recipe->interactions_avg_rating, 1) : '—' }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-bookmark"></i>
                                                    {{ __('chef.recipes.saves', ['count' => number_format($recipe->saved_count ?? 0)]) }}
                                                </span>
                                                <span class="chef-chip">
                                                    <i class="fa-solid fa-heart"></i>
                                                    {{ __('chef.recipes.likes', ['count' => number_format($recipe->rating_count ?? 0)]) }}
                                                </span>
                                            </div>
                                            <a href="{{ route('recipe.show', ['recipe' => $recipe->slug]) }}" class="chef-recipe-card__cta">
                                                {{ __('chef.popular.view_details') }}
                                                <i class="fa-solid {{ $arrowIcon }}"></i>
                                            </a>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                        <div class="chef-carousel-pagination swiper-pagination"></div>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @php
    $chefTranslations = array(
        'buttons' => array(
            'follow' => __('chef.hero.buttons.follow'),
            'following' => __('chef.hero.buttons.following'),
        ),
        'follow' => array(
            'error' => __('chef.follow.errors.generic'),
        ),
    );
@endphp
    <script>
        const chefTranslations = @json($chefTranslations);
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.chef-tab-btn');
            const tabPanels = document.querySelectorAll('.chef-tab-panel');

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-tab');

                    tabButtons.forEach((btn) => {
                        const isActive = btn === button;
                        btn.classList.toggle('is-active', isActive);
                        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    tabPanels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
                    });
                });
            });

            const followButton = document.querySelector('[data-follow-button]');
            if (followButton) {
                const followLabel = followButton.querySelector('[data-follow-label]');
                const followIcon = followButton.querySelector('[data-follow-icon]');
                const followersCounter = document.querySelector('[data-followers-count]');
                const numberFormatter = new Intl.NumberFormat(document.documentElement.lang || 'ar');
                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
                const states = {
                    follow: {
                        icon: 'fa-plus',
                        label: chefTranslations.buttons.follow,
                    },
                    following: {
                        icon: 'fa-check',
                        label: chefTranslations.buttons.following,
                    },
                };

                const updateFollowState = (isFollowing) => {
                    followButton.classList.toggle('is-following', isFollowing);
                    const state = isFollowing ? states.following : states.follow;

                    if (followLabel) {
                        followLabel.textContent = state.label;
                    }

                    if (followIcon) {
                        followIcon.classList.remove('fa-plus', 'fa-check');
                        followIcon.classList.add(state.icon);
                    }
                };

                const updateFollowersCount = (value) => {
                    if (!followersCounter) {
                        return;
                    }

                    followersCounter.dataset.followersValue = value;
                    followersCounter.textContent = numberFormatter.format(value);
                };

                const initialFollowersValue = followersCounter
                    ? Number(followersCounter.dataset.followersValue || followersCounter.textContent.replace(/[^0-9.]/g, ''))
                    : NaN;

                if (!Number.isNaN(initialFollowersValue)) {
                    updateFollowersCount(initialFollowersValue);
                }

                followButton.addEventListener('click', async () => {
                    const followUrl = followButton.dataset.followUrl;
                    const unfollowUrl = followButton.dataset.unfollowUrl;

                    if (!followUrl || !unfollowUrl) {
                        return;
                    }

                    if (followButton.dataset.loading === 'true') {
                        return;
                    }

                    const isFollowing = followButton.classList.contains('is-following');
                    const targetUrl = isFollowing ? unfollowUrl : followUrl;
                    const method = isFollowing ? 'DELETE' : 'POST';

                    followButton.dataset.loading = 'true';
                    followButton.disabled = true;

                    try {
                        const response = await fetch(targetUrl, {
                            method,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                ...(method === 'POST' ? { 'Content-Type': 'application/json' } : {}),
                            },
                            body: method === 'POST' ? '{}' : undefined,
                        });

                        let payload = {};

                        try {
                            payload = await response.json();
                        } catch (error) {
                            payload = {};
                        }

                        if (!response.ok) {
                            throw new Error(payload.message || chefTranslations.follow.error);
                        }

                        updateFollowState(Boolean(payload.is_following));

                        if (typeof payload.followers_count === 'number') {
                            updateFollowersCount(payload.followers_count);
                        }
                    } catch (error) {
                        console.error('Follow toggle failed:', error);
                        alert(error.message || chefTranslations.follow.error);
                    } finally {
                        followButton.dataset.loading = 'false';
                        followButton.disabled = false;
                    }
                });

                updateFollowState(followButton.classList.contains('is-following'));
            }

            const swiperElement = document.querySelector('.chef-popular-swiper');
            if (swiperElement && typeof Swiper !== 'undefined') {
                new Swiper(swiperElement, {
                    slidesPerView: 1.1,
                    spaceBetween: 20,
                    breakpoints: {
                        640: { slidesPerView: 1.5, spaceBetween: 22 },
                        768: { slidesPerView: 2.1, spaceBetween: 24 },
                        1024: { slidesPerView: 2.7, spaceBetween: 26 },
                        1280: { slidesPerView: 3.2, spaceBetween: 28 }
                    },
                    loop: false,
                    grabCursor: true,
                    keyboard: { enabled: true },
                    pagination: {
                        el: '.chef-carousel-pagination',
                        clickable: true
                    }
                });
            }
        });
    </script>
@endpush












