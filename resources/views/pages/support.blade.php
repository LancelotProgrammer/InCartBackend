<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدعم الفني</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .support-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 900px;
            padding: 40px;
            margin: 20px 0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }

        .alert-success {
            background-color: #e8f5e9;
            border-right: 4px solid #2ecc71;
            color: #2e7d32;
        }

        .alert-error {
            background-color: #fdecea;
            border-right: 4px solid #e74c3c;
            color: #c0392b;
        }

        .support-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
        }

        .support-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .support-header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .support-content {
            color: #444;
            font-size: 16px;
        }

        .support-content h2 {
            color: #2c3e50;
            margin: 25px 0 15px 0;
            font-size: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .support-content p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .support-content ul {
            margin: 15px 0;
            padding-right: 20px;
        }

        .support-content li {
            margin-bottom: 8px;
        }

        .support-content .important-note {
            background-color: #fff8e1;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-right: 4px solid #ffc107;
            font-weight: 500;
        }

        form {
            margin-top: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #2c3e50;
            font-weight: 500;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 15px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .support-container {
                padding: 25px;
                margin: 10px;
            }

            .support-header h1 {
                font-size: 24px;
            }

            .support-content {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="support-container">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="support-header">
            <h1>الدعم الفني</h1>
            <p>نحن هنا لمساعدتك في أي وقت تحتاج فيه إلى المساعدة أو تواجه مشكلة.</p>
        </div>

        <div class="support-content">
            <h2>طرق التواصل معنا</h2>
            <p>يمكنك التواصل مع فريق الدعم الفني عبر القنوات التالية:</p>
            <ul>
                <li><strong>البريد الإلكتروني:</strong> <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                </li>
                <li><strong>رقم الهاتف:</strong>{{ $supportPhone }}</li>
            </ul>

            <div class="important-note">
                إذا كان لديك حساب لدينا، يمكنك تسجيل الدخول إلى التطبيق وفتح <strong>تذكرة دعم</strong> مباشرة من شاشة
                المستخدم.
                هذا يتيح لفريقنا متابعة مشكلتك بشكل أسرع وأكثر دقة.
            </div>

            <h2>نموذج التواصل</h2>
            <p>يمكنك أيضًا مراسلتنا مباشرة عبر النموذج التالي:</p>

            <form action="{{ route('support.submit') }}" method="POST">
                @csrf
                <input type="text" name="website" style="display:none">

                <label for="name">الاسم الكامل</label>
                <input type="text" id="name" name="name" placeholder="أدخل اسمك الكامل" required>

                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" placeholder="example@email.com" required>

                <label for="message">الرسالة</label>
                <textarea id="message" name="message" placeholder="صف مشكلتك أو استفسارك هنا..." required></textarea>

                <button type="submit">إرسال الرسالة</button>
            </form>
        </div>
    </div>
</body>

</html>