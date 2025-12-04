@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $textAlignClass = $isRtl ? 'text-right' : 'text-left';
    $textAlignOppositeClass = $isRtl ? 'text-left' : 'text-right';
    $arrowIcon = $isRtl ? 'fa-arrow-left' : 'fa-arrow-right';
    $notSpecifiedLabel = __('workshops.details.not_specified');
    $levelLabels = [
        'beginner' => __('workshops.details.sidebar.levels.beginner'),
        'advanced' => __('workshops.details.sidebar.levels.advanced'),
    ];
    $difficultyLabels = [
        'easy' => __('workshops.details.recipes.difficulty.easy'),
        'medium' => __('workshops.details.recipes.difficulty.medium'),
        'hard' => __('workshops.details.recipes.difficulty.hard'),
    ];
@endphp

@section('title', $workshop->title . ' - ' . __('workshops.details.title_suffix'))

@push('styles')
<style>
    :root {
        --primary-color: #0819ff;
        --primary-dark: #0b344f;
        --accent-color: #f59e0b;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --bg-soft: #f8fafc;
        --card-bg: #ffffff;
        --radius-lg: 1.5rem;
        --radius-md: 1rem;
        --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px -10px rgba(0,0,0,0.1);
    }

    .workshop-details-wrapper {
        font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif;
        color: var(--text-main);
        background-color: var(--bg-soft);
    }

    /* Modern Hero Section */
    .workshop-hero-container {
        padding: 3rem 1.25rem 0;
        max-width: 1400px;
        margin: 1.25rem auto 0;
    }

    .workshop-hero {
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background:
            radial-gradient(circle at 20% 20%, rgba(147, 197, 253, 0.28), transparent 32%),
            linear-gradient(135deg, #0b3b5a 0%, #0a3f73 36%, #0c5ad5 78%, #0f172a 100%);
        background-size: cover !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        color: white;
        box-shadow: var(--shadow-soft);
        min-height: 420px;
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        align-items: stretch;
        padding: 0 !important;
    }

    .workshop-hero-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
    }

    .workshop-hero-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.4;
        filter: blur(10px);
        transform: scale(1.1);
    }

    .workshop-hero-bg::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to right,
            rgba(8, 47, 73, 0.9) 22%,
            rgba(14, 116, 144, 0.82) 58%,
            rgba(12, 90, 213, 0.78) 100%
        );
    }

    .workshop-hero-content {
        position: relative;
        z-index: 10;
        padding: 2.75rem;
        width: 100%;
        max-width: 720px;
        flex: 1 1 520px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #e2e8f0;
        margin-bottom: 1.5rem;
        width: fit-content;
    }

    .workshop-title {
        font-size: clamp(1.85rem, 3vw, 2.35rem);
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 1.1rem;
        background: linear-gradient(90deg, #ffffff 0%, #ecf2ff 45%, #c7d2fe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .workshop-excerpt-wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 2rem;
        max-width: 600px;
    }

    .workshop-excerpt {
        font-size: 1rem;
        line-height: 1.7;
        color: #f8fafc;
        margin: 0;
    }

    .workshop-excerpt.collapsed {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        position: relative;
    }

    .workshop-excerpt.collapsed::after {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 40%;
        background: linear-gradient(180deg, transparent, rgba(8, 47, 73, 0.62));
        pointer-events: none;
    }

    .workshop-excerpt-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.85rem;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #fff7ed;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 999px;
        backdrop-filter: blur(8px);
        transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
    }

    .workshop-excerpt-toggle:hover {
        background: rgba(255, 255, 255, 0.24);
        border-color: rgba(255, 255, 255, 0.35);
        transform: translateY(-1px);
    }

    .workshop-excerpt-toggle i {
        font-size: 0.85rem;
    }

    .hero-instructor {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .instructor-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        background: #e8f1ff;
        border: 1px solid #c7d2fe;
        border-radius: 14px;
        color: #0b1f4a;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        width: fit-content;
        transition: transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 10px 22px -18px rgba(12, 74, 110, 0.45);
    }

    .instructor-chip:hover {
        border-color: #93c5fd;
        transform: translateY(-1px);
        box-shadow: 0 14px 28px -18px rgba(12, 74, 110, 0.55);
    }

    .instructor-chip .chip-icon {
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 999px;
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #0b1f4a;
        font-size: 0.95rem;
    }

    .instructor-chip .chip-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .instructor-chip .chip-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #2563eb;
    }

    .instructor-chip .chip-name {
        color: #0b1f4a;
        font-weight: 800;
        font-size: 0.92rem;
    }

    .instructor-chip .chip-arrow {
        color: #2563eb;
        font-size: 0.85rem;
    }

    .hero-stat-link {
        color: #0f172a;
        text-decoration: none;
        border-bottom: 1px dotted #cbd5e1;
        padding-bottom: 2px;
    }

    .hero-stat-link:hover {
        color: #0f172a;
        border-bottom-color: #0f172a;
    }

    .hero-stats {
        position: relative;
        margin-bottom: 1.6rem;
        padding: 1rem 1.05rem;
        border-radius: 1.1rem;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        box-shadow: 0 18px 36px -26px rgba(15, 23, 42, 0.32);
        overflow: hidden;
        isolation: isolate;
    }

    .hero-stats::before,
    .hero-stats::after {
        content: none;
        display: none;
    }

    .hero-stats-header {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .hero-stats-headline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        width: 100%;
        flex-wrap: wrap;
    }

    .hero-stats-title {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        color: #0f172a;
    }

    .hero-stats-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #2563eb;
        border: 1px solid #93c5fd;
        box-shadow: 0 0 0 6px rgba(147, 197, 253, 0.18);
    }

    .hero-stats-subtitle {
        margin: 0;
        color: #475569;
        font-size: 0.95rem;
        max-width: 540px;
    }

    .hero-stats-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 0.65rem;
        margin-top: 1rem;
    }

    .hero-stat-item {
        --accent-bg: #f5f8ff;
        --accent-border: #d6e4ff;
        --accent-ink: #0b1f4a;
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 1.05rem;
        border-radius: 1.1rem;
        background: linear-gradient(140deg, #ffffff, var(--accent-bg));
        border: 1px solid var(--accent-border);
        box-shadow:
            0 0 0 1px rgba(214, 228, 255, 0.6),
            0 12px 30px -20px rgba(11, 31, 74, 0.25);
        overflow: hidden;
        z-index: 1;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .hero-stat-item:hover {
        transform: translateY(-2px);
        border-color: #c5d8ff;
        box-shadow:
            0 0 0 1px rgba(197, 216, 255, 0.8),
            0 16px 36px -18px rgba(11, 31, 74, 0.35);
        background: linear-gradient(140deg, #ffffff, #f0f5ff);
    }

    .hero-stat-item::after {
        content: '';
        display: none;
    }

    .hero-stat-item.accent-amber,
    .hero-stat-item.accent-cyan,
    .hero-stat-item.accent-emerald,
    .hero-stat-item.accent-indigo,
    .hero-stat-item.accent-rose {
        --accent-bg: #f5f8ff;
        --accent-border: #d6e4ff;
        --accent-ink: #0b1f4a;
    }

    .hero-stat-item > * {
        position: relative;
        z-index: 1;
    }

    .hero-stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.9rem;
        border: 1px solid var(--accent-border);
        background: linear-gradient(145deg, #ffffff, #eef4ff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        color: #0b1f4a;
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.82),
            0 12px 18px -16px rgba(15, 23, 42, 0.35);
    }

    .hero-stat-text {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .hero-stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
    }

    .hero-stat-value {
        font-weight: 800;
        font-size: 1.02rem;
        color: var(--accent-ink);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        line-height: 1.35;
    }

    .hero-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        width: fit-content;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        border: 1px solid var(--accent-border);
        background: var(--accent-bg);
        color: var(--accent-ink);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .hero-stat-chip i {
        color: var(--accent-ink);
        font-size: 0.9rem;
    }

    .workshop-hero-visual {
        position: relative;
        z-index: 10;
        flex: 1 1 380px;
        min-width: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 2.5rem;
    }

    .hero-image-glow {
        position: absolute;
        inset: 18%;
        background: radial-gradient(circle at 20% 20%, rgba(147, 197, 253, 0.45), transparent 45%),
                    radial-gradient(circle at 85% 30%, rgba(59, 130, 246, 0.38), transparent 45%),
                    radial-gradient(circle at 40% 80%, rgba(14, 165, 233, 0.35), transparent 50%);
        filter: blur(40px);
        opacity: 0.85;
        z-index: 0;
    }

    .hero-image-card {
        position: relative;
        width: 100%;
        max-width: 680px;
        aspect-ratio: 16 / 10;
        border-radius: 1.5rem;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.04));
        border: 1px solid rgba(255, 255, 255, 0.24);
        box-shadow: 0 32px 70px -32px rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(10px);
        isolation: isolate;
    }

    .hero-image-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(1.02);
        transition: transform 0.5s ease;
    }

    .hero-image-card:hover img {
        transform: scale(1.06);
    }

    .hero-image-chip {
        position: absolute;
        top: 1.25rem;
        left: 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.6rem 0.9rem;
        background: rgba(11, 52, 79, 0.75);
        border-radius: 10px;
        font-weight: 700;
        color: #e2e8f0;
        font-size: 0.85rem;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.25);
        z-index: 2;
    }

    .hero-image-footer {
        position: absolute;
        inset: auto 0 0 0;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        padding: 1.1rem 1.35rem;
        background: linear-gradient(180deg, transparent 0%, rgba(11, 52, 79, 0.9) 65%);
        color: #e2e8f0;
        z-index: 1;
    }

    .hero-image-meta {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .hero-meta-label {
        font-size: 0.75rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(226, 232, 240, 0.78);
    }

    .hero-meta-value {
        font-weight: 800;
        font-size: 1rem;
        color: #fff;
    }

    /* Main Content Layout */
    .content-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 3rem 1rem;
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 3rem;
    }

    /* Cards */
    .content-card {
        background: white;
        border-radius: var(--radius-md);
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-soft);
        border: 1px solid #f1f5f9;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-icon {
        color: var(--primary-color);
        background: #e0f2fe;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .prose {
        color: var(--text-muted);
        line-height: 1.8;
        font-size: 1.05rem;
    }

    /* Booking Sidebar */
    .booking-sidebar {
        position: sticky;
        top: 2rem;
    }

    .booking-card {
        background: white;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 20px 50px -10px rgba(8, 25, 255, 0.15);
        border: 1px solid rgba(8, 25, 255, 0.1);
    }

    .booking-header {
        background: var(--primary-color);
        padding: 2rem;
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .booking-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.1), transparent);
    }

    .booking-price {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        position: relative;
    }

    .booking-body {
        padding: 2rem;
    }

    .booking-features {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .booking-feature {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 1rem;
        transition: transform 0.2s;
    }

    .booking-feature:hover {
        transform: translateX(-5px);
        background: #f1f5f9;
    }

    .feature-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .booking-btn {
        width: 100%;
        padding: 1.25rem;
        border-radius: 1rem;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        box-shadow: 0 10px 20px -5px rgba(8, 25, 255, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(8, 25, 255, 0.4);
    }

    /* RTL Support */
    html[dir="rtl"] .workshop-hero-bg::after {
        background: linear-gradient(
            to left,
            rgba(8, 47, 73, 0.9) 22%,
            rgba(14, 116, 144, 0.82) 58%,
            rgba(12, 90, 213, 0.78) 100%
        );
    }

    html[dir="rtl"] .booking-feature:hover {
        transform: translateX(5px);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .content-wrapper {
            grid-template-columns: 1fr;
        }

        .workshop-hero {
            flex-direction: column;
        }

        .workshop-hero-visual {
            padding: 2rem 2.5rem;
            width: 100%;
        }
        
        .booking-sidebar {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .workshop-hero-container {
            padding-top: 3.25rem;
            margin-top: 1rem;
            padding-inline: 0.9rem;
        }

        .workshop-hero {
            min-height: 260px;
            gap: 0;
            border-radius: 1.5rem;
        }

        .workshop-hero-content {
            padding: 0.85rem 0.75rem 0.35rem;
        }

        .hero-badge {
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .workshop-hero-visual {
            padding: 0 0.5rem 0.5rem;
            margin-top: -0.5rem;
        }

        .hero-image-card {
            max-width: 380px;
            max-height: 380px;
        }
        
        .workshop-title {
            font-size: 1.35rem;
            margin-bottom: 0.3rem;
            line-height: 1.2;
        }

        .workshop-excerpt-wrapper {
            margin-bottom: 1rem;
            gap: 0.45rem;
        }

        .workshop-excerpt {
            margin-bottom: 0.35rem;
            font-size: 0.88rem;
            line-height: 1.4;
        }

        .workshop-excerpt-toggle {
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            font-size: 0.78rem;
        }

        .workshop-excerpt-toggle i {
            font-size: 0.75rem;
        }
        
        .hero-stats {
            padding: 0.75rem 0.8rem;
            margin-bottom: 0.7rem;
        }

        .hero-stats-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
        }

        .hero-stats-title {
            font-size: 0.95rem;
        }

        .hero-stats-subtitle {
            font-size: 0.85rem;
        }

        .hero-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem;
            margin-top: 0.65rem;
        }

        .hero-stat-item {
            gap: 0.55rem;
            padding: 0.7rem 0.8rem;
            border-radius: 0.9rem;
            background: linear-gradient(140deg, #ffffff, #f5f8ff);
            border: 1px solid var(--accent-border);
        }

        .hero-stat-icon {
            width: 2rem;
            height: 2rem;
            font-size: 0.9rem;
        }

        .hero-stat-label {
            font-size: 0.68rem;
        }

        .hero-stat-value {
            font-size: 0.92rem;
        }

        .hero-image-footer {
            grid-template-columns: 1fr;
        }

        .hero-instructor {
            margin-inline-start: 0;
            width: 100%;
            justify-content: flex-start;
            order: 3;
        }
    }

    @media (max-width: 640px) {
        .workshop-hero-container {
            padding-top: 2.4rem;
        }

        .workshop-hero {
            min-height: 240px;
        }

        .workshop-hero-content {
            padding: 0.65rem 0.65rem 0.25rem;
        }

        .workshop-excerpt-wrapper {
            margin-bottom: 0.8rem;
        }

        .hero-stats {
            padding: 0.7rem;
        }

        .hero-stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .hero-stat-item {
            padding: 0.65rem 0.75rem;
            background: linear-gradient(140deg, #ffffff, #f5f8ff);
            border: 1px solid var(--accent-border);
        }

        .hero-stat-icon {
            width: 1.9rem;
            height: 1.9rem;
            font-size: 0.85rem;
        }

        .instructor-chip {
            padding: 0.6rem 0.9rem;
        }

        .workshop-title {
            font-size: 1.28rem;
        }

        .hero-image-card {
            max-width: 380px;
            max-height: 380px;
        }
    }
    
    /* Recipe Cards */
    .recipe-card {
        background: white;
        border-radius: 1.25rem;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    
    .recipe-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }
    
    .recipe-image-wrapper {
        position: relative;
        padding-top: 65%;
        overflow: hidden;
    }
    
    .recipe-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .recipe-card:hover .recipe-image {
        transform: scale(1.05);
    }
    
    .recipe-content {
        padding: 1.5rem;
    }
    
    .recipe-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-main);
    }
    
    .recipe-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 1rem;
    }

    /* Floating Bar */
    .floating-booking-bar {
        position: fixed;
        inset-inline-start: 50%;
        bottom: var(--floating-booking-mobile-offset, 1.1rem);
        transform: translate3d(-50%, 0, 0);
        background: rgba(255, 255, 255, 0.96);
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 50;
        border: 1px solid rgba(0,0,0,0.05);
        width: 82%;
        max-width: 440px;
        transition:
            transform 0.45s cubic-bezier(0.4, 0, 0.2, 1),
            opacity 0.3s ease,
            box-shadow 0.3s ease;
        will-change: transform, opacity;
        backdrop-filter: blur(12px);
    }

    .floating-booking-bar.is-hidden {
        transform: translate3d(-50%, 130%, 0);
        opacity: 0;
        pointer-events: none;
    }

    .floating-booking-bar .floating-content {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        animation: floating-bar-fade-up 0.45s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    .floating-booking-bar .floating-info {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        color: #0f172a;
    }

    .floating-booking-bar .floating-price {
        font-weight: 800;
        font-size: 0.96rem;
    }

    .floating-booking-bar .floating-meta {
        font-size: 0.8rem;
        color: #475569;
    }

    .floating-booking-bar .floating-btn {
        min-width: 108px;
        padding: 0.5rem 0.9rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        background: linear-gradient(135deg, #0819ff, #1296d9);
        color: #fff;
        box-shadow: 0 14px 32px rgba(8, 25, 255, 0.28);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .floating-booking-bar .floating-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(8, 25, 255, 0.32);
    }

    .floating-booking-bar .floating-btn i {
        font-size: 0.85rem;
    }

    @keyframes floating-bar-fade-up {
        0% {
            opacity: 0;
            transform: translate3d(0, 26%, 0);
        }
        70% {
            opacity: 1;
            transform: translate3d(0, -4%, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (max-width: 768px) {
        .floating-booking-bar {
            width: calc(100% - 2.5rem);
            padding: 0.6rem 1rem;
        }
    }
    
    @media (min-width: 1024px) {
        .floating-booking-bar {
            display: none;
        }
    }

    /* Additional details + related styling */
    .additional-details-card {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
    }

    .additional-details-card::before,
    .additional-details-card::after {
        content: none;
    }

    .additional-details-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .additional-details-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        background: linear-gradient(140deg, var(--primary-color), #1296d9);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 30px rgba(8, 25, 255, 0.28);
    }

    .additional-details-title {
        font-weight: 800;
        color: var(--primary-dark);
        margin: 0;
        line-height: 1.2;
    }

    .additional-details-sub {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin: 0.15rem 0 0;
    }

    .additional-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 0.85rem;
    }

    .additional-detail {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        padding: 0.9rem 1rem;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 1rem;
        border: 1px solid rgba(8, 25, 255, 0.1);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(8px);
    }

    .additional-detail-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, var(--primary-color), #1296d9);
        color: #fff;
        box-shadow: 0 12px 22px rgba(8, 25, 255, 0.24);
    }

    .additional-detail-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
    }

    .additional-detail-value {
        font-weight: 700;
        color: var(--primary-dark);
        margin: 0;
    }

    .related-workshops-section {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        padding-block: 1rem 3rem;
    }

    .related-workshop-card {
        border-radius: 1.25rem;
        overflow: hidden;
        border: 1px solid rgba(8, 25, 255, 0.08);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        background: #ffffff;
    }

    .related-workshop-card a {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .related-workshop-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 52px rgba(8, 25, 255, 0.16);
    }

    .related-workshop-card .p-6 {
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 0.9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .related-price {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        color: #0b344f;
    }

    .related-cta {
        color: #c2410c;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
@php
    $notSpecifiedLabel = $notSpecifiedLabel ?? __('workshops.details.not_specified');
    $showAdminMetrics = auth()->check() && auth()->user()->isAdmin();
    $stripePublicKey = config('services.stripe.public_key');
    $stripeEnabled = $stripePublicKey && config('services.stripe.secret_key');
    $onlinePaymentsEnabled = $stripeEnabled;
    $whatsappBookingEnabled = data_get($whatsappBookingConfig ?? [], 'enabled', false);
    $whatsappBookingPayload = [
        'isLoggedIn' => data_get($whatsappBookingConfig ?? [], 'isLoggedIn', false),
        'whatsappNumber' => data_get($whatsappBookingConfig ?? [], 'number'),
        'bookingEndpoint' => data_get($whatsappBookingConfig ?? [], 'bookingEndpoint'),
        'bookingNotes' => data_get($whatsappBookingConfig ?? [], 'notes'),
        'loginUrl' => data_get($whatsappBookingConfig ?? [], 'loginUrl'),
        'registerUrl' => data_get($whatsappBookingConfig ?? [], 'registerUrl'),
        'user' => data_get($whatsappBookingConfig ?? [], 'user', []),
    ];
    $instructorName = trim((string) ($workshop->chef?->name ?? $workshop->instructor ?? ''));
    if ($instructorName === '') {
        $instructorName = null;
    }
    $instructorPublicIdentifier = $workshop->chef?->username
        ?? $workshop->chef?->slug
        ?? $workshop->chef?->handle
        ?? $workshop->chef?->referral_code
        ?? $workshop->chef?->id;
    $instructorPublicUrl = $instructorPublicIdentifier
        ? route('chef.public.workshops', ['username' => $instructorPublicIdentifier])
        : null;
    $displayedInstructorName = $instructorName ?? $notSpecifiedLabel;
    $whatsappPriceLabel = trim($workshop->formatted_price ?? ($workshop->price.' '.$workshop->currency));
    $whatsappDateLabel = $workshop->start_date
        ? $workshop->start_date->format('d/m/Y h:i A')
        : $notSpecifiedLabel;
    $whatsappDeadlineLabel = $workshop->registration_deadline
        ? $workshop->registration_deadline->format('d/m/Y')
        : $notSpecifiedLabel;
    $whatsappInstructorLabel = $displayedInstructorName;
    $whatsappLocationLabel = $workshop->is_online
        ? __('workshops.labels.online_workshop')
        : ($workshop->location ?? $notSpecifiedLabel);
    $whatsappTopicsLabel = $workshop->what_you_will_learn
        ? (string) \Illuminate\Support\Str::of($workshop->what_you_will_learn)->stripTags()->squish()
        : $notSpecifiedLabel;
    $whatsappRequirementsLabel = $workshop->requirements
        ? (string) \Illuminate\Support\Str::of($workshop->requirements)->stripTags()->squish()
        : $notSpecifiedLabel;
    $whatsappDurationLabel = $workshop->duration
        ? $workshop->formatted_duration
        : $notSpecifiedLabel;
    $legalTermsUrl = config('legal.terms_url') ?: route('legal.terms');
    $whatsappTermsLabel = __('workshops.whatsapp.terms_fallback', ['url' => $legalTermsUrl]);
    $whatsappNumberDigits = preg_replace('/\D+/', '', data_get($whatsappBookingConfig ?? [], 'number', ''));
    $whatsappQuickMessage = app()->isLocale('ar')
        ? "مرحباً، أود حجز ورشة {$workshop->title} بتاريخ {$whatsappDateLabel} بقيمة {$whatsappPriceLabel}."
        : "Hello, I'd like to book the workshop \"{$workshop->title}\" on {$whatsappDateLabel} (price {$whatsappPriceLabel}).";
    $whatsappQuickLink = $whatsappNumberDigits
        ? 'https://wa.me/'.$whatsappNumberDigits.'?text='.rawurlencode($whatsappQuickMessage)
        : null;
    $isWorkshopFull = $workshop->max_participants && $workshop->bookings_count >= $workshop->max_participants;
    $userBookedViaWhatsapp = (bool) ($userBooking?->is_whatsapp_booking ?? false);
    $whatsappPendingApproval = $userBookedViaWhatsapp && ($userBooking?->status !== 'confirmed');
    $whatsappButtonAvailable = $whatsappBookingEnabled
        && !$workshop->is_completed
        && $workshop->is_registration_open
        && !$isWorkshopFull
        && (empty($userBooking) || $userBookedViaWhatsapp);
    $showFloatingBookingButton = !$userBooking
        && !$workshop->is_completed
        && $workshop->is_registration_open
        && !($isWorkshopFull || $workshop->is_fully_booked);
    $workshopDateLabel = $workshop->start_date ? $workshop->start_date->format('d/m/Y') : $notSpecifiedLabel;
    $workshopStartTimeLabel = $workshop->start_date ? $workshop->start_date->format('g:i A') : $notSpecifiedLabel;
    $workshopEndTimeLabel = $workshop->end_date ? $workshop->end_date->format('g:i A') : $notSpecifiedLabel;
    $workshopStartDateTimeLabel = $workshop->start_date ? $workshop->start_date->format('d/m/Y g:i A') : $notSpecifiedLabel;
    $workshopEndDateTimeLabel = $workshop->end_date ? $workshop->end_date->format('d/m/Y g:i A') : $notSpecifiedLabel;
    $workshopStartIso = optional($workshop->start_date)->toIso8601String();
    $workshopEndIso = optional($workshop->end_date)->toIso8601String();
    $workshopDurationLabel = $workshop->duration
        ? __('workshops.details.recipes.minutes', ['count' => $workshop->duration])
        : $notSpecifiedLabel;
    $hostTimezoneName = $workshop->host_timezone
        ?? optional($workshop->start_date)?->getTimezone()?->getName()
        ?? config('app.timezone', 'UTC');
    $hostTimezonePretty = $hostTimezoneName ? str_replace('_', ' ', $hostTimezoneName) : null;
    $hostOffset = $workshop->start_date
        ? sprintf('GMT%s', $workshop->start_date->format('P'))
        : 'GMT+00:00';
    $hostTimezoneDisplay = $workshop->start_date
        ? __('workshops.details.timezones.host_timezone_template', [
            'label' => __('workshops.details.timezones.host_label'),
            'date' => $workshopStartDateTimeLabel,
            'offset' => $hostOffset,
            'timezone' => $hostTimezonePretty ?? 'UTC',
        ])
        : null;
    $workshopDeadlineLabel = $workshop->registration_deadline ? $workshop->registration_deadline->format('d/m/Y') : $notSpecifiedLabel;
    $workshopLocationLabel = $whatsappLocationLabel;
    $bookingStatusPill = [
        'label' => __('workshops.details.booking_status.open'),
        'classes' => 'bg-white/20 text-white border-white/30',
    ];
    if ($workshop->is_completed) {
        $bookingStatusPill = [
            'label' => __('workshops.details.booking_status.completed'),
            'classes' => 'bg-gray-900/30 text-white border-white/20',
        ];
    } elseif ($isWorkshopFull || $workshop->is_fully_booked) {
        $bookingStatusPill = [
            'label' => __('workshops.details.booking_status.full'),
            'classes' => 'bg-rose-100 text-rose-700 border-rose-200',
        ];
    } elseif (! $workshop->is_registration_open) {
        $bookingStatusPill = [
            'label' => __('workshops.details.booking_status.closed'),
            'classes' => 'bg-amber-100 text-amber-700 border-amber-200',
        ];
    } elseif ($userBooking) {
        $bookingStatusPill = [
            'label' => __('workshops.details.booking_status.booked'),
            'classes' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        ];
    }
    $heroImageUrl = $workshop->image
        ? asset('storage/' . $workshop->image)
        : 'https://placehold.co/800x600/f87171/FFFFFF?text=' . urlencode(__('workshops.labels.featured_placeholder_text'));
    $heroDescriptionFull = (string) \Illuminate\Support\Str::of($workshop->description ?? '')->stripTags()->squish();
    $heroDescriptionPreview = \Illuminate\Support\Str::limit($heroDescriptionFull, 220, '...');
    $heroDescriptionIsTrimmed = $heroDescriptionPreview !== $heroDescriptionFull;
    $heroToggleLabels = [
        'more' => $isRtl ? 'عرض المزيد' : 'Show more',
        'less' => $isRtl ? 'عرض أقل' : 'Show less',
    ];
@endphp
<div class="workshop-details-wrapper min-h-screen" style="background-color: #f3f4f6;">
    <!-- Workshop Hero Section -->
    <section class="workshop-hero-container">
        <div class="workshop-hero">
            <div class="workshop-hero-bg">
                <img src="{{ $heroImageUrl }}" 
                     alt="{{ $workshop->title }}"
                     onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';">
            </div>
            
            <div class="workshop-hero-content">
                @if($workshop->is_featured)
                    <div class="hero-badge">
                        <i class="fas fa-star text-yellow-400"></i>
                        <span>{{ __('workshops.details.hero.featured_badge') }}</span>
                    </div>
                @endif

                <h1 class="workshop-title">
                    {{ $workshop->title }}
                </h1>

                @if($heroDescriptionFull !== '')
                    <div class="workshop-excerpt-wrapper">
                        <p
                            id="workshopHeroExcerpt"
                            class="workshop-excerpt {{ $heroDescriptionIsTrimmed ? 'collapsed' : '' }}"
                            data-preview-text="{{ e($heroDescriptionPreview) }}"
                            data-full-text="{{ e($heroDescriptionFull) }}"
                            data-expanded="false"
                        >
                            {{ $heroDescriptionIsTrimmed ? $heroDescriptionPreview : $heroDescriptionFull }}
                        </p>
                        @if($heroDescriptionIsTrimmed)
                            <button
                                type="button"
                                class="workshop-excerpt-toggle"
                                data-toggle="workshop-hero-excerpt"
                                data-more-label="{{ $heroToggleLabels['more'] }}"
                                data-less-label="{{ $heroToggleLabels['less'] }}"
                            >
                                <span class="toggle-label">{{ $heroToggleLabels['more'] }}</span>
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>
                @endif

                <div class="hero-stats">
                    <div class="hero-stats-header">
                        <div class="hero-stats-headline">
                            <div class="hero-stats-title">
                                <span class="hero-stats-dot"></span>
                                <span>{{ $isRtl ? 'نظرة سريعة' : 'Quick snapshot' }}</span>
                            </div>
                            @if($instructorName)
                                <div class="hero-instructor">
                                    @if($instructorPublicUrl)
                                        <a href="{{ $instructorPublicUrl }}" class="instructor-chip">
                                            <div class="chip-icon">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                            <div class="chip-text">
                                                <span class="chip-label">{{ __('workshops.details.hero.instructor_label') }}</span>
                                                <span class="chip-name">{{ $displayedInstructorName }}</span>
                                            </div>
                                            <i class="fas {{ $arrowIcon }} chip-arrow" aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <div class="instructor-chip" aria-disabled="true">
                                            <div class="chip-icon">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                            <div class="chip-text">
                                                <span class="chip-label">{{ __('workshops.details.hero.instructor_label') }}</span>
                                                <span class="chip-name">{{ $displayedInstructorName }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <p class="hero-stats-subtitle">
                            {{ $isRtl ? 'مواعيد الورشة، المدرب والتقييم في لمحة واحدة.' : 'Workshop timing, instructor, and rating at a glance.' }}
                        </p>
                    </div>

                    <div class="hero-stats-grid">
                        <!-- Start -->
                        <div class="hero-stat-item accent-amber">
                            <div class="hero-stat-icon">
                                <i class="fas fa-play"></i>
                            </div>
                            <div class="hero-stat-text">
                                <span class="hero-stat-label">{{ __('workshops.details.hero.start_label') }}</span>
                                <span class="hero-stat-value">{{ $workshopStartDateTimeLabel }}</span>
                            </div>
                        </div>

                        <!-- End -->
                        <div class="hero-stat-item accent-cyan">
                            <div class="hero-stat-icon">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div class="hero-stat-text">
                                <span class="hero-stat-label">{{ __('workshops.details.hero.end_label') }}</span>
                                <span class="hero-stat-value">{{ $workshopEndDateTimeLabel }}</span>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="hero-stat-item accent-emerald">
                            <div class="hero-stat-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="hero-stat-text">
                                <span class="hero-stat-label">{{ __('workshops.details.hero.duration_label') }}</span>
                                <span class="hero-stat-value">{{ $workshopDurationLabel }}</span>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="hero-stat-item accent-rose">
                            <div class="hero-stat-icon">
                                <i class="fas fa-star text-amber-300"></i>
                            </div>
                            <div class="hero-stat-text">
                                <span class="hero-stat-label">{{ __('workshops.details.hero.rating_label') }}</span>
                                <span class="hero-stat-value">
                                    {{ $reviewSummary['average'] ? number_format($reviewSummary['average'], 1) : '—' }}
                                    <span class="hero-stat-chip">
                                        <i class="fas fa-user-check" aria-hidden="true"></i>
                                        {{ __('workshops.details.hero.reviews_count', ['count' => number_format($reviewSummary['count'])]) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="workshop-hero-visual">
                <div class="hero-image-glow" aria-hidden="true"></div>
                <div class="hero-image-card">
                    <img src="{{ $heroImageUrl }}"
                         alt="{{ $workshop->title }}"
                         onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';">
                </div>
            </div>
        </div>
    </section>

    <!-- Workshop Details -->
    <div class="content-wrapper">
        <!-- Main Content -->
        <div class="space-y-8">
            <!-- About Workshop -->
            <div class="content-card">
                <h2 class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    {{ __('workshops.details.sections.about') }}
                </h2>
                <div class="prose">
                    {!! $workshop->content !!}
                </div>
            </div>

            @if($workshop->what_you_will_learn)
                <div class="content-card">
                    <h2 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        {{ __('workshops.details.sections.learn') }}
                    </h2>
                    <div class="prose">
                        {!! $workshop->what_you_will_learn !!}
                    </div>
                </div>
            @endif

            @if($workshop->requirements)
                <div class="content-card">
                    <h2 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-list-check"></i>
                        </div>
                        {{ __('workshops.details.sections.requirements') }}
                    </h2>
                    <div class="prose">
                        {!! $workshop->requirements !!}
                    </div>
                </div>
            @endif

            @if($workshop->materials_needed)
                <div class="content-card">
                    <h2 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        {{ __('workshops.details.sections.materials') }}
                    </h2>
                    <div class="prose">
                        {!! $workshop->materials_needed !!}
                    </div>
                </div>
            @endif

            @if($workshop->recipes && $workshop->recipes->count() > 0)
                <div class="content-card">
                    <h2 class="section-title">
                        <div class="section-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        {{ __('workshops.details.sections.recipes') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($workshop->recipes as $recipe)
                            @php
                                $recipeMinutes = (int) ($recipe->prep_time ?? 0) + (int) ($recipe->cook_time ?? 0);
                            @endphp
                            <div class="recipe-card">
                                <a href="{{ route('recipe.show', $recipe->slug) }}" class="block group">
                                    <div class="recipe-image-wrapper">
                                        <img src="{{ $recipe->image_url ?: 'https://placehold.co/400x300/f87171/FFFFFF?text=' . urlencode(__('workshops.details.recipes.placeholder')) }}" 
                                             alt="{{ $recipe->title }}" 
                                             class="recipe-image"
                                            onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';">
                                    </div>
                                    <div class="recipe-content">
                                        <h3 class="recipe-title group-hover:text-amber-600 transition-colors">
                                            {{ $recipe->title }}
                                        </h3>
                                        <p class="text-sm text-gray-500 line-clamp-2">
                                            {{ Str::limit($recipe->description, 100) }}
                                        </p>
                                        <div class="recipe-meta">
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-clock text-amber-500"></i>
                                                <span>{{ __('workshops.details.recipes.minutes', ['count' => $recipeMinutes]) }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-signal text-amber-500"></i>
                                                <span>{{ $difficultyLabels[$recipe->difficulty] ?? $difficultyLabels['medium'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="booking-sidebar">
            <div id="workshop-booking" class="booking-card">
                <div class="booking-header">
                    <div class="flex justify-between items-start mb-4">
                        <span class="booking-status-pill {{ $bookingStatusPill['classes'] }}">
                            <i class="fas fa-circle text-[0.6rem]"></i>
                            {{ $bookingStatusPill['label'] }}
                        </span>
                    </div>
                    
                    <div class="booking-price">
                        {{ $workshop->formatted_price }}
                    </div>
                    <p class="text-white/80 text-sm">{{ __('workshops.details.booking_card.price_hint') }}</p>
                </div>

                <div class="booking-body">
                    <div class="booking-features">
                        <div class="booking-feature">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ __('workshops.details.booking_card.summary.date') }}</p>
                                <p class="font-bold text-gray-900">{{ $workshopDateLabel }}</p>
                            </div>
                        </div>

                        <div class="booking-feature">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ __('workshops.details.booking_card.summary.start_time') }}</p>
                                <p class="font-bold text-gray-900">{{ $workshopStartTimeLabel }}</p>
                            </div>
                        </div>

                        <div class="booking-feature">
                            <div class="feature-icon">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ __('workshops.details.booking_card.summary.end_time') }}</p>
                                <p class="font-bold text-gray-900">{{ $workshopEndDateTimeLabel }}</p>
                            </div>
                        </div>

                        <div class="booking-feature">
                            <div class="feature-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">{{ __('workshops.details.booking_card.summary.location') }}</p>
                                <p class="font-bold text-gray-900">{{ $workshopLocationLabel }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="booking-actions" id="booking-methods">
                        <div id="booking-primary-action">
                        @if($workshop->is_completed)
                            <button class="booking-btn bg-gray-100 text-gray-400 cursor-not-allowed">
                                <i class="fas fa-check-circle"></i>
                                {{ __('workshops.details.booking_card.cta_completed') }}
                            </button>
                        @elseif($userBooking)
                            @if($userBooking->status === 'confirmed')
                                @if($workshop->is_online && $workshop->meeting_link && $userBooking->public_code)
                                    <a href="{{ $userBooking->secure_join_url }}" class="booking-btn btn-primary">
                                        <i class="fas fa-video"></i>
                                        <span>{{ __('workshops.details.booking_card.cta_join') }}</span>
                                    </a>
                                    <p class="booking-action-hint">
                                        {{ __('workshops.details.booking_card.join_room_hint') }}
                                    </p>
                                @else
                                    <button class="booking-btn bg-emerald-50 text-emerald-600 border border-emerald-100 cursor-not-allowed" disabled>
                                        <i class="fas fa-check"></i>
                                        <span>{{ __('workshops.details.booking_card.cta_confirmed') }}</span>
                                    </button>
                                    <p class="booking-action-hint">
                                        {{ __('workshops.details.booking_card.confirmation_hint') }}
                                    </p>
                                @endif
                            @else
                                <button class="booking-btn bg-amber-50 text-amber-600 border border-amber-100 cursor-not-allowed" disabled>
                                    <i class="fas fa-hourglass-half"></i>
                                    <span>{{ __('workshops.details.booking_card.cta_pending') }}</span>
                                </button>
                                <p class="booking-action-hint">
                                    {{ __('workshops.details.booking_card.pending_hint') }}
                                </p>
                            @endif
                        @elseif($workshop->is_fully_booked)
                            <button class="booking-btn bg-gray-100 text-gray-400 cursor-not-allowed">
                                <i class="fas fa-times-circle"></i>
                                {{ __('workshops.details.booking_card.cta_full') }}
                            </button>
                        @elseif(! $workshop->is_registration_open)
                            <button class="booking-btn bg-amber-50 text-amber-600 cursor-not-allowed">
                                <i class="fas fa-clock"></i>
                                {{ __('workshops.details.booking_card.cta_closed') }}
                            </button>
                        @else
                            @guest
                                <button class="booking-btn btn-primary" onclick="showLoginRequiredModal({{ $workshop->id }})">
                                    <i class="fas fa-sign-in-alt"></i>
                                    {{ __('workshops.details.booking_card.login_required') }}
                                </button>
                                <p class="booking-action-hint">
                                    {{ __('workshops.details.booking_card.login_hint') }}
                                </p>
                            @else
                                @if(! $onlinePaymentsEnabled)
                                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                                        <p class="text-amber-800 font-semibold">{{ __('workshops.details.booking_card.payments_disabled_title') }}</p>
                                    </div>
                                @else
                                    <div class="space-y-4">
                                        <div id="booking-methods-grid">
                                            @if($stripeEnabled && ! $userBookedViaWhatsapp)
                                            <div class="space-y-4" id="stripe-checkout-card">
                                                <div id="stripe-wallet-section" class="hidden space-y-3">
                                                    <div id="stripe-wallet-button" class="min-h-[48px]"></div>
                                                    <p id="stripe-wallet-hint" class="hidden text-xs text-gray-500 text-center">
                                                        {{ __('workshops.stripe.wallet_hint') }}
                                                    </p>
                                                </div>
                                                
                                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                                    <div id="stripe-payment-element" class="min-h-[140px]"></div>
                                                </div>
                                                
                                                <p id="stripe-card-errors" class="hidden text-sm text-red-600 text-center" role="alert"></p>
                                                <p id="stripe-success-message" class="hidden text-sm text-green-600 text-center" role="status"></p>
                                                
                                                <button type="button"
                                                        id="stripe-submit-button"
                                                        class="booking-btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span id="stripe-submit-label">{{ __('workshops.stripe.pay_button') }}</span>
                                                    <span id="stripe-submit-spinner" class="hidden items-center justify-center gap-2 text-sm">
                                                        <i class="fas fa-spinner fa-spin"></i>
                                                        {{ __('workshops.stripe.processing') }}
                                                    </span>
                                                </button>
                                            </div>
                                            @endif

                                            @if($whatsappButtonAvailable)
                                                <div class="mt-4 pt-4 border-t border-gray-100">
                                                    <div class="js-whatsapp-pending-alert {{ $whatsappPendingApproval ? '' : 'hidden' }} mb-4 rounded-xl bg-amber-50 p-3 flex items-start gap-3" data-workshop-id="{{ $workshop->id }}">
                                                        <i class="fas fa-hourglass-half text-amber-500 mt-1"></i>
                                                        <div class="text-sm text-amber-800">
                                                            <p class="font-semibold">{{ __('workshops.whatsapp.pending_badge') }}</p>
                                                            <p>{{ __('workshops.whatsapp.pending_helper') }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="js-whatsapp-booking-section {{ $userBookedViaWhatsapp ? 'hidden' : '' }}" data-workshop-id="{{ $workshop->id }}">
                                                        <a href="{{ $whatsappQuickLink ?? '#' }}"
                                                           target="_blank"
                                                           class="booking-btn bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 js-whatsapp-booking"
                                                           data-workshop-id="{{ $workshop->id }}"
                                                           data-title="{{ e($workshop->title) }}"
                                                           data-price="{{ $whatsappPriceLabel }}"
                                                           data-date="{{ $whatsappDateLabel }}"
                                                           data-instructor="{{ $whatsappInstructorLabel }}"
                                                           data-location="{{ $whatsappLocationLabel }}"
                                                           data-deadline="{{ $whatsappDeadlineLabel }}"
                                                           data-topics="{{ e($whatsappTopicsLabel) }}"
                                                           data-requirements="{{ e($whatsappRequirementsLabel) }}"
                                                           data-duration="{{ e($whatsappDurationLabel) }}"
                                                           data-terms="{{ e($whatsappTermsLabel) }}">
                                                            <i class="fab fa-whatsapp text-xl"></i>
                                                            <span>{{ __('workshops.whatsapp.button') }}</span>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="js-whatsapp-inquiry-section {{ $userBookedViaWhatsapp ? '' : 'hidden' }} mt-2" data-workshop-id="{{ $workshop->id }}">
                                                        <button type="button"
                                                                class="w-full text-sm text-gray-500 hover:text-gray-700 font-medium py-2 flex items-center justify-center gap-2 js-whatsapp-inquiry-button"
                                                                data-workshop-id="{{ $workshop->id }}"
                                                                data-workshop-title="{{ e($workshop->title) }}"
                                                                data-booking-code="{{ optional($userBooking)->public_code ?? '' }}">
                                                            <i class="fas fa-comments"></i>
                                                            <span>{{ __('workshops.whatsapp.inquiry_button') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endguest
                        @endif
                        </div>
                        @livewire('bookings.whatsapp-booking-verification', [
                            'workshopId' => $workshop->id,
                            'initialHasWhatsappBooking' => $userBookedViaWhatsapp,
                            'initialBookingId' => optional($userBooking)->id,
                            'stripeElementId' => 'stripe-checkout-card',
                        ], key('whatsapp-verification-'.$workshop->id))
                    </div>
                </div>
            </div>

            @php
                $ratingAverage = $reviewSummary['average'] ?? null;
                $ratingCount = (int) ($reviewSummary['count'] ?? 0);
            @endphp

            @if($workshop->is_completed || $ratingCount > 0 || $canReview)
            <div class="content-card mt-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-amber-600">{{ __('workshops.reviews.title') }}</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $ratingAverage ? number_format($ratingAverage, 1) : '—' }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ __('workshops.details.hero.reviews_count', ['count' => number_format($ratingCount)]) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $ratingAverage >= $i ? 'text-amber-400' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                </div>

                @if (session('review_saved'))
                    <div class="mt-4 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                        {{ session('review_saved') }}
                    </div>
                @endif

                @if($canReview)
                    @php
                        $selectedRating = (int) old('rating', optional($userReview)->rating ?? 5);
                    @endphp
                    <form method="POST" action="{{ route('workshops.reviews.store', ['workshop' => $workshop->slug]) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="workshop-rating" class="block text-sm font-semibold text-gray-800">{{ __('workshops.reviews.rating_label') }}</label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @for($i = 5; $i >= 1; $i--)
                                    @php
                                        $isChecked = $selectedRating === $i;
                                    @endphp
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border {{ $isChecked ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-gray-50' }} px-2.5 py-1.5 text-sm font-semibold text-gray-700 hover:border-amber-300 hover:bg-amber-50 rating-option" data-rating-option="{{ $i }}">
                                        <input
                                            type="radio"
                                            name="rating"
                                            value="{{ $i }}"
                                            class="sr-only"
                                            @checked($isChecked)
                                        >
                                        <span>{{ $i }}</span>
                                        <i class="fas fa-star text-amber-400"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="workshop-comment" class="block text-sm font-semibold text-gray-800">
                                {{ __('workshops.reviews.comment_label') }}
                                <span class="text-xs font-normal text-gray-400">({{ __('workshops.reviews.optional') }})</span>
                            </label>
                            <textarea
                                id="workshop-comment"
                                name="comment"
                                rows="3"
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white p-3 text-sm text-gray-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                                placeholder="{{ __('workshops.reviews.comment_placeholder') }}">{{ old('comment', optional($userReview)->comment) }}</textarea>
                            @error('comment')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="booking-btn btn-primary w-full justify-center">
                            <i class="fas fa-paper-plane"></i>
                            <span>{{ $userReview ? __('workshops.reviews.update') : __('workshops.reviews.submit') }}</span>
                        </button>
                    </form>
                @elseif(! $reviewWindowOpen)
                    <p class="mt-3 text-sm text-gray-500">{{ __('workshops.reviews.messages.wait_until_end') }}</p>
                @elseif(auth()->check())
                    <p class="mt-3 text-sm text-gray-500">{{ __('workshops.reviews.messages.participant_only') }}</p>
                @else
                    <p class="mt-3 text-sm text-gray-500">{{ __('workshops.reviews.messages.login_to_review') }}</p>
                @endif
            </div>
            @endif

            <!-- Additional Details Card -->
            <div class="content-card additional-details-card mt-6">
                <div class="additional-details-header">
                    <div class="additional-details-icon">
                        <i class="fas fa-info"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-amber-600 mb-1">
                            {{ __('workshops.details.sidebar.title') }}
                        </p>
                        <p class="additional-details-title">{{ $workshop->title }}</p>
                        <p class="additional-details-sub">{{ $workshop->location ?? $workshopLocationLabel }}</p>
                    </div>
                </div>
                <div class="additional-details-grid">
                    <div class="additional-detail">
                        <div class="additional-detail-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <div>
                            <p class="additional-detail-label">{{ __('workshops.details.sidebar.start') }}</p>
                            <p class="additional-detail-value">{{ $workshopStartDateTimeLabel }}</p>
                        </div>
                    </div>
                    <div class="additional-detail">
                        <div class="additional-detail-icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div>
                            <p class="additional-detail-label">{{ __('workshops.details.sidebar.end') }}</p>
                            <p class="additional-detail-value">{{ $workshopEndDateTimeLabel }}</p>
                        </div>
                    </div>
                    <div class="additional-detail">
                        <div class="additional-detail-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <p class="additional-detail-label">{{ __('workshops.details.sidebar.category') }}</p>
                            <p class="additional-detail-value">{{ $workshop->category ?? $notSpecifiedLabel }}</p>
                        </div>
                    </div>
                    <div class="additional-detail">
                        <div class="additional-detail-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <p class="additional-detail-label">{{ __('workshops.details.sidebar.level') }}</p>
                            <p class="additional-detail-value">{{ $levelLabels[$workshop->level] ?? $levelLabels['beginner'] }}</p>
                        </div>
                    </div>
                    <div class="additional-detail">
                        <div class="additional-detail-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div>
                            <p class="additional-detail-label">{{ __('workshops.details.sidebar.views') }}</p>
                            <p class="additional-detail-value">{{ $workshop->views_count }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($relatedWorkshops->count() > 0)
        <section class="related-workshops-section">
            <div class="content-wrapper">
                <div class="text-center mb-12">
                    <h2 class="section-title justify-center text-3xl mb-4">{{ __('workshops.details.related.title') }}</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-amber-500 to-orange-600 mx-auto rounded-full"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($relatedWorkshops as $related)
                         @php 
                            $isFull = $related->bookings_count >= $related->max_participants; 
                        @endphp
                        <div class="related-workshop-card group {{ $isFull ? 'opacity-75' : '' }}">
                            <a href="{{ route('workshop.show', $related->slug) }}" class="block">
                                <div class="relative overflow-hidden">
                                    <img src="{{ $related->image ? asset('storage/' . $related->image) : 'https://placehold.co/600x400/f87171/FFFFFF?text=' . urlencode(__('workshops.labels.card_placeholder_text')) }}" 
                                         alt="{{ $related->title }}" 
                                         class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500"
                                        onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                                    
                                    @if($isFull)
                                        <span class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">{{ __('workshops.details.related.badge_full') }}</span>
                                    @elseif($related->is_online)
                                        <span class="absolute top-4 left-4 bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">{{ __('workshops.labels.online_short') }}</span>
                                    @else
                                        <span class="absolute top-4 left-4 bg-orange-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">{{ __('workshops.labels.onsite_short') }}</span>
                                    @endif
                                </div>
                                
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-amber-600 transition-colors line-clamp-2">
                                        {{ $related->title }}
                                    </h3>
                                    
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <i class="fas fa-chalkboard-teacher text-amber-500 ml-2"></i>
                                        <span>{{ $related->instructor }}</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                        <div class="related-price">
                                            <span class="text-xl font-bold">{{ $related->price }}</span>
                                            <span class="text-xs font-normal text-gray-500">{{ $related->currency }}</span>
                                        </div>
                                        <span class="related-cta text-sm group-hover:translate-x-[-4px] transition-transform flex items-center gap-1">
                                            {{ __('workshops.details.related.details') }}
                                            <i class="fas {{ $arrowIcon }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>

@if($showFloatingBookingButton)
    <div class="floating-booking-bar">
        <div class="floating-content">
            <div class="floating-info">
                <span class="floating-price">{{ $workshop->formatted_price }}</span>
                <span class="floating-meta">
                    @if($workshop->max_participants)
                        {{ $workshop->bookings_count }}/{{ $workshop->max_participants }} {{ __('workshops.labels.participants') }}
                    @else
                        {{ $workshopDateLabel }}
                    @endif
                </span>
            </div>
            <a href="#stripe-checkout-card" 
               class="floating-btn"
               data-scroll-target="#stripe-checkout-card"
               data-scroll-fallback="#workshop-booking">
                <span>{{ __('workshops.cards.button_book') }}</span>
                <i class="fas {{ $arrowIcon }}"></i>
            </a>
        </div>
    </div>
@endif
@endsection

@if($stripeEnabled)
    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
    @endpush
@endif

@push('scripts')
<script>
const csrfMetaTag = document.querySelector('meta[name="csrf-token"]');
const bookingCsrfToken = csrfMetaTag ? csrfMetaTag.getAttribute('content') : null;

const bookingConfig = {
    workshopId: {{ $workshop->id }},
    workshop: {
        title: @json($workshop->title),
        price: @json((float) $workshop->price),
        currency: @json(strtoupper($workshop->currency ?? config('finance.default_currency', 'USD'))),
    },
    stripe: {
        enabled: @json((bool) $stripeEnabled),
        publishableKey: @json($stripePublicKey),
        createIntentUrl: "{{ $stripeEnabled ? route('payments.stripe.intent') : '' }}",
        confirmUrl: "{{ $stripeEnabled ? route('payments.stripe.confirm') : '' }}",
        paymentCountry: @json(config('services.stripe.payment_country', 'SA')),
        messages: {
            disabled: @json(__('workshops.stripe.disabled')),
            genericError: @json(__('workshops.stripe.generic_error')),
            initError: @json(__('workshops.stripe.init_error')),
            validationError: @json(__('workshops.stripe.validation_error')),
            successMessage: @json(__('workshops.stripe.success_message')),
            notReady: @json(__('workshops.stripe.not_ready')),
            intentError: @json(__('workshops.stripe.intent_error')),
            walletUnavailable: @json(__('workshops.stripe.wallet_unavailable')),
        },
    },
};

const workshopMessages = {
    unexpected: @json(__('workshops.details.messages.unexpected_error')),
    paymentSuccess: @json(__('workshops.details.messages.payment_success')),
    stripeError: @json(__('workshops.details.messages.stripe_error')),
    stripeCancelled: @json(__('workshops.details.messages.stripe_cancelled')),
    joinLinkReady: @json(__('workshops.details.messages.join_link_ready')),
    joinLinkAction: @json(__('workshops.details.messages.join_link_action')),
};

const bookingUi = {
    joinLabel: @json(__('workshops.details.booking_card.cta_join')),
    joinHint: @json(__('workshops.details.booking_card.join_room_hint')),
    joinButtonClasses: 'w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl text-lg booking-button flex items-center justify-center gap-2',
    joinIconClasses: 'fas fa-video mr-2 rtl:ml-2',
};

const loginModalTexts = {
    title: @json(__('workshops.details.modal.title')),
    description: @json(__('workshops.details.modal.description')),
    hint: @json(__('workshops.details.modal.hint')),
    login: @json(__('workshops.details.modal.login')),
    register: @json(__('workshops.details.modal.register')),
};

let stripeInstance = null;
let stripeElements = null;
let stripePaymentElement = null;
let stripeClientSecret = null;
let stripePaymentIntentId = null;
let stripeInitAttempts = 0;
let stripeEventsBound = false;
let stripeElementReady = false;
let stripeIsLoading = false;
let stripePaymentRequest = null;
let stripeWalletElement = null;
let stripeWalletReady = false;
let stripeIntentAmount = null;
let stripeIntentCurrency = null;
const STRIPE_MAX_INIT_ATTEMPTS = 5;
let loginModalKeyListener = null;

function initHeroExcerptToggle() {
    const excerpt = document.getElementById('workshopHeroExcerpt');
    const toggle = document.querySelector('[data-toggle="workshop-hero-excerpt"]');

    if (!excerpt || !toggle) {
        return;
    }

    const previewText = excerpt.dataset.previewText || '';
    const fullText = excerpt.dataset.fullText || '';
    const moreLabel = toggle.dataset.moreLabel || 'عرض المزيد';
    const lessLabel = toggle.dataset.lessLabel || 'عرض أقل';
    let expanded = false;

    const applyState = () => {
        excerpt.textContent = expanded ? fullText : previewText;
        excerpt.dataset.expanded = expanded ? 'true' : 'false';
        excerpt.classList.toggle('collapsed', !expanded);

        const label = toggle.querySelector('.toggle-label');
        if (label) {
            label.textContent = expanded ? lessLabel : moreLabel;
        }

        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-chevron-down', !expanded);
            icon.classList.toggle('fa-chevron-up', expanded);
        }
    };

    toggle.addEventListener('click', () => {
        expanded = !expanded;
        applyState();
    });

    applyState();
}

function getJsonHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': bookingCsrfToken,
    };
}

function handleJsonResponse(response) {
    return response.text().then(text => {
        let data = {};
        let errorMessage = null;

        if (text) {
            try {
                data = JSON.parse(text);
            } catch (error) {
                console.warn('Failed to parse JSON response', error);
            }
        }

        if (!response.ok) {
            if (data && data.errors) {
                const firstErrorGroup = Object.values(data.errors)[0];
                if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
                    errorMessage = firstErrorGroup[0];
                }
            }

            const message = errorMessage || data.message || workshopMessages.unexpected;
            const error = new Error(message);
            error.payload = data;
            throw error;
        }

        return data;
    });
}

function updateBookingPrimaryAction(joinUrl) {
    if (!joinUrl) {
        return false;
    }

    const primaryAction = document.getElementById('booking-primary-action');

    if (!primaryAction) {
        return false;
    }

    const button = document.createElement('a');
    button.href = joinUrl;
    button.className = bookingUi.joinButtonClasses;
    button.innerHTML = `
        <i class="${bookingUi.joinIconClasses}"></i>
        <span>${bookingUi.joinLabel}</span>
    `;

    primaryAction.innerHTML = '';
    primaryAction.appendChild(button);

    const hint = document.createElement('p');
    hint.className = 'booking-action-hint';
    hint.textContent = bookingUi.joinHint;
    primaryAction.appendChild(hint);

    return true;
}

let bookingRefreshFallbackBound = false;

function registerBookingRefreshFallback() {
    if (bookingRefreshFallbackBound) {
        return;
    }

    const reloadIfPending = () => {
        if (window.__bookingRefreshPending__) {
            window.__bookingRefreshPending__ = false;

            if (window.__bookingStatusRefreshTimer__) {
                clearTimeout(window.__bookingStatusRefreshTimer__);
            }

            window.location.reload();
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            reloadIfPending();
        }
    });

    window.addEventListener('focus', reloadIfPending);

    bookingRefreshFallbackBound = true;
}

function scheduleBookingStatusRefresh(delay = 2500) {
    const refreshDelay = Number.isFinite(delay) ? delay : Number(delay);
    const finalDelay = Number.isFinite(refreshDelay) && refreshDelay >= 0 ? refreshDelay : 2500;

    if (window.__bookingStatusRefreshTimer__) {
        clearTimeout(window.__bookingStatusRefreshTimer__);
    }

    window.__bookingRefreshPending__ = true;
    registerBookingRefreshFallback();

    window.__bookingStatusRefreshTimer__ = window.setTimeout(() => {
        window.__bookingRefreshPending__ = false;
        window.location.reload();
    }, finalDelay);
}

function handlePaymentSuccess(payload) {
    const baseMessage = payload.message || workshopMessages.paymentSuccess;
    let alertMessage = baseMessage;

    if (payload.join_url) {
        const joinNotice = workshopMessages.joinLinkReady || '';
        const joinActionLabel = workshopMessages.joinLinkAction || '';
        const joinLink = payload.join_url;

        alertMessage += `
            <span class="block mt-2 text-xs font-semibold">${joinNotice}</span>
            <a class="mt-2 inline-flex items-center gap-2 rounded-md bg-white/80 px-3 py-1.5 text-xs font-semibold text-green-700 underline hover:text-green-900"
               href="${joinLink}"
               target="_blank"
               rel="noopener noreferrer">
                <i class="fas fa-link"></i>
                ${joinActionLabel}
            </a>
        `;

        showCustomAlert(alertMessage, 'success');
        const actionUpdated = updateBookingPrimaryAction(joinLink);

        scheduleBookingStatusRefresh(actionUpdated ? 2000 : undefined);

        return;
    }

    showCustomAlert(alertMessage, 'success');

    const redirectTarget = payload.redirect_url;

    if (redirectTarget) {
        setTimeout(() => {
            window.location.href = redirectTarget;
        }, 1200);
    } else {
        scheduleBookingStatusRefresh(1500);
    }
}

function updateStripeButtonState() {
    const button = document.getElementById('stripe-submit-button');

    if (!button) {
        return;
    }

    const disabled = stripeIsLoading || !stripeElementReady;
    button.disabled = disabled;
    button.classList.toggle('opacity-50', disabled);
    button.classList.toggle('cursor-not-allowed', disabled);
}

function toggleStripeLoading(isLoading) {
    stripeIsLoading = isLoading;
    const button = document.getElementById('stripe-submit-button');
    const spinner = document.getElementById('stripe-submit-spinner');
    const label = document.getElementById('stripe-submit-label');

    updateStripeButtonState();

    if (spinner) {
        spinner.classList.toggle('hidden', !isLoading);
        spinner.classList.toggle('flex', isLoading);
    }

    if (label) {
        label.classList.toggle('hidden', isLoading);
    }
}

function showStripeError(message) {
    const errorEl = document.getElementById('stripe-card-errors');

    if (!errorEl) {
        return;
    }

    if (!message) {
        errorEl.classList.add('hidden');
        errorEl.textContent = '';
        return;
    }

    errorEl.textContent = message;
    errorEl.classList.remove('hidden');
}

function setStripeSuccess(message) {
    const successEl = document.getElementById('stripe-success-message');

    if (!successEl) {
        return;
    }

    if (!message) {
        successEl.classList.add('hidden');
        successEl.textContent = '';
        return;
    }

    successEl.textContent = message;
    successEl.classList.remove('hidden');
}

function hideStripeWalletSection() {
    const walletSection = document.getElementById('stripe-wallet-section');
    const walletUnavailable = document.getElementById('stripe-wallet-unavailable');

    if (walletSection) {
        walletSection.classList.add('hidden');
    }

    if (walletUnavailable) {
        walletUnavailable.classList.add('hidden');
    }
}

function resetStripeWalletElements() {
    if (stripeWalletElement) {
        try {
            stripeWalletElement.unmount();
        } catch (error) {
            console.warn('Failed to unmount wallet element', error);
        }
    }

    stripePaymentRequest = null;
    stripeWalletElement = null;
    stripeWalletReady = false;
    hideStripeWalletSection();
}

async function fetchStripeIntent() {
    if (!bookingConfig.stripe.createIntentUrl) {
        throw new Error(bookingConfig.stripe.messages.disabled);
    }

    return fetch(bookingConfig.stripe.createIntentUrl, {
        method: 'POST',
        headers: getJsonHeaders(),
        body: JSON.stringify({
            workshop_id: bookingConfig.workshopId,
        }),
    }).then(handleJsonResponse);
}

async function initializeStripeCheckout(forceRecreate = false) {
    if (!bookingConfig.stripe.enabled) {
        return;
    }

    const container = document.getElementById('stripe-payment-element');

    if (!container) {
        return;
    }

    resetStripeWalletElements();

    if (!bookingConfig.stripe.publishableKey) {
        container.innerHTML = `<p class="text-sm text-red-500 text-center">${bookingConfig.stripe.messages.disabled}</p>`;
        return;
    }

    if (typeof Stripe === 'undefined') {
        if (stripeInitAttempts < STRIPE_MAX_INIT_ATTEMPTS) {
            stripeInitAttempts += 1;
            setTimeout(() => initializeStripeCheckout(forceRecreate), 500);
            return;
        }

        container.innerHTML = `<p class="text-sm text-red-500 text-center">${bookingConfig.stripe.messages.initError}</p>`;
        return;
    }

    if (!stripeInstance || forceRecreate) {
        stripeInstance = Stripe(bookingConfig.stripe.publishableKey);
    }

    if (stripePaymentElement) {
        try {
            stripePaymentElement.unmount();
        } catch (error) {
            console.warn('Failed to unmount Stripe element', error);
        }
        stripePaymentElement = null;
    }

    stripeElementReady = false;
    updateStripeButtonState();
    toggleStripeLoading(true);
    showStripeError('');
    setStripeSuccess('');

    try {
        const payload = await fetchStripeIntent();
        stripeClientSecret = payload.client_secret || null;
        stripePaymentIntentId = payload.payment_intent_id || null;

        if (!stripeClientSecret) {
            throw new Error(bookingConfig.stripe.messages.genericError);
        }

        stripeElements = stripeInstance.elements({
            clientSecret: stripeClientSecret,
        });

        stripePaymentElement = stripeElements.create('payment', { layout: 'tabs' });

        stripePaymentElement.on('ready', () => {
            stripeElementReady = true;
            updateStripeButtonState();
            toggleStripeLoading(false);
        });

        stripePaymentElement.on('loaderror', (event) => {
            console.error('Stripe element load error', event);
            stripeElementReady = false;
            updateStripeButtonState();
            showStripeError(event.error?.message || bookingConfig.stripe.messages.genericError);
        });

        stripePaymentElement.mount('#stripe-payment-element');

        stripeIntentAmount = payload.amount ?? null;
        stripeIntentCurrency = payload.currency ?? bookingConfig.workshop.currency;
        await setupStripeWalletButton();
    } catch (error) {
        console.error(error);
        showStripeError(error.message || bookingConfig.stripe.messages.genericError);
        stripeElementReady = false;
        updateStripeButtonState();
    } finally {
        if (!stripeElementReady) {
            toggleStripeLoading(false);
        }
    }
}

async function setupStripeWalletButton() {
    const walletSection = document.getElementById('stripe-wallet-section');
    const walletUnavailable = document.getElementById('stripe-wallet-unavailable');
    const walletHint = document.getElementById('stripe-wallet-hint');

    if (!walletSection) {
        return;
    }

    hideStripeWalletSection();

    if (!window.PaymentRequest || !stripeInstance || !stripeElements || !stripeClientSecret) {
        walletUnavailable?.classList.remove('hidden');
        return;
    }

    if (!stripeIntentAmount || !stripeIntentCurrency) {
        walletUnavailable?.classList.remove('hidden');
        return;
    }

    const paymentRequestAllowedCountries = [
        'AE', 'AT', 'AU', 'BE', 'BG', 'BR', 'CA', 'CH', 'CI', 'CR', 'CY', 'CZ', 'DE', 'DK', 'DO',
        'EE', 'ES', 'FI', 'FR', 'GB', 'GI', 'GR', 'GT', 'HK', 'HR', 'HU', 'ID', 'IE', 'IN', 'IT',
        'JP', 'LI', 'LT', 'LU', 'LV', 'MT', 'MX', 'MY', 'NL', 'NO', 'NZ', 'PE', 'PH', 'PL', 'PT',
        'RO', 'SE', 'SG', 'SI', 'SK', 'SN', 'TH', 'TT', 'US', 'UY',
    ];
    const configuredPaymentCountry = (bookingConfig.stripe.paymentCountry || '').toUpperCase();
    const paymentCountry = paymentRequestAllowedCountries.includes(configuredPaymentCountry)
        ? configuredPaymentCountry
        : 'AE';

    const paymentRequest = stripeInstance.paymentRequest({
        country: paymentCountry,
        currency: (stripeIntentCurrency || bookingConfig.workshop.currency || 'USD').toLowerCase(),
        total: {
            label: bookingConfig.workshop.title || 'Workshop booking',
            amount: stripeIntentAmount,
        },
        requestPayerName: true,
        requestPayerEmail: true,
    });

    const paymentRequestButton = stripeElements.create('paymentRequestButton', {
        paymentRequest,
        style: {
            paymentRequestButton: {
                type: 'default',
                theme: 'dark',
                height: '48px',
            },
        },
    });

    try {
        const result = await paymentRequest.canMakePayment();

        if (!result) {
            walletUnavailable?.classList.remove('hidden');
            return;
        }
    } catch (error) {
        console.warn('PaymentRequest not available', error);
        walletUnavailable?.classList.remove('hidden');
        return;
    }

    paymentRequest.on('paymentmethod', async (event) => {
        toggleStripeLoading(true);
        showStripeError('');

        try {
            const confirmResult = await stripeInstance.confirmCardPayment(
                stripeClientSecret,
                {
                    payment_method: event.paymentMethod.id,
                },
                {
                    handleActions: false,
                }
            );

            if (confirmResult.error) {
                event.complete('fail');
                throw new Error(confirmResult.error.message || bookingConfig.stripe.messages.genericError);
            }

            let paymentIntent = confirmResult.paymentIntent;

            event.complete('success');

            if (paymentIntent && paymentIntent.status === 'requires_action') {
                const nextStep = await stripeInstance.confirmCardPayment(stripeClientSecret);

                if (nextStep.error) {
                    throw new Error(nextStep.error.message || bookingConfig.stripe.messages.genericError);
                }

                paymentIntent = nextStep.paymentIntent;
            }

            await finalizeStripeIntent(paymentIntent?.id || stripePaymentIntentId);
        } catch (error) {
            console.error(error);
            event.complete('fail');
            showStripeError(error.message || bookingConfig.stripe.messages.genericError);
        } finally {
            toggleStripeLoading(false);
        }
    });

    paymentRequestButton.mount('#stripe-wallet-button');
    walletSection.classList.remove('hidden');
    walletHint?.classList.remove('hidden');
    walletUnavailable?.classList.add('hidden');

    stripePaymentRequest = paymentRequest;
    stripeWalletElement = paymentRequestButton;
    stripeWalletReady = true;
}

async function finalizeStripeIntent(intentId) {
    if (!intentId) {
        throw new Error(bookingConfig.stripe.messages.intentError || bookingConfig.stripe.messages.genericError);
    }

    if (!bookingConfig.stripe.confirmUrl) {
        throw new Error(bookingConfig.stripe.messages.disabled);
    }

    const payload = await fetch(bookingConfig.stripe.confirmUrl, {
        method: 'POST',
        headers: getJsonHeaders(),
        body: JSON.stringify({
            workshop_id: bookingConfig.workshopId,
            payment_intent_id: intentId,
        }),
    }).then(handleJsonResponse);

    setStripeSuccess(payload.message || bookingConfig.stripe.messages.successMessage);
    handlePaymentSuccess(payload);
    return payload;
}

async function submitStripePayment(event) {
    if (event) {
        event.preventDefault();
    }

    if (!bookingConfig.stripe.enabled) {
        return;
    }

    if (!bookingConfig.stripe.confirmUrl) {
        showStripeError(bookingConfig.stripe.messages.disabled);
        return;
    }

    if (!stripeInstance || !stripeElements || !stripePaymentElement || !stripeClientSecret) {
        await initializeStripeCheckout(true);
    }

    if (!stripeInstance || !stripeElements || !stripePaymentElement) {
        showStripeError(bookingConfig.stripe.messages.initError);
        return;
    }

    toggleStripeLoading(true);
    showStripeError('');
    setStripeSuccess('');

    try {
        if (!stripeElementReady) {
            throw new Error(bookingConfig.stripe.messages.notReady);
        }

        const { error, paymentIntent } = await stripeInstance.confirmPayment({
            elements: stripeElements,
            redirect: 'if_required',
            confirmParams: {
                return_url: window.location.href,
            },
        });

        if (error) {
            throw new Error(error.message || bookingConfig.stripe.messages.genericError);
        }

        const intentId = paymentIntent?.id || stripePaymentIntentId;

        await finalizeStripeIntent(intentId);
    } catch (error) {
        console.error(error);
        showStripeError(error.message || bookingConfig.stripe.messages.genericError);
    } finally {
        toggleStripeLoading(false);
    }
}

function bindStripeEvents() {
    if (stripeEventsBound) {
        return;
    }

    const button = document.getElementById('stripe-submit-button');

    if (button) {
        button.addEventListener('click', submitStripePayment);
        stripeEventsBound = true;
    }
}

function initWorkshopDetailsBooking() {
    if (bookingConfig.stripe.enabled) {
        bindStripeEvents();
        initializeStripeCheckout();
    }
}

// Custom alert helper
function showCustomAlert(message, type = 'info') {
    const existingAlert = document.getElementById('custom-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    let bgColor, textColor, icon, borderColor;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-50';
            textColor = 'text-green-800';
            icon = 'fas fa-check-circle';
            borderColor = 'border-green-200';
            break;
        case 'error':
            bgColor = 'bg-red-50';
            textColor = 'text-red-800';
            icon = 'fas fa-exclamation-circle';
            borderColor = 'border-red-200';
            break;
        case 'warning':
            bgColor = 'bg-yellow-50';
            textColor = 'text-yellow-800';
            icon = 'fas fa-exclamation-triangle';
            borderColor = 'border-yellow-200';
            break;
        default:
            bgColor = 'bg-blue-50';
            textColor = 'text-blue-800';
            icon = 'fas fa-info-circle';
            borderColor = 'border-blue-200';
    }

    const alertHTML = `
        <div id="custom-alert" class="fixed top-4 right-4 z-50 max-w-sm w-full mx-4 transform transition-all duration-300 ease-in-out">
            <div class="${bgColor} ${borderColor} border-l-4 rounded-lg shadow-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${icon} ${textColor} text-xl"></i>
                    </div>
                    <div class="mr-3 flex-1">
                        <p class="${textColor} text-sm font-medium leading-5">
                            ${message}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <button onclick="closeCustomAlert()" class="${textColor} hover:opacity-75 focus:outline-none">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHTML);

    setTimeout(() => {
        const alert = document.getElementById('custom-alert');
        if (alert) {
            alert.style.transform = 'translateX(0)';
            alert.style.opacity = '1';
        }
    }, 100);

    setTimeout(() => {
        closeCustomAlert();
    }, 5000);
}

// Remove the alert with a subtle animation
function closeCustomAlert() {
    const alert = document.getElementById('custom-alert');
    if (alert) {
        alert.style.transform = 'translateX(100%)';
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

window.showCustomAlert = showCustomAlert;
window.closeCustomAlert = closeCustomAlert;

// Prompt unauthenticated users to sign in before booking
function showLoginRequiredModal(workshopId = null) {
    const existingModal = document.getElementById('login-required-modal');
    if (existingModal) {
        existingModal.remove();
    }

    if (workshopId) {
        localStorage.setItem('pending_workshop_booking', workshopId);
    }

    const modalHTML = `
        <div id="login-required-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeLoginRequiredModal(event)">
            <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-100 relative" onclick="event.stopPropagation()">
                <button onclick="closeLoginRequiredModal()" class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                    <i class="fas fa-times text-xl"></i>
                </button>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-user-lock text-white text-3xl"></i>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">${loginModalTexts.title}</h3>
                    
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
                        <p class="text-gray-700 text-lg leading-relaxed">
                            ${loginModalTexts.description}
                        </p>
                        <p class="text-gray-600 text-sm mt-2">
                            ${loginModalTexts.hint}
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="redirectToLoginWithWorkshop()" class="flex-1 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-sign-in-alt {{ $isRtl ? 'ml-2' : 'mr-2' }}"></i>
                            ${loginModalTexts.login}
                        </button>
                        <button onclick="redirectToRegisterWithWorkshop()" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-user-plus {{ $isRtl ? 'ml-2' : 'mr-2' }}"></i>
                            ${loginModalTexts.register}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    if (loginModalKeyListener) {
        document.removeEventListener('keydown', loginModalKeyListener);
    }

    loginModalKeyListener = function(event) {
        if (event.key === 'Escape') {
            closeLoginRequiredModal();
        }
    };

    document.addEventListener('keydown', loginModalKeyListener);
}

// Close the login modal, optionally when the backdrop is clicked
function closeLoginRequiredModal(event) {
    if (event && event.target && event.target.id !== 'login-required-modal') {
        return;
    }

    const modal = document.getElementById('login-required-modal');
    if (modal) {
        modal.remove();
    }

    if (loginModalKeyListener) {
        document.removeEventListener('keydown', loginModalKeyListener);
        loginModalKeyListener = null;
    }
}

function buildAuthRedirect(baseUrl, workshopId) {
    const url = new URL(baseUrl, window.location.origin);
    if (workshopId) {
        url.searchParams.set('pending_workshop_booking', workshopId);
    }
    url.searchParams.set('return_to', window.location.href);
    return url.toString();
}

// Redirect helper when the user wants to sign in
function redirectToLoginWithWorkshop() {
    const workshopId = localStorage.getItem('pending_workshop_booking');
    window.location.href = buildAuthRedirect('{{ route('login') }}', workshopId);
}

// Redirect helper when the user wants to register
function redirectToRegisterWithWorkshop() {
    const workshopId = localStorage.getItem('pending_workshop_booking');
    window.location.href = buildAuthRedirect('{{ route('register') }}', workshopId);
}

// Expose helpers globally for inline handlers
window.closeLoginRequiredModal = closeLoginRequiredModal;
window.showLoginRequiredModal = showLoginRequiredModal;
window.redirectToLoginWithWorkshop = redirectToLoginWithWorkshop;
window.redirectToRegisterWithWorkshop = redirectToRegisterWithWorkshop;

function handleWorkshopHideStripeEvent(event) {
    if (typeof bookingConfig === 'undefined') {
        return;
    }

    const detail = event?.detail || {};
    const targetId = detail.elementId || 'stripe-checkout-card';
    const targetWorkshopId = Number(detail.workshopId || detail.workshop_id || 0);

    if (Number(bookingConfig.workshopId) && targetWorkshopId && targetWorkshopId !== Number(bookingConfig.workshopId)) {
        return;
    }

    const target = document.getElementById(targetId);

    if (!target) {
        return;
    }

    target.classList.add('hidden', 'opacity-0', 'pointer-events-none');
    target.setAttribute('aria-hidden', 'true');
}

function registerWorkshopHideStripeListener() {
    if (window.__workshopHideStripeListenerRegistered) {
        return;
    }

    window.addEventListener('workshop-hide-stripe', handleWorkshopHideStripeEvent);
    window.__workshopHideStripeListenerRegistered = true;
}

if (window.Livewire) {
    registerWorkshopHideStripeListener();
} else {
    document.addEventListener('livewire:init', registerWorkshopHideStripeListener, { once: true });
}

function scrollToElementWithOffset(element) {
    if (!element) {
        return false;
    }

    const header = document.querySelector('[data-sticky-header]');
    const headerOffset = header ? header.getBoundingClientRect().height : 96;
    const scrollTarget = element.getBoundingClientRect().top + window.pageYOffset - (headerOffset + 24);

    window.scrollTo({
        top: Math.max(scrollTarget, 0),
        behavior: 'smooth',
    });

    return true;
}

function handleScrollTriggerClick(event) {
    const trigger = event.currentTarget;

    if (!trigger) {
        return;
    }

    event.preventDefault();

    const targetSelector = trigger.getAttribute('data-scroll-target');
    const fallbackSelector = trigger.getAttribute('data-scroll-fallback');
    const hrefSelector = trigger.getAttribute('href');

    const target =
        (targetSelector ? document.querySelector(targetSelector) : null) ||
        (fallbackSelector ? document.querySelector(fallbackSelector) : null) ||
        (hrefSelector ? document.querySelector(hrefSelector) : null);

    if (!scrollToElementWithOffset(target) && hrefSelector && hrefSelector.startsWith('#')) {
        window.location.hash = hrefSelector;
    }
}

function initFloatingBookingBarScroll() {
    if (window.__floatingBookingBarScrollInitialized) {
        return;
    }

    const triggers = document.querySelectorAll('[data-scroll-target]');

    if (!triggers.length) {
        return;
    }

    triggers.forEach(trigger => {
        trigger.addEventListener('click', handleScrollTriggerClick);
    });

    window.__floatingBookingBarScrollInitialized = true;
}

function initFloatingBookingBarFooterObserver() {
    if (window.__floatingBookingBarFooterObserverInitialized) {
        return;
    }

    const floatingBar = document.querySelector('.floating-booking-bar');
    const footer = document.querySelector('footer');
    const bookingSection = document.getElementById('workshop-booking');
    const stripeSection = document.getElementById('stripe-checkout-card');
    const observerTargets = [footer, bookingSection, stripeSection].filter(Boolean);

    if (!floatingBar || !observerTargets.length) {
        return;
    }

    const toggleBar = shouldHide => {
        floatingBar.classList.toggle('is-hidden', shouldHide);
    };

    if ('IntersectionObserver' in window) {
        const intersectingTargets = new Set();
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    intersectingTargets.add(entry.target);
                } else {
                    intersectingTargets.delete(entry.target);
                }
            });

            toggleBar(intersectingTargets.size > 0);
        }, {
            rootMargin: '0px 0px -10% 0px',
        });

        observerTargets.forEach(target => observer.observe(target));
    } else {
        const handleScroll = () => {
            const shouldHide = observerTargets.some(target => {
                const rect = target.getBoundingClientRect();
                return rect.top < window.innerHeight && rect.bottom > 0;
            });

            toggleBar(shouldHide);
        };

        window.addEventListener('scroll', handleScroll);
        handleScroll();
    }

    window.__floatingBookingBarFooterObserverInitialized = true;
}

function initFloatingBookingBarTabBarSync() {
    if (window.__floatingBookingBarTabBarSyncInitialized) {
        return;
    }

    const floatingBar = document.querySelector('.floating-booking-bar');
    const mobileTabBar = document.querySelector('[data-mobile-tab-bar]');

    if (!floatingBar || !mobileTabBar || typeof window === 'undefined') {
        return;
    }

    const mediaQuery = typeof window.matchMedia === 'function'
        ? window.matchMedia('(max-width: 768px)')
        : null;
    const BASE_SPACING = 12;

    const updateOffset = () => {
        if (!mediaQuery || !mediaQuery.matches) {
            floatingBar.style.removeProperty('--floating-booking-mobile-offset');
            return;
        }

        const navRect = mobileTabBar.getBoundingClientRect();
        const navHeight = navRect && navRect.height ? navRect.height : 0;
        const isHidden = mobileTabBar.classList.contains('mobile-tab-bar--hidden');
        const offset = isHidden ? BASE_SPACING : Math.round(navHeight);

        floatingBar.style.setProperty('--floating-booking-mobile-offset', `${offset}px`);
    };

    if (typeof MutationObserver === 'function') {
        const observer = new MutationObserver(updateOffset);
        observer.observe(mobileTabBar, { attributes: true, attributeFilter: ['class'] });
    } else {
        window.addEventListener('scroll', updateOffset, { passive: true });
    }

    window.addEventListener('resize', updateOffset);

    if (mediaQuery) {
        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', updateOffset);
        } else if (typeof mediaQuery.addListener === 'function') {
            mediaQuery.addListener(updateOffset);
        }
    }

    updateOffset();
    window.__floatingBookingBarTabBarSyncInitialized = true;
}

function initWorkshopReviewRating() {
    const ratingOptions = Array.from(document.querySelectorAll('[data-rating-option]'));

    if (!ratingOptions.length) {
        return;
    }

    const updateRatingStyles = selectedValue => {
        ratingOptions.forEach(label => {
            const value = label.dataset.ratingOption;
            const isSelected = value === String(selectedValue);

            label.classList.toggle('border-amber-300', isSelected);
            label.classList.toggle('bg-amber-50', isSelected);
            label.classList.toggle('border-gray-200', !isSelected);
            label.classList.toggle('bg-gray-50', !isSelected);
        });
    };

    ratingOptions.forEach(label => {
        const input = label.querySelector('input[type="radio"]');

        if (!input) {
            return;
        }

        label.addEventListener('click', () => {
            input.checked = true;
            updateRatingStyles(input.value);
        });

        input.addEventListener('change', () => {
            updateRatingStyles(input.value);
        });
    });

    const initialChecked = document.querySelector('input[name="rating"]:checked');

    if (initialChecked) {
        updateRatingStyles(initialChecked.value);
    }
}

function bootWorkshopDetailsScripts() {
    initHeroExcerptToggle();
    initWorkshopDetailsBooking();
    initWorkshopReviewRating();
    initFloatingBookingBarScroll();
    initFloatingBookingBarFooterObserver();
    initFloatingBookingBarTabBarSync();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootWorkshopDetailsScripts);
} else {
    bootWorkshopDetailsScripts();
}
</script>
@endpush

@if($whatsappBookingEnabled)
    @push('scripts')
        <script>
(function bootstrapWhatsAppBooking(config) {
    if (window.WhatsAppBooking) {
        window.WhatsAppBooking.configure(config);
        window.WhatsAppBooking.initButtons();
        window.WhatsAppBooking.initInquiryButtons();
        return;
    }

    window.__WHATSAPP_BOOKING_PENDING__ = window.__WHATSAPP_BOOKING_PENDING__ || [];
    window.__WHATSAPP_BOOKING_PENDING__.push(function(instance) {
        instance.configure(config);
        instance.initButtons();
        instance.initInquiryButtons();
    });
})(@json($whatsappBookingPayload));
        </script>
    @endpush
@endif












