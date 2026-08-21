<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="page-title-custom">Store Performance Report</h4>
                <p class="text-muted mb-0">Store-wise order volume and revenue breakdown</p>
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
                                <label class="form-label small text-muted">Store</label>
                                <select class="form-control select2" id="store_filter">
                                    <option value="">All Stores</option>
                                    <?php foreach ($stores as $store): ?>
                                        <option value="<?php echo $store->id; ?>"><?php echo $store->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label small text-muted">Module</label>
                                <select class="form-control select2" id="module_filter">
                                    <option value="">All Modules</option>
                                    <option value="QSR">QSR</option>
                                    <option value="KOT">KOT</option>
                                    <option value="PREMEAL">Premeal</option>
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
                                <p class="stat-label">Active Stores</p>
                                <h3 class="stat-value" id="summary_stores">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-store"></i>
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
                                <p class="stat-label">Total Orders</p>
                                <h3 class="stat-value" id="summary_orders">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
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
                                <p class="stat-label">Total Revenue</p>
                                <h3 class="stat-value" id="summary_total">Rs. 0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-rupee-sign"></i>
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
                                <p class="stat-label">Company Contributions</p>
                                <h3 class="stat-value" id="summary_company">Rs. 0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-bill"></i>
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
                            <table id="store_table" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Store</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th class="text-center">Orders</th>
                                        <th class="text-end">Company Paid</th>
                                        <th class="text-end">Employee Paid</th>
                                        <th class="text-end">Total Revenue</th>
                                        <th class="text-end">Avg / Order</th>
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
