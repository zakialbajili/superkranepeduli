<?php

namespace App\Http\Controllers\backend\master;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

use App\Traits\ModuleTraits;
use App\Traits\PushNotification;
use App\Traits\StatusTraits;
use Illuminate\Support\Facades\Auth;

use DB;
use Str;

class TaskController extends Controller
{
    use ModuleTraits, StatusTraits, PushNotification;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headertag = 'Task Data';
        $headername = 'Daftar Task';
        $headerlink = '';
        $parentname = 'Halaman Utama';
        $parentlink = '';

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];



        $costsubmissionAuthorizedStatus = $this->getStatusAuthorized('admin.costsubmission.index');
        $costsubmissionreportAuthorizedStatus = $this->getStatusAuthorized('admin.costsubmissionreport.index');
        // $authorizeStatus = array_merge($costsubmissionAuthorizedStatus['authorizedStatus'], $costsubmissionreportAuthorizedStatus['authorizedStatus']);
        // $rawStatus = DB::table('tprojectmaster')
        //     ->whereIn('pk_projectmaster_id', $authorizeStatus)
        //     ->get();
$rawStatus="";
        $rawCosttype = DB::table('tprojectmaster')
            ->select('pk_projectmaster_id', 'name')
            ->orderBy('name', 'asc')
            ->where('master_category', 'Cost Submission')
            ->get();

        return view('backend.master.task.index', compact('headerparam', 'rawStatus', 'rawCosttype'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function taskdatatables(Request $request)
    {
        // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $datafilter = [
            "transactiondate" => "",
            "status" => "",
        ];
        if (isset($request['data'][0])) {
            $datafilter = $request['data'][0];
        }
        $columns = [
            'date',
            'type',
            'description',
            'status',
        ];
        $columnkey = 'pk_cost_submission_report_id';
        $table = 'vcost_submission_report';
        $order = [$columns[1], 'desc'];

        $selectColumn = $columns;
        $selectColumn[] = $columnkey;
        $selectColumn[] = 'full_name';
        $selectColumn[] = 'employee_position';
        $searchColumn = $selectColumn;

        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        $costsubmissionAuthorizedStatus = $this->getStatusAuthorized('admin.costsubmission.index');
        $costsubmissionreportAuthorizedStatus = $this->getStatusAuthorized('admin.costsubmissionreport.index');
        $MaintenanceAuthorizedStatus = $this->getStatusAuthorized('admin.maintenanceorder.index');

        $datacostsubmission = DB::table('vcostsubmission')
            ->select(
                "pk_cost_submission_id as id",
                DB::RAW("'Pengajuan Dana' as type"),
                "date",
                DB::RAW('CONCAT(no_reg," ",employee_no," ",full_name," - ",wo_name," Total : ",format(total,2)) as description'),
                "status_name as status"
            )
            ->orWhere(function ($query) use ($request) {
                $query->orWhere("no_reg", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("employee_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("full_name", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("wo_name", 'like', "%" . $request['search']['value'] . "%");
            })
            ->whereIn('fk_status_id', $costsubmissionAuthorizedStatus['authorizedStatus'])
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('date', ">=", $datadate[0]);
                        $query->Where('date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            });

        $datacostsubmissionreport = DB::table('vcost_submission_report')
            ->select(
                "pk_cost_submission_report_id as id",
                DB::RAW("'Pemakaian Dana' as type"),
                "reg_date as date",
                DB::RAW('CONCAT(no_reg," ",employee_no," ",full_name," - ",notes," Total : ",format(reported_total,2)) as description'),
                "status_name as status"
            )
            ->orWhere(function ($query) use ($request) {
                $query->orWhere("no_reg", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("employee_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("full_name", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("notes", 'like', "%" . $request['search']['value'] . "%");
            })
            ->whereIn('fk_status_id', $costsubmissionreportAuthorizedStatus['authorizedStatus'])
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('reg_date', ">=", $datadate[0]);
                        $query->Where('reg_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            });


        $datamoehader = DB::table('tmoheader')
            ->join('tprojectmaster', 'tprojectmaster.pk_projectmaster_id', '=', 'tmoheader.fk_status_id')
            ->select(
                "pk_moheader_id as id",
                DB::RAW("'Maintenance Order' as type"),
                "start_date",
                DB::RAW('CONCAT(reg_no," ",ordertype," ",maint_activity," - ",unit_no," - ",ifnull(mostatus_name,"")) as description'),
                "tprojectmaster.name as status"
            )
            ->orWhere(function ($query) use ($request) {
                $query->orWhere("reg_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("maint_activity", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("unit_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("mostatus_name", 'like', "%" . $request['search']['value'] . "%");
            })
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('start_date', ">=", $datadate[0]);
                        $query->Where('start_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            })
            ->whereIn('fk_status_id', $MaintenanceAuthorizedStatus['authorizedStatus']);

        if ($request['length'] > 1) {
            $datas = $datacostsubmission
                ->union($datacostsubmissionreport)
                ->union($datamoehader)
                ->skip($request['start'])
                ->take($request['length'])
                ->orderBy($order[0], $order[1])
                ->get();
        } else {
            $datas = $datacostsubmission->unionall($datacostsubmissionreport)
                ->unionAll($datamoehader)
                ->orderBy($order[0], $order[1])
                ->get();

        }


        $filteredcostsubmission = DB::table('vcostsubmission')
            ->select(
                "pk_cost_submission_id as id",
                DB::RAW("'Pengajuan Dana' as type"),
                "date",
                DB::RAW('CONCAT(no_reg," ",employee_no," ",full_name," - ",wo_name," Total : ",format(total,2)) as description'),
                "status_name as status"
            )
            ->orWhere(function ($query) use ($request) {
                $query->orWhere("no_reg", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("employee_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("full_name", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("wo_name", 'like', "%" . $request['search']['value'] . "%");
            })
            ->whereIn('fk_status_id', $costsubmissionAuthorizedStatus['authorizedStatus'])
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('date', ">=", $datadate[0]);
                        $query->Where('date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            });

        $filteredcostsubmissionreport = DB::table('vcost_submission_report')
            ->select(
                "pk_cost_submission_report_id as id",
                DB::RAW("'Pemakaian Dana' as type"),
                "reg_date as date",
                DB::RAW('CONCAT(no_reg," ",employee_no," ",full_name," - ",notes," Total : ",format(reported_total,2)) as description'),
                "status_name as status"
            )
            ->orWhere(function ($query) use ($request) {
                $query->orWhere("no_reg", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("employee_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("full_name", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("notes", 'like', "%" . $request['search']['value'] . "%");
            })
            ->whereIn('fk_status_id', $costsubmissionreportAuthorizedStatus['authorizedStatus'])
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('reg_date', ">=", $datadate[0]);
                        $query->Where('reg_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            });

        $filtereddatamoehader = DB::table('tmoheader')
            ->join('tprojectmaster', 'tprojectmaster.pk_projectmaster_id', '=', 'tmoheader.fk_status_id')
            ->select(
                "pk_moheader_id as id",
                DB::RAW("'Maintenance Order' as type"),
                "start_date",
                DB::RAW('CONCAT(reg_no," ",ordertype," ",maint_activity," - ",unit_no," - ",ifnull(mostatus_name,"")) as description'),
                "tprojectmaster.name as status"
            )
            ->orWhere(function ($query) use ($request) {
                $query->orWhere("reg_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("maint_activity", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("unit_no", 'like', "%" . $request['search']['value'] . "%");
                $query->orWhere("mostatus_name", 'like', "%" . $request['search']['value'] . "%");
            })
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('start_date', ">=", $datadate[0]);
                        $query->Where('start_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            })
            ->whereIn('fk_status_id', $MaintenanceAuthorizedStatus['authorizedStatus']);

        $filteredrecordcount = $filteredcostsubmission
            ->unionall($filteredcostsubmissionreport)
            ->unionAll($filtereddatamoehader)
            ->count();

        $dataresult = [];
        foreach ($datas as $data) {
            //ubah dibagian ini untuk membuat raw html
            $subdata = [];

            foreach ($columns as $itemcolumn) {
                $rawcolumn = explode(".", $itemcolumn);
                $column = $itemcolumn;
                if (count($rawcolumn) > 1) {
                    $column = $rawcolumn[1];
                }

                if (strpos($column, ' as ') > 1) {
                    $column = substr($column, (strlen($column) - strpos($column, ' as ') - 4) * -1);
                }

                switch ($column) {
                    case 'type':
                        switch ($data->type) {
                            case 'Pengajuan Dana':
                                $subdata[] = '<span class="badge badge-danger">Pengajuan Dana</span>';
                                break;
                            case 'Pemakaian Dana':
                                $subdata[] = '<span class="badge badge-warning">Pemakaian Dana</span>';
                                break;
                            case 'Maintenance Order':
                                $subdata[] = '<span class="badge badge-success">Maintenance Order</span>';
                                break;
                            default:
                                $edit = "";
                                $delete = "";
                                break;
                        }
                        break;
                    default:
                        $subdata[] = $data->$column;
                        break;
                }
            }

            switch ($data->type) {
                case 'Pengajuan Dana':
                    $edit = "<a href='" . route('admin.costsubmission.edit', encrypt($data->id)) . "' class='btn btn-sm btn-success ml-2'><i class='fas fa-edit'></i></button>";
                    $delete = "<a href='" . route('admin.costsubmission.streampdf', encrypt($data->id)) . "'  target=_blank' rel='noopener noreferrer' class='btn btn-sm btn-warning ml-2'><i class='fa fa-print'></i></a>";
                    break;
                case 'Pemakaian Dana':
                    $edit = "<a href='" . route('admin.costsubmissionreport.edit', encrypt($data->id)) . "' class='btn btn-sm btn-success ml-2'><i class='fas fa-edit'></i></button>";
                    $delete = "<a href='" . route('admin.costsubmissionreport.streampdf', encrypt($data->id)) . "'  target=_blank' rel='noopener noreferrer' class='btn btn-sm btn-warning ml-2'><i class='fa fa-print'></i></a>";
                    break;
                case 'Maintenance Order':
                    $edit = "<a href='" . route('admin.maintenanceorder.edit', encrypt($data->id)) . "' class='btn btn-sm btn-success ml-2'><i class='fas fa-edit'></i></button>";
                    $delete = "<a href='" . route('admin.maintenanceorder.streampdf', encrypt($data->id)) . "'  target=_blank' rel='noopener noreferrer' class='btn btn-sm btn-warning ml-2'><i class='fa fa-print'></i></a>";
                    break;
                default:
                    $edit = "";
                    $delete = "";
                    break;
            }

            $subdata[] = $edit . $delete;
            $dataresult[] = $subdata;
        }

        //ambil total data
        $allcostsubmission = DB::table('vcostsubmission')
            ->select("pk_cost_submission_id as id")
            ->whereIn('fk_status_id', $costsubmissionAuthorizedStatus['authorizedStatus'])
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('date', ">=", $datadate[0]);
                        $query->Where('date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            });

        $allcostsubmissionreport = DB::table('vcost_submission_report')
            ->select("pk_cost_submission_report_id as id")
            ->whereIn('fk_status_id', $costsubmissionreportAuthorizedStatus['authorizedStatus'])
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('reg_date', ">=", $datadate[0]);
                        $query->Where('reg_date', "<=", $datadate[1]);
                    }
                }
                if ($datafilter["status"] != "") {
                    $query->Where('fk_status_id', decryptForNumber($datafilter["status"]));
                }
            });

        $alldata = $allcostsubmissionreport->unionall($allcostsubmission)
            ->count();

        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );
        // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

        return response()->json($output, 200);
    }
}
