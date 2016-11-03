<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\RotaSlotStaff;


class TimesheetController extends Controller
{
    /**
     * Gets the timesheet.
     */
   public function getTimesheet()
   {
        try
        {
            // Get staff rota data for the timesheet
            $timesheet = RotaSlotStaff::staffRota();

            // Get which hours, per day, someone of staff will be alone on its shift
            $monday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("0"));
            $tuesday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("1"));
            $wednesday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("2"));
            $thursday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("3"));
            $friday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("4"));
            $saturday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("5"));
            $sunday = $this->getAloneShifts(RotaSlotStaff::weekdayShifts("6"));

            // Using these times, calculate how many minutes per day someone of staff will be alone on its shift
            $monday_minutes = $this->sumShiftMinutes($monday);
            $tuesday_minutes = $this->sumShiftMinutes($tuesday);
            $wednesday_minutes = $this->sumShiftMinutes($wednesday);
            $thursday_minutes = $this->sumShiftMinutes($thursday);
            $friday_minutes = $this->sumShiftMinutes($friday);
            $saturday_minutes = $this->sumShiftMinutes($saturday);
            $sunday_minutes = $this->sumShiftMinutes($sunday);

            // Send to view
            return view('welcome', ['timesheet' => $timesheet, 'monday' => $monday, 'tuesday' => $tuesday, 'wednesday' => $wednesday, 'thursday' => $thursday, 'friday' => $friday, 'saturday' => $saturday, 'sunday' => $sunday, 'monday_minutes' => $monday_minutes, 'tuesday_minutes' => $tuesday_minutes, 'wednesday_minutes' => $wednesday_minutes, 'thursday_minutes' => $thursday_minutes, 'friday_minutes' => $friday_minutes, 'saturday_minutes' => $saturday_minutes, 'sunday_minutes' => $sunday_minutes]);
        }
        catch(\Illuminate\Database\QueryException $e)       
        {   
            // Go to error page
            return view('error', ['msg' => $e->getMessage()]);
        }
   }

   /**
    * Gets the alone shifts. Blame this one for the time it took for this system to be ready.
    *
    * @param      <type>  $weekday_shifts  The weekday shifts
    *
    * @return     array   The alone shifts.
    */
   public function getAloneShifts($weekday_shifts)
   {    
        $aloneShifts = array();

        // Starts loop through shifts
        foreach ($weekday_shifts as $i => $current_shift) 
        {
            // Get start time in seconds
            $start = $this->getSeconds($current_shift->starttime);

            // Get end time in seconds
            $end = $this->getSeconds($current_shift->endtime);

            // If end comes before start then it means it's some time on the next day, so add 24h to it (86400 seconds)
            if($end < $start)
            {
                $end = $end + 86400;
            }

            // For each shift, compare it with subsequent shifts
            // The comparison is madethis way because we want to avoid repeat result (comparing 1st shift with 2nd shift is the same thing as compare 2nd shift with 1st shift)
            for ($j = $i + 1; $j < sizeof($weekday_shifts) ; $j++)
            {   
                // Set a temporary shift variable to be compared with the current shift (from the first loop)
                $other_shift = $weekday_shifts[$j];


                 // Get start time in seconds
                $other_start = $this->getSeconds($other_shift->starttime);

                // Get end time in seconds
                $other_end = $this->getSeconds($other_shift->endtime);

                // If end comes before start then it means it's some time on the next day, so add 24h to it (86400 seconds)
                if($other_end < $other_start)
                {
                    $other_end = $other_end + 86400;
                }

                // Now send both ranges to another function who's going to return which parts of them are not overlapping
                $tempAloneShifts = $this->getNotOverlapped($start, $end, $other_start, $other_end, $i, $j);

                // Add those times to original alone shifts array
                $aloneShifts = array_merge($aloneShifts, $tempAloneShifts);
            }
        }

        // This variable will store which indexes will be excluded from final results (because they won't be valid)
        $to_remove = array();

        // Start loop through those alone shifts from previous loop
        foreach ($aloneShifts as $i => &$aloneShift) 
        {
            // Flag to be used on "do while" loop
            $continue = true;

            // Create an array with from where these indexes came from
            $indexes = explode(",", $aloneShift['indexes']);

            // Repeat this until continue flag is set to false
            // It means that time it's not valid at all, or it's really a "alone shift" (compared with all other shifts)
            do 
            {   
                // Initiate comparison with all shifts for the given day
                foreach ($weekday_shifts as $j => $shift) 
                {   
                    // Do the validation just if this shift doesn't have the same index of one of those indexes inside alone shift variable
                    // It avoids camparions with itself
                    if (!in_array($j, $indexes)) 
                    {   
                        // Get start time in seconds
                        $start = $this->getSeconds($shift->starttime);

                        // Get end time in seconds
                        $end = $this->getSeconds($shift->endtime);
                        
                        // If end comes before start then it means it's some time on the next day, so add 24h to it (86400 seconds)
                        if($end < $start)
                        {
                            $end = $end + 86400;
                        }

                        // If both start time and end time (from alone shift) are inside a normal shift range, so it means it's not a valid alone shift
                        if($aloneShift['start'] >= $start && $aloneShift['start'] <= $end && $aloneShift['end'] >= $start && $aloneShift['end'] <= $end)
                        {
                            // Mark it as invalid and finish its loop
                            array_push($to_remove, $i);
                            $continue = false;
                        }
                        // If not, so it's a valid alone shift or at least part of it was "spilling" from the current normal shift range
                        else
                        {
                            // If part of it is overlapping, but another part not then "cut the edges", leave just the valid (alone) part of it
                            if(($aloneShift['start'] >= $start && $aloneShift['start'] <= $end) || ($aloneShift['end'] >= $start && $aloneShift['end'] <= $end))
                            {
                                // If start time is inside the range, set new start as the end of this normal shift range
                                if($aloneShift['start'] >= $start && $aloneShift['start'] <= $end)
                                {
                                    $aloneShift['start'] = $end;
                                }
                                
                                // If end time is inside the range, set new end as the start of this normal shift range
                                if($aloneShift['end'] >= $start && $aloneShift['end'] <= $end)
                                {
                                    $aloneShift['end'] = $start;
                                }
                            }
                            // Comes here if it's a valid alone shift, set continue flag as false and start validations on next one in the queue (or finish this loop and go to the next part of validations)
                            else
                            {
                                $continue = false;
                            }
                        }
                    }
                }
            } 
            // At the end, if continue flag isn't set as false, repeat the whole loop throught normal shift times, using the same alone shift (but now updated with new start and and)
            while($continue);
        }

        // After the end of validations, unsetting all those invalid indexes found on previous loop
        foreach ($to_remove as $key => $value)
        {
            unset($aloneShifts[$value]);
        }

        // Reindex array keys
        $aloneShifts = array_values($aloneShifts);

        // Tidy up alone shifts array, removing possible repeat values
        $array_length = sizeof($aloneShifts);

        for ($i = 0; $i < $array_length; $i++)
        {
            $aloneShifts[$i]['minutes'] = ($aloneShifts[$i]['end'] - $aloneShifts[$i]['start']) / 60;
            for ($j = 0; $j < $array_length; $j++) 
            { 
                if($i != $j)
                {
                    if(array_key_exists($i, $aloneShifts) && array_key_exists($j, $aloneShifts))
                    {
                        if($aloneShifts[$i]['start'] == $aloneShifts[$j]['start'] && $aloneShifts[$i]['end'] == $aloneShifts[$j]['end'])
                        {
                            unset($aloneShifts[$i]);
                        }
                    }  
                }
            }
        }

        // Return array with shifts where only one person was working
        return $aloneShifts;
   }


    /**
     * Gets not overlapped.
     *
     * @param      integer  $start        The start
     * @param      integer  $end          The end
     * @param      integer  $other_start  The other start
     * @param      integer  $other_end    The other end
     * @param      integer  $index_1      The index 1
     * @param      integer  $index_2      The index 2
     *
     * @return     array    $times        
     */
    public function getNotOverlapped($start, $end, $other_start, $other_end, $index_1, $index_2)
    {   
        $times = array();

        // If the 1st range of time given is not overlapping the 2nd one given then insert both on times array
        if(!($start >= $other_start && $start <= $other_end) && !($end >= $other_start && $end <= $other_end))
        {
            // Times array will store times with start time (in seconds), end time (also in seconds) and both indexes from where they came from
            // Those indexes will be used in the future to avoid comparisons with itself
            array_push($times, ['start' => $start, 'end' => $end, 'indexes' => $index_1.','.$index_2]);
            array_push($times, ['start' => $other_start, 'end' => $other_end, 'indexes' => $index_1.','.$index_2]);
        }
        // If not then do further comparisons to check which parts are overlapping
        else
        {
            // If 1st time range start is somewhere between 2nd time range 
            if($start >= $other_start && $start <= $other_end)
            {
                // Set new start and end, excluding the overlapped part
                $new_start = $other_start;
                $new_end = $start;

                // If new start and new end are the same, it means were not really overlapping, they were just adjacent and we shouldn't count them
                if($new_start != $new_end)
                {
                    array_push($times, ['start' => $new_start, 'end' => $new_end, 'indexes' => $index_1.','.$index_2]);
                }
            }
            // If it's not the 1st timerange start inside 2nd time range, but 2nd time range start inside the 1st one
            else if($other_start >= $start && $other_start <= $end)
            {
                // Set new start and end, excluding the overlapped part
                $new_start = $start;
                $new_end = $other_start;

                // If new start and new end are the same, it means were not really overlapping, they were just adjacent and we shouldn't count them
                if($new_start != $new_end)
                {
                    array_push($times, ['start' => $new_start, 'end' => $new_end, 'indexes' => $index_1.','.$index_2]);
                }
            }

            if($end >= $other_start && $end <= $other_end)
            {
                // Set new start and end, excluding the overlapped part
                $new_start = $end;
                $new_end = $other_end;

                // If new start and new end are the same, it means were not really overlapping, they were just adjacent and we shouldn't count them
                if($new_start != $new_end)
                {
                    array_push($times, ['start' => $new_start, 'end' => $new_end, 'indexes' => $index_1.','.$index_2]);
                }
            }
            else if($other_end >= $start && $other_end <= $end)
            {
                // Set new start and end, excluding the overlapped part
                $new_start = $other_end;
                $new_end = $end;

                // If new start and new end are the same, it means were not really overlapping, they were just adjacent and we shouldn't count them (4th ctrl+v in a row :S sorry)
                if($new_start != $new_end)
                {
                    array_push($times, ['start' => $new_start, 'end' => $new_end, 'indexes' => $index_1.','.$index_2]);
                }
            } 
        }

        // Return a first view of non overlapped times for those 2 ranges given
        // Return a empty array in case of no non overlapped times at all
        return $times;
    }


   /**
    * Gets the seconds.
    *
    * @param      string   $time_string  The time string
    *
    * @return     integer  The seconds.
    */
   public function getSeconds($time_string)
   {
        // Times coming from database are like '11:11:11'
        // This creates an array where its 1st index contais hours, the 2nd minutes and the 3rd seconds
        $time_exploded = explode(":", $time_string);

        // Calculate seconds from a given time
        $seconds = $time_exploded[0] * 3600 + $time_exploded[1] * 60 + $time_exploded[2];

        // Return time in seconds
        return $seconds;
   }

   /**
    *  Take shifts and sum how many minutes they have
    *
    * @param      array   $shifts  The shifts
    *
    * @return     float   $total   Total minutes
    */
   public function sumShiftMinutes($shifts)
   {
        $total = 0;

        foreach ($shifts as $key => $shift) {
            $total += ($shift['end'] - $shift['start']) / 60;
        }

        return $total;
   }
}
