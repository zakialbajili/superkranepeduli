@extends('backend.layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $headerparam['headertag'] }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a
                                href="{{ $headerparam['parentlink'] }}">{{ $headerparam['parentname'] }}</a></li>
                        <li class="breadcrumb-item active">{{ $headerparam['headername'] }}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="card card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-unread-tab" data-toggle="pill"
                                    href="#custom-tabs-unread" role="tab" aria-controls="custom-tabs-unread"
                                    aria-selected="true">Unread</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-read-tab" data-toggle="pill" href="#custom-tabs-read"
                                    role="tab" aria-controls="custom-tabs-read" aria-selected="false">Read</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <div class="tab-pane fade show active" id="custom-tabs-unread" role="tabpanel"
                                aria-labelledby="custom-tabs-unread-tab">
                                <div class="table-responsive">
                                    <table id="dataTable" class="table table-striped display" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Deskripsi</th>
                                                <th>Tindakan</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="custom-tabs-read" role="tabpanel"
                                aria-labelledby="custom-tabs-read-tab">
                                <div class="table-responsive">
                                    <table id="readdataTable" class="table table-striped display" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Deskripsi</th>
                                                <th>Tindakan</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
@endsection
@push('scripts')
    @include('js.notification.index')
@endpush
