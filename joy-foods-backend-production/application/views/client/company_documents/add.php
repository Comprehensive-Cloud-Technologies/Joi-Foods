<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Upload Document</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies/view/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies/documents/' . $company->id); ?>">Documents</a></li>
                            <li class="breadcrumb-item active">Upload</li>
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
                        <h4 class="card-title mb-4">Document Information</h4>

                        <form id="upload_document" action="<?php echo base_url('client/companies/store_document') ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="company_id" value="<?php echo $company->id; ?>">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="label" class="form-label">Document Label <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="label" name="label" placeholder="e.g. Contract Agreement, GST Certificate, PAN Card">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="document" class="form-label">Select File <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="document" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                                        <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG, XLS, XLSX. Max size: 10MB</small>
                                    </div>
                                    <div id="file-preview" class="mb-3" style="display: none;">
                                        <div class="border rounded p-3 d-flex align-items-center">
                                            <i class="uil uil-file font-size-24 text-primary me-3" id="file-icon"></i>
                                            <div>
                                                <h6 class="mb-0" id="file-name"></h6>
                                                <small class="text-muted" id="file-size"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo base_url('client/companies/documents/' . $company->id); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Upload Document</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <!-- Company Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Company</h4>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                    <i class="uil uil-building"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo $company->name; ?></h6>
                                <small class="text-muted"><?php echo $company->company_code; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Help</h4>
                        <div class="text-muted">
                            <p><i class="uil uil-check-circle text-success me-1"></i> Label helps identify the document (e.g. "GST Certificate").</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Supported formats: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX.</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Maximum file size is 10MB.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
