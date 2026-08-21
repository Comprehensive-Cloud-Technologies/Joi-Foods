<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Product Importer Library
 *
 * Parses + validates + commits product bulk-import files for the client portal.
 * Supports .xlsx, .xls, .csv. Categories can be auto-created. Existing products
 * can either be skipped or updated based on the duplicate strategy.
 *
 * Images (thumbnail / gallery) are not handled here — admins add them later
 * via the per-product Edit screen.
 *
 * @category  Libraries
 * @package   Joy_Foods
 * @developed_by ZooBit Infotech for Joy Foods.
 */
class ProductImporter
{
    /** Required column headers (lowercased) */
    private $required_columns = ['product_name', 'category_name', 'base_price'];

    /** All known columns in the template (lowercased) */
    private $known_columns = [
        'product_name', 'category_name', 'base_price', 'tax_percentage',
        'description', 'ingredients', 'calories',
        'is_vegetarian', 'qsr_enabled', 'kot_enabled', 'premeal_enabled',
        'meal_type', 'is_featured'
    ];

    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /* ============================================================
     * Public API
     * ============================================================ */

    /**
     * Generate the import template (xlsx) and stream it as a download.
     */
    public function generate_template($download_filename = 'product_import_template.xlsx')
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Products
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        $headers = [
            'product_name', 'category_name', 'base_price', 'tax_percentage',
            'description', 'ingredients', 'calories',
            'is_vegetarian', 'qsr_enabled', 'kot_enabled', 'premeal_enabled',
            'meal_type', 'is_featured'
        ];

        $col_letter = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col_letter . '1', $h);
            $col_letter++;
        }

        // Header styling
        $last_col = chr(ord('A') + count($headers) - 1);
        $header_range = 'A1:' . $last_col . '1';
        $sheet->getStyle($header_range)->getFont()->setBold(true);
        $sheet->getStyle($header_range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('556EE6');
        $sheet->getStyle($header_range)->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($header_range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-width
        foreach (range('A', $last_col) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // Sample rows
        $samples = [
            ['Veg Thali',     'Main Course', 120, 5, 'Traditional Indian meal', 'Rice, dal, sabzi, roti, raita', 450, 'Y', 'Y', 'N', 'Y', 'LUNCH',     'N'],
            ['Masala Chai',   'Beverages',   30,  5, 'Hot Indian spiced tea',   'Tea, milk, sugar, cardamom',    80,  'Y', 'Y', 'Y', 'N', '',          'N']
        ];

        $row = 2;
        foreach ($samples as $s) {
            $c = 'A';
            foreach ($s as $val) {
                $sheet->setCellValue($c . $row, $val);
                $c++;
            }
            $row++;
        }

        // Sheet 2: Instructions
        $inst = $spreadsheet->createSheet();
        $inst->setTitle('Instructions');

        $inst->setCellValue('A1', 'Product Bulk Import — Column Reference');
        $inst->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $inst->mergeCells('A1:C1');

        $inst->setCellValue('A3', 'Column');
        $inst->setCellValue('B3', 'Required');
        $inst->setCellValue('C3', 'Notes');
        $inst->getStyle('A3:C3')->getFont()->setBold(true);

        $rows = [
            ['product_name',    'Yes',         'Unique per client. Existing names are skipped or updated based on import options.'],
            ['category_name',   'Yes',         'Auto-created if missing (when "Auto-create categories" is enabled).'],
            ['base_price',      'Yes',         'Numeric, greater than 0.'],
            ['tax_percentage',  'No',          '0 – 100. Defaults to 0.'],
            ['description',    'No',          'Free text, up to 1000 characters.'],
            ['ingredients',    'No',          'Free text, up to 1000 characters.'],
            ['calories',       'No',          'Integer, 0 or greater.'],
            ['is_vegetarian',  'No',          'Y / N (default Y).'],
            ['qsr_enabled',    'No',          'Y / N (default N).'],
            ['kot_enabled',    'No',          'Y / N (default N).'],
            ['premeal_enabled', 'No',         'Y / N (default N). At least one module must be Y.'],
            ['meal_type',      'Conditional', 'Required when premeal_enabled = Y. One of BREAKFAST, LUNCH, DINNER.'],
            ['is_featured',    'No',          'Y / N (default N).'],
            ['',               '',            ''],
            ['Images:',        '',            'Thumbnail and gallery images are not supported in bulk import. Add them later via Edit.'],
            ['Limits:',        '',            'Max 500 rows per file, max file size 5 MB.']
        ];

        $r = 4;
        foreach ($rows as $row) {
            $inst->setCellValue('A' . $r, $row[0]);
            $inst->setCellValue('B' . $r, $row[1]);
            $inst->setCellValue('C' . $r, $row[2]);
            $r++;
        }

        $inst->getColumnDimension('A')->setWidth(20);
        $inst->getColumnDimension('B')->setWidth(15);
        $inst->getColumnDimension('C')->setWidth(80);

        // Set Products as the active sheet
        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $download_filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Parse a file and validate every row. Does NOT mutate the database.
     *
     * @param string $file_path Absolute path on disk
     * @param int    $client_id
     * @param array  $options [
     *     'duplicate_strategy'     => 'SKIP' | 'UPDATE',
     *     'auto_create_categories' => bool
     * ]
     * @return array {
     *     headers, rows, summary, new_category_names
     * }
     * @throws Exception
     */
    public function parse_and_validate($file_path, $client_id, $options)
    {
        $options = array_merge([
            'duplicate_strategy'     => 'SKIP',
            'auto_create_categories' => true
        ], $options);

        if (!file_exists($file_path)) {
            throw new Exception('Uploaded file not found');
        }

        $spreadsheet = IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        if (count($data) < 2) {
            throw new Exception('File is empty or has no data rows');
        }

        // Parse header row (row 1)
        $header_row = array_shift($data);
        $columns = [];
        foreach ($header_row as $cell_letter => $value) {
            $h = strtolower(trim((string)$value));
            if ($h !== '') {
                $columns[$cell_letter] = $h;
            }
        }

        // Validate required columns present
        $present = array_values($columns);
        foreach ($this->required_columns as $req) {
            if (!in_array($req, $present, true)) {
                throw new Exception('Missing required column: ' . $req);
            }
        }

        // Row count cap
        if (count($data) > 500) {
            throw new Exception('Too many rows. Maximum 500 rows per import. Found ' . count($data));
        }

        // Preload existing categories and products for this client
        $existing_categories = $this->_load_existing_categories($client_id);
        $existing_products = $this->_load_existing_products($client_id);

        $rows = [];
        $new_category_names = [];
        $row_number = 1; // first data row will be row 2 in the source file

        $summary = [
            'total_rows'        => 0,
            'new_products'      => 0,
            'updates'           => 0,
            'skips'             => 0,
            'errors'            => 0,
            'new_categories'    => 0
        ];

        foreach ($data as $excel_row_index => $row_data) {
            $row_number++;
            // Skip fully empty rows
            if ($this->_is_empty_row($row_data)) {
                continue;
            }

            $summary['total_rows']++;

            $normalised = $this->_normalise_row($row_data, $columns);
            $validation = $this->_validate_row(
                $normalised,
                $row_number,
                $existing_categories,
                $existing_products,
                $options
            );

            $rows[] = $validation;

            switch ($validation['status']) {
                case 'NEW':    $summary['new_products']++; break;
                case 'UPDATE': $summary['updates']++; break;
                case 'SKIP':   $summary['skips']++; break;
                case 'ERROR':  $summary['errors']++; break;
            }

            if (!empty($validation['category_will_be_created'])) {
                $key = strtolower(trim($validation['data']['category_name']));
                if (!isset($new_category_names[$key])) {
                    $new_category_names[$key] = [
                        'name'            => trim($validation['data']['category_name']),
                        'qsr_enabled'     => 0,
                        'kot_enabled'     => 0,
                        'premeal_enabled' => 0
                    ];
                }
                // OR the module flags
                $new_category_names[$key]['qsr_enabled']     |= (int)$validation['data']['qsr_enabled'];
                $new_category_names[$key]['kot_enabled']     |= (int)$validation['data']['kot_enabled'];
                $new_category_names[$key]['premeal_enabled'] |= (int)$validation['data']['premeal_enabled'];
            }
        }

        $summary['new_categories'] = count($new_category_names);

        return [
            'headers'            => array_values($columns),
            'rows'               => $rows,
            'summary'            => $summary,
            'new_category_names' => array_values($new_category_names),
            'options'            => $options
        ];
    }

    /**
     * Commit a validated preview into the database in a single transaction.
     *
     * @param array $preview The full output of parse_and_validate()
     * @param int   $client_id
     * @param int   $user_id  Client user performing the import
     * @return array { success_count, skip_count, fail_count, created_categories }
     * @throws Exception
     */
    public function commit($preview, $client_id, $user_id)
    {
        $db = $this->CI->db;

        $created_categories = 0;
        $success = 0;
        $skipped = 0;
        $failed  = 0;

        $db->trans_start();

        // 1. Create any new categories first and resolve their IDs
        $category_lookup = $this->_load_existing_categories($client_id);
        foreach ($preview['new_category_names'] as $cat) {
            $insert = [
                'client_id'       => $client_id,
                'parent_id'       => null,
                'name'            => $cat['name'],
                'description'     => null,
                'icon'            => null,
                'thumbnail'       => null,
                'qsr_enabled'     => $cat['qsr_enabled'] ? 1 : 0,
                'kot_enabled'     => $cat['kot_enabled'] ? 1 : 0,
                'premeal_enabled' => $cat['premeal_enabled'] ? 1 : 0,
                'is_primary'      => 0,
                'display_order'   => 0,
                'is_active'       => 1,
                'created_by'      => $user_id,
                'created_at'      => date('Y-m-d H:i:s')
            ];
            if ($db->insert('categories', $insert)) {
                $new_id = $db->insert_id();
                $category_lookup[strtolower($cat['name'])] = (object)[
                    'id'   => $new_id,
                    'name' => $cat['name']
                ];
                $created_categories++;
            }
        }

        // 2. Process each row
        $now = date('Y-m-d H:i:s');
        foreach ($preview['rows'] as $row) {
            if ($row['status'] === 'ERROR') {
                $failed++;
                continue;
            }
            if ($row['status'] === 'SKIP') {
                $skipped++;
                continue;
            }

            $d = $row['data'];
            $cat_key = strtolower(trim($d['category_name']));
            $category_id = isset($category_lookup[$cat_key]) ? (int)$category_lookup[$cat_key]->id : null;

            if (!$category_id) {
                $failed++;
                continue;
            }

            $payload = [
                'category_id'     => $category_id,
                'name'            => $d['product_name'],
                'description'     => $d['description'],
                'ingredients'     => $d['ingredients'],
                'base_price'      => $d['base_price'],
                'tax_percentage'  => $d['tax_percentage'],
                'calories'        => $d['calories'],
                'is_vegetarian'   => $d['is_vegetarian'],
                'qsr_enabled'     => $d['qsr_enabled'],
                'kot_enabled'     => $d['kot_enabled'],
                'premeal_enabled' => $d['premeal_enabled'],
                'breakfast'       => ($d['premeal_enabled'] && $d['meal_type'] === 'BREAKFAST') ? 1 : 0,
                'lunch'           => ($d['premeal_enabled'] && $d['meal_type'] === 'LUNCH')     ? 1 : 0,
                'dinner'          => ($d['premeal_enabled'] && $d['meal_type'] === 'DINNER')    ? 1 : 0,
                'is_featured'     => $d['is_featured'],
            ];

            if ($row['status'] === 'NEW') {
                $payload['client_id']    = $client_id;
                $payload['thumbnail']    = null;
                $payload['images']       = null;
                $payload['display_order'] = 0;
                $payload['is_active']    = 1;
                $payload['created_by']   = $user_id;
                $payload['created_at']   = $now;

                if ($db->insert('products', $payload)) {
                    $success++;
                } else {
                    $failed++;
                }
            } elseif ($row['status'] === 'UPDATE') {
                $payload['updated_by'] = $user_id;
                $payload['updated_at'] = $now;
                if ($db->update('products', $payload, ['id' => $row['existing_product_id']])) {
                    $success++;
                } else {
                    $failed++;
                }
            }
        }

        $db->trans_complete();

        if ($db->trans_status() === false) {
            throw new Exception('Import transaction failed and was rolled back');
        }

        return [
            'success_count'      => $success,
            'skip_count'         => $skipped,
            'fail_count'         => $failed,
            'created_categories' => $created_categories
        ];
    }

    /* ============================================================
     * Internals
     * ============================================================ */

    private function _load_existing_categories($client_id)
    {
        $rows = $this->CI->db
            ->select('id, name')
            ->from('categories')
            ->where('client_id', $client_id)
            ->where('deleted_at', null)
            ->get()
            ->result();

        $map = [];
        foreach ($rows as $r) {
            $map[strtolower(trim($r->name))] = $r;
        }
        return $map;
    }

    private function _load_existing_products($client_id)
    {
        $rows = $this->CI->db
            ->select('id, name')
            ->from('products')
            ->where('client_id', $client_id)
            ->where('deleted_at', null)
            ->get()
            ->result();

        $map = [];
        foreach ($rows as $r) {
            $map[strtolower(trim($r->name))] = $r;
        }
        return $map;
    }

    private function _is_empty_row($row_data)
    {
        foreach ($row_data as $v) {
            if ($v !== null && trim((string)$v) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Map cells from spreadsheet column letters into known field names.
     * Unknown columns are ignored. Missing columns are returned as null.
     */
    private function _normalise_row($row_data, $columns)
    {
        $r = [];
        foreach ($this->known_columns as $col) {
            $r[$col] = null;
        }
        foreach ($columns as $letter => $col_name) {
            if (in_array($col_name, $this->known_columns, true) && isset($row_data[$letter])) {
                $val = $row_data[$letter];
                $r[$col_name] = is_string($val) ? trim($val) : $val;
            }
        }
        return $r;
    }

    private function _to_bool($v, $default = 0)
    {
        if ($v === null || $v === '') return $default;
        $s = strtoupper(trim((string)$v));
        if (in_array($s, ['Y', 'YES', '1', 'TRUE', 'T'], true))  return 1;
        if (in_array($s, ['N', 'NO',  '0', 'FALSE', 'F'], true)) return 0;
        return null;
    }

    /**
     * Validate a single row.
     * Returns:
     *   row_number, status (NEW|UPDATE|SKIP|ERROR), errors[], data, existing_product_id, category_will_be_created
     */
    private function _validate_row($n, $row_number, $existing_categories, $existing_products, $options)
    {
        $errors = [];

        // product_name
        $name = $n['product_name'];
        if ($name === null || $name === '') {
            $errors[] = 'product_name is required';
        } elseif (strlen($name) > 255) {
            $errors[] = 'product_name exceeds 255 characters';
        }

        // category_name
        $category_name = $n['category_name'];
        if ($category_name === null || $category_name === '') {
            $errors[] = 'category_name is required';
        } elseif (strlen($category_name) > 255) {
            $errors[] = 'category_name exceeds 255 characters';
        }

        // base_price
        $base_price = $n['base_price'];
        if ($base_price === null || $base_price === '') {
            $errors[] = 'base_price is required';
        } elseif (!is_numeric($base_price)) {
            $errors[] = 'base_price must be numeric';
        } elseif ((float)$base_price <= 0) {
            $errors[] = 'base_price must be greater than 0';
        }

        // tax_percentage
        $tax = $n['tax_percentage'];
        if ($tax === null || $tax === '') {
            $tax = 0;
        } elseif (!is_numeric($tax)) {
            $errors[] = 'tax_percentage must be numeric';
        } elseif ((float)$tax < 0 || (float)$tax > 100) {
            $errors[] = 'tax_percentage must be 0–100';
        }

        // calories
        $calories = $n['calories'];
        if ($calories === null || $calories === '') {
            $calories = null;
        } elseif (!is_numeric($calories)) {
            $errors[] = 'calories must be numeric';
        } elseif ((int)$calories < 0) {
            $errors[] = 'calories cannot be negative';
        }

        // boolean fields with defaults
        $is_veg = $this->_to_bool($n['is_vegetarian'], 1);
        if ($is_veg === null) $errors[] = 'is_vegetarian must be Y/N';

        $qsr = $this->_to_bool($n['qsr_enabled'], 0);
        if ($qsr === null) $errors[] = 'qsr_enabled must be Y/N';

        $kot = $this->_to_bool($n['kot_enabled'], 0);
        if ($kot === null) $errors[] = 'kot_enabled must be Y/N';

        $premeal = $this->_to_bool($n['premeal_enabled'], 0);
        if ($premeal === null) $errors[] = 'premeal_enabled must be Y/N';

        $featured = $this->_to_bool($n['is_featured'], 0);
        if ($featured === null) $errors[] = 'is_featured must be Y/N';

        // At least one module must be enabled
        if ($qsr === 0 && $kot === 0 && $premeal === 0) {
            $errors[] = 'At least one module must be enabled (qsr_enabled, kot_enabled or premeal_enabled = Y)';
        }

        // meal_type required when premeal_enabled
        $meal_type = null;
        if ($premeal === 1) {
            $mt = strtoupper((string)$n['meal_type']);
            if ($mt === '') {
                $errors[] = 'meal_type is required when premeal_enabled is Y';
            } elseif (!in_array($mt, ['BREAKFAST', 'LUNCH', 'DINNER'], true)) {
                $errors[] = 'meal_type must be BREAKFAST, LUNCH or DINNER';
            } else {
                $meal_type = $mt;
            }
        }

        // string length caps (cosmetic)
        $description = $n['description'];
        if ($description !== null && strlen($description) > 1000) {
            $errors[] = 'description exceeds 1000 characters';
        }
        $ingredients = $n['ingredients'];
        if ($ingredients !== null && strlen($ingredients) > 1000) {
            $errors[] = 'ingredients exceeds 1000 characters';
        }

        // Category resolution
        $cat_will_create = false;
        if ($category_name !== null && $category_name !== '') {
            $cat_key = strtolower(trim($category_name));
            if (!isset($existing_categories[$cat_key])) {
                if (!empty($options['auto_create_categories'])) {
                    $cat_will_create = true;
                } else {
                    $errors[] = 'category_name "' . $category_name . '" does not exist (enable auto-create)';
                }
            }
        }

        // Duplicate detection
        $existing_product_id = null;
        $status = 'NEW';
        if ($name !== null && $name !== '') {
            $name_key = strtolower(trim($name));
            if (isset($existing_products[$name_key])) {
                $existing_product_id = (int)$existing_products[$name_key]->id;
                $status = ($options['duplicate_strategy'] === 'UPDATE') ? 'UPDATE' : 'SKIP';
            }
        }

        // If there are validation errors, status is ERROR
        if (!empty($errors)) {
            $status = 'ERROR';
        }

        return [
            'row_number'              => $row_number,
            'status'                  => $status,
            'errors'                  => $errors,
            'existing_product_id'     => $existing_product_id,
            'category_will_be_created' => $cat_will_create,
            'data' => [
                'product_name'    => $name,
                'category_name'   => $category_name,
                'base_price'      => is_numeric($base_price) ? round((float)$base_price, 2) : 0,
                'tax_percentage'  => is_numeric($tax) ? round((float)$tax, 2) : 0,
                'description'    => $description,
                'ingredients'    => $ingredients,
                'calories'        => $calories === null ? null : (int)$calories,
                'is_vegetarian'   => (int)($is_veg ?? 1),
                'qsr_enabled'     => (int)($qsr ?? 0),
                'kot_enabled'     => (int)($kot ?? 0),
                'premeal_enabled' => (int)($premeal ?? 0),
                'meal_type'       => $meal_type,
                'is_featured'     => (int)($featured ?? 0)
            ]
        ];
    }
}
