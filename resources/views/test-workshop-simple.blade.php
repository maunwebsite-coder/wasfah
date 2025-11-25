<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Workshops</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-center mb-8">Test Workshops Page</h1>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Workshop Data Test:</h2>
            
            @if(isset($workshops) && $workshops->count() > 0)
                <p class="text-green-600 mb-4">✅ Workshops loaded successfully! Count: {{ $workshops->count() }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($workshops as $workshop)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3 class="font-semibold text-lg">{{ $workshop->title }}</h3>
                            <p class="text-gray-600 text-sm mt-2">{{ Str::limit($workshop->description, 100) }}</p>
                            <div class="mt-3 text-sm">
                                <p><strong>المدرب:</strong> {{ $workshop->instructor }}</p>
                                <p><strong>التاريخ:</strong> {{ $workshop->start_date->format('Y-m-d H:i') }}</p>
                                <p><strong>السعر:</strong> {{ $workshop->formatted_price }}</p>
                                <p><strong>نشط:</strong> {{ $workshop->is_active ? 'نعم' : 'لا' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-red-600">❌ No workshops found or error loading workshops</p>
            @endif
            
            @if(isset($featuredWorkshop) && $featuredWorkshop)
                <div class="mt-8 p-4 bg-orange-100 rounded-lg">
                    <h3 class="font-semibold text-lg text-orange-800">الورشة المميزة:</h3>
                    <p class="text-orange-700">{{ $featuredWorkshop->title }}</p>
                </div>
            @endif
        </div>
        
        <div class="mt-8 text-center">
            <a href="/workshops" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                العودة لصفحة الورشات الأصلية
            </a>
        </div>
    </div>
</body>
</html>

