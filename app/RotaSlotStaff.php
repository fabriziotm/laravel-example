<?php 

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class RotaSlotStaff extends Model {
	protected $table = 'rota_slot_staff';

	// to define wich attributes are mass assingable
	protected $fillable = array('rotaid', 'daynumber', 'staffid', 'slottype', 'starttime', 'endtime', 'workhours', 'premiumminutes', 'roletypeid', 'freeminutes', 'seniorcashierminutes', 'splitshifttimes');  

	// Considering valid just the ones where staffid is not null and slottype equals 'shift'
	public function scopeValid($query)
	{
		return $query->whereNotNull('staffid')->where('slottype','shift');
	}

	// Restrict to Mondays data
	public function scopeMonday($query)
	{
		return $query->where('daynumber',0);
	}

	// Restrict to Tuesdays data
	public function scopeTuesday($query)
	{
		return $query->where('daynumber',1);
	}

	// Restrict to Wednesdays data
	public function scopeWednesday($query)
	{
		return $query->where('daynumber',2);
	}

	// Restrict to Thursdays data
	public function scopeThursday($query)
	{
		return $query->where('daynumber',3);
	}

	// Restrict to Fridays data
	public function scopeFriday($query)
	{
		return $query->where('daynumber',4);
	}

	// Restrict to Saturdays data
	public function scopeSaturday($query)
	{
		return $query->where('daynumber',5);
	}

	// Restrict to Sundays data
	public function scopeSunday($query)
	{
		return $query->where('daynumber',6);
	}

	// Get fields for timesheet
	public function scopeTimesheet($query)
	{
		return $query->select(
        	'staffid',
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 0, concat(starttime, \' to \', endtime) , null)) as monday'),
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 1, concat(starttime, \' to \', endtime) , null)) as tuesday'),
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 2, concat(starttime, \' to \', endtime) , null)) as wednesday'),
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 3, concat(starttime, \' to \', endtime) , null)) as thursday'),
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 4, concat(starttime, \' to \', endtime) , null)) as friday'),
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 5, concat(starttime, \' to \', endtime) , null)) as saturday'),
        	 DB::raw('GROUP_CONCAT(IF(daynumber = 6, concat(starttime, \' to \', endtime) , null)) as sunday')
        )
        ->groupBy('staffid');
	}

	// Get total hours works per day
	// x3600 to convert to seconds then parse from seconds to time then substring to take just hours and minutes
	public function scopeWorkedHours($query)
	{
		return $query->select(
    		 DB::raw('\'Total Hours Worked\' as staffid'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 0, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as monday'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 1, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as tuesday'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 2, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as wednesday'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 3, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as thursday'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 4, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as friday'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 5, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as saturday'),
        	 DB::raw('SUBSTRING(SEC_TO_TIME(SUM(IF(daynumber = 6, TRUNCATE(workhours,2)*3600 , null))), 1, 5) as sunday')
        );
	}

	// Select shift start and end times
	public function scopeShift($query)
	{
		return $query->select('starttime','endtime');
	}

	// Get data for staff rota timesheet, joining results from timesheet and worked hours
	public static function staffRota()
	{
		$workhours = RotaSlotStaff::valid()->workedHours();
        $query = RotaSlotStaff::valid()->timesheet()->unionAll($workhours)->get();

        return $query;
	}

	// Get all valid shifts for each day
	public static function weekdayShifts($weekday)
	{
        $query = RotaSlotStaff::valid()->shift();

        switch ($weekday) {
        	case '0':
        		$query->monday();
        		break;
        	case '1':
        		$query->tuesday();
        		break;
        	case '2':
        		$query->wednesday();
        		break;
        	case '3':
        		$query->thursday();
        		break;
        	case '4':
        		$query->friday();
        		break;
        	case '5':
        		$query->saturday();
        		break;
        	case '6':
        		$query->sunday();
        		break;
        }

        return $query->get();
	}
}