<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Inventory Transactions</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Inventory Transactions</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Company</label>
                                <select class="form-control select2" id="filter_company">
                                    <option value="">All Companies</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?php echo $company->id; ?>"><?php echo $company->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Store</label>
                                <select class="form-control select2" id="filter_store">
                                    <option value="">All Stores</option>
                                    <?php foreach ($stores as $store): ?>
                                        <option value="<?php echo $store->id; ?>"><?php echo $store->name; ?> (<?php echo $store->store_type; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select class="form-control select2" id="filter_type">
                                    <option value="">All</option>
                                    <option value="IN">Stock In</option>
                                    <option value="OUT">Stock Out</option>
                                    <option value="SET">Adjustment (Set)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Source</label>
                                <select class="form-control select2" id="filter_source">
                                    <option value="">All Sources</option>
                                    <option value="ORDER_PLACED">Order Placed</option>
                                    <option value="ORDER_REJECTED">Order Rejected</option>
                                    <option value="ORDER_CANCELLED">Order Cancelled</option>
                                    <option value="MANUAL_UPDATE">Manual Update</option>
                                    <option value="INITIAL_STOCK">Initial Stock</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="text" class="form-control" id="filter_date_from" placeholder="Start date" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="text" class="form-control" id="filter_date_to" placeholder="End date" readonly>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary me-1" onclick="loadTransactions()"><i class="uil uil-filter me-1"></i>Filter</button>
                                <button type="button" class="btn btn-light" onclick="resetFilters()"><i class="uil uil-redo me-1"></i>Reset</button>
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
                                <p class="stat-label">Total Transactions</p>
                                <h3 class="stat-value" id="total_transactions">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-exchange"></i>
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
                                <p class="stat-label">Total Stock In</p>
                                <h3 class="stat-value text-success" id="total_in">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-arrow-down-left"></i>
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
                                <p class="stat-label">Total Stock Out</p>
                                <h3 class="stat-value text-danger" id="total_out">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-arrow-up-right"></i>
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
                                <p class="stat-label">Adjustments</p>
                                <h3 class="stat-value text-info" id="total_adjustments">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-edit"></i>
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
                        <h5 class="card-title mb-4">Stock Movement Ledger</h5>
                        <div class="table-responsive">
                            <table id="transactions_table" class="table table-hover" style="width:100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Store</th>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Before</th>
                                        <th>After</th>
                                        <th>Source</th>
                                        <th>Reference</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody id="transactions_tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
