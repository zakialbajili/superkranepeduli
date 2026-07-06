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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <h3 class="card-title">Menu Builder</h3>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div>
                                                <button type="button" class="btn btn-success btn-xs"
                                                    onclick="menu_create();"><i class="fa fa-plus"></i> Create</button>
                                                <button type="button" class="btn btn-warning btn-xs"
                                                    onclick="menu_rename();"><i class="glyphicon glyphicon-pencil"></i>
                                                    Rename</button>
                                                <button type="button" class="btn btn-danger btn-xs"
                                                    onclick="menu_delete();"><i class="glyphicon glyphicon-remove"></i>
                                                    Delete</button>
                                            </div>
                                            <form id="menu_builder" name="menu_builder" style="min-height:250px">
                                                <div id="menutree"></div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <h3 class="card-title">Informasi Menu</h3>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form id="menu_action" name="menu_action" class="form-horizontal">
                                                <div class="form-group row">
                                                    <label for="menuid" class="col-sm-3 col-form-label">Menu ID</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="menuid"
                                                            name="menuid" readonly>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-3  col-form-label">Nama</label>
                                                    <div class="col-sm-9">
                                                        <label id="menuname" name="menuname" class="form-control"></label>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="module" class="col-sm-3 col-form-label">Module</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control select2" id="module" name="module">
                                                            <option value="0">Pilih Module</option>
                                                            @foreach ($datamodule as $item)
                                                                <option value="{{ encryptId($item->pk_module_id) }}">
                                                                    {{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="module_action"
                                                        class="col-sm-3 col-form-label">Action</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control select2" id="module_action"
                                                            name="module_action">

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-3 col-form-label">URL</label>
                                                    <div class="col-sm-9">
                                                        <label id="menu_url" name="menu_url" class="form-control"></label>
                                                    </div>
                                                </div>
                                                <div class="row mt-10">
                                                    <div class="col-md-12 form-actions">
                                                        <button id="save_menu" class="btn btn-xs btn-success"><i
                                                                class="fa fa-floppy-o"></i> Save Menu Information</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <!-- /.card-footer -->
                        <div class="card-footer">
                            <button id="cancel" class="btn btn-default">Cancel</button>
                            <button id="save" class="btn btn-info float-right">Submit</button>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#menutree').jstree({
                "core": {
                    "animation": 0,
                    "check_callback": true,
                    "themes": {
                        "stripes": true
                    },
                    'data': {!! $datamenu !!}
                },
                "types": {
                    "#": {
                        "max_children": 12,
                        "max_depth": 6,
                    },
                    "default": {
                        "icon": "fas fa-folder",
                    },
                },
                "plugins": [
                    "contextmenu", "dnd", "search",
                    "state", "types", "wholerow"
                ]
            });
        });
        $("#cancel").click(function(e) {
            e.preventDefault();
            window.location = "{!! route('admin.menus.index') !!}";
        });
        $("#save").click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Menyimpan Menu ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Iya, Simpan Data ini!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.LoadingOverlay("show");
                    $.ajax({
                        type: "post",
                        url: "{!! route('admin.menus.savemenu') !!}",
                        data: {
                            data: JSON.stringify($('#menutree').jstree(false).get_json('#', {
                                no_a_attr: true,
                                no_li_attr: true,
                                no_state: true,
                                no_id: true,
                            }))
                        },
                        success: function(response) {
                            $.LoadingOverlay("hide");
                            if (response.status == "success") {
                                window.location = "{!! route('admin.modules.index') !!}";
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            $.LoadingOverlay("hide");
                            toastr.error("Simpan Menu Gagal");
                        }
                    });
                }
            });

        });

        $("#menutree").on("select_node.jstree", function(evt, data) {

            $('#module').val("0");
            $('#module').trigger('change');
            $("#menuname").text("");
            $("#menuid").val('');
            $("#menu_url").text('');

            $nodedata = data.node.data;
            $node = data.node;
            if ($nodedata != null) {
                $('#module').val($nodedata.module_id).trigger('change');
                $("#menu_url").text($nodedata.url);
            }

            $.ajax({
                type: "post",
                url: "{!! route('admin.menus.getmoduleactionsinmoduleid') !!}",
                data: {
                    "id": $('#module').val()
                },
                success: function(response) {
                    if ($('#module_action').select2('data').length > 0) {
                        $('#module_action').empty().trigger("change");
                    }
                    if (response.status == "error") {
                        toastr.warning(response.message);
                        return;
                    }

                    $moduleaction = response.data;

                    //generate module action
                    var newOption = new Option("Silahkan Pilih", "", false, false);
                    $('#module_action').append(newOption).trigger('change');

                    $('#module_action').select2({
                        data: $moduleaction
                    });



                    $('#module_action').val($nodedata.module_action_id).trigger('change');

                }
            });

            // moduleactionselect();
            $("#menuname").text($node.text);
            $("#menuid").val($node.id);
        });

        $('#module').on('select2:select', function(e) {
            $.ajax({
                type: "post",
                url: "{!! route('admin.menus.getmoduleactionsinmoduleid') !!}",
                data: {
                    "id": $('#module').val()
                },
                success: function(response) {

                    if ($('#module_action').select2('data').length > 0) {
                        $('#module_action').empty().trigger("change");
                    }
                    if (response.status == "error") {
                        toastr.warning(response.message);
                        return;
                    }

                    $moduleaction = response.data;

                    var newOption = new Option("Silahkan Pilih", "", true, false);
                    $('#module_action').append(newOption).trigger('change');
                    //generate module action

                    $('#module_action').select2({
                        data: $moduleaction
                    });

                }
            });
        });

        $('#module_action').on('change', function(e) {
            $("#menu_url").text('');
            if ($(this).select2('data').length > 0) {
                $("#menu_url").text($(this).select2('data')[0].url);
            }
        });

        $("#save_menu").click(function(e) {
            e.preventDefault();
            var node_id = $("#menuid").val();
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Menyimpan Menu ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Iya, Simpan Data ini!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var module_id = $("#module").val();
                    var module_action_id = $("#module_action").val();
                    var module_url = $("#menu_url").text();
                    var data = {
                        "module_id": module_id,
                        "module_action_id": module_action_id,
                        "url": module_url
                    };
                    $('#menutree').jstree(true).get_node(node_id).data = data;
                    /*$("#module").text('');*/
                    $('#module').val('').trigger('change');
                    $('#module_action').val('').trigger('change');
                    $("#module_action").text('');
                    $("#menu_url").text('');
                    $("#menuname").text('');
                    $("#menuid").text('');
                    toastr.success("Simpan Menu Berhasil");
                }
            });
        });

        function menu_create() {
            var ref = $('#menutree').jstree(true),
                sel = ref.get_selected();
            if (!sel.length) {
                return false;
            }
            sel = sel[0];
            sel = ref.create_node(sel, {
                "type": "file",
                "icon": "fas fa-folder",
                "valid_children": []
            });
            if (sel) {
                ref.edit(sel);
            }
        };

        function menu_rename() {
            var ref = $('#menutree').jstree(true),
                sel = ref.get_selected();
            if (!sel.length) {
                return false;
            }
            sel = sel[0];
            ref.edit(sel);
        };

        function menu_delete() {
            var ref = $('#menutree').jstree(true),
                sel = ref.get_selected(),
                nodeval = ref.get_node(sel[0]),
                tree = $('#menutree');
            if (!sel.length) {
                toastr.warning("Silahkan Pilih Menu");
                return false;
            }
            if (sel[0] == "root") {
                toastr.warning("Anda Tidak Dapat Menghapus Root");
                return false
            };
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Anda tidak dapat mengembalikan data!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Iya, Hapus Data ini!'
            }).then((result) => {
                if (result.isConfirmed) {
                    ref.delete_node(sel);
                    toastr.success("Hapus Menu Berhasil");
                }
            });
        };
    </script>
@endpush
