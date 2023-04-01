<div class="col-md-12 dashboard-screen" id="subscribers_main_screen">
    <!-- Modal -->
    <div class="modal fade" id="subscriber_form_modal" tabindex="-1" aria-labelledby="subscriber_form_modal_label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="subscriber_form_modal_label">Adding new Subscriber.</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="subscriber_form">
                    <div class="mb-3">
                        <label for="subscriber_name" class="form-label">Subscriber's name:</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <input type="text" data-required="true" id="subscriber_name" class="form-control" placeholder="Name..." aria-label="Name" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subscriber_email" class="form-label">Email address:</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic_addon_email">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" data-required-type="email" data-required="true" class="form-control" id="subscriber_email" placeholder="name@example.com" aria-label="Email address" aria-describedby="basic_addon_email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="country_name" class="form-label">Subscriber's country:</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fa-solid fa-earth-americas"></i>
                            </span>
                            <input type="text" data-required="true" class="form-control" id="country_name" placeholder="Country name...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancel_subscriber_btn" class="btn btn-warning" data-bs-dismiss="modal"><i class="fa-solid fa-ban"></i> Close</button>
                    <button type="button" id="save_subscriber_btn" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i> Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <h4>Morales Subscribers Dashboard</h4>
    <div class="mailer-lite-status-container">
        <span class="">MailerLite API: <b>Connected.</b></span>
        <button id="disconnect_btn" class="btn btn-danger"><i class="fa-solid fa-power-off"></i> Disconnect</button>
    </div>
    <div class="subscriber-sub-title">
        <h5>Subscribers</h5>
        <button id="add_subs_btn" data-bs-toggle="modal" data-bs-target="#subscriber_form_modal" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Subscriber</button>
    </div>
    <table id="subscribers-table" class="table">
        <thead>
            <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Country</th>
            <th>Subscription Date</th>
            <th>Subscription Time</th>
            <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>