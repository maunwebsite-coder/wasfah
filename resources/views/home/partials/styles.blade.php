    <style data-page-style="home">
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #fcfcfc;
            background: linear-gradient(180deg, rgba(244, 246, 255, 0.4), rgba(255, 255, 255, 0.85) 40%, #ffffff 100%);
            color: #1f2937;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Keep navbar flush to the top on home page */
        header[data-navbar-layer] {
            top: 0 !important;
            margin-top: 0 !important;
            padding-bottom: 0 !important;
        }

        :root {
            --wasfa-primary: #6b2e30;
            --wasfa-primary-strong: #7f3a3d;
            --wasfa-primary-soft: #f8edee;
            --wasfa-neutral-900: #1f2937;
            --wasfa-neutral-700: #374151;
            --wasfa-neutral-500: #6b7280;
            --wasfa-card-border: rgba(107, 46, 48, 0.12);
            --wasfa-card-shadow: 0 26px 52px rgba(15, 23, 42, 0.08);
        }

        .home-page-wrapper {
            display: flex;
            flex-direction: column;
            gap: clamp(2.75rem, 6vw, 4.5rem);
        }

        .home-section-shell {
            position: relative;
            border-radius: 2.25rem;
            padding: clamp(2rem, 5vw, 3.25rem);
            background: linear-gradient(135deg, rgba(244, 246, 255, 0.88), rgba(255, 255, 255, 0.96));
            box-shadow: var(--wasfa-card-shadow);
            border: 1px solid var(--wasfa-card-border);
            overflow: hidden;
        }
        .home-section-shell:not(.home-hero-shell)::before,
        .home-section-shell:not(.home-hero-shell)::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
        }
        .home-section-shell:not(.home-hero-shell)::before {
            inset-inline-start: -120px;
            inset-block-start: -110px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(107, 46, 48, 0.09), transparent 68%);
        }
        .home-section-shell:not(.home-hero-shell)::after {
            inset-inline-end: -150px;
            inset-block-end: -160px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(107, 46, 48, 0.06), transparent 72%);
        }
        .home-section-shell > * {
            position: relative;
            z-index: 1;
        }

        .section-heading {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: clamp(1.5rem, 3vw, 2.75rem);
            text-align: center;
        }
        .section-heading.is-left {
            text-align: start;
            align-items: flex-start;
        }
        .section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            align-self: center;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--wasfa-primary-strong);
            background: rgba(107, 46, 48, 0.12);
            border-radius: 9999px;
            padding: 0.5rem 1.35rem;
        }
        .section-heading.is-left .section-eyebrow {
            align-self: flex-start;
        }
        .section-title {
            font-size: clamp(2.1rem, 4.5vw, 2.85rem);
            font-weight: 800;
            color: var(--wasfa-neutral-900);
        }
        .section-subtitle {
            max-width: 52ch;
            margin: 0 auto;
            color: var(--wasfa-neutral-500);
            font-size: clamp(1rem, 2.25vw, 1.15rem);
            line-height: 1.8;
        }
        .section-heading.is-left .section-subtitle {
            margin-inline-start: 0;
        }
        .section-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 700;
            color: var(--wasfa-primary-strong);
            transition: color 0.2s ease;
            padding: 0.45rem 1.05rem;
        }
        .section-cta:hover {
            color: var(--wasfa-primary);
        }

        /* Hide scrollbar for a cleaner look */
        .swiper-wrapper {
            background: transparent;
            scrollbar-width: none; /* For Firefox */
        }
        .swiper-wrapper::-webkit-scrollbar {
            display: none; /* For Chrome, Safari, and Opera */
        }

        /* Workshop Cards Styling */
        .workshop-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .workshop-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .featured-workshops-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
        }
        .featured-workshops-grid > * {
            height: 100%;
        }
        @media (min-width: 768px) {
            .featured-workshops-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1280px) {
            .featured-workshops-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 1536px) {
            .featured-workshops-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        /* Home tools slider */
        .home-tools-section {
            background: linear-gradient(145deg, rgba(244, 246, 255, 0.45), rgba(255, 255, 255, 0.85));
            border-radius: 2rem;
            box-shadow: 0 30px 60px rgba(107, 46, 48, 0.08);
            border: 1px solid rgba(107, 46, 48, 0.12);
        }
        .home-tools-swiper .swiper-slide {
            height: auto;
        }
        .home-tool-card {
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(107, 46, 48, 0.08);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .home-tool-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 55px rgba(107, 46, 48, 0.16);
        }
        .home-tool-card__image {
            position: relative;
            background: linear-gradient(140deg, rgba(255, 237, 213, 0.5), rgba(254, 215, 170, 0.35));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.75rem;
            min-height: 220px;
            aspect-ratio: 4 / 3;
        }
        .home-tool-card__image img {
            width: 100%;
            height: 100%;
            max-height: 160px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .home-tool-card:hover .home-tool-card__image img {
            transform: scale(1.07);
        }
        .home-tool-card__category {
            position: absolute;
            inset-inline-end: 1.25rem;
            inset-block-start: 1.25rem;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--wasfa-primary-strong);
            box-shadow: 0 8px 18px rgba(107, 46, 48, 0.18);
        }
        .home-tool-card__body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            flex: 1;
        }
        .home-tool-card__title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--wasfa-neutral-900);
            line-height: 1.5;
        }
        .home-tool-card__rating {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
            color: var(--wasfa-primary-strong);
            font-size: 0.9rem;
        }
        .home-tool-card__rating i {
            color: #9f5e63;
        }
        .home-tool-card__price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--wasfa-primary-strong);
        }
        .home-tool-card__actions {
            display: flex;
            gap: 0.6rem;
            margin-top: auto;
            flex-wrap: wrap;
        }
        .home-tool-card__actions a {
            flex: 1;
        }
        .home-tools-nav {
            display: flex;
            gap: 0.75rem;
        }
        .home-tools-nav button {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid rgba(107, 46, 48, 0.25);
            background: #ffffff;
            color: var(--wasfa-primary-strong);
            box-shadow: 0 18px 32px rgba(107, 46, 48, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .home-tools-nav button:hover {
            background: var(--wasfa-primary);
            color: #ffffff;
            border-color: transparent;
            transform: translateY(-2px);
        }
        .home-tools-nav button.swiper-button-disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* Workshop Card Image Improvements */
        .workshop-card img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
        }
        
        .workshop-card:hover img {
            transform: scale(1.05);
        }
        .premium-workshop-media {
            aspect-ratio: 3 / 2;
            min-height: 260px;
        }
        .premium-workshop-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Home hero layout */
        .home-hero-shell {
            position: relative;
            border-radius: 2rem;
            padding: clamp(1.5rem, 4vw, 2.75rem);
            margin-top: clamp(4rem, 7vw, 5.5rem); /* keep the hero clear of the sticky navbar */
            background: linear-gradient(135deg, #ffffff 0%, #f8edee 45%, #ffffff 100%);
            box-shadow: 0 30px 60px rgba(107, 46, 48, 0.12);
            overflow: hidden;
        }
        .home-hero-shell::after {
            content: "";
            position: absolute;
            inset-inline-end: -120px;
            top: -60px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle at center, rgba(251, 191, 36, 0.35), transparent 70%);
            pointer-events: none;
        }
        .home-hero-grid {
            position: relative;
            display: grid;
            gap: clamp(1.75rem, 4vw, 2.5rem);
            z-index: 1;
        }
        .hero-main-card {
            position: relative;
            background: #ffffff;
            border-radius: 1.8rem;
            padding: 0;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .hero-slider {
            width: 100%;
            height: 100%;
            isolation: isolate;
            margin-bottom: 0;
        }
        .hero-slider .swiper-wrapper {
            align-items: stretch;
        }
        .hero-slider .swiper-slide {
            display: flex;
            height: auto;
        }
        .hero-slide {
            display: grid;
            grid-template-areas:
                "media"
                "content";
            gap: clamp(1.25rem, 3vw, 1.75rem);
            min-height: 100%;
            width: 100%;
            align-content: center;
            flex: 1;
            padding: clamp(1.6rem, 3vw, 2.5rem);
        }
        .hero-media {
            grid-area: media;
            border-radius: 1.5rem;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255, 237, 213, 0.7), rgba(254, 215, 170, 0.3));
            position: relative;
            isolation: isolate;
            min-height: clamp(220px, 32vw, 320px);
            height: auto;
            width: 100%;
            display: flex;
        }
        .hero-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-media picture {
            width: 100%;
            height: 100%;
            flex: 1;
            display: block;
        }
        .hero-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.1), rgba(0, 0, 0, 0.05));
            mix-blend-mode: soft-light;
        }
        .hero-main-image {
            width: 100%;
            height: 100%;
            max-height: 360px;
            object-fit: cover;
            object-position: center;
            display: block;
            border-radius: inherit;
            transform: scale(1.06);
            transform-origin: center;
        }
        .hero-content {
            grid-area: content;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            justify-content: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #4d1f22;
            background: linear-gradient(135deg, rgba(143, 74, 80, 0.22), rgba(12, 63, 97, 0.22));
            border-radius: 9999px;
            padding: 0.28rem 0.8rem;
            box-shadow: 0 8px 14px rgba(107, 46, 48, 0.16);
            align-self: flex-start;
        }
        .hero-badge.is-right,
        .hero-badge.is-end {
            align-self: flex-end;
            margin-inline-start: auto;
        }
        .hero-title {
            font-size: clamp(1.9rem, 4.8vw, 3rem);
            font-weight: 800;
            color: #1f2937;
            line-height: 1.25;
        }
        .hero-description {
            font-size: clamp(1rem, 2.7vw, 1.125rem);
            color: #4b5563;
            line-height: 1.8;
            max-width: 48ch;
        }
        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }
        .hero-actions--balanced {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .hero-actions--balanced .hero-action {
            width: 100%;
            min-height: 3rem;
            text-align: center;
            justify-content: center;
            line-height: 1.45;
            padding-inline: 1.15rem;
        }
        .hero-actions--balanced .hero-action span {
            text-align: center;
        }
        .hero-features {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 0.6rem;
        }
        .hero-feature {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.7;
        }
        .hero-feature i {
            color: var(--wasfa-primary-strong);
            font-size: 1rem;
            margin-top: 0.15rem;
        }
        .hero-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border-radius: 9999px;
            font-weight: 700;
            padding: 0.7rem 1.35rem;
            font-size: 0.97rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hero-action i {
            font-size: 0.9rem;
        }
        .hero-action:hover {
            transform: translateY(-2px);
        }
        .primary-action {
            background: linear-gradient(135deg, #6b2e30, #8f4a50);
            color: #ffffff;
            box-shadow: 0 12px 22px rgba(107, 46, 48, 0.25);
        }
        .primary-action:hover {
            box-shadow: 0 16px 30px rgba(107, 46, 48, 0.32);
        }
        .secondary-action {
            border: 2px solid rgba(107, 46, 48, 0.35);
            color: #4d1f22;
            background: rgba(255, 255, 255, 0.85);
        }
        .secondary-action:hover {
            background: rgba(255, 237, 213, 0.6);
        }
        .accent-action {
            border: 2px dashed rgba(107, 46, 48, 0.55);
            background: rgba(244, 246, 255, 0.85);
            color: #4d1f22;
            box-shadow: 0 10px 18px rgba(107, 46, 48, 0.18);
        }
        .accent-action:hover {
            background: rgba(255, 237, 213, 0.92);
            border-color: rgba(234, 88, 12, 0.6);
            color: #7c2d12;
        }
        .hero-slider-pagination {
            position: static;
            margin-top: clamp(1.15rem, 2.5vw, 1.75rem);
            display: flex;
            justify-content: center;
            gap: 0.55rem;
        }
        .hero-slider-pagination .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: rgba(107, 46, 48, 0.28);
            opacity: 1;
        }
        .hero-slider-pagination .swiper-pagination-bullet-active {
            background: var(--wasfa-primary-strong);
        }
        .hero-slider-nav {
            position: absolute;
            inset-inline-end: clamp(1.4rem, 3vw, 2.3rem);
            inset-block-end: clamp(1.2rem, 2.6vw, 1.9rem);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 5;
        }
        .hero-slider-nav button {
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            border: 1px solid rgba(107, 46, 48, 0.28);
            background: #ffffff;
            color: var(--wasfa-primary-strong);
            box-shadow: 0 16px 30px rgba(107, 46, 48, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .hero-slider-nav button:hover {
            background: var(--wasfa-primary);
            color: #ffffff;
            border-color: transparent;
            transform: translateY(-2px);
        }
        .hero-slider-nav button.swiper-button-disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .hero-latest-card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 1.8rem;
            padding: clamp(1.25rem, 3vw, 2rem);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
            min-width: 0;
            backdrop-filter: blur(12px);
        }
        .hero-latest-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
            padding-bottom: 0.85rem;
        }
        .hero-latest-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        .hero-latest-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 600;
            color: #6b2e30;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }
        .hero-latest-link:hover {
            color: #7f3a3d;
        }
        .hero-latest-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
            align-content: start;
            scrollbar-width: none;
        }
        .hero-latest-list::-webkit-scrollbar {
            display: none;
        }
        .hero-latest-list > li {
            width: 100%;
        }
        .hero-latest-empty {
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
            padding: 2rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            grid-column: 1 / -1;
        }
        .latest-recipe-mini {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 0.85rem;
            border-radius: 1.25rem;
            background: linear-gradient(140deg, rgba(255, 255, 255, 0.98), rgba(227, 236, 255, 0.9));
            border: 1px solid rgba(107, 46, 48, 0.12);
            box-shadow: 0 12px 28px rgba(107, 46, 48, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
            color: inherit;
            text-decoration: none;
        }
        .latest-recipe-mini:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(107, 46, 48, 0.2);
            border-color: rgba(107, 46, 48, 0.25);
            background: linear-gradient(140deg, rgba(255, 255, 255, 1), rgba(203, 215, 255, 0.9));
        }
        .latest-recipe-thumb {
            width: 56px;
            height: 56px;
            border-radius: 1.1rem;
            overflow: hidden;
            flex-shrink: 0;
            background: rgba(107, 46, 48, 0.1);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
        }
        .latest-recipe-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .latest-recipe-mini:hover .latest-recipe-thumb img {
            transform: scale(1.05);
        }
        .latest-recipe-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .latest-recipe-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--wasfa-primary);
            margin: 0;
            line-height: 1.35;
            transition: color 0.2s ease;
        }
        .latest-recipe-mini:hover .latest-recipe-title {
            color: var(--wasfa-primary-strong);
        }
        .latest-recipe-meta {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.8rem;
            color: #6b7280;
            flex-wrap: wrap;
        }
        .latest-recipe-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }
        .latest-recipe-meta-item i {
            font-size: 0.8rem;
            color: var(--wasfa-primary-strong);
        }
        .latest-recipe-meta-item.is-rating i {
            color: #9f5e63;
        }
        .latest-recipe-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.7rem;
            border-radius: 9999px;
            background: rgba(107, 46, 48, 0.12);
            color: var(--wasfa-primary-strong);
            font-size: 0.75rem;
            font-weight: 600;
            align-self: flex-start;
        }
        .latest-workshop-chip {
            border: 1px solid transparent;
        }
        .latest-workshop-chip.is-online {
            background: linear-gradient(135deg, #6b2e30, #8f4a50);
            color: #ffffff;
            border-color: #6b2e30;
            box-shadow: 0 8px 18px rgba(107, 46, 48, 0.25);
        }
        .latest-workshop-chip.is-onsite {
            background: linear-gradient(135deg, #5a2628, #b2767d);
            color: #f8fafc;
            border-color: #6b2e30;
            box-shadow: 0 8px 18px rgba(107, 46, 48, 0.18);
        }
        .hero-latest-empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 9999px;
            background: rgba(229, 231, 235, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 1.35rem;
        }

        .home-highlight-shell {
            background: linear-gradient(135deg, rgba(244, 246, 255, 0.8), rgba(255, 255, 255, 0.96));
        }
        .highlight-card {
            position: relative;
            border-radius: 1.9rem;
            padding: clamp(1.75rem, 4vw, 2.85rem);
            background: linear-gradient(135deg, rgba(107, 46, 48, 0.95), rgba(107, 46, 48, 0.88), rgba(143, 74, 80, 0.85));
            box-shadow: 0 28px 50px rgba(107, 46, 48, 0.28);
            color: #fff;
            overflow: hidden;
        }
        .highlight-card::before,
        .highlight-card::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
        }
        .highlight-card::before {
            inset-inline-start: -110px;
            inset-block-start: -130px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.32), transparent 72%);
            opacity: 0.35;
        }
        .highlight-card::after {
            inset-inline-end: -120px;
            inset-block-end: -150px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 70%);
            opacity: 0.4;
        }
        .highlight-card.is-empty {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(255, 237, 213, 0.94));
            box-shadow: 0 24px 45px rgba(15, 23, 42, 0.08);
            color: var(--wasfa-neutral-900);
        }
        .highlight-card.is-empty::before,
        .highlight-card.is-empty::after {
            background: radial-gradient(circle, rgba(107, 46, 48, 0.1), transparent 70%);
            opacity: 1;
        }
        .highlight-grid {
            position: relative;
            display: grid;
            gap: clamp(1.5rem, 3vw, 2.5rem);
        }
        @media (min-width: 1024px) {
            .highlight-grid {
                grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
                align-items: stretch;
            }
        }
        .highlight-content {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            justify-content: center;
        }
        .highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 9999px;
            padding: 0.55rem 1.4rem;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            width: fit-content;
        }
        .highlight-card.is-empty .highlight-badge {
            background: rgba(107, 46, 48, 0.12);
            color: var(--wasfa-primary-strong);
        }
        .highlight-title {
            font-size: clamp(1.85rem, 3.5vw, 2.4rem);
            font-weight: 800;
            line-height: 1.3;
        }
        .highlight-card.is-empty .highlight-title {
            color: var(--wasfa-neutral-900);
        }
        .highlight-description {
            font-size: clamp(1rem, 2.4vw, 1.1rem);
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.85);
        }
        .highlight-card.is-empty .highlight-description {
            color: var(--wasfa-neutral-500);
        }
        .highlight-meta {
            display: grid;
            gap: 0.85rem;
            margin-top: 0.5rem;
        }
        .highlight-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-size: 0.98rem;
            font-weight: 600;
        }
        .highlight-meta .meta-item i {
            font-size: 1.05rem;
        }
        .highlight-card.is-empty .highlight-meta .meta-item {
            color: var(--wasfa-neutral-500);
        }
        .highlight-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 1rem;
        }
        .highlight-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-weight: 700;
            border-radius: 9999px;
            padding: 0.72rem 1.35rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }
        .highlight-cta:hover {
            transform: translateY(-2px);
        }
        .highlight-cta.primary {
            background: #ffffff;
            color: #16a34a;
            box-shadow: 0 14px 28px rgba(15, 118, 110, 0.18);
        }
        .highlight-cta.primary:hover {
            background: rgba(255, 255, 255, 0.92);
        }
        .highlight-cta.secondary {
            border: 2px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            background: rgba(255, 255, 255, 0.12);
        }
        .highlight-card.is-empty .highlight-cta.primary {
            background: linear-gradient(135deg, #6b2e30, #8f4a50);
            color: #ffffff;
            box-shadow: 0 16px 32px rgba(107, 46, 48, 0.25);
        }
        .highlight-card.is-empty .highlight-cta.secondary {
            border-color: rgba(107, 46, 48, 0.25);
            color: var(--wasfa-primary-strong);
            background: rgba(107, 46, 48, 0.08);
        }
        .highlight-cta.accent {
            background: linear-gradient(135deg, #6b2e30, #8f4a50);
            color: #ffffff;
            box-shadow: 0 16px 32px rgba(107, 46, 48, 0.22);
        }
        .highlight-cta.outline {
            border: 2px solid rgba(107, 46, 48, 0.25);
            color: var(--wasfa-primary-strong);
            background: rgba(107, 46, 48, 0.05);
        }
        .highlight-media {
            position: relative;
            border-radius: 1.6rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
        }
        .highlight-card.is-empty .highlight-media {
            background: rgba(255, 255, 255, 0.55);
        }
        .highlight-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            min-height: 260px;
        }
        .highlight-price-badge {
            position: absolute;
            top: 1.5rem;
            inset-inline-end: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 9999px;
            padding: 0.5rem 1.2rem;
            font-weight: 700;
        }
        .highlight-card.is-empty .highlight-price-badge {
            display: none;
        }
        .highlight-media .media-icon {
            font-size: clamp(3rem, 6vw, 4.5rem);
            color: rgba(107, 46, 48, 0.35);
        }

        .home-empty-state {
            text-align: center;
            padding: 3rem 2rem;
            border-radius: 1.75rem;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        }
        .home-empty-state-icon {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(107, 46, 48, 0.15), rgba(143, 74, 80, 0.22));
            color: var(--wasfa-primary-strong);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.25rem;
        }
        .home-empty-state p {
            color: var(--wasfa-neutral-500);
            font-size: 1.05rem;
            line-height: 1.7;
            max-width: 45ch;
            margin: 0 auto 1.5rem;
        }
        .home-empty-state .section-cta {
            background: linear-gradient(135deg, #6b2e30, #8f4a50);
            color: #fff;
            padding: 0.85rem 1.8rem;
            border-radius: 9999px;
            box-shadow: 0 16px 32px rgba(107, 46, 48, 0.22);
        }
        .home-empty-state .section-cta:hover {
            color: #fff;
        }

        .feature-grid {
            display: grid;
            gap: 1.8rem;
        }
        @media (min-width: 768px) {
            .feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .feature-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .feature-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            text-align: center;
            background: #ffffff;
            border-radius: 1.6rem;
            padding: 2rem;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(107, 46, 48, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px rgba(107, 46, 48, 0.18);
        }
        .feature-icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(107, 46, 48, 0.15), rgba(143, 74, 80, 0.25));
            color: var(--wasfa-primary-strong);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: inset 0 0 0 1px rgba(107, 46, 48, 0.12);
        }
        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--wasfa-neutral-900);
        }
        .feature-card p {
            color: var(--wasfa-neutral-500);
            line-height: 1.7;
        }

        .workshop-card {
            background: #ffffff;
            border-radius: 1.8rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(107, 46, 48, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .workshop-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 26px 60px rgba(107, 46, 48, 0.18);
        }
        .workshop-image-wrapper {
            position: relative;
        }
        .workshop-status-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.45);
        }
        .workshop-price-chip {
            position: absolute;
            top: 1.25rem;
            inset-inline-end: 1.25rem;
            background: rgba(17, 24, 39, 0.65);
            color: #ffffff;
            padding: 0.45rem 1.1rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
        }
        .workshop-body {
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            height: 100%;
            gap: 1rem;
        }
        .workshop-meta {
            display: grid;
            gap: 0.6rem;
            color: var(--wasfa-neutral-500);
            font-size: 0.95rem;
        }
        .workshop-meta .meta-line {
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }
        .workshop-meta .meta-line i {
            color: var(--wasfa-primary-strong);
        }
        .workshop-actions {
            margin-top: auto;
            display: flex;
            gap: 0.55rem;
            flex-wrap: wrap;
        }
        .workshop-actions .highlight-cta {
            flex: 1;
            min-width: 0;
        }

        .featured-workshop-card {
            position: relative;
        }
        .featured-workshop-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0));
            pointer-events: none;
        }
        .featured-workshop-actions > * {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .featured-workshop-actions > *:hover {
            transform: translateY(-2px);
        }
        .featured-workshop-media {
            aspect-ratio: 16 / 9;
            min-height: 360px;
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            isolation: isolate;
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.12), transparent 45%),
                        linear-gradient(135deg, #0f172a, #b45309);
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.25);
        }
        .featured-workshop-media::after {
            content: "";
            position: absolute;
            inset: -12% -8%;
            background: radial-gradient(circle at 25% 30%, rgba(255, 255, 255, 0.15), transparent 48%),
                        radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.12), transparent 46%);
            filter: blur(10px);
            opacity: 0.6;
            z-index: 0;
            pointer-events: none;
        }
        .featured-media-backdrop {
            position: absolute;
            inset: -8%;
            background: linear-gradient(140deg, rgba(255, 237, 213, 0.4), rgba(251, 191, 36, 0.2));
            filter: blur(28px);
            transform: scale(1.08);
            z-index: 0;
        }
        .featured-workshop-media img {
            position: relative;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.02);
            transition: transform 0.35s ease, filter 0.35s ease;
            z-index: 1;
        }
        .featured-media-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(15, 23, 42, 0.55) 0%, rgba(15, 23, 42, 0.2) 42%, rgba(251, 191, 36, 0.3) 100%);
            mix-blend-mode: multiply;
            opacity: 0.92;
            z-index: 2;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .featured-media-badge {
            position: absolute;
            inset-inline-end: 1.4rem;
            inset-block-start: 1.2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: #92400e;
            font-weight: 800;
            border-radius: 999px;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.18);
            backdrop-filter: blur(12px);
            z-index: 3;
        }
        .featured-media-badge i {
            color: #f59e0b;
        }
        .featured-media-meta {
            position: absolute;
            inset-inline-start: 1.25rem;
            inset-block-end: 1.25rem;
            display: grid;
            gap: 0.45rem;
            min-width: min(280px, 82%);
            padding: 0.9rem 1.05rem;
            background: rgba(15, 23, 42, 0.55);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 1.2rem;
            box-shadow: 0 22px 44px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(14px);
            z-index: 3;
        }
        .featured-media-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-weight: 700;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .featured-media-meta i {
            color: #fbbf24;
        }
        .featured-workshop-media:hover img {
            transform: scale(1.06);
            filter: saturate(1.08) contrast(1.05);
        }
        .featured-workshop-media:hover .featured-media-overlay {
            opacity: 0.8;
        }
        @media (max-width: 640px) {
            .hero-media {
                aspect-ratio: 1 / 1;
                min-height: auto;
                height: auto;
            }
            .hero-main-image,
            .hero-media video {
                height: 100%;
                max-height: none;
            }
        }

        @media (min-width: 1024px) {
            .home-hero-grid {
                grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
                align-items: stretch;
            }
            .hero-slide {
                grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
                grid-template-areas: "content media";
                align-items: center;
            }
            .hero-media {
                height: 100%;
                max-height: none;
            }
            .hero-slider-nav {
                inset-block-start: 50%;
                inset-block-end: auto;
                transform: translateY(-50%);
            }
        }

        @media (max-width: 1024px) {
            .home-hero-shell::after {
                inset-inline-end: -160px;
                top: -110px;
                width: 280px;
                height: 280px;
            }
            .hero-slide {
                padding: 0;
                gap: 0;
                border-radius: 1.65rem;
                overflow: hidden;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 246, 255, 0.98));
            }
            .hero-media {
                aspect-ratio: 16 / 9;
                height: auto;
                min-height: clamp(220px, 55vw, 360px);
                border-radius: 0;
            }
            .hero-content {
                padding: clamp(1.15rem, 4vw, 2.25rem);
                background: rgba(255, 255, 255, 0.96);
            }
            .featured-workshop-media {
                aspect-ratio: 16 / 9;
                min-height: 300px;
                width: 100%;
                height: 100%;
            }
            .featured-media-badge {
                inset-inline-end: 1.1rem;
                inset-block-start: 1.1rem;
                padding: 0.55rem 0.9rem;
                font-size: 0.9rem;
            }
            .featured-media-meta {
                inset-inline-start: 1.1rem;
                inset-block-end: 1.1rem;
                min-width: min(260px, 88%);
                padding: 0.8rem 0.95rem;
            }
            .featured-media-meta .meta-item {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .home-hero-shell {
                padding: 1.75rem;
                border-radius: 1.75rem;
            }
            .home-hero-grid {
                gap: 1.5rem;
            }
            .hero-main-card {
                padding: 0;
            }
            .hero-slider {
                margin-bottom: 0;
            }
            .hero-slide {
                padding: 0;
                gap: 0;
            }
            .hero-actions {
                flex-direction: column;
            }
            .hero-action {
                width: 100%;
            }
            .hero-slider-nav {
                display: none;
            }
            .hero-slider-pagination {
                margin-top: 1.25rem;
                display: none !important;
            }
            .hero-latest-card {
                padding: 1.5rem;
            }
            .hero-latest-list {
                grid-auto-flow: column;
                grid-template-rows: repeat(2, minmax(0, auto));
                grid-template-columns: calc(100% - 1.25rem);
                grid-auto-columns: calc(100% - 1.25rem);
                gap: 0.75rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                scroll-snap-type: x proximity;
                overscroll-behavior-x: contain;
            }
            .hero-latest-list > li {
                scroll-snap-align: start;
                scroll-snap-stop: always;
            }
            .latest-recipe-mini {
                padding: 0.7rem 0.75rem;
            }
            .latest-recipe-thumb {
                width: 52px;
                height: 52px;
            }
            .latest-recipe-title {
                font-size: 0.9rem;
            }
            .featured-workshop-section {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }
            .featured-workshop-media {
                aspect-ratio: 16 / 9;
                min-height: 240px;
            }
            .featured-media-badge {
                inset-inline-end: 0.9rem;
                inset-block-start: 0.9rem;
                padding: 0.5rem 0.85rem;
                font-size: 0.85rem;
            }
            .featured-media-meta {
                inset-inline-start: 0.9rem;
                inset-block-end: 0.9rem;
                min-width: min(230px, 90%);
                padding: 0.75rem 0.85rem;
                gap: 0.35rem;
            }
            .featured-media-meta .meta-item {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 640px) {
            .home-hero-shell {
                padding: 0.6rem;
                border-radius: 1.5rem;
            }
            .hero-slide {
                padding: 0;
                gap: 0;
            }
            .hero-slider {
                margin-bottom: 0;
            }
            .hero-main-card {
                padding: 0;
                border-radius: 1.5rem;
            }
            .hero-actions {
                gap: 0.45rem;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            .hero-action {
                flex: 0 1 auto;
                min-width: 140px;
                padding: 0.65rem 1.05rem;
                font-size: 0.95rem;
                min-height: 44px;
            }
            .hero-badge {
                font-size: 0.35rem;
                padding: 0.18rem 0.45rem;
            }
            .hero-main-image {
                max-height: none;
                border-radius: 0;
            }
            .hero-media {
                min-height: 0;
                height: auto;
                aspect-ratio: 1 / 1;
                width: 100%;
                max-width: none;
                margin: 0;
                border-radius: 0;
            }
            .hero-media picture,
            .hero-media img,
            .hero-media video {
                height: 100%;
                width: 100%;
                object-fit: cover;
                border-radius: 0;
            }
            .hero-content {
                gap: 0.55rem;
                padding: 0.9rem 1rem;
            }
            .hero-title {
                font-size: 1.4rem;
            }
            .hero-description {
                font-size: 0.85rem;
            }
            .hero-features {
                gap: 0.45rem;
            }
            .hero-feature {
                font-size: 0.82rem;
            }
            .hero-latest-card {
                border-radius: 1.5rem;
                padding: 1.05rem;
                gap: 0.9rem;
            }
            .hero-latest-header h2 {
                font-size: 1.1rem;
            }
            .hero-latest-link {
                font-size: 0.85rem;
            }
            .hero-latest-list {
                gap: 0.65rem;
                grid-template-columns: calc(100% - 1.75rem);
                grid-auto-columns: calc(100% - 1.75rem);
            }
            .home-tools-section {
                border-radius: 1.5rem;
                padding: 1.5rem;
            }
            .home-tool-card__image {
                min-height: 160px;
                padding: 1.25rem;
            }
            .home-tool-card__image img {
                max-height: 120px;
            }
            .home-tool-card__body {
                padding: 1.05rem;
                gap: 0.7rem;
            }
            .home-tool-card__actions {
                flex-direction: column;
            }
            .latest-recipe-mini {
                padding: 0.6rem 0.65rem;
                border-radius: 1rem;
            }
            .latest-recipe-thumb {
                width: 48px;
                height: 48px;
                border-radius: 1rem;
            }
            .latest-recipe-title {
                font-size: 0.82rem;
            }
            .latest-recipe-meta {
                gap: 0.45rem;
                font-size: 0.72rem;
            }
            .latest-recipe-chip {
                font-size: 0.65rem;
                padding: 0.2rem 0.6rem;
            }
            .card-container {
                width: 100%;
                height: auto;
            }
            .feed-videos-swiper .swiper-slide {
                width: min(72vw, 180px);
                height: auto;
            }
            .feed-videos-swiper .feed-video-card {
                min-height: 180px;
            }
            .featured-workshop-card {
                border-radius: 1.5rem;
            }
            .featured-workshop-section {
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }
            .featured-workshop-media {
                min-height: 210px;
                aspect-ratio: 16 / 9;
            }
            .featured-media-badge {
                inset-inline-end: 0.85rem;
                inset-block-start: 0.8rem;
                padding: 0.45rem 0.8rem;
                font-size: 0.82rem;
            }
            .featured-media-meta {
                inset-inline-start: 0.8rem;
                inset-block-end: 0.8rem;
                min-width: min(210px, 92%);
                padding: 0.7rem 0.8rem;
                gap: 0.3rem;
            }
            .featured-media-meta .meta-item {
                font-size: 0.82rem;
            }
        }

        @media (max-width: 480px) {
            .featured-workshop-section {
                padding-top: 1.35rem !important;
                padding-bottom: 1.35rem !important;
            }
            .featured-workshop-media {
                min-height: 190px;
                aspect-ratio: 16 / 9;
            }
            .featured-media-badge {
                inset-inline-end: 0.7rem;
                inset-block-start: 0.7rem;
                padding: 0.4rem 0.7rem;
                font-size: 0.78rem;
            }
            .featured-media-meta {
                inset-inline-start: 0.7rem;
                inset-block-end: 0.7rem;
                min-width: min(200px, 94%);
                padding: 0.65rem 0.75rem;
            }
            .featured-media-meta .meta-item {
                font-size: 0.78rem;
            }
            .hero-title {
                font-size: 1.2rem;
            }
            .hero-description {
                font-size: 0.78rem;
            }
            .hero-feature {
                font-size: 0.78rem;
            }
            .hero-actions {
                gap: 0.4rem;
                justify-content: center;
            }
            .hero-action {
                flex: 1 1 48%;
                min-width: 150px;
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
                min-height: 44px;
            }
            .hero-media {
                height: auto;
                min-height: 0;
                aspect-ratio: 1 / 1;
                width: 100%;
                max-width: none;
                margin: 0;
                border-radius: 0;
            }
            .hero-latest-header h2 {
                font-size: 1.35rem;
            }
        }
        
        /* Mobile Responsive Images */
        @media (max-width: 768px) {
            .workshop-card img {
                height: 220px;
                object-fit: cover;
                object-position: center;
                min-height: 200px;
                max-height: 250px;
            }
            
            /* Featured workshop image mobile fix */
            .featured-workshop-image {
                height: 210px;
                object-fit: cover;
                object-position: center;
                min-height: 180px;
                max-height: 240px;
            }
        }
        
        @media (max-width: 480px) {
            .workshop-card img {
                height: 200px;
                object-fit: cover;
                object-position: center;
                min-height: 180px;
                max-height: 220px;
            }
            
            /* Featured workshop image mobile fix */
            .featured-workshop-image {
                height: 190px;
                object-fit: cover;
                object-position: center;
                min-height: 160px;
                max-height: 220px;
            }
        }

        /* Flip Card Styles */
        .card-container {
            perspective: 1000px;
            width: clamp(240px, 28vw, 280px);
            height: 400px;
            cursor: pointer;
            margin: 0;
        }

        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .card-container.is-flipped .card-inner {
            transform: rotateY(180deg);
        }

        .card-front,
        .card-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex; /* Ensure content is centered */
            flex-direction: column;
        }

        .card-front { background: #fff; }
        .card-back { background: #fff; transform: rotateY(180deg); }

        /* Swiper Settings */
        .swiper, .swiper-container {
            padding: 0 !important;
            margin: 0 !important;
        }

        .feed-videos-shell {
            position: relative;
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.04);
            padding: 0.75rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.02), 0 16px 40px rgba(0,0,0,0.28);
        }
        .feed-videos-swiper {
            /* Allow horizontal swipes while keeping vertical scroll on touch devices */
            touch-action: pan-y;
            overscroll-behavior-x: contain;
        }
        .feed-videos-meta {
            position: absolute;
            inset: 0.65rem 0.9rem auto 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
            z-index: 7;
        }
        .feed-videos-dots {
            display: inline-flex;
            gap: 0.35rem;
        }
        .feed-videos-dots span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: rgba(255,255,255,0.5);
            display: block;
        }
        .feed-videos-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.7rem;
            background: rgba(255,255,255,0.08);
            color: #f8fafc;
            font-weight: 700;
            font-size: 0.85rem;
            border-radius: 999px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .feed-videos-chip i {
            color: #fbbf24;
        }
        .feed-videos-fade {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 76px;
            pointer-events: none;
            z-index: 5;
            transition: opacity 0.35s ease;
        }
        .feed-videos-fade-left {
            left: 0;
            background: linear-gradient(90deg, rgba(17,24,39,0.95) 0%, rgba(17,24,39,0) 100%);
        }
        .feed-videos-fade-right {
            right: 0;
            background: linear-gradient(270deg, rgba(17,24,39,0.95) 0%, rgba(17,24,39,0) 100%);
        }
        .dark .feed-videos-fade-left {
            background: linear-gradient(90deg, rgba(15,23,42,0.96) 0%, rgba(15,23,42,0) 100%);
        }
        .dark .feed-videos-fade-right {
            background: linear-gradient(270deg, rgba(15,23,42,0.96) 0%, rgba(15,23,42,0) 100%);
        }

        .feed-videos-swiper .swiper-slide {
            flex: 0 0 auto;
            width: clamp(120px, 22vw, 180px);
            height: auto;
            box-sizing: border-box;
            display: flex;
            align-items: stretch;
            justify-content: center;
            background: transparent;
            transition: transform 0.35s ease, box-shadow 0.35s ease, filter 0.35s ease;
            touch-action: pan-y;
        }
        @media (min-width: 1024px) {
            .feed-videos-swiper .swiper-slide {
                width: clamp(140px, 16vw, 190px);
            }
        }
        .feed-videos-swiper .feed-video-card {
            width: 100%;
            height: 100%;
            min-height: 190px;
            border-radius: 1.5rem;
        }
        /* Smoother feed slider motion */
        .feed-videos-swiper .swiper-wrapper {
            transition-timing-function: cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        .feed-videos-swiper .swiper-slide.swiper-slide-active,
        .feed-videos-swiper .swiper-slide.swiper-slide-next,
        .feed-videos-swiper .swiper-slide.swiper-slide-prev {
            transform: none;
            filter: none;
        }

        /* Feed videos nav */
        .feed-videos-nav {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 0.35rem;
            z-index: 8;
            pointer-events: none;
        }
        .feed-videos-nav button {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.12);
            color: #e5e7eb;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
            backdrop-filter: blur(12px);
            pointer-events: auto;
        }
        .feed-videos-nav button:hover {
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.28);
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.28);
        }
        .feed-videos-nav button.swiper-button-disabled {
            opacity: 0.35;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            color: #9ca3af;
        }

        .feed-videos-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        .feed-videos-bullet {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.25);
            opacity: 1;
            transition: all 0.25s ease;
        }
        .feed-videos-bullet-active {
            width: 24px;
            background: #fbbf24;
        }

        .feed-video-card {
            border: 1px solid rgba(15, 23, 42, 0.06);
            background: #0f172a;
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.24);
        }
        .touch-device .feed-video-card iframe,
        .touch-device .feed-video-card video {
            pointer-events: none;
        }
        .feed-video-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: rgba(255,255,255,0.6);
            box-shadow: 0 0 0 1px rgba(0,0,0,0.15);
        }
        .feed-video-card:hover {
            transform: translateY(-6px);
            border-color: rgba(245, 158, 11, 0.55);
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.28);
        }

        /* Recipe Cards Enhancements - removed hover effects */

        /* Additional card styles for home page */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Additional details card - simplified palette */
        .additional-details-card {
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.25rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }
        .additional-details-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.25rem 1.35rem;
            background: #f8fafc;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }
        .additional-details-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.9rem;
            background: #e8eef4;
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.05);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
            flex-shrink: 0;
        }
        .additional-details-title {
            color: var(--wasfa-neutral-900);
            font-weight: 800;
            margin: 0.05rem 0;
        }
        .additional-details-sub {
            color: var(--wasfa-neutral-500);
            font-weight: 600;
        }
        .additional-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            padding: 1.25rem;
        }
        .additional-detail {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.95rem 1rem;
            border-radius: 0.95rem;
            border: 1px solid rgba(15, 23, 42, 0.06);
            background: #f9fbfd;
        }
        .additional-detail-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            background: #eef2f7;
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.05);
            flex-shrink: 0;
        }
        .additional-detail-label {
            color: var(--wasfa-neutral-500);
            font-weight: 600;
            margin-bottom: 0.1rem;
        }
        .additional-detail-value {
            color: var(--wasfa-neutral-900);
            font-weight: 700;
        }

        /* Quick snapshot block */
        .quick-snapshot-section {
            margin-top: clamp(0.25rem, 2vw, 1.25rem);
        }
        .quick-snapshot-shell {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            padding: clamp(1.5rem, 3vw, 2.75rem);
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.18), transparent 38%),
                radial-gradient(circle at 85% 18%, rgba(255, 237, 213, 0.2), transparent 46%),
                linear-gradient(135deg, #0d1b2a, #0a2460 55%, #0a4c7a);
            color: #f8fafc;
            box-shadow: 0 30px 60px rgba(3, 7, 18, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .quick-snapshot-shell::before,
        .quick-snapshot-shell::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
            filter: blur(12px);
            opacity: 0.5;
        }
        .quick-snapshot-shell::before {
            inset-inline-start: -80px;
            inset-block-start: -80px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.25), transparent 70%);
        }
        .quick-snapshot-shell::after {
            inset-inline-end: -90px;
            inset-block-end: -90px;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(15, 118, 110, 0.24), transparent 68%);
        }
        .quick-snapshot-header {
            position: relative;
            display: grid;
            gap: 1rem;
            align-items: center;
        }
        @media (min-width: 1024px) {
            .quick-snapshot-header {
                grid-template-columns: minmax(0, 1.25fr) minmax(0, 0.75fr);
            }
        }
        .quick-snapshot-info {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }
        .quick-snapshot-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
            width: fit-content;
        }
        .quick-snapshot-pill i {
            color: #fbbf24;
        }
        .quick-snapshot-pill-label {
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            font-weight: 700;
            opacity: 0.9;
        }
        .quick-snapshot-pill-value {
            font-weight: 800;
        }
        .quick-snapshot-eyebrow {
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #efdde1;
            margin: 0;
        }
        .quick-snapshot-title {
            margin: 0;
            font-weight: 800;
            color: #ffffff;
            font-size: clamp(1.35rem, 2.8vw, 1.8rem);
            line-height: 1.5;
            max-width: 52ch;
        }
        .quick-snapshot-rating {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.9rem 1.1rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 18px 32px rgba(0, 0, 0, 0.18);
            width: fit-content;
            margin-inline-start: auto;
        }
        .quick-snapshot-rating-value {
            font-size: clamp(2rem, 4vw, 2.6rem);
            font-weight: 800;
            line-height: 1;
        }
        .quick-snapshot-rating-meta {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            line-height: 1.3;
        }
        .quick-snapshot-rating-label {
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.8);
        }
        .quick-snapshot-rating-reviews {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #e5e7eb;
            font-weight: 600;
        }
        .quick-snapshot-rating-reviews i {
            color: #fbbf24;
        }
        .quick-snapshot-grid {
            position: relative;
            margin-top: clamp(1.2rem, 3vw, 1.8rem);
            display: grid;
            gap: 0.9rem;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        }
        .quick-snapshot-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 1.1rem;
            padding: 1rem 1.1rem;
            box-shadow: 0 18px 32px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(6px);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .quick-snapshot-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 42px rgba(0, 0, 0, 0.26);
            border-color: rgba(255, 255, 255, 0.32);
        }
        .quick-snapshot-card--accent {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(59, 130, 246, 0.16));
            border-color: rgba(255, 255, 255, 0.28);
        }
        .quick-snapshot-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.82);
            margin-bottom: 0.5rem;
        }
        .quick-snapshot-label i {
            color: #fbbf24;
            font-size: 1rem;
        }
        .quick-snapshot-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.35;
        }
        .quick-snapshot-subvalue {
            margin-top: 0.35rem;
            color: rgba(229, 231, 235, 0.86);
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Modern glass refresh */
        :root {
            --home-glass-surface: linear-gradient(145deg, rgba(255, 255, 255, 0.76), rgba(255, 255, 255, 0.58));
            --home-glass-border: rgba(255, 255, 255, 0.62);
            --home-glass-shadow: 0 26px 56px rgba(107, 46, 48, 0.14);
            --home-glass-blur: blur(16px) saturate(135%);
        }

        body {
            background:
                radial-gradient(circle at 8% 14%, rgba(143, 74, 80, 0.16), transparent 40%),
                radial-gradient(circle at 88% 10%, rgba(107, 46, 48, 0.14), transparent 42%),
                linear-gradient(180deg, #f6f8ff 0%, #f8faff 40%, #ffffff 100%);
            background-attachment: fixed;
        }

        .home-hero-shell,
        .home-tools-section,
        .home-feed-section .feed-videos-shell,
        .premium-workshops-section {
            position: relative;
            overflow: hidden;
        }

        .home-hero-shell::before,
        .home-tools-section::before,
        .premium-workshops-section::before {
            content: "";
            position: absolute;
            width: clamp(200px, 24vw, 320px);
            height: clamp(200px, 24vw, 320px);
            border-radius: 9999px;
            pointer-events: none;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.38), rgba(255, 255, 255, 0));
            filter: blur(8px);
            z-index: 0;
        }

        .home-hero-shell::before {
            inset-inline-start: -90px;
            inset-block-end: -120px;
        }

        .home-tools-section::before {
            inset-inline-end: -120px;
            inset-block-start: -90px;
        }

        .premium-workshops-section::before {
            inset-inline-start: -110px;
            inset-block-start: -110px;
        }

        .home-hero-shell > *,
        .home-tools-section > *,
        .premium-workshops-section > * {
            position: relative;
            z-index: 1;
        }

        .home-hero-shell,
        .hero-main-card,
        .hero-latest-card,
        .home-tools-section,
        .home-tool-card,
        .premium-workshops-section .workshop-card {
            background: var(--home-glass-surface);
            border: 1px solid var(--home-glass-border);
            box-shadow: var(--home-glass-shadow);
            backdrop-filter: var(--home-glass-blur);
            -webkit-backdrop-filter: var(--home-glass-blur);
        }

        .featured-workshop-card {
            border: 1px solid rgba(255, 255, 255, 0.34);
            box-shadow: 0 28px 62px rgba(7, 9, 24, 0.26);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .hero-main-card,
        .hero-latest-card,
        .premium-workshops-section .workshop-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .hero-main-card:hover,
        .hero-latest-card:hover,
        .premium-workshops-section .workshop-card:hover {
            transform: translateY(-6px);
            border-color: rgba(107, 46, 48, 0.26);
            box-shadow: 0 30px 64px rgba(107, 46, 48, 0.2);
        }

        .hero-content {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.5));
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 1.35rem;
            padding: clamp(1rem, 2.5vw, 1.3rem);
            backdrop-filter: blur(9px);
            -webkit-backdrop-filter: blur(9px);
        }

        .hero-badge,
        .latest-recipe-chip,
        .workshop-price-chip {
            border: 1px solid rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .hero-slider-nav button,
        .home-tools-nav button,
        .feed-videos-nav button {
            background: rgba(255, 255, 255, 0.6);
            border-color: rgba(107, 46, 48, 0.24);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .latest-recipe-mini,
        .home-tool-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.8), rgba(227, 236, 255, 0.5));
            border: 1px solid rgba(255, 255, 255, 0.62);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .home-tool-card__image {
            background: linear-gradient(140deg, rgba(255, 255, 255, 0.46), rgba(255, 255, 255, 0.18));
        }

        .home-feed-section .feed-videos-shell {
            background:
                radial-gradient(circle at 16% 12%, rgba(143, 74, 80, 0.25), transparent 32%),
                linear-gradient(135deg, rgba(2, 6, 23, 0.88), rgba(8, 25, 120, 0.82));
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 24px 54px rgba(2, 6, 23, 0.45);
            backdrop-filter: blur(14px) saturate(140%);
            -webkit-backdrop-filter: blur(14px) saturate(140%);
        }

        .home-feed-section .feed-videos-fade-left {
            background: linear-gradient(90deg, rgba(2, 6, 23, 0.86) 0%, rgba(2, 6, 23, 0) 100%);
        }

        .home-feed-section .feed-videos-fade-right {
            background: linear-gradient(270deg, rgba(2, 6, 23, 0.86) 0%, rgba(2, 6, 23, 0) 100%);
        }

        .premium-workshops-section {
            background:
                radial-gradient(circle at 14% 0%, rgba(143, 74, 80, 0.11), transparent 34%),
                radial-gradient(circle at 88% 12%, rgba(107, 46, 48, 0.1), transparent 38%),
                linear-gradient(180deg, rgba(244, 248, 255, 0.8), rgba(255, 255, 255, 0.95));
        }

        .premium-workshops-header a {
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid rgba(107, 46, 48, 0.16);
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .premium-workshops-section .workshop-price-chip {
            background: rgba(107, 46, 48, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 14px 30px rgba(107, 46, 48, 0.28);
        }

        .home-hero-shell,
        .home-tools-section,
        .home-feed-section .feed-videos-shell,
        .premium-workshops-section .workshop-card,
        .featured-workshop-card {
            animation: home-glass-reveal 0.75s cubic-bezier(0.22, 0.61, 0.36, 1) both;
        }

        .home-tools-section {
            animation-delay: 0.08s;
        }

        .home-feed-section .feed-videos-shell {
            animation-delay: 0.14s;
        }

        .premium-workshops-section .workshop-card {
            animation-delay: 0.18s;
        }

        @keyframes home-glass-reveal {
            0% {
                opacity: 0;
                transform: translate3d(0, 16px, 0) scale(0.985);
            }
            100% {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @media (max-width: 1024px) {
            .hero-content {
                border-radius: 0;
                border-inline: 0;
                border-bottom: 0;
                background: rgba(255, 255, 255, 0.86);
            }

            .home-tools-section,
            .premium-workshops-section .workshop-card,
            .home-feed-section .feed-videos-shell {
                backdrop-filter: blur(10px) saturate(120%);
                -webkit-backdrop-filter: blur(10px) saturate(120%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-hero-shell,
            .home-tools-section,
            .home-feed-section .feed-videos-shell,
            .premium-workshops-section .workshop-card,
            .featured-workshop-card {
                animation: none !important;
            }
        }

    </style>











