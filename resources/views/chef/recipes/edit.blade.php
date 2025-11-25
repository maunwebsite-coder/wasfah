@extends('layouts.app')

@section('title', 'تعديل الوصفة - منطقة الشيف')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-wider text-orange-500 font-semibold mb-2">منطقة الشيف</p>
                <h1 class="text-3xl font-bold text-gray-900">تعديل الوصفة: {{ $recipe->title }}</h1>
                <p class="text-gray-600 mt-1">قم بتحديث معلومات الوصفة. أي تعديل جديد سيعيدها للمراجعة قبل النشر.</p>
            </div>
            <a href="{{ route('chef.recipes.index') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-300 hover:bg-white transition">
                <i class="fas fa-arrow-right"></i>
                العودة للوحة الوصفات
            </a>
        </div>

        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-blue-700 text-sm">
                <p class="font-semibold">حالة الوصفة الحالية:</p>
                @php
                    $statusLabels = [
                        \App\Models\Recipe::STATUS_DRAFT => 'مسودة',
                        \App\Models\Recipe::STATUS_PENDING => 'قيد المراجعة',
                        \App\Models\Recipe::STATUS_APPROVED => 'معتمدة',
                        \App\Models\Recipe::STATUS_REJECTED => 'مرفوضة',
                    ];
                @endphp
                <p class="mt-1">{{ $statusLabels[$recipe->status] ?? $recipe->status }}</p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-orange-700 text-sm">
                <p>بعد تعديل الوصفة وإرسالها للمراجعة، سيتم إعلامك فور موافقة الإدارة.</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm">
                <p>يمكنك حفظ التعديلات كمسودة للعودة إليها لاحقاً قبل إرسالها للمراجعة.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm leading-6">
                <p class="font-semibold mb-1">يرجى التحقق من الحقول التالية:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('chef.recipes.update', $recipe) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            @include('chef.recipes.form', ['recipe' => $recipe])

            <div class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <div class="text-sm text-gray-500">
                    <p>تاريخ آخر تعديل: {{ $recipe->updated_at?->locale('ar')->translatedFormat('d F Y - h:i a') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" data-submit-action="draft" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-3 text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-save"></i>
                        حفظ كمسودة
                    </button>
                    <button type="submit" data-submit-action="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3 text-white font-semibold shadow hover:from-orange-600 hover:to-orange-700 transition">
                        <i class="fas fa-paper-plane"></i>
                        إرسال للمراجعة
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

