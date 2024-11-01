<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $todayDate->format('d F Y') }} Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #686868;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <h1 style="text-align: center">Today's Attendance on {{ $todayDate->format('d F Y') }}</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Date</th>
                <th>Time-In</th>
                <th>Time-Out</th>
                <th>-</th>
            </tr>
        </thead>
        @php
            $counter = 1;
        @endphp
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}</td>
                    <td>{{ Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') }}</td>
                    <td>{{ Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') }}</td>
                    <td>{{ $attendance->user->shift->time_in < $attendance->clock_in ? 'Late' : 'On time' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
