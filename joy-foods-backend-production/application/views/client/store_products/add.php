<style>
    .remove_item {
        background: #f46a6a;
        height: 30px;
        width: 30px;
        display: block;
        text-align: center;
        color: #fff;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
    }

    .multi-product {
        cursor: pointer;
    }

    .existing-badge {
        font-size: 10px;
    }

    /* Keep the search bar fixed and the table header sticky while the
       product list scrolls inside the selection modal */
    #productSelectModal .modal-scroll-body {
        max-height: 55vh;
        overflow-y: auto;
    }

    /* border-collapse:separate is required so the sticky header cells paint
       over the scrolling rows; borders are redrawn with box-shadow */
    #productSelectModal .modal-scroll-body table {
        border-collapse: separate;
        border-spacing: 0;
    }

    #productSelectModal .modal-scroll-body thead th {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 3;
        box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Add Items To Store</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/store_products'); ?>">Store Items</a></li>
                            <li class="breadcrumb-item active">Add To Store</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Product Filter Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Product Filter</h4>
                        <p class="card-title-desc text-muted">Filter products by category to add multiple items at once.</p>

                        <form class="needs-validation" id="products_filter_form" name="products_filter_form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="filter_category">Category</label>
                                        <select class="form-control select2" name="filter_category" id="filter_category">
                                            <option value="all">All Categories</option>
                                            <?php if (!empty($categories)): ?>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <button class="btn btn-primary" type="submit" id="filter_products_button">
                                            <i class="uil-search-alt me-1"></i> Get Products
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Items Form -->
        <form class="needs-validation" id="add_items_to_store" name="add_items_to_store" action="<?php echo base_url('client/store_products/store') ?>">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <h4 class="card-title mb-0">Store Items</h4>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control typeahead" id="auto_complete_product_name" name="auto_complete_product_name" placeholder="Search product by name...">
                                </div>
                                <div class="col-md-4">
                                    <select name="store_id" id="store_id" class="form-control select2" required>
                                        <option value="">Select Store</option>
                                        <?php if (!empty($stores)): ?>
                                            <?php foreach ($stores as $store): ?>
                                                <option value="<?php echo $store->id; ?>"><?php echo $store->name; ?> (<?php echo $store->store_code; ?>)</option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 pt-3">
                                    <!-- Table toolbar (bulk actions) -->
                                    <div class="d-flex justify-content-between align-items-center mb-2 d-none" id="table_toolbar">
                                        <span class="text-muted"><span id="selected_count">0</span> selected</span>
                                        <button type="button" class="btn btn-danger btn-sm" id="remove_selected_button" disabled>
                                            <i class="uil uil-trash me-1"></i> Remove Selected
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table width="100%" class="table table-bordered" id="products_table">
                                            <thead>
                                                <tr>
                                                    <th width="3%" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="select_all_rows" title="Select all">
                                                    </th>
                                                    <th width="5%">S.No.</th>
                                                    <th>Product Name</th>
                                                    <th>Base Price</th>
                                                    <th width="15%">Store Price</th>
                                                    <th>Category</th>
                                                    <th width="8%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="prodtable">
                                                <!-- Products will be added here dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted text-center" id="empty_message">Search for products or use category filter to add items.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer row d-none" id="card_footer">
                            <div class="col-md-12 text-center">
                                <button class="btn btn-success mx-auto d-inline-block" type="submit" id="submit_button">
                                    <i class="uil uil-check me-2"></i> Save Store Items
                                </button>
                                <button class="btn btn-warning mx-auto d-inline-block ms-2" type="button" id="cancel_button" onclick="cancel_items()">
                                    <i class="uil uil-exclamation-triangle me-2"></i> Cancel
                                </button>
                                <a href="<?php echo base_url('client/store_products'); ?>" class="btn btn-secondary mx-auto d-inline-block ms-2">
                                    <i class="uil uil-arrow-left me-2"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- Product Selection Modal (shown after category fetch) -->
<div class="modal fade" id="productSelectModal" tabindex="-1" aria-labelledby="productSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productSelectModalLabel">Select Products to Add</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">All products are selected by default. Uncheck any you don't want, then click <strong>Add Selected</strong>.</p>
                <div class="mb-3">
                    <input type="text" class="form-control" id="modal_search" placeholder="Filter products by name...">
                </div>
                <div class="table-responsive modal-scroll-body">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="modal_select_all" checked title="Select all">
                                </th>
                                <th>Product Name</th>
                                <th width="18%">Base Price</th>
                                <th width="22%">Category</th>
                                <th width="20%">Status</th>
                            </tr>
                        </thead>
                        <tbody id="modal_product_list">
                            <!-- Fetched products rendered here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto"><span id="modal_selected_count">0</span> selected</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="modal_add_selected">
                    <i class="uil uil-plus me-1"></i> Add Selected
                </button>
            </div>
        </div>
    </div>
</div>
