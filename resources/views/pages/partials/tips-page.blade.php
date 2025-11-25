@php
    use Illuminate\Support\Facades\Lang;

    $categories = Lang::get("{$namespace}.categories");
    $details = Lang::get("{$namespace}.details");
    $mistakes = Lang::get("{$namespace}.mistakes");
    $tools = Lang::get("{$namespace}.tools");
    $categoryVisuals = [
        ['wrapper' => 'bg-orange-100 text-orange-600', 'icon' => 'fas fa-cookie-bite'],
        ['wrapper' => 'bg-red-100 text-red-600', 'icon' => 'fas fa-thermometer-half'],
        ['wrapper' => 'bg-green-100 text-green-600', 'icon' => 'fas fa-candy-cane'],
    ];
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl text-white p-12 mb-12 text-center shadow-2xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ __("{$namespace}.hero.title") }}</h1>
            <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">
                {{ __("{$namespace}.hero.subtitle") }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @foreach ($categories as $index => $category)
                @php
                    $visual = $categoryVisuals[$index % count($categoryVisuals)];
                @endphp
                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-shadow">
                    <div class="{{ $visual['wrapper'] }} w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="{{ $visual['icon'] }} text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">{{ $category['title'] }}</h2>
                    <ul class="space-y-3 text-gray-600">
                        @foreach ($category['items'] as $item)
                            <li class="flex items-start space-x-3 rtl:space-x-reverse">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-10 mb-12">
            <div class="flex flex-col lg:flex-row gap-10 items-center">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ $details['title'] }}</h2>
                    @if (!empty($details['subtitle']))
                        <p class="text-gray-600 text-lg max-w-2xl">{{ $details['subtitle'] }}</p>
                    @endif
                </div>
                <div class="w-full lg:w-1/3">
                    <img src="https://images.unsplash.com/photo-1499636136210-6f4ee915583e?q=80&w=1200&auto=format&fit=crop"
                         alt="Premium dessert preparation"
                         class="rounded-xl shadow-lg w-full h-64 object-cover" loading="lazy">
                </div>
            </div>

            <div class="mt-10 space-y-6">
                @foreach ($details['tips'] as $tip)
                    <div class="p-6 border border-gray-100 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50">
                        <div class="flex items-center gap-4 mb-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-orange-600 font-bold shadow">
                                {{ $loop->iteration }}
                            </span>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $tip['title'] }}</h3>
                        </div>
                        <p class="text-gray-700 leading-relaxed">{{ $tip['description'] }}</p>
                        <p class="mt-3 text-sm font-medium text-orange-700">{{ $tip['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl shadow-lg p-10">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    {{ $mistakes['title'] }}
                </h2>
                <div class="space-y-6">
                    @foreach ($mistakes['items'] as $mistake)
                        <div class="border-l-4 border-red-500 pl-6">
                            <h3 class="text-xl font-semibold text-gray-900">{{ $mistake['title'] }}</h3>
                            <p class="text-gray-600 mt-2">{{ $mistake['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-10">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    <i class="fas fa-tools text-amber-500"></i>
                    {{ $tools['title'] }}
                </h2>
                <ul class="space-y-4">
                    @foreach ($tools['items'] as $tool)
                        <li class="flex items-start gap-4 bg-gray-50 rounded-xl p-4">
                            <div class="w-10 h-10 rounded-full bg-white shadow flex items-center justify-center text-amber-500">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ $tool['title'] }}</p>
                                <p class="text-gray-600">{{ $tool['description'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

