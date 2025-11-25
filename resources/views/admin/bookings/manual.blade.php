@extends('layouts.app')

@section('title', 'إضافة حجز يدوي')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-plus-circle text-green-600 ml-2"></i>
                        إضافة حجز يدوي
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">إضافة حجز جديد للمستخدمين الذين حجزوا عبر الواتساب</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة لقائمة الحجوزات
                    </a>
                </div>
            </div>
        </div>

        <!-- إضافة سريعة -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-bolt text-yellow-500 ml-2"></i>
                    إضافة سريعة (لحجوزات الواتساب)
                </h3>
                <p class="text-sm text-gray-500 mt-1">أدخل بيانات المستخدم والحجز بسرعة</p>
            </div>
            <div class="p-6">
                <form id="quickAddForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الورشة *</label>
                            <select name="workshop_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر الورشة</option>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}" data-price="{{ $workshop->price }}">
                                        {{ $workshop->title }} - {{ $workshop->formatted_price }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ *</label>
                            <input type="number" name="payment_amount" step="0.01" min="0" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم المستخدم *</label>
                            <input type="text" name="user_name" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="الاسم الكامل">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف *</label>
                            <input type="text" name="user_phone" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="رقم الهاتف">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                            <input type="email" name="user_email" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="example@email.com">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                            <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <i class="fas fa-plus ml-2"></i>
                            إضافة الحجز
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- إضافة تفصيلية -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-cog text-blue-500 ml-2"></i>
                    إضافة تفصيلية
                </h3>
                <p class="text-sm text-gray-500 mt-1">إضافة حجز مع جميع التفاصيل والخيارات</p>
            </div>
            <div class="p-6">
                <form id="detailedForm" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الورشة *</label>
                            <select name="workshop_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر الورشة</option>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}">
                                        {{ $workshop->title }} - {{ $workshop->formatted_price }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المستخدم *</label>
                            <select name="user_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر المستخدم</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">حالة الحجز *</label>
                            <select name="status" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending">في الانتظار</option>
                                <option value="confirmed">مؤكدة</option>
                                <option value="cancelled">ملغية</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">حالة الدفع *</label>
                            <select name="payment_status" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending">في الانتظار</option>
                                <option value="paid">مدفوعة</option>
                                <option value="refunded">مستردة</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع</label>
                            <input type="text" name="payment_method" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="نقد، تحويل بنكي، إلخ">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ *</label>
                            <input type="number" name="payment_amount" step="0.01" min="0" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الحجز *</label>
                            <input type="datetime-local" name="booking_date" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                            <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="أي ملاحظات إضافية..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-save ml-2"></i>
                            حفظ الحجز
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // تعيين التاريخ الحالي كقيمة افتراضية
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.querySelector('input[name="booking_date"]').value = localDateTime;

    // تحديث المبلغ عند تغيير الورشة
    document.querySelector('select[name="workshop_id"]').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        if (price) {
            document.querySelector('input[name="payment_amount"]').value = price;
        }
    });

    // معالجة النموذج السريع
    document.getElementById('quickAddForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("admin.bookings.quick-add") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم إضافة الحجز بنجاح!');
                this.reset();
                // إعادة توجيه لقائمة الحجوزات
                window.location.href = '{{ route("admin.bookings.index") }}';
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء إضافة الحجز');
        });
    });

    // معالجة النموذج التفصيلي
    document.getElementById('detailedForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("admin.bookings.manual.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم إضافة الحجز بنجاح!');
                this.reset();
                // إعادة توجيه لقائمة الحجوزات
                window.location.href = '{{ route("admin.bookings.index") }}';
            } else {
                alert('خطأ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء إضافة الحجز');
        });
    });
});
</script>
@endsection

