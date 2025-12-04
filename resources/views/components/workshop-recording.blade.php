@props(['workshop', 'user' =\u003e null])

@php
    $user = $user ?? auth()->user();
    
    // Check if workshop has recording and user can view it
    if (!$workshop-\u003ehasRecording() || !$workshop-\u003euserCanViewRecording($user)) {
        return;
    }
    
    $recordingUrl = $workshop-\u003egetAccessibleRecordingUrl($user);
    
    if (!$recordingUrl) {
        return;
    }
    
    // Use ResolvesWorkshopRecordings trait methods via a helper
    $previewUrl = null;
    try {
        // Try to build an embeddable preview URL
        if (class_exists('App\\Support\\Concerns\\ResolvesWorkshopRecordings')) {
            $resolver = new class {
                use App\\Support\\Concerns\\ResolvesWorkshopRecordings;
            };
            $previewUrl = $resolver-\u003ebuildRecordingPreviewUrl($recordingUrl);
        }
    } catch (\\Throwable $e) {
        // Fallback to direct link
    }
    
    $isHost = $workshop-\u003euser_id === $user?-\u003eid;
@endphp

\u003cdiv class=\"mt-8 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl shadow-lg overflow-hidden border border-indigo-100\"\u003e
    \u003cdiv class=\"p-6\"\u003e
        \u003cdiv class=\"flex items-center gap-3 mb-4\"\u003e
            \u003cdiv class=\"w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-md\"\u003e
                \u003ci class=\"fas fa-video text-white text-xl\"\u003e\u003c/i\u003e
            \u003c/div\u003e
            \u003cdiv class=\"flex-1\"\u003e
                \u003ch3 class=\"text-2xl font-bold text-slate-900\"\u003eتسجيل الورشة\u003c/h3\u003e
                \u003cp class=\"text-sm text-slate-600 mt-1\"\u003e
                    @if($isHost)
                        \u003cspan class=\"inline-flex items-center gap-1 text-indigo-600 font-semibold\"\u003e
                            \u003ci class=\"fas fa-crown text-xs\"\u003e\u003c/i\u003e
                            أنت مضيف هذه الورشة
                        \u003c/span\u003e
                    @else
                        متاح للمشاركين المؤكدين فقط
                    @endif
                \u003c/p\u003e
            \u003c/div\u003e
        \u003c/div\u003e
        
        @if($previewUrl)
            \u003c!-- Embedded Video Player --\u003e
            \u003cdiv class=\"aspect-video bg-slate-900 rounded-xl overflow-hidden shadow-xl mb-4\"\u003e
                \u003ciframe 
                    src=\"{{ $previewUrl }}\" 
                    class=\"w-full h-full\"
                    allow=\"autoplay; encrypted-media; picture-in-picture\"
                    allowfullscreen
                    loading=\"lazy\"
                \u003e\u003c/iframe\u003e
            \u003c/div\u003e
        @else
            \u003c!-- Direct Link Button --\u003e
            \u003cdiv class=\"bg-white rounded-xl p-6 mb-4 border border-indigo-100\"\u003e
                \u003cdiv class=\"flex items-center justify-between gap-4\"\u003e
                    \u003cdiv class=\"flex-1\"\u003e
                        \u003cp class=\"font-semibold text-slate-800 mb-1\"\u003eالتسجيل جاهز للمشاهدة\u003c/p\u003e
                        \u003cp class=\"text-sm text-slate-600\"\u003eانقر على الزر لفتح التسجيل في نافذة جديدة\u003c/p\u003e
                    \u003c/div\u003e
                    \u003ca 
                        href=\"{{ $recordingUrl }}\" 
                        target=\"_blank\"
                        rel=\"noopener noreferrer\"
                        class=\"inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5\"\u003e
                        \u003ci class=\"fas fa-play-circle\"\u003e\u003c/i\u003e
                        مشاهدة التسجيل
                    \u003c/a\u003e
                \u003c/div\u003e
            \u003c/div\u003e
        @endif
        
        \u003c!-- Info Notice --\u003e
        \u003cdiv class=\"bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-indigo-100\"\u003e
            \u003cdiv class=\"flex items-start gap-3\"\u003e
                \u003ci class=\"fas fa-info-circle text-indigo-600 mt-0.5\"\u003e\u003c/i\u003e
                \u003cdiv class=\"flex-1 text-sm text-slate-700\"\u003e
                    \u003cp class=\"font-semibold mb-1\"\u003eملاحظة هامة:\u003c/p\u003e
                    \u003cul class=\"list-disc list-inside space-y-1 text-slate-600\"\u003e
                        \u003cli\u003eهذا التسجيل متاح فقط للمشاركين الذين تم تأكيد حجزهم\u003c/li\u003e
                        \u003cli\u003eإذا تم إلغاء حجزك، سيتم إلغاء صلاحية الوصول تلقائياً\u003c/li\u003e
                        @if($isHost)
                            \u003cli class=\"text-indigo-600 font-semibold\"\u003eبصفتك المضيف، يمكنك الوصول إلى التسجيل في أي وقت\u003c/li\u003e
                        @endif
                    \u003c/ul\u003e
                \u003c/div\u003e
            \u003c/div\u003e
        \u003c/div\u003e
    \u003c/div\u003e
\u003c/div\u003e
