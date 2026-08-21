<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Documents - <?php echo $company->name; ?></h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies/view/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                                <li class="breadcrumb-item active">Documents</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="<?php echo base_url('client/companies/add_document/' . $company->id) ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Upload Document
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Documents</p>
                                <h3 class="stat-value"><?php echo count($documents); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All uploaded documents</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">PDF Documents</p>
                                <?php $pdf_count = count(array_filter($documents, function($d) { return strtolower($d->file_extension) == '.pdf'; })); ?>
                                <h3 class="stat-value"><?php echo $pdf_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">PDF files</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Images</p>
                                <?php $img_count = count(array_filter($documents, function($d) { return in_array(strtolower($d->file_extension), ['.jpg', '.jpeg', '.png']); })); ?>
                                <h3 class="stat-value"><?php echo $img_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-image"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Image files</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Documents List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Label</th>
                                        <th>File</th>
                                        <th>Size</th>
                                        <th>Uploaded At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($documents)) { $i = 1; foreach ($documents as $doc) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                            <i class="<?php echo get_mime_type_icon($doc->mime_type); ?> font-size-16"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $doc->label; ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo $doc->original_filename; ?>
                                                <br>
                                                <span class="badge bg-secondary-subtle text-secondary"><?php echo strtoupper(ltrim($doc->file_extension, '.')); ?></span>
                                            </td>
                                            <td><?php echo get_file_size_formatted($doc->file_size); ?></td>
                                            <td><?php echo date('d M Y, h:i A', strtotime($doc->created_at)); ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('client/companies/download_document/' . $doc->id); ?>" class="btn btn-soft-primary action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Download">
                                                        <i class="uil uil-download-alt"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteDocument(<?php echo $doc->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="uil uil-file-alt font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No documents uploaded</p>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
