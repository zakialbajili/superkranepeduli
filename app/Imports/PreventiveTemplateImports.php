<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PreventiveTemplateImports implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public $data;
    public function collection(Collection $collection)
    {
        // #items: array:21 [
        //     0 => "No"
        //     1 => "Deskripsi"
        //     2 => "Mingguan"
        //     3 => "2 Mingguan"
        //     4 => "1 Bulan"
        //     5 => "1.5 Bulan"
        //     6 => "3 Bulan"
        //     7 => "6 Bulan"
        //     8 => "1 Tahun"
        //     9 => "2 Tahun"
        //     10 => "250 H"
        //     11 => "500 H"
        //     12 => "1000 H"
        //     13 => "2000 H"
        //     14 => "5000 H"
        //     15 => "6000 H"
        //     16 => "10000 H"
        //     17 => "15000 H"
        //     18 => "25000 H"
        //     19 => "30000 H"
        //     20 => "40000 H"
        //   ]
        $htmldata = "";
        $i = 1;
        if (count($collection) > 1) {
            foreach ($collection as $item) {
                if ($item[0] == "No") {
                    continue;
                }
                
                $htmldata .= '<tr>
                        <td style="display: none">' . $i . '</td>
                        <td style="min-width: 75px"><input type="text" name="no" class="form-control" value="' . strtoupper($item[0]) . '"></td>
                        <td style="min-width: 300px"><input type="text" name="description" class="form-control" value="' . strtoupper($item[1]) . '"></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="weekly" ' . ($item[2] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="biweekly" ' . ($item[3] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="monthly" ' . ($item[4] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="1.5monthly" ' . ($item[5] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="3monthly" ' . ($item[6] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="6monthly" ' . ($item[7] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="yearly" ' . ($item[8] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="2yearly" ' . ($item[9] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="250h" ' . ($item[10] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="500h" ' . ($item[11] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="1000h" ' . ($item[12] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="2000h" ' . ($item[13] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="5000h" ' . ($item[14] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="6000h" ' . ($item[15] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="10000h" ' . ($item[16] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="15000h" ' . ($item[17] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="25000h" ' . ($item[18] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="30000h" ' . ($item[19] == 1 ? "checked" : "") . ' /></td>
                        <td style="min-width: 75px" class="center"><input type="checkbox" class="checkmark" name="40000h" ' . ($item[20] == 1 ? "checked" : "") . ' /></td>
                        <td><button class="btn btn-sm btn-danger ml-2 delete-row"><i class="fa fa-trash"></i></button></td>
                    </tr>';
                $i++;
            }
        }

        $this->data = array("content" => $htmldata, "count" => $i);
    }
}
