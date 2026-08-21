<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="page-title-custom">KOT Report</h4>
                <p class="text-muted mb-0">Pantry / Delivery order reports with department-wise billing</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label small text-muted">From Date</label>
                                <input type="text" class="form-control" id="date_from" placeholder="Select date" readonly>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label small text-muted">To Date</label>
                                <input type="text" class="form-control" id="date_to" placeholder="Select date" readonly>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label small text-muted">Department</label>
                                <select class="form-control select2" id="department_filter">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept->id; ?>"><?php echo $dept->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label small text-muted">Status</label>
                                <select class="form-control select2" id="status_filter">
                                    <option value="">All Status</option>
                                    <option value="PENDING">Pending</option>
                                    <option value="CONFIRMED">Confirmed</option>
                                    <option value="PREPARING">Preparing</option>
                                    <option value="READY">Ready</option>
                                    <option value="DELIVERED">Delivered</option>
                                    <option value="COMPLETED">Completed</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label small text-muted">Order Type</label>
                                <select class="form-control select2" id="order_type_filter">
                                    <option value="">All Orders</option>
                                    <option value="employee">Employee</option>
                                    <option value="guest">Guest</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-8">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" id="btn_filter">
                                        <i class="uil uil-filter me-1"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary" id="btn_reset">
                                        <i class="uil uil-redo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Orders</p>
                                <h3 class="stat-value" id="summary_orders">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Amount</p>
                                <h3 class="stat-value" id="summary_total">Rs. 0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Company Paid</p>
                                <h3 class="stat-value" id="summary_company">Rs. 0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-bill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Employee Paid</p>
                                <h3 class="stat-value" id="summary_employee">Rs. 0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="kot_table" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Department</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Company</th>
                                        <th class="text-end">Employee</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
