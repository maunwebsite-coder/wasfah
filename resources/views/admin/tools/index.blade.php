@extends('layouts.app')

@section('title', 'إدارة أدوات الشيف - لوحة الإدارة')

@push('styles')
<style>
    .admin-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .tool-status-active {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .tool-status-inactive {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    .action-btn {
        transition: all 0.3s ease;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="admin-card text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">إدارة أدوات الشيف</h1>
                    <p class="text-blue-100">إدارة أدوات الشيف الاحترافية لصناعة الحلويات</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('admin.tools.create') }}" 
                       class="bg-white text-purple-600 hover:bg-gray-100 font-bold py-3 px-6 rounded-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة أداة شيف جديدة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tools Table -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">الصورة</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">الاسم</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">الفئة</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">السعر</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">التقييم</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">الحالة</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($tools as $tool)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden">
                                        @if($tool->image)
                                            <img src="{{ asset('storage/' . $tool->image) }}" 
                                                 alt="{{ $tool->name }}" 
                                                 class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-tools text-gray-400"></i>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $tool->name }}</div>
                                        <div class="text-sm text-gray-500 line-clamp-2">{{ Str::limit($tool->description, 50) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $tool->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ number_format($tool->price, 2) }} درهم
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400 text-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($tool->rating) ? '' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-sm text-gray-500 mr-2">{{ $tool->rating }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('admin.tools.toggle', $tool) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-all {{ $tool->is_active ? 'tool-status-active text-white' : 'tool-status-inactive text-white' }}">
                                            <i class="fas {{ $tool->is_active ? 'fa-check' : 'fa-times' }} ml-1"></i>
                                            {{ $tool->is_active ? 'نشط' : 'غير نشط' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                        <a href="{{ route('admin.tools.show', $tool) }}" 
                                           class="action-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg" 
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.tools.edit', $tool) }}" 
                                           class="action-btn bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg" 
                                           title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.tools.destroy', $tool) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه الأداة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="action-btn bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg" 
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="fas fa-tools text-4xl mb-4"></i>
                                        <p class="text-lg">لا توجد أدوات شيف متاحة</p>
                                        <p class="text-sm">ابدأ بإضافة أول أداة شيف لك</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($tools->hasPages())
            <div class="mt-6">
                {{ $tools->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


