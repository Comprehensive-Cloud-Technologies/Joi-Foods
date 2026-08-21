<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Store Items</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Product Management</a></li>
                            <li class="breadcrumb-item active">Store Items</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Filter Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">Filter Store Items</h4>
                            <a href="<?php echo base_url('client/store_products/add'); ?>" class="btn btn-primary">
                                <i class="uil uil-plus me-1"></i> Add Items to Store
                            </a>
                        </div>

                        <form class="needs-validation" id="filter_form" name="filter_form">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="store_id">Store <code>*</code></label>
                                        <select class="form-control select2" name="store_id" id="store_id" required>
                                            <option value="">Select Store</option>
                                            <?php if (!empty($stores)): ?>
                                                <?php foreach ($stores as $store): ?>
                                                    <option value="<?php echo $store->id; ?>"><?php echo $store->name; ?> (<?php echo $store->store_code; ?>)</option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="category_id">Category</label>
                                        <select class="form-control select2" name="category_id" id="category_id">
                                            <option value="all">All Categories</option>
                                            <?php if (!empty($categories)): ?>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <button class="btn btn-primary" type="submit" id="filter_button">
                                            <i class="uil-search-alt me-1"></i> Get Store Items
                                        </button>
                                        <button class="btn btn-secondary ms-2" type="button" id="reset_button">
                                            <i class="uil-redo me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Store Items List</h4>
                        <p class="card-title-desc text-muted" id="results_info">Select a store and click "Get Store Items" to view items.</p>

                        <!-- Bulk actions toolbar -->
                        <div class="d-flex justify-content-between align-items-center mb-2 d-none" id="items_toolbar">
                            <span class="text-muted"><span id="selected_count">0</span> selected</span>
                            <button type="button" class="btn btn-danger btn-sm" id="delete_selected_button" disabled>
                                <i class="uil uil-trash me-1"></i> Delete Selected
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="3%" class="text-center">
                                            <input type="checkbox" class="form-check-input" id="select_all_items" title="Select all">
                                        </th>
                                        <th width="5%">#</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Base Price</th>
                                        <th>Store Price</th>
                                        <th>Store</th>
                                        <th width="8%">Status</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="store_products_tbody">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No data available. Please filter to view store items.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
