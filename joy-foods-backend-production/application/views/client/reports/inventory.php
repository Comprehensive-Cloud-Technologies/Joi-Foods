<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Inventory Report</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Inventory Report</li>
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
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select class="form-control select2" id="filter_category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stock Status</label>
                                <select class="form-control select2" id="filter_stock_status">
                                    <option value="">All</option>
                                    <option value="in_stock">In Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="tracked">Tracked Only</option>
                                    <option value="unlimited">Unlimited</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary me-1" onclick="loadInventory()"><i class="uil uil-filter me-1"></i>Filter</button>
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
                                <p class="stat-label">Total Products</p>
                                <h3 class="stat-value" id="total_products">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-box"></i>
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
                                <p class="stat-label">In Stock</p>
                                <h3 class="stat-value text-success" id="in_stock">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-check-circle"></i>
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
                                <p class="stat-label">Out of Stock</p>
                                <h3 class="stat-value text-danger" id="out_of_stock">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-times-circle"></i>
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
                                <p class="stat-label">Total Units (Tracked)</p>
                                <h3 class="stat-value text-info" id="total_units">0</h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-layer-group"></i>
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
                        <h5 class="card-title mb-4">Current Stock Levels</h5>
                        <div class="table-responsive">
                            <table id="inventory_table" class="table table-hover" style="width:100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Store</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Module</th>
                                        <th>Current Stock</th>
                                        <th>Stock Status</th>
                                        <th>Product Status</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody id="inventory_tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
