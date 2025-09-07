<!DOCTYPE html>
<html>
<head>
    <title>אישור בקשת עבודה</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center;">
<table width="100%" cellspacing="0" cellpadding="10" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; direction: rtl;">
    <tr>
        <td align="center">
            <img src="https://job2day.work/logo/jobs2day.png" alt="לוגו Jobs2Day" style="max-width: 200px; margin: 20px 0;">
        </td>
    </tr>
    <tr>
        <td align="center" style="padding: 20px;">
            <h2 style="color: #333;">ברכות, {{ $data->user->name }}!</h2>
            <p>בקשתך למשרה <strong>{{ $data->post->job_role }}</strong> אושרה.</p>
            <p><strong>חברה:</strong> {{ $data->post->user->companyDetails->company_name }}</p>
            <p><strong>תחום:</strong> {{ $data->post->field }}</p>

            @if(!empty($data->post->subdomain))
                <p><strong>תת-תחום:</strong> {{ $data->post->subdomain }}</p>
            @endif

            <p><strong>מיקום:</strong> {{ $data->post->coordinates }}</p>

            <p><strong>שכר:</strong>
                @if($data->post->fixed_salary)
                    {{ $data->post->fixed_salary }}
                @else
                    {{ $data->post->min_offered_salary }} - {{ $data->post->max_offered_salary }}
                @endif
            </p>
            <p><strong>סוג עבודה:</strong>
                @if($data->post->work_type == 0)
                    משרה חלקית
                @elseif($data->post->work_type == 1)
                    משרה מלאה
                @else
                    גמיש
                @endif
            </p>
            <p><strong>סוג משרה:</strong>
                @if($data->post->is_remote == 0)
                    עבודה במשרד
                @elseif($data->post->is_remote == 1)
                    עבודה מרחוק
                @else
                    גמיש
                @endif
            </p>
            <p><strong>תיאור משרה:</strong>
                {{ $data->post->job_description }}
            </p>
            <p>אנו מצפים לתרומתך. אנא צור קשר עם המעסיק לפרטים נוספים.</p>
            <p>בברכה,</p>
            <p><strong>צוות Job2Day</strong></p>
        </td>
    </tr>
</table>
</body>
</html>
