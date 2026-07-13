<?php

namespace App\Http\Controllers\backend\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use DB;

class DashboardAdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $headertag = 'Dashboard';
        $headername = 'Dashboard';
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

        // ---- Statistik Angka (ringan, tanpa data chart) ----
        $totalLaporan = DB::table('thsepelaporanbahaya')->count();

        $statusCounts = DB::table('thsepelaporanbahaya')
            ->select('status_pelaporan', DB::raw('COUNT(*) AS total'))
            ->groupBy('status_pelaporan')
            ->get()
            ->keyBy('status_pelaporan');

        $openCount = $statusCounts->get(5)?->total ?? 0;
        $progressCount = $statusCounts->get(6)?->total ?? 0;
        $closedCount = $statusCounts->get(7)?->total ?? 0;

        $tahunIni = now()->format('Y');

        return view('backend.master.admin.dashboard.index', compact(
            'headerparam',
            'totalLaporan',
            'openCount',
            'progressCount',
            'closedCount',
            'tahunIni',
        ));
    }

    /**
     * AJAX: Get chart data (laporan per bulan) by year.
     */
    public function chartCountReport(Request $request)
    {
        $startMonth = $request->get('start_month');
        $endMonth = $request->get('end_month');

        $query = DB::table('thsepelaporanbahaya');

        if ($startMonth) {
            $query->where('tgl_pelaporan', '>=', $startMonth . '-01');
        }
        if ($endMonth) {
            $query->where('tgl_pelaporan', '<=', $endMonth . '-31');
        }

        $laporanPerBulan = (clone $query)
            ->select(DB::raw('MONTH(tgl_pelaporan) AS bulan'), DB::raw('YEAR(tgl_pelaporan) AS tahun'), DB::raw('COUNT(*) AS total'))
            ->groupBy(DB::raw('YEAR(tgl_pelaporan)'), DB::raw('MONTH(tgl_pelaporan)'))
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        $chartTotal = [];
        $labels = [];
        foreach ($laporanPerBulan as $row) {
            $labels[] = $row->tahun . ' ' . substr('JanFebMarAprMeiJunJulAguSepOktNovDes', ($row->bulan - 1) * 3, 3);
            $chartTotal[] = $row->total;
        }

        return response()->json([
            'data' => $chartTotal,
            'labels' => $labels,
        ]);
    }

    /**
     * AJAX: Get chart data (status laporan - doughnut).
     */
    public function chartStatusReport()
    {
        $statusCounts = DB::table('thsepelaporanbahaya AS t')
            ->select('t.status_pelaporan', 's.name AS status_name', DB::raw('COUNT(*) AS total'))
            ->leftJoin('thsedata_master AS s', 't.status_pelaporan', '=', 's.pk_hsedatamaster_id')
            ->groupBy('t.status_pelaporan', 's.name')
            ->get()
            ->keyBy('status_pelaporan');

        $labels = [];
        $data = [];
        $encryptedParams = [];

        $labelMap = [5 => 'Open', 6 => 'On Progress', 7 => 'Closed'];

        foreach ([5, 6, 7] as $s) {
            $row = $statusCounts->get($s);
            $count = $row?->total ?? 0;
            $labels[] = $labelMap[$s] . ' (' . $count . ')';
            $data[] = $count;
            $encryptedParams[] = encryptId((string) $s);
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'encryptedParams' => $encryptedParams,
        ]);
    }

    /**
     * AJAX: Chart jenis kondisi tidak aman (desc_kategori_bahaya WHERE kategori_bahaya=3).
     */
    public function chartJenisKondisiTidakAman()
    {
        $data = DB::table('thsepelaporanbahaya AS t')
            ->select(DB::raw('COALESCE(d.name, t.desc_kategori_bahaya) AS label'), DB::raw('COUNT(*) AS total'))
            ->leftJoin('thsedata_master AS d', 't.desc_kategori_bahaya', '=', 'd.pk_hsedatamaster_id')
            ->where('t.kategori_bahaya', 3)
            ->groupBy(DB::raw('COALESCE(d.name, t.desc_kategori_bahaya)'))
            ->orderByDesc('total')
            ->get();

        $labels = $data->pluck('label')->toArray();
        $values = $data->pluck('total')->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $values,
        ]);
    }

    /**
     * AJAX: Chart jenis tindakan tidak aman (desc_kategori_bahaya WHERE kategori_bahaya=4).
     */
    public function chartJenisTindakanTidakAman()
    {
        $data = DB::table('thsepelaporanbahaya AS t')
            ->select(DB::raw('COALESCE(d.name, t.desc_kategori_bahaya) AS label'), DB::raw('COUNT(*) AS total'))
            ->leftJoin('thsedata_master AS d', 't.desc_kategori_bahaya', '=', 'd.pk_hsedatamaster_id')
            ->where('t.kategori_bahaya', 4)
            ->groupBy(DB::raw('COALESCE(d.name, t.desc_kategori_bahaya)'))
            ->orderByDesc('total')
            ->get();

        $labels = $data->pluck('label')->toArray();
        $values = $data->pluck('total')->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $values,
        ]);
    }

    /**
     * AJAX: Get chart data (kategori bahaya - doughnut).
     */
    public function chartKategoriReport()
    {
        $kategoriCounts = DB::table('thsepelaporanbahaya AS t')
            ->select('k.pk_hsedatamaster_id', 'k.name', DB::raw('COUNT(*) AS total'))
            ->leftJoin('thsedata_master AS k', 't.kategori_bahaya', '=', 'k.pk_hsedatamaster_id')
            ->groupBy('k.pk_hsedatamaster_id', 'k.name')
            ->get();

        $labels = $kategoriCounts->pluck('name')->toArray();
        $data = $kategoriCounts->pluck('total')->toArray();
        $encryptedParams = $kategoriCounts->pluck('pk_hsedatamaster_id')
            ->map(fn($id) => encryptId((string) $id))
            ->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'encryptedParams' => $encryptedParams,
        ]);
    }

    /**
     * DataTable: Laporan dengan due date mendekati 30 hari.
     */
    public function duedatereportdatatable(Request $request)
    {
        $columns = [
            'due_date',
            'sisa_hari',
            'full_name',
            'employee_no',
            'lokasi_bahaya',
            'desc_temuan_bahaya',
            'kategori_bahaya',
            'status_pelaporan',
        ];
        $columnkey = 'pk_hsepelaporanbahaya_id';
        $selectColumn = [
            't.pk_hsepelaporanbahaya_id',
            't.full_name',
            't.employee_no',
            't.due_date',
            DB::raw('DATEDIFF(t.due_date, CURDATE()) AS sisa_hari'),
            DB::raw('COALESCE(lokasi_m.name, t.lokasi_bahaya) AS lokasi_bahaya'),
            DB::raw('COALESCE(kat_m.name, t.kategori_bahaya) AS kategori_bahaya'),
            DB::raw('COALESCE(status_m.name, t.status_pelaporan) AS status_pelaporan'),
            DB::raw('LEFT(t.desc_temuan_bahaya, 100) AS desc_temuan_bahaya'),
        ];
        $selectColumn[] = "t.$columnkey";
        $searchColumn = [
            't.full_name',
            't.employee_no',
            'lokasi_m.name',
            'kat_m.name',
            'status_m.name',
            't.desc_temuan_bahaya',
        ];
        $order = [$columns[0], 'asc'];

        if (isset($request["order"])) {
            $order = [$columns[$request['order']['0']['column']], $request['order']['0']['dir']];
        }

        // Base query
        $queryBuilder = DB::table('thsepelaporanbahaya AS t')
            ->select($selectColumn)
            ->leftJoin('thsedata_master AS lokasi_m', 't.lokasi_bahaya', '=', 'lokasi_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS kat_m', 't.kategori_bahaya', '=', 'kat_m.pk_hsedatamaster_id')
            ->leftJoin('thsedata_master AS status_m', 't.status_pelaporan', '=', 'status_m.pk_hsedatamaster_id')
            ->whereNotNull('t.due_date')
            ->where('t.status_pelaporan', '!=', 7) // exclude Closed
            ->whereBetween('t.due_date', [now()->subDays(1), now()->addDays(30)]);

        $alldata = (clone $queryBuilder)->count();

        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $queryBuilder->where(function ($query) use ($searchColumn, $searchValue) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%{$searchValue}%");
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
            $subdata = [];
            foreach ($columns as $column) {
                switch ($column) {
                    case 'sisa_hari':
                        $subdata[] = "<p class='p-1 bg-danger rounded text-center'>" . $data->$column . " Hari Lagi" . "</p>";
                        break;
                    case 'due_date':
                        $subdata[] = $data->due_date ? Carbon::parse($data->due_date)->format('d-m-Y') : '-';
                        break;
                    default:
                        $subdata[] = $data->$column ?? '-';
                        break;
                }
            }

            $id = encrypt($data->pk_hsepelaporanbahaya_id);
            $detail = '<a href="' . route('admin.reports.show', $id) . '" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></a>';
            $subdata[] = $detail;

            $dataresult[] = $subdata;
        }

        return response()->json([
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult
        ], 200);
    }
    /**
     * DataTable: 10 besar user yang melakukan pelaporan bahaya (ranking by jumlah laporan).
     */
    public function rankreportdatatable(Request $request)
    {
        $datafilter = [];
        if (isset($request['data'][0])) {
            $datafilter = $request['data'][0];
        }
        $columns = [
            'peringkat',
            'employee_no',
            'full_name',
            'posisi',
            'jumlah',
        ];

        // Leaderboard selalu urut berdasarkan peringkat (kolom 0 ascending)
        // Tidak mendengarkan request order dari DataTable — semua kolom non-orderable
        $order = [$columns[0], 'asc'];

        // Subquery: group by employee_no untuk menghitung jumlah laporan per user,
        // lengkap dengan ROW_NUMBER sebagai peringkat sebenarnya berdasarkan jumlah laporan.
        $sub = DB::table('thsepelaporanbahaya')
            ->select(
                'employee_no',
                DB::raw('MAX(full_name) AS full_name'),
                DB::raw('MAX(posisi) AS posisi'),
                DB::raw('COUNT(pk_hsepelaporanbahaya_id) AS jumlah'),
                DB::raw('ROW_NUMBER() OVER (ORDER BY COUNT(pk_hsepelaporanbahaya_id) DESC, MAX(tgl_pelaporan) DESC) AS peringkat')
            )
            ->groupBy('employee_no');

        // Total records: jumlah unique employee yang pernah melapor
        $alldata = (clone $sub)->get()->count();

        // Wrap subquery sebagai derived table untuk difilter dan diorder
        $queryBuilder = DB::table(DB::raw("({$sub->toSql()}) as ranked"))
            ->mergeBindings($sub);

        $searchColumn = ['ranked.employee_no', 'ranked.full_name', 'ranked.posisi'];

        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $queryBuilder->where(function ($query) use ($searchColumn, $searchValue) {
                foreach ($searchColumn as $column) {
                    $query->orWhere($column, 'like', "%{$searchValue}%");
                }
            });
        }

        $filteredrecordcount = (clone $queryBuilder)->count();

        $datas = $queryBuilder
            ->orderBy($order[0], $order[1])
            ->skip($request['start'])
            ->take($request['length'])
            ->get();

        $dataresult = [];
        foreach ($datas as $data) {
            $subdata = [];
            $peringkat = (int) $data->peringkat;

            // Kolom 0: peringkat — trophy/medal (berdasarkan peringkat sebenarnya dari DB)
            switch ($peringkat) {
                case 1:
                    $rankHtml = '<div class="rank-trophy rank-1">'
                        . '<i class="fas fa-trophy"></i>'
                        . '<span>#1</span>'
                        . '</div>';
                    break;
                case 2:
                    $rankHtml = '<div class="rank-trophy rank-2">'
                        . '<i class="fas fa-medal"></i>'
                        . '<span>#2</span>'
                        . '</div>';
                    break;
                case 3:
                    $rankHtml = '<div class="rank-trophy rank-3">'
                        . '<i class="fas fa-medal"></i>'
                        . '<span>#3</span>'
                        . '</div>';
                    break;
                default:
                    $rankHtml = '<div class="rank-number">#' . $peringkat . '</div>';
                    break;
            }
            $subdata[] = $rankHtml;

            // Kolom 1: employee_no
            $subdata[] = $data->employee_no ?? '-';

            // Kolom 2: full_name
            $subdata[] = '<span class="' . ($peringkat <= 3 ? 'fw-bold' : '') . '">' . ($data->full_name ?? '-') . '</span>';

            // Kolom 3: posisi
            $subdata[] = $data->posisi ?? '-';

            // Kolom 4: jumlah — badge laporan
            $badgeColor = match (true) {
                $peringkat === 1 => 'badge-gold',
                $peringkat === 2 => 'badge-silver',
                $peringkat === 3 => 'badge-bronze',
                default => 'badge-primary',
            };
            $subdata[] = '<span class="rank-badge ' . $badgeColor . '">'
                . '<i class="fas fa-file-alt mr-1"></i> '
                . $data->jumlah . ' Laporan</span>';

            $dataresult[] = $subdata;
        }

        return response()->json([
            "draw" => intval($request["draw"]),
            "recordsTotal" => $alldata,
            "recordsFiltered" => $filteredrecordcount,
            "data" => $dataresult,
        ], 200);
    }
}
