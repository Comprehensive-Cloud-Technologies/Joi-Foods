<?php
defined('BASEPATH') or exit('No direct script access allowed');


$config['application_name'] = 'Joi Foods';
$config['application_name_font_end'] = 'Joi Foods';
$config['author']           = 'CCT PVT LTD';
$config['author_link']      = 'https://cctindia.com/';

$config['img_extensions'] = array("jpeg", "jpg", "png", "svg", "webp", "mp4");


$config['app_version'] = '1.1.8';

$config['api_authorization'] = 'd14e5fe6050fda6693e8f658a80879f236e36da3173b4009d56b8100e3782646';


//mail config
$config['email_username'] = $_ENV['EMAIL_USERNAME'] ?? '';
$config['email_password'] = $_ENV['EMAIL_PASSWORD'] ?? '';
$config['email_host'] = $_ENV['EMAIL_HOST'] ?? '';
$config['email_port'] = $_ENV['EMAIL_PORT'] ?? '';
$config['email_from'] = $_ENV['EMAIL_FROM'] ?? '';


//store links apple and android
$config['store_links']['ios'] = 'https://apps.apple.com/us/app/app=name/id1234567890';
$config['store_links']['android'] = 'https://play.google.com/store/apps/details?id=com.company.app';

//guest booking application
// $config['guest_booking_web'] = 'https://joi-guest.zbit.ltd/';
$config['guest_booking_web'] = $_ENV['GUEST_WEB'] ?? 'https://guest.joifood.co/';