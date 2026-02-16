@extends('layouts.app')

@section('title', 'Ø¹Ø±Ø¶ Ø£Ø¯Ø§Ø© Ø§Ù„Ø´ÙŠÙ - Ù„ÙˆØ­Ø© Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©')

@push('styles')
<style>
    .admin-card {
        background: linear-gradient(135deg, #a56970 0%, #8a4348 100%);
    }
    .tool-detail-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    }
    .feature-tag {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .status-active {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .status-inactive {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="admin-card text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.tools.index') }}" 
                       class="bg-white/20 hover:bg-white/30 text-white p-3 rounded-lg transition-all duration-300 mr-4">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2">ØªÙØ§ØµÙŠÙ„ Ø£Ø¯Ø§Ø© Ø§Ù„Ø´ÙŠÙ</h1>
                        <p class="text-blue-100">{{ $tool->name }}</p>
                    </div>
                </div>
                <div class="flex space-x-3 rtl:space-x-reverse">
                    <a href="{{ route('admin.tools.edit', $tool) }}" 
                       class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-edit ml-2"></i>
                        ØªØ¹Ø¯ÙŠÙ„
                    </a>
                    <form action="{{ route('admin.tools.destroy', $tool) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ù…Ù† Ø­Ø°Ù Ù‡Ø°Ù‡ Ø§Ù„Ø£Ø¯Ø§Ø©ØŸ')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500/20 hover:bg-red-500/30 text-white px-4 py-2 rounded-lg transition-all duration-300">
                            <i class="fas fa-trash ml-2"></i>
                            Ø­Ø°Ù
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tool Details -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Image and Basic Info -->
                <div class="tool-detail-card rounded-xl shadow-lg p-8">
                    <div class="text-center mb-6">
                        @if($tool->image)
                            <img src="{{ asset('storage/' . $tool->image) }}" 
                                 alt="{{ $tool->name }}" 
                                 class="w-full max-w-md mx-auto rounded-lg shadow-lg" loading="lazy">
                        @else
                            <div class="w-full max-w-md mx-auto h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tools text-6xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $tool->name }}</h2>
                            <p class="text-gray-600 leading-relaxed">{{ $tool->description }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-3xl font-bold text-purple-600">{{ number_format($tool->price, 2) }} Ø¯Ø±Ù‡Ù…</span>
                            </div>
                            <div class="flex items-center">
                                <div class="flex text-yellow-400 text-lg">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($tool->rating) ? '' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-gray-600 mr-2">{{ $tool->rating }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $tool->category }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $tool->is_active ? 'status-active text-white' : 'status-inactive text-white' }}">
                                <i class="fas {{ $tool->is_active ? 'fa-check' : 'fa-times' }} ml-1"></i>
                                {{ $tool->is_active ? 'Ù†Ø´Ø·' : 'ØºÙŠØ± Ù†Ø´Ø·' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-6">
                    <!-- Features -->
                    @if($tool->features && count($tool->features) > 0)
                        <div class="tool-detail-card rounded-xl shadow-lg p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Ø§Ù„Ù…Ù…ÙŠØ²Ø§Øª</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($tool->features as $feature)
                                    <span class="feature-tag text-white text-sm px-3 py-1 rounded-full">
                                        {{ $feature }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Links -->
                    <div class="tool-detail-card rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Ø±ÙˆØ§Ø¨Ø· Ø§Ù„Ø´Ø±Ø§Ø¡</h3>
                        <div class="space-y-3">
                            @if($tool->amazon_url)
                                <a href="{{ $tool->amazon_url }}" 
                                   target="_blank"
                                   class="flex items-center justify-between p-3 bg-orange-100 hover:bg-orange-200 rounded-lg transition-all">
                                    <div class="flex items-center">
                                        <i class="fab fa-amazon text-orange-600 text-xl ml-3"></i>
                                        <span class="font-semibold text-gray-800">Amazon</span>
                                    </div>
                                    <i class="fas fa-external-link-alt text-gray-500"></i>
                                </a>
                            @endif

                            @if($tool->affiliate_url)
                                <a href="{{ $tool->affiliate_url }}" 
                                   target="_blank"
                                   class="flex items-center justify-between p-3 bg-blue-100 hover:bg-blue-200 rounded-lg transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-shopping-cart text-blue-600 text-xl ml-3"></i>
                                        <span class="font-semibold text-gray-800">Ø§Ù„Ø´Ø±Ø§Ø¡ Ø§Ù„Ù…Ø­Ù„ÙŠ</span>
                                    </div>
                                    <i class="fas fa-external-link-alt text-gray-500"></i>
                                </a>
                            @endif

                            @if(!$tool->amazon_url && !$tool->affiliate_url)
                                <p class="text-gray-500 text-center py-4">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø±ÙˆØ§Ø¨Ø· Ø´Ø±Ø§Ø¡ Ù…ØªØ§Ø­Ø©</p>
                            @endif
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="tool-detail-card rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Ø§Ù„ØªÙØ§ØµÙŠÙ„ Ø§Ù„ØªÙ‚Ù†ÙŠØ©</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">ØªØ±ØªÙŠØ¨ Ø§Ù„Ø¹Ø±Ø¶:</span>
                                <span class="font-semibold text-gray-800">{{ $tool->sort_order }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¥Ù†Ø´Ø§Ø¡:</span>
                                <span class="font-semibold text-gray-800">{{ $tool->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ø¢Ø®Ø± ØªØ­Ø¯ÙŠØ«:</span>
                                <span class="font-semibold text-gray-800">{{ $tool->updated_at->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



