<?php

namespace App\Http\Controllers\backend\master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ModuleTraits;
use App\Traits\PushNotification;
use App\Traits\StatusTraits;
use Illuminate\Support\Facades\Auth;

use DB;
use Str;

class NotificationController extends Controller
{
    use ModuleTraits, StatusTraits, PushNotification;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headertag = 'Data Notifikasi';
        $headername = 'Daftar Notifikasi';
        $headerlink = '#';
        $parentname = 'Halaman Utama';
        $parentlink = '#';

        $headerparam = [
            'headertag' => $headertag,
            'headername' => $headername,
            'headerlink' => $headerlink,
            'parentname' => $parentname,
            'parentlink' => $parentlink,
        ];

        return view('backend.master.notification.index', compact('headerparam'));
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

    public function unreaddatatables(Request $request)
    {
        // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $datafilter = [];
        if (isset($request['data'][0])) {
            $datafilter = $request['data'][0];
        }
        $columns = [
            'created_date',
            'type',
            'content',
        ];
        $columnkey = 'pk_notification_id';
        $table = 'tnotification';
        $order = [$columns[1], 'desc'];

        $selectColumn = $columns;
        $selectColumn[] = 'pk_notification_id';
        $searchColumn = $selectColumn;
        
        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
            }
            
        $queryBuilder = DB::table($table)
            ->select($selectColumn)
            ->where('is_read', 0)
            ->where('fk_user_id', Auth::user()->pk_user_id);
        $alldata = (clone $queryBuilder)->count();
        if (!empty($request['search']['value'])){
            $queryBuilder->where(function ($query) use ($searchColumn, $request) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            });
        }
        if (!empty($datafilter)){
            $queryBuilder->where(function ($query) use ($datafilter) {
                if (!empty($datafilter["transactiondate"])) {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('created_date', ">=", $datadate[0]);
                        $query->Where('created_date', "<=", $datadate[1]);
                    }
                }
            });
        }
        $filteredrecordcount = (clone $queryBuilder)->count();
        $datas = $queryBuilder->orderBy($order[0], $order[1])
            ->skip($request['start'])
            ->take($request['length'])
            ->get();
        $dataresult = [];
        foreach ($datas as $data) {
            //ubah dibagian ini untuk membuat raw html
            $id = encrypt($data->$columnkey);
            $subdata = [];

            foreach ($columns as $column) {
                switch ($column) {
                    case 'employee_no':
                        $subdata[] = $data->employee_no . ' ' . $data->full_name . ' ' . $data->employee_position;
                        break;
                    default:
                        $subdata[] = $data->$column;
                        break;
                }
            }
            $select = '<a href="' . route('admin.utility.selectNotification', encrypt($data->pk_notification_id)) . '" class="btn btn-sm btn-success ml-2"><i class="fab fa-readme"></i></a>';
            $subdata[] = $select;
            $dataresult[] = $subdata;
        }

        //ambil total data
        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );
        // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

        return response()->json($output, 200);
    }

    public function readdatatables(Request $request)
    {
        $datafilter = [];
        if (isset($request['data'][0])) {
            $datafilter = $request['data'][0];
        }
        $columns = [
            'created_date',
            'type',
            'content',
        ];
        $columnkey = 'pk_notification_id';
        $table = 'tnotification';
        $order = [$columns[1], 'desc'];

        $selectColumn = $columns;
        $selectColumn[] = 'pk_notification_id';
        $searchColumn = $selectColumn;

        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        $queryBuilder = DB::table($table)
            ->select($selectColumn)
            ->where('is_read', 1)
            ->where('fk_user_id', Auth::user()->pk_user_id);
        $alldata = (clone $queryBuilder)->count();
        if (!empty($request['search']['value'])){
            $queryBuilder->where(function ($query) use ($searchColumn, $request) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            });
        }
        if (!empty($datafilter)){
            $queryBuilder->where(function ($query) use ($datafilter) {
                if (!empty($datafilter["transactiondate"])) {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('created_date', ">=", $datadate[0]);
                        $query->Where('created_date', "<=", $datadate[1]);
                    }
                }
            });
        }
        $filteredrecordcount = (clone $queryBuilder)->count();
        $datas = $queryBuilder->orderBy($order[0], $order[1])
            ->skip($request['start'])
            ->take($request['length'])
            ->get();

        $dataresult = [];
        foreach ($datas as $data) {
            $id = encrypt($data->$columnkey);
            $subdata = [];

            foreach ($columns as $column) {
                switch ($column) {
                    case 'employee_no':
                        $subdata[] = $data->employee_no . ' ' . $data->full_name . ' ' . $data->employee_position;
                        break;
                    default:
                        $subdata[] = $data->$column;
                        break;
                }
            }
            $select = '<a href="' . route('admin.utility.selectNotification', encrypt($data->pk_notification_id)) . '" class="btn btn-sm btn-success ml-2"><i class="fab fa-readme"></i></a>';
            $subdata[] = $select;
            $dataresult[] = $subdata;
        }

        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );

        return response()->json($output, 200);
    }
}
