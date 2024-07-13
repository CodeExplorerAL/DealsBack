<!DOCTYPE html>
<html>

<head>
    <title>檢舉通知</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }

        h1 {
            color: #e91e63;
            border-bottom: 2px solid #e91e63;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-size: 24px;
        }

        p {
            margin: 20px 0;
            line-height: 1.6;
            color: #555555;
        }

        .highlight {
            font-weight: bold;
            color: #e91e63;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #777777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo1.png') }}" alt="Your Logo" class="logo">
            <h1>檢舉通知</h1>
        </div>
        <p><strong>致管理者，</strong></p>
        <p><span class="highlight">檢舉人ID:</span> {{ $subAndReport->UID }}</p>
        <p><span class="highlight">檢舉人名稱:</span> {{ $subAndReport->user->name }}</p>

        @if(isset($subAndReport->ReportWID))
        <p><span class="highlight">被檢舉文章ID:</span> {{ $subAndReport->ReportWID }}</p>
        <p><span class="highlight">被檢舉文章標題:</span> {{ $subAndReport->userPost->Title }}</p>
        <p><span class="highlight">被檢舉文章內容:</span> {{ $subAndReport->userPost->Article }}</p>
        @endif

        @if($subAndReport->postMessage)
        <p><span class="highlight">被檢舉評論ID:</span> {{ $subAndReport->ReportMSGWID }}</p>
        <p><span class="highlight">被檢舉評論內容:</span> {{ $subAndReport->postMessage->MSGPost }}</p>
        @endif

        <p><span class="highlight">檢舉原因:</span> {{ $subAndReport->ReportContent }}</p>
        <div class="footer">
            <p>請儘速至後台查看並處理上述檢舉事宜，感謝您。</p>
        </div>
    </div>
</body>

</html>