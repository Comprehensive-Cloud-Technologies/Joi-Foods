<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Extends CI_Input to normalize request header names.
 *
 * HTTP/2 clients (e.g. the mobile app over HTTPS) send all header names in
 * lowercase, so the original case-sensitive lookups like $headers['Auth'] and
 * $headers['Authorization'] failed, causing "Invalid API key" and 401 /
 * session-expired errors. This normalizes every header name to canonical
 * Title-Case (auth -> Auth, authorization -> Authorization, content-type ->
 * Content-Type) so all existing lookups work regardless of the sent case.
 */
class MY_Input extends CI_Input
{
    public function request_headers($xss_clean = false)
    {
        $headers = parent::request_headers($xss_clean);
        $normalized = array();
        foreach ($headers as $key => $value) {
            $canonical = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($key))));
            $normalized[$canonical] = $value;
            // preserve the original key as well, so nothing that relied on it breaks
            $normalized[$key] = $value;
        }
        return $normalized;
    }
}
