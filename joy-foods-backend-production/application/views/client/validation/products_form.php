<script>
// Toggle meal times section
function toggleMealTimes(checkbox) {
    var mealTimesSection = document.getElementById('meal_times_section');
    if (checkbox.checked) {
        mealTimesSection.style.display = 'block';
    } else {
        mealTimesSection.style.display = 'none';
        // Uncheck all meal type radio buttons
        document.getElementById('meal_type_breakfast').checked = false;
        document.getElementById('meal_type_lunch').checked = false;
        document.getElementById('meal_type_dinner').checked = false;
    }
}

$(document).ready(function() {

    // Initialize Category Tree for Add/Edit pages
    if ($('#category_tree').length > 0 && typeof categoryTreeData !== 'undefined') {
        var treeData = [];

        // Create a map for quick lookup
        var categoryMap = {};
        categoryTreeData.forEach(function(cat) {
            // Use folder icon for parent categories, file icon for child categories
            var icon = cat.parent_id ? 'uil-files-landscapes' : 'uil-folder';

            categoryMap[cat.id] = {
                id: cat.id.toString(),
                text: cat.name,
                parent: cat.parent_id ? cat.parent_id.toString() : '#',
                icon: icon
            };
        });

        // Convert to array
        for (var key in categoryMap) {
            treeData.push(categoryMap[key]);
        }

        // Add search box for categories
        $('#category_tree').before('<div class="mb-3"><input type="text" class="form-control form-control-sm" id="category_search" placeholder="Search categories..."></div>');

        // Initialize jsTree with checkbox and single selection
        $('#category_tree').jstree({
            'core': {
                'data': treeData,
                'multiple': false,
                'themes': {
                    'responsive': true,
                    'icons': true
                }
            },
            'checkbox': {
                'deselect_all': true,
                'three_state': false,
                'two_state': false
            },
            'plugins': ['checkbox', 'search']
        }).bind("loaded.jstree", function(event, data) {
            $(this).jstree("open_all");

            // Pre-select current category in tree (for edit page)
            if (typeof currentProductCategoryId !== 'undefined' && currentProductCategoryId) {
                $('#category_tree').jstree('select_node', currentProductCategoryId);
            }
        });

        // Handle category selection - auto-select in dropdown
        $('#category_tree').on('select_node.jstree', function(e, data) {
            var categoryId = data.node.id;
            if (categoryId) {
                $('#category_id').val(categoryId).trigger('change');
            }
        });

        // Handle category deselection - clear dropdown
        $('#category_tree').on('deselect_node.jstree', function(e, data) {
            $('#category_id').val('').trigger('change');
        });

        // Category search
        var searchTimeout = false;
        $('#category_search').keyup(function() {
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            searchTimeout = setTimeout(function() {
                var v = $('#category_search').val();
                $('#category_tree').jstree(true).search(v);
            }, 250);
        });
    }

    // Form validation for add product
    if ($('#add_product').length > 0) {

        // Track which submit button triggered the form
        var addAnother = false;
        $('#submit_button').on('click', function() { addAnother = false; });
        $('#submit_add_another').on('click', function() { addAnother = true; });

        // Reset the form so the user can immediately enter another product
        function resetAddProductForm($form) {
            $form[0].reset();

            // Clear formValidation icons/messages
            if ($form.data('formValidation')) {
                $form.data('formValidation').resetForm();
            }

            // Clear category selection (hidden field + jsTree)
            $('#category_id').val('');
            if ($('#category_tree').length && $('#category_tree').jstree(true)) {
                $('#category_tree').jstree('deselect_all');
            }

            // Clear image previews
            if (typeof selectedFiles !== 'undefined') {
                selectedFiles = [];
            }
            $('#images_preview').empty();

            // Hide PREMEAL meal-time section (premeal toggle is reset off)
            $('#meal_times_section').hide();

            // Focus product name for quick entry
            $('#name').focus();
            $('html, body').animate({ scrollTop: 0 }, 300);
        }

        $('#add_product').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Product name is required'
                        }
                    }
                },
                base_price: {
                    validators: {
                        notEmpty: {
                            message: 'Base price is required'
                        },
                        numeric: {
                            message: 'Base price must be a number'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            var $clicked = addAnother ? $('#submit_add_another') : $('#submit_button');
            var originalHtml = $clicked.html();

            $('#submit_button, #submit_add_another').prop('disabled', true);
            $clicked.html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: (function(f, fd) { f.find('select.select2').each(function() { fd.set(this.name, $(this).val() || ''); }); return fd; })($form, new FormData($form[0])),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);

                        if (addAnother) {
                            // Stay on the page and reset for the next product
                            resetAddProductForm($form);
                            $('#submit_button, #submit_add_another').prop('disabled', false);
                            $clicked.html(originalHtml);
                        } else {
                            setTimeout(function() {
                                window.location.href = base_url + 'client/products';
                            }, 2000);
                        }
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button, #submit_add_another').prop('disabled', false);
                        $clicked.html(originalHtml);
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button, #submit_add_another').prop('disabled', false);
                    $clicked.html(originalHtml);
                }
            });
        });
    }

    // Form validation for edit product
    if ($('#edit_product').length > 0) {
        $('#edit_product').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Product name is required'
                        }
                    }
                },
                base_price: {
                    validators: {
                        notEmpty: {
                            message: 'Base price is required'
                        },
                        numeric: {
                            message: 'Base price must be a number'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: (function(f, fd) { f.find('select.select2').each(function() { fd.set(this.name, $(this).val() || ''); }); return fd; })($form, new FormData($form[0])),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        setTimeout(function() {
                            window.location.href = base_url + 'client/products';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-1"></i> Update Product');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-1"></i> Update Product');
                }
            });
        });
    }
});
</script>
