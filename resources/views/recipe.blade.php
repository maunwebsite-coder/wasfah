@extends('layouts.app')

@section('title', $recipe->title)

@push('styles')
    <style>
      .serving-size-btn.bg-orange-500 {
        background-color: #0819ff !important;
      }
      .serving-size-btn.text-white {
        color: #fff !important;
      }
      .star-rating {
        unicode-bidi: bidi-override;
        direction: rtl;
        text-align: center;
        display: flex;
        justify-content: center;
        gap: 5px;
        flex-direction: row; /* Remove row-reverse */
      }
      .star-rating input {
        display: none;
      }
      .star-rating label {
        display: inline-block;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        padding: 5px;
        position: relative;
      }
      .star-rating .star {
        font-size: 2.5rem;
        color: #e5e7eb; /* Gray stars by default */
        display: block;
        transition: all 0.2s ease-in-out;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        line-height: 1;
      }
      .video-shell {
        position: relative;
        border-radius: 1.5rem;
        overflow: hidden;
        background: radial-gradient(circle at 20% 20%, rgba(8, 25, 255, 0.12), transparent 45%), #0b1c2d;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.08);
      }
      .video-shell .video-frame {
        width: 100%;
        aspect-ratio: 16 / 9;
        border: none;
        display: block;
        background: #000;
      }
      .video-shell .video-overlay-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #0d9488;
        font-weight: 700;
      }
      .video-shell .video-overlay-link:hover {
        color: #0f766e;
      }
      
      /* Selected stars */
      .star-rating input:checked ~ label .star {
        color: #1ab8ff !important;
        transform: scale(1.1);
        text-shadow: 0 2px 4px rgba(26, 184, 255, 0.3);
      }
      
      /* Hover effect: highlight the hovered star and the ones after it */
      .star-rating label:hover .star {
        color: #1ab8ff !important;
        transform: scale(1.1);
        text-shadow: 0 2px 4px rgba(26, 184, 255, 0.3);
      }
      
      /* Highlight stars that follow the hovered one (RTL order) */
      .star-rating label:hover ~ label .star {
        color: #1ab8ff !important;
        transform: scale(1.1);
        text-shadow: 0 2px 4px rgba(26, 184, 255, 0.3);
      }
      
      /* Reset stars that come before the hovered one */
      .star-rating label:hover + label .star {
        color: #e5e7eb !important;
        transform: scale(1);
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
      }
      
      .star-rating label:hover {
        transform: scale(1.05);
      }
      
      /* Remove CSS that shaded all stars */
      
      /* Keep selected stars highlighted */
      .star-rating input:checked ~ label .star {
        color: #1ab8ff !important;
        transform: scale(1.1);
        text-shadow: 0 2px 4px rgba(26, 184, 255, 0.3);
      }
      .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.25rem;
        border-radius: 9999px; /* rounded-full */
        font-weight: 600; /* font-semibold */
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
      }

      .btn.save-recipe-btn {
        background-color: #10b981; /* bg-green-500 - default green */
        color: #ffffff;
        min-width: 140px; /* Keep width consistent */
        min-height: 48px; /* Keep height consistent */
      }

      .btn.save-recipe-btn:hover {
        background-color: #059669; /* hover:bg-green-600 */
      }
  
      .btn.save-recipe-btn.bg-orange-500 {
        background-color: #0819ff !important; /* bg-orange-500 */
      }
  
      .btn.save-recipe-btn.bg-orange-500:hover {
        background-color: #0f78ad !important; /* hover:bg-orange-600 */
      }
      
      .btn.save-recipe-btn.bg-green-500 {
        background-color: #10b981 !important; /* bg-green-500 */
      }
  
      .btn.save-recipe-btn.bg-green-500:hover {
        background-color: #059669 !important; /* hover:bg-green-600 */
      }

      .recipe-hero {
        position: relative;
        background: linear-gradient(135deg, rgba(232, 242, 255, 0.85) 0%, rgba(244, 246, 255, 0.92) 45%, #ffffff 100%);
        border: 1px solid rgba(8, 25, 255, 0.08);
        box-shadow: 0 25px 55px rgba(15, 23, 42, 0.07);
        overflow: hidden;
      }

      .recipe-hero::before,
      .recipe-hero::after {
        content: "";
        position: absolute;
        border-radius: 9999px;
        background: radial-gradient(circle at center, rgba(8, 25, 255, 0.18), transparent 65%);
        pointer-events: none;
        transition: transform 0.5s ease;
      }

      .recipe-hero::before {
        width: 380px;
        height: 380px;
        top: -160px;
        left: -120px;
        opacity: 0.45;
      }

      .recipe-hero::after {
        width: 320px;
        height: 320px;
        bottom: -140px;
        right: -80px;
        opacity: 0.35;
      }

      .recipe-hero:hover::before,
      .recipe-hero:hover::after {
        transform: scale(1.05);
      }

      .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(8, 25, 255, 0.25);
        color: #0f78ad;
        font-weight: 600;
        font-size: 0.95rem;
        backdrop-filter: blur(6px);
      }

      .hero-title {
        font-size: clamp(2rem, 5vw, 3.2rem);
        line-height: 1.2;
        font-weight: 800;
        color: #1f2937;
        letter-spacing: -0.015em;
      }

      .hero-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        align-items: stretch;
      }

      .hero-stat {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1rem 1.25rem;
        border-radius: 1.5rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(8, 25, 255, 0.12);
        box-shadow: 0 12px 30px rgba(8, 25, 255, 0.08);
      }
      .hero-stat.hero-stat--compact {
        padding: 0.8rem 1rem;
        gap: 0.7rem;
      }

      .hero-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 9999px;
        background: rgba(8, 25, 255, 0.12);
        color: #0819ff;
        font-size: 1.25rem;
      }

      .hero-stat-label {
        display: block;
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
      }

      .hero-stat-value {
        color: #111827;
        font-size: 1.1rem;
        font-weight: 700;
      }
      .hero-stat-value.hero-stat-value--compact {
        display: grid;
        gap: 0.25rem;
        font-size: 0.95rem;
      }
      .hero-stat-value.hero-stat-value--compact .hero-stat-line {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #4b5563;
        font-size: 0.9rem;
        font-weight: 600;
      }
      .hero-stat-link {
        color: #0f78ad;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
      }
      .hero-stat-link:hover {
        color: #0b344f;
        text-decoration: underline;
      }

      .hero-stat-sub {
        display: inline-block;
        margin-right: 0.75rem;
        color: #9ca3af;
        font-size: 0.75rem;
        font-weight: 600;
      }

      .hero-panel {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(8, 25, 255, 0.15);
        border-radius: 1.5rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(8px);
      }


      .hero-actions {
        display: grid;
        gap: 0.75rem;
      }

      @media (min-width: 640px) {
        .hero-actions {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }

      @media (min-width: 1024px) {
        .hero-panel {
          min-width: 18rem;
        }
      }

      .media-card {
        background: #ffffff;
        border-radius: 1.5rem;
        overflow: hidden;
        border: 1px solid rgba(8, 25, 255, 0.12);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
      }

      .media-card .thumbnail {
        border-radius: 1rem;
        border: 2px solid transparent;
        opacity: 0.65;
      }

      .media-card .thumbnail.active,
      .media-card .thumbnail:hover {
        border-color: #0819ff;
        opacity: 1;
      }

      .media-card .main-image-wrapper {
        height: 400px;
      }

      .media-card .thumbnail-strip {
        gap: 0.75rem;
      }

      .section-card {
        position: relative;
        background: linear-gradient(160deg, rgba(255, 255, 255, 0.96) 0%, rgba(244, 246, 255, 0.75) 80%, #ffffff 100%);
        border-radius: 1.5rem;
        padding: 2rem;
        border: 1px solid rgba(8, 25, 255, 0.12);
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.07);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
      }

      .section-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.1);
      }

      .section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 1.5rem;
      }

      .section-title i {
        color: #0819ff;
        font-size: 1.4rem;
      }

      .info-highlight-grid {
        display: grid;
        gap: 1rem;
      }

      .info-highlight {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1rem 1.25rem;
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(229, 231, 235, 0.6);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
      }

      .info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        background: rgba(8, 25, 255, 0.12);
        color: #0819ff;
        font-size: 1.3rem;
      }

      .info-label {
        display: block;
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
      }

      .info-value {
        color: #111827;
        font-size: 1.25rem;
        font-weight: 700;
      }

      .info-unit {
        margin-right: 0.5rem;
        color: #9ca3af;
        font-size: 0.85rem;
        font-weight: 600;
      }

      .serving-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
      }

      .serving-size-btn {
        min-width: 68px;
        padding: 0.65rem 1.25rem;
        border-radius: 9999px;
        font-weight: 600;
        border: 1px solid rgba(8, 25, 255, 0.25);
        background: rgba(255, 255, 255, 0.95);
        color: #0819ff;
        transition: all 0.2s ease;
      }

      .serving-size-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(8, 25, 255, 0.15);
      }

      .serving-size-hint {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 1.1rem;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px dashed rgba(8, 25, 255, 0.3);
      }

      .ingredient-list {
        display: grid;
        gap: 0.75rem;
      }

      .ingredient-item {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.85rem 1.1rem;
        border-radius: 1.25rem;
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.7);
        font-weight: 600;
        color: #374151;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .ingredient-item:hover {
        transform: translateX(-4px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
      }

      .ingredient-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.9rem;
        background: rgba(8, 25, 255, 0.12);
        color: #0819ff;
        font-size: 1rem;
      }

      .section-card .tool-card {
        border-radius: 1.25rem;
        border: 1px solid rgba(229, 231, 235, 0.8);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .section-card .tool-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.08);
      }

      .step-list {
        counter-reset: steps;
        display: grid;
        gap: 1.25rem;
      }

      .step-item {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        padding: 1.25rem 1.4rem;
        border-radius: 1.4rem;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 16px 35px rgba(15, 23, 42, 0.06);
        font-size: 1.05rem;
        line-height: 1.8;
        color: #374151;
      }

      .step-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #0819ff, #16c3ff);
        color: #ffffff;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 12px 24px rgba(8, 25, 255, 0.3);
      }

      .step-text {
        flex: 1;
      }

      .callout-card {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        background: linear-gradient(140deg, rgba(8, 25, 255, 0.12) 0%, rgba(26, 184, 255, 0.08) 45%, rgba(255, 255, 255, 0.85) 100%);
        border: 1px solid rgba(8, 25, 255, 0.14);
      }

      .origin-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
      }

      .origin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid rgba(8, 25, 255, 0.16);
        font-weight: 800;
        color: #0819ff;
      }

      .origin-badge small {
        color: #475569;
        font-weight: 700;
      }

      .origin-badge.origin-creator { background: rgba(26, 184, 255, 0.12); border-color: rgba(26, 184, 255, 0.28); color: #0a8f78; }
      .origin-badge.origin-partner { background: rgba(247, 201, 72, 0.12); border-color: rgba(247, 201, 72, 0.28); color: #c47a00; }
      .origin-badge.origin-peah { background: rgba(8, 25, 255, 0.1); border-color: rgba(8, 25, 255, 0.28); color: #0819ff; }
      .origin-badge.origin-community { background: rgba(148, 163, 184, 0.14); border-color: rgba(148, 163, 184, 0.3); color: #334155; }

      .workshop-cta {
        margin: 1rem 0 2rem;
        padding: 1.4rem 1.6rem;
        border-radius: 1.5rem;
        background: linear-gradient(120deg, rgba(8, 25, 255, 0.12), rgba(26, 184, 255, 0.12));
        border: 1px solid rgba(8, 25, 255, 0.12);
        box-shadow: 0 18px 40px rgba(8, 25, 255, 0.08);
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 1rem;
        align-items: center;
      }

      .workshop-cta h3 {
        font-size: 1.4rem;
        font-weight: 900;
        color: #0819ff;
        margin-bottom: 0.35rem;
      }

      .workshop-cta p {
        color: #475569;
        line-height: 1.7;
        margin-bottom: 0.6rem;
      }

      .workshop-cta__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(26, 184, 255, 0.14);
        color: #0b8c78;
        font-weight: 800;
        margin-bottom: 0.35rem;
      }

      .workshop-cta__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 0.5rem;
      }

      .workshop-primary {
        background: linear-gradient(135deg, #0819ff, #16c3ff);
        color: #fff;
        padding: 0.7rem 1rem;
        border-radius: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
      }

      .workshop-secondary {
        background: rgba(8, 25, 255, 0.06);
        color: #0819ff;
        padding: 0.65rem 0.9rem;
        border-radius: 12px;
        font-weight: 800;
        border: 1px solid rgba(8, 25, 255, 0.14);
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
      }

      .workshop-cta__meta {
        display: grid;
        gap: 0.5rem;
      }

      .workshop-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.8rem;
        border-radius: 12px;
        background: #fff;
        border: 1px solid rgba(8, 25, 255, 0.1);
        color: #0819ff;
        font-weight: 800;
      }

      @media (max-width: 900px) {
        .workshop-cta {
          grid-template-columns: 1fr;
        }
      }

      @media (min-width: 768px) {
        .callout-card {
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
        }
      }

      .callout-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 1.1rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(8, 25, 255, 0.3);
        color: #0819ff;
        font-weight: 700;
        font-size: 0.9rem;
      }

      .callout-card .callout-meta {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
      }

      .callout-card .callout-meta p {
        color: #4b5563;
        font-size: 1rem;
        line-height: 1.7;
      }

      .callout-card .cta-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
      }

      @media (min-width: 768px) {
        .callout-card .cta-buttons {
          flex-direction: row;
          align-items: center;
        }
      }

      .rating-card {
        background: linear-gradient(160deg, rgba(255, 255, 255, 0.96) 0%, rgba(254, 215, 170, 0.4) 100%);
        border: 1px solid rgba(8, 25, 255, 0.15);
      }

      .rating-card .section-title {
        margin-bottom: 1.5rem;
      }

      .rating-card .star-rating {
        margin-bottom: 1rem;
      }

      .rating-card .rating-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
      }

      @media (min-width: 768px) {
        .rating-card .rating-actions {
          flex-direction: row;
          width: auto;
        }
      }

      .related-section .related-recipe-card {
        border-radius: 1.25rem;
        border: 1px solid rgba(229, 231, 235, 0.8);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
      }

      .related-section .related-recipe-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 28px 52px rgba(15, 23, 42, 0.1);
      }

      @media (max-width: 640px) {
        .recipe-hero {
          padding: 1.75rem 1.5rem;
          border-radius: 2rem;
        }

        .hero-title {
          font-size: 1.9rem;
        }

        .hero-stats-grid {
          display: flex;
          gap: 0.55rem;
          overflow-x: auto;
          padding: 0.65rem 0.75rem 0.5rem;
          margin: 1.1rem -0.75rem 0;
          scroll-snap-type: x mandatory;
          -webkit-overflow-scrolling: touch;
          scrollbar-width: none;
        }

        .hero-stats-grid::-webkit-scrollbar {
          display: none;
        }

        .hero-stat {
          flex: 0 0 auto;
          min-width: 150px;
          padding: 0.7rem 0.8rem;
          border-radius: 1.1rem;
          gap: 0.6rem;
          scroll-snap-align: start;
        }

        .hero-stat-icon {
          width: 28px;
          height: 28px;
          font-size: 0.95rem;
        }

        .hero-stat-label {
          font-size: 0.72rem;
          margin-bottom: 0.1rem;
        }

        .hero-stat-value {
          font-size: 0.95rem;
        }

        .hero-stat-sub {
          font-size: 0.65rem;
          margin-right: 0.35rem;
        }

        .hero-panel {
          width: 100%;
          padding: 0 !important;
          margin-top: 1.1rem;
          background: transparent !important;
          border: none !important;
          box-shadow: none !important;
        }

        .hero-actions {
          display: flex;
          align-items: center;
          justify-content: space-between;
          flex-wrap: nowrap;
          gap: 0.2rem;
          margin-top: 0.12rem !important;
          padding: 0;
        }

        .hero-actions .btn,
        .hero-actions button {
          flex: 1 1 0%;
          min-width: 0;
          min-height: unset;
          padding: 0.5rem 0.45rem;
          font-size: 0.74rem;
          border-radius: 10px;
          justify-content: center;
          line-height: 1.3;
        }

        .hero-actions button i {
          font-size: 0.75rem;
          margin-inline-start: 0.35rem;
          margin-inline-end: 0;
        }

        .section-card {
          padding: 1.75rem !important;
          border-radius: 1.25rem;
        }

        .info-highlight-grid {
          gap: 0.75rem;
        }

        .info-highlight {
          padding: 0.9rem 1rem;
        }

        .serving-controls {
          display: grid;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 0.75rem;
        }

        .serving-size-btn {
          width: 100%;
        }

        .ingredient-item {
          padding: 0.75rem 1rem;
        }

        .media-card {
          border-radius: 1.5rem;
        }

        .media-card .main-image-wrapper {
          height: 260px;
        }

        .media-card .thumbnail-strip {
          gap: 0.5rem;
          padding-inline: 0.25rem;
        }

        .step-item {
          flex-direction: column;
          align-items: flex-start;
        }

        .step-number {
          margin-bottom: 0.75rem;
        }

        .callout-card {
          gap: 1.25rem;
          padding: 1.75rem;
        }

        .callout-card .cta-buttons {
          width: 100%;
        }

        .callout-card .cta-buttons button {
          width: 100%;
        }

        .rating-card {
          padding: 1.75rem !important;
        }

        .slider-track {
          display: flex !important;
          flex-wrap: nowrap;
          overflow-x: auto;
          gap: 0.85rem;
          padding-inline: 0.75rem;
          margin-inline: -0.75rem;
          scroll-snap-type: x mandatory;
          -webkit-overflow-scrolling: touch;
          scroll-padding-inline-start: 0.75rem;
        }

        .slider-track::-webkit-scrollbar {
          display: none;
        }

        .slider-track > * {
          flex: 0 0 78%;
          max-width: 78%;
          scroll-snap-align: start;
        }

        .tools-slider > * {
          flex-basis: 72%;
          max-width: 72%;
        }

        .related-slider > * {
          flex-basis: 80%;
          max-width: 80%;
        }

        .related-slider {
          padding-bottom: 0.5rem;
        }
      }

      @media (max-width: 460px) {
        .hero-stats-grid {
          gap: 0.45rem;
          padding: 0.55rem 0.65rem 0.4rem;
          margin-top: 0.9rem;
        }

        .hero-stat {
          min-width: 135px;
          padding: 0.6rem 0.65rem;
          gap: 0.45rem;
        }

        .hero-stat-icon {
          width: 24px;
          height: 24px;
          font-size: 0.85rem;
        }

        .hero-stat-label {
          font-size: 0.66rem;
        }

        .hero-stat-value {
          font-size: 0.88rem;
        }

        .hero-stat-sub {
          font-size: 0.6rem;
        }

        .hero-actions {
          gap: 0.15rem;
          margin-top: 0.08rem !important;
        }

        .hero-actions .btn,
        .hero-actions button {
          padding: 0.45rem 0.4rem;
          font-size: 0.66rem;
          border-radius: 8px;
        }

        .hero-actions button i {
          font-size: 0.62rem;
          margin-inline-start: 0.25rem;
        }
      }

      @media (max-width: 360px) {
        .hero-stat {
          min-width: 120px;
        }

        .hero-stat-value {
          font-size: 0.82rem;
        }

        .hero-stat-sub {
          display: none;
        }

        .hero-actions .btn,
        .hero-actions button {
          padding: 0.38rem 0.3rem;
          font-size: 0.56rem;
        }

        .hero-actions {
          margin-top: 0.05rem !important;
        }
      }
      
      /* Print Styles */
      @media print {
        .no-print {
          display: none !important;
        }
        
        body {
          font-size: 12pt;
          line-height: 1.6;
          color: #000;
          background: white;
          direction: rtl;
          font-family: 'Arial', 'Tahoma', 'Segoe UI', sans-serif;
        }
        
        .container {
          max-width: none;
          margin: 0;
          padding: 0;
        }
        
        .bg-white {
          background: white !important;
          box-shadow: none !important;
          border: 1px solid #ddd !important;
        }
        
        .text-orange-500 {
          color: #0819ff !important;
        }
        
        .shadow-lg {
          box-shadow: none !important;
        }
        
        .rounded-xl {
          border-radius: 8px !important;
        }
        
        .p-6 {
          padding: 20px !important;
        }
        
        .mb-8, .mb-6, .mb-4 {
          margin-bottom: 20px !important;
        }
        
        .mt-8, .mt-4 {
          margin-top: 20px !important;
        }
        
        .grid {
          display: block !important;
        }
        
        .flex {
          display: block !important;
        }
        
        .hidden {
          display: none !important;
        }
        
        h1, h2, h3 {
          page-break-after: avoid;
          color: #0819ff !important;
          font-weight: bold;
        }
        
        h1 {
          font-size: 2.2em;
          text-align: center;
          margin-bottom: 20px;
          border-bottom: 3px solid #0819ff;
          padding-bottom: 15px;
        }
        
        h2 {
          font-size: 1.8em;
          margin-bottom: 15px;
          border-bottom: 2px solid #0819ff;
          padding-bottom: 8px;
          text-align: center;
        }
        
        .print-section {
          page-break-inside: avoid;
          margin-bottom: 25px;
        }
        
        .print-ingredients {
          background: #f8f9fa !important;
          padding: 20px !important;
          border: 1px solid #dee2e6 !important;
          border-radius: 8px !important;
        }
        
        .print-steps {
          background: white !important;
          padding: 20px !important;
          border: 1px solid #dee2e6 !important;
          border-radius: 8px !important;
        }
        
        .print-info {
          background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
          padding: 15px !important;
          border: 1px solid #dee2e6 !important;
          margin-bottom: 20px !important;
          border-radius: 8px !important;
          display: flex !important;
          justify-content: space-around !important;
        }
        
        .print-info-item {
          text-align: center;
          font-weight: 600;
          font-size: 1.1em;
        }
        
        .print-info-item i {
          color: #0819ff;
          margin-left: 8px;
          font-size: 1.2em;
        }
        
        ul, ol {
          margin: 15px 0;
          padding-right: 25px;
        }
        
        ul li {
          margin: 8px 0;
          padding: 8px 0;
          border-bottom: 1px solid #eee;
          position: relative;
          padding-right: 20px;
        }
        
        ul li:before {
          content: "•";
          color: #0819ff;
          font-weight: bold;
          position: absolute;
          right: 0;
          font-size: 1.3em;
        }
        
        ol li {
          margin: 10px 0;
          padding: 10px 0;
          border-bottom: 1px solid #f0f0f0;
          position: relative;
          padding-right: 30px;
        }
        
        ol li:before {
          content: counter(step-counter);
          counter-increment: step-counter;
          position: absolute;
          right: -25px;
          top: 10px;
          background: #0819ff;
          color: white;
          width: 22px;
          height: 22px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
          font-size: 0.8em;
        }
        
        ol {
          counter-reset: step-counter;
        }
        
        .print-footer {
          text-align: center;
          margin-top: 40px;
          padding-top: 20px;
          border-top: 2px solid #0819ff;
          font-size: 11pt;
          color: #666;
          background: #f8f9fa;
          padding: 20px;
          border-radius: 8px;
        }
        
      .print-footer p {
        margin: 5px 0;
      }
      
      /* Equipment card styles */
      .tool-card {
        background: linear-gradient(135deg, #f4f6ff 0%, #c8d6ff 100%);
        border: 1px solid #16c3ff;
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }
      
      .tool-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #0819ff, #16c3ff, #1ab8ff);
      }
      
      .tool-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(8, 25, 255, 0.15);
        border-color: #0819ff;
      }
      
      .tool-icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #0819ff, #16c3ff);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 12px rgba(8, 25, 255, 0.3);
      }
      
      .tool-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        text-align: center;
      }
      
      .tool-divider {
        width: 2rem;
        height: 3px;
        background: linear-gradient(90deg, #0819ff, #16c3ff);
        border-radius: 2px;
        margin: 0 auto;
      }

      /* Extra equipment card styling for the recipe page */
      #tools-container .tool-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
      }
      
      #tools-container .tool-card::before {
        display: none;
      }
      
      #tools-container .tool-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: #0819ff;
      }
      
      #tools-container .tool-card .p-3,
      #tools-container .tool-card .p-4 {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
      }
      
      #tools-container .tool-card h3 {
        min-height: 3.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
      }
      
      #tools-container .tool-card .mt-auto {
        margin-top: auto;
      }
      
      #tools-container .tool-card .rating-stars {
        color: #1ab8ff;
      }
      
      #tools-container .tool-card .empty-rating {
        color: #d1d5db;
      }
      
      #tools-container .tool-card .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      
      /* Mobile improvements */
      @media (max-width: 640px) {
        #tools-container .tool-card {
          margin-bottom: 0.75rem;
          min-height: 280px;
        }
        
        #tools-container .tool-card .p-3 {
          padding: 0.75rem;
        }
        
        #tools-container .tool-card h3 {
          font-size: 0.75rem;
          line-height: 1.2rem;
          min-height: 3rem;
          display: -webkit-box;
          -webkit-line-clamp: 4;
          -webkit-box-orient: vertical;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        
        #tools-container .tool-card .text-sm {
          font-size: 0.875rem;
        }
        
        #tools-container .tool-card .text-lg {
          font-size: 1rem;
        }
        
        #tools-container .tool-card .text-xl {
          font-size: 1.125rem;
        }
        
        #tools-container .tool-card button {
          font-size: 0.75rem;
          padding: 0.5rem 0.75rem;
        }
        
        #tools-container .tool-card .rating-stars {
          font-size: 0.625rem;
        }
      }
      
      /* Medium screen tweaks */
      @media (min-width: 641px) and (max-width: 1024px) {
        #tools-container .tool-card {
          margin-bottom: 1.25rem;
        }
      }
      
      /* Button refinements */
      #tools-container .save-for-later-btn:active {
        transform: scale(0.98);
      }
      
      /* Amazon button tweaks */
      #tools-container .tool-card a[href*="amazon"]:active {
        transform: scale(0.98);
      }
      
      #tools-container .tool-card a[href*="amazon"]:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
      }
      
      /* Badge improvements */
      #tools-container .category-badge {
        backdrop-filter: blur(10px);
        background: rgba(8, 25, 255, 0.9);
      }
      
      /* ضمان التناسق على الهواتف */
      @media (max-width: 640px) {
        #tools-container {
          gap: 0.75rem;
        }
        
        #tools-container .tool-card {
          width: 100%;
          max-width: 100%;
        }
        
        #tools-container .tool-card img {
          max-height: 120px;
        }
      }
      
      /* أنماط الوصفات المشابهة */
      .related-recipe-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }
      
      .related-recipe-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      }
      
      .related-recipe-card .recipe-image {
        position: relative;
        overflow: hidden;
      }
      
      .related-recipe-card .recipe-image::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 0%, rgba(8, 25, 255, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
      }
      
      .related-recipe-card:hover .recipe-image::after {
        opacity: 1;
      }
      
      .related-recipe-card .recipe-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
        min-height: 2.8rem;
      }
      
      .related-recipe-card .category-badge {
        background: linear-gradient(135deg, #c8d6ff 0%, #16c3ff 100%);
        color: #050f9f;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.3);
        box-shadow: 0 2px 4px rgba(8, 25, 255, 0.2);
      }
      
      .related-recipe-card .rating-stars {
        color: #1ab8ff;
        text-shadow: 0 1px 2px rgba(251, 191, 36, 0.3);
      }
      
      .related-recipe-card .empty-rating {
        color: #d1d5db;
      }
      
      .related-recipe-card .save-recipe-btn {
        min-width: 44px;
        min-height: 44px;
        border-radius: 8px;
        transition: all 0.2s ease;
      }
      
      .related-recipe-card .save-recipe-btn:hover {
        transform: scale(1.05);
      }
      
      .related-recipe-card .save-recipe-btn:active {
        transform: scale(0.95);
      }
      
      /* Mobile tweaks */
      @media (max-width: 640px) {
        .related-recipe-card {
          margin-bottom: 1rem;
        }
        
        .related-recipe-card .recipe-image {
          height: 200px;
        }
        
        .related-recipe-card .recipe-title {
          font-size: 1rem;
          min-height: 2.4rem;
        }
        
        .related-recipe-card .recipe-info {
          font-size: 0.875rem;
        }
        
        .related-recipe-card .action-buttons {
          flex-direction: column;
          gap: 0.5rem;
        }
        
        .related-recipe-card .save-recipe-btn {
          width: 100%;
          min-height: 40px;
        }
      }
      
      /* Medium screen refinements */
      @media (min-width: 641px) and (max-width: 1024px) {
        .related-recipe-card .recipe-image {
          height: 220px;
        }
      }
        
        /* Improve image layout */
        img {
          max-width: 100% !important;
          height: auto !important;
          page-break-inside: avoid;
        }
        
        /* Improve table layout */
        table {
          width: 100%;
          border-collapse: collapse;
          margin: 15px 0;
        }
        
        th, td {
          border: 1px solid #ddd;
          padding: 8px;
          text-align: right;
        }
        
        th {
          background-color: #f8f9fa;
          font-weight: bold;
        }
      }
</style>
@endpush

<!-- Recipe data is now loaded server-side -->

@section('content')

    @php
        $recipeJsTranslations = [
            'tools' => [
                'actions' => [
                    'save' => __('recipe.tools.actions.save'),
                    'saved' => __('recipe.tools.actions.saved'),
                    'saving' => __('recipe.tools.actions.saving'),
                    'removing' => __('recipe.tools.actions.removing'),
                    'error' => __('recipe.tools.actions.error'),
                ],
                'messages' => [
                    'save_success' => __('recipe.tools.messages.save_success'),
                    'save_error' => __('recipe.tools.messages.save_error'),
                    'remove_success' => __('recipe.tools.messages.remove_success'),
                    'remove_error' => __('recipe.tools.messages.remove_error'),
                ],
            ],
            'rating' => [
                'button' => [
                    'submit' => __('recipe.rating.button.submit'),
                    'remove' => __('recipe.rating.button.remove'),
                    'rate' => __('recipe.rating.button.rate'),
                    'rated' => __('recipe.rating.button.rated'),
                ],
                'state' => [
                    'submitting' => __('recipe.rating.state.submitting'),
                    'submitted' => __('recipe.rating.state.submitted'),
                    'removing' => __('recipe.rating.state.removing'),
                ],
                'messages' => [
                    'choose' => __('recipe.rating.messages.choose_rating'),
                    'login' => __('recipe.rating.messages.login_required'),
                    'login_remove' => __('recipe.rating.messages.login_required_remove'),
                    'submit_success' => __('recipe.rating.messages.submit_success'),
                    'submit_error' => __('recipe.rating.messages.submit_error'),
                    'remove_success' => __('recipe.rating.messages.remove_success'),
                    'remove_error' => __('recipe.rating.messages.remove_error'),
                ],
                'user_rating_template' => __('recipe.rating.user_rating'),
                'prompt' => __('recipe.rating.prompt'),
            ],
            'share' => [
                'copied' => __('recipe.share.modal.copied'),
            ],
        ];
    @endphp
    @php
        use Illuminate\Support\Str;

        $skillTracks = [
            'cooking' => [
                'label' => 'Cooking Skills',
                'subtitle' => 'وصفات، وجبات سريعة، وحيل المطبخ',
                'accent' => '#16c3ff',
            ],
            'content' => [
                'label' => 'Content Creation',
                'subtitle' => 'إضاءة، مونتاج، مهارات تصوير',
                'accent' => '#8f7ee7',
            ],
            'business' => [
                'label' => 'Business Skills',
                'subtitle' => 'تسعير، براندينج، مبيعات',
                'accent' => '#f7c948',
            ],
            'crafts' => [
                'label' => 'Crafts & DIY',
                'subtitle' => 'مشاريع منزلية وأعمال يدوية',
                'accent' => '#e46f4d',
            ],
        ];

        $classifyTrack = function ($recipe) {
            $categoryName = Str::of($recipe->category->name ?? '')->lower();
            $map = [
                'cooking' => ['cook', 'cooking', 'وصفات', 'مطبخ', 'recipe', 'kitchen', 'طهي', 'طبخ', 'meal', 'أكل'],
                'content' => ['محتوى', 'تصوير', 'فيديو', 'اضاءة', 'lighting', 'editing', 'مونتاج', 'كاميرا', 'content'],
                'business' => ['business', 'عمل', 'تسويق', 'branding', 'براند', 'سعر', 'pricing', 'بيع', 'selling'],
                'crafts' => ['حرف', 'diy', 'منزلي', 'مشروع', 'handmade', 'craft', 'منزل', 'ديكور'],
            ];

            foreach ($map as $trackKey => $keywords) {
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && $categoryName->contains(Str::of($keyword)->lower())) {
                        return $trackKey;
                    }
                }
            }

            return 'cooking';
        };

        $resolveSource = function ($recipe) {
            $chef = $recipe->chef;

            if ($chef && $chef->isAdmin()) {
                return 'peah';
            }

            if ($chef && $chef->isReferralPartner()) {
                return 'partner';
            }

            if ($chef && $chef->isChef()) {
                return 'creator';
            }

            return 'community';
        };

        $sourceLabels = [
            'creator' => ['label' => 'Creator', 'hint' => 'محتوى مدربين'],
            'partner' => ['label' => 'Partner', 'hint' => 'محتوى الشركاء'],
            'peah' => ['label' => 'Peah Official', 'hint' => 'حساب رسمي'],
            'community' => ['label' => 'Community Post', 'hint' => 'من مجتمع Wasfah'],
        ];

        $recipeTrackKey = $classifyTrack($recipe);
        $recipeTrack = $skillTracks[$recipeTrackKey] ?? $skillTracks['cooking'];
        $sourceType = $resolveSource($recipe);
        $sourceLabel = $sourceLabels[$sourceType];
        $primaryWorkshop = $recipe->workshops->first();
    @endphp

    <main class="container mx-auto px-4 py-8">
      <!-- Recipe hero -->
      <section class="recipe-hero rounded-3xl px-6 py-8 md:px-10 md:py-10 mb-12">
        @php
          $prepMinutes = (int) ($recipe->prep_time ?? 0);
          $cookMinutes = (int) ($recipe->cook_time ?? 0);
          $totalTime = $prepMinutes + $cookMinutes;
          $servingsCount = (int) ($recipe->servings ?? 0);
          $baseServings = $servingsCount > 0 ? $servingsCount : 2;
        @endphp
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-start gap-10">
          <div class="flex-1">
            <span class="hero-badge">
              <i class="fas fa-fire ltr:mr-1 rtl:ml-1 text-sm"></i>
              {{ __('recipe.hero.badge') }}
            </span>
            <h1 class="hero-title mt-4">
              {{ $recipe->title }}
            </h1>
            <p class="text-gray-700 text-base md:text-lg leading-relaxed mt-4 max-w-3xl">
              {{ $recipe->description }}
            </p>
            <div class="origin-badges">
              <span class="origin-badge origin-{{ $sourceType }}">
                <i class="fas fa-user-astronaut"></i>
                {{ $sourceLabel['label'] }}
                @if($sourceType === 'partner' && $recipe->chef?->referral_commission_rate)
                  <small>{{ number_format($recipe->chef->referral_commission_rate, 0) }}% share</small>
                @else
                  <small>{{ $sourceLabel['hint'] }}</small>
                @endif
              </span>
              <span class="origin-badge" style="background: {{ $recipeTrack['accent'] }}1c; border-color: {{ $recipeTrack['accent'] }}50;">
                <i class="fas fa-layer-group"></i>
                {{ $recipeTrack['label'] }}
                <small>{{ $recipeTrack['subtitle'] }}</small>
              </span>
            </div>
            @if($recipe->is_registration_closed)
              <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-2xl mt-6 no-print flex items-start gap-3">
                <i class="fas fa-clock mt-1 ltr:mr-2 rtl:ml-2"></i>
                <span class="font-semibold">{{ __('recipe.hero.registration_closed') }}</span>
              </div>
            @endif
            <div class="hero-stats-grid mt-8 gap-3 sm:gap-4">
              <div class="hero-stat">
                <span class="hero-stat-icon">
                  <i class="fas fa-star"></i>
                </span>
                <div>
                  <span class="hero-stat-label">{{ __('recipe.hero.stats.rating.label') }}</span>
                  <span class="hero-stat-value">
                    @if($recipe->interactions_avg_rating)
                      {{ number_format($recipe->interactions_avg_rating, 1) }}
                      <span class="hero-stat-sub">{{ __('recipe.hero.stats.rating.sub') }}</span>
                    @else
                      {{ __('recipe.hero.stats.rating.empty') }}
                    @endif
                  </span>
                </div>
              </div>
              <div class="hero-stat">
                <span class="hero-stat-icon">
                  <i class="fas fa-bookmark"></i>
                </span>
                <div>
                  <span class="hero-stat-label">{{ __('recipe.hero.stats.saved.label') }}</span>
                  <span class="hero-stat-value" id="recipe-save-count">
                    {{ trans_choice('recipe.hero.stats.saved.value', $recipe->saved_count, ['count' => $recipe->saved_count]) }}
                  </span>
                </div>
              </div>
              @if($recipe->servings || $totalTime)
              <div class="hero-stat hero-stat--compact">
                <span class="hero-stat-icon">
                  <i class="fas fa-stopwatch"></i>
                </span>
                <div>
                  <span class="hero-stat-label">
                      @if($totalTime)
                        {{ __('recipe.hero.stats.time.total') }}
                      @else
                        {{ __('recipe.hero.stats.time.servings') }}
                      @endif
                    </span>
                    <span class="hero-stat-value">
                      @if($totalTime)
                        {{ trans_choice('recipe.units.minutes', $totalTime, ['count' => $totalTime]) }}
                        @if($prepMinutes && $cookMinutes)
                          <span class="hero-stat-sub">
                            {{ __('recipe.hero.stats.time.details.prep_and_cook', [
                                'prep' => trans_choice('recipe.units.minutes_short', $prepMinutes, ['count' => $prepMinutes]),
                                'cook' => trans_choice('recipe.units.minutes_short', $cookMinutes, ['count' => $cookMinutes]),
                            ]) }}
                          </span>
                        @elseif($prepMinutes)
                          <span class="hero-stat-sub">
                            {{ __('recipe.hero.stats.time.details.prep_only', [
                                'minutes' => trans_choice('recipe.units.minutes', $prepMinutes, ['count' => $prepMinutes]),
                            ]) }}
                          </span>
                        @elseif($cookMinutes)
                          <span class="hero-stat-sub">
                            {{ __('recipe.hero.stats.time.details.cook_only', [
                                'minutes' => trans_choice('recipe.units.minutes', $cookMinutes, ['count' => $cookMinutes]),
                            ]) }}
                          </span>
                        @endif
                      @elseif($recipe->servings)
                        {{ trans_choice('recipe.hero.stats.servings.value', $servingsCount, ['count' => $servingsCount]) }}
                      @endif
                    </span>
                  </div>
                </div>
              @endif
              <div class="hero-stat hero-stat--compact">
                <span class="hero-stat-icon">
                  <i class="fas fa-user"></i>
                </span>
                <div>
                  <span class="hero-stat-label">{{ __('recipe.hero.stats.published_by') }}</span>
                  <span class="hero-stat-value hero-stat-value--compact">
                    <span class="hero-stat-line">
                      @if ($recipe->chef)
                        <a href="{{ route('chefs.show', ['chef' => $recipe->chef->id]) }}" class="hero-stat-link">
                          {{ __('recipe.hero.byline.chef', ['name' => $recipe->chef->name]) }}
                        </a>
                      @else
                        {{ $recipe->author ?: __('recipe.hero.byline.team') }}
                      @endif
                    </span>
                    <span class="hero-stat-line">{{ __('recipe.hero.stats.updated_at', ['date' => $recipe->updated_at->format('Y-m-d')]) }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="hero-panel lg:w-80 xl:w-96 p-6 md:p-7">
            <div class="hero-actions mt-6 no-print">
              <button 
                id="save-recipe-page-btn"
                class="btn save-recipe-btn {{ $recipe->is_saved ? 'bg-green-500 hover:bg-green-600' : 'bg-orange-500 hover:bg-orange-600' }}" 
                data-recipe-id="{{ $recipe->recipe_id }}" 
                data-saved="{{ $recipe->is_saved ? 'true' : 'false' }}"
                data-user-id="{{ Auth::id() }}">
                <i class="fas fa-bookmark ltr:mr-2 rtl:ml-2"></i>
                <span>{{ $recipe->is_saved ? __('recipe.hero.actions.saved') : __('recipe.hero.actions.save') }}</span>
              </button>
              <button
                id="rating-scroll-btn"
                class="flex items-center justify-center p-3 text-base border border-gray-200 rounded-full font-semibold text-gray-700 bg-white hover:bg-gray-100 transition-colors {{ $recipe->user_rating ? 'bg-green-50 border-green-300 text-green-700' : '' }}"
              >
                <i class="fas fa-star ltr:mr-2 rtl:ml-2"></i>
                <span id="rating-btn-text">
                  @if($recipe->user_rating)
                    {{ __('recipe.hero.actions.rated') }}
                  @else
                    {{ __('recipe.hero.actions.rate') }}
                  @endif
                </span>
              </button>
              <button
                id="print-recipe-btn"
                class="flex items-center justify-center p-3 text-base border border-gray-200 rounded-full font-semibold text-gray-700 bg-white hover:bg-gray-100 transition-colors"
              >
                <i class="fas fa-print ltr:mr-2 rtl:ml-2"></i>
                {{ __('recipe.hero.buttons.print') }}
              </button>
              <button
                id="share-recipe-btn-1"
                class="flex items-center justify-center p-3 text-base border border-gray-200 rounded-full font-semibold text-gray-700 bg-white hover:bg-gray-100 transition-colors"
              >
                <i class="fas fa-share-alt ltr:mr-2 rtl:ml-2"></i>
                {{ __('recipe.hero.buttons.share') }}
              </button>
            </div>
          </div>
        </div>
      </section>
      <!-- End recipe hero -->

      <section class="workshop-cta no-print" aria-label="ورشة مشابهة">
        <div>
          <span class="workshop-cta__eyebrow">
            <i class="fas fa-bolt"></i>
            سجّل في ورشة مشابهة
          </span>
          <h3>حوّل الزيارة إلى حجز: جرّب ورشة بنفس الروح</h3>
          <p>
            محتوى <strong>{{ $recipeTrack['label'] }}</strong> يقدم تجربة عملية مع مدربين موثوقين. اربط الوصفة بورشة مباشرة لزيادة التحويلات والمبيعات المرتبطة بالمهارات.
          </p>
          <div class="workshop-cta__actions">
            @if($primaryWorkshop)
              <a href="{{ route('workshop.show', $primaryWorkshop) }}" class="workshop-primary">
                <i class="fas fa-fire"></i>
                سجّل في ورشة مشابهة
              </a>
              <a href="{{ route('workshops') }}" class="workshop-secondary">
                <i class="fas fa-grid-2"></i>
                استعرض كل الورش
              </a>
            @else
              <a href="{{ route('workshops') }}" class="workshop-primary">
                <i class="fas fa-fire"></i>
                استكشف الورش المرتبطة
              </a>
              <a href="{{ route('workshops') }}" class="workshop-secondary">
                <i class="fas fa-bullseye-pointer"></i>
                جرّب ورش أخرى
              </a>
            @endif
          </div>
        </div>
        <div class="workshop-cta__meta">
          <span class="workshop-chip">
            <i class="fas fa-stopwatch"></i>
            {{ $recipe->prep_time ? __('recipes.cards.prep_time', ['minutes' => $recipe->prep_time]) : 'وقت مرن' }}
          </span>
          <span class="workshop-chip">
            <i class="fas fa-chart-line"></i>
            {{ $recipe->difficulty ? ucfirst($recipe->difficulty) : 'مستوى مرن' }}
          </span>
          <span class="workshop-chip">
            <i class="fas fa-layer-group"></i>
            {{ $recipeTrack['label'] }}
          </span>
        </div>
      </section>


      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Media Container for Images or Video -->
        <div class="media-card">
            @php
                $images = $recipe->getAllImages();
                $hasImages = $images && count($images) > 0;
                $videoEmbedUrl = $recipe->video_url ? \App\Support\VideoEmbed::embedUrl($recipe->video_url) : null;
                $inlineVideo = \App\Support\VideoEmbed::inlinePlayable($recipe->video_url);
            @endphp

            @if($recipe->video_url)
                <div class="video-shell">
                    @if($videoEmbedUrl)
                        <iframe class="video-frame" src="{{ $videoEmbedUrl }}" title="{{ $recipe->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @elseif($inlineVideo)
                        <video class="video-frame" controls playsinline poster="{{ $hasImages ? $images[0] : '' }}">
                            <source src="{{ $recipe->video_url }}">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                    @else
                        <div class="aspect-video flex items-center justify-center text-center text-gray-100 p-6">
                            <div>
                                <p class="font-semibold mb-2">تعذر عرض الفيديو داخل الصفحة</p>
                                <a href="{{ $recipe->video_url }}" target="_blank" rel="noopener" class="video-overlay-link">
                                    <i class="fas fa-external-link-alt ml-1"></i>
                                    فتح الفيديو في تبويب جديد
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                @if($hasImages)
                    <div class="thumbnail-strip flex space-x-2 rtl:space-x-reverse p-4 bg-gray-50 overflow-x-auto mt-3">
                        @foreach($images as $index => $imageUrl)
                            <img class="w-16 h-16 object-cover rounded-xl border-2" src="{{ $imageUrl }}" alt="{{ __('recipe.misc.gallery_image_alt', ['number' => $index + 1]) }}" onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                        @endforeach
                    </div>
                @endif
            @elseif($hasImages)
                <!-- Image Gallery -->
                <div class="relative">
                    <!-- Main Image Display -->
                    <div class="main-image-wrapper relative overflow-hidden">
                        <img id="main-recipe-image" class="w-full h-full object-cover transition-opacity duration-300" 
                             src="{{ $images[0] }}" alt="{{ __('recipe.misc.image_alt') }}"
                            onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='{{ __('recipe.misc.placeholder_image_alt') }}';" loading="lazy">
                        
                        <!-- Navigation Arrows -->
                        @if(count($images) > 1)
                            <button onclick="previousImage()" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button onclick="nextImage()" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                        
                        <!-- Image Counter -->
                        @if(count($images) > 1)
                            <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                                <span id="image-counter">1</span> / {{ count($images) }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Thumbnail Strip -->
                    @if(count($images) > 1)
                        <div class="thumbnail-strip flex space-x-2 rtl:space-x-reverse p-4 bg-gray-50 overflow-x-auto">
                            @foreach($images as $index => $imageUrl)
                                <img onclick="showImage({{ $index }})" 
                                     class="w-16 h-16 object-cover rounded-xl cursor-pointer border-2 transition-all thumbnail {{ $index === 0 ? 'active' : '' }}" 
                                     src="{{ $imageUrl }}" 
                                     alt="{{ __('recipe.misc.gallery_image_alt', ['number' => $index + 1]) }}"
                                    onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';" loading="lazy">
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-gray-400"></i>
                </div>
            @endif
        </div>

        <div class="section-card info-card px-6 py-6 md:px-8 md:py-8">
          <h2 class="section-title">
            <i class="fas fa-info-circle"></i>
            <span>{{ __('recipe.sections.info') }}</span>
          </h2>
          <div class="info-highlight-grid">
            <div class="info-highlight">
              <span class="info-icon">
                <i class="fas fa-clock"></i>
              </span>
              <div>
                <span class="info-label">{{ __('recipe.info.prep') }}</span>
                <span class="info-value">
                  <span data-prep-time>{{ (int)$recipe->prep_time }}</span>
                  <span class="info-unit">{{ __('recipe.info_units.minutes') }}</span>
                </span>
              </div>
            </div>
            <div class="info-highlight">
              <span class="info-icon">
                <i class="fas fa-fire"></i>
              </span>
              <div>
                <span class="info-label">{{ __('recipe.info.cook') }}</span>
                <span class="info-value">
                  <span data-cook-time>{{ (int)$recipe->cook_time }}</span>
                  <span class="info-unit">{{ __('recipe.info_units.minutes') }}</span>
                </span>
              </div>
            </div>
            <div class="info-highlight">
              <span class="info-icon">
                <i class="fas fa-utensils"></i>
              </span>
              <div>
                <span class="info-label">{{ __('recipe.info.servings') }}</span>
                <span class="info-value">
                  <span data-servings>{{ (int)$recipe->servings }}</span>
                  <span class="info-unit">{{ __('recipe.info_units.people') }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="section-card px-6 py-6 md:px-8 md:py-8">
          <h2 class="section-title">
            <i class="fas fa-list"></i>
            <span>{{ __('recipe.sections.ingredients') }}</span>
          </h2>

          <div class="serving-controls mb-6 no-print">
            <button
              class="serving-size-btn"
              data-multiplier="0.5"
            >
              1/2x
            </button>
            <button
              class="serving-size-btn bg-orange-500 text-white"
              data-multiplier="1"
            >
              1x
            </button>
            <button
              class="serving-size-btn"
              data-multiplier="2"
            >
              2x
            </button>
          </div>

          <div class="serving-size-hint no-print">
            <span class="text-sm text-gray-600">
              {{ __('recipe.ingredients.original_yield', ['count' => $baseServings]) }}
            </span>
            <i
              id="info-icon"
              class="fas fa-question-circle text-gray-400 cursor-pointer"
            ></i>
            <div
              id="info-tooltip"
              class="absolute bottom-full right-0 mb-2 p-4 bg-white rounded-xl shadow-xl max-w-xs w-64 z-10 hidden transform origin-bottom-right transition-opacity duration-300 border border-orange-100"
            >
              <p class="text-sm text-gray-700 leading-relaxed">
                {{ __('recipe.ingredients.tooltip') }}
              </p>
            </div>
          </div>

          <ul class="ingredient-list mt-6">
            @foreach($recipe->ingredients as $ingredient)
              @php
                $ingredientFullText = trim(($ingredient->quantity ?? __('recipe.ingredients.quantity_as_needed')) . ' ' . $ingredient->name);
              @endphp
              <li class="ingredient-item" 
                  data-original-quantity="{{ $ingredient->quantity ?? '' }}"
                  data-name="{{ $ingredient->name }}"
                  data-full-text="{{ $ingredientFullText }}">
                <span class="ingredient-icon">
                  <i class="fas fa-check"></i>
                </span>
                <span class="full-ingredient-text">{{ $ingredientFullText }}</span>
              </li>
            @endforeach
          </ul>
        </div>

        <!-- قسم المعدات -->
        <div class="section-card px-6 py-6 md:px-8 md:py-8">
          <h2 class="section-title mb-6">
            <i class="fas fa-tools"></i>
            <span>{{ __('recipe.sections.tools') }}</span>
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 slider-track tools-slider">
            @if((is_array($recipe->tools) ? count($recipe->tools) : $recipe->tools->count()) > 0)
              @foreach($recipe->tools as $tool)
                <div class="tool-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                  <!-- Tool Image -->
                  <div class="h-36 bg-gray-100 flex items-center justify-center">
                    @if(isset($tool['image']) && $tool['image'])
                      <img src="{{ asset('storage/' . $tool['image']) }}" alt="{{ $tool['name'] }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                      <i class="fas fa-tools text-4xl text-gray-400"></i>
                    @endif
                  </div>
                  
                  <!-- Tool Content -->
                  <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">{{ $tool['name'] }}</h3>
                    
                    <!-- Rating -->
                    <div class="flex items-center mb-3">
                      <div class="flex space-x-1 rtl:space-x-reverse">
                        @for($i = 1; $i <= 5; $i++)
                          <i class="fas fa-star {{ $i <= $tool['rating'] ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                        @endfor
                      </div>
                      <span class="text-sm text-gray-500 mr-2">{{ number_format($tool['rating'], 1) }}</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="text-lg font-bold text-orange-500 mb-3">
                      {{ $tool['price'] ? __('recipe.tools.price_label', ['price' => number_format($tool['price'], 2)]) : __('recipe.tools.price_unknown') }}
                    </div>
                    
                    <!-- Save Button -->
                    <button class="save-for-later-btn w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center space-x-2 rtl:space-x-reverse" 
                            data-tool-id="{{ $tool['id'] }}" 
                            data-tool-name="{{ $tool['name'] }}" 
                            data-tool-price="{{ $tool['price'] }}">
                      <i class="fas fa-bookmark"></i>
                      <span class="btn-text">{{ __('recipe.tools.actions.save') }}</span>
                      <i class="fas fa-spinner fa-spin loading-icon hidden"></i>
                    </button>
                    
                    <!-- Amazon Link -->
                    @if($tool['amazon_url'])
                      <a href="{{ $tool['amazon_url'] }}" target="_blank" 
                         class="mt-2 w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center space-x-2 rtl:space-x-reverse">
                        <i class="fab fa-amazon"></i>
                        <span>{{ __('recipe.tools.actions.view') }}</span>
                      </a>
                    @endif
                  </div>
                </div>
              @endforeach
            @else
              <div class="text-center text-gray-500 italic col-span-full py-8">
                <i class="fas fa-tools text-4xl text-gray-300 mb-3"></i>
                <p>{{ __('recipe.tools.empty') }}</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="mt-8">
        <div class="section-card px-6 py-6 md:px-8 md:py-8">
          <h2 class="section-title">
            <i class="fas fa-clipboard-check"></i>
            <span>{{ __('recipe.sections.instructions') }}</span>
          </h2>
          <ol class="step-list">
            @foreach($recipe->steps as $index => $step)
              <li class="step-item">
                <span class="step-number">{{ $index + 1 }}</span>
                <span class="step-text">{{ $step }}</span>
              </li>
            @endforeach
          </ol>
        </div>
      </div>
      </div>

      <div class="section-card callout-card mt-8 no-print">
        <div class="callout-meta">
          <span class="callout-badge">
            <i class="fas fa-users ltr:mr-1 rtl:ml-1"></i>
            {{ __('recipe.community.title') }}
          </span>
          <h3 id="question-text" class="font-semibold text-gray-800 text-lg md:text-xl">
            {{ __('recipe.community.question') }}
          </h3>
          <p id="made-it-count" class="text-gray-600 text-sm md:text-base">
            @if($recipe->made_count > 0)
              {!! trans_choice('recipe.community.count', $recipe->made_count, [
                  'count' => '<span class="font-bold text-orange-500 text-lg">'.$recipe->made_count.'</span>',
              ]) !!}
            @else
              {{ trans_choice('recipe.community.count', 0, ['count' => 0]) }}
            @endif
          </p>
        </div>
        <div class="cta-buttons">
          <button
            id="made-recipe-btn"
            class="flex items-center justify-center px-6 py-3 rounded-full font-semibold text-white transition-colors {{ $recipe->is_made ? 'bg-green-500 hover:bg-green-600' : 'bg-orange-500 hover:bg-orange-600' }}"
            data-recipe-id="{{ $recipe->recipe_id }}"
            data-user-id="{{ Auth::id() }}"
            data-made="{{ $recipe->is_made ? 'true' : 'false' }}"
            data-default-text="{{ __('recipe.community.button.default') }}"
            data-active-text="{{ __('recipe.community.button.active') }}"
          >
            <i class="fas fa-check-circle ltr:mr-2 rtl:ml-2"></i>
            <span id="made-btn-text">{{ $recipe->is_made ? __('recipe.community.button.active') : __('recipe.community.button.default') }}</span>
          </button>
          <button
            id="share-recipe-btn-2"
            class="flex items-center justify-center px-6 py-3 border border-orange-200 text-orange-600 rounded-full font-semibold bg-white/90 hover:bg-white transition-colors"
          >
            <i class="fas fa-share-alt ltr:mr-2 rtl:ml-2"></i>
            {{ __('recipe.share.section_title') }}
          </button>
        </div>
      </div>

      <section id="rating-section" class="py-8 no-print">
        <div class="section-card rating-card px-6 py-6 md:px-8 md:py-8">
          <h2 class="section-title">
            <i class="fas fa-star"></i>
            <span>{{ __('recipe.sections.rating') }}</span>
          </h2>
          <p class="text-gray-600 text-center max-w-2xl mx-auto -mt-2 mb-2">
            شارك تقييمك وتعليق قصير لدعم بقية المتعلمين. للتعليقات الأطول استخدم
            <a href="{{ route('contact') }}" class="text-emerald-600 font-semibold underline">نموذج التعليقات</a>.
          </p>
          <div class="flex flex-col items-center text-center gap-4">
            <div class="star-rating">
              <input type="radio" id="star5" name="rating" value="5" {{ $recipe->user_rating == 5 ? 'checked' : '' }} /><label for="star5" title="{{ trans_choice('recipe.rating.star_title', 5, ['count' => 5]) }}">
                <span class="star">★</span></label
              >
              <input type="radio" id="star4" name="rating" value="4" {{ $recipe->user_rating == 4 ? 'checked' : '' }} /><label for="star4" title="{{ trans_choice('recipe.rating.star_title', 4, ['count' => 4]) }}">
                <span class="star">★</span></label
              >
              <input type="radio" id="star3" name="rating" value="3" {{ $recipe->user_rating == 3 ? 'checked' : '' }} /><label for="star3" title="{{ trans_choice('recipe.rating.star_title', 3, ['count' => 3]) }}">
                <span class="star">★</span></label
              >
              <input type="radio" id="star2" name="rating" value="2" {{ $recipe->user_rating == 2 ? 'checked' : '' }} /><label for="star2" title="{{ trans_choice('recipe.rating.star_title', 2, ['count' => 2]) }}">
                <span class="star">★</span></label
              >
              <input type="radio" id="star1" name="rating" value="1" {{ $recipe->user_rating == 1 ? 'checked' : '' }} /><label for="star1" title="{{ trans_choice('recipe.rating.star_title', 1, ['count' => 1]) }}">
                <span class="star">★</span></label
              >
            </div>
            <p id="user-rating-text" class="text-center text-gray-500 dark-text mt-2 mb-4">
              @if(Auth::check())
                @if($recipe->user_rating)
                  {{ __('recipe.rating.user_rating', ['rating' => $recipe->user_rating]) }}
                @else
                  {{ __('recipe.rating.prompt') }}
                @endif
              @else
                <a href="/login" class="text-orange-500 hover:text-orange-600 underline">{{ __('recipe.rating.cta.login_link') }}</a>
              @endif
            </p>
            @if(Auth::check())
              <div class="rating-actions">
                <button
                  id="submit-rating-btn"
                  class="flex-1 px-6 py-3 rounded-full font-semibold text-white bg-orange-500 hover:bg-orange-600 transition-colors"
                >
                  <i class="fas fa-paper-plane ltr:mr-2 rtl:ml-2"></i>
                  {{ __('recipe.rating.button.submit') }}
                </button>
                @if($recipe->user_rating)
                  <button
                    id="remove-rating-btn"
                    class="flex-1 px-6 py-3 rounded-full font-semibold text-red-500 bg-transparent border-2 border-red-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                  >
                    <i class="fas fa-trash ltr:mr-2 rtl:ml-2"></i>
                    {{ __('recipe.rating.button.remove') }}
                  </button>
                @endif
              </div>
            @else
              <a href="/login" class="inline-flex items-center justify-center px-6 py-3 rounded-full font-semibold text-white bg-orange-500 hover:bg-orange-600 transition-colors">
                <i class="fas fa-sign-in-alt ltr:mr-2 rtl:ml-2"></i>
                {{ __('recipe.rating.cta.login_button') }}
              </a>
            @endif
          </div>
        </div>
      </section>

      <section class="mt-8 no-print related-section">
        <div class="section-card px-6 py-6 md:px-8 md:py-8">
          <h2 class="section-title">
            <i class="fas fa-utensils"></i>
            <span>{{ __('recipe.sections.related') }}</span>
          </h2>
          
          @if($relatedRecipes && $relatedRecipes->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 slider-track related-slider">
              @foreach($relatedRecipes as $relatedRecipe)
                <div class="related-recipe-card bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                  <!-- Recipe Image -->
                  <div class="recipe-image h-48 bg-gray-100 flex items-center justify-center">
                    @if($relatedRecipe->image_url)
                      <img src="{{ $relatedRecipe->image_url }}" alt="{{ $relatedRecipe->title }}" 
                           class="w-full h-full object-cover"
                          onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}'; this.alt='{{ __('recipe.misc.placeholder_image_alt') }}';" loading="lazy">
                    @else
                      <i class="fas fa-image text-4xl text-gray-400"></i>
                    @endif
                  </div>
                  
                  <!-- Recipe Content -->
                  <div class="p-4">
                    <h3 class="recipe-title font-semibold text-gray-800 mb-2 text-lg">
                      {{ Str::limit($relatedRecipe->title, 50) }}
                    </h3>
                    
                    <!-- Category Badge -->
                    @if($relatedRecipe->category)
                      <div class="mb-3">
                        <span class="category-badge inline-block text-xs font-semibold px-2 py-1 rounded-full">
                          {{ $relatedRecipe->category->name }}
                        </span>
                      </div>
                    @endif
                    
                    <!-- Recipe Info -->
                    <div class="recipe-info flex items-center justify-between text-sm text-gray-600 mb-3">
                      <div class="flex items-center">
                        <i class="fas fa-clock text-orange-500 ltr:mr-1 rtl:ml-1"></i>
                        <span>{{ trans_choice('recipe.units.minutes', (int)$relatedRecipe->prep_time, ['count' => (int)$relatedRecipe->prep_time]) }}</span>
                      </div>
                      <div class="flex items-center">
                        <i class="fas fa-users text-orange-500 ltr:mr-1 rtl:ml-1"></i>
                        <span>{{ trans_choice('recipe.units.people', (int)$relatedRecipe->servings, ['count' => (int)$relatedRecipe->servings]) }}</span>
                      </div>
                    </div>
                    
                    <!-- Rating -->
                    <div class="flex items-center mb-4">
                      <div class="flex space-x-1 rtl:space-x-reverse">
                        @for($i = 1; $i <= 5; $i++)
                          <i class="fas fa-star {{ $i <= ($relatedRecipe->interactions_avg_rating ?? 0) ? 'rating-stars' : 'empty-rating' }} text-sm"></i>
                        @endfor
                      </div>
                      <span class="text-sm text-gray-500 mr-2">
                        @if($relatedRecipe->interactions_avg_rating)
                          {{ number_format($relatedRecipe->interactions_avg_rating, 1) }}
                        @else
                          {{ __('recipe.related.no_ratings') }}
                        @endif
                      </span>
                    </div>
                    
                    <!-- Save Count -->
                    <div class="flex items-center text-sm text-gray-600 mb-4">
                      <i class="fas fa-bookmark text-orange-500 ltr:mr-1 rtl:ml-1"></i>
                      <span>{{ trans_choice('recipe.related.saved', $relatedRecipe->saved_count, ['count' => $relatedRecipe->saved_count]) }}</span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons flex space-x-2 rtl:space-x-reverse">
                      <a href="{{ route('recipe.show', $relatedRecipe->slug) }}" 
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg transition-colors text-center text-sm font-semibold">
                        <i class="fas fa-eye ltr:mr-1 rtl:ml-1"></i>
                        {{ __('recipe.actions.view_recipe') }}
                      </a>

                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center text-gray-500 py-8">
              <i class="fas fa-utensils text-4xl text-gray-300 mb-3"></i>
              <p>{{ __('recipe.related.empty') }}</p>
            </div>
          @endif
        </div>
      </section>
      
    </main>
    
    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
            </div>
            
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ __('recipe.rating.modal.title') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('recipe.rating.modal.question') }}<br>
                    <span class="text-sm text-gray-500">{{ __('recipe.rating.modal.hint') }}</span>
                </p>
            </div>
            
            <div class="flex space-x-3 rtl:space-x-reverse">
                <button id="confirm-cancel" class="flex-1 p-3 rounded-lg font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                    {{ __('recipe.rating.modal.cancel') }}
                </button>
                <button id="confirm-delete" class="flex-1 p-3 rounded-lg font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">
                    <i class="fas fa-trash ltr:mr-2 rtl:ml-2"></i>
                    {{ __('recipe.rating.modal.confirm') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="share-modal" data-share-recipe class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">{{ __('recipe.share.modal.title') }}</h3>
                <button id="close-share-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Recipe Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">{{ $recipe->title }}</h4>
                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($recipe->description, 100) }}</p>
                    <div class="flex items-center space-x-4 rtl:space-x-reverse text-sm text-gray-500">
                        <span><i class="fas fa-clock ltr:mr-1 rtl:ml-1"></i> {{ __('recipe.share.modal.stats.prep', ['minutes' => trans_choice('recipe.units.minutes', (int)$recipe->prep_time, ['count' => (int)$recipe->prep_time])]) }}</span>
                        <span><i class="fas fa-users ltr:mr-1 rtl:ml-1"></i> {{ __('recipe.share.modal.stats.servings', ['count' => (int)$recipe->servings]) }}</span>
                    </div>
                </div>
                
                <!-- Share Options -->
                <div class="space-y-3">
                    <h5 class="font-semibold text-gray-700">{{ __('recipe.share.modal.options_title') }}</h5>
                    
                    <!-- Copy Link -->
                    <button id="copy-link-btn" class="w-full flex items-center justify-center p-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                        <i class="fas fa-copy ltr:mr-2 rtl:ml-2"></i>
                        {{ __('recipe.share.modal.copy_link') }}
                    </button>
                    
                    <!-- Social Media -->
                    <div class="grid grid-cols-2 gap-3">
                        <a id="whatsapp-share" href="#" target="_blank" class="flex items-center justify-center p-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            <i class="fab fa-whatsapp ltr:mr-2 rtl:ml-2"></i>
                            {{ __('recipe.share.modal.whatsapp') }}
                        </a>
                        <a id="telegram-share" href="#" target="_blank" class="flex items-center justify-center p-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fab fa-telegram ltr:mr-2 rtl:ml-2"></i>
                            {{ __('recipe.share.modal.telegram') }}
                        </a>
                    </div>
                </div>
                
                <!-- Success Message -->
                <div id="copy-success" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle ltr:mr-2 rtl:ml-2"></i>
                    {{ __('recipe.share.modal.copied') }}
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script>
            const recipeText = @json($recipeJsTranslations, JSON_UNESCAPED_UNICODE);
            const toolsText = recipeText.tools;
            const ratingText = recipeText.rating;
            const formatUserRating = (value) => ratingText.user_rating_template.replace(':rating', value);
            // Extra handling for Google Drive links on the recipe page
            document.addEventListener('DOMContentLoaded', function() {
                // Process images already in the DOM
                function processRecipeImages() {
                    const images = document.querySelectorAll('img[src*="drive.google.com"]');
                    images.forEach(function(img) {
                        const originalSrc = img.src;
                        const convertedSrc = convertGoogleDriveUrl(originalSrc);
                        if (convertedSrc !== originalSrc) {
                            img.src = convertedSrc;
                        }
                    });
                }
                
                // Initial pass
                processRecipeImages();
                
                // Repeat every two seconds to catch dynamically loaded images
                const interval = setInterval(function() {
                    processRecipeImages();
                }, 2000);
                
                // Stop polling after ten seconds
                setTimeout(function() {
                    clearInterval(interval);
                }, 10000);

                // Load saved status for tools
                loadToolsSavedStatus();
                
                // Load saved count for the badge
                
                // Initialize serving size functionality
                initializeServingSize();
                
                // Initialize rating functionality
                initializeRating();
                
                // Initialize rating scroll functionality
                initializeRatingScroll();
                
                // Initialize remove rating functionality
                initializeRemoveRating();
                
                // Initialize modal close functionality
                initializeModalClose();
            });

            function initializeModalClose() {
                const confirmModal = document.getElementById('confirm-modal');
                const shareModal = document.getElementById('share-modal');

                const hideModal = (modal) => {
                    if (!modal) {
                        return;
                    }

                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                if (confirmModal) {
                    confirmModal.addEventListener('click', function(event) {
                        if (event.target === confirmModal) {
                            hideModal(confirmModal);
                        }
                    });
                }

                document.addEventListener('keydown', function(event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    hideModal(confirmModal);

                    if (window.ShareRecipe && typeof window.ShareRecipe.closeShareModal === 'function') {
                        window.ShareRecipe.closeShareModal();
                    } else if (shareModal && !shareModal.classList.contains('hidden')) {
                        shareModal.classList.add('hidden');
                        shareModal.classList.remove('opacity-100', 'opacity-0');
                        document.body.style.overflow = '';
                    }
                });
            }

            // Load saved status for tools
            function loadToolsSavedStatus() {
                fetch('/saved/status')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update each tool's saved status
                            document.querySelectorAll('.save-for-later-btn').forEach(btn => {
                                const toolId = btn.dataset.toolId;
                                const isSaved = data.saved_tools.includes(parseInt(toolId));
                                
                                if (isSaved) {
                                    btn.querySelector('.btn-text').textContent = toolsText.actions.saved;
                                    btn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                                    btn.classList.add('bg-green-500', 'hover:bg-green-600', 'saved');
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading saved status:', error);
                    });
            }

            // Save for Later Functionality for tools
            document.addEventListener('click', function(e) {
                if (e.target.closest('.save-for-later-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.save-for-later-btn');
                    const toolId = btn.dataset.toolId;
                    const toolName = btn.dataset.toolName;
                    const toolPrice = btn.dataset.toolPrice;
                    
                    // Check if item is already saved
                    if (btn.classList.contains('saved')) {
                        // Item is saved, remove it
                        removeFromSaved(btn, toolId);
                        return;
                    }
                    
                    // Show loading state
                    btn.disabled = true;
                    btn.querySelector('.btn-text').textContent = toolsText.actions.saving;
                    btn.querySelector('.loading-icon').classList.remove('hidden');
                    
                    // Save for later
                    fetch('/saved/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            tool_id: toolId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success state - Item is now saved
                            btn.querySelector('.btn-text').textContent = toolsText.actions.saved;
                            btn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                            btn.classList.add('bg-green-500', 'hover:bg-green-600');
                            btn.classList.add('saved');
                            btn.disabled = false;
                            
                            // Show success animation on the button
                            btn.style.transform = 'scale(1.05)';
                            setTimeout(() => {
                                btn.style.transform = 'scale(1)';
                            }, 200);
                            
                            // Show toast notification
                            showToast(toolsText.messages.save_success, 'success');
                            
                            // Hide loading icon
                            btn.querySelector('.loading-icon').classList.add('hidden');
                        } else {
                            // Show error state
                            btn.querySelector('.btn-text').textContent = toolsText.actions.error;
                            btn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                            btn.classList.add('bg-red-500');
                            
                            showToast(data.message || toolsText.messages.save_error, 'error');
                            
                            // Reset button after 2 seconds
                            setTimeout(() => {
                                btn.disabled = false;
                                btn.querySelector('.btn-text').textContent = toolsText.actions.save;
                                btn.querySelector('.loading-icon').classList.add('hidden');
                                btn.classList.remove('bg-red-500');
                                btn.classList.add('bg-orange-500', 'hover:bg-orange-600');
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.disabled = false;
                        btn.querySelector('.btn-text').textContent = toolsText.actions.save;
                        btn.querySelector('.loading-icon').classList.add('hidden');
                        showToast(toolsText.messages.save_error, 'error');
                    });
                }
            });

            // Remove from saved function
            function removeFromSaved(button, toolId) {
                // Show loading state
                button.disabled = true;
                button.querySelector('.btn-text').textContent = toolsText.actions.removing;
                button.querySelector('.loading-icon').classList.remove('hidden');
                
                // Remove from saved
                fetch('/saved/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        tool_id: toolId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success state - Item removed
                        button.querySelector('.btn-text').textContent = toolsText.actions.save;
                        button.classList.remove('bg-green-500', 'hover:bg-green-600', 'saved');
                        button.classList.add('bg-orange-500', 'hover:bg-orange-600');
                        button.disabled = false;
                        
                        // Show success animation
                        button.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            button.style.transform = 'scale(1)';
                        }, 200);
                        
                        // Show success message
                        showToast(toolsText.messages.remove_success, 'success');
                        
                        // Hide loading icon
                        button.querySelector('.loading-icon').classList.add('hidden');
                    } else {
                        throw new Error(data.message || toolsText.messages.remove_error);
                    }
                })
                .catch(error => {
                    console.error('Error removing from saved:', error);
                    button.disabled = false;
                    button.querySelector('.btn-text').textContent = toolsText.actions.saved;
                    button.querySelector('.loading-icon').classList.add('hidden');
                    showToast(error.message || toolsText.messages.remove_error, 'error');
                });
            }

            // Toast notification function
            function showToast(message, type = 'info') {
                // Remove existing toasts
                const existingToasts = document.querySelectorAll('.toast');
                existingToasts.forEach(toast => toast.remove());
                
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
                
                // Set color based on type
                if (type === 'success') {
                    toast.classList.add('bg-green-500');
                } else if (type === 'error') {
                    toast.classList.add('bg-red-500');
                } else if (type === 'warning') {
                    toast.classList.add('bg-yellow-500');
                } else {
                    toast.classList.add('bg-blue-500');
                }
                
                toast.textContent = message;
                document.body.appendChild(toast);
                
                // Animate in
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 100);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }

            // Serving size functionality
            function initializeServingSize() {
                const servingButtons = document.querySelectorAll('.serving-size-btn');
                const ingredientItems = document.querySelectorAll('.ingredient-item');
                
                servingButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons
                        servingButtons.forEach(btn => {
                            btn.classList.remove('bg-orange-500', 'text-white');
                            btn.classList.add('bg-white', 'text-gray-700');
                        });
                        
                        // Add active class to clicked button
                        this.classList.remove('bg-white', 'text-gray-700');
                        this.classList.add('bg-orange-500', 'text-white');
                        
                        // Get multiplier
                        const multiplier = parseFloat(this.dataset.multiplier);
                        
                        // Update ingredient quantities
                        ingredientItems.forEach(item => {
                            const originalQuantity = item.dataset.originalQuantity;
                            const name = item.dataset.name || '';
                            
                            if (multiplier === 1) {
                                // Reset to original quantity
                                const fullTextSpan = item.querySelector('.full-ingredient-text');
                                if (fullTextSpan) {
                                    fullTextSpan.textContent = originalQuantity + ' ' + name;
                                }
                            } else {
                                // Try to extract number from quantity string (e.g., "2 cups" -> 2)
                                const quantityMatch = originalQuantity.match(/(\d+(?:\.\d+)?)/);
                                if (quantityMatch) {
                                    const originalAmount = parseFloat(quantityMatch[1]);
                                    const newAmount = originalAmount * multiplier;
                                    
                                    // Format the new amount
                                    let displayAmount;
                                    if (newAmount === 0.25) displayAmount = "1/4";
                                    else if (newAmount === 0.5) displayAmount = "1/2";
                                    else if (newAmount === 0.75) displayAmount = "3/4";
                                    else if (newAmount % 1 === 0) displayAmount = newAmount.toString();
                                    else displayAmount = newAmount.toFixed(2).replace(/\.00$/, '');
                                    
                                    // Extract the unit part (everything after the number)
                                    const unitPart = originalQuantity.replace(/(\d+(?:\.\d+)?)/, '').trim();
                                    
                                    // Update the full text span with new amount + unit + name
                                    const fullTextSpan = item.querySelector('.full-ingredient-text');
                                    if (fullTextSpan) {
                                        fullTextSpan.textContent = displayAmount + ' ' + unitPart + ' ' + name;
                                    }
                                } else {
                                    // If no number found, keep original
                                    const fullTextSpan = item.querySelector('.full-ingredient-text');
                                    if (fullTextSpan) {
                                        fullTextSpan.textContent = originalQuantity + ' ' + name;
                                    }
                                }
                            }
                        });
                    });
                });
            }

            // Rating functionality
            function initializeRating() {
                const ratingInputs = document.querySelectorAll('input[name="rating"]');
                const submitBtn = document.getElementById('submit-rating-btn');
                const userRatingText = document.getElementById('user-rating-text');
                const recipeId = document.querySelector('[data-recipe-id]')?.dataset.recipeId;

                // Handle star clicks
                ratingInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const rating = this.value;
                        userRatingText.textContent = formatUserRating(rating);
                        
                        // Reset submit button if it was previously submitted
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = `<i class="fas fa-paper-plane ltr:mr-2 rtl:ml-2"></i>${ratingText.button.submit}`;
                            submitBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
                            submitBtn.classList.add('bg-orange-500', 'hover:bg-orange-600');
                        }
                    });
                });

                // Handle submit button
                if (submitBtn && recipeId) {
                    submitBtn.addEventListener('click', function() {
                        const selectedRating = document.querySelector('input[name="rating"]:checked');
                        
                        if (!selectedRating) {
                            showToast(ratingText.messages.choose, 'warning');
                            return;
                        }

                        const rating = selectedRating.value;
                        
                        // Check if user is authenticated
                        const userId = document.querySelector('[data-user-id]')?.dataset.userId;
                        if (!userId || userId === '' || userId === 'null') {
                            showToast(ratingText.messages.login, 'warning');
                            // Redirect to login page
                            setTimeout(() => {
                                window.location.href = '/login';
                            }, 2000);
                            return;
                        }
                        
                        // Show loading state
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin ltr:mr-2 rtl:ml-2"></i>${ratingText.state.submitting}`;

                        // Send rating to server
                        const saveRecipeBtn = document.getElementById('save-recipe-page-btn');
                        const madeRecipeBtn = document.getElementById('made-recipe-btn');

                        const requestPayload = {
                            recipe_id: recipeId,
                            rating: parseInt(rating)
                        };

                        if (saveRecipeBtn && typeof saveRecipeBtn.dataset.saved !== 'undefined') {
                            requestPayload.is_saved = saveRecipeBtn.dataset.saved === 'true';
                        }

                        if (madeRecipeBtn && typeof madeRecipeBtn.dataset.made !== 'undefined') {
                            requestPayload.is_made = madeRecipeBtn.dataset.made === 'true';
                        }

                        fetch('/api/interactions', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(requestPayload)
                        })
                        .then(response => {
                            if (response.status === 401) {
                                throw new Error(ratingText.messages.login);
                            }
                            return response.json();
                        })
                        .then(data => {
                            showToast(ratingText.messages.submit_success, 'success');
                            userRatingText.textContent = formatUserRating(rating);
                            
                            // Update button text
                            submitBtn.innerHTML = `<i class="fas fa-check ltr:mr-2 rtl:ml-2"></i>${ratingText.state.submitted}`;
                            submitBtn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                            submitBtn.classList.add('bg-green-500', 'hover:bg-green-600');
                            
                            // Update rating button text and style
                            const ratingBtn = document.getElementById('rating-scroll-btn');
                            const ratingBtnText = document.getElementById('rating-btn-text');
                            if (ratingBtn && ratingBtnText) {
                                ratingBtnText.textContent = ratingText.button.rated;
                                ratingBtn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');
                                ratingBtn.classList.remove('text-gray-700');
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting rating:', error);
                            showToast(error.message || ratingText.messages.submit_error, 'error');
                            
                            // Reset button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = `<i class="fas fa-paper-plane ltr:mr-2 rtl:ml-2"></i>${ratingText.button.submit}`;
                        });
                    });
                }
            }

            // Image Gallery Functions
            let currentImageIndex = 0;
            const images = @json($recipe->getAllImages() ?? []);
            
            function showImage(index) {
                if (index >= 0 && index < images.length) {
                    currentImageIndex = index;
                    const mainImage = document.getElementById('main-recipe-image');
                    const imageCounter = document.getElementById('image-counter');
                    const thumbnails = document.querySelectorAll('.thumbnail');
                    
                    if (mainImage) {
                        mainImage.src = images[index];
                    }
                    
                    if (imageCounter) {
                        imageCounter.textContent = index + 1;
                    }
                    
                    // Update thumbnail state
                    thumbnails.forEach((thumb, i) => {
                        thumb.classList.remove('active');
                        if (i === index) {
                            thumb.classList.add('active');
                        }
                    });
                }
            }
            
            function nextImage() {
                const nextIndex = (currentImageIndex + 1) % images.length;
                showImage(nextIndex);
            }
            
            function previousImage() {
                const prevIndex = currentImageIndex === 0 ? images.length - 1 : currentImageIndex - 1;
                showImage(prevIndex);
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (images.length > 1) {
                    if (e.key === 'ArrowRight') {
                        nextImage();
                    } else if (e.key === 'ArrowLeft') {
                        previousImage();
                    }
                }
            });

            // Rating scroll functionality
            function initializeRatingScroll() {
                const ratingScrollBtn = document.getElementById('rating-scroll-btn');
                const ratingSection = document.getElementById('rating-section');
                
                if (ratingScrollBtn && ratingSection) {
                    ratingScrollBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Smooth scroll to rating section
                        ratingSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        
                        // Add a subtle highlight effect to the rating section
                        ratingSection.style.transition = 'box-shadow 0.3s ease';
                        ratingSection.style.boxShadow = '0 0 20px rgba(8, 25, 255, 0.3)';
                        
                        // Remove highlight after 2 seconds
                        setTimeout(() => {
                            ratingSection.style.boxShadow = '';
                        }, 2000);
                    });
                }
            }

            // Remove rating functionality
            function initializeRemoveRating() {
                const removeRatingBtn = document.getElementById('remove-rating-btn');
                const confirmModal = document.getElementById('confirm-modal');
                const confirmCancel = document.getElementById('confirm-cancel');
                const confirmDelete = document.getElementById('confirm-delete');
                const recipeId = document.querySelector('[data-recipe-id]')?.dataset.recipeId;
                
                if (removeRatingBtn && recipeId) {
                    removeRatingBtn.addEventListener('click', function() {
                        // Show custom confirmation modal
                        confirmModal.classList.remove('hidden');
                        confirmModal.classList.add('flex');
                    });
                }
                
                // Handle cancel button
                if (confirmCancel) {
                    confirmCancel.addEventListener('click', function() {
                        confirmModal.classList.add('hidden');
                        confirmModal.classList.remove('flex');
                    });
                }
                
                // Handle confirm delete button
                if (confirmDelete && recipeId) {
                    confirmDelete.addEventListener('click', function() {
                        // Hide modal first
                        confirmModal.classList.add('hidden');
                        confirmModal.classList.remove('flex');
                        
                        // Show loading state
                        const originalText = removeRatingBtn.innerHTML;
                        removeRatingBtn.disabled = true;
                        removeRatingBtn.innerHTML = `<i class="fas fa-spinner fa-spin ltr:mr-2 rtl:ml-2"></i>${ratingText.state.removing}`;
                        
                        // Send request to remove rating
                        fetch('/api/interactions/remove', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                recipe_id: recipeId
                            })
                        })
                        .then(response => {
                            if (response.status === 401) {
                                throw new Error(ratingText.messages.login_remove);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showToast(ratingText.messages.remove_success, 'success');
                                
                                // Reset rating inputs
                                document.querySelectorAll('input[name="rating"]').forEach(input => {
                                    input.checked = false;
                                });
                                
                                // Update user rating text
                                const userRatingText = document.getElementById('user-rating-text');
                                if (userRatingText) {
                                    userRatingText.textContent = ratingText.prompt;
                                }
                                
                                // Hide remove rating button
                                removeRatingBtn.style.display = 'none';
                                
                                // Update rating button in header
                                const ratingBtn = document.getElementById('rating-scroll-btn');
                                const ratingBtnText = document.getElementById('rating-btn-text');
                                if (ratingBtn && ratingBtnText) {
                                    ratingBtnText.textContent = ratingText.button.rate;
                                    ratingBtn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
                                    ratingBtn.classList.add('text-gray-700');
                                }
                                
                                // Reset submit button
                                const submitBtn = document.getElementById('submit-rating-btn');
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = `<i class="fas fa-paper-plane ltr:mr-2 rtl:ml-2"></i>${ratingText.button.submit}`;
                                    submitBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
                                    submitBtn.classList.add('bg-orange-500', 'hover:bg-orange-600');
                                }
                            } else {
                                throw new Error(data.message || ratingText.messages.remove_error);
                            }
                        })
                        .catch(error => {
                            console.error('Error removing rating:', error);
                            showToast(error.message || ratingText.messages.remove_error, 'error');
                            
                            // Reset button
                            removeRatingBtn.disabled = false;
                            removeRatingBtn.innerHTML = originalText;
                        });
                    });
                }
            }
        </script>
    @endpush
@endsection














