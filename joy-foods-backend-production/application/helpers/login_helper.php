<?php

/**
 * Check if the admin user is logged in.
 * 
 * @return bool Returns true if the admin user is logged in, otherwise false.
 */
function is_loggedin_admin()
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_admin');
    return $user ? true : false;
}

/**
 * Retrieve a specific piece of data from the admin session.
 * 
 * @param string $row The key of the session data to retrieve.
 * @return mixed Returns the value of the specified session data key, or false if not found.
 */
function get_admin_sessiondata($row)
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_admin');
    return $user ? $user->$row : false;
}

/**
 * Retrieve detailed information about the admin user.
 * 
 * @param int|null $uid The user ID to fetch details for. If null, the current logged-in admin's ID is used.
 * @return object|null Returns the admin user details as an associative array, or null if not found.
 */
function get_admin_user_details($uid = null)
{
    if ($uid == null) {
        $uid = get_admin_sessiondata('id');
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id' => $uid
    );
    $user = $CI->common->getdatabytable('admin', $where);
    return !empty($user) ? $user : null;
}

/**
 * Check if a regular user is logged in.
 * 
 * @return bool Returns true if the user is logged in, otherwise false.
 */
function is_loggedin_user()
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_user');
    return $user ? true : false;
}

/**
 * Retrieve a specific piece of data from the user session.
 * 
 * @param string $row The key of the session data to retrieve.
 * @return mixed Returns the value of the specified session data key, or false if not found.
 */
function get_user_sessiondata($row)
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_user');
    return $user ? $user->$row : false;
}

/**
 * Retrieve detailed information about a regular user.
 * 
 * @param int|null $uid The user ID to fetch details for. If null, the current logged-in user's ID is used.
 * @return object|null Returns the user details as an associative array, or null if not found.
 */
function get_user_user_details($uid = null)
{
    if ($uid == null) {
        $uid = get_user_sessiondata('id');
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id ' => $uid
    );
    $user = $CI->common->getdatabytable('vendors', $where);
    return !empty($user) ? $user : null;
}

/**
 * Check if the client user is logged in.
 * 
 * @return bool Returns true if the client user is logged in, otherwise false.
 */
function is_loggedin_client()
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_client');
    return $user ? true : false;
}

/**
 * Retrieve a specific piece of data from the client session.
 * 
 * @param string $row The key of the session data to retrieve.
 * @return mixed Returns the value of the specified session data key, or false if not found.
 */
function get_client_sessiondata($row)
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_client');
    return $user ? $user->$row : false;
}

/**
 * Retrieve detailed information about a client user.
 * 
 * @param int|null $uid The user ID to fetch details for. If null, the current logged-in client's ID is used.
 * @return array|null Returns the client user details as an associative array, or null if not found.
 */
function get_client_user_details($uid = null)
{
    if ($uid == null) {
        $uid = get_client_sessiondata('id');
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id' => $uid
    );
    $user = $CI->common->getdatabytable('client_users', $where);
    return !empty($user) ? $user : null;
}

/**
 * Check if the company user is logged in.
 *
 * @return bool Returns true if the company user is logged in, otherwise false.
 */
function is_loggedin_company()
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_company');
    return $user ? true : false;
}

/**
 * Retrieve a specific piece of data from the company session.
 *
 * @param string $row The key of the session data to retrieve.
 * @return mixed Returns the value of the specified session data key, or false if not found.
 */
function get_company_sessiondata($row)
{
    $CI = &get_instance();
    $user = $CI->session->userdata('user_details_company');
    return $user ? $user->$row : false;
}

/**
 * Retrieve detailed information about a company user.
 *
 * @param int|null $uid The user ID to fetch details for. If null, the current logged-in company user's ID is used.
 * @return object|null Returns the company user details as an object, or null if not found.
 */
function get_company_user_details($uid = null)
{
    if ($uid == null) {
        $uid = get_company_sessiondata('id');
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id' => $uid
    );
    $user = $CI->common->getdatabytable('company_users', $where);
    return !empty($user) ? $user : null;
}

/**
 * Get the company details for the currently logged in company user.
 *
 * @return object|null Returns the company details as an object, or null if not found.
 */
function get_company_details()
{
    $company_id = get_company_sessiondata('company_id');
    if ($company_id === null || $company_id === false || $company_id === '') {
        return null;
    }

    $CI = &get_instance();
    $CI->load->model('Common_model', 'common');
    $where = array(
        'id' => $company_id,
        'deleted_at' => null
    );
    $company = $CI->common->getdatabytable('companies', $where);
    return !empty($company) ? $company : null;
}