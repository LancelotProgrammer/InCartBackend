<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شروط الخدمة</title>
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

        .terms-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 900px;
            padding: 40px;
            margin: 20px 0;
        }

        .terms-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
        }

        .terms-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .terms-header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .terms-content {
            color: #444;
            font-size: 16px;
        }

        .terms-content h2 {
            color: #2c3e50;
            margin: 25px 0 15px 0;
            font-size: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .terms-content p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .terms-content ul {
            margin: 15px 0;
            padding-right: 20px;
        }

        .terms-content li {
            margin-bottom: 8px;
        }

        .terms-content .important-note {
            background-color: #fff8e1;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-right: 4px solid #ffc107;
            font-weight: 500;
        }

        .acceptance-section {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
            border: 1px solid #c8e6c9;
        }

        .last-updated {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
            border-right: 4px solid #3498db;
        }

        @media (max-width: 768px) {
            .terms-container {
                padding: 25px;
                margin: 10px;
            }

            .terms-header h1 {
                font-size: 24px;
            }

            .terms-content {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="terms-container">
        <div class="terms-header">
            <h1>شروط الخدمة</h1>
            <p>يرجى قراءة هذه الشروط والأحكام بعناية قبل استخدام خدماتنا</p>
        </div>

        <div class="terms-content">
            {!! $termsOfServices !!}
        </div>

        <div class="acceptance-section">
            <p>باستخدامك لخدماتنا، فإنك توافق على الالتزام بهذه الشروط والأحكام.</p>
        </div>
    </div>
</body>

</html>