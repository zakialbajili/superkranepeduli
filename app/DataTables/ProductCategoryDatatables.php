<?php

namespace App\DataTables;

use App\Models\ProductCategoryDatatable;
use App\Models\ProductCategoryModel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProductCategoryDatatables extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.productcategory.edit', encrypt($query->pk_productcategory_id)) . "' class='btn btn-sm btn-primary'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.productcategory.destroy', encrypt($query->pk_productcategory_id)) . "' class='btn btn-sm btn-danger ml-2 delete-item'><i class='far fa-trash-alt'></i></a>";

                return $editBtn . $deleteBtn;
            })
            ->addColumn('status', function ($query) {
                return '<label class="switch">
                <input type="checkbox" id="status" name="status"  data-id="' . encrypt($query->pk_productcategory_id) . '" class="change-status" ' . ($query->actived == 1 ? "checked" : '') . '>
                <span class="slider round"></span>
            </label>';
            })
            ->addColumn('simpanGudang', function ($query) {
                return '<label class="switch">
                <input type="checkbox" disabled ' . ($query->is_stored == 1 ? "checked" : '') . '>
                <span class="slider round"></span>
            </label>';
            })
            ->rawColumns(['action', 'simpanGudang', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductCategoryModel $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('productcategorydatatables-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Nama'),
            Column::computed('simpanGudang'),
            Column::computed('status'),
            Column::computed('action')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ProductCategoryDatatables_' . date('YmdHis');
    }
}
