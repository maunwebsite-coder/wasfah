@extends('layouts.app')

@section('title', __('recipes.meta.title'))

@php
    $hideFooter = true;
    // Keep navbar on desktop; hide only for mobile webview that sends the tab-bar header
    $hideNavbar = request()->header('X-Mobile-Tab-Bar') ? true : false;
    $lockMobileTabBar = true;
    $hideBreadcrumbs = true;
@endphp

@push('styles')
<style>
:root {
    --peah-navy: #3b1a1f;
    --peah-teal: #9f5e63;
    --peah-yellow: #f7c948;
    --peah-ink: #321417;
    --peah-cloud: #f4f7fb;
    --mobile-tab-bar-height: 72px;
    --navbar-safe-offset: 72px;
    --feed-top-gap: 24px;
}

/* Hide the main navbar on mobile feed view and give the first card breathing room */
@media (max-width: 768px) {
    :root {
        --navbar-safe-offset: 0px;
        --feed-top-gap: 0px;
    }
    body:has(.feed-page) header[data-navbar-layer] {
        display: none !important;
    }
    .feed-grid,
    body:has([data-navbar-layer]) .feed-page,
    .feed-page {
        padding-top: 0 !important;
        scroll-padding-top: 0 !important;
    }
}

.composer-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 31, 61, 0.38);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.2rem;
    z-index: 40;
}

.composer-modal.is-open {
    display: flex;
}

.composer-modal__dialog {
    width: min(980px, 100%);
    background: linear-gradient(135deg, #f9fbff 0%, #ffffff 45%, #f7fbff 100%);
    border: 1px solid #e4e7ec;
    border-radius: 18px;
    box-shadow: 0 24px 50px rgba(15, 31, 61, 0.18);
    overflow: hidden;
    animation: modalFade 180ms ease;
}

@keyframes modalFade {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.composer-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.4rem;
    border-bottom: 1px solid #eef1f5;
    background: #f8fbff;
}

.composer-modal__eyebrow {
    font-weight: 800;
    color: #0c8f78;
    font-size: 0.9rem;
    margin: 0;
}

.composer-modal__title {
    font-size: 1.28rem;
    font-weight: 900;
    color: var(--peah-ink);
    margin: 0.1rem 0 0;
}

.composer-modal__subtitle {
    color: #475569;
    font-weight: 700;
    margin-top: 0.2rem;
    font-size: 0.95rem;
}

.composer-modal__body {
    padding: 1.2rem 1.4rem 1.5rem;
    display: grid;
    gap: 1rem;
    background: radial-gradient(circle at 16% 20%, rgba(143, 74, 80, 0.06), transparent 34%),
        radial-gradient(circle at 80% 10%, rgba(15, 31, 61, 0.04), transparent 42%);
}

.composer-form {
    display: grid;
    gap: 1rem;
}

.composer-field {
    display: grid;
    gap: 0.35rem;
}

.composer-field label {
    font-weight: 800;
    color: var(--peah-ink);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.95rem;
}

.composer-field input,
.composer-field textarea {
    width: 100%;
    border: 1px solid #dfe3eb;
    border-radius: 12px;
    padding: 0.75rem 0.9rem;
    background: #fff;
    font-size: 0.97rem;
    color: #3b1a1f;
    transition: border-color 0.12s ease, box-shadow 0.12s ease;
}

.composer-field input:focus,
.composer-field textarea:focus {
    outline: none;
    border-color: rgba(143, 74, 80, 0.45);
    box-shadow: 0 0 0 3px rgba(143, 74, 80, 0.15);
}

.composer-field textarea {
    min-height: 140px;
    resize: vertical;
}

.composer-upload {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 0.9rem;
    background: #f8fafc;
    display: grid;
    gap: 0.55rem;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.composer-upload__label {
    font-weight: 800;
    color: #3b1a1f;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.6rem 0.75rem;
    border-radius: 10px;
    background: #fff;
    border: 1px solid #e2e8f0;
    width: fit-content;
}

.composer-hint {
    color: #64748b;
    font-weight: 700;
    font-size: 0.9rem;
}

.composer-upload__input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.composer-upload__label.has-file {
    background: #3b1a1f;
    color: #fff;
    border-color: #3b1a1f;
}

.composer-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.65rem;
    border-radius: 10px;
    background: rgba(143, 74, 80, 0.12);
    color: #0c8f78;
    font-weight: 800;
    font-size: 0.9rem;
}

.composer-modal__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 0 1.4rem 1.4rem;
}

.composer-btn {
    border: none;
    border-radius: 10px;
    padding: 0.7rem 1rem;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    transition: transform 0.12s ease, box-shadow 0.14s ease, background 0.12s ease;
}

.composer-btn:active {
    transform: translateY(1px);
}

.composer-btn--ghost {
    background: #f1f5f9;
    color: #3b1a1f;
    border: 1px solid #e2e8f0;
}

.composer-btn--primary {
    background: linear-gradient(135deg, #9f5e63, #0c8f78);
    color: #fff;
    box-shadow: 0 12px 24px rgba(143, 74, 80, 0.24);
}

.composer-modal__close {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #3b1a1f;
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: transform 0.12s ease, background 0.12s ease;
}

.composer-modal__close:hover {
    background: #f8fafc;
    transform: translateY(-1px);
}

.composer-toast {
    position: fixed;
    inset-inline-start: 50%;
    top: 12px;
    transform: translateX(-50%) translateY(-12px);
    background: #0c8f78;
    color: #fff;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.15);
    font-weight: 800;
    z-index: 60;
    opacity: 0;
    transition: opacity 0.14s ease, transform 0.14s ease;
    pointer-events: none;
}

.composer-toast--error {
    background: #dc2626;
}

.composer-toast.is-visible {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

body.is-modal-open {
    overflow: hidden;
}

.post-composer {
    margin-bottom: 1.2rem;
    padding: 0.95rem 1.05rem;
    border: 1px solid #e4e7ec;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 10px 24px rgba(15, 31, 61, 0.06);
    display: grid;
    gap: 0.75rem;
}

.post-composer__top {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.post-composer__avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(143, 74, 80, 0.14), rgba(15, 31, 61, 0.12));
    display: grid;
    place-items: center;
    font-weight: 900;
    color: var(--peah-ink);
    flex-shrink: 0;
}

.post-composer__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-composer__field {
    flex: 1;
    display: flex;
}

.post-composer__trigger {
    width: 100%;
    border: 1px solid #dfe3eb;
    background: linear-gradient(120deg, #f8fafc, #fdfefe);
    border-radius: 999px;
    padding: 0.78rem 1.05rem;
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: #4b5563;
    font-weight: 800;
    font-size: 0.99rem;
    text-decoration: none;
    transition: border-color 0.12s ease, box-shadow 0.12s ease, transform 0.08s ease;
    cursor: pointer;
    appearance: none;
}

.post-composer__trigger:hover {
    border-color: rgba(143, 74, 80, 0.28);
    box-shadow: 0 6px 14px rgba(15, 31, 61, 0.08);
    transform: translateY(-1px);
}

.post-composer__actions {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    padding-inline-start: calc(46px + 0.75rem);
    flex-wrap: wrap;
}

.composer-action {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: var(--peah-ink);
    font-weight: 800;
    font-size: 0.93rem;
    text-decoration: none;
    padding: 0.35rem 0.4rem;
    border-radius: 10px;
    transition: background 0.12s ease, transform 0.08s ease;
    background: transparent;
    border: none;
    cursor: pointer;
}

.composer-action i {
    font-size: 1rem;
}

.composer-action--video i {
    color: var(--peah-teal);
}

.composer-action--photo i {
    color: #6b2e30;
}

.composer-action--article i {
    color: var(--peah-yellow);
}

.composer-action:hover {
    background: rgba(143, 74, 80, 0.08);
    transform: translateY(-1px);
}

.feed-page {
    height: 100vh;
    height: 100dvh;
    width: 100%;
    background: #000;
    box-sizing: border-box;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
    position: fixed;
    inset: 0;
    z-index: 10;
}
.feed-page {
    scroll-padding-top: calc(var(--navbar-safe-offset) + var(--feed-top-gap));
}
body:has([data-navbar-layer]) .feed-page {
    padding-top: calc(var(--navbar-safe-offset) + var(--feed-top-gap));
    scroll-padding-top: calc(var(--navbar-safe-offset) + var(--feed-top-gap));
}
.feed-pattern {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 15% 20%, rgba(143, 74, 80, 0.08), transparent 28%),
        radial-gradient(circle at 85% 30%, rgba(247, 201, 72, 0.07), transparent 32%),
        radial-gradient(circle at 70% 80%, rgba(12, 143, 120, 0.08), transparent 32%);
    opacity: 0.75;
    pointer-events: none;
    z-index: 0;
}

.feed-page::-webkit-scrollbar {
    display: none;
}

.feed-shell {
    width: 100%;
    height: 100%;
    position: relative;
    z-index: 1;
}

.feed-stream {
    width: 100%;
    padding: 0;
    margin: 0;
    position: relative;
    z-index: 1;
}

.feed-grid {
    display: block;
    width: 100%;
    padding-top: var(--feed-top-gap);
}

.feed-card {
    position: relative;
    width: 100%;
    height: 100vh;
    height: 100dvh;
    scroll-snap-align: start;
    scroll-snap-stop: always;
    background: #000;
    overflow: hidden;
    border: none;
    border-radius: 0;
    margin: 0;
    box-shadow: none;
}

.feed-card.is-highlighted {
    outline: 2px solid #f59e0b;
    box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.28),
        0 0 0 6px rgba(245, 158, 11, 0.22);
    animation: feed-card-highlight 1.3s ease-in-out 2;
    scroll-margin-top: 96px;
}

@keyframes feed-card-highlight {
    0% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-2px) scale(1.01);
    }
    100% {
        transform: translateY(0) scale(1);
    }
}

.feed-card__more {
    position: absolute;
    inset-inline-end: 0.6rem;
    inset-block-start: 0.6rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 0.85rem;
    font-weight: 800;
    transition: transform 0.1s ease, background 0.15s ease;
    z-index: 2;
}

.feed-card__more:hover {
    background: rgba(255, 255, 255, 0.22);
    transform: translateY(-1px);
}

.feed-card__menu {
    position: absolute;
    inset-inline-end: 0.6rem;
    inset-block-start: 2.6rem;
    min-width: 150px;
    background: rgba(0, 0, 0, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4);
    padding: 0.3rem 0;
    display: none;
    z-index: 3;
}

.feed-card__menu.is-open {
    display: block;
}

.feed-card__menu a {
    display: block;
    padding: 0.55rem 0.85rem;
    color: #fff;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
}

.feed-card__menu a:hover {
    background: rgba(255, 255, 255, 0.08);
}

.feed-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.35rem;
    padding-inline-end: 0.5rem;
}

.feed-card__profile {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.feed-card__avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 900;
    font-size: 1.1rem;
    overflow: hidden;
}

.feed-card__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.feed-card__author {
    display: grid;
    gap: 0.1rem;
}

.feed-card__name {
    font-weight: 900;
    color: #ffffff;
    font-size: 0.98rem;
}

.feed-card__name-row {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.feed-card__role {
    color: rgba(255, 255, 255, 0.8);
    font-weight: 700;
    font-size: 0.9rem;
}

.feed-card__time {
    color: rgba(255, 255, 255, 0.7);
    font-weight: 700;
    font-size: 0.85rem;
}

.feed-card__follow {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-weight: 800;
    font-size: 0.78rem;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.15s ease;
}

.feed-card__follow:hover {
    background: rgba(255, 255, 255, 0.24);
    transform: translateY(-1px);
}

.feed-card__follow-mobile {
    display: none;
}

.feed-card__detail-link,
.feed-card__back {
    position: absolute;
    inset-inline-start: 0.65rem;
    inset-block-start: 0.65rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 0.88rem;
    font-weight: 800;
    transition: transform 0.1s ease, background 0.15s ease;
    z-index: 2;
}

.feed-card__back {
    gap: 0.25rem;
}

.feed-card__detail-link {
    display: none !important;
}

.feed-card__detail-link:hover,
.feed-card__back:hover {
    background: rgba(255, 255, 255, 0.22);
    transform: translateY(-1px);
}

.feed-card__text {
    display: grid;
    gap: 0.3rem;
}

.feed-card__media {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: #000;
    z-index: 0;
}

.feed-card__media img,
.feed-card__media video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.feed-card__embed {
    position: absolute;
    inset: 0;
    border: none;
    background: #3b1a1f;
}

.feed-card__overlay {
    position: absolute;
    inset: 0;
    z-index: 10;
    background: linear-gradient(180deg, 
        rgba(0,0,0,0.3) 0%, 
        rgba(0,0,0,0) 20%, 
        rgba(0,0,0,0) 60%, 
        rgba(0,0,0,0.8) 100%);
    pointer-events: none;
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-rows: 1fr auto;
    padding: 1rem;
    padding-bottom: calc(var(--mobile-tab-bar-height, 72px) + 1rem);
}

.feed-card__overlay > * {
    pointer-events: auto;
}

.feed-card__embed iframe {
    width: 100%;
    height: 100%;
    border: 0;
}

.feed-card:hover .feed-card__media img,
.feed-card:hover .feed-card__media video {
    transform: scale(1.03);
}

.feed-card__play {
    position: absolute;
    inset-inline-start: 1rem;
    bottom: 6rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.7rem;
    background: rgba(0, 0, 0, 0.65);
    color: #fff;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.9rem;
    z-index: 2;
}

.feed-card__eyebrow {
    font-weight: 800;
    color: #0c8f78;
    font-size: 0.86rem;
    letter-spacing: 0.01em;
}

.feed-card__title {
    font-size: 1.05rem;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.feed-card__meta {
    display: none;
}

.feed-card__value {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.value-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.65rem;
    border-radius: 10px;
    background: rgba(15, 31, 61, 0.06);
    color: #321417;
    font-weight: 700;
    font-size: 0.9rem;
}

.value-chip i {
    color: #9f5e63;
}

.feed-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.35rem 0.6rem;
    border-radius: 10px;
    background: rgba(15, 31, 61, 0.05);
    color: #3b1a1f;
    font-weight: 700;
    font-size: 0.88rem;
}

.feed-pill i {
    color: #9f5e63;
}

.feed-card__info {
    grid-column: 1;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: flex-end;
    padding-inline-end: 1rem;
    pointer-events: auto;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.feed-card__actions {
    grid-column: 2;
    grid-row: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.2rem;
    justify-content: flex-end;
    padding-bottom: 0.5rem;
    pointer-events: auto;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    transition: transform 0.1s;
}

.action-btn:active {
    transform: scale(0.9);
}

.action-btn i {
    font-size: 1.6rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.action-btn span {
    font-size: 0.75rem;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

.feed-card__header {
    display: none; /* Hide default header, we'll use custom layout */
}

.feed-card__footer {
    display: none; /* Hide default footer */
}

.feed-card__social {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.9rem;
}

.social-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.4rem;
    border-radius: 10px;
    background: rgba(0, 0, 0, 0.45);
    color: #fff;
    font-weight: 700;
    font-size: 0.82rem;
}

.feed-card__cta {
    display: none;
}

.cta-primary {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.52rem 0.68rem;
    border-radius: 9px;
    background: linear-gradient(120deg, #d7dde8, #c2ccd9);
    color: #42526b;
    font-weight: 700;
    font-size: 0.86rem;
    transition: transform 0.1s ease, box-shadow 0.15s ease;
    border: none;
}

.cta-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 18px rgba(15, 31, 61, 0.12);
}

.cta-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.75rem 0.95rem;
    border-radius: 12px;
    background: rgba(143, 74, 80, 0.12);
    color: #0c8f78;
    font-weight: 800;
    border: 1px solid rgba(143, 74, 80, 0.25);
    transition: background 0.15s ease;
}

.cta-secondary:hover {
    background: rgba(143, 74, 80, 0.18);
}

.feed-empty {
    grid-column: 1 / -1;
    text-align: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px dashed rgba(255, 255, 255, 0.25);
    border-radius: 18px;
    padding: 2.4rem 1.4rem;
}

.feed-empty h3 {
    font-size: 1.4rem;
    font-weight: 900;
    color: #ffffff;
    margin-bottom: 0.6rem;
}

.feed-empty p {
    color: rgba(255, 255, 255, 0.82);
    margin-bottom: 1rem;
}

.feed-loader,
.feed-end {
    text-align: center;
    margin-top: 1.4rem;
    color: rgba(255, 255, 255, 0.82);
    font-weight: 700;
}

.spinner {
    width: 42px;
    height: 42px;
    border: 4px solid rgba(255, 255, 255, 0.14);
    border-top-color: #9f5e63;
    border-radius: 50%;
    margin: 0 auto 0.4rem;
    animation: spin 0.9s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (min-width: 1024px) {
    .feed-page {
        background: radial-gradient(circle at 10% 20%, rgba(143, 74, 80, 0.08), transparent 30%),
            radial-gradient(circle at 88% 16%, rgba(247, 201, 72, 0.08), transparent 36%),
            radial-gradient(circle at 65% 78%, rgba(12, 143, 120, 0.06), transparent 32%),
            #030712;
        padding-block: calc(1.6rem + var(--navbar-safe-offset) + var(--feed-top-gap)) 2.2rem;
    }

    .feed-stream {
        display: flex;
        justify-content: center;
        padding-inline: 1.25rem;
    }

    .feed-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .feed-grid {
        display: grid;
        gap: 1.5rem;
        align-items: center;
    }

    .feed-card {
        height: calc(100vh - 140px);
        max-width: 940px;
        margin: 0 auto;
        border-radius: 26px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow:
            0 24px 70px rgba(0, 0, 0, 0.45),
            0 0 0 1px rgba(255, 255, 255, 0.02);
        background: #050910;
    }

    .feed-card__overlay {
        padding: 1.8rem 2rem;
        grid-template-columns: minmax(0, 1fr) 96px;
        align-items: end;
        column-gap: 1.5rem;
    }

    .feed-card__info {
        gap: 0.8rem;
        max-width: 640px;
    }

    .feed-card__title {
        font-size: 1.32rem;
    }

    .feed-card__excerpt {
        font-size: 1rem;
    }

    .feed-card__actions {
        gap: 1.05rem;
        padding: 1rem 0.65rem 1.1rem;
        border-radius: 20px;
        background: rgba(0, 0, 0, 0.36);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(10px);
        grid-row: 1 / 3;
        align-self: center;
        justify-content: center;
    }

    .feed-card__more,
    .feed-card__detail-link,
    .feed-card__back {
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, 0.14);
    }
}

@media (min-width: 1280px) {
    .feed-card {
        height: calc(100vh - 170px);
        max-width: 1040px;
    }

    .feed-card__title {
        font-size: 1.45rem;
    }
}

@media (max-width: 768px) {
    .feed-page {
        --feed-top-gap: 0px;

        padding-top: var(--feed-top-gap);
        scroll-padding-top: var(--feed-top-gap);
        padding-bottom: 0;
        background: #000;
    }

    .feed-shell {
        max-width: 100%;
    }

    .feed-stream {
        padding: 0;
    }

    .feed-grid {
        width: 100%;
        display: block;
    }

    .feed-card {
        width: 100%;
        height: 100vh;
        height: 100dvh;
        margin: 0;
        border-radius: 0;
    }
}

@media (max-width: 540px) {
    .post-composer {
        padding: 0.85rem 0.9rem;
    }

    .feed-grid {
        gap: 0;
    }
}

    .feed-card__name {
        font-size: 0.94rem;
    }

    .feed-card__follow {
        display: none;
    }

    .feed-card__follow-mobile {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
        margin-inline-start: 0.1rem;
        border-radius: 8px;
        background: #e6f7f3;
        color: #0c8f78;
        border: 1px solid rgba(143, 74, 80, 0.25);
        font-weight: 800;
        font-size: 0.8rem;
    }

    .feed-card__cta {
        flex-direction: column;
        align-items: stretch;
    }

    .feed-card__social {
        gap: 0.35rem;
    }

    .social-chip {
        font-size: 0.78rem;
        padding: 0.28rem 0.45rem;
    }

    .composer-modal {
        padding: 0.8rem;
        align-items: flex-end;
    }

    .composer-modal__dialog {
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 14px;
    }

    .composer-modal__header,
    .composer-modal__body {
        padding: 1rem 1rem 0.9rem;
    }

    .composer-modal__footer {
        padding: 0 1rem 1rem;
    }
}

/* Modern glass refresh */
:root {
    --feed-glass-surface: linear-gradient(145deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.07));
    --feed-glass-border: rgba(255, 255, 255, 0.2);
    --feed-glass-shadow: 0 26px 62px rgba(2, 6, 23, 0.5);
}

.feed-page-modern {
    background:
        radial-gradient(circle at 12% 14%, rgba(143, 74, 80, 0.14), transparent 30%),
        radial-gradient(circle at 88% 9%, rgba(124, 58, 237, 0.12), transparent 36%),
        radial-gradient(circle at 52% 86%, rgba(15, 118, 110, 0.08), transparent 30%),
        #020617;
}

.feed-shell-modern {
    position: relative;
}

.feed-shell-modern::before {
    content: "";
    position: absolute;
    inset: -8% -3%;
    border-radius: 2rem;
    pointer-events: none;
    background: radial-gradient(circle at 18% 24%, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
    z-index: 0;
}

.feed-card-modern {
    position: relative;
    border: 1px solid var(--feed-glass-border);
    box-shadow: var(--feed-glass-shadow);
    backdrop-filter: blur(10px) saturate(130%);
    -webkit-backdrop-filter: blur(10px) saturate(130%);
}

.feed-card-modern::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(140deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0));
    z-index: 1;
}

.feed-card-modern .feed-card__overlay {
    background: linear-gradient(180deg, rgba(2, 6, 23, 0.24) 0%, rgba(2, 6, 23, 0) 22%, rgba(2, 6, 23, 0) 58%, rgba(2, 6, 23, 0.84) 100%);
}

.feed-card-modern .feed-card__info {
    background: linear-gradient(145deg, rgba(2, 6, 23, 0.5), rgba(15, 23, 42, 0.3));
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 1rem;
    padding: 0.9rem 1rem;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.feed-card-modern .feed-card__actions {
    background: linear-gradient(160deg, rgba(2, 6, 23, 0.52), rgba(15, 23, 42, 0.36));
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 1.1rem;
    padding: 0.7rem 0.45rem;
    box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.feed-card-modern .action-btn > div {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.24);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}

.feed-card-modern .action-btn:hover > div {
    background: rgba(255, 255, 255, 0.24);
    border-color: rgba(255, 255, 255, 0.38);
    transform: translateY(-2px);
}

.feed-card-modern .action-btn span {
    color: rgba(255, 255, 255, 0.92);
}

.feed-card-modern .feed-card__more,
.feed-card-modern .feed-card__detail-link,
.feed-card-modern .feed-card__back {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.26);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.28);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.feed-card-modern .feed-card__more:hover,
.feed-card-modern .feed-card__detail-link:hover,
.feed-card-modern .feed-card__back:hover {
    background: rgba(255, 255, 255, 0.26);
}

.feed-loader,
.feed-end {
    width: fit-content;
    margin-inline: auto;
    padding: 0.65rem 0.95rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.26);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.feed-card-modern,
.feed-card-modern .feed-card__info,
.feed-card-modern .feed-card__actions {
    animation: feed-card-reveal 0.64s cubic-bezier(0.22, 0.61, 0.36, 1) both;
}

.feed-card-modern .feed-card__actions {
    animation-delay: 0.06s;
}

@keyframes feed-card-reveal {
    0% {
        opacity: 0;
        transform: translate3d(0, 14px, 0) scale(0.99);
    }
    100% {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
    }
}

@media (min-width: 1024px) {
    .feed-card-modern .feed-card__overlay {
        grid-template-columns: minmax(0, 1fr) 108px;
    }

    .feed-card-modern .feed-card__actions {
        padding: 0.9rem 0.55rem;
    }
}

@media (max-width: 768px) {
    .feed-card-modern {
        border: none;
        border-radius: 0;
        box-shadow: none;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .feed-card-modern::after {
        display: none;
    }

    .feed-card-modern .feed-card__overlay {
        padding: 0.8rem;
        padding-bottom: calc(var(--mobile-tab-bar-height, 72px) + 0.9rem);
    }

    .feed-card-modern .feed-card__info {
        border-radius: 0.9rem;
        padding: 0.7rem 0.8rem;
    }

    .feed-card-modern .feed-card__actions {
        border-radius: 0.9rem;
        padding: 0.55rem 0.35rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .feed-card-modern,
    .feed-card-modern .feed-card__info,
    .feed-card-modern .feed-card__actions {
        animation: none !important;
    }
}
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;

    if (!isset($skillTracks)) {
        $skillTracks = [
            'cooking' => [
                'label' => 'Cooking Skills',
                'subtitle' => 'Recipes · Quick meals · Kitchen hacks',
                'color' => '#3b1a1f',
                'accent' => '#9f5e63',
            ],
            'content' => [
                'label' => 'Content Creation',
                'subtitle' => 'Lighting · Editing · Camera skills',
                'color' => '#3b1a1f',
                'accent' => '#b2767d',
            ],
            'business' => [
                'label' => 'Business Skills',
                'subtitle' => 'Pricing · Branding · Selling',
                'color' => '#3b1a1f',
                'accent' => '#f7c948',
            ],
            'crafts' => [
                'label' => 'Crafts & DIY',
                'subtitle' => 'Home projects · Handmade items',
                'color' => '#3b1a1f',
                'accent' => '#e46f4d',
            ],
        ];
    }

    if (!isset($sourceLabels)) {
        $sourceLabels = [
            'creator' => ['label' => 'Creator', 'hint' => '???? ?????'],
            'partner' => ['label' => 'Partner', 'hint' => '????? ????'],
            'peah' => ['label' => 'Peah Official', 'hint' => '???? Peahskill'],
            'community' => ['label' => 'Community Post', 'hint' => '?? ????? Wasfah'],
        ];
    }

    if (!isset($classifyTrack)) {
        $classifyTrack = function ($recipe) {
            $categoryName = Str::of($recipe->category->name ?? '')->lower();
            $map = [
                'cooking' => ['cook', 'cooking', '?????', '????', 'recipe', 'kitchen', '???', '???', 'meal', '???'],
                'content' => ['?????', '?????', '?????', '?????', 'lighting', 'editing', '??????', '??????', 'content'],
                'business' => ['business', '???', '?????', 'branding', '?????', '???', 'pricing', '???', 'selling'],
                'crafts' => ['???', 'diy', '?????', '?????', 'handmade', 'craft', '????', '?????'],
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
    }

    if (!isset($resolveSource)) {
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
    }

    $latestUpdated = optional($recipes->first())->created_at
        ? optional($recipes->first())->created_at->locale(app()->getLocale())->diffForHumans(null, null, false, 2)
        : '—';
@endphp
<div class="feed-page feed-page-modern min-h-screen pb-16">
    <div class="feed-pattern" aria-hidden="true"></div>

    <section class="feed-stream feed-stream-modern px-4">
        <div
            id="recipe-feed"
            class="feed-shell feed-shell-modern"
            data-next-page="{{ $recipes->nextPageUrl() }}"
            data-track="all"
        >
            <div id="feed-grid" class="feed-grid">
                @php
                    // Fallbacks in case helpers are not defined
                    $skillTracks = $skillTracks ?? [
                        'cooking' => ['label' => 'Cooking Skills', 'accent' => '#9f5e63', 'color' => '#3b1a1f'],
                        'content' => ['label' => 'Content Creation', 'accent' => '#b2767d', 'color' => '#3b1a1f'],
                        'business' => ['label' => 'Business Skills', 'accent' => '#f7c948', 'color' => '#3b1a1f'],
                        'crafts' => ['label' => 'Crafts & DIY', 'accent' => '#e46f4d', 'color' => '#3b1a1f'],
                    ];
                    $sourceLabels = $sourceLabels ?? [
                        'creator' => ['label' => 'Creator', 'hint' => ''],
                        'partner' => ['label' => 'Partner', 'hint' => ''],
                        'peah' => ['label' => 'Peah Official', 'hint' => ''],
                        'community' => ['label' => 'Community Post', 'hint' => ''],
                    ];
                    $classifyTrack = $classifyTrack ?? fn($recipe) => 'cooking';
                    $resolveSource = $resolveSource ?? fn() => 'community';
                @endphp
                @forelse($recipes as $recipe)
                    @php
                        $imageSource = $recipe->image_url ?: \App\Support\BrandAssets::logoAsset('webp');

                        $avgRating = (float) ($recipe->interactions_avg_rating ?? $recipe->rating ?? 0);
                        $trackKey = $classifyTrack($recipe);
                        $sourceType = $resolveSource($recipe);
                        $track = $skillTracks[$trackKey];
                        $partnerCut = $recipe->chef?->referral_commission_rate;
                        $canInlineVideo = class_exists(\App\Support\VideoEmbed::class)
                            ? \App\Support\VideoEmbed::inlinePlayable($recipe->video_url)
                            : false;
                        $embedUrl = class_exists(\App\Support\VideoEmbed::class)
                            ? \App\Support\VideoEmbed::embedUrl($recipe->video_url)
                            : null;
                        $embedProvider = class_exists(\App\Support\VideoEmbed::class)
                            ? \App\Support\VideoEmbed::provider($recipe->video_url)
                            : null;
                        $chefName = $recipe->chef->name ?? '??? Wasfah';
                        $categoryName = $recipe->category->name ?? __('recipes.cards.category_fallback');
                        $publishedAgo = optional($recipe->created_at)->locale(app()->getLocale())->diffForHumans(null, null, false, 1);
                        $avatarUrl = optional($recipe->chef)->avatar ?? optional($recipe->chef)->profile_photo_url ?? null;
                        $avatarInitial = Str::substr($chefName, 0, 1);
                        $madeCount = (int) ($recipe->made_count ?? 0);
                        $savedCount = (int) ($recipe->saved_count ?? 0);
                        $commentCount = (int) ($recipe->comments_count ?? 0);
                    @endphp
                    <article
                        id="feed-card-{{ $recipe->slug }}"
                        class="feed-card feed-card-modern"
                        data-track="{{ $trackKey }}"
                        data-source="{{ $sourceType }}"
                        data-recipe-id="{{ $recipe->recipe_id }}"
                        data-video-url="{{ $recipe->video_url }}"
                        data-recipe-slug="{{ $recipe->slug }}"
                        style="--track-bg: {{ $track['accent'] }};"
                    >
                        <a href="{{ route('recipe.show', $recipe->slug) }}" class="feed-card__detail-link" aria-label="???? ????????">
                            <i class="fas fa-arrow-up-right"></i>
                        </a>
                        <button
                            type="button"
                            class="feed-card__back"
                            data-feed-action="go-back"
                            data-back-url="{{ route('home') }}"
                            aria-label="????"
                        >
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="feed-card__media">
                            @if($canInlineVideo)
                                <video src="{{ $recipe->video_url }}" autoplay muted loop playsinline controlslist="nodownload" poster="{{ $imageSource }}"></video>
                            @elseif($embedUrl)
                                <div class="feed-card__embed">
                                    <iframe
                                        src="{{ $embedUrl }}"
                                        @if($embedProvider) data-embed-provider="{{ $embedProvider }}" @endif
                                        title="Embedded media"
                                        loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture"
                                        allowfullscreen
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>
                                </div>
                            @else
                                <img
                                    src="{{ $imageSource }}"
                                    alt="{{ $recipe->title }}"
                                    loading="lazy"
                                    onerror="this.src='{{ \App\Support\BrandAssets::logoAsset('webp') }}';"
                                >
                                @if($recipe->video_url)
                                    <span class="feed-card__play">
                                        <i class="fas fa-play"></i>
                                        ????? ????
                                    </span>
                                @endif
                            @endif

                            <div class="feed-card__overlay">
                                <div class="feed-card__info">
                                    <h3 class="feed-card__title text-white font-black text-lg leading-tight mb-1 drop-shadow-md">{{ Str::words($recipe->title, 6, '...') }}</h3>
                                    
                                </div>

                                    <div class="feed-card__actions">
                                    <button
                                        class="action-btn group"
                                        type="button"
                                        data-feed-action="like"
                                        data-recipe-id="{{ $recipe->recipe_id }}"
                                        data-made="{{ $recipe->is_made ? 'true' : 'false' }}"
                                        data-count="{{ $madeCount }}"
                                    >
                                        <div class="p-2 rounded-full bg-black/20 group-hover:bg-black/40 transition-colors">
                                            <i class="fas fa-heart text-2xl {{ $recipe->is_made ? 'text-red-500' : 'text-white' }} group-hover:text-red-500 transition-colors"></i>
                                        </div>
                                        <span data-count-value>{{ number_format($madeCount) }}</span>
                                    </button>

                                    <button
                                        class="action-btn group"
                                        type="button"
                                        data-feed-action="save"
                                        data-recipe-id="{{ $recipe->recipe_id }}"
                                        data-saved="{{ $recipe->is_saved ? 'true' : 'false' }}"
                                        data-count="{{ $savedCount }}"
                                    >
                                        <div class="p-2 rounded-full bg-black/20 group-hover:bg-black/40 transition-colors">
                                            <i class="fas fa-bookmark text-2xl text-white {{ $recipe->is_saved ? 'text-yellow-400' : '' }}"></i>
                                        </div>
                                        <span data-count-value>{{ number_format($savedCount) }}</span>
                                    </button>

                                    <button
                                        class="action-btn group"
                                        type="button"
                                        data-feed-action="share"
                                        data-share-url="{{ route('recipe.show', $recipe->slug) }}"
                                        data-share-title="{{ $recipe->title }}"
                                    >
                                        <div class="p-2 rounded-full bg-black/20 group-hover:bg-black/40 transition-colors">
                                            <i class="fas fa-share text-2xl text-white"></i>
                                        </div>
                                        <span>??????</span>
                                    </button>

                                    <button
                                        class="action-btn group"
                                        type="button"
                                        data-feed-action="toggle-audio"
                                        data-muted="true"
                                    >
                                        <div class="p-2 rounded-full bg-black/20 group-hover:bg-black/40 transition-colors">
                                            <i class="fas fa-volume-mute text-2xl text-white"></i>
                                        </div>
                                        <span data-sound-label>???? ???</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="feed-empty">
                        <h3>{{ __('recipes.empty.title') }}</h3>
                        <p>{{ __('recipes.empty.subtitle') }}</p>
                        <a href="{{ route('recipes') }}" class="cta-secondary">
                            <i class="fas fa-rotate-right"></i>
                            ????? ??????
                        </a>
                    </div>
                @endforelse
            </div>

            <div id="feed-loader" class="feed-loader {{ $recipes->hasMorePages() ? '' : 'hidden' }}">
                <div class="spinner"></div>
                ???? ??? ??????...
            </div>
            <div id="feed-end" class="feed-end {{ $recipes->hasMorePages() ? 'hidden' : '' }}">
                <i class="fas fa-check-circle text-emerald-500"></i>
                ????? ???? ???????? ???????
            </div>
            <div id="feed-sentinel" style="height: 1px;"></div>

            <button id="autoClick" type="button" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden;border:0;padding:0;clip:rect(0,0,0,0);">
                play
            </button>

            <noscript>
                <div class="mt-6">
                    {{ $recipes->links('pagination.custom') }}
                </div>
            </noscript>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('feed-page-active');

    const feedShell = document.getElementById('recipe-feed');
    const grid = document.getElementById('feed-grid');
    const loader = document.getElementById('feed-loader');
    const endState = document.getElementById('feed-end');
    const sentinel = document.getElementById('feed-sentinel');
    const trackButtons = document.querySelectorAll('[data-track-filter]');
    const feedPageEl = document.querySelector('.feed-page');
    const navbarLayer = document.querySelector('[data-navbar-layer]');
    const mobileTabBar = document.querySelector('[data-mobile-tab-bar]');
    const requestFrame = window.requestAnimationFrame ?? ((cb) => window.setTimeout(cb, 16));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const currentUserId = document.body.dataset.userId || document.querySelector('[data-user-id]')?.dataset.userId || '';
    const autoClickButton = document.getElementById('autoClick');
    const titleCache = new Map();
    let activeMedia = null;
    let scrollTicking = false;
    let nextPage = feedShell?.dataset.nextPage || '';
    let activeTrack = 'all';
    let videoObserver;
    let embedObserver;
    let globalMuted = true;
    let firstMediaPlayed = false;
    let firstMediaAttempts = 0;
    let didSyntheticClick = false;
    const highlightSlug = (() => {
        const hash = (window.location.hash || '').replace('#', '');
        const params = new URLSearchParams(window.location.search);
        const candidateFromHash = hash.startsWith('feed-card-') ? hash.replace('feed-card-', '') : '';
        const candidate = candidateFromHash || params.get('highlight') || '';
        return /^[A-Za-z0-9-_]+$/.test(candidate) ? candidate : '';
    })();
    let highlightHandled = false;
    const feedTopGapPx = window.matchMedia('(max-width: 768px)').matches ? 0 : 24;

    const clampWords = (text = '', maxWords = 6) => {
        if (!text) return '';
        const words = text.trim().split(/\s+/);
        if (words.length <= maxWords) return text.trim();
        return `${words.slice(0, maxWords).join(' ')}...`;
    };

    const updateNavbarOffset = () => {
        const navHeight = navbarLayer?.getBoundingClientRect().height ?? 0;
        const value = Math.round(navHeight || 72);
        document.documentElement.style.setProperty('--navbar-safe-offset', `${value}px`);
        document.documentElement.style.setProperty('--feed-top-gap', `${feedTopGapPx}px`);
        feedPageEl?.style.setProperty('--navbar-safe-offset', `${value}px`);
        feedPageEl?.style.setProperty('--feed-top-gap', `${feedTopGapPx}px`);
    };
    updateNavbarOffset();
    window.addEventListener('resize', () => requestFrame(updateNavbarOffset));

    const updateTabBarHeight = () => {
        const measuredHeight = mobileTabBar?.getBoundingClientRect().height ?? 0;
        const tabHeight = measuredHeight > 0 ? measuredHeight : 0;
        const heightValue = `${Math.round(tabHeight)}px`;
        document.documentElement.style.setProperty('--mobile-tab-bar-height', heightValue);
        feedPageEl?.style.setProperty('--mobile-tab-bar-height', heightValue);
    };

    const getVisibleRatio = (el) => {
        const rect = el.getBoundingClientRect();
        const viewHeight = window.innerHeight || document.documentElement.clientHeight;
        const visibleHeight = Math.min(rect.bottom, viewHeight) - Math.max(rect.top, 0);
        return Math.max(0, visibleHeight) / Math.max(rect.height, 1);
    };

    const fetchYouTubeTitle = async (videoUrl) => {
        if (!videoUrl || !/youtu\.?be/.test(videoUrl)) return '';
        if (titleCache.has(videoUrl)) return titleCache.get(videoUrl);
        const endpoint = `https://www.youtube.com/oembed?format=json&url=${encodeURIComponent(videoUrl)}`;
        const response = await fetch(endpoint);
        if (!response.ok) throw new Error('Failed to load YouTube title');
        const data = await response.json();
        const title = data?.title || '';
        titleCache.set(videoUrl, title);
        return title;
    };

    const populateYouTubeTitles = () => {
        document.querySelectorAll('.feed-card').forEach(card => {
            const videoUrl = card.dataset.videoUrl;
            const titleEl = card.querySelector('.feed-card__title');
            if (!videoUrl || !titleEl || titleEl.dataset.autoFilled === 'true') return;
            if (!/youtu\.?be/.test(videoUrl)) return;
            fetchYouTubeTitle(videoUrl)
                .then(title => {
                    if (title) {
                        titleEl.textContent = clampWords(title, 6);
                        titleEl.dataset.autoFilled = 'true';
                    }
                })
                .catch(() => {});
        });
    };

    const pauseInlineVideo = (video) => {
        if (!video) return;
        video.pause();
    };

    const applyVideoMuteState = (video) => {
        if (!video) return;
        const shouldMute = video.dataset.userMuted !== 'false';
        video.muted = shouldMute;
        if (shouldMute) {
            video.setAttribute('muted', '');
        } else {
            video.removeAttribute('muted');
        }
    };

    const playInlineVideo = (video) => {
        if (!video) return;
        video.playsInline = true;
        video.loop = true;
        video.autoplay = true;
        video.setAttribute('playsinline', '');
        if (video.dataset.userMuted === undefined) {
            video.dataset.userMuted = 'true';
        }
        applyVideoMuteState(video);
        const playPromise = video.play();
        if (playPromise?.catch) {
            playPromise.catch(() => {});
        }
    };

    const getFeedVideos = () => Array.from(document.querySelectorAll('.feed-card__media video'));
    const getYouTubeEmbeds = () => Array.from(document.querySelectorAll('iframe[data-embed-provider="youtube"]'));

    const sendYouTubeCommand = (iframe, command) => {
        if (!iframe?.contentWindow) return;
        try {
            iframe.contentWindow.postMessage(JSON.stringify({
                event: 'command',
                func: command,
                args: []
            }), '*');
        } catch (error) {
            console.warn('YouTube command failed', error);
        }
    };

    const applyEmbedMuteState = (iframe) => {
        if (!iframe) return;
        const shouldMute = iframe.dataset.userMuted !== 'false';
        sendYouTubeCommand(iframe, shouldMute ? 'mute' : 'unMute');
    };

    const pauseYouTubeEmbed = (iframe) => sendYouTubeCommand(iframe, 'pauseVideo');
    const playYouTubeEmbed = (iframe) => sendYouTubeCommand(iframe, 'playVideo');

    const getMediaCandidates = () => ([
        ...getFeedVideos().map(video => ({ el: video, type: 'inline' })),
        ...getYouTubeEmbeds().map(iframe => ({ el: iframe, type: 'youtube' })),
    ]);

    const pauseMedia = (candidate) => {
        if (!candidate?.el) return;
        if (candidate.type === 'youtube') {
            pauseYouTubeEmbed(candidate.el);
        } else {
            pauseInlineVideo(candidate.el);
        }
    };

    const playMedia = (candidate) => {
        if (!candidate?.el) return;
        if (candidate.type === 'youtube') {
            applyEmbedMuteState(candidate.el);
            playYouTubeEmbed(candidate.el);
        } else {
            playInlineVideo(candidate.el);
        }
    };

    const ensureFirstMediaPlays = () => {
        if (firstMediaPlayed || activeMedia) return;
        const candidates = getMediaCandidates();
        if (!candidates.length) return;

        const visibleCandidate = candidates.find(c => getVisibleRatio(c.el) > 0.05) || candidates[0];
        if (visibleCandidate) {
            playMedia(visibleCandidate);
            activeMedia = visibleCandidate.el;
            firstMediaPlayed = true;
        }
    };

    const tryPlayFirstMedia = () => {
        if (firstMediaPlayed) return;
        ensureFirstMediaPlays();
        if (!firstMediaPlayed && firstMediaAttempts < 6) {
            firstMediaAttempts += 1;
            setTimeout(tryPlayFirstMedia, 200 * firstMediaAttempts);
        }
    };

    const fireSyntheticClick = () => {
        if (didSyntheticClick) return;
        didSyntheticClick = true;
        const targets = [autoClickButton, feedShell, document.documentElement, document.body].filter(Boolean);
        const mouseInit = { bubbles: true, cancelable: true, view: window };
        const pointerInit = { bubbles: true, cancelable: true, pointerId: 1, pointerType: 'touch' };

        targets.forEach(target => {
            try { target.click(); } catch (_) {}
            ['pointerdown', 'pointerup'].forEach(type => {
                try { target.dispatchEvent(new PointerEvent(type, pointerInit)); } catch (_) {}
            });
            ['mousedown', 'mouseup', 'click'].forEach(type => {
                try { target.dispatchEvent(new MouseEvent(type, mouseInit)); } catch (_) {}
            });
        });
    };

    const syncActiveMedia = () => {
        const candidates = getMediaCandidates();
        if (!candidates.length) return;

        const viewportCenter = window.innerHeight / 2;
        let closest = null;
        let minDistance = Infinity;

        candidates.forEach((candidate) => {
            const visibility = getVisibleRatio(candidate.el);
            if (visibility <= 0.05) {
                pauseMedia(candidate);
                return;
            }

            const rect = candidate.el.getBoundingClientRect();
            const mediaCenter = rect.top + (rect.height / 2);
            const distance = Math.abs(mediaCenter - viewportCenter);

            if (distance < minDistance) {
                minDistance = distance;
                closest = candidate;
            }
        });

        if (!closest) {
            activeMedia = null;
            return;
        }

        activeMedia = closest.el;
        candidates.forEach((candidate) => {
            if (candidate.el === activeMedia) {
                playMedia(candidate);
            } else {
                pauseMedia(candidate);
            }
        });

        firstMediaPlayed = true;
    };

    const handleScroll = () => {
        if (scrollTicking) return;
        scrollTicking = true;
        requestFrame(() => {
            syncActiveMedia();
            scrollTicking = false;
        });
    };

    updateTabBarHeight();

    if (mobileTabBar && 'ResizeObserver' in window) {
        const tabBarObserver = new ResizeObserver(() => updateTabBarHeight());
        tabBarObserver.observe(mobileTabBar);
    }

    window.addEventListener('resize', () => {
        updateTabBarHeight();
        syncActiveMedia();
    });

    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('orientationchange', () => {
        updateTabBarHeight();
        setTimeout(() => syncActiveMedia(), 120);
    });

    const maybeHighlightCard = () => {
        if (highlightHandled || !highlightSlug) return;
        const targetCard = document.getElementById(`feed-card-${highlightSlug}`);
        if (!targetCard) return;
        highlightHandled = true;
        targetCard.classList.add('is-highlighted');
        targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(() => targetCard.classList.remove('is-highlighted'), 2200);
    };

    const applyTrackFilter = () => {
        const cards = grid.querySelectorAll('.feed-card');
        cards.forEach(card => {
            const track = card.dataset.track;
            card.style.display = (activeTrack === 'all' || track === activeTrack) ? 'flex' : 'none';
        });
    };

    if (trackButtons.length) {
        trackButtons.forEach(button => {
            button.addEventListener('click', () => {
                trackButtons.forEach(btn => btn.classList.remove('is-active'));
                button.classList.add('is-active');
                activeTrack = button.dataset.trackFilter;
                feedShell.dataset.track = activeTrack;
                applyTrackFilter();
            });
        });
    }

    const supportsIO = 'IntersectionObserver' in window;
    let manualLoadTicking = false;

    const manualLoadCheck = () => {
        manualLoadTicking = false;
        if (!sentinel) return;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
        const docHeight = Math.max(
            document.documentElement.scrollHeight,
            document.body.scrollHeight,
            document.documentElement.offsetHeight,
            document.body.offsetHeight
        );
        const distanceFromBottom = docHeight - (scrollTop + viewportHeight);
        if (distanceFromBottom < 360) {
            loadMore();
        }
    };

    if (supportsIO && sentinel) {
        const observer = new IntersectionObserver(async (entries) => {
            if (entries.some(entry => entry.isIntersecting)) {
                await loadMore();
            }
        }, { rootMargin: '320px 0px' });
        observer.observe(sentinel);
    } else if (sentinel) {
        const handleManualScroll = () => {
            if (manualLoadTicking) return;
            manualLoadTicking = true;
            requestFrame(manualLoadCheck);
        };
        window.addEventListener('scroll', handleManualScroll, { passive: true });
        window.addEventListener('resize', handleManualScroll, { passive: true });
        handleManualScroll();
    }

    const modals = document.querySelectorAll('.composer-modal');
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloseButtons = document.querySelectorAll('[data-modal-close]');

    const closeModal = () => {
        modals.forEach(modal => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        });
        document.body.classList.remove('is-modal-open');
    };

    const openModal = (modalKey) => {
        const targetModal = Array.from(modals).find(modal => modal.dataset.modal === modalKey);
        if (!targetModal) return;
        closeModal();
        targetModal.classList.add('is-open');
        targetModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('is-modal-open');
    };

    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const target = trigger.dataset.modalTarget;
            if (target) {
                openModal(target);
            }
        });
    });

    modalCloseButtons.forEach(button => {
        button.addEventListener('click', () => closeModal());
    });

    modals.forEach(modal => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    document.querySelectorAll('.composer-form').forEach(form => {
        form.addEventListener('submit', (event) => event.preventDefault());
    });

    let composerToast;
    const showToast = (message, tone = 'success') => {
        if (composerToast) {
            composerToast.remove();
        }
        composerToast = document.createElement('div');
        composerToast.className = `composer-toast ${tone === 'error' ? 'composer-toast--error' : ''}`;
        composerToast.textContent = message;
        document.body.appendChild(composerToast);
        requestAnimationFrame(() => composerToast.classList.add('is-visible'));
        setTimeout(() => {
            composerToast?.classList.remove('is-visible');
            setTimeout(() => composerToast?.remove(), 180);
        }, 2600);
    };

    const quickPostEndpoint = '{{ route("feed.quick-post") }}';
    const formatCount = (value) => Number(value || 0).toLocaleString('en-US');
    const updateActionCount = (button, value) => {
        const safeValue = Math.max(0, Number.isFinite(value) ? value : 0);
        button.dataset.count = safeValue.toString();
        const countEl = button.querySelector('[data-count-value]');
        if (countEl) {
            countEl.textContent = formatCount(safeValue);
        }
    };

    const toggleIconState = (button, isActive, activeClass) => {
        const icon = button.querySelector('i');
        if (!icon) return;
        if (activeClass) {
            icon.classList.toggle(activeClass, isActive);
        }
        if (!activeClass || activeClass.startsWith('text-')) {
            icon.classList.toggle('text-white', !isActive);
        }
    };

    const setSoundButtonState = (button, isMuted) => {
        if (!button) return;
        const icon = button.querySelector('i');
        const label = button.querySelector('[data-sound-label]');
        button.dataset.muted = isMuted ? 'true' : 'false';
        if (icon) {
            icon.classList.toggle('fa-volume-mute', isMuted);
            icon.classList.toggle('fa-volume-up', !isMuted);
        }
        if (label) {
            label.textContent = isMuted ? '???? ???' : '???';
        }
    };

    const applyGlobalMuteState = (isMuted, targetMedia = null) => {
        globalMuted = isMuted;
        document.querySelectorAll('[data-feed-action="toggle-audio"]').forEach(btn => {
            setSoundButtonState(btn, globalMuted);
        });

        getFeedVideos().forEach(video => {
            video.dataset.userMuted = globalMuted ? 'true' : 'false';
            applyVideoMuteState(video);
        });

        getYouTubeEmbeds().forEach(iframe => {
            iframe.dataset.userMuted = globalMuted ? 'true' : 'false';
            applyEmbedMuteState(iframe);
        });

        if (!globalMuted && targetMedia) {
            getMediaCandidates().forEach(candidate => {
                if (candidate.el !== targetMedia) {
                    pauseMedia(candidate);
                }
            });
            playMedia({ el: targetMedia, type: targetMedia.tagName === 'IFRAME' ? 'youtube' : 'inline' });
            activeMedia = targetMedia;
        }
    };

    const toggleAudioForCard = (button) => {
        const card = button.closest('.feed-card');
        if (!card) return;

        const video = card.querySelector('video');
        const youtube = card.querySelector('iframe[data-embed-provider="youtube"]');
        const target = video || youtube;

        if (!target) {
            showToast('?? ???? ????? ?? ??? ??????', 'error');
            return;
        }

        const currentlyMuted = button.dataset.muted !== 'false';
        const nextMuted = !currentlyMuted;

        applyGlobalMuteState(nextMuted, target);
    };

    const requireAuth = () => {
        if (currentUserId && currentUserId !== 'null') {
            return true;
        }
        showToast('??? ????? ?????? ?????', 'error');
        window.location.href = '/login';
        return false;
    };

    const toggleMadeAction = async (button) => {
        const recipeId = parseInt(button.dataset.recipeId || '0', 10);
        if (!recipeId || !requireAuth()) return;

        const wasMade = button.dataset.made === 'true';
        const baseCount = parseInt(button.dataset.count || '0', 10) || 0;
        const optimisticCount = Math.max(0, baseCount + (wasMade ? -1 : 1));

        updateActionCount(button, optimisticCount);
        button.dataset.made = (!wasMade).toString();
        toggleIconState(button, !wasMade, 'text-red-500');

        try {
            await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
            const res = await fetch('/api/interactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'include',
                body: JSON.stringify({
                    recipe_id: recipeId,
                    is_made: !wasMade,
                }),
            });

            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                if (res.status === 419) {
                    window.location.reload();
                    return;
                }
                throw new Error('Failed to update made state');
            }

            const data = await res.json();
            const finalState = typeof data.is_made !== 'undefined' ? Boolean(data.is_made) : !wasMade;
            const finalCount = finalState === !wasMade
                ? optimisticCount
                : Math.max(0, baseCount + (finalState ? 1 : -1));

            button.dataset.made = finalState.toString();
            toggleIconState(button, finalState, 'text-red-500');
            updateActionCount(button, finalCount);
        } catch (error) {
            console.error(error);
            button.dataset.made = wasMade.toString();
            toggleIconState(button, wasMade, 'text-red-500');
            updateActionCount(button, baseCount);
            showToast('???? ????? ??????? ????', 'error');
        }
    };

    const toggleSaveAction = async (button) => {
        const recipeId = parseInt(button.dataset.recipeId || '0', 10);
        if (!recipeId || !requireAuth()) return;

        const wasSaved = button.dataset.saved === 'true';
        const baseCount = parseInt(button.dataset.count || '0', 10) || 0;
        const optimisticCount = Math.max(0, baseCount + (wasSaved ? -1 : 1));

        updateActionCount(button, optimisticCount);
        button.dataset.saved = (!wasSaved).toString();
        toggleIconState(button, !wasSaved, 'text-yellow-400');

        try {
            await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
            const res = await fetch('/api/interactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'include',
                body: JSON.stringify({
                    recipe_id: recipeId,
                    is_saved: !wasSaved,
                }),
            });

            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                if (res.status === 419) {
                    window.location.reload();
                    return;
                }
                throw new Error('Failed to update save state');
            }

            const data = await res.json();
            const finalState = typeof data.is_saved !== 'undefined' ? Boolean(data.is_saved) : !wasSaved;
            const finalCount = finalState === !wasSaved
                ? optimisticCount
                : Math.max(0, baseCount + (finalState ? 1 : -1));

            button.dataset.saved = finalState.toString();
            toggleIconState(button, finalState, 'text-yellow-400');
            updateActionCount(button, finalCount);
            showToast(finalState ? '?? ??? ??????' : '?? ????? ?????', 'success');
        } catch (error) {
            console.error(error);
            button.dataset.saved = wasSaved.toString();
            toggleIconState(button, wasSaved, 'text-yellow-400');
            updateActionCount(button, baseCount);
            showToast('???? ????? ????? ?????', 'error');
        }
    };

    const handleShareAction = async (button) => {
        const shareUrl = button.dataset.shareUrl || window.location.href;
        const title = button.dataset.shareTitle || document.title;

        try {
            if (navigator.share) {
                await navigator.share({ title, url: shareUrl });
                return;
            }

            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(shareUrl);
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = shareUrl;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                tempInput.remove();
            }

            showToast('?? ??? ?????? ????????', 'success');
        } catch (error) {
            console.error(error);
            showToast('???? ?????? ?????? ????', 'error');
        }
    };

    const attachFeedActionButtons = () => {
        const soundButtons = document.querySelectorAll('[data-feed-action="toggle-audio"]');
        if (soundButtons.length) {
            const initialMuted = soundButtons[0].dataset.muted !== 'false';
            globalMuted = initialMuted;
        }

        document.querySelectorAll('[data-feed-action="like"]').forEach((button) => {
            if (button.dataset.actionBound === 'true') return;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                toggleMadeAction(button);
            }, { passive: false });
            button.dataset.actionBound = 'true';
        });

        document.querySelectorAll('[data-feed-action="save"]').forEach((button) => {
            if (button.dataset.actionBound === 'true') return;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                toggleSaveAction(button);
            }, { passive: false });
            button.dataset.actionBound = 'true';
        });

        document.querySelectorAll('[data-feed-action="comment"]').forEach((button) => {
            if (button.dataset.actionBound === 'true') return;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const targetUrl = button.dataset.commentUrl || button.closest('.feed-card')?.querySelector('.feed-card__detail-link')?.href;
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            }, { passive: false });
            button.dataset.actionBound = 'true';
        });

        document.querySelectorAll('[data-feed-action="share"]').forEach((button) => {
            if (button.dataset.actionBound === 'true') return;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                handleShareAction(button);
            }, { passive: false });
            button.dataset.actionBound = 'true';
        });

        document.querySelectorAll('[data-feed-action="go-back"]').forEach((button) => {
            if (button.dataset.actionBound === 'true') return;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const fallback = button.dataset.backUrl || '/';
                if (document.referrer && document.referrer !== window.location.href) {
                    window.history.back();
                    return;
                }
                window.location.href = fallback;
            }, { passive: false });
            button.dataset.actionBound = 'true';
        });

        document.querySelectorAll('[data-feed-action="toggle-audio"]').forEach((button) => {
            if (button.dataset.actionBound === 'true') return;
            // Sync button state with the current global mute preference
            setSoundButtonState(button, globalMuted);
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                toggleAudioForCard(button);
            }, { passive: false });
            button.dataset.actionBound = 'true';
        });
    };
    attachFeedActionButtons();

    const setButtonLoading = (button, isLoading, loadingText = '') => {
        if (!button) return;
        if (isLoading) {
            if (!button.dataset.originalText) {
                button.dataset.originalText = button.textContent;
            }
            button.disabled = true;
            button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${loadingText || button.dataset.originalText}`;
        } else {
            const originalText = button.dataset.originalText || button.textContent;
            button.disabled = false;
            button.textContent = originalText;
            button.dataset.originalText = '';
        }
    };

    const submitQuickPost = async ({ formData, button, successMessage }) => {
        setButtonLoading(button, true, '???? ?????...');

        try {
            const response = await fetch(quickPostEndpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                showToast(successMessage || '?? ????? ?????!');
                closeModal();
                return true;
            }

            if (response.status === 422 && data.errors) {
                const firstError = Object.values(data.errors)[0]?.[0] ?? '???? ?? ???????? ???????';
                showToast(firstError, 'error');
                console.error('Validation errors:', data.errors);
            } else {
                showToast(data.message || '??? ??? ????? ?????', 'error');
                if (data.errors) {
                    console.error(data.errors);
                }
            }
            return false;
        } catch (error) {
            console.error(error);
            showToast('??? ??? ?? ???????', 'error');
            return false;
        } finally {
            setButtonLoading(button, false);
        }
    };

    const scheduleFeedReload = () => {
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    };

    document.querySelectorAll('.composer-upload input[type="file"]').forEach(input => {
        const label = input.closest('.composer-upload')?.querySelector('.composer-upload__label');
        const updateLabel = () => {
            if (!label) return;
            const file = input.files?.[0];
            label.textContent = file ? file.name : (label.dataset.placeholder || '???? ?????');
            label.classList.toggle('has-file', !!file);
        };
        input.addEventListener('change', updateLabel);
    });

    const photoPublishButton = document.querySelector('[data-action="publish-photo"]');
    if (photoPublishButton) {
        photoPublishButton.addEventListener('click', async () => {
            const modal = photoPublishButton.closest('.composer-modal');
            const fileInput = modal?.querySelector('#photo-upload');
            const textInput = modal?.querySelector('#photo-text');
            
            if (!fileInput || !fileInput.files?.length) {
                showToast('??? ???? ?????', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('image', fileInput.files[0]);
            formData.append('description', textInput?.value || '');
            formData.append('type', 'photo');
            formData.append('_token', '{{ csrf_token() }}');

            const success = await submitQuickPost({
                formData,
                button: photoPublishButton,
                successMessage: '?? ??? ?????? ?????!'
            });

            if (success) {
                // Reset form
                fileInput.value = '';
                if (textInput) textInput.value = '';
                const label = fileInput.closest('.composer-upload')?.querySelector('.composer-upload__label');
                if (label) {
                    label.textContent = label.dataset.placeholder || '???? ?? ???? ????';
                    label.classList.remove('has-file');
                }

                scheduleFeedReload();
            }
        });
    }

    const articlePublishButton = document.querySelector('[data-action="publish-article"]');
    if (articlePublishButton) {
        articlePublishButton.addEventListener('click', async () => {
            const modal = articlePublishButton.closest('.composer-modal');
            const titleInput = modal?.querySelector('#article-title');
            const bodyInput = modal?.querySelector('#article-body');
            const imageInput = modal?.querySelector('#article-image');
            const videoInput = modal?.querySelector('#article-video');

            const description = bodyInput?.value?.trim() || '';
            if (!description) {
                showToast('???? ????? ?????? ?????', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('type', 'article');
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('title', titleInput?.value || '');
            formData.append('description', description);

            if (videoInput?.value) {
                formData.append('video_url', videoInput.value.trim());
            }

            if (imageInput?.files?.length) {
                formData.append('image', imageInput.files[0]);
            }

            const success = await submitQuickPost({
                formData,
                button: articlePublishButton,
                successMessage: '?? ??? ??????!'
            });

            if (success) {
                if (titleInput) titleInput.value = '';
                if (bodyInput) bodyInput.value = '';
                if (videoInput) videoInput.value = '';
                if (imageInput) {
                    imageInput.value = '';
                    const label = imageInput.closest('.composer-upload')?.querySelector('.composer-upload__label');
                    if (label) {
                        label.textContent = label.dataset.placeholder || '???? ?? ???? ????';
                        label.classList.remove('has-file');
                    }
                }

                scheduleFeedReload();
            }
        });
    }

    const videoPublishButton = document.querySelector('[data-action="publish-video"]');
    if (videoPublishButton) {
        videoPublishButton.addEventListener('click', async () => {
            const modal = videoPublishButton.closest('.composer-modal');
            const linkInput = modal?.querySelector('#video-link');
            const textInput = modal?.querySelector('#video-text');

            const link = linkInput?.value?.trim() || '';
            const description = textInput?.value?.trim() || '';

            if (!link) {
                showToast('??? ???? ??????? ?????', 'error');
                return;
            }

            if (!description) {
                showToast('??? ???? ????? ???????', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('type', 'video');
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('video_url', link);
            formData.append('description', description);
            formData.append('title', '');

            const success = await submitQuickPost({
                formData,
                button: videoPublishButton,
                successMessage: '?? ??? ???????!'
            });

            if (success) {
                if (linkInput) linkInput.value = '';
                if (textInput) textInput.value = '';
                scheduleFeedReload();
            }
        });
    }

    async function loadMore() {
        if (!nextPage || feedShell.dataset.loading === 'true') {
            return;
        }

        feedShell.dataset.loading = 'true';
        loader?.classList.remove('hidden');

        try {
            const response = await fetch(nextPage, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                throw new Error('Failed to load more recipes');
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('feed-grid');
            const newShell = doc.getElementById('recipe-feed');

            if (newGrid) {
                newGrid.querySelectorAll('.feed-card').forEach(card => {
                    grid.appendChild(card);
                });
            }
            attachFeedActionButtons();

            nextPage = newShell?.dataset.nextPage || '';
            feedShell.dataset.nextPage = nextPage;

            if (!nextPage) {
                endState?.classList.remove('hidden');
                sentinel?.classList.add('hidden');
            }

            applyTrackFilter();
            convertDriveImages();
            setupAutoplay();
            maybeHighlightCard();
        } catch (error) {
            console.error(error);
            if (loader) {
                loader.innerHTML = '<span class="text-red-500 font-semibold">???? ????? ??????</span>';
            }
        } finally {
            feedShell.dataset.loading = 'false';
            loader?.classList.add('hidden');
        }
    }

    function convertDriveImages() {
        const convertGoogleDriveUrl = (url) => {
            if (!url || !url.includes('drive.google.com')) {
                return url;
            }

            try {
                const directMatch = url.match(/\/file\/d\/([a-zA-Z0-9-_]+)/);
                if (directMatch && directMatch[1]) {
                    return `https://lh3.googleusercontent.com/d/${directMatch[1]}`;
                }

                const params = new URL(url).searchParams;
                const fileId = params.get('id');
                if (fileId) {
                    return `https://lh3.googleusercontent.com/d/${fileId}`;
                }

                const fallbackId = url.match(/[a-zA-Z0-9-_]{25,}/);
                if (fallbackId) {
                    return `https://lh3.googleusercontent.com/d/${fallbackId[0]}`;
                }
            } catch (error) {
                console.warn('Error converting Google Drive URL:', error);
            }

            return url;
        };

        document.querySelectorAll('img[src*="drive.google.com"]').forEach(img => {
            const converted = convertGoogleDriveUrl(img.src);
            if (converted !== img.src) {
                img.src = converted;
            }
        });
    }

    const setupAutoplay = () => {
        if (videoObserver) videoObserver.disconnect();
        if (embedObserver) embedObserver.disconnect();

        const videos = getFeedVideos();
        const youtubeEmbeds = getYouTubeEmbeds();
        
        // Initialize videos
        videos.forEach(video => {
            if (video.dataset.userMuted === undefined) {
                video.dataset.userMuted = 'true';
            }
            video.playsInline = true;
            video.loop = true;
            applyVideoMuteState(video);
        });

        // Prime YouTube embeds so API commands work reliably
        youtubeEmbeds.forEach(iframe => {
            if (iframe.dataset.userMuted === undefined) {
                iframe.dataset.userMuted = 'true';
            }
            applyEmbedMuteState(iframe);
            if (iframe.dataset.autoplayBound === 'true') return;
            iframe.addEventListener('load', () => {
                pauseYouTubeEmbed(iframe);
                tryPlayFirstMedia();
            }, { once: true });
            iframe.dataset.autoplayBound = 'true';
        });

        if ('IntersectionObserver' in window) {
            const options = { 
                threshold: [0, 0.15, 0.35, 0.55, 0.75, 1],
                rootMargin: '0px'
            };

            videoObserver = new IntersectionObserver(() => {
                syncActiveMedia();
            }, options);

            embedObserver = new IntersectionObserver(() => {
                syncActiveMedia();
            }, options);

            videos.forEach(video => videoObserver.observe(video));
            youtubeEmbeds.forEach(iframe => embedObserver.observe(iframe));
        } else {
            syncActiveMedia();
        }

        ensureFirstMediaPlays();

        syncActiveMedia();

        // Add click to toggle play/pause for inline videos
        videos.forEach(video => {
            // Use onclick to overwrite any existing listener without cloning/replacing nodes
            // This preserves the IntersectionObserver connection
            video.parentElement.onclick = () => {
                if (video.paused) {
                    getMediaCandidates().forEach(candidate => {
                        if (candidate.el !== video) pauseMedia(candidate);
                    });
                    activeMedia = video;
                    playInlineVideo(video);
                } else {
                    video.pause();
                }
            };
        });
    };

    convertDriveImages();
    applyTrackFilter();

    populateYouTubeTitles();
    
    // Initial setup
    setupAutoplay();
    tryPlayFirstMedia();

    if (autoClickButton) {
        autoClickButton.addEventListener('click', () => {
            tryPlayFirstMedia();
            fireSyntheticClick();
        });
        window.addEventListener('load', () => {
            autoClickButton.click();
            setTimeout(() => autoClickButton.click(), 120);
            setTimeout(() => autoClickButton.click(), 320);
        });
    } else {
        fireSyntheticClick();
        setTimeout(fireSyntheticClick, 120);
        setTimeout(fireSyntheticClick, 320);
    }
    
    // Force check on load to play the first video immediately
    setTimeout(() => {
        syncActiveMedia();
        tryPlayFirstMedia();
        fireSyntheticClick();
        
        // Mobile Safari hack
        const onTouch = () => {
            syncActiveMedia();
            tryPlayFirstMedia();
            document.removeEventListener('touchstart', onTouch);
            document.removeEventListener('click', onTouch);
        };
        document.addEventListener('touchstart', onTouch, { once: true });
        document.addEventListener('click', onTouch, { once: true });
    }, 100);
    maybeHighlightCard();
});
</script>
@endpush


