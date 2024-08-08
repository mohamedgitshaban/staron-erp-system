<!DOCTYPE html>
<html>
<head>
    <title>Daily Attendance Log</title>
</head>
<body>
    <h1 style="color: blue">Daily Attendance Log</h1>
    @if($attendanceLogs->isEmpty())
        <p>No attendance logs for the selected date.</p>
    @else

                @foreach($attendanceLogs as $log)

                        <p style="margin-left: 5px">{{ $log->user->name }} upload {{ $log->date }} Attendance</p>
                @endforeach

    @endif
</body>
</html>
