<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Account Deletion Requests</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Support</a></li>
                            <li class="breadcrumb-item active">Account Deletion Requests</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form class="needs-validation" id="requests_filter_form" name="requests_filter_form">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="company_id">Company</label>
                                        <select class="form-control select2" name="company_id" id="company_id">
                                            <option value="">All Companies</option>
                                            <?php if (!empty($companies)) { foreach ($companies as $company) { ?>
                                                <option value="<?php echo $company->id; ?>"><?php echo $company->name; ?></option>
                                            <?php }} ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="status_1">Status</label>
                                        <select class="form-control select2" name="status" id="status_1">
                                            <option value="">All Statuses</option>
                                            <option value="PENDING">Pending</option>
                                            <option value="PROCESSED">Processed</option>
                                            <option value="REJECTED">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="date_from">From Date</label>
                                        <input type="text" class="form-control datepicker-init" readonly name="date_from" id="date_from" placeholder="Select date" value="<?php echo $default_date_from; ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="date_to">To Date</label>
                                        <input type="text" class="form-control datepicker-init" readonly name="date_to" id="date_to" placeholder="Select date" value="<?php echo $default_date_to; ?>">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button class="btn btn-primary w-100" type="submit" id="submit_button">
                                            <i class="uil-search-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button class="btn btn-outline-secondary w-100" type="button" id="clear_filters">
                                            <i class="uil-times me-1"></i> Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="requests_data" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Employee</th>
                                        <th>Status</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRequestModalLabel">Deletion Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Request Info -->
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3"><i class="uil uil-info-circle me-1"></i> Request Info</h6>
                                <p class="mb-1"><strong>Company:</strong> <span id="view_company_name">-</span></p>
                                <p class="mb-1"><strong>Company Code:</strong> <span id="view_company_code">-</span></p>
                                <p class="mb-1"><strong>Submitted Email:</strong> <span id="view_email">-</span></p>
                                <p class="mb-1"><strong>Status:</strong> <span id="view_status" class="badge bg-soft-secondary text-secondary">-</span></p>
                                <p class="mb-1"><strong>Submitted:</strong> <span id="view_created_at">-</span></p>
                                <p class="mb-0"><strong>IP Address:</strong> <span id="view_ip_address">-</span></p>
                            </div>
                        </div>
                    </div>
                    <!-- Matched Employee -->
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3"><i class="uil uil-user me-1"></i> Matched Employee</h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="view_employee_name">-</span></p>
                                <p class="mb-1"><strong>Code:</strong> <span id="view_employee_code">-</span></p>
                                <p class="mb-1"><strong>Email:</strong> <span id="view_employee_email">-</span></p>
                                <p class="mb-0"><strong>Phone:</strong> <span id="view_employee_phone">-</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Internal Note</label>
                            <textarea class="form-control" id="process_note" rows="2" placeholder="Optional note (saved when you mark the request)"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="process_request_id" value="">
                <button type="button" class="btn btn-danger" id="btn_reject"><i class="uil uil-times me-1"></i> Reject</button>
                <button type="button" class="btn btn-success" id="btn_process"><i class="uil uil-check me-1"></i> Mark Processed</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
