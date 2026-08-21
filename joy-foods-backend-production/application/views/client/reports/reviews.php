<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Customer Reviews</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Reports</a></li>
                            <li class="breadcrumb-item active">Customer Reviews</li>
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
                        <form class="needs-validation" id="reviews_filter_form" name="reviews_filter_form">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="date_from">From Date</label>
                                        <input type="text" class="form-control datepicker-init" readonly name="date_from" id="date_from" placeholder="Select date">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="date_to">To Date</label>
                                        <input type="text" class="form-control datepicker-init" readonly name="date_to" id="date_to" placeholder="Select date">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="company_id">Company</label>
                                        <select class="form-control select2" name="company_id" id="company_id">
                                            <option value="">All Companies</option>
                                            <?php if (!empty($companies)) { foreach ($companies as $company) { ?>
                                                <option value="<?php echo $company->id; ?>"><?php echo htmlspecialchars($company->name); ?></option>
                                            <?php }} ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="store_id">Store</label>
                                        <select class="form-control select2" name="store_id" id="store_id">
                                            <option value="">All Stores</option>
                                            <?php if (!empty($stores)) { foreach ($stores as $store) { ?>
                                                <option value="<?php echo $store->id; ?>"><?php echo htmlspecialchars($store->name); ?> (<?php echo $store->store_type; ?>)</option>
                                            <?php }} ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="module">Module</label>
                                        <select class="form-control select2" name="module" id="module">
                                            <option value="">All Modules</option>
                                            <option value="QSR">QSR</option>
                                            <option value="KOT">KOT</option>
                                            <option value="PREMEAL">PREMEAL</option>
                                        </select>
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
                                <div class="col-md-1">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button class="btn btn-outline-secondary w-100" type="button" id="clear_filters">
                                            <i class="uil-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row" id="summary_cards">
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Total Reviews</p>
                                <h4 class="mb-0" id="summary_total_reviews">0</h4>
                            </div>
                            <div class="mini-stat-icon avatar-sm align-self-center rounded-circle bg-primary">
                                <span class="avatar-title"><i class="uil-comment-alt-message font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Stores Reviewed</p>
                                <h4 class="mb-0" id="summary_stores_reviewed">0</h4>
                            </div>
                            <div class="mini-stat-icon avatar-sm align-self-center rounded-circle bg-success">
                                <span class="avatar-title"><i class="uil-store font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Companies Reviewed</p>
                                <h4 class="mb-0" id="summary_companies_reviewed">0</h4>
                            </div>
                            <div class="mini-stat-icon avatar-sm align-self-center rounded-circle bg-info">
                                <span class="avatar-title"><i class="uil-building font-size-24"></i></span>
                            </div>
                        </div>
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
                            <table id="reviews_data" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="3%">#</th>
                                        <th>Date</th>
                                        <th>Order #</th>
                                        <th>Company</th>
                                        <th>Store</th>
                                        <th>Employee</th>
                                        <th>Module</th>
                                        <th>Food Review</th>
                                        <th>Service Review</th>
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

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="order_detail_body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Review Modal -->
<div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReviewModalLabel">Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
