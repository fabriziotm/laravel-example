@extends('layouts.main')

@section('title', 'Staff Timesheet')

@section('content')
    <table class="table table-striped">
        <thead>
            <th class="text-center">Staff Id</th>
            <th class="text-center">Monday</th>
            <th class="text-center">Tuesday</th>
            <th class="text-center">Wednesday</th>
            <th class="text-center">Thursday</th>
            <th class="text-center">Friday</th>
            <th class="text-center">Saturday</th>
            <th class="text-center">Sunday</th>
        </thead>
        <tbody>
        @foreach ($timesheet as $staff)
            <tr>
                <td class="text-center text-middle">{{ $staff->staffid }}</td>
                <td class="text-center text-middle">{{ $staff->monday }}</td>
                <td class="text-center text-middle">{{ $staff->tuesday }}</td>
                <td class="text-center text-middle">{{ $staff->wednesday }}</td>
                <td class="text-center text-middle">{{ $staff->thursday }}</td>
                <td class="text-center text-middle">{{ $staff->friday }}</td>
                <td class="text-center text-middle">{{ $staff->saturday }}</td>
                <td class="text-center text-middle">{{ $staff->sunday }}</td>
            </tr>
        @endforeach
            <tr>
                <td class="text-center text-middle">
                    Total Minutes of <br>Alone Shifts
                </td>
                <td class="text-center text-middle">
                    {{ $monday_minutes }}
                </td>
                <td class="text-center text-middle">
                    {{ $tuesday_minutes }} 
                </td>
                <td class="text-center text-middle">
                    {{ $wednesday_minutes }}
                </td>
                <td class="text-center text-middle">
                    {{ $thursday_minutes }}
                </td>
                <td class="text-center text-middle">
                    {{ $friday_minutes }}
                </td>
                <td class="text-center text-middle">
                    {{ $saturday_minutes }}
                </td>
                <td class="text-center text-middle">
                    {{ $sunday_minutes }}
                </td>
            </tr>
            <tr>
                <td class="text-center text-middle">
                    Alone Shifts
                </td>
                <td class="text-center text-middle">
                    @foreach ($monday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
                <td class="text-center text-middle">
                    @foreach ($tuesday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
                <td class="text-center text-middle">
                    @foreach ($wednesday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
                <td class="text-center text-middle">
                    @foreach ($thursday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
                <td class="text-center text-middle">
                    @foreach ($friday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
                <td class="text-center text-middle">
                    @foreach ($saturday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
                <td class="text-center text-middle">
                    @foreach ($sunday as $shift)
                        <p>{{ gmdate("H:i:s", $shift['start']) }} to {{ gmdate("H:i:s", $shift['end']) }}</p>
                    @endforeach 
                </td>
            </tr>
            
        </tbody>        
    </table>  
@endsection