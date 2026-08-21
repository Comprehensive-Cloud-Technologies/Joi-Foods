<script>
$(document).ready(function() {

    // File input preview
    $('#document').on('change', function() {
        var file = this.files[0];
        if (file) {
            var size = file.size;
            var sizeStr = '';
            if (size >= 1048576) {
                sizeStr = (size / 1048576).toFixed(2) + ' MB';
            } else if (size >= 1024) {
                sizeStr = (size / 1024).toFixed(2) + ' KB';
            } else {
                sizeStr = size + ' bytes';
            }

            var ext = file.name.split('.').pop().toLowerCase();
            var iconClass = 'uil uil-file';
            if (ext === 'pdf') iconClass = 'uil uil-file-alt';
            else if (['jpg', 'jpeg', 'png'].includes(ext)) iconClass = 'uil uil-image';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'uil uil-file-alt';
            else if (['xls', 'xlsx'].includes(ext)) iconClass = 'uil uil-file-alt';

            $('#file-icon').attr('class', iconClass + ' font-size-24 text-primary me-3');
            $('#file-name').text(file.name);
            $('#file-size').text(sizeStr);
            $('#file-preview').show();
        } else {
            $('#file-preview').hide();
        }
    });

    // Form validation for upload document
    $('#upload_document').formValidation({
        framework: 'bootstrap',
        icon: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            label: {
                validators: {
                    notEmpty: {
                        message: 'Document label is required'
                    },
                    stringLength: {
                        max: 255,
                        message: 'Label must be less than 255 characters'
                    }
                }
            },
            document: {
                validators: {
                    notEmpty: {
                        message: 'Please select a file to upload'
                    },
                    file: {
                        extension: 'pdf,doc,docx,jpg,jpeg,png,xls,xlsx',
                        maxSize: 10485760,
                        message: 'File must be PDF, DOC, DOCX, JPG, JPEG, PNG, XLS or XLSX and less than 10MB'
                    }
                }
            }
        }
    }).on('success.form.fv', function(e) {
        e.preventDefault();
        var $form = $(e.target);
        $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

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
                        window.location.href = document.referrer;
                    }, 2000);
                } else {
                    toastr["error"]("Error", obj.message);
                    $('#submit_button').prop('disabled', false).html('Upload Document');
                }
            },
            error: function() {
                toastr["error"]("Error", "Something went wrong. Please try again.");
                $('#submit_button').prop('disabled', false).html('Upload Document');
            }
        });
    });
});
</script>
