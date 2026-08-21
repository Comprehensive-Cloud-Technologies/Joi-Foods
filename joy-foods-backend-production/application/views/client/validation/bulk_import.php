<script>
var previewData = null;
var currentTab = 'ALL';

$(document).ready(function() {
    $('#import_form').on('submit', function(e) {
        e.preventDefault();

        var fileInput = document.getElementById('import_file');
        if (!fileInput.files.length) {
            toastr["error"]("Please select a file");
            return;
        }

        if (fileInput.files[0].size > 5 * 1024 * 1024) {
            toastr["error"]("File too large. Max 5 MB.");
            return;
        }

        var btnHtml = $('#preview_btn').html();
        $('#preview_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading & parsing...');

        var fd = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: fd,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                try {
                    var obj = JSON.parse(response);
                    if (obj.status === 'success') {
                        previewData = obj;
                        renderPreview();
                        $('#upload_stage').hide();
                        $('#preview_stage').show();
                        window.scrollTo(0, 0);
                    } else {
                        toastr["error"]("Error", obj.message || 'Failed to parse file');
                        $('#preview_btn').prop('disabled', false).html(btnHtml);
                    }
                } catch (err) {
                    toastr["error"]("Error", "Could not parse server response");
                    $('#preview_btn').prop('disabled', false).html(btnHtml);
                }
            },
            error: function() {
                toastr["error"]("Error", "Upload failed. Please try again.");
                $('#preview_btn').prop('disabled', false).html(btnHtml);
            }
        });
    });

    // Tab clicks
    $(document).on('click', '#pv_tabs a', function(e) {
        e.preventDefault();
        $('#pv_tabs a').removeClass('active');
        $(this).addClass('active');
        currentTab = $(this).data('status');
        renderRows();
    });

    // Confirm import
    $('#commit_btn').on('click', function() {
        if (!previewData) return;

        var hasAction = (previewData.summary.new_products + previewData.summary.updates) > 0;
        if (!hasAction) {
            toastr["warning"]("Nothing to import — no valid rows or only skips");
            return;
        }

        Swal.fire({
            title: 'Confirm Import',
            html: 'This will create <strong>' + previewData.summary.new_products + '</strong> products' +
                  (previewData.summary.updates > 0 ? ', update <strong>' + previewData.summary.updates + '</strong>' : '') +
                  (previewData.summary.new_categories > 0 ? ', and create <strong>' + previewData.summary.new_categories + '</strong> new categories' : '') +
                  '. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#34c38f',
            confirmButtonText: 'Yes, import'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            var btnHtml = $('#commit_btn').html();
            $('#commit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Importing...');

            $.ajax({
                url: base_url + 'client/products/bulk_import_commit',
                type: 'POST',
                success: function(response) {
                    try {
                        var obj = JSON.parse(response);
                        if (obj.status === 'success') {
                            Swal.fire({
                                title: 'Import Complete',
                                html: '<div class="text-start">' +
                                      '<div><i class="uil uil-check-circle text-success me-1"></i> Imported: <strong>' + obj.success_count + '</strong></div>' +
                                      '<div><i class="uil uil-minus-circle text-warning me-1"></i> Skipped: <strong>' + obj.skip_count + '</strong></div>' +
                                      '<div><i class="uil uil-times-circle text-danger me-1"></i> Errors: <strong>' + obj.fail_count + '</strong></div>' +
                                      '<div><i class="uil uil-folder-plus text-info me-1"></i> New categories: <strong>' + obj.created_categories + '</strong></div>' +
                                      '</div>',
                                icon: 'success',
                                confirmButtonColor: '#556ee6'
                            }).then(function() {
                                window.location.href = base_url + 'client/products';
                            });
                        } else {
                            toastr["error"]("Error", obj.message || 'Import failed');
                            $('#commit_btn').prop('disabled', false).html(btnHtml);
                        }
                    } catch (err) {
                        toastr["error"]("Error", "Could not parse server response");
                        $('#commit_btn').prop('disabled', false).html(btnHtml);
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Import failed. Please try again.");
                    $('#commit_btn').prop('disabled', false).html(btnHtml);
                }
            });
        });
    });

    // Download error report (CSV) — generated client-side from the preview
    $('#download_errors_btn').on('click', function() {
        if (!previewData) return;

        var lines = ['"Row","Errors"'];
        previewData.rows.forEach(function(r) {
            if (r.status === 'ERROR') {
                lines.push('"' + r.row_number + '","' + r.errors.join(' | ').replace(/"/g, '""') + '"');
            }
        });

        var blob = new Blob([lines.join('\n')], {type: 'text/csv'});
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'import_errors.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });
});

function renderPreview() {
    var s = previewData.summary;
    $('#pv_total').text(s.total_rows);
    $('#pv_new').text(s.new_products);
    $('#pv_updates').text(s.updates);
    $('#pv_skips').text(s.skips);
    $('#pv_errors').text(s.errors);
    $('#pv_new_cats').text(s.new_categories);
    $('#pv_filename').text(previewData.file_name);

    $('#pv_count_all').text(s.total_rows);
    $('#pv_count_new').text(s.new_products);
    $('#pv_count_update').text(s.updates);
    $('#pv_count_skip').text(s.skips);
    $('#pv_count_error').text(s.errors);

    if (s.errors > 0) {
        $('#download_errors_btn').show();
    } else {
        $('#download_errors_btn').hide();
    }

    if (s.new_categories > 0 && previewData.new_category_names.length > 0) {
        var names = previewData.new_category_names.map(function(c) { return c.name; }).join(', ');
        $('#pv_new_categories_list').text(' ' + names);
        $('#pv_new_categories_card').show();
    } else {
        $('#pv_new_categories_card').hide();
    }

    // Disable commit if nothing actionable
    var hasAction = (s.new_products + s.updates) > 0;
    $('#commit_btn').prop('disabled', !hasAction);

    renderRows();
}

function renderRows() {
    var html = '';
    var shown = 0;
    var max = 200; // safety: only show first 200 rows in the table

    previewData.rows.forEach(function(r) {
        if (currentTab !== 'ALL' && r.status !== currentTab) return;
        if (shown >= max) return;
        shown++;

        var statusBadge = statusBadgeHtml(r.status);
        var modules = [];
        if (r.data.qsr_enabled)     modules.push('<span class="badge bg-info-subtle text-info">QSR</span>');
        if (r.data.kot_enabled)     modules.push('<span class="badge bg-warning-subtle text-warning">KOT</span>');
        if (r.data.premeal_enabled) modules.push('<span class="badge bg-success-subtle text-success">PREMEAL</span>');

        var issues = '';
        if (r.errors && r.errors.length) {
            issues = '<small class="text-danger">' + r.errors.map(esc).join('<br>') + '</small>';
        } else if (r.status === 'SKIP') {
            issues = '<small class="text-muted">Existing product — skipped</small>';
        } else if (r.status === 'UPDATE') {
            issues = '<small class="text-info">Existing product — will be updated</small>';
        } else if (r.category_will_be_created) {
            issues = '<small class="text-success">New category will be created</small>';
        }

        html += '<tr>' +
                  '<td>' + r.row_number + '</td>' +
                  '<td>' + statusBadge + '</td>' +
                  '<td>' + esc(r.data.product_name || '-') + '</td>' +
                  '<td>' + esc(r.data.category_name || '-') + (r.category_will_be_created ? ' <small class="text-success">(new)</small>' : '') + '</td>' +
                  '<td>&#8377;' + parseFloat(r.data.base_price || 0).toFixed(2) + '</td>' +
                  '<td>' + parseFloat(r.data.tax_percentage || 0).toFixed(2) + '%</td>' +
                  '<td>' + (modules.join(' ') || '<small class="text-muted">-</small>') + '</td>' +
                  '<td>' + esc(r.data.meal_type || '-') + '</td>' +
                  '<td>' + issues + '</td>' +
                '</tr>';
    });

    if (shown === 0) {
        html = '<tr><td colspan="9" class="text-center py-3 text-muted">No rows in this tab</td></tr>';
    } else if (shown < previewData.rows.filter(function(r) { return currentTab === 'ALL' || r.status === currentTab; }).length) {
        html += '<tr><td colspan="9" class="text-center text-muted py-2">Showing first ' + max + ' rows. Use tabs to filter.</td></tr>';
    }

    $('#pv_tbody').html(html);
}

function statusBadgeHtml(status) {
    var map = {
        'NEW':    '<span class="badge bg-success-subtle text-success">New</span>',
        'UPDATE': '<span class="badge bg-info-subtle text-info">Update</span>',
        'SKIP':   '<span class="badge bg-warning-subtle text-warning">Skip</span>',
        'ERROR':  '<span class="badge bg-danger-subtle text-danger">Error</span>'
    };
    return map[status] || status;
}

function esc(s) {
    return $('<div>').text(s == null ? '' : String(s)).html();
}
</script>
