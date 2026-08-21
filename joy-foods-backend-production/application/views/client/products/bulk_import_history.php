<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Import History</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/products'); ?>">Products</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/products/bulk_import'); ?>">Bulk Import</a></li>
                                <li class="breadcrumb-item active">History</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="<?php echo base_url('client/products/bulk_import'); ?>" class="btn btn-primary">
                        <i class="uil uil-import me-1"></i> New Import
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="history_table" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Rows</th>
                                <th>Imported</th>
                                <th>Skipped</th>
                                <th>Errors</th>
                                <th>New Cats</th>
                                <th>Strategy</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($imports)): ?>
                                <?php foreach ($imports as $i => $imp): ?>
                                    <?php
                                        $status_badge = [
                                            'PREVIEW'   => '<span class="badge bg-secondary-subtle text-secondary">Preview only</span>',
                                            'COMMITTED' => '<span class="badge bg-success-subtle text-success">Committed</span>',
                                            'FAILED'    => '<span class="badge bg-danger-subtle text-danger">Failed</span>'
                                        ];
                                        $by = trim(($imp->first_name ?? '') . ' ' . ($imp->last_name ?? '')) ?: '-';
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo $imp->created_at; ?></td>
                                        <td><?php echo htmlspecialchars($imp->file_name); ?></td>
                                        <td><?php echo $status_badge[$imp->status] ?? $imp->status; ?></td>
                                        <td><?php echo (int)$imp->total_rows; ?></td>
                                        <td class="text-success"><?php echo (int)$imp->success_count; ?></td>
                                        <td class="text-warning"><?php echo (int)$imp->skip_count; ?></td>
                                        <td class="text-danger"><?php echo (int)$imp->fail_count; ?></td>
                                        <td><?php echo (int)$imp->new_categories_created; ?></td>
                                        <td><small><?php echo $imp->duplicate_strategy; ?><?php echo $imp->auto_create_categories ? ' / Auto-create cats' : ''; ?></small></td>
                                        <td><small><?php echo htmlspecialchars($by); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No imports yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function() {
    if ($('#history_table tbody tr').length > 1) {
        $('#history_table').DataTable({
            responsive: true,
            order: [[1, 'desc']],
            pageLength: 25
        });
    }
});
</script>
