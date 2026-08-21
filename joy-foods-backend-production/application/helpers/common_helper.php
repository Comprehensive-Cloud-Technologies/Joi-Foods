<?php

/**
 * Generates a UUID v4 string
 *
 * @return string UUID in format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
 */
function generate_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

/**
 * Generates a numeric pickup code (OTP-like) for order verification
 *
 * @param int $length Length of the code (default: 4)
 * @return string Numeric pickup code
 */
function generate_pickup_code($length = 4)
{
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    return (string) mt_rand($min, $max);
}

/**
 * Calculates the percentage of a number.
 *
 * @param float $number The original number.
 * @param float $percentage The percentage to be calculated.
 * @return float The calculated percentage.
 */
function calculatePercentage($number, $percentage)
{
    return ($percentage / 100) * $number;
}

/**
 * Generates a random code of specified length using alphanumeric characters.
 *
 * @param int $length The length of the generated random code.
 * @return string The generated random code.
 */
function generateRandomCode($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    // Generate random code by selecting characters from the specified set
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = mt_rand(0, strlen($characters) - 1);
        $code .= $characters[$randomIndex];
    }

    return $code;
}

/**
 * Retrieves a system configuration value by key.
 *
 * @param string $key The configuration key.
 * @param mixed $default The default value to return if the config is not found.
 * @return mixed The configuration value or the default value.
 */
function get_system_config($key, $default = null)
{
    $CI =& get_instance();
    $config = $CI->common->getdatabytable('system_config', array('config_key' => $key));
    return $config ? $config->config_value : $default;
}

/**
 * Masks a name by replacing characters with asterisks.
 *
 * @param string $name The original name.
 * @return string The masked name.
 */
function mask_name($name)
{
    $length = strlen($name);
    if ($length <= 4) return str_repeat('*', $length);

    return substr($name, 0, 2) . str_repeat('*', $length - 4) . substr($name, -2);
}

/**
 * Formats the time remaining into a human-readable string.
 *
 * @param int $seconds The time remaining in seconds.
 * @return string The formatted time remaining.
 */
function format_time_remaining($seconds)
{
    if ($seconds <= 0) return 'Expired';

    $days = floor($seconds / (24 * 3600));
    $hours = floor(($seconds % (24 * 3600)) / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($days > 0) {
        return "{$days}d {$hours}h {$minutes}m";
    } else if ($hours > 0) {
        return "{$hours}h {$minutes}m";
    } else {
        return "{$minutes}m";
    }
}

/**
 * Get product name by ID
 *
 * @param int $id Product ID
 * @return string Product name or empty string
 */
function get_product_name($id)
{
    if (empty($id)) return '';
    $CI =& get_instance();
    $product = $CI->common->getdatabytable('products', array('id' => $id, 'deleted_at' => NULL));
    return $product ? $product->name : '';
}

/**
 * Get category name by ID
 *
 * @param int $id Category ID
 * @return string Category name or empty string
 */
function get_category_name($id)
{
    if (empty($id)) return '';
    $CI =& get_instance();
    $category = $CI->common->getdatabytable('categories', array('id' => $id, 'deleted_at' => NULL));
    return $category ? $category->name : '';
}

/**
 * Calculate base price from GST inclusive price
 * Formula: Base Price = Inclusive Price × 100 / (100 + GST Rate)
 *
 * @param float $inclusive_price The price including GST
 * @param float $gst_rate The GST percentage rate
 * @return float The base price before GST
 */
function calculate_base_price($inclusive_price, $gst_rate)
{
    if ($gst_rate <= 0) {
        return $inclusive_price;
    }
    return ($inclusive_price * 100) / (100 + $gst_rate);
}

/**
 * Calculate GST amount from GST inclusive price
 * Formula: GST = (Inclusive Price × GST Rate) / (100 + GST Rate)
 *
 * @param float $inclusive_price The price including GST
 * @param float $gst_rate The GST percentage rate
 * @return float The GST amount
 */
function calculate_gst_amount($inclusive_price, $gst_rate)
{
    if ($gst_rate <= 0) {
        return 0;
    }
    return ($inclusive_price * $gst_rate) / (100 + $gst_rate);
}


/**
 * Gets the wallet balance for a employee(user) as an array containing total money, debited amount, and available balance.
 *
 * @param int $uid The ID of the employee.
 * @return array An array containing total money, debited amount, and available balance.
 */

function getWalletMoneyArray($uid)
{
    $CI = &get_instance();
    $CI->load->model('Employees_model', 'employee');

    // Get total money and debited amount from the wallet
    $money = $CI->employee->GetWalletMoney($uid);
    $debit = $CI->employee->GetWalletMoneydebit($uid);
    // Calculate and return the available balance
    return array(
        'money'     => $money->amount ? $money->amount : 0,
        'debit'     => $debit->amount ? $debit->amount : 0,
        'available' => $money->amount - $debit->amount
    );
}