<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>אימות דוא"ל - Jobs2Day</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .otp-code {
            font-size: 28px;
            font-weight: bold;
            color: #FF8947;
            text-align: center;
            margin: 25px 0;
            letter-spacing: 3px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
        .divider {
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="https://job2day.work/logo/jobs2day.png" alt="לוגו Jobs2Day" style="max-width: 200px;">
    </div>

    <h1 style="text-align: center; color: #FF8947;">אימות דוא"ל</h1>

    <p style="text-align: right;">שלום רב,</p>

    <p style="text-align: right;">תודה שנרשמת ל-Jobs2Day. אנא השתמש בקוד האימות הבא כדי להשלים את הרישום:</p>

    <div class="otp-code">{{ $otp }}</div>

    <p style="text-align: right;">קוד זה יהיה תקף למשך 10 דקות. אם לא ביקשת קוד זה, ניתן להתעלם מהודעה זו.</p>

    <div class="divider"></div>

    <div class="footer">
        <p><strong>צוות Job2Day</strong></p>
        <p>© {{ date('Y') }} Jobs2Day. כל הזכויות שמורות.</p>
    </div>
</div>
</body>
</html>
