<!DOCTYPE html>
<html>
<head>
    <title>Morales Subscribers Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: 'Lato', sans-serif;
        }
        .disconnected-container{
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .disconnected-container i{
            font-size: 5rem;
        }
        .disconnected-container h5{
            margin: 20px 0px;
        }
        .token-form textarea{
            margin: 5px 0px 20px;
        }
        .token-form label{
            font-weight:500;
        }
        .btn i{
            margin-right: 10px;
        }
        .error-field{
            border: 1px solid #ff0000;
        }
        .dashboard-screen{
            display:none;
        }
        .mailer-lite-status-container{
            padding: 10px 0px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #cecece;
            border-bottom: 1px solid #cecece;
            margin-bottom: 20px;
        }
        .subscriber-sub-title{
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        .btn-subscriber-action i{
            margin-right: 0px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row mt-4">
            <div class="col-md-6 mx-auto dashboard-screen" id="authorize_screen">
               
                <div class="form-group token-form">
                    <div class="disconnected-container">
                        <i class="fa-solid fa-ban text-warning"></i>
                        <h5>Disconnected from API</h5>
                    </div>
                    <label for="api_key">API Key:</label>
                    <textarea data-required="true" data-required-type="apikey" class="form-control" id="api_key" name="api_key"></textarea>
                </div>
                <button id="save_token_btn" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Save</button>
            </div>
             @include('subscribers_list')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DatatTAbles -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- Moment -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        class DashboardHandler {
            #authorize_screen = '#authorize_screen';
            #subscribers_screen = '#subscribers_main_screen';
            #authorized = {{ $authorized ? 'true' : 'false' }};
            #data_table = null;
            editing_item = null;

            init(){
                const _context = this;
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $(document).ready(function() {
                    if (_context.#authorized === false) {
                        $(_context.#authorize_screen).fadeIn('fast');
                    } else {
                        _context.#bindSubscribersScreen();
                        $(_context.#subscribers_screen).fadeIn('fast');
                    }

                    $('#subscriber_form_modal').on('hidden.bs.modal', function () {
                        $('#subscriber_email').unbind('blur');
                        $('#save_subscriber_btn').unbind('click');
                        _context.#restartForm();
                    });
                    $('#subscriber_form_modal').on('shown.bs.modal', function () {
                        if(_context.editing_item === null){
                            $('#subscriber_email').blur(function() {
                                if(_context.#validateEmail($(this).val())){
                                _context.#checkIfEmailExists(this); 
                                }
                            });
                        }
                        $('#save_subscriber_btn').click(function() {
                            if (_context.#validForm('#subscriber_form')) {
                                _context.#saveSubscriberForm(this);
                            }
                        });
                    });

                    $('#disconnect_btn').click(function() {
                        _context.#deleteKey($(this));
                    });
                    $('#save_token_btn').click(function(e) {
                        if (_context.#validForm('.token-form')) {
                            _context.#saveKey($(this));
                        }
                    });
                });
            }
            
            #restartForm(hide, show, callback){
                $('#subscriber_name').val('');
                $('#subscriber_email').val('');
                $('#country_name').val('');
                this.editing_item = null;
                $('#subscriber_email').prop('readonly', false);
                $('#subscriber_form_modal_label').text('Adding new Subscriber.');
            }

            #fadeHideShow(hide, show, callback){
                $(hide).fadeOut('fast', function(){
                    if(typeof callback === 'function'){
                        callback();
                    }
                    $(show).fadeIn('fast');
                });
            }

            #btnLoading(elem, status) {
                const icon = elem.find('i');
                let css_class = 'fa-solid fa-circle-notch fa-spin';
                if(status !== false){
                    icon.data('real', icon.attr('class'));
                    status = true;
                }else{
                    css_class = icon.data('real');
                }
                elem.prop('disabled', status);
                icon.removeClass().addClass(css_class);
            }

            #validForm(form) {
                let form_valid = true;
                const _context = this;
                $(form).find('[data-required="true"]').each(function(){
                    const value = $(this).val();
                    let valid_field = true;
                    if ($(this).data('required-type') === 'apikey' && value.length < 20) {
                        valid_field = false;
                    } else if ($(this).data('required-type') === 'email' && !_context.#validateEmail(value)) {
                        valid_field = false;
                    } else if (value === '') {
                        valid_field = false;
                    }

                    if (valid_field === true) {
                        $(this).removeClass('error-field');
                    }else {
                        form_valid = false;
                        $(this).addClass('error-field');
                    }
                });
                return form_valid;
            }

            #apiDisconnected() {
                const _context = this;
                this.#authorized = false;
                this.#fadeHideShow(this.#subscribers_screen, this.#authorize_screen, function() {
                    _context.#data_table.destroy();
                    _context.#data_table =  null;
                    //DESTROY TABLE
                });
            }

            #deleteKey(btn) {
                this.#btnLoading(btn);
                const _context = this;
                $.ajax({
                    type: 'POST',
                    url: '{{ route('api_key.delete') }}',
                    data: {
                        api_key: $('#api_key').val(),
                    },
                    complete: function(xhr, status) {
                        let message = 'Apikey deleted successfully';
                        let icon = 'success';
                        if (status === 'success' && xhr.responseJSON.status === 'success') {
                            _context.#apiDisconnected();
                        } else {
                            message = 'An error ocurred.';
                            icon = 'error';
                        }
                        Swal.fire({
                            title: message,
                            icon: icon,
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        _context.#btnLoading(btn, false);
                    },
                });
            }

            #saveSubscriberForm(btn) {
                this.#btnLoading($(btn));
                $('#cancel_subscriber_btn').prop('disabled', true);
                const _context = this;
                const editing =  this.editing_item !== null;
                const data_subscriber = !editing ? {
                    name: $('#subscriber_name').val(),
                    email: $('#subscriber_email').val(),
                    country: $('#country_name').val(),
                } : {
                    id: this.editing_item.id,
                    name: $('#subscriber_name').val(),
                    country: $('#country_name').val(),
                };
                $.ajax({
                    type: 'POST',
                    url: !editing ? '{{ route('subscriber.store') }}' : '{{ route('subscriber.update') }}',
                    data: data_subscriber,
                    complete: function(xhr, status) {
                        let message = 'Subscriber saved successfully';
                        let icon = 'success';
                        if (status === 'success' && xhr.responseJSON.status === 'success') {
                            $('#subscriber_form_modal').modal('hide');
                            if(editing === true){
                                const row = _context.#data_table.rows().eq(0).filter(function (idx) {
                                    return _context.#data_table.row(idx).data().id === _context.editing_item.id ? true : false;
                                });

                                data_subscriber.email = $('#subscriber_email').val();
                                _context.#data_table.row(row).data(data_subscriber).draw();
                            }else{
                                _context.#data_table.ajax.reload();
                            }
                        } else {
                            message = 'An error ocurred.';
                            if (xhr.responseJSON?.code === 400) {
                                message = 'Email address entered already exists.';
                                $('#subscriber_email').val('');
                            } else if (xhr.responseJSON?.code === 422) {
                                if (xhr.responseJSON.errors?.email !== undefined) {
                                    message = "The email must be a valid email address.";
                                }
                            } else if (xhr.responseJSON?.code === 401) {
                                message = "Your API key has expired and is no longer allowed.";
                                $('#subscriber_form_modal').modal('hide');
                                _context.#apiDisconnected();
                            }
                            icon = 'error';
                        }
                        Swal.fire({
                            text: message,
                            icon: icon,
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        _context.#btnLoading($(btn), false);
                        $('#cancel_subscriber_btn').prop('disabled', false);
                    },
                });
            }

            #saveKey(btn) {
                this.#btnLoading(btn);
                const _context = this;
                $.ajax({
                    type: 'POST',
                    url: '{{ route('api_key.store') }}',
                    data: {
                        api_key: $('#api_key').val(),
                    },
                    complete: function(xhr, status) {
                        let message = 'Apikey saved successfully';
                        let icon = 'success';
                        if (status === 'success' && xhr.responseJSON.status === 'success') {
                            _context.#authorized = true;
                            _context.#fadeHideShow(_context.#authorize_screen, _context.#subscribers_screen, function() {
                                _context.#bindSubscribersScreen();
                                $('#api_key').val('');
                            });
                        } else {
                            message = 'An error ocurred.';
                            icon = 'error';
                        }
                        Swal.fire({
                            title: message,
                            icon: icon,
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        _context.#btnLoading(btn, false);
                    },
                });
            }
            
            #checkIfEmailExists(elem) {
                $(elem).prop('readonly', true);
                $('#save_subscriber_btn').prop('disabled', true);
                $('#basic_addon_email i').removeClass().addClass('fa-solid fa-circle-notch fa-spin');
                $.ajax({
                    type: 'GET',
                    url: 'subscribers/email/check/' + $('#subscriber_email').val(),
                    complete: function(xhr, status) {
                        if (status === 'success' && xhr.responseJSON.status === 'success' && xhr.responseJSON.exists === true) {
                            Swal.fire({
                                text: 'Email address entered already exists.',
                                icon: 'error',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                            $(elem).val('');
                        }
                        $('#save_subscriber_btn').prop('disabled', false);
                        $(elem).prop('readonly', false);
                        $('#basic_addon_email i').removeClass().addClass('fa-regular fa-envelope');
                    },
                });
                
            }
            
            #bindSubscribersPage(){
                const _context = this;   
                $('.btn-subscriber-delete').click(function() {
                    let subs_delete = null;
                    const id_edit = $(this).data('id');
                    const map = _context.#data_table.rows({ page: 'current' }).data().toArray();
                    //let item_edit = null;
                    for (const subs_key in map) {
                        if (id_edit === map[subs_key].id) {
                            subs_delete = map[subs_key]
                            break;
                        }
                    }
                    if(subs_delete !== null){
                        _context.#btnLoading($(this));
                        const btn = $(this);
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('subscriber.delete') }}',
                            data: {
                                id: subs_delete.id
                            },
                            complete: function(xhr, status) {
                                let message = 'Subscriber saved successfully';
                                let icon = 'success';
                                if (status === 'success' && xhr.responseJSON.status === 'success') {
                                     _context.#data_table.ajax.reload();
                                } else {
                                    message = 'An error ocurred.';
                                    if (xhr.responseJSON?.code === 404) {
                                        message = 'Invalid subscriber id';
                                    } else if (xhr.responseJSON?.code === 422) {
                                        if (xhr.responseJSON.errors?.id !== undefined) {
                                            message = "Subscriber id required";
                                        }
                                    } else if (xhr.responseJSON?.code === 401) {
                                        message = "Your API key has expired and is no longer allowed.";
                                        $('#subscriber_form_modal').modal('hide');
                                        _context.#apiDisconnected();
                                    }
                                    _context.#btnLoading(btn, false);
                                    icon = 'error';
                                }
                                Swal.fire({
                                    text: message,
                                    icon: icon,
                                    timer: 3500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                            },
                        });
                    }
                });
                $('.btn-subscriber-edit').click(function() {
                    _context.editing_item = null;
                    const id_edit = $(this).data('id');
                    const map = _context.#data_table.rows({ page: 'current' }).data().toArray();
                    //let item_edit = null;
                    for (const subs_key in map) {
                        if (id_edit === map[subs_key].id) {
                            _context.editing_item = map[subs_key]
                            break;
                        }
                    }
                    if(_context.editing_item !== null){
                        $('#subscriber_form_modal_label').text('Editing subscriber.');
                        $('#subscriber_name').val(_context.editing_item.name);
                        $('#subscriber_email').prop('readonly', true);
                        $('#subscriber_email').val(_context.editing_item.email);
                        $('#country_name').val(_context.editing_item.country);
                        $('#subscriber_form_modal').modal('show');
                    }
                });
            }
            
            #validateEmail(email){
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            #bindSubscribersScreen() {
                const _context = this;                
                this.#data_table = $('#subscribers-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('subscribers.index') }}",
                        error: function(xhr, error, thrown) {
                            let message = 'An error ocurred.';
                            if(xhr.status === 401){
                                message = "Your API key has expired and is no longer allowed.";
                                _context.#apiDisconnected();
                            }
                            Swal.fire({
                                text: message,
                                icon: 'error',
                                timer: 3500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        },
                    },
                    drawCallback: function() {
                        _context.#bindSubscribersPage();
                    },
                    columns: [
                        { data: 'email', name: 'email' },
                        { data: 'name', name: 'name' },
                        { data: 'country', name: 'country' },
                        {
                            data: 'subscribed_at',
                            name: 'subscribed_at_date',
                            render: function(data) {
                                return moment(data).format('DD/MM/YYYY');
                            }
                        },
                        {
                            data: 'subscribed_at',
                            name: 'subscribed_at_time',
                            render: function(data) {
                                return moment(data).format('HH:MM:ss');
                            }
                        },
                        {
                            data: 'id',
                            render: function(data, row) {
                                return '<button data-id="' + data + '" class="btn btn-primary btn-subscriber-edit btn-subscriber-action"><i class="fa-regular fa-pen-to-square"></i></button> ' +
                                        '<button data-id="' + data + '" class="btn btn-danger btn-subscriber-delete btn-subscriber-action"><i class="fa-solid fa-trash"></i></button>';
                            }
                        }
                    ]
                });
            }
        }

        // Instantiate DashboardHandler class
        const dashboard = new DashboardHandler();
        dashboard.init();
    </script>
</body>
</html>
