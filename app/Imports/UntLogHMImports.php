<?php

namespace App\Imports;

use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UntLogHMImports implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $htmldatafound = "";
        $htmldatanotfound = "";
        $i = 1;
        if (count($collection) > 1) {
            foreach ($collection as $item) {
                if ($item[0] == "Kode Unit") {
                    continue;
                }
                $descrption = "";
                $dataunit = DB::table('tunit')
                    ->select('description', 'pk_unit_id')
                    ->where('unit_no', $item[0])
                    ->first();
                if ($dataunit) {
                    $descrption = $dataunit->description;
                    $htmldatafound .= '<tr>
                        <td style="display: none"><label id="id">' . encryptId($dataunit->pk_unit_id) . '</label></td>
                        <td style="min-width: 100px"><label id="unitno">' . strtoupper($item[0]) . '</label></td>
                        <td style="min-width: 300px"><label id="hmdescription">' . strtoupper($descrption) . '</label></td>
                        <td style="min-width: 100px"><label id="hmatas">' . strtoupper($item[1]) . '</label></td>
                        <td style="min-width: 100px"><label id="hmbawah">' . strtoupper($item[2]) . '</label></td>
                        <td style="min-width: 300px"><label id="notes" class="bg-success color-palette">Kode Unit Ditemukan</label></td>
                    </tr>';
                } else {
                    $htmldatanotfound .= '<tr>
                    <td style="display: none"><label id="id"></label></td>
                    <td style="min-width: 100px"><label id="unitno">' . strtoupper($item[0]) . '</label></td>
                    <td style="min-width: 300px"><label id="hmdescription"></label></td>
                    <td style="min-width: 100px"><label id="hmatas">' . strtoupper($item[1]) . '</label></td>
                    <td style="min-width: 100px"><label id="hmbawah">' . strtoupper($item[1]) . '</label></td>
                    <td style="min-width: 300px"><label id="notes" class="bg-danger color-palette">Kode Unit Tidak Ditemukan</label></td>
                </tr>';
                }
                $i++;
            }
        }

        $this->data = array("content" => $htmldatanotfound . $htmldatafound, "count" => $i);
    }
}
