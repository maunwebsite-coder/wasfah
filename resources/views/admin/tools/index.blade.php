@extends('layouts.app')

@section('title', 'Ø¥Ø¯Ø§Ø±Ø© Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ø´ÙŠÙ - Ù„ÙˆØ­Ø© Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©')

@push('styles')
<style>
    .admin-card {
        background: linear-gradient(135deg, #a56970 0%, #8a4348 100%);
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
                    <h1 class="text-3xl font-bold mb-2">Ø¥Ø¯Ø§Ø±Ø© Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ø´ÙŠÙ</h1>
                    <p class="text-blue-100">Ø¥Ø¯Ø§Ø±Ø© Ø£Ø¯ÙˆØ§Øª Ø§Ù„Ø´ÙŠÙ Ø§Ù„Ø§Ø­ØªØ±Ø§ÙÙŠØ© Ù„ØµÙ†Ø§Ø¹Ø© Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('admin.tools.create') }}" 
                       class="bg-white text-purple-600 hover:bg-gray-100 font-bold py-3 px-6 rounded-lg transition-all duration-300 flex items-center">
                        <i class="fas fa-plus ml-2"></i>
                        Ø¥Ø¶Ø§ÙØ© Ø£Ø¯Ø§Ø© Ø´ÙŠÙ Ø¬Ø¯ÙŠØ¯Ø©
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
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„ØµÙˆØ±Ø©</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„Ø§Ø³Ù…</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„ÙØ¦Ø©</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„Ø³Ø¹Ø±</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„ØªÙ‚ÙŠÙŠÙ…</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„Ø­Ø§Ù„Ø©</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡Ø§Øª</th>
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
                                    {{ number_format($tool->price, 2) }} Ø¯Ø±Ù‡Ù…
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
                                            {{ $tool->is_active ? 'Ù†Ø´Ø·' : 'ØºÙŠØ± Ù†Ø´Ø·' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                        <a href="{{ route('admin.tools.show', $tool) }}" 
                                           class="action-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg" 
                                           title="Ø¹Ø±Ø¶">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.tools.edit', $tool) }}" 
                                           class="action-btn bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg" 
                                           title="ØªØ¹Ø¯ÙŠÙ„">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.tools.destroy', $tool) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Ù‡Ù„ Ø£Ù†Øª Ù…ØªØ£ÙƒØ¯ Ù…Ù† Ø­Ø°Ù Ù‡Ø°Ù‡ Ø§Ù„Ø£Ø¯Ø§Ø©ØŸ')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="action-btn bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg" 
                                                    title="Ø­Ø°Ù">
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
                                        <p class="text-lg">Ù„Ø§ ØªÙˆØ¬Ø¯ Ø£Ø¯ÙˆØ§Øª Ø´ÙŠÙ Ù…ØªØ§Ø­Ø©</p>
                                        <p class="text-sm">Ø§Ø¨Ø¯Ø£ Ø¨Ø¥Ø¶Ø§ÙØ© Ø£ÙˆÙ„ Ø£Ø¯Ø§Ø© Ø´ÙŠÙ Ù„Ùƒ</p>
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



