<!-- modal view qr with download -->
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4 px-4">
                <div class="mb-3">
                    <?php if (!empty($store->thumbnail)): ?>
                        <img src="<?php echo base_url($store->thumbnail); ?>" alt="<?php echo htmlspecialchars($store->name); ?>" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover; border: 2px solid #e9ecef;">
                    <?php else: ?>
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-soft-primary" style="width: 56px; height: 56px;">
                            <i class="uil uil-store text-primary" style="font-size: 26px;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h5 class="fw-semibold mb-1"><?php echo htmlspecialchars($store->name); ?></h5>
                <span class="badge bg-soft-info text-info mb-3"><?php echo $store->store_type; ?> Store</span>
                <div class="d-flex justify-content-center mb-3">
                    <div id="qrCode" style="padding: 12px; border: 2px solid #e9ecef; border-radius: 12px; display: inline-block; background: #fff;"></div>
                </div>
                <p class="text-muted small mb-3">Scan this QR code to access guest ordering</p>
                <div class="d-grid gap-2">
                    <button id="download_qr_btn" class="btn btn-primary">
                        <i class="uil uil-download-alt me-1"></i> Download QR Code
                    </button>
                    <button id="print_qr_btn" class="btn btn-outline-secondary btn-sm">
                        <i class="uil uil-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo base_url('assets/js/qrcode.min.js') ?>"></script>

<script>
    let store_qr_data = '<?php echo $qr_data; ?>';
    let store_file_name = '<?php echo preg_replace("/[^a-zA-Z0-9]/", "_", $store->name); ?>_qr_code';

    $('#generate_qr_btn').on('click', function() {
        $('#qrCode').empty();
        new QRCode(document.getElementById("qrCode"), {
            text: store_qr_data,
            width: 256,
            height: 256,
            correctLevel: QRCode.CorrectLevel.H
        });
        setTimeout(function() {
            $('#qrModal').modal('show');
        }, 100);
    });

    $('#download_qr_btn').on('click', function() {
        var img = $('#qrCode img');
        if (img.length && img.attr('src')) {
            var link = document.createElement('a');
            link.href = img.attr('src');
            link.download = store_file_name + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });

    $('#print_qr_btn').on('click', function() {
        var img = $('#qrCode img');
        if (img.length && img.attr('src')) {
            var win = window.open('', '_blank');
            win.document.write(
                '<html><head><title>QR Code - <?php echo addslashes($store->name); ?></title>' +
                '<style>body{text-align:center;margin:0;padding:40px 0;font-family:Arial,sans-serif;}' +
                'h2{margin:0 0 8px;}p{color:#666;margin:0 0 24px;}img{width:300px;height:300px;}</style></head>' +
                '<body><h2><?php echo addslashes($store->name); ?></h2>' +
                '<p><?php echo $store->store_type; ?> Store</p>' +
                '<img src="' + img.attr('src') + '">' +
                '<script>window.onload=function(){window.print();window.close();}<\/script></body></html>'
            );
            win.document.close();
        }
    });
</script>