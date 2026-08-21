<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('create_upload_directory')) {
    function create_upload_directory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }
}

if (!function_exists('upload_file')) {
    function upload_file($file_input_name, $upload_path, $allowed_types = 'pdf|doc|docx|jpg|jpeg|png', $max_size = 10240)
    {
        $CI = &get_instance();
        $CI->load->library('upload');

        create_upload_directory($upload_path);

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size'] = $max_size; // in KB
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;

        $CI->upload->initialize($config);

        if ($CI->upload->do_upload($file_input_name)) {
            return $CI->upload->data();
        } else {
            return false;
        }
    }
}

if (!function_exists('delete_uploaded_file')) {
    function delete_uploaded_file($file_path)
    {
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }
}

if (!function_exists('get_file_size_formatted')) {
    function get_file_size_formatted($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }
}

if (!function_exists('get_mime_type_icon')) {
    function get_mime_type_icon($mime_type)
    {
        $icons = array(
            'application/pdf' => 'uil-file-alt',
            'application/msword' => 'uil-file-alt',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'uil-file-alt',
            'image/jpeg' => 'uil-image',
            'image/jpg' => 'uil-image',
            'image/png' => 'uil-image',
            'image/gif' => 'uil-image'
        );

        return isset($icons[$mime_type]) ? $icons[$mime_type] : 'uil-file';
    }
}

if (!function_exists('validate_upload_file')) {
    function validate_upload_file($file, $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'), $max_size = 10485760)
    {
        $errors = array();

        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'File is too large.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'File upload was incomplete.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'No file was uploaded.';
                    break;
                default:
                    $errors[] = 'File upload failed.';
                    break;
            }
            return $errors;
        }

        if ($file['size'] > $max_size) {
            $errors[] = 'File size exceeds maximum allowed size of ' . get_file_size_formatted($max_size) . '.';
        }

        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        }

        return $errors;
    }
}

if (!function_exists('sanitize_filename')) {
    function sanitize_filename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        return trim($filename, '_');
    }
}