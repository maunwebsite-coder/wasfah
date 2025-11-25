@extends('layouts.app')

@section('title', 'اختبار الإحصائيات')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">اختبار إحصائيات الداشبورد</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- إجمالي الحجوزات -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-r-4 border-blue-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check text-3xl text-blue-500"></i>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    إجمالي الحجوزات
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ number_format($totalBookings) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الحجوزات المؤكدة -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-r-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-3xl text-green-500"></i>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    حجوزات مؤكدة
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ number_format($confirmedBookings) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الحجوزات المعلقة -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-r-4 border-yellow-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-3xl text-yellow-500"></i>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    حجوزات معلقة
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ number_format($pendingBookings) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إجمالي الإيرادات -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-r-4 border-purple-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill text-3xl text-purple-500"></i>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    إجمالي الإيرادات
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ number_format($totalRevenue, 2) }} دولار أمريكي
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- آخر الحجوزات -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">آخر الحجوزات</h3>
                
                @if($recentBookings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الورشة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentBookings as $booking)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $booking->workshop->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $booking->user->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($booking->status === 'confirmed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    مؤكدة
                                                </span>
                                            @elseif($booking->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    معلقة
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    ملغية
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $booking->created_at->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-300 text-4xl mb-2"></i>
                        <p class="text-gray-500">لا توجد حجوزات</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">ملاحظة مهمة</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>إذا كانت هذه الإحصائيات صحيحة ولكن لا تظهر في الداشبورد الأصلي، جرب:</p>
                        <ul class="list-disc list-inside mt-2">
                            <li>تحديث الصفحة (F5)</li>
                            <li>مسح ذاكرة التخزين المؤقت للمتصفح</li>
                            <li>فتح الداشبورد في نافذة خاصة (Incognito)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

