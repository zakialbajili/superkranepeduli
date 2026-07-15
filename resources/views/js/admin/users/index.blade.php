<script>
        $(function() {
            var obj_User = dataTableBoilerPlate(
                'dataTable',
                "{{ route('admin.user-mobile.datatables') }}",
                {},
            )

            $('body').on('click', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.user-mobile.change-status') }}",
                    method: 'PUT',
                    data: {
                        status: isChecked,
                        id: id
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            toastr.success(data.message)
                        } else {
                            toastr.error(data.message)
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                })
            })
        });
    </script>