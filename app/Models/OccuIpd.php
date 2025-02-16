<?php

namespace App\Models;

use App\Helpers\FunctionDateTimes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccuIpd extends Model
{
    use HasFactory, FunctionDateTimes;

    protected $appends = ['date_for_editing', 'time_for_editing', 
        'occu_status_name', 'ipd_nurse_shift_name', 'ward_name'];

    protected $fillable = [
        'nurse_shift_date',
        'nurse_shift_time',
        'ward_id',
        'getin',
        'getnew',
        'getmove',
        'moveout',
        'discharge',
        'getout',
        'occu_status_id',
        'note',
        'ipd_nurse_shift_id',
        'severe_1',
        'severe_2',
        'severe_3',
        'severe_4',
        'severe_5',
        'severe_6',
        'dc_appr',
        'dc_refer',
        'dc_agnt',
        'dc_esc',
        'dc_dead',
        'delflag',
        'saved',
        'updated_by',
        'created_by',
        'created_at',
        'updated_at',
        'to_ref_id',
        'occu_percent',
    ];

    public function getOccuStatusNameAttribute()
    {
        $data = OccuStatus::find($this->occu_status_id);

        return $data ? $data->status_name : '';
    }

    public function getIpdNurseShiftNameAttribute()
    {
        $data = IpdNurseShift::find($this->ipd_nurse_shift_id);

        return $data ? $data->nurse_shift_name : '';
    }

    public function getWardNameAttribute()
    {
        $data = Ward::find($this->ward_id);

        return $data ? $data->name : '';
    }

    public function getUpdatedNameAttribute()
    {
        $data = Officer::select('fullname', 'licenseno')
            ->where('id', $this->updated_by)->first();

        return $data ? $data->fullname . '(' . $data->licenseno . ')' : '';
    }

    public function getCreatedNameAttribute()
    {
        $data = Officer::select('fullname', 'licenseno')
            ->where('id', $this->created_by)->first();

        return $data ? $data->fullname . '(' . $data->licenseno . ')' : '';
    }

    public function getDateForEditingAttribute()
    {
        return Carbon::parse($this->nurse_shift_date)->format('d/m/Y');
    }

    public function setDateForEditingAttribute($value)
    {
        $date = Carbon::createFromFormat('d/m/Y',  $value);
        $this->nurse_shift_date = $date->format('Y-m-d');
    }

    public function getTimeForEditingAttribute()
    {
        return Carbon::parse($this->nurse_shift_time)->format('H:i');
    }

    public function setTimeForEditingAttribute($value)
    {
        $time = Carbon::createFromFormat('H:i',  $value);
        $this->nurse_shift_time = $time->format('H:i');
    }

    public function getDateForThaiAttribute()
    {
        return $this->thai_date_short_number2(Carbon::parse($this->nurse_shift_date));
    }

    public function detail()
    {
        return OccuIpdDetail::where('occu_ipd_id',$this->id)
            ->where('is_getout',true)
            ->where('saved',true);
    }
}
