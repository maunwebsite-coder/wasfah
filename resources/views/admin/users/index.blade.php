@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
    <div class="bg-slate-50 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white rounded-3xl border border-orange-100 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide mb-2">لوحة الإدارة</p>
                        <h1 class="text-3xl font-black text-slate-900 mb-2">جميع مستخدمي وصفة</h1>
                        <p class="text-slate-600 leading-relaxed max-w-2xl">
                            راقب نمو المجتمع، ابحث عن أي مستخدم، وتابع حالة الشيفات و الشركاء من مكان واحد.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-slate-600 uppercase">المجموع</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-orange-600 uppercase">الشيفات</p>
                            <p class="text-2xl font-black text-orange-600">{{ number_format($stats['chefs']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-emerald-600 uppercase">شركاء الإحالات</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format($stats['referrals']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right">
                            <p class="text-xs font-semibold text-slate-600 uppercase">الأدمن</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format($stats['admins']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">بحث</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="الاسم، البريد أو رقم الهاتف"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-orange-400 focus:ring-2 focus:ring-orange-200"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">الدور</label>
                        <select name="role" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            <option value="">الكل</option>
                            <option value="{{ \App\Models\User::ROLE_CUSTOMER }}" @selected($filters['role'] === \App\Models\User::ROLE_CUSTOMER)>مشترك</option>
                            <option value="{{ \App\Models\User::ROLE_CHEF }}" @selected($filters['role'] === \App\Models\User::ROLE_CHEF)>شيف</option>
                            <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected($filters['role'] === \App\Models\User::ROLE_ADMIN)>أدمن</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">حالة الشيف</label>
                        <select name="chef_status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            <option value="">الكل</option>
                            <option value="{{ \App\Models\User::CHEF_STATUS_NEEDS_PROFILE }}" @selected($filters['chef_status'] === \App\Models\User::CHEF_STATUS_NEEDS_PROFILE)>بحاجة بيانات</option>
                            <option value="{{ \App\Models\User::CHEF_STATUS_PENDING }}" @selected($filters['chef_status'] === \App\Models\User::CHEF_STATUS_PENDING)>قيد المراجعة</option>
                            <option value="{{ \App\Models\User::CHEF_STATUS_APPROVED }}" @selected($filters['chef_status'] === \App\Models\User::CHEF_STATUS_APPROVED)>معتمد</option>
                            <option value="{{ \App\Models\User::CHEF_STATUS_REJECTED }}" @selected($filters['chef_status'] === \App\Models\User::CHEF_STATUS_REJECTED)>مرفوض</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 mb-1 block">عدد العناصر بالصفحة</label>
                        <select name="per_page" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-200">
                            @foreach([20, 40, 60, 100] as $size)
                                <option value="{{ $size }}" @selected($filters['per_page'] == $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4 flex justify-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300">
                            تفريغ الفلاتر
                        </a>
                        <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            تطبيق الفلاتر
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">المستخدم</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">الدور</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">حالة الشيف</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">ورش نشطة</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">الحجوزات</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase">تاريخ الانضمام</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-900">{{ $user->name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                        <div class="flex items-center gap-2 mt-1 text-[11px]">
                                            @if($user->isReferralPartner())
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700 border border-emerald-100">شريك إحالة</span>
                                            @endif
                                            @if($user->instagram_url || $user->youtube_url)
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 font-semibold text-blue-600 border border-blue-100">حضور رقمي</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-700">
                                        {{ __('roles.' . $user->role, [], 'ar') ?? $user->role }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        @if($user->role === \App\Models\User::ROLE_CHEF)
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                                @class([
                                                    'bg-emerald-50 text-emerald-700 border border-emerald-200' => $user->chef_status === \App\Models\User::CHEF_STATUS_APPROVED,
                                                    'bg-amber-50 text-amber-700 border border-amber-200' => $user->chef_status === \App\Models\User::CHEF_STATUS_PENDING,
                                                    'bg-slate-50 text-slate-600 border border-slate-200' => $user->chef_status === \App\Models\User::CHEF_STATUS_NEEDS_PROFILE,
                                                    'bg-rose-50 text-rose-700 border border-rose-200' => $user->chef_status === \App\Models\User::CHEF_STATUS_REJECTED,
                                                ])">
                                                {{ __('chef.status.' . $user->chef_status, [], 'ar') ?? $user->chef_status }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-700">
                                        {{ number_format($user->workshops_count) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-700">
                                        {{ number_format($user->workshop_bookings_count) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ optional($user->created_at)->locale('ar')->translatedFormat('d F Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">
                                        لا توجد نتائج مطابقة للبحث الحالي.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    {{ $users->links() }}
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase">شيفات بانتظار الاعتماد</p>
                            <h2 class="text-xl font-bold text-slate-900">
                                {{ number_format($stats['pending_chefs']) }} طلب
                            </h2>
                        </div>
                        <a href="{{ route('admin.chefs.requests') }}" class="inline-flex items-center rounded-full border border-orange-200 px-3 py-1 text-xs font-semibold text-orange-600 hover:border-orange-300 hover:bg-orange-50">
                            إدارة الطلبات
                        </a>
                    </div>
                    <p class="text-sm text-slate-500">تابع ملفات الشيفات الجدد وإكمل مراجعة بياناتهم لضمان جودة المحتوى.</p>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase">أحدث المنضمين</p>
                            <h2 class="text-xl font-bold text-slate-900">آخر 6 مستخدمين</h2>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentUsers as $recent)
                            <div class="flex items-center justify-between rounded-2xl border border-slate-100 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $recent->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $recent->email }}</p>
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ optional($recent->created_at)->locale('ar')->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

