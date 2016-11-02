<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\RotaSlotStaff;


class TimesheetController extends Controller
{
   public function getTimesheet()
   {
        try
        {
            $timesheet = RotaSlotStaff::staffRota();
            return view('welcome', ['timesheet' => $timesheet]);
        }
        catch(\Illuminate\Database\QueryException $e)       
        {
            return view('error', ['msg' => $e->getMessage()]);
        }
   }
}
