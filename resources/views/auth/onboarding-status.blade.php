@extends('layouts.app')

@section('title', 'Track your chef application')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-orange-100 py-12">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="bg-white rounded-3xl shadow-xl border border-orange-100 overflow-hidden">
            <div class="bg-gradient-to-l from-orange-500 to-orange-400 text-white px-8 py-10">
                <h1 class="text-3xl font-extrabold mb-3">
                    <i class="fas fa-bowl-food text-white ml-3"></i>
                    Your application is under review
                </h1>
                <p class="text-orange-100 text-lg leading-7">
                    Thanks for joining the Wasfah family! We are reviewing your details to verify your expertise before approving you as an official chef on the platform.
                </p>
            </div>

            <div class="px-8 py-10 space-y-8">
                <div class="bg-orange-50 border border-orange-200 rounded-2xl px-6 py-5 flex gap-4 items-start">
                    <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white text-orange-500 shadow">
                        <i class="fas fa-clock text-lg"></i>
                    </span>
                    <div>
                        <h2 class="font-semibold text-orange-700 text-lg mb-1">
                            Reviews usually take 24–72 hours
                        </h2>
                        <p class="text-sm text-orange-600 leading-6">
                            We verify your digital presence to keep the experience high-quality for every member. We will notify you by email and in-app alerts once the review is complete.
                        </p>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        What can you do now?
                    </h3>
                    <ul class="space-y-3 text-sm text-slate-600 leading-6">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle mt-1 text-emerald-500"></i>
                            <span>Make sure your contact info and social links are up to date.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-share-alt mt-1 text-orange-500"></i>
                            <span>Share new work or additional accounts with our support team to speed up the approval.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-bell mt-1 text-sky-500"></i>
                            <span>Keep an eye on your email and notifications and we will update you once we decide.</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-3">
                        Need help?
                    </h3>
                    <p class="text-sm text-slate-600 leading-6 mb-4">
                        We are happy to answer any question. Email us and we will reply as soon as possible.
                    </p>
                    <a href="mailto:support@wasfah.ae"
                       class="inline-flex items-center gap-2 rounded-xl border border-orange-200 px-5 py-3 text-orange-600 text-sm font-semibold transition hover:bg-orange-50">
                        <i class="fas fa-envelope"></i>
                        support@wasfah.ae
                    </a>
                </div>
            </div>

            <div class="bg-slate-50 border-t border-slate-200 px-8 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-slate-500">
                    You can return to the homepage and browse content while we review your application.
                </p>
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-3 text-white text-sm font-semibold transition hover:bg-slate-900">
                    <i class="fas fa-home"></i>
                    Back to homepage
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

