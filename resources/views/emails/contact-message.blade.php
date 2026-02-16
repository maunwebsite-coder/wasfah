<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رسالة جديدة من نموذج الاتصال</title>
    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 640px;
            margin: 2rem auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(107, 46, 48, 0.12);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #6b2e30, #9f5e63);
            color: #fff;
            padding: 2.5rem;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 0.5rem;
            font-size: 1.8rem;
        }
        .content {
            padding: 2.5rem;
            color: #1f2937;
        }
        .detail {
            margin-bottom: 1.25rem;
        }
        .label {
            font-weight: 700;
            color: #7f3a3d;
            font-size: 0.95rem;
        }
        .value {
            margin-top: 0.35rem;
            font-size: 1.05rem;
            color: #0f172a;
        }
        .message-block {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: #ffffff;
            border: 1px solid rgba(107, 46, 48, 0.2);
            border-radius: 16px;
            line-height: 1.8;
            color: #0f172a;
        }
        .footer {
            padding: 2rem 2.5rem;
            color: #64748b;
            font-size: 0.9rem;
            border-top: 1px solid rgba(15, 23, 42, 0.05);
            background-color: #f8fafc;
        }
        .meta {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>لديك رسالة جديدة ✉️</h1>
            <p>تم إرسالها من صفحة "اتصل بنا" على وصفة</p>
        </div>
        <div class="content">
            <div class="detail">
                <div class="label">المرسل</div>
                <div class="value">{{ $contactMessage->full_name }} ({{ $contactMessage->email }})</div>
            </div>
            @if($contactMessage->phone)
                <div class="detail">
                    <div class="label">رقم الهاتف</div>
                    <div class="value">{{ $contactMessage->phone }}</div>
                </div>
            @endif
            <div class="detail">
                <div class="label">الموضوع المختار</div>
                <div class="value">{{ $contactMessage->subject_label }}</div>
            </div>
            <div class="message-block">
                {!! nl2br(e($contactMessage->message)) !!}
            </div>
        </div>
        <div class="footer">
            <p>تم استلام الرسالة بتاريخ {{ $contactMessage->created_at->format('Y-m-d H:i') }} بتوقيت الخادم.</p>
            @if(data_get($contactMessage->meta, 'ip') || data_get($contactMessage->meta, 'user_agent'))
                <div class="meta">
                    @if(data_get($contactMessage->meta, 'ip'))
                        مصدر الزيارة: {{ data_get($contactMessage->meta, 'ip') }}
                    @endif
                    @if(data_get($contactMessage->meta, 'user_agent'))
                        <br>المتصفح: {{ data_get($contactMessage->meta, 'user_agent') }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>
</html>








