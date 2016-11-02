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

	public function scopeMonday($query)
	{
		return $query->where('daynumber',0);
	}

	public function scopeTuesday($query)
	{
		return $query->where('daynumber',1);
	}

	public function scopeWednesday($query)
	{
		return $query->where('daynumber',2);
	}

	public function scopeThurdays($query)
	{
		return $query->where('daynumber',3);
	}

	public function scopeFriday($query)
	{
		return $query->where('daynumber',4);
	}

	public function scopeSaturday($query)
	{
		return $query->where('daynumber',5);
	}

	public function scopeSunday($query)
	{
		return $query->where('daynumber',6);
	}

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

	public function scopeWorkHours($query)
	{
		return $query->select(
    		 DB::raw('\'Total Hours Worked\' as staffid'),
        	 DB::raw('SUM(IF(daynumber = 0, TRUNCATE(workhours,2) , null)) as monday'),
        	 DB::raw('SUM(IF(daynumber = 1, TRUNCATE(workhours,2) , null)) as tuesday'),
        	 DB::raw('SUM(IF(daynumber = 2, TRUNCATE(workhours,2) , null)) as wednesday'),
        	 DB::raw('SUM(IF(daynumber = 3, TRUNCATE(workhours,2) , null)) as thursday'),
        	 DB::raw('SUM(IF(daynumber = 4, TRUNCATE(workhours,2) , null)) as friday'),
        	 DB::raw('SUM(IF(daynumber = 5, TRUNCATE(workhours,2) , null)) as saturday'),
        	 DB::raw('SUM(IF(daynumber = 6, TRUNCATE(workhours,2) , null)) as sunday')
        );
	}

	public function scopeAloneMinutes($query)
	{
		return $query->select(
    		 DB::raw('\'Total Minutes Staff Alone\' as staffid'),
        	 DB::raw('SUM(IF(daynumber = 0, TRUNCATE(workhours,2) , null)) as monday'),
        	 DB::raw('SUM(IF(daynumber = 1, TRUNCATE(workhours,2) , null)) as tuesday'),
        	 DB::raw('SUM(IF(daynumber = 2, TRUNCATE(workhours,2) , null)) as wednesday'),
        	 DB::raw('SUM(IF(daynumber = 3, TRUNCATE(workhours,2) , null)) as thursday'),
        	 DB::raw('SUM(IF(daynumber = 4, TRUNCATE(workhours,2) , null)) as friday'),
        	 DB::raw('SUM(IF(daynumber = 5, TRUNCATE(workhours,2) , null)) as saturday'),
        	 DB::raw('SUM(IF(daynumber = 6, TRUNCATE(workhours,2) , null)) as sunday')
        );
	}

	public static function staffRota()
	{
		$workhours = RotaSlotStaff::valid()->workHours();
		$aloneminutes = RotaSlotStaff::valid()->aloneMinutes();
        $query = RotaSlotStaff::valid()->timesheet()->unionAll($workhours)->unionAll($aloneminutes)->get();

        return $query;
	}
}