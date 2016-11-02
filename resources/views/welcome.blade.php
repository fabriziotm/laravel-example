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
                    <td class="text-center">{{ $staff->staffid }}</td>
                    <td class="text-center">{{ $staff->monday }}</td>
                    <td class="text-center">{{ $staff->tuesday }}</td>
                    <td class="text-center">{{ $staff->wednesday }}</td>
                    <td class="text-center">{{ $staff->thursday }}</td>
                    <td class="text-center">{{ $staff->friday }}</td>
                    <td class="text-center">{{ $staff->saturday }}</td>
                    <td class="text-center">{{ $staff->sunday }}</td>
                </tr>
            @endforeach
        </tbody>        
    </table>   
@endsection