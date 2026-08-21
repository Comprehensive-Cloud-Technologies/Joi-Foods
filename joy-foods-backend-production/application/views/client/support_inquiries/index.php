<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Support Inquiries</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Support</a></li>
                            <li class="breadcrumb-item active">Inquiries</li>
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
                        <form class="needs-validation" id="inquiries_filter_form" name="inquiries_filter_form">
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
                                        <label class="form-label" for="topic">Topic</label>
                                        <select class="form-control select2" name="topic" id="topic">
                                            <option value="">All Topics</option>
                                            <?php if (!empty($topics)) { foreach ($topics as $t) { ?>
                                                <option value="<?php echo htmlspecialchars($t->topic); ?>"><?php echo htmlspecialchars($t->topic); ?></option>
                                            <?php }} ?>
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
                            <table id="inquiries_data" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Company</th>
                                        <th>Employee</th>
                                        <th>Topic</th>
                                        <th>Subject</th>
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

<!-- View Inquiry Modal -->
<div class="modal fade" id="viewInquiryModal" tabindex="-1" aria-labelledby="viewInquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewInquiryModalLabel">Inquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Employee Info -->
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3"><i class="uil uil-user me-1"></i> Employee Details</h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="view_employee_name">-</span></p>
                                <p class="mb-1"><strong>Code:</strong> <span id="view_employee_code">-</span></p>
                                <p class="mb-1"><strong>Email:</strong> <span id="view_employee_email">-</span></p>
                                <p class="mb-0"><strong>Phone:</strong> <span id="view_employee_phone">-</span></p>
                            </div>
                        </div>
                    </div>
                    <!-- Inquiry Info -->
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3"><i class="uil uil-info-circle me-1"></i> Inquiry Info</h6>
                                <p class="mb-1"><strong>Company:</strong> <span id="view_company_name">-</span></p>
                                <p class="mb-1"><strong>Topic:</strong> <span id="view_topic" class="badge bg-soft-info text-info">-</span></p>
                                <p class="mb-0"><strong>Submitted:</strong> <span id="view_created_at">-</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject</label>
                            <div class="p-3 bg-light rounded" id="view_subject">-</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Message</label>
                            <div class="p-3 bg-light rounded" id="view_message" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
