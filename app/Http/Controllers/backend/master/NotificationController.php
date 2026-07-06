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
        $datafilter = [
            "transactiondate" => "",
            "status" => "",
        ];
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

        if ($request['length'] != 1) {
            $datas = DB::table($table)
                ->select($selectColumn)
                ->orWhere(function ($query) use ($searchColumn, $request) {
                    foreach ($searchColumn as $column) {
                        $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                    };
                })
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('created_date', ">=", $datadate[0]);
                            $query->Where('created_date', "<=", $datadate[1]);
                        }
                    }
                })
                ->where('is_read', 0)
                ->where('fk_user_id', Auth::user()->pk_user_id)
                ->orderBy($order[0], $order[1])
                ->skip($request['start'])
                ->take($request['length'])
                ->get();
        } else {
            $datas = DB::table($table)
                ->select($selectColumn)
                ->orWhere(function ($query) use ($searchColumn, $request) {
                    foreach ($searchColumn as $column) {
                        $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                    };
                })
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('created_date', ">=", $datadate[0]);
                            $query->Where('created_date', "<=", $datadate[1]);
                        }
                    }
                })
                ->where('is_read', 0)
                ->where('fk_user_id', Auth::user()->pk_user_id)
                ->orderBy($order[0], $order[1])
                ->get();
        }

        $filteredrecordcount = DB::table($table)
            ->select($columns)
            ->orWhere(function ($query) use ($searchColumn, $request) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            })
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('created_date', ">=", $datadate[0]);
                        $query->Where('created_date', "<=", $datadate[1]);
                    }
                }
            })
            ->where('is_read', 0)
            ->where('fk_user_id', Auth::user()->pk_user_id)
            ->orderBy($order[0], $order[1])
            ->count();

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
                    case 'submission_total':
                        $subdata[] = numberDelimited($data->submission_total);
                        break;
                    case 'reported_total':
                        $subdata[] = numberDelimited($data->reported_total);
                        break;
                    case 'remaining_total':
                        $subdata[] = numberDelimited($data->remaining_total);
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
        $alldata = DB::table($table)
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('created_date', ">=", $datadate[0]);
                        $query->Where('created_date', "<=", $datadate[1]);
                    }
                }
            })
            ->where('is_read', 0)
            ->where('fk_user_id', Auth::user()->pk_user_id)->count();
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
        // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $datafilter = [
            "transactiondate" => "",
            "status" => "",
        ];
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

        if ($request['length'] != 1) {
            $datas = DB::table($table)
                ->select($selectColumn)
                ->orWhere(function ($query) use ($searchColumn, $request) {
                    foreach ($searchColumn as $column) {
                        $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                    };
                })
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('created_date', ">=", $datadate[0]);
                            $query->Where('created_date', "<=", $datadate[1]);
                        }
                    }
                })
                ->where('is_read', 1)
                ->where('fk_user_id', Auth::user()->pk_user_id)
                ->orderBy($order[0], $order[1])
                ->skip($request['start'])
                ->take($request['length'])
                ->get();
        } else {
            $datas = DB::table($table)
                ->select($selectColumn)
                ->orWhere(function ($query) use ($searchColumn, $request) {
                    foreach ($searchColumn as $column) {
                        $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                    };
                })
                ->where(function ($query) use ($datafilter) {
                    if ($datafilter["transactiondate"] != "") {
                        $datadate = explode(' - ', $datafilter["transactiondate"]);
                        if (count($datadate) > 0) {
                            $query->Where('created_date', ">=", $datadate[0]);
                            $query->Where('created_date', "<=", $datadate[1]);
                        }
                    }
                })
                ->where('is_read', 1)
                ->where('fk_user_id', Auth::user()->pk_user_id)
                ->orderBy($order[0], $order[1])
                ->get();
        }

        $filteredrecordcount = DB::table($table)
            ->select($columns)
            ->orWhere(function ($query) use ($searchColumn, $request) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            })
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('created_date', ">=", $datadate[0]);
                        $query->Where('created_date', "<=", $datadate[1]);
                    }
                }
            })
            ->where('is_read', 1)
            ->where('fk_user_id', Auth::user()->pk_user_id)
            ->orderBy($order[0], $order[1])
            ->count();

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
                    case 'submission_total':
                        $subdata[] = numberDelimited($data->submission_total);
                        break;
                    case 'reported_total':
                        $subdata[] = numberDelimited($data->reported_total);
                        break;
                    case 'remaining_total':
                        $subdata[] = numberDelimited($data->remaining_total);
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
        $alldata = DB::table($table)
            ->where(function ($query) use ($datafilter) {
                if ($datafilter["transactiondate"] != "") {
                    $datadate = explode(' - ', $datafilter["transactiondate"]);
                    if (count($datadate) > 0) {
                        $query->Where('created_date', ">=", $datadate[0]);
                        $query->Where('created_date', "<=", $datadate[1]);
                    }
                }
            })
            ->where('is_read', 1)
            ->where('fk_user_id', Auth::user()->pk_user_id)->count();
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
