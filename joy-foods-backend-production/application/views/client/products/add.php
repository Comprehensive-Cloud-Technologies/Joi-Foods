<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Add Product</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/products'); ?>">Products</a></li>
                            <li class="breadcrumb-item active">Add Product</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Product Information</h5>

                        <form class="needs-validation" id="add_product" name="add_product" action="<?php echo base_url('client/products/store'); ?>" method="post" enctype="multipart/form-data">
                            <!-- Hidden Category Field (populated by jsTree) -->
                            <input type="hidden" id="category_id" name="category_id" value="" data-fv-excluded="true">

                            <div class="row">
                                <!-- Product Name -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Product Name <code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required>
                                    </div>
                                </div>

                                <!-- Base Price -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="base_price">Base Price <code>*</code></label>
                                        <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" value="0" placeholder="Enter base price" required>
                                    </div>
                                </div>

                                <!-- Tax Percentage -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="tax_percentage">Tax Percentage (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="tax_percentage" name="tax_percentage" value="0" placeholder="Enter tax percentage (e.g., 18)">
                                    </div>
                                </div>

                                <!-- Calories -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="calories">Calories</label>
                                        <input type="number" class="form-control" id="calories" name="calories" placeholder="Enter calories (optional)">
                                    </div>
                                </div>

                                <!-- Hidden Display Order -->
                                <input type="hidden" name="display_order" value="0" data-fv-excluded="true">

                                <!-- Thumbnail -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="thumbnail">Product Thumbnail (Primary Image)</label>
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                        <small class="text-muted">Upload primary product image (JPG, PNG - Max 2MB)</small>
                                    </div>
                                </div>

                                <!-- Additional Images -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Additional Images (Gallery)</label>
                                        <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                                        <small class="text-muted">Select multiple images for product gallery (JPG, PNG - Max 2MB each)</small>

                                        <!-- Preview Container -->
                                        <div id="images_preview" class="row mt-3"></div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter product description"></textarea>
                                    </div>
                                </div>

                                <!-- Ingredients -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="ingredients">Ingredients</label>
                                        <textarea class="form-control" id="ingredients" name="ingredients" rows="2" placeholder="Enter ingredients list"></textarea>
                                    </div>
                                </div>

                                <!-- Module Access -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Module Access</label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="qsr_enabled" name="qsr_enabled" value="1">
                                                    <label class="form-check-label" for="qsr_enabled">QSR Module</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="kot_enabled" name="kot_enabled" value="1">
                                                    <label class="form-check-label" for="kot_enabled">KOT Module</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="premeal_enabled" name="premeal_enabled" value="1" onchange="toggleMealTimes(this)">
                                                    <label class="form-check-label" for="premeal_enabled">PREMEAL Module</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PREMEAL Meal Type -->
                                <div class="col-md-12" id="meal_times_section" style="display: none;">
                                    <div class="form-group mb-3">
                                        <label class="form-label">PREMEAL Meal Type <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="meal_type_breakfast" name="meal_type" value="BREAKFAST">
                                                    <label class="form-check-label" for="meal_type_breakfast">Breakfast</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="meal_type_lunch" name="meal_type" value="LUNCH">
                                                    <label class="form-check-label" for="meal_type_lunch">Lunch</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="meal_type_dinner" name="meal_type" value="DINNER">
                                                    <label class="form-check-label" for="meal_type_dinner">Dinner</label>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">Select the meal time when this product will be available for PREMEAL orders</small>
                                    </div>
                                </div>

                                <!-- Product Properties -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Product Properties</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1">
                                                    <label class="form-check-label" for="is_featured">Featured Product</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1" checked>
                                                    <label class="form-check-label" for="is_vegetarian">Vegetarian Product</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" id="submit_button" class="btn btn-primary">
                                    <i class="uil uil-check me-1"></i> Add Product
                                </button>
                                <button type="submit" id="submit_add_another" class="btn btn-success">
                                    <i class="uil uil-plus me-1"></i> Save &amp; Add Another
                                </button>
                                <a href="<?php echo base_url('client/products'); ?>" class="btn btn-secondary">
                                    <i class="uil uil-times me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Category Tree -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Categories</h5>
                        <div id="category_tree"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Category tree data for validation file
    var categoryTreeData = <?php echo json_encode($categories); ?>;

    // Multiple images preview and remove functionality
    var selectedFiles = [];

    document.getElementById('product_images').addEventListener('change', function(e) {
        var files = Array.from(e.target.files);
        selectedFiles = files;
        updateImagePreview();
    });

    function updateImagePreview() {
        var previewContainer = document.getElementById('images_preview');
        previewContainer.innerHTML = '';

        selectedFiles.forEach(function(file, index) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var col = document.createElement('div');
                col.className = 'col-md-3 col-sm-4 col-6 mb-2';
                col.innerHTML = `
                    <div class="position-relative border rounded p-1">
                        <img src="${e.target.result}" class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="removeImage(${index})">
                            <i class="uil uil-times"></i>
                        </button>
                        <small class="d-block text-truncate mt-1">${file.name}</small>
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        });

        // Update file input with selected files
        updateFileInput();
    }

    function removeImage(index) {
        selectedFiles.splice(index, 1);
        updateImagePreview();
    }

    function updateFileInput() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function(file) {
            dt.items.add(file);
        });
        document.getElementById('product_images').files = dt.files;
    }
</script>