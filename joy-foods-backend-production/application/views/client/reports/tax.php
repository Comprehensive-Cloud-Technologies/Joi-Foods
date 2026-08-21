<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Tax Report</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Tax Report</li>
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
                                <label class="form-label">Module</label>
                                <select class="form-control select2" id="filter_module">
                                    <option value="">All Modules</option>
                                    <option value="QSR">QSR</option>
                                    <option value="KOT">KOT</option>
                                    <option value="PREMEAL">PREMEAL</option>
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
                                <button type="button" class="btn btn-primary me-1" onclick="loadTax()"><i class="uil uil-filter me-1"></i>Filter</button>
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
                                <p class="stat-label">Total Orders</p>
                                <h3 class="stat-value" id="total_orders">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
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
                                <p class="stat-label">Taxable Value</p>
                                <h3 class="stat-value text-info" id="taxable_value">&#8377;0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-money-bill"></i>
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
                                <p class="stat-label">Total Tax (GST)</p>
                                <h3 class="stat-value text-success" id="total_tax">&#8377;0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-percentage"></i>
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
                                <p class="stat-label">Gross Total</p>
                                <h3 class="stat-value text-dark" id="gross_total">&#8377;0.00</h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-bill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax by Slab -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Tax Summary by GST Slab</h5>
                        <p class="text-muted small mb-3">CGST and SGST shown as an equal split of total GST (intra-state supply).</p>
                        <div class="table-responsive">
                            <table id="slab_table" class="table table-hover mb-0" style="width:100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>GST Rate</th>
                                        <th>Orders</th>
                                        <th>Taxable Value</th>
                                        <th>CGST</th>
                                        <th>SGST</th>
                                        <th>Total Tax</th>
                                        <th>Gross Total</th>
                                    </tr>
                                </thead>
                                <tbody id="slab_tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order-wise Tax Detail -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order-wise Tax Detail</h5>
                        <div class="table-responsive">
                            <table id="tax_orders_table" class="table table-hover" style="width:100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Company</th>
                                        <th>Store</th>
                                        <th>Module</th>
                                        <th>Taxable Value</th>
                                        <th>Tax (GST)</th>
                                        <th>Discount</th>
                                        <th>Gross Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tax_orders_tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
