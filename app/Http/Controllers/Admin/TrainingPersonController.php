<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TrainingPerson;
use App\Models\Talim;
use App\Models\TrainingPhase;
class TrainingPersonController extends DM_BaseController
{
    protected $panel = 'Training Person';
    protected $base_route = 'admin.training_person';
    protected $view_path = 'admin.training_person';
    protected $model;
    protected $table;

    public function __construct(TrainingPerson $model)
    {
        $this->model = $model;
        $this->middleware('permission:view TrainingPerson')->only(['index', 'show']);
        $this->middleware('permission:create TrainingPerson')->only(['create', 'store']);
        $this->middleware('permission:edit TrainingPerson')->only(['edit', 'update']);
        $this->middleware('permission:delete TrainingPerson')->only('destroy');
    }

    public function index(Request $request)
    {
        $data['rows'] =  $this->model->getData();
        return view(parent::loadView($this->view_path . '.index'), compact('data'));
    }
    function create(Request $request){
        $data['talim'] = null;
        if($request->has('talim')){
            $talim = Talim::where('title', $request->talim)->first();
            if($talim){
                $data['talim'] = $talim;
            }
        }
        return view(parent::loadView($this->view_path . '.create'), compact('data'));

    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'name' => 'required',
        //     'email' => 'required|email|unique:persons,email',
        //     'phone' => 'required',
        //     'address' => 'required',
        //     'trainings' => 'array',
        // ]);
        $data = [];

        // Validate and sanitize input data as needed
        if($request->has('talim')){
            $talim = Talim::where('title', $request->talim)->first();

            if($talim){
                foreach ($request->name as $key => $name) {
                    if (isset($request->phone[$key]) && isset($request->email[$key]) && isset($request->address[$key])) {
                        $phaseId = TrainingPhase::where('id', $request->training_phase[$key])->first();
                        $data[] = [
                            'name' => $name,
                            'phone' => $request->phone[$key],
                            'email' => $request->email[$key],
                            'address' => $request->address[$key],
                            'phase_id' => $phaseId != null ? $phaseId : null,
                        ];
                    } else {
                        // Handle missing or incomplete data
                        // You can log an error, skip the iteration, or take appropriate action
                    }
                }

                try {
                    DB::beginTransaction();

                    foreach ($data as $personData) {
                        $person = TrainingPerson::create($personData);
                        $person->trainings()->attach($talim);
                        $person->phases()->attach($personData['phase_id'], ['talim_id' => $talim->id]);
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();
                    dd($e);
                    return redirect()->back()->with('error', 'Failed to create Training Persons. ' . $e->getMessage());
                }
            }else{
                return back();
            }
        }else{
            return back();
        }


        // $person->trainings()->sync($request->trainings);
        return redirect()->route('admin.talim.index');
        return view(parent::loadView($this->view_path . '.index'), compact('data'));
    }

    function edit($id){
        $data['talim'] = Talim::get();
        $data['row'] = $this->model->findOrFail($id);
        return view(parent::loadView($this->view_path . '.edit'), compact('data'));
    }

    function update(Request $request, TrainingPerson $id){
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
        ]);

        $id->update($request->all());
        return redirect()->route($this->base_route.'.index');
    }

    function view($id){
        $data['rows'] = TrainingPerson::with(['trainings.phases'])->findOrFail($id);
    // Dump and die to inspect the person object
        // $person = TrainingPerson::find($id);
        // $trainings = $person->trainings;
        // $data['trainings'] = $person->trainings()->with('phases')->get();
        return view(parent::loadView($this->view_path . '.view'), compact('data'));

    }
}
