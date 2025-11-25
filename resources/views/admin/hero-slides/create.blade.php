@extends('layouts.app')

@section('title', 'إضافة شريحة هيرو جديدة')

@section('content')
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                    <div>
                        <p class="text-sm text-orange-500 font-semibold mb-1">سلايدر الصفحة الرئيسية</p>
                        <h1 class="text-3xl font-bold text-gray-900">إضافة شريحة جديدة</h1>
                        <p class="text-gray-500 mt-2">
                            خصص الرسائل والصور التي تظهر في أول الشاشة لزوار وصفة.
                        </p>
                    </div>
                    <a href="{{ route('admin.hero-slides.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold">
                        <i class="fas fa-arrow-right ml-2"></i>
                        عودة للقائمة
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.hero-slides.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @include('admin.hero-slides.form', ['heroSlide' => $heroSlide])

                    <div class="flex flex-col sm:flex-row sm:justify-end gap-4 pt-4">
                        <a href="{{ route('admin.hero-slides.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold">
                            إلغاء
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold shadow-lg shadow-orange-200">
                            حفظ الشريحة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

