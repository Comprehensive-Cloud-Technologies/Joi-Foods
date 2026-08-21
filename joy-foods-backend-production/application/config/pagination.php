<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['full_tag_open'] = '<ul class="pagination gap-2">';
$config['full_tag_close'] = '</ul>';

$config['first_link'] = false;
$config['last_link'] = false;

$config['prev_link'] = '<i class="fas fa-chevron-left"></i>';
$config['prev_tag_open'] = '<li class="page-item">';
$config['prev_tag_close'] = '</li>';

$config['next_link'] = '<i class="fas fa-chevron-right"></i>';
$config['next_tag_open'] = '<li class="page-item">';
$config['next_tag_close'] = '</li>';

$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="#">';
$config['cur_tag_close'] = '</a></li>';

$config['num_tag_open'] = '<li class="page-item">';
$config['num_tag_close'] = '</li>';

$config['attributes'] = ['class' => 'page-link rounded-circle d-flex align-items-center justify-content-center'];
$config['attributes']['rel'] = false;

// Optional: Add a class to the disabled previous/next links
$config['prev_tag_open_disabled'] = '<li class="page-item disabled">';
$config['next_tag_open_disabled'] = '<li class="page-item disabled">';
