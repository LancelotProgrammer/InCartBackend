<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية</title>
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

        .privacy-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 900px;
            padding: 40px;
            margin: 20px 0;
        }

        .privacy-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
        }

        .privacy-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .privacy-header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .privacy-content {
            color: #444;
            font-size: 16px;
        }

        .privacy-content h2 {
            color: #2c3e50;
            margin: 25px 0 15px 0;
            font-size: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .privacy-content p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .privacy-content ul {
            margin: 15px 0;
            padding-right: 20px;
        }

        .privacy-content li {
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .privacy-container {
                padding: 25px;
                margin: 10px;
            }

            .privacy-header h1 {
                font-size: 24px;
            }

            .privacy-content {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="privacy-container">
        <div class="privacy-header">
            <h1>سياسة الخصوصية</h1>
            <p>نحن نلتزم بحماية خصوصية معلوماتك الشخصية</p>
        </div>

        <div class="privacy-content">
            {!! $privacyPolicy !!}
        </div>
    </div>
</body>

</html>