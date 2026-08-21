<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Product</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/products'); ?>">Products</a></li>
                            <li class="breadcrumb-item active">Edit Product</li>
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

                        <form class="needs-validation" id="edit_product" name="edit_product" action="<?php echo base_url('client/products/update'); ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="product_id" value="<?php echo $product->id; ?>" data-fv-excluded="true">
                            <input type="hidden" name="existing_thumbnail" value="<?php echo $product->thumbnail; ?>" data-fv-excluded="true">
                            <input type="hidden" id="existing_images" name="existing_images" value="<?php echo htmlspecialchars($product->images ?? ''); ?>" data-fv-excluded="true">
                            <!-- Hidden Category Field (populated by jsTree) -->
                            <input type="hidden" id="category_id" name="category_id" value="<?php echo $product->category_id; ?>" data-fv-excluded="true">

                            <div class="row">
                                <!-- Product Name -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Product Name <code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $product->name; ?>" placeholder="Enter product name" required>
                                    </div>
                                </div>

                                <!-- Base Price -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="base_price">Base Price <code>*</code></label>
                                        <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" value="<?php echo $product->base_price; ?>" placeholder="Enter base price" required>
                                    </div>
                                </div>

                                <!-- Tax Percentage -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="tax_percentage">Tax Percentage (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="tax_percentage" name="tax_percentage" value="<?php echo isset($product->tax_percentage) ? $product->tax_percentage : 0; ?>" placeholder="Enter tax percentage (e.g., 18)">
                                    </div>
                                </div>

                                <!-- Calories -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="calories">Calories</label>
                                        <input type="number" class="form-control" id="calories" name="calories" value="<?php echo $product->calories; ?>" placeholder="Enter calories (optional)">
                                    </div>
                                </div>

                                <!-- Hidden Display Order -->
                                <input type="hidden" name="display_order" value="0" data-fv-excluded="true">

                                <!-- Thumbnail -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="d-flex align-items-center mb-1">
                                            <label class="form-label mb-0" for="thumbnail">Product Thumbnail (Primary Image)</label>
                                            <button type="button" id="thumbnail_paste_btn" class="btn btn-sm btn-link p-0 ms-2 lh-1" title="Paste image from clipboard">
                                                <i class="uil uil-clipboard-notes font-size-18"></i>
                                            </button>
                                        </div>
                                        <?php if (!empty($product->thumbnail)): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo base_url($product->thumbnail); ?>" alt="Current Thumbnail" class="img-thumbnail" style="max-width: 200px;">
                                                <p class="text-muted mb-0"><small>Current thumbnail</small></p>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                        <small class="text-muted">Upload new image to replace (JPG, PNG - Max 2MB)</small>

                                        <div id="thumbnail_paste_preview" class="mt-2"></div>
                                    </div>
                                </div>

                                <!-- Additional Images -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Additional Images (Gallery)</label>

                                        <!-- Existing Images Preview -->
                                        <div id="existing_images_preview" class="row mb-2">
                                            <?php
                                            $existing_images_array = [];
                                            if (!empty($product->images)) {
                                                $existing_images_array = json_decode($product->images, true);
                                                if (is_array($existing_images_array)) {
                                                    foreach ($existing_images_array as $index => $img_path):
                                            ?>
                                                <div class="col-md-3 col-sm-4 col-6 mb-2" id="existing_img_<?php echo $index; ?>">
                                                    <div class="position-relative border rounded p-1">
                                                        <img src="<?php echo base_url($img_path); ?>" class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;">
                                                        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="removeExistingImage(<?php echo $index; ?>)">
                                                            <i class="uil uil-times"></i>
                                                        </button>
                                                        <small class="d-block text-truncate mt-1">Existing</small>
                                                    </div>
                                                </div>
                                            <?php
                                                    endforeach;
                                                }
                                            }
                                            ?>
                                        </div>

                                        <input type="file" class="form-control" id="product_images" name="product_images[]" accept="image/*" multiple>
                                        <small class="text-muted">Select multiple images to add to gallery (JPG, PNG - Max 2MB each)</small>

                                        <!-- New Images Preview Container -->
                                        <div id="images_preview" class="row mt-3"></div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter product description"><?php echo $product->description; ?></textarea>
                                    </div>
                                </div>

                                <!-- Ingredients -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="ingredients">Ingredients</label>
                                        <textarea class="form-control" id="ingredients" name="ingredients" rows="2" placeholder="Enter ingredients list"><?php echo $product->ingredients; ?></textarea>
                                    </div>
                                </div>

                                <!-- Module Access -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Module Access</label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="qsr_enabled" name="qsr_enabled" value="1" <?php echo $product->qsr_enabled ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="qsr_enabled">QSR Module</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="kot_enabled" name="kot_enabled" value="1" <?php echo $product->kot_enabled ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="kot_enabled">KOT Module</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="premeal_enabled" name="premeal_enabled" value="1" <?php echo $product->premeal_enabled ? 'checked' : ''; ?> onchange="toggleMealTimes(this)">
                                                    <label class="form-check-label" for="premeal_enabled">PREMEAL Module</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PREMEAL Meal Type -->
                                <div class="col-md-12" id="meal_times_section" style="display: <?php echo $product->premeal_enabled ? 'block' : 'none'; ?>;">
                                    <div class="form-group mb-3">
                                        <label class="form-label">PREMEAL Meal Type <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="meal_type_breakfast" name="meal_type" value="BREAKFAST" <?php echo $product->breakfast ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="meal_type_breakfast">Breakfast</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="meal_type_lunch" name="meal_type" value="LUNCH" <?php echo $product->lunch ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="meal_type_lunch">Lunch</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" id="meal_type_dinner" name="meal_type" value="DINNER" <?php echo $product->dinner ? 'checked' : ''; ?>>
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
                                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $product->is_featured ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="is_featured">Featured Product</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_vegetarian" name="is_vegetarian" value="1" <?php echo $product->is_vegetarian ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="is_vegetarian">Vegetarian Product</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" id="submit_button" class="btn btn-primary">
                                    <i class="uil uil-check me-1"></i> Update Product
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
var currentProductCategoryId = '<?php echo $product->category_id; ?>';

// Existing images management
var existingImages = <?php echo $product->images ?? '[]'; ?>;
if (!Array.isArray(existingImages)) {
    existingImages = [];
}

// New images management
var selectedFiles = [];

// Remove existing image
function removeExistingImage(index) {
    // Remove from array
    existingImages.splice(index, 1);
    // Update hidden input
    document.getElementById('existing_images').value = JSON.stringify(existingImages);
    // Remove from DOM
    var element = document.getElementById('existing_img_' + index);
    if (element) {
        element.remove();
    }
    // Re-render remaining existing images with new indices
    renderExistingImages();
}

function renderExistingImages() {
    var container = document.getElementById('existing_images_preview');
    container.innerHTML = '';

    existingImages.forEach(function(imgPath, index) {
        var col = document.createElement('div');
        col.className = 'col-md-3 col-sm-4 col-6 mb-2';
        col.id = 'existing_img_' + index;
        col.innerHTML = `
            <div class="position-relative border rounded p-1">
                <img src="<?php echo base_url(); ?>${imgPath}" class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;">
                <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="removeExistingImage(${index})">
                    <i class="uil uil-times"></i>
                </button>
                <small class="d-block text-truncate mt-1">Existing</small>
            </div>
        `;
        container.appendChild(col);
    });

    // Update hidden input
    document.getElementById('existing_images').value = JSON.stringify(existingImages);
}

// Multiple new images preview and remove functionality
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
                    <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="removeNewImage(${index})">
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

function removeNewImage(index) {
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

// ---------------- Paste thumbnail from clipboard ----------------
(function() {
    var pasteBtn = document.getElementById('thumbnail_paste_btn');
    var thumbInput = document.getElementById('thumbnail');
    var previewBox = document.getElementById('thumbnail_paste_preview');
    var pasteActive = false;

    if (!pasteBtn || !thumbInput) {
        return;
    }

    // Activate paste mode on click (icon highlights while active)
    pasteBtn.addEventListener('click', function() {
        pasteActive = true;
        pasteBtn.classList.add('text-primary');
        pasteBtn.focus();
    });
    pasteBtn.addEventListener('blur', function() {
        pasteActive = false;
        pasteBtn.classList.remove('text-primary');
    });

    // Handle paste only while the icon is active
    document.addEventListener('paste', function(e) {
        if (!pasteActive) {
            return;
        }

        var clipboard = e.clipboardData || window.clipboardData;
        if (!clipboard || !clipboard.items) {
            return;
        }

        for (var i = 0; i < clipboard.items.length; i++) {
            var item = clipboard.items[i];
            if (item.type && item.type.indexOf('image') === 0) {
                var blob = item.getAsFile();
                if (!blob) {
                    continue;
                }
                e.preventDefault();

                // Build a named File so the CI upload library accepts the extension
                var ext = (blob.type.split('/')[1] || 'png').toLowerCase();
                if (ext === 'jpeg') {
                    ext = 'jpg';
                }
                var fileName = 'pasted-thumbnail-' + Date.now() + '.' + ext;
                var file = new File([blob], fileName, { type: blob.type });

                // Assign the pasted file to the thumbnail file input
                var dt = new DataTransfer();
                dt.items.add(file);
                thumbInput.files = dt.files;

                showPastedPreview(file);

                pasteActive = false;
                pasteBtn.classList.remove('text-primary');
                return;
            }
        }

        toastr["info"]("No image found in the clipboard. Copy an image first, then paste.");
    });

    // When a file is chosen normally, drop any pasted preview to avoid confusion
    thumbInput.addEventListener('change', function() {
        if (thumbInput.files.length && previewBox.dataset.pasted === '1') {
            previewBox.innerHTML = '';
            previewBox.dataset.pasted = '';
        }
    });

    function showPastedPreview(file) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            previewBox.dataset.pasted = '1';
            previewBox.innerHTML =
                '<div class="position-relative d-inline-block border rounded p-1">' +
                    '<img src="' + ev.target.result + '" class="img-thumbnail mb-0" style="max-width: 200px;">' +
                    '<button type="button" class="btn btn-danger btn-sm position-absolute" ' +
                        'style="top: 5px; right: 5px; padding: 2px 6px;" id="clear_pasted_thumb">' +
                        '<i class="uil uil-times"></i>' +
                    '</button>' +
                '</div>' +
                '<p class="text-success mb-0"><small><i class="uil uil-check-circle me-1"></i>Pasted image ready to upload</small></p>';

            document.getElementById('clear_pasted_thumb').addEventListener('click', function() {
                thumbInput.value = '';
                previewBox.innerHTML = '';
                previewBox.dataset.pasted = '';
            });
        };
        reader.readAsDataURL(file);
    }
})();
</script>
