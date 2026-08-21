<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Bulk Import Products</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/products'); ?>">Products</a></li>
                                <li class="breadcrumb-item active">Bulk Import</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="<?php echo base_url('client/products/bulk_import_history'); ?>" class="btn btn-outline-secondary">
                        <i class="uil uil-history me-1"></i> Import History
                    </a>
                </div>
            </div>
        </div>

        <!-- STAGE 1: Upload Form -->
        <div id="upload_stage">
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Upload Product File</h5>

                            <div class="alert alert-info">
                                <i class="uil uil-info-circle me-1"></i>
                                Upload a spreadsheet (.xlsx, .xls or .csv) with product data. We'll preview the data and let you confirm before any changes are saved. Max 500 rows, max file size 5 MB.
                            </div>

                            <form id="import_form" enctype="multipart/form-data" action="<?php echo base_url('client/products/bulk_import_preview'); ?>" method="post">
                                <div class="mb-3">
                                    <label class="form-label">File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="import_file" name="import_file" accept=".xlsx,.xls,.csv" required>
                                </div>

                                <hr>
                                <h6 class="mb-3">Options</h6>

                                <div class="mb-3">
                                    <label class="form-label">If product name already exists</label>
                                    <select class="form-control" name="duplicate_strategy">
                                        <option value="SKIP" selected>Skip duplicates (recommended)</option>
                                        <option value="UPDATE">Update the existing product</option>
                                    </select>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="auto_create_categories" name="auto_create_categories" value="1" checked>
                                    <label class="form-check-label" for="auto_create_categories">
                                        <strong>Auto-create missing categories</strong>
                                        <br><small class="text-muted">Unknown category names will be created automatically. Module flags are inferred from the products using them.</small>
                                    </label>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary" id="preview_btn">
                                        <i class="uil uil-eye me-1"></i> Upload &amp; Preview
                                    </button>
                                    <a href="<?php echo base_url('client/products'); ?>" class="btn btn-light">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Need the template?</h5>
                            <p class="text-muted">Download the template with the required headers and two example rows. Fill it in, then upload.</p>
                            <a href="<?php echo base_url('client/products/download_template'); ?>" class="btn btn-success">
                                <i class="uil uil-download-alt me-1"></i> Download Template
                            </a>

                            <hr>
                            <h6>Notes</h6>
                            <ul class="text-muted small mb-0">
                                <li>Required: <code>product_name</code>, <code>category_name</code>, <code>base_price</code></li>
                                <li>At least one of <code>qsr_enabled</code> / <code>kot_enabled</code> / <code>premeal_enabled</code> must be Y</li>
                                <li><code>meal_type</code> is required when PREMEAL is Y</li>
                                <li>Boolean values accept Y / N / 1 / 0</li>
                                <li>Images are not part of bulk import — add them via Edit after</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAGE 2: Preview (rendered by JS) -->
        <div id="preview_stage" style="display:none;">
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card">
                        <div class="stat-card-body">
                            <p class="stat-label">Total Rows</p>
                            <h3 class="stat-value" id="pv_total">0</h3>
                            <small class="text-muted" id="pv_filename">-</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card">
                        <div class="stat-card-body">
                            <p class="stat-label">New Products</p>
                            <h3 class="stat-value text-success" id="pv_new">0</h3>
                            <small class="text-muted">Will be created</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card">
                        <div class="stat-card-body">
                            <p class="stat-label">Updates / Skips</p>
                            <h3 class="stat-value text-info"><span id="pv_updates">0</span> / <span id="pv_skips">0</span></h3>
                            <small class="text-muted">Existing products</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card">
                        <div class="stat-card-body">
                            <p class="stat-label">Errors</p>
                            <h3 class="stat-value text-danger" id="pv_errors">0</h3>
                            <small class="text-muted"><span id="pv_new_cats">0</span> new categories</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="pv_new_categories_card" style="display:none;">
                <div class="card-body py-2">
                    <strong><i class="uil uil-folder-plus me-1 text-success"></i> New categories to be created:</strong>
                    <span id="pv_new_categories_list" class="text-muted"></span>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="pv_tabs">
                        <li class="nav-item"><a class="nav-link active" data-status="ALL" href="#">All <span class="badge bg-secondary ms-1" id="pv_count_all">0</span></a></li>
                        <li class="nav-item"><a class="nav-link" data-status="NEW" href="#">New <span class="badge bg-success ms-1" id="pv_count_new">0</span></a></li>
                        <li class="nav-item"><a class="nav-link" data-status="UPDATE" href="#">Update <span class="badge bg-info ms-1" id="pv_count_update">0</span></a></li>
                        <li class="nav-item"><a class="nav-link" data-status="SKIP" href="#">Skip <span class="badge bg-warning ms-1" id="pv_count_skip">0</span></a></li>
                        <li class="nav-item"><a class="nav-link" data-status="ERROR" href="#">Errors <span class="badge bg-danger ms-1" id="pv_count_error">0</span></a></li>
                    </ul>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="pv_table">
                            <thead class="table-light">
                                <tr>
                                    <th>Row</th>
                                    <th>Status</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Base Price</th>
                                    <th>Tax %</th>
                                    <th>Modules</th>
                                    <th>Meal Type</th>
                                    <th>Issues</th>
                                </tr>
                            </thead>
                            <tbody id="pv_tbody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <a href="<?php echo base_url('client/products/bulk_import'); ?>" class="btn btn-light">
                            <i class="uil uil-times me-1"></i> Cancel &amp; Upload Different File
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-danger me-1" id="download_errors_btn" style="display:none;">
                                <i class="uil uil-file-download-alt me-1"></i> Download Error Report
                            </button>
                            <button type="button" class="btn btn-primary" id="commit_btn">
                                <i class="uil uil-check me-1"></i> Confirm Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
