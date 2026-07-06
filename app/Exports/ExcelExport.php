<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExcelExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle {
    private $setData;
    private $setHeader;
    private $setSheetTitle;

    public function __construct(Collection $data, $header = [], $title = null) {
        $this->setData = $data;
        $this->setSheetTitle = $title;
        $this->setHeader = $header;
    }

    public function array(): array {
        $dt = $this->setData->toArray();
        return $dt;
    }

    public function headings(): array {
        // $dataHeader = array_keys(json_decode(json_encode($this->setData[0]), true));
        $dataHeader = $this->setHeader;
        return $dataHeader;
    }

    public function title(): string {
        return is_null($this->setSheetTitle) ? 'Main' : $this->setSheetTitle;
    }
    public function failed(Throwable $exception): void {
        Log::info($exception);
    }
}