@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $avatarUrl = null;
    if ($user->avatar) {
        $avatarUrl = Str::startsWith($user->avatar, ['http://', 'https://'])
            ? $user->avatar
            : Storage::disk('public')->url($user->avatar);
    }
@endphp

<section class="bg-gradient-to-br from-orange-50 via-white to-white border border-orange-100 rounded-3xl shadow-sm p-6 md:p-8 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-32 h-32 bg-orange-200/30 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-44 h-44 bg-orange-100/40 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>

    <div class="relative flex flex-col md:flex-row items-center md:items-start gap-6">
        <div class="relative">
            <div class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-white text-4xl font-bold flex items-center justify-center shadow-xl ring-8 ring-white/60">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="صورة الملف الشخصي" class="w-full h-full rounded-full object-cover" loading="lazy">
                @else
                    {{ mb_substr($user->name, 0, 1) }}
                @endif
            </div>
        </div>

        <div class="flex-1 w-full">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                <div class="text-center md:text-right space-y-2">
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">{{ $user->name }}</h1>
                    <p class="text-gray-600 flex items-center justify-center md:justify-start gap-2">
                        <i class="fas fa-envelope text-orange-500"></i>
                        <span>{{ $user->email }}</span>
                    </p>
                    @if ($user->phone)
                        <p class="text-gray-600 flex items-center justify-center md:justify-start gap-2">
                            <i class="fas fa-phone-alt text-orange-500"></i>
                            <span>{{ $user->phone }}</span>
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap justify-center md:justify-end gap-3">
                    <a href="#profile-settings" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 text-white font-medium shadow-md transition-all hover:bg-orange-600 hover:shadow-lg">
                        <i class="fas fa-edit text-sm"></i>
                        <span>تعديل البيانات</span>
                    </a>
                    @if ($user->isChef())
                        <a href="{{ route('chef.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-orange-200 bg-white text-orange-600 font-medium shadow-sm transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700">
                            <i class="fas fa-tachometer-alt text-sm"></i>
                            <span>لوحة الشيف</span>
                        </a>
                        <a href="{{ route('chefs.show', ['chef' => $user->id]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 font-medium shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fas fa-external-link-alt text-sm"></i>
                            <span>صفحتي العامة</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

