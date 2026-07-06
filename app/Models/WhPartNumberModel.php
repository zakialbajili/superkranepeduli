<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WhPartNumberModel extends Model
{
    use HasFactory;

    protected $table = 'twhpartnumber';
    protected $primaryKey = 'pk_whpartnumber_id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    // Method to get part numbers with complex joins and filters
    public static function getPartNumbersWithDetails($request)
    {
        // Default filter values
        $datafilter = [
            "status" => "",
            "part_no" => "",
            "item_number" => "",
            "nsc_title" => "",
            "inc_title" => "",
        ];
        if (isset($request['data'][0])) {
            $datafilter = $request['data'][0];
        }

        // Columns and sorting
        $columns = [
            'itemno',
            'part_no',
            'part_no_alternate1',
            'part_no_alternate2',
            'title',
            'full_title',
            'description',
            'model',
            'category_name',
            'balance',
            'uom_name',
            'spesification',
            'notes',
            'status_name'
        ];
        $columnKey = 'pk_whpartnumber_id';
        $orderColumn = $columns[0];
        $orderDirection = 'desc';

        if (isset($request["order"])) {
            $orderColumn = $columns[$request['order']['0']['column']];
            $orderDirection = $request['order']['0']['dir'];
        }

        // Define search columns and select columns
        $searchColumns = [
            'part_no',
            'part_no_alternate1',
            'part_no_alternate2',
            'description',
            'model',
            'spesification',
            'notes',
            'category_name',
            'balance',
            'uom_name',
            'status_name',
            'crossref_details',
        ];
        $selectColumns = array_merge($columns, [$columnKey]);

        // Base query with joins
        $searchValue = strtoupper($request['search']['value']);
        $query = DB::table('twhpartnumber')
            ->select(
                'twhpartnumber.pk_whpartnumber_id',
                'twhpartnumber.part_no',
                'twhpartnumber.fk_whitemnumber_id',
                'twhlocation.name AS location_name',
                'twhpartnumber.part_no_alternate1',
                'twhpartnumber.part_no_alternate2',
                'twhpartnumber.fk_unitmanufacture_id',
                'twhpartnumber.description',
                'twhpartnumber.model',
                'twhpartnumber.dimension',
                'twhpartnumber.weight',
                'twhpartnumber.wh_location',
                'twhpartnumber.fk_category_id',
                'twhpartnumber.fk_uom_id',
                'twhpartnumber.spesification',
                'twhpartnumber.notes',
                'twhitemnumber.itemno',
                'twhnsc.title',
                'twhinc.full_title',
                'twhpartnumber.fk_partnoaccsms_id',
                'twhpartnumber.partno_acc',
                'twhpartnumber.fk_status_id',
                'twhpartnumber.created_date',
                'twhpartnumber.created_by',
                'twhpartnumber.updated_date',
                'twhpartnumber.updated_by',
                'twhpartnumber.moq',
                'b.itemdescription',
                DB::raw('(SELECT name FROM tprojectmaster WHERE pk_projectmaster_id = twhpartnumber.fk_category_id) as category_name'),
                DB::raw('(SELECT name FROM tunitmanufacture WHERE pk_unitmanufacture_id = twhpartnumber.fk_unitmanufacture_id) as manufacture_name'),
                DB::raw('(SELECT name FROM tprojectmaster WHERE pk_projectmaster_id = twhpartnumber.fk_uom_id) as uom_name'),
                DB::raw('(SELECT name FROM tprojectmaster WHERE pk_projectmaster_id = twhpartnumber.fk_status_id) as status_name'),
                DB::raw('IFNULL(vwhtotalin.totalin, 0) as totalin'),
                DB::raw('IFNULL(vwhtotalout.totalout, 0) as totalout'),
                DB::raw('(IFNULL(vwhtotalin.totalin, 0) - IFNULL(vwhtotalout.totalout, 0)) as balance'),
                DB::raw('subquery.crossref_details')
            )
            ->leftJoin(
                DB::raw('(SELECT
                twhincitemcrossref.fk_whinc_id,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        "title", UPPER(twhcrossref.crossref_title),
                        "short", UPPER(twhcrossref.crossref_short)
                    )
                ) AS crossref_details
            FROM
                twhincitemcrossref
            LEFT JOIN twhcrossref ON twhcrossref.pk_whcrossref_id = twhincitemcrossref.fk_whcrossref_id
            GROUP BY twhincitemcrossref.fk_whinc_id) AS subquery'),
                'twhpartnumber.fk_whinc_id',
                '=',
                'subquery.fk_whinc_id'
            )
            ->leftJoin('tpartnoacc AS b', 'twhpartnumber.fk_partnoaccsms_id', '=', 'b.pk_partnoacc_id')
            ->leftJoin('twhlocation', 'twhlocation.pk_whlocation_id', '=', 'twhpartnumber.wh_location')
            ->leftJoin('vwhtotalin', 'vwhtotalin.fk_whpartnumber_id', '=', 'twhpartnumber.pk_whpartnumber_id')
            ->leftJoin('vwhtotalout', 'vwhtotalout.fk_whpartnumber_id', '=', 'twhpartnumber.pk_whpartnumber_id')
            ->leftJoin('twhitemnumber', 'twhpartnumber.fk_whitemnumber_id', '=', 'twhitemnumber.pk_whitemnumber_id')
            ->leftJoin('twhnsc', 'twhitemnumber.fk_whnsc_id', '=', 'twhnsc.pk_whnsc_id')
            ->leftJoin('twhinc', 'twhitemnumber.fk_whinc_id', '=', 'twhinc.pk_whinc_id')

            // Searching conditions
            ->where(function ($query) use ($searchValue) {
                $query->whereRaw('EXISTS (SELECT 1 FROM tprojectmaster WHERE pk_projectmaster_id = twhpartnumber.fk_category_id AND name LIKE ?)', ["%$searchValue%"])
                    ->orWhereRaw('EXISTS (SELECT 1 FROM tunitmanufacture WHERE pk_unitmanufacture_id = twhpartnumber.fk_unitmanufacture_id AND name LIKE ?)', ["%$searchValue%"])
                    ->orWhereRaw('EXISTS (SELECT 1 FROM tprojectmaster WHERE pk_projectmaster_id = twhpartnumber.fk_uom_id AND name LIKE ?)', ["%$searchValue%"])
                    ->orWhereRaw('EXISTS (SELECT 1 FROM tprojectmaster WHERE pk_projectmaster_id = twhpartnumber.fk_status_id AND name LIKE ?)', ["%$searchValue%"])
                    ->orWhere('subquery.crossref_details', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhnsc.title', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhinc.full_title', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhitemnumber.itemno', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.part_no', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.part_no_alternate1', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.part_no_alternate2', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.description', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.model', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.spesification', 'LIKE', "%" . $searchValue . "%")
                    ->orWhere('twhpartnumber.notes', 'LIKE', "%" . $searchValue . "%");
            })

            // Data filtering conditions
            ->where(function ($query) use ($datafilter) {
                if (!empty($datafilter["status"])) {
                    $query->where('twhpartnumber.fk_status_id', decryptForNumber($datafilter["status"]));
                }
                if (!empty($datafilter["part_no"])) {
                    $query->where('twhpartnumber.part_no', 'LIKE', '%' . $datafilter["part_no"] . '%');
                }
                if (!empty($datafilter["item_number"])) {
                    $query->where('twhitemnumber.itemno', 'LIKE', '%' . $datafilter["item_number"] . '%');
                }
                if (!empty($datafilter["nsc_title"])) {
                    $query->where('twhnsc.title', 'LIKE', '%' . $datafilter["nsc_title"] . '%');
                }
                if (!empty($datafilter["inc_title"])) {
                    $query->where('twhinc.full_title', 'LIKE', '%' . $datafilter["inc_title"] . '%');
                }
            })

            ->where('twhpartnumber.fk_status_id', 156);


        // Get filtered count
        $filteredRecordCount = $query->count();

        // Get paginated data
        $data = $query->orderBy($orderColumn, $orderDirection)
            ->skip($request['start'])
            ->take($request['length'])
            ->get();

        // Process data for DataTable
        $dataResult = $data->map(function ($item) use ($columnKey, $columns) {
            $id = encrypt($item->$columnKey);
            $subdata = [];
            foreach ($columns as $column) {
                switch ($column) {
                    case 'itemno':
                        $subdata[] = "<a href='" . route('admin.whitemnumber.edit', encrypt($item->fk_whitemnumber_id)) . "'>$item->itemno</a>";
                        break;
                    case 'part_no':
                        $subdata[] = "<a href='" . route('admin.warehousepartnumber.edit', $id) . "'>$item->part_no</a>";
                        break;
                    case 'itemdescription':
                        $subdata[] = $item->itemdescription;
                        break;
                    case 'crossref_details':
                        $subdata[] = $item->crossref_details;
                        break;
                    default:
                        $subdata[] = $item->$column;
                        break;
                }
            }
            $edit = "<a href='" . route('admin.warehousepartnumber.edit', $id) . "' class='btn btn-sm btn-success ml-2'><i class='fas fa-edit'></i></a>";
            $subdata[] = $edit;
            return $subdata;
        });

        // Get total records
        $totalRecords = DB::table('twhpartnumber')
            ->when($datafilter["status"] !== "", function ($query) use ($datafilter) {
                $query->where('fk_status_id', decryptForNumber($datafilter["status"]));
            }, function ($query) {
                $query->where('fk_status_id', 156);
            })
            ->count();

        // Prepare output for DataTable
        return [
            "draw" => intval($request["draw"]),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecordCount,
            "data" => $dataResult
        ];
    }


}

