@extends('layouts.app')

@section('title', 'Debug & Test Pages')

@push('styles')
<style>
    .debug-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .debug-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .debug-icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #6b2e30, #9f5e63);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Debug & Test Pages</h1>
        <p class="text-gray-600 text-lg max-w-3xl mx-auto">
            مجموعة من الصفحات المخصصة للاختبار والتطوير لضمان عمل الموقع بشكل صحيح
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Debug Recipe Page -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-bug text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Debug Recipe Page</h3>
                <p class="text-gray-600 mb-4">صفحة لاختبار عرض أدوات الوصفات وتشخيص المشاكل</p>
                <a href="{{ route('debug.recipe') }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Test Recipe JavaScript -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fab fa-js-square text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Test Recipe JavaScript</h3>
                <p class="text-gray-600 mb-4">اختبار ملفات JavaScript الخاصة بالوصفات</p>
                <a href="{{ route('test.recipe.js') }}" class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Test API -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-code text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Test API Response</h3>
                <p class="text-gray-600 mb-4">اختبار استجابة API للوصفات</p>
                <a href="{{ route('test.api') }}" class="inline-block bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Test Recipe Debug -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-search text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Test Recipe Debug</h3>
                <p class="text-gray-600 mb-4">تشخيص شامل لصفحات الوصفات</p>
                <a href="{{ route('test.recipe.debug') }}" class="inline-block bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Test Recipe Page -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Test Recipe Page</h3>
                <p class="text-gray-600 mb-4">اختبار صفحة الوصفة الفردية</p>
                <a href="{{ route('test.recipe.page') }}" class="inline-block bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Check Script Loading -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-download text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Check Script Loading</h3>
                <p class="text-gray-600 mb-4">فحص تحميل ملفات JavaScript</p>
                <a href="{{ route('check.script.loading') }}" class="inline-block bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Check DOM Elements -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-code-branch text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Check DOM Elements</h3>
                <p class="text-gray-600 mb-4">فحص عناصر DOM في صفحة الوصفة</p>
                <a href="{{ route('check.dom.elements') }}" class="inline-block bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>

        <!-- Check Tools -->
        <div class="debug-card bg-white rounded-xl shadow-lg p-6">
            <div class="text-center">
                <div class="debug-icon">
                    <i class="fas fa-tools text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3">Check Tools</h3>
                <p class="text-gray-600 mb-4">فحص الأدوات والوصفات المرتبطة بها</p>
                <a href="{{ route('check.tools') }}" class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    <i class="fas fa-external-link-alt ml-2"></i>
                    فتح الصفحة
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-12 bg-gray-50 rounded-xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">إجراءات سريعة</h2>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('home') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                <i class="fas fa-home ml-2"></i>
                العودة للصفحة الرئيسية
            </a>
            <a href="{{ route('recipes') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                <i class="fas fa-utensils ml-2"></i>
                عرض الوصفات
            </a>
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('tools') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                    <i class="fas fa-tools ml-2"></i>
                    عرض الأدوات
                </a>
            @endif
        </div>
    </div>
</div>
@endsection



