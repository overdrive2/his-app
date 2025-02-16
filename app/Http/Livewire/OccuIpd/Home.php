<?php

namespace App\Http\Livewire\OccuIpd;

use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\Traits\DateTimeHelpers;
use App\Models\Ipd;
use App\Models\IpdBedmove;
use App\Models\IpdNurseShift;
use App\Models\IpdRecord;
use App\Models\IpdRecordList;
use App\Models\OccuIpd;
use App\Models\OccuIpdDetail;
use App\Models\OccuIpdRecord;
use App\Models\OccuIpdStaff;
use App\Models\OccuIpdStaffList;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Livewire\Component;

class Home extends Component
{
    use WithPerPagePagination, DateTimeHelpers;

    public OccuIpd $editing;
    public $filters = [
        'sdate' => '',
        'edate' => '',
        'shiftId' => 0,
    ];
    public $nurseshifts = [];
    public $wards = [];
    public $userId;
    public $from_ref_id;
    public $delId;
    public $occu_stime;
    public $occu_etime;

    protected $listeners = [
        'delete:occu-ipd' => 'delete'
    ];

    public function rules()
    {
        return [
            'editing.nurse_shift_date' => 'required',
            'editing.nurse_shift_time' => 'required',
            'editing.ward_id' => 'required|exists:wards,id',
            'editing.ipd_nurse_shift_id' => 'required|exists:ipd_nurse_shifts,id',
            'editing.occu_status_id' => '',
            'editing.note' => '',
            'editing.getin' => '',
            'editing.getnew' => '',
            'editing.getmove' => '',
            'editing.moveout' => '',
            'editing.discharge' => '',
            'editing.getout' => '',
            'editing.severe_1' => '',
            'editing.severe_2' => '',
            'editing.severe_3' => '',
            'editing.severe_4' => '',
            'editing.severe_5' => '',
            'editing.severe_6' => '',
            'editing.dc_appr' => '',
            'editing.dc_refer' => '',
            'editing.dc_agnt' => '',
            'editing.dc_esc' => '',
            'editing.dc_dead' => '',
            'editing.created_by' => '',
            'editing.updated_by' => '',
            'editing.delflag' => '',
            'editing.saved' => '',
            'editing.time_for_editing' => '',
            'editing.date_for_editing' => '',
        ];
    }

    public function makeBlank()
    {
        return OccuIpd::make([
            'nurse_shift_date' => $this->getCurrentDate(),
            'nurse_shift_time' => $this->getCurrentTime(),
            'getin' => 0,
            'getnew' => 0,
            'getmove' => 0,
            'moveout' => 0,
            'discharge' => 0,
            'getout' => 0,
            'occu_status_id' => 1,
            'severe_1' => 0,
            'severe_2' => 0,
            'severe_3' => 0,
            'severe_4' => 0,
            'severe_5' => 0,
            'severe_6' => 0,
            'dc_appr' => 0,
            'dc_refer' => 0,
            'dc_agnt' => 0,
            'dc_esc' => 0,
            'dc_dead' => 0,
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
            'delflag' => false,
            'saved' => false,
            'note' => '',
        ]);
    }

    public function mount()
    {
        // $this->perPage = 3;
        $this->editing = $this->makeBlank();
        $this->nurseshifts = IpdNurseShift::orderBy('display_order', 'asc')->get();
        $this->wards = auth()->user()->wards();
        $this->filters['edate'] = Carbon::parse($this->getCurrentDate())->format('d/m/Y');
        $this->filters['sdate'] = Carbon::parse($this->getCurrentDate())->format('d/m/Y');
        $this->userId = auth()->user()->id;
    }

    public function edit($id)
    {
        $this->editing = OccuIpd::find($id);
        $this->dispatchBrowserEvent('ipdmain-modal-show');
    }

    public function new()
    {
        $this->editing = $this->makeBlank();
        //dd($this->editing);
        $this->dispatchBrowserEvent('ipdmain-modal-show');
    }

    public function saveDraft()
    {
        //get start & end time to make shift
        $etnf = IpdNurseShift::where('id', $this->editing->ipd_nurse_shift_id)->value('etime');
        $this->occu_etime = Carbon::parse($this->editing->nurse_shift_date . ' ' . $etnf);
        $this->occu_stime = clone $this->occu_etime;
        $this->occu_stime->addSecond(-28801);
        //dd($stime,$etime);

        $this->saveOccuDetail();
        $this->saveSevere();
        $this->saveStaff();
        $this->saveIpdRecord();
        $this->savePercent();
    }

    public function saveStaff()
    {
        $occustaff = OccuIpdStaff::orderBy('display_order', 'asc')->get();
        foreach ($occustaff as $os) {
            OccuIpdStaffList::create([
                'occu_ipd_id' => $this->editing->id,
                'staff_id' => $os->id,
                'qty' => 0,
                'updated_by' => $this->userId,
                'created_by' => $this->userId,
            ]);
        }
    }

    public function saveIpdRecord()
    {
        $cc_record1 = IpdRecord::where('is_occu', true)
            ->orderBy('display_order', 'asc')
            ->get();
        foreach ($cc_record1 as $cc_rc1) {
            OccuIpdRecord::create([
                'occu_ipd_id' => $this->editing->id,
                'ipd_record_id' => $cc_rc1->id,
                'qty' => 0,
            ]);
        }

        $cc_record2 = IpdRecordList::selectRaw('ipd_record_id,count(*)')
            ->whereIn('ipd_id', OccuIpdDetail::where('occu_ipd_id', $this->editing->id)
                ->where('is_getout', true)->pluck('ipd_id'))
            ->whereIn('ipd_bedmove_id', OccuIpdDetail::where('occu_ipd_id', $this->editing->id)
                ->where('is_getout', true)->pluck('ipd_bedmove_id'))
            ->groupBy('ipd_record_id')
            ->orderBy('ipd_record_id')->get();
        foreach ($cc_record2 as $cc_rc2) {
            if ($cc_rc2->count > 0) {
                OccuIpdRecord::upsert(
                    ['occu_ipd_id' => $this->editing->id, 'ipd_record_id' => $cc_rc2->ipd_record_id, 'qty' => $cc_rc2->count],
                    ['occu_ipd_id', 'ipd_record_id'],
                    ['qty'],
                );
            }
        }
    }

    public function savePercent()
    {
        $bedc = Ward::where('id',$this->editing->ward_id)->get();
        $getout = OccuIpd::where('id',$this->editing->id)->get();

        $cc = $getout[0]->getout*100;
        $cc = $cc / $bedc[0]->bedcount;
        OccuIpd::where('id', $this->editing->id)->update(['occu_percent' => $cc]);
    }    

    public function saveOccuDetail()
    {
        //update to_ref_id to last shift
        $occuipd = OccuIpd::where('ward_id', $this->editing->ward_id)
            ->whereNull('to_ref_id')
            ->where('id', '<>', $this->editing->id)
            ->where('delflag',false)
            ->orderBy('nurse_shift_date', 'desc')
            ->orderBy('nurse_shift_time', 'desc')->first();

        //occu_ipd_type_id = 1	ยกมา
        $i_getin = 0;
        if ($occuipd != null) {

            OccuIpd::where('id', $occuipd->id)->update(['to_ref_id' => $this->editing->id]);
            $i_getin = $occuipd->getout;
            OccuIpd::where('id', $this->editing->id)->update(['getin' => $i_getin]);

            $bedmoves_t1 = OccuIpdDetail::where('occu_ipd_id', $occuipd->id)
                ->where('is_getout', true)
                ->orderBy('id', 'asc')->get();
            foreach ($bedmoves_t1 as $bm1) {

                OccuIpdDetail::create([
                    'occu_ipd_id' => $this->editing->id,
                    'ipd_id' => $bm1->ipd_id,
                    'occu_ipd_type_id' => 1,
                    'is_getout' => true,
                    'ipd_bedmove_id' => $bm1->ipd_bedmove_id,
                    'updated_by' => $this->userId,
                    'created_by' => $this->userId,
                    'saved' => false,
                    'ipd_admit_type_id' => $bm1->ipd_admit_type_id,
                    'ipd_severe_id' => $bm1->ipd_severe_id,
                ]);
            }
        }

        //occu_ipd_type_id = 2	รับใหม่
        $bedmoves_t2 = IpdBedmove::wherebetween('moved_at', [$this->occu_stime, $this->occu_etime])
            ->where('bedmove_type_id', '1')
            ->where('ward_id', $this->editing->ward_id)
            ->where('delflag', false)
            ->orderBy('moved_at', 'asc')->get();
        $i_getnew = 0;
        foreach ($bedmoves_t2 as $bm2) {
            OccuIpdDetail::create([
                'occu_ipd_id' => $this->editing->id,
                'ipd_id' => $bm2->ipd_id,
                'occu_ipd_type_id' => 2,
                'is_getout' => 'Y',
                'ipd_bedmove_id' => $bm2->id,
                'updated_by' => $this->userId,
                'created_by' => $this->userId,
                'saved' => false,
                'ipd_admit_type_id' => $bm2->ipd_admit_type_id,
                'ipd_severe_id' => $bm2->ipd_severe_id,
            ]);
            $i_getnew++;
        }
        OccuIpd::where('id', $this->editing->id)->update(['getnew' => $i_getnew]);

        //occu_ipd_type_id = 3	รับย้าย
        $bedmoves_t3 = IpdBedmove::wherebetween('moved_at', [$this->occu_stime, $this->occu_etime])
            ->where('bedmove_type_id', '2')
            ->where('delflag', false)
            ->where('ward_id', $this->editing->ward_id)
            ->orderBy('moved_at', 'asc')->get();
        $i_getmove = 0;
        foreach ($bedmoves_t3 as $bm3) {

            OccuIpdDetail::where('occu_ipd_id', $this->editing->id)
                ->where('ipd_id', $bm3->ipd_id)->update(['is_getout' => false]);
            OccuIpdDetail::create([
                'occu_ipd_id' => $this->editing->id,
                'ipd_id' => $bm3->ipd_id,
                'occu_ipd_type_id' => 3,
                'is_getout' => 'Y',
                'ipd_bedmove_id' => $bm3->id,
                'updated_by' => $this->userId,
                'created_by' => $this->userId,
                'saved' => false,
                'ipd_admit_type_id' => $bm3->ipd_admit_type_id,
                'ipd_severe_id' => $bm3->ipd_severe_id,
            ]);
            $i_getmove++;
        }
        OccuIpd::where('id', $this->editing->id)->update(['getmove' => $i_getmove]);

        //occu_ipd_type_id = 4	ย้าย Ward
        $bedmoves_t4 = IpdBedmove::wherebetween('moved_at', [$this->occu_stime, $this->occu_etime])
            ->where('bedmove_type_id', '3')
            ->where('delflag', false)
            ->where('ward_id', $this->editing->ward_id)
            ->orderBy('moved_at', 'asc')->get();
        $i_moveout = 0;
        foreach ($bedmoves_t4 as $bm4) {

            OccuIpdDetail::where('occu_ipd_id', $this->editing->id)
                ->where('ipd_id', $bm4->ipd_id)->update(['is_getout' => false]);
            OccuIpdDetail::create([
                'occu_ipd_id' => $this->editing->id,
                'ipd_id' => $bm4->ipd_id,
                'occu_ipd_type_id' => 4,
                'is_getout' => false,
                'ipd_bedmove_id' => $bm4->id,
                'updated_by' => $this->userId,
                'created_by' => $this->userId,
                'saved' => false,
                'ipd_admit_type_id' => $bm4->ipd_admit_type_id,
                'ipd_severe_id' => $bm4->ipd_severe_id,
            ]);
            $i_moveout++;
        }
        OccuIpd::where('id', $this->editing->id)->update(['moveout' => $i_moveout]);

        //occu_ipd_type_id = 5	Discharge
        $bedmoves_t5 = IpdBedmove::wherebetween('moved_at', [$this->occu_stime, $this->occu_etime])
            ->where('bedmove_type_id', '5')
            ->where('delflag', false)
            ->where('ward_id', $this->editing->ward_id)
            ->orderBy('moved_at', 'asc')->get();
        $i_dc = 0;
        foreach ($bedmoves_t5 as $bm5) {

            OccuIpdDetail::where('occu_ipd_id', $this->editing->id)
                ->where('ipd_id', $bm5->ipd_id)->update(['is_getout' => false]);
            OccuIpdDetail::create([
                'occu_ipd_id' => $this->editing->id,
                'ipd_id' => $bm5->ipd_id,
                'occu_ipd_type_id' => 5,
                'is_getout' => false,
                'ipd_bedmove_id' => $bm5->id,
                'updated_by' => $this->userId,
                'created_by' => $this->userId,
                'saved' => false,
                'ipd_admit_type_id' => $bm5->ipd_admit_type_id,
                'ipd_severe_id' => $bm5->ipd_severe_id,
            ]);
            $i_dc++;
        }
        OccuIpd::where('id', $this->editing->id)->update(['discharge' => $i_dc]);

        $i_getout = ($i_getin + $i_getnew + $i_getmove) - ($i_moveout - $i_dc);
        OccuIpd::where('id', $this->editing->id)->update(['getout' => $i_getout]);
    }

    public function saveSevere()
    {
        //update severe
        $cc_severe = Ipd::selectRaw('ipd_severe_id,count(*)')
            ->whereIn('id', OccuIpdDetail::where('occu_ipd_id', $this->editing->id)
                ->where('is_getout', true)->pluck('ipd_id'))
                ->whereIn('ipd_severe_id',[1,2,3,4,5,6])
            ->groupBy('ipd_severe_id')->get();
        foreach ($cc_severe as $cc_sv) {
            OccuIpd::where('id', $this->editing->id)
                ->update(['severe_' . $cc_sv->ipd_severe_id => $cc_sv->count]);
        }
    }

    public function save()
    {
        //dd($this->editing);
        $checkex = OccuIpd::where('nurse_shift_date', $this->editing->nurse_shift_date)
            ->where('ward_id', $this->editing->ward_id)
            ->where('ipd_nurse_shift_id', $this->editing->ipd_nurse_shift_id)
            ->where('delflag', false)->count();

        if ($checkex > 0) { 
            return $this->dispatchBrowserEvent('swal:error', [
                'title' => 'พบการส่งเวรซ้ำ',
                'text' => 'กรุณาตรวจสอบการส่งเวรซ้ำ',
            ]);
        } else {
            $this->withValidator(function (Validator $validator) {
                $validator->after(function ($validator) {
                    if ($validator->errors()->isNotEmpty()) {
                        $errorMsg =  $validator->errors()->messages();
                        $this->dispatchBrowserEvent('err-message', ['errors' => json_encode($errorMsg)]);
                    }
                });
            })->validate();

            $editmode = $this->editing->id ? true : false;
            $saved = $this->editing->save();

            if (!$saved)
                return $this->dispatchBrowserEvent('swal:error', [
                    'title' => '',
                    'text' => 'ไม่สามารถบันทึกส่งเวรได้ กรุณาตรวจสอบ',
                ]);

            if (!$editmode) {
                $this->saveDraft();
            }

            $this->dispatchBrowserEvent('ipdmain-modal-close', [
                'msgstatus' => 'done',
            ]);
        }
    }

    public function deleteConfirm($id)
    {
        $this->delId = $id;
        $this->dispatchBrowserEvent('delete:confirm', [
            'action' => 'delete:occu-ipd',
        ]);
    }

    public function delete()
    {
        OccuIpd::where('id', $this->delId)->update(['delflag' => true]);
        OccuIpd::where('to_ref_id', $this->delId)->update(['to_ref_id' => null]);

        $this->dispatchBrowserEvent('toastify');
    }

    public function setDate($date)
    {
        $date = Carbon::createFromFormat('d/m/Y',  $date);
        return $date->format('Y-m-d');
    }

    public function getRowsQueryProperty()
    {
        $query =  OccuIpd::query()
            ->when($this->filters['shiftId'], function ($query, $sid) {
                return $query->where('ipd_nurse_shift_id', $sid);
            })
            ->when($this->filters['sdate'] && $this->filters['edate'], function ($query) {
                $sdate = $this->setDate($this->filters['sdate']);
                $edate = $this->setDate($this->filters['edate']);

                return $query->whereBetween('nurse_shift_date', [$sdate, $edate]);
            })
            ->where('delflag', false)
            ->orderBy('nurse_shift_date', 'asc')
            ->orderBy('nurse_shift_time', 'asc');
        return $query;
    }

    public function getRowCountProperty()
    {
        return $this->rowsQuery->count();
    }

    public function getRowsProperty()
    {
        return $this->applyPagination($this->rowsQuery);
    }

    public function render()
    {
        return view(
            'livewire.occu-ipd.home',
            [
                'rows' => $this->rows,
            ]
        );
    }
}
