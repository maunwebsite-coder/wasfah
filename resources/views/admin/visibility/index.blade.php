@extends('layouts.app')

@section('title', 'إدارة إعدادات الرؤية')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">إدارة إعدادات الرؤية</h1>
            <div class="flex space-x-2">
                <button onclick="initializeDefaults()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    تهيئة الإعدادات الافتراضية
                </button>
                <button onclick="clearCache()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    مسح الذاكرة المؤقتة
                </button>
            </div>
        </div>

        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($settings as $setting)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">{{ ucfirst($setting->section) }}</h3>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 mr-2">
                                {{ $setting->is_visible ? 'مرئي' : 'مخفي' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       class="sr-only peer" 
                                       {{ $setting->is_visible ? 'checked' : '' }}
                                       onchange="toggleVisibility('{{ $setting->section }}', this.checked)">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    @if($setting->description)
                    <p class="text-sm text-gray-600">{{ $setting->description }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-gray-100 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">تحديث جماعي</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($settings as $setting)
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="bulk-{{ $setting->section }}" 
                           class="mr-2"
                           {{ $setting->is_visible ? 'checked' : '' }}>
                    <label for="bulk-{{ $setting->section }}" class="text-sm text-gray-700">
                        {{ ucfirst($setting->section) }}
                    </label>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
                <button onclick="bulkUpdate()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    تحديث الإعدادات المحددة
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle visibility for a specific section
function toggleVisibility(section, isVisible) {
    fetch(`/admin/visibility/${section}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            is_visible: isVisible
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification('حدث خطأ في تحديث الإعداد', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في الاتصال', 'error');
    });
}

// Bulk update visibility settings
function bulkUpdate() {
    const settings = {};
    document.querySelectorAll('input[id^="bulk-"]').forEach(input => {
        const section = input.id.replace('bulk-', '');
        settings[section] = input.checked;
    });

    fetch('/admin/visibility/bulk-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            settings: settings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Reload page to reflect changes
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('حدث خطأ في التحديث الجماعي', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في الاتصال', 'error');
    });
}

// Initialize default settings
function initializeDefaults() {
    if (confirm('هل أنت متأكد من تهيئة الإعدادات الافتراضية؟')) {
        fetch('/admin/visibility/initialize-defaults', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification('حدث خطأ في تهيئة الإعدادات', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('حدث خطأ في الاتصال', 'error');
        });
    }
}

// Clear cache
function clearCache() {
    if (confirm('هل أنت متأكد من مسح الذاكرة المؤقتة؟')) {
        fetch('/admin/visibility/clear-cache', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification('حدث خطأ في مسح الذاكرة المؤقتة', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('حدث خطأ في الاتصال', 'error');
        });
    }
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection

