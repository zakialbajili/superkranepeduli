<?php
namespace App\Traits;

use App\Models\AccountModel;
use DB;
use Illuminate\Support\Facades\Auth;

trait PartNumberTraits
{
    public function itempartnumbergeneraldatatable($request)
    {
        try {
            // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            $itemnumberid = 0;
            if (isset($request->itemnumber)) {
                $itemnumberid = decryptForNumber($request->itemnumber);
            }

            $columns = [
                'itemno',
                'part_no',
                'part_no_alternate1',
                'part_no_alternate2',
                'description',
                'manufacture_name',
            ];
            $columnkey = 'pk_whitemnumber_id';
            $table = 'twhitemnumber';
            $order = [$columns[0], 'asc'];

            $selectColumn[] = $columnkey;

            if (isset($request["order"])) {
                $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
            }

            $datapartnumbers = DB::table($table)
                ->leftJoin('twhpartnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
                ->leftJoin('tunitmanufacture', 'twhpartnumber.fk_unitmanufacture_id', '=', 'tunitmanufacture.pk_unitmanufacture_id')
                ->leftJoin('tprojectmaster', 'twhpartnumber.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')
                ->select(
                    DB::raw("CONCAT(twhitemnumber.itemno, '-', IFNULL(twhpartnumber.itemnumberprefix, '')) AS itemno"),
                    'twhitemnumber.description_template AS itemdesc_template',
                    'twhitemnumber.description_free AS itemdesc_free',
                    'twhpartnumber.part_no',
                    'twhpartnumber.part_no_alternate1',
                    'twhpartnumber.part_no_alternate2',
                    DB::raw("IF(IFNULL(twhitemnumber.description_template, '') = '', twhitemnumber.description_free, twhitemnumber.description_template) AS description"),
                    'tunitmanufacture.name AS manufacture_name',
                    'twhpartnumber.fk_uom_id',
                    'twhpartnumber.pk_whpartnumber_id',
                    'twhitemnumber.pk_whitemnumber_id'
                );
            $filtereddatapartnumbers = $datapartnumbers;
            $filtereddatapartnumbers = $filtereddatapartnumbers
                ->Where(function ($query) use ($request) {
                    $query->where('twhitemnumber.itemno', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhpartnumber.part_no', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhpartnumber.part_no_alternate1', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhpartnumber.part_no_alternate2', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhitemnumber.description_template', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhitemnumber.description_free', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('tunitmanufacture.name', 'LIKE', "%" . $request['search']['value'] . "%");
                });

            $dataitemnumbers = DB::table('twhitemnumber')
                ->select(
                    DB::raw("CONCAT(twhitemnumber.itemno, '-') AS itemno"),
                    'twhitemnumber.description_template AS itemdesc_template',
                    'twhitemnumber.description_free AS itemdesc_free',
                    DB::raw("NULL AS part_no"),
                    DB::raw("NULL AS part_no_alternate1"),
                    DB::raw("NULL AS part_no_alternate2"),
                    DB::raw("IF(IFNULL(twhitemnumber.description_template, '') = '', twhitemnumber.description_free, twhitemnumber.description_template) AS description"),
                    DB::raw("NULL AS manufacture_name"),
                    DB::raw("NULL AS fk_uom_id"),
                    DB::raw("NULL AS pk_whpartnumber_id"),
                    'twhitemnumber.pk_whitemnumber_id'
                );
            $filtereddataitemnumbers = $dataitemnumbers;
            $filtereddataitemnumbers = $filtereddataitemnumbers
                ->where(function ($query) use ($request) {
                    $query->where('twhitemnumber.itemno', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhitemnumber.description_template', 'LIKE', "%" . $request['search']['value'] . "%")
                        ->orWhere('twhitemnumber.description_free', 'LIKE', "%" . $request['search']['value'] . "%");
                });

            if ($itemnumberid > 0) {
                $filtereddatapartnumbers = $filtereddatapartnumbers->where('twhitemnumber.pk_whitemnumber_id', $itemnumberid);
                $filtereddataitemnumbers = $filtereddataitemnumbers->where('twhitemnumber.pk_whitemnumber_id', $itemnumberid);
            }

            if ($request['length'] != 1) {
                $datapartnumber = $filtereddatapartnumbers
                    ->orderBy($order[0], $order[1])
                    ->skip($request['start'])
                    ->take($request['length']);

                $dataitemnumber = $filtereddataitemnumbers
                    ->orderBy($order[0], $order[1])
                    ->skip($request['start'])
                    ->take($request['length']);
            } else {
                $datapartnumber = $filtereddatapartnumbers
                    ->orderBy($order[0], $order[1]);

                $dataitemnumber = $filtereddataitemnumbers
                    ->orderBy($order[0], $order[1]);

            }

            $datas = $datapartnumber->union($dataitemnumber)->orderBy('itemno')->get();

            $filteredrecordcount = $filtereddatapartnumbers->union($filtereddataitemnumbers)->orderBy('itemno')->count();

            $dataresult = [];
            foreach ($datas as $data) {
                //ubah dibagian ini untuk membuat raw html
                $id = encryptId($data->$columnkey);
                $idpartnumber = encryptId($data->pk_whpartnumber_id);
                $uom = encryptId($data->fk_uom_id);
                $subdata = [];
                foreach ($columns as $column) {
                    switch ($column) {
                        case 'fk_uom_id':
                            $subdata[] = decryptForNumber($data->fk_uom_id);
                            break;
                        default:
                            $subdata[] = $data->$column;
                            break;
                    }
                }
                $select = "<button data-id='$id' data-partnumberid='$idpartnumber' data-uom='$uom' class='btn btn-sm btn-danger ml-2 selected-item'><i class='fa fa-check'></i></button>";
                $subdata[] = $select;
                $dataresult[] = $subdata;
            }

            //ambil total data
            $alldata = $datapartnumbers->union($dataitemnumbers)->orderBy('itemno')->count();
            $output = array(
                "draw" => intval($request["draw"]),
                "recordsTotal" => $alldata,
                "recordsFiltered" => $filteredrecordcount,
                "data" => $dataresult
            );
            // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

            return response()->json($output, 200);
        } catch (\Throwable $th) {
            $output = array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            );
            // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

            return response()->json($output, 200);
        }

    }

    public function getitempartnumbergeneral($request)
    {
        $partnumbers = [];
        try {
            $rawPartnumber = DB::table('twhitemnumber')
                ->leftJoin('twhpartnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
                ->leftJoin('tunitmanufacture', 'twhpartnumber.fk_unitmanufacture_id', '=', 'tunitmanufacture.pk_unitmanufacture_id')
                ->leftJoin('tprojectmaster', 'twhpartnumber.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')
                ->select(
                    DB::raw("CONCAT(twhitemnumber.itemno, '-', IFNULL(twhpartnumber.itemnumberprefix, '')) as itemno"),
                    'twhitemnumber.description_template as itemdesc_template',
                    'twhitemnumber.description_free as itemdesc_free',
                    'twhpartnumber.part_no',
                    'twhpartnumber.part_no_alternate1',
                    'twhpartnumber.part_no_alternate2',
                    DB::raw("IF(IFNULL(twhitemnumber.description_template, '') = '', twhitemnumber.description_free, twhitemnumber.description_template) as description"),
                    'tunitmanufacture.name as manufacture_name',
                    'twhpartnumber.fk_uom_id',
                    'twhpartnumber.pk_whpartnumber_id',
                    'twhitemnumber.pk_whitemnumber_id'
                )->where(function ($query) use ($request) {
                    $query->orwhere('twhpartnumber.part_no', 'like', '%' . $request->search . '%')
                        ->orwhere('twhitemnumber.description_template', 'like', '%' . $request->search . '%')
                        ->orwhere('twhitemnumber.description_free', 'like', '%' . $request->search . '%')
                        ->orwhere('twhitemnumber.itemno', 'like', '%' . $request->search . '%')
                        ->orwhere('twhpartnumber.itemnumberprefix', 'like', '%' . $request->search . '%')
                        ->orwhere('tunitmanufacture.name', 'like', '%' . $request->search . '%');
                });


            $rawItemNumber = DB::table('twhitemnumber')
                ->select(
                    DB::raw("CONCAT(twhitemnumber.itemno, '-') AS itemno"),
                    'twhitemnumber.description_template AS itemdesc_template',
                    'twhitemnumber.description_free AS itemdesc_free',
                    DB::raw("NULL AS part_no"),
                    DB::raw("NULL AS part_no_alternate1"),
                    DB::raw("NULL AS part_no_alternate2"),
                    DB::raw("IF(IFNULL(twhitemnumber.description_template, '') = '', twhitemnumber.description_free, twhitemnumber.description_template) AS description"),
                    DB::raw("NULL AS manufacture_name"),
                    DB::raw("NULL AS fk_uom_id"),
                    DB::raw("NULL AS pk_whpartnumber_id"),
                    'twhitemnumber.pk_whitemnumber_id'
                )->where(function ($query) use ($request) {
                    $query->where('twhitemnumber.itemno', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('twhitemnumber.description_template', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('twhitemnumber.description_free', 'LIKE', "%" . $request->search . "%");
                });


            $itemnumberid = 0;
            if (isset($request->itemnumber)) {
                $itemnumberid = decryptForNumber($request->itemnumber);
            }
            if ($itemnumberid > 0) {
                $rawPartnumber = $rawPartnumber->where('twhitemnumber.pk_whitemnumber_id', $itemnumberid);
                $rawItemNumber = $rawItemNumber->where('twhitemnumber.pk_whitemnumber_id', $itemnumberid);
            }
            $rawData = $rawPartnumber->union($rawItemNumber)
                ->orderBy('itemno')->get();
            if ($rawData != null) {
                foreach ($rawData as $item) {
                    $partnumbers[] = [
                        "id" => encryptId($item->pk_whitemnumber_id),
                        "text" => $item->itemno . ' | ' . $item->part_no . ' | ' . $item->description . ' | ' . $item->manufacture_name,
                        "partnumberid" => encryptId($item->pk_whpartnumber_id),
                        "description" => $item->description,
                        "itemno" => $item->itemno,
                        "manuf" => $item->manufacture_name,
                        "uom" => encryptId($item->fk_uom_id),
                    ];
                }
            }
            return response(['status' => 'success', 'data' => ["data" => $partnumbers]]);
        } catch (\Throwable $th) {
            // return response(['status' => 'success', 'data' => $th->getMessage()]);
        }

    }

    public function itempartnumberlocationdatatable($request)
    {
        // <<<<<<<<<<<<<< START untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $itemnumberid = 0;
        if (isset($request->itemnumber)) {
            $itemnumberid = decryptForNumber($request->itemnumber);
        }
        $columns = [
            'itemno',
            'part_no',
            'part_no_alternate1',
            'part_no_alternate2',
            'description',
            'partlocation',
            'unitmanufacture_name',
            'balance',
        ];
        $columnkey = 'pk_whpartnumberlocation_id';
        $table = 'vpartnobalanceonpartlocation2';
        $order = [$columns[0], 'asc'];

        $selectColumn = [
            'itemno',
            'part_no',
            'part_no_alternate1',
            'part_no_alternate2',
            'description',
            'unitmanufacture_name',
            'balance',
            'whlocation_name',
            'partlocation',
            'uom_name',
            'pk_whpartnumber_id',
            'fk_uom_id'
        ];
        $selectColumn[] = $columnkey;
        $searchColumn = [
            'itemno',
            'part_no',
            'part_no_alternate1',
            'part_no_alternate2',
            'description',
            'partlocation',
            'whlocation_name',
            'unitmanufacture_name',
            'uom_name',
        ];
        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        $query = DB::table($table)
            ->join('tprojectmaster', 'vpartnobalanceonpartlocation2.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')
            ->select($selectColumn);
        $filteredquery = $query;

        if ($itemnumberid > 0) {
            $filteredquery = $filteredquery->where('vpartnobalanceonpartlocation2.fk_whitemnumber_id', $itemnumberid);
        }

        $filteredquery = $filteredquery
            ->where('tprojectmaster.master_value', 'Active')
            ->Where(function ($query) use ($searchColumn, $request) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%" . $request['search']['value'] . "%");
                };
            });

        if ($request['length'] != 1) {
            $datas = $filteredquery
                ->orderBy($order[0], $order[1])
                ->skip($request['start'])
                ->take($request['length'])
                ->get();

        } else {
            $datas = $filteredquery
                ->orderBy($order[0], $order[1])
                ->get();
        }

        $filteredrecordcount = $filteredquery->count();

        $dataresult = [];
        foreach ($datas as $data) {
            //ubah dibagian ini untuk membuat raw html
            $partlocid = encryptId($data->$columnkey);
            $partid = encryptId($data->pk_whpartnumber_id);
            $uomid = encryptId($data->fk_uom_id);
            $subdata = [];
            foreach ($columns as $column) {
                switch ($column) {
                    case 'balance':
                        $subdata[] = numberDelimited($data->balance, 2) . ' ' . $data->uom_name;
                        break;
                    case 'partlocation':
                        $subdata[] = $data->whlocation_name . ' ' . $data->partlocation;
                        break;
                    default:
                        $subdata[] = $data->$column;
                        break;
                }
            }
            $select = "<button data-partid='$partid' data-uom='$data->uom_name' data-partlocid='$partlocid' class='btn btn-sm btn-danger ml-2 selected-item'><i class='fa fa-check'></i></button>";
            $subdata[] = $select;
            $dataresult[] = $subdata;
        }

        //ambil total data
        $alldata = $query->count();
        $output = array(
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        );
        // <<<<<<<<<<<<<< END untuk pembentukan data table >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

        return response()->json($output, 200);
    }

    public function getitempartnumberlocation($request)
    {
        $partnumbers = [];
        try {
            $rawPartnumber = DB::table('vpartnobalanceonpartlocation2')
                ->join('tprojectmaster', 'vpartnobalanceonpartlocation2.fk_status_id', '=', 'tprojectmaster.pk_projectmaster_id')
                ->select(
                    'itemno',
                    'pk_whpartnumberlocation_id',
                    'pk_whpartnumber_id',
                    'part_no',
                    'part_no_alternate1',
                    'part_no_alternate2',
                    'description',
                    'unitmanufacture_name',
                    'balance',
                    'whlocation_name',
                    'partlocation',
                    'uom_name',
                    'fk_uom_id'
                )
                ->where(function ($query) use ($request) {
                    $query
                        ->orwhere('itemno', 'like', '%' . $request->search . '%')
                        ->orwhere('part_no', 'like', '%' . $request->search . '%')
                        ->orwhere('part_no_alternate1', 'like', '%' . $request->search . '%')
                        ->orwhere('part_no_alternate2', 'like', '%' . $request->search . '%')
                        ->orwhere('description', 'like', '%' . $request->search . '%')
                        ->orwhere('unitmanufacture_name', 'like', '%' . $request->search . '%');
                })
                ->where('tprojectmaster.master_value', 'Active');


            $itemnumberid = 0;
            if (isset($request->itemnumber)) {
                $itemnumberid = decryptForNumber($request->itemnumber);
            }
            if ($itemnumberid > 0) {
                $rawPartnumber = $rawPartnumber->where('fk_whitemnumber_id', $itemnumberid);
            }
            $rawPartnumber = $rawPartnumber->get();

            if ($rawPartnumber != null) {
                foreach ($rawPartnumber as $item) {
                    $partnumbers[] = [
                        "id" => encryptId($item->pk_whpartnumberlocation_id),
                        "uom" => $item->uom_name,
                        "description" => ($item->description),
                        "itemno" => $item->itemno,
                        "manuf" => $item->unitmanufacture_name,
                        "partlocation" => $item->whlocation_name . ' ' . $item->partlocation,
                        "text" => $item->itemno . ' | ' . $item->part_no . ' | ' . $item->description . ' | ' . $item->unitmanufacture_name . ' | ' . $item->whlocation_name . ' ' . $item->partlocation
                    ];
                }
            }
            return response(['status' => 'success', 'data' => ["data" => $partnumbers]]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
