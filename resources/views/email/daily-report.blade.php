<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #939970eb;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 20px;
            font-size: 16px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        Daily Attendance Report
    </div>

    <div class="content">
        <h3>Attendance report of {{ $pdfTodayReport ?? 'No report available' }}</h3>
    <p>Hello, here is the daily attendance report for today. Click the links below to view:</p>
    <p><span><u><a href="{{ $fileUrlPdf ?? '#' }}" target="_blank">View PDF</a></u></span></p>
    <p><span><u><a href="{{ $fileUrlExcel ?? '#' }}" target="_blank">View Excel</a></u></span></p>
    <p>Thank you</p>
    </div>
</div>

</body>
</html>
