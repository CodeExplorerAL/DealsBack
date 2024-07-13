<!DOCTYPE html>
<html>

<head>
    <title>檢舉通知</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #ffe6f2;
            /* 輕粉紅背景 */
            color: #5c3a75;
            /* 深粉紫色文字 */
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
            /* 白色背景 */
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
            color: #ff66b2;
            /* 淺粉紅標題 */
            border-bottom: 2px solid #ff66b2;
            /* 淺粉紅下劃線 */
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-size: 24px;
        }

        p {
            margin: 20px 0;
            line-height: 1.6;
        }

        .highlight {
            font-weight: bold;
            color: #5c3a75;
            /* 深粉紫色文字 */
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #a64d79;
            /* 深粉紫色頁腳文字 */
        }

        .note {
            color: #a64d79;
            /* 深粉紫色提示文字 */
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo1.png') }}" alt="Your Logo" class="logo">
            <h1>收到檢舉通知</h1>
        </div>

        <p><span class="highlight">親愛的 {{ $subAndReport->user->name }},</span></p>

        <p>我們已收到您對於內容的檢舉，非常感謝您的貢獻與合作！我們非常重視每一份檢舉，並將進行審核。</p>

        @if(isset($subAndReport->ReportWID))
        <p><span class="highlight">檢舉的文章標題:</span> {{ $subAndReport->userPost->Title }}</p>
        <p><span class="highlight">檢舉的文章內容:</span> {{ $subAndReport->userPost->Article }}</p>
        @endif

        @if($subAndReport->postMessage)
        <p><span class="highlight">檢舉的評論內容:</span> {{ $subAndReport->postMessage->MSGPost }}</p>
        @endif 

        <p><span class="highlight">您的檢舉原因:</span> {{ $subAndReport->ReportContent }}</p>

        <div class="footer">
            <p>這是自動回覆訊息，請勿回覆此郵件。</p>
            <div class="note">
                <p>如有其他疑問或需進一步的協助，請聯繫我們的客服團隊。</p>
            </div>
        </div>

        <!-- 我們團隊的內容和logo -->
        <div class="team-info" style="text-align: center;">
            <img src="{{ asset('images/logo2.png') }}" alt="Our Team Logo" class="team-logo" style="max-width: 150px; display: inline-block;">
            <p>感謝您支持我們的平台。我們致力於提供一個安全和友善的環境給所有用戶。</p>
        </div>

    </div>

</body>

</html>