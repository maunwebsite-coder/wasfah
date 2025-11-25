@if ($paginator->hasPages())
    <nav role="navigation" aria-label="تنقل الصفحات" dir="rtl">
        <div class="flex items-center justify-center gap-3">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-400 bg-white border border-slate-200 rounded-full shadow-sm cursor-not-allowed">
                    <i class="fas fa-angle-right text-xs"></i>
                    السابق
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-full shadow-sm hover:text-orange-500 hover:border-orange-300 transition">
                    <i class="fas fa-angle-right text-xs"></i>
                    السابق
                </a>
            @endif

            <div class="flex items-center gap-1 bg-white/80 px-2 py-1 rounded-full shadow-sm border border-slate-200">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-1 text-xs font-semibold text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-full bg-gradient-to-br from-orange-500 to-amber-400 shadow-inner">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-semibold text-slate-600 rounded-full border border-transparent hover:border-orange-300 hover:text-orange-500 transition" aria-label="الذهاب إلى الصفحة {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-full shadow-sm hover:text-orange-500 hover:border-orange-300 transition">
                    التالي
                    <i class="fas fa-angle-left text-xs"></i>
                </a>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-400 bg-white border border-slate-200 rounded-full shadow-sm cursor-not-allowed">
                    التالي
                    <i class="fas fa-angle-left text-xs"></i>
                </span>
            @endif
        </div>
    </nav>
@endif

