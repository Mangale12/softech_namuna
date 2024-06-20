<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\Udhyog;
use App\Models\VoucherDrCr;
use Illuminate\Support\Facades\DB;
use App\Models\WorkerList;
use App\Models\WorkerPosition;
use App\Models\WorkerTypes;
use App\Models\FinanceTitle;
class UdhyogDudhController extends DM_BaseController
{
    protected $panel = 'Udhyog Dudh';
    protected $base_route = 'admin.udhyog.dudh.fianance';
    protected $view_path = 'admin.voucher';
    protected $view_path_worker_types = 'admin.worker-types';
    protected $view_path_worker_position = 'admin.worker-position';
    protected $view_path_worker_list = 'admin.worker-list';
    protected $model;
    protected $table;

    // public function __construct(UdhyogAchar $model)
    // {
    //     $this->model = $model;
    // }

    function fianance(){
        $data['vouchers'] = [];
        $data['udhyog_voucher'] = [];

        if (request()->is('admin/udhyog/dudh/fianance/index')) {
            $udhyog = Udhyog::where('name', 'Dudh')->first();
            $data['vouchers'] = Voucher::with('voucherType')->where('udhyog_id',$udhyog->id)->get();
            $data['udhyog_voucher'] = 'दुध';

        }
        $data['lekha_shirsak'] = Voucher::with('lekhaShirsak')->get();
        return view(parent::loadView($this->view_path . '.index'),compact('data'));
    }

    function fianance_create(){
        $voucherModel = new Voucher();
        $data['udhyog'] = [];
        $data['path'] = [];
        if (request()->is('admin/udhyog/dudh/fianance/create')) {
            $data['udhyog'] = Udhyog::where('name', 'Dudh')->first();
            $data['path'] = 'दुध';
            // $data['vouchers'] = Voucher::with('voucherType')->where('udhyog_id',$udhyog->id)->get();
        }

        $currentDate = date('Y-m-d');
        //conver English date to Nepali date   // Thaman 2078-01-01
        $data['nep_date_unicode']  = datenepUnicode($currentDate, 'nepali');
        $data['get_sirshak']       = $voucherModel->getLekhaSirshak();
        $data['bhoucher_no']       = $voucherModel->getbhoucherNo();
        $data['fiscal']            = $voucherModel->getFiscal();
        $data['voucher_type']      = $voucherModel->getVoucherType();
        $data['lekha_shirshak']    = $voucherModel->getLekhaSirshak();
        $data['titles']            = FinanceTitle::where('status', 1)->get();
        return view(parent::loadView($this->view_path . '.create'), compact('data'));
    }

    function store(Request $request){
        $request->validate([
            'date' => 'required',
            'voucher_type' => 'required',
        ]);

        $voucher = new Voucher();
        $voucher->storeData($request, $request->date, $request->voucher_type, $request->lekha_shirshak, $request->bhoucher_no, $request->fiscal, $request->remarks, $request->status,$request->total_debit,$request->total_credit, $request->title, $request->dr, $request->cr, $request->bhoucher_name,$request->udhyog);
        return redirect()->route($this->base_route . '.index')->with('success', 'Voucher created successfully');
    }

    function view_report($id){
        $voucher = Voucher::findOrFail($id);
        $data['dr_cr_details'] = VoucherDrCr::where('voucher_id', $voucher->id)->get();
        $sums = VoucherDrCr::select(
            'voucher_id',
            DB::raw('SUM(dr) as dr_total'),
            DB::raw('SUM(cr) as cr_total')
        )
        ->groupBy('voucher_id')
        ->get();
        foreach($sums as $sum){
            if($sum['voucher_id'] == $id){
                $data['dr_cr_sum'] = $sum;
            }
        }

        $data['voucher'] = $voucher;
        return view(parent::loadView($this->view_path . '.report'), compact('data'));
    }

      //worer method start

      function workersType(){
        try {
                $this->panel = 'Udhyog Dudh Workers Type';
                $workers_base_route = 'admin.udhyog.dudh.workers';
                $this->base_route = 'admin.udhyog.dudh.workers';
                $udhyog = Udhyog::where('name', 'Dudh')->first();
                $data['rows'] = WorkerTypes::orderBy('id', 'ASC')->where('udhyog_id',$udhyog->id)->paginate(10);
                return view(parent::loadView($this->view_path_worker_types . '.index'), compact('udhyog','data','workers_base_route'));
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['message' => 'Udhyog Not Found'], 404);
        }
    }
    function workersTypeCreate(){
        try{
                $_panel = 'Udhyog Dudh Workers';
                $this->panel = 'Udhyog Dudh Workers';
                $udhyog = Udhyog::where('name', 'Dudh')->first();
                $this->base_route = 'admin.udhyog.dudh.workers.workerstype';
                return view(parent::loadView($this->view_path_worker_types . '.create'), compact('udhyog','_panel'));
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['message' => 'Udhyog Not Found'], 404);
        }
    }
    function workersTypeStore(Request $request){
        $workerType = new WorkerTypes();
        $request->validate([$workerType->getRules()]);
        $workerType->storeData($request, $request->types, $request->status, $request->udhyog_id);
        return redirect()->route('admin.udhyog.dudh.workers.workerstype.index')->with('success','Worker added successfully');
    }

    function workersPosition(){
        try {
                $this->panel = 'Udhyog Dudh Workers Position';
                $this->base_route = 'admin.udhyog.dudh.workers';
                $udhyog = Udhyog::where('name', 'Dudh')->first();
                $data['rows'] = WorkerPosition::orderBy('id', 'ASC')->where('udhyog_id',$udhyog->id)->paginate(10);
                return view(parent::loadView($this->view_path_worker_position . '.index'), compact('udhyog','data'));
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['message' => 'Udhyog Not Found'], 404);
        }
    }
    function workersPositionCreate(){
        try{
                $_panel = 'Udhyog Dudh Workers';
                $this->panel = 'Udhyog Dudh Workers';
                $udhyog = Udhyog::where('name', 'Dudh')->first();
                $this->base_route = 'admin.udhyog.dudh.workers.workersposition';
                $data['rows'] = WorkerTypes::where('udhyog_id',$udhyog->id)->get();
                return view(parent::loadView($this->view_path_worker_position . '.create'), compact('udhyog','_panel','data'));
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['message' => 'Udhyog Not Found'], 404);
        }
    }
    function workersPositionStore(Request $request){
        $workerPosition = new WorkerPosition();
        // $request->validate($workerPosition->getRules(), $workerPosition->getMessage());
        $workerPosition->storeData($request, $request->addmore, $request->udhyog_id);
        return redirect()->route('admin.udhyog.dudh.workers.workersposition.index')->with('success','Worker added successfully');
    }

    function workersList(){
        try {
            if (request()->is('admin/udhyog/dudh/workers/workers-list')) {
                // dd("test");
                $this->panel = 'Udhyog Dudh Workers List';
                $this->base_route = 'admin.udhyog.dudh.workers';
                $udhyog = Udhyog::where('name', 'Dudh')->first();
                $data['rows'] = WorkerList::orderBy('id', 'ASC')->where('udhyog_id',$udhyog->id)->paginate(10);
                return view(parent::loadView($this->view_path_worker_list . '.index'), compact('udhyog','data'));
            }
            //code...
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['message' => 'Udhyog Not Found'], 404);
        }
    }
    function workersListCreate(){
        try{
                $this->panel = 'Udhyog Dudh Workers';
                $_panel = 'Udhyog Dudh Workers';
                $udhyog = Udhyog::where('name', 'Dudh')->first();
                $data['rows'] = WorkerPosition::where('udhyog_id', $udhyog->id)->get();
                $this->base_route = 'admin.udhyog.dudh.workers.workerslist';
                return view(parent::loadView($this->view_path_worker_list . '.create'), compact('udhyog','_panel','data'));
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['message' => 'Udhyog Not Found'], 404);
        }
    }
    function workersListStore(Request $request){
        $workerList = new WorkerList();
        // $this->base_route =
        $request->validate($workerList->getRules(), $workerList->getMessage());
        if ($workerList->storeData($request, $request->full_name, $request->mobile, $request->gender, $request->address, $request->day_of_joining, $request->worker_position_id, $request->salary, $request->bhatta, $request->image,$request->udhyog_id)) {
            session()->flash('alert-success', 'कामदार पद अध्यावधिक भयो ।');
        } else {
            session()->flash('alert-danger', 'कामदार पद अध्यावधिक हुन सकेन ।');
        }
        return redirect()->route('admin.udhyog.dudh.workers.workerslist.index');
    }
}
