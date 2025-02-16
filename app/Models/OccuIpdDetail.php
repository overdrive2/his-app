<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccuIpdDetail extends Model
{
    use HasFactory;

    protected $appends = ['occu_ipd_type_name','ipd_admit_type_name','ipd_severe_name'];

    protected $fillable = [
        'occu_ipd_id',
        'ipd_id',
        'occu_ipd_type_id',
        'is_getout',
        'ipd_bedmove_id',      
        'updated_by',
        'created_by',
        'saved', 
        'ipd_admit_type_id',   
        'ipd_severe_id',                      
    ];

    public function getIpdSevereNameAttribute()
    {
        $data = IpdSevere::find($this->ipd_severe_id);
    
        return $data ? $data->name : '';
    } 

    public function getOccuIpdTypeNameAttribute()
    {
        $data = OccuIpdType::find($this->occu_ipd_type_id);
    
        return $data ? $data->type_name : '';
    } 

    public function getIpdAdmitTypeNameAttribute()
    {
        $data = IpdAdmitType::find($this->ipd_admit_type_id);
    
        return $data ? $data->name : '';
    } 

    public function bedmove()
    {        
        return $this->ipd_bedmove_id ? IpdBedmove::find($this->ipd_bedmove_id) : [];
    } 

    protected static function boot()
    {
        parent::boot();

        self::deleted(function($model){
            //dd($model);
            $occuipd_update = OccuIpd::find($model->occu_ipd_id);
            $occuipd_update->getnew = $occuipd_update->getnew-1;
            $occuipd_update->save();
        });

        //////////////original
        // self::saved(function($model){
        //     OccuIpd::where('id', $model->ipd_id)
        //         ->update([
        //             'current_bedmove_id' => IpdBedmove::where('ipd_id', $model->ipd_id)
        //                 ->where('delflag', false)
        //                 ->orderBy('movedate', 'desc')
        //                 ->orderBy('movetime', 'desc')
        //                 ->value('id')
        //         ]);
        //     if($model->bedmove_type_id == config('ipd.moverecp')) {
        //         IpdBedmove::where('id', $model->from_ref_id)->update([
        //             'to_ref_id' => $model->id
        //         ]);
        //     }
        // });

    }
}
