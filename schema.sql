-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 05:47 PM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 7.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `joy_foods`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_deletion_requests`
--

CREATE TABLE `account_deletion_requests` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) DEFAULT NULL COMMENT 'Resolved from company_code; NULL if company not found',
  `company_id` bigint(20) DEFAULT NULL COMMENT 'Resolved from company_code',
  `employee_id` bigint(20) DEFAULT NULL COMMENT 'Resolved from email within company; NULL if no match',
  `company_code` varchar(20) NOT NULL COMMENT 'As submitted by the user',
  `email` varchar(255) NOT NULL COMMENT 'As submitted by the user',
  `status` enum('PENDING','PROCESSED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `ip_address` varchar(45) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL COMMENT 'Internal note set when processing',
  `processed_by` bigint(20) DEFAULT NULL COMMENT 'client user id who processed the request',
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Public account deletion requests';

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `full_name` varchar(30) NOT NULL,
  `username` varchar(20) NOT NULL,
  `phone_number` varchar(13) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_type` enum('CLIENT_USER','COMPANY_USER','EMPLOYEE','OUTLET_STAFF') NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `description` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `action_type` enum('PRODUCT','CATEGORY','URL','NONE') NOT NULL DEFAULT 'NONE',
  `action_payload` varchar(500) DEFAULT NULL COMMENT 'Product ID, Category ID, or external URL based on action_type',
  `display_order` int(11) DEFAULT 0 COMMENT 'Sort order for display',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `module` enum('QSR','KOT','PREMEAL') NOT NULL DEFAULT 'QSR',
  `scheduled_date` date DEFAULT NULL COMMENT 'For PREMEAL only',
  `meal_type` enum('BREAKFAST','LUNCH','DINNER','SNACKS') DEFAULT NULL COMMENT 'For PREMEAL only',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `parent_id` bigint(20) DEFAULT NULL COMMENT 'Parent category ID for hierarchical structure',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL COMMENT 'Icon URL or icon class name',
  `thumbnail` varchar(500) DEFAULT NULL COMMENT 'Category image',
  `qsr_enabled` tinyint(1) DEFAULT 0 COMMENT 'Available in QSR module',
  `kot_enabled` tinyint(1) DEFAULT 0 COMMENT 'Available in KOT module',
  `premeal_enabled` tinyint(1) DEFAULT 0 COMMENT 'Available in PREMEAL module',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Primary/Featured category',
  `display_order` int(11) DEFAULT 0 COMMENT 'Sort order for display',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) NOT NULL,
  `client_code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `pincode` varchar(10) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `fssai_license` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `razorpay_key_id` varchar(64) NOT NULL,
  `razorpay_key_secret` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `client_users`
--

CREATE TABLE `client_users` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `company_code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `primary_email` varchar(255) DEFAULT NULL,
  `secondary_email` varchar(255) DEFAULT NULL,
  `primary_phone` varchar(20) DEFAULT NULL,
  `secondary_phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `pincode` varchar(10) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `cin_number` varchar(50) DEFAULT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `billing_cycle` enum('WEEKLY','BIWEEKLY','MONTHLY','QUARTERLY') DEFAULT 'MONTHLY',
  `payment_terms_days` int(11) DEFAULT 30,
  `max_employees` int(11) DEFAULT NULL,
  `allow_guest_booking` tinyint(1) DEFAULT 0,
  `guest_booking_requires_approval` tinyint(1) DEFAULT 1,
  `qsr_enabled` tinyint(1) DEFAULT 1,
  `premeal_enabled` tinyint(1) DEFAULT 1,
  `delivery_enabled` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `company_departments`
--

CREATE TABLE `company_departments` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `company_documents`
--

CREATE TABLE `company_documents` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `original_filename` varchar(500) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_extension` varchar(10) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `company_policies`
--

CREATE TABLE `company_policies` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `policy_id` bigint(20) NOT NULL,
  `custom_daily_meal_limit` int(11) DEFAULT NULL,
  `custom_monthly_budget_limit` decimal(10,2) DEFAULT NULL,
  `apply_to_qsr` tinyint(1) DEFAULT 1,
  `apply_to_premeal` tinyint(1) DEFAULT 1,
  `apply_to_delivery` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `priority` int(11) DEFAULT 0,
  `effective_from` date NOT NULL,
  `effective_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `company_users`
--

CREATE TABLE `company_users` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `can_manual_credit` tinyint(1) NOT NULL DEFAULT 0,
  `can_manual_debit` tinyint(1) NOT NULL DEFAULT 0,
  `can_razorpay_recharge` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `company_id` bigint(20) DEFAULT NULL COMMENT 'NULL = all companies under client',
  `code` varchar(50) NOT NULL COMMENT 'Unique coupon code',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('PERCENTAGE','FIXED') NOT NULL DEFAULT 'PERCENTAGE',
  `discount_value` decimal(10,2) NOT NULL COMMENT 'Percentage or fixed amount',
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum discount cap for percentage',
  `min_order_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Minimum order amount required',
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Total usage limit (NULL = unlimited)',
  `usage_count` int(11) DEFAULT 0 COMMENT 'Current usage count',
  `per_user_limit` int(11) DEFAULT 1 COMMENT 'Usage limit per user',
  `applies_to_qsr` tinyint(1) DEFAULT 1,
  `applies_to_kot` tinyint(1) DEFAULT 1,
  `applies_to_premeal` tinyint(1) DEFAULT 1,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime DEFAULT NULL COMMENT 'NULL = no expiry',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` bigint(20) NOT NULL,
  `coupon_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_locations`
--

CREATE TABLE `delivery_locations` (
  `id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `location_code` varchar(20) NOT NULL COMMENT 'Unique random code for QR codes',
  `name` varchar(255) NOT NULL COMMENT 'Location name (e.g., Floor 3 - Bay A)',
  `short_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `building` varchar(100) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `department_id` bigint(20) DEFAULT NULL,
  `employee_code` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `display_name` varchar(150) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `profile_picture_url` varchar(500) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('MALE','FEMALE','OTHER','PREFER_NOT_TO_SAY') DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `employment_type` enum('FULL_TIME','PART_TIME','CONTRACT','INTERN') DEFAULT 'FULL_TIME',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `device_token` varchar(500) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` timestamp NULL DEFAULT NULL,
  `qsr_access` tinyint(1) DEFAULT 1,
  `premeal_access` tinyint(1) DEFAULT 1,
  `delivery_access` tinyint(1) DEFAULT 0,
  `kot_permission` tinyint(1) DEFAULT 0,
  `rfid_card_number` varchar(100) DEFAULT NULL,
  `rfid_card_issued_at` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_registered` tinyint(1) DEFAULT 0,
  `fcm_token` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `employee_policies`
--

CREATE TABLE `employee_policies` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `policy_id` bigint(20) NOT NULL,
  `custom_daily_meal_limit` int(11) DEFAULT NULL,
  `custom_monthly_budget_limit` decimal(10,2) DEFAULT NULL,
  `apply_to_qsr` tinyint(1) DEFAULT 1,
  `apply_to_premeal` tinyint(1) DEFAULT 1,
  `apply_to_delivery` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 0,
  `effective_from` date NOT NULL,
  `effective_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `guest_carts`
--

CREATE TABLE `guest_carts` (
  `id` bigint(20) NOT NULL,
  `session_id` varchar(64) NOT NULL COMMENT 'UUID generated on first cart add',
  `store_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'ORDER_CONFIRMED, ORDER_READY, ORDER_COMPLETED, ORDER_CANCELLED, ORDER_REJECTED, ORDER_PLACED',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `module` enum('QSR','KOT','PREMEAL') DEFAULT NULL,
  `data` text DEFAULT NULL COMMENT 'JSON payload for deep linking',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `order_number` varchar(50) NOT NULL COMMENT 'Auto-generated: QSR-20260102-0001',
  `employee_id` bigint(20) DEFAULT NULL,
  `company_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `module` enum('QSR','KOT','PREMEAL') NOT NULL,
  `pending_order_id` bigint(20) DEFAULT NULL COMMENT 'Source pending_orders row; unique so one payment session creates at most one order',
  `is_guest_order` tinyint(1) NOT NULL DEFAULT 0,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `pickup_time` datetime DEFAULT NULL COMMENT 'Scheduled pickup time for QSR',
  `is_scheduled` tinyint(1) DEFAULT 0 COMMENT 'Is this a scheduled order',
  `delivery_location_id` bigint(20) DEFAULT NULL COMMENT 'Delivery location for KOT',
  `department_id` bigint(20) DEFAULT NULL COMMENT 'Department to bill for KOT orders',
  `scheduled_date` date DEFAULT NULL COMMENT 'Scheduled date for PREMEAL',
  `meal_type` enum('BREAKFAST','LUNCH','DINNER','SNACKS') DEFAULT NULL COMMENT 'Meal type for PREMEAL',
  `is_primary_order` tinyint(1) DEFAULT 1 COMMENT 'True if this is the primary/first order in a multi-day booking',
  `parent_order_id` bigint(20) DEFAULT NULL COMMENT 'Reference to primary order for sub-orders (PREMEAL multi-day)',
  `status` enum('PENDING','CONFIRMED','PREPARING','READY','OUT_FOR_DELIVERY','DELIVERED','COMPLETED','CANCELLED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `pickup_code` varchar(6) DEFAULT NULL COMMENT 'OTP-like code for order pickup verification',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total taxable value (base price)',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total tax amount',
  `amount_before_discount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total before discount (subtotal + tax)',
  `coupon_id` bigint(20) DEFAULT NULL COMMENT 'Applied coupon reference',
  `coupon_code` varchar(50) DEFAULT NULL COMMENT 'Coupon code used',
  `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Discount applied',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Final order total',
  `policy_id` bigint(20) DEFAULT NULL COMMENT 'Applied policy for KOT/PREMEAL',
  `company_contribution` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount paid by company',
  `employee_contribution` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount to be paid by employee',
  `wallet_deducted` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount deducted from wallet',
  `payment_status` enum('PENDING','PAID','PARTIALLY_PAID','REFUNDED','FAILED') DEFAULT 'PENDING',
  `payment_method` enum('WALLET','ONLINE','POLICY','MIXED') DEFAULT NULL COMMENT 'How payment was made',
  `paid_at` timestamp NULL DEFAULT NULL,
  `confirmed_by` bigint(20) DEFAULT NULL COMMENT 'Store staff who confirmed',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `preparing_started_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `prep_time` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Preparation time in minutes',
  `delivered_by` bigint(20) DEFAULT NULL COMMENT 'Store staff who delivered (KOT)',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) DEFAULT NULL COMMENT 'Employee who cancelled',
  `cancellation_reason` text DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) DEFAULT NULL COMMENT 'Store staff who rejected',
  `rejection_reason` text DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_status` enum('NONE','PENDING','PROCESSED','FAILED') DEFAULT 'NONE',
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refund_transaction_id` bigint(20) DEFAULT NULL COMMENT 'Reference to transaction table',
  `customer_note` text DEFAULT NULL COMMENT 'Special instructions from customer',
  `staff_note` text DEFAULT NULL COMMENT 'Internal notes by store staff',
  `total_items` int(11) DEFAULT 0 COMMENT 'Total quantity of items',
  `unique_items` int(11) DEFAULT 0 COMMENT 'Number of unique products',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reflect_at` datetime DEFAULT NULL COMMENT 'When to show in active queue for scheduled orders',
  `is_review_required` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = review required, 0 = not required',
  `is_reviewed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = review submitted, 0 = pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `product_name` varchar(255) NOT NULL COMMENT 'Product name at time of order',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Price per unit (GST inclusive)',
  `tax_percentage` decimal(5,2) DEFAULT 0.00,
  `base_price` decimal(10,2) NOT NULL COMMENT 'Unit price without tax',
  `tax_amount` decimal(10,2) NOT NULL COMMENT 'Tax for this item (qty * unit_tax)',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Base price * quantity',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'unit_price * quantity',
  `note` text DEFAULT NULL COMMENT 'Special instructions for this item',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order_payments`
--

CREATE TABLE `order_payments` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `payment_type` enum('WALLET_DEBIT','COMPANY_SUBSIDY','REFUND_CREDIT','ONLINE_PAYMENT') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` bigint(20) DEFAULT NULL COMMENT 'Reference to transaction table',
  `razorpay_payment_id` varchar(100) DEFAULT NULL COMMENT 'Razorpay payment ID for online payments',
  `razorpay_order_id` varchar(100) DEFAULT NULL COMMENT 'Razorpay order ID',
  `status` enum('PENDING','SUCCESS','FAILED') DEFAULT 'PENDING',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order_reviews`
--

CREATE TABLE `order_reviews` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `module` enum('QSR','KOT','PREMEAL') NOT NULL,
  `food_review` text DEFAULT NULL,
  `service_review` text DEFAULT NULL,
  `extra_comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `from_status` enum('PENDING','CONFIRMED','PREPARING','READY','OUT_FOR_DELIVERY','DELIVERED','COMPLETED','CANCELLED','REJECTED') DEFAULT NULL,
  `to_status` enum('PENDING','CONFIRMED','PREPARING','READY','OUT_FOR_DELIVERY','DELIVERED','COMPLETED','CANCELLED','REJECTED') NOT NULL,
  `changed_by_type` enum('EMPLOYEE','STORE_STAFF','SYSTEM') NOT NULL,
  `changed_by_id` bigint(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `purpose` enum('PASSWORD_RESET','EMAIL_VERIFY','PHONE_VERIFY') NOT NULL DEFAULT 'PASSWORD_RESET',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pending_orders`
--

CREATE TABLE `pending_orders` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `session_id` varchar(64) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `store_id` bigint(20) NOT NULL,
  `module` enum('QSR','KOT','PREMEAL') NOT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL COMMENT 'Razorpay order ID for online payment',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Online payment amount',
  `wallet_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Wallet amount to deduct',
  `coupon_id` bigint(20) DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pickup_time` datetime DEFAULT NULL COMMENT 'Scheduled pickup time for QSR',
  `items_json` longtext NOT NULL COMMENT 'JSON encoded cart items snapshot',
  `status` enum('PENDING','COMPLETED','EXPIRED','FAILED') NOT NULL DEFAULT 'PENDING',
  `order_id` bigint(20) DEFAULT NULL COMMENT 'Created order ID after completion',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL COMMENT 'Order session expiry time',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `policy_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `policy_type` enum('FREE','PARTIAL','PAID') NOT NULL,
  `company_contribution_type` enum('PERCENTAGE','FIXED_AMOUNT') DEFAULT 'PERCENTAGE',
  `company_contribution_value` decimal(10,2) DEFAULT 0.00,
  `employee_contribution_type` enum('PERCENTAGE','FIXED_AMOUNT') DEFAULT 'PERCENTAGE',
  `employee_contribution_value` decimal(10,2) DEFAULT 0.00,
  `max_meal_value` decimal(10,2) DEFAULT NULL,
  `daily_meal_limit` int(11) DEFAULT 1,
  `weekly_meal_limit` int(11) DEFAULT NULL,
  `monthly_meal_limit` int(11) DEFAULT NULL,
  `monthly_budget_limit` decimal(10,2) DEFAULT NULL,
  `applies_to_qsr` tinyint(1) DEFAULT 0,
  `applies_to_premeal` tinyint(1) DEFAULT 1,
  `applies_to_delivery` tinyint(1) DEFAULT 0,
  `breakfast_enabled` tinyint(1) DEFAULT 0,
  `lunch_enabled` tinyint(1) DEFAULT 1,
  `dinner_enabled` tinyint(1) DEFAULT 0,
  `snacks_enabled` tinyint(1) DEFAULT 0,
  `meal_timings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meal_timings`)),
  `advance_booking_days` int(11) DEFAULT 7,
  `booking_cutoff_hours` int(11) DEFAULT 2,
  `cancellation_cutoff_hours` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `premeal_schedules`
--

CREATE TABLE `premeal_schedules` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `day_of_week` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL COMMENT 'Day of week for this schedule',
  `display_order` int(11) DEFAULT 0 COMMENT 'Sort order for display in menu',
  `menu_json` longtext DEFAULT NULL COMMENT 'JSON data for menu details',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ingredients` text DEFAULT NULL COMMENT 'List of ingredients',
  `thumbnail` varchar(500) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT 0.00,
  `qsr_enabled` tinyint(1) DEFAULT 0 COMMENT 'Available in QSR module',
  `kot_enabled` tinyint(1) DEFAULT 0 COMMENT 'Available in KOT module',
  `premeal_enabled` tinyint(1) DEFAULT 0 COMMENT 'Available in PREMEAL module',
  `breakfast` tinyint(1) DEFAULT 0 COMMENT 'Available for breakfast',
  `lunch` tinyint(1) DEFAULT 0 COMMENT 'Available for lunch',
  `dinner` tinyint(1) DEFAULT 0 COMMENT 'Available for dinner',
  `is_vegetarian` tinyint(1) DEFAULT 1,
  `is_vegan` tinyint(1) DEFAULT 0,
  `calories` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1 COMMENT 'Currently available for ordering',
  `stock_quantity` int(11) DEFAULT NULL COMMENT 'Stock quantity (null = unlimited)',
  `low_stock_alert` int(11) DEFAULT NULL COMMENT 'Alert threshold for low stock',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Featured product',
  `is_popular` tinyint(1) DEFAULT 0 COMMENT 'Popular/Best seller',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `product_imports`
--

CREATE TABLE `product_imports` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_path` varchar(500) DEFAULT NULL,
  `total_rows` int(11) NOT NULL DEFAULT 0,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `skip_count` int(11) NOT NULL DEFAULT 0,
  `fail_count` int(11) NOT NULL DEFAULT 0,
  `new_categories_created` int(11) NOT NULL DEFAULT 0,
  `duplicate_strategy` enum('SKIP','UPDATE') NOT NULL DEFAULT 'SKIP',
  `auto_create_categories` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('PREVIEW','COMMITTED','FAILED') NOT NULL DEFAULT 'PREVIEW',
  `error_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`error_summary`)),
  `imported_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) DEFAULT NULL COMMENT 'NULL for guest orders',
  `is_guest_order` tinyint(1) NOT NULL DEFAULT 0,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `refund_method` enum('WALLET','RAZORPAY') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_refund_id` varchar(100) DEFAULT NULL,
  `wallet_transaction_id` bigint(20) DEFAULT NULL COMMENT 'FK to transaction.transaction_id',
  `status` enum('PROCESSED','FAILED','PENDING') NOT NULL DEFAULT 'PROCESSED',
  `refunded_by` bigint(20) DEFAULT NULL COMMENT 'store_staff id who initiated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `company_id` bigint(20) DEFAULT NULL,
  `store_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `transaction_type` enum('IN','OUT','SET') NOT NULL COMMENT 'IN=added, OUT=removed, SET=absolute set',
  `source` enum('ORDER_PLACED','ORDER_REJECTED','ORDER_CANCELLED','MANUAL_UPDATE','INITIAL_STOCK') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Always positive; direction given by transaction_type',
  `stock_before` int(11) DEFAULT NULL COMMENT 'NULL if stock was unlimited before',
  `stock_after` int(11) DEFAULT NULL COMMENT 'NULL if stock is unlimited after',
  `reference_type` enum('ORDER','MANUAL') DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL COMMENT 'order_id when source is order-related',
  `order_number` varchar(50) DEFAULT NULL,
  `performed_by_type` enum('STORE_STAFF','EMPLOYEE','GUEST','SYSTEM') DEFAULT NULL,
  `performed_by_id` bigint(20) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `company_id` bigint(20) DEFAULT NULL,
  `store_code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `primary_email` varchar(255) DEFAULT NULL,
  `secondary_email` varchar(255) DEFAULT NULL,
  `primary_phone` varchar(20) DEFAULT NULL,
  `secondary_phone` varchar(20) DEFAULT NULL,
  `contact_person_name` varchar(100) DEFAULT NULL,
  `contact_person_phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `pincode` varchar(10) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `fssai_license` varchar(50) DEFAULT NULL,
  `trade_license_number` varchar(50) DEFAULT NULL,
  `store_type` enum('QSR','KOT','PREMEAL') NOT NULL DEFAULT 'QSR',
  `breakfast_time` time DEFAULT NULL COMMENT 'Breakfast serving time for PREMEAL',
  `lunch_time` time DEFAULT NULL COMMENT 'Lunch serving time for PREMEAL',
  `dinner_time` time DEFAULT NULL COMMENT 'Dinner serving time for PREMEAL',
  `is_active` tinyint(1) DEFAULT 1,
  `is_operational` tinyint(1) DEFAULT 1,
  `opening_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `store_documents`
--

CREATE TABLE `store_documents` (
  `id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `original_filename` varchar(500) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_extension` varchar(10) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `store_products`
--

CREATE TABLE `store_products` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Store-specific price',
  `available_stock` int(11) DEFAULT NULL COMMENT 'Available stock quantity (NULL = unlimited)',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `store_staff`
--

CREATE TABLE `store_staff` (
  `id` bigint(20) NOT NULL,
  `store_id` bigint(20) NOT NULL,
  `staff_code` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('BILLER','MANAGER') DEFAULT 'BILLER',
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `device_token` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `support_inquiries`
--

CREATE TABLE `support_inquiries` (
  `id` bigint(20) NOT NULL,
  `client_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `topic` varchar(100) NOT NULL COMMENT 'Topic/Category of inquiry',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Support inquiries submitted by employees';

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `transaction_uuid` varchar(60) NOT NULL,
  `user_id` int(10) NOT NULL,
  `order_id` bigint(20) DEFAULT NULL COMMENT 'Reference to order for order-related transactions',
  `transaction_type` int(10) NOT NULL,
  `amount` double(10,2) NOT NULL,
  `transaction_label` varchar(120) NOT NULL,
  `source` varchar(30) DEFAULT NULL COMMENT 'RAZORPAY, COMPANY_CREDIT, ORDER_REFUND, SYSTEM',
  `transaction_date` date NOT NULL,
  `transaction_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_order_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_order_summary` (
`id` bigint(20)
,`order_number` varchar(50)
,`module` enum('QSR','KOT','PREMEAL')
,`status` enum('PENDING','CONFIRMED','PREPARING','READY','OUT_FOR_DELIVERY','DELIVERED','COMPLETED','CANCELLED','REJECTED')
,`payment_status` enum('PENDING','PAID','PARTIALLY_PAID','REFUNDED','FAILED')
,`total_amount` decimal(10,2)
,`company_contribution` decimal(10,2)
,`employee_contribution` decimal(10,2)
,`wallet_deducted` decimal(10,2)
,`scheduled_date` date
,`meal_type` enum('BREAKFAST','LUNCH','DINNER','SNACKS')
,`is_primary_order` tinyint(1)
,`parent_order_id` bigint(20)
,`parent_order_number` varchar(50)
,`created_at` timestamp
,`employee_code` varchar(50)
,`employee_name` varchar(201)
,`company_name` varchar(255)
,`store_name` varchar(255)
,`delivery_location` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_credits`
--

CREATE TABLE `wallet_credits` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `company_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL COMMENT 'Reference to transaction table',
  `credited_by` int(11) NOT NULL COMMENT 'company_users.id who added the credit',
  `credited_by_name` varchar(200) NOT NULL,
  `razorpay_order_id` varchar(64) DEFAULT NULL,
  `razorpay_payment_id` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_recharges`
--

CREATE TABLE `wallet_recharges` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `razorpay_order_id` varchar(100) NOT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('PENDING','COMPLETED','EXPIRED','FAILED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure for view `v_order_summary`
--
DROP TABLE IF EXISTS `v_order_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_order_summary`  AS SELECT `o`.`id` AS `id`, `o`.`order_number` AS `order_number`, `o`.`module` AS `module`, `o`.`status` AS `status`, `o`.`payment_status` AS `payment_status`, `o`.`total_amount` AS `total_amount`, `o`.`company_contribution` AS `company_contribution`, `o`.`employee_contribution` AS `employee_contribution`, `o`.`wallet_deducted` AS `wallet_deducted`, `o`.`scheduled_date` AS `scheduled_date`, `o`.`meal_type` AS `meal_type`, `o`.`is_primary_order` AS `is_primary_order`, `o`.`parent_order_id` AS `parent_order_id`, `po`.`order_number` AS `parent_order_number`, `o`.`created_at` AS `created_at`, `e`.`employee_code` AS `employee_code`, concat(`e`.`first_name`,' ',coalesce(`e`.`last_name`,'')) AS `employee_name`, `c`.`name` AS `company_name`, `s`.`name` AS `store_name`, `dl`.`name` AS `delivery_location` FROM (((((`orders` `o` join `employees` `e` on(`e`.`id` = `o`.`employee_id`)) join `companies` `c` on(`c`.`id` = `o`.`company_id`)) join `stores` `s` on(`s`.`id` = `o`.`store_id`)) left join `delivery_locations` `dl` on(`dl`.`id` = `o`.`delivery_location_id`)) left join `orders` `po` on(`po`.`id` = `o`.`parent_order_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_deletion_requests`
--
ALTER TABLE `account_deletion_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_adr_client` (`client_id`),
  ADD KEY `idx_adr_company` (`company_id`),
  ADD KEY `idx_adr_employee` (`employee_id`),
  ADD KEY `idx_adr_email` (`email`),
  ADD KEY `idx_adr_created` (`created_at`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_type`,`user_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_banner_client` (`client_id`),
  ADD KEY `idx_banner_company` (`company_id`),
  ADD KEY `idx_banner_active` (`is_active`),
  ADD KEY `idx_banner_action` (`action_type`),
  ADD KEY `idx_banner_order` (`display_order`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_employee` (`employee_id`),
  ADD KEY `idx_cart_store` (`store_id`),
  ADD KEY `idx_cart_product` (`product_id`),
  ADD KEY `idx_cart_employee_store_module` (`employee_id`,`store_id`,`module`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_client` (`client_id`),
  ADD KEY `idx_category_parent` (`parent_id`),
  ADD KEY `idx_category_active` (`is_active`),
  ADD KEY `idx_category_primary` (`is_primary`),
  ADD KEY `idx_category_qsr` (`qsr_enabled`),
  ADD KEY `idx_category_kot` (`kot_enabled`),
  ADD KEY `idx_category_premeal` (`premeal_enabled`),
  ADD KEY `idx_category_order` (`display_order`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_code` (`client_code`),
  ADD KEY `idx_client_code` (`client_code`),
  ADD KEY `idx_client_active` (`is_active`),
  ADD KEY `idx_client_email` (`email`);

--
-- Indexes for table `client_users`
--
ALTER TABLE `client_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_client_user_email` (`client_id`,`email`),
  ADD KEY `idx_client_user_email` (`email`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_code` (`company_code`),
  ADD KEY `idx_company_client` (`client_id`),
  ADD KEY `idx_company_code` (`company_code`),
  ADD KEY `idx_company_active` (`is_active`);

--
-- Indexes for table `company_departments`
--
ALTER TABLE `company_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_dept_code` (`company_id`,`code`),
  ADD KEY `idx_dept_company` (`company_id`);

--
-- Indexes for table `company_documents`
--
ALTER TABLE `company_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_documents_company` (`company_id`),
  ADD KEY `idx_company_documents_client` (`client_id`);

--
-- Indexes for table `company_policies`
--
ALTER TABLE `company_policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_policy` (`company_id`,`policy_id`),
  ADD KEY `idx_cp_company` (`company_id`),
  ADD KEY `idx_cp_policy` (`policy_id`);

--
-- Indexes for table `company_users`
--
ALTER TABLE `company_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_user_email` (`company_id`,`email`),
  ADD KEY `idx_company_user_email` (`email`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_coupon_code` (`client_id`,`code`),
  ADD KEY `idx_coupon_client` (`client_id`),
  ADD KEY `idx_coupon_company` (`company_id`),
  ADD KEY `idx_coupon_code` (`code`),
  ADD KEY `idx_coupon_active` (`is_active`),
  ADD KEY `idx_coupon_validity` (`valid_from`,`valid_until`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupon_usage_coupon` (`coupon_id`),
  ADD KEY `idx_coupon_usage_employee` (`employee_id`),
  ADD KEY `idx_coupon_usage_order` (`order_id`),
  ADD KEY `idx_coupon_usage_employee_coupon` (`employee_id`,`coupon_id`);

--
-- Indexes for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_location_code` (`location_code`),
  ADD KEY `idx_delivery_loc_store` (`store_id`),
  ADD KEY `idx_delivery_loc_active` (`is_active`),
  ADD KEY `idx_delivery_loc_order` (`display_order`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_employee_code` (`company_id`,`employee_code`),
  ADD UNIQUE KEY `uk_company_employee_email` (`company_id`,`email`),
  ADD KEY `idx_employee_company` (`company_id`),
  ADD KEY `idx_employee_department` (`department_id`),
  ADD KEY `idx_employee_email` (`email`),
  ADD KEY `idx_employee_phone` (`phone`),
  ADD KEY `idx_employee_rfid` (`rfid_card_number`);

--
-- Indexes for table `employee_policies`
--
ALTER TABLE `employee_policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_employee_policy` (`employee_id`,`policy_id`),
  ADD KEY `idx_ep_employee` (`employee_id`),
  ADD KEY `idx_ep_policy` (`policy_id`);

--
-- Indexes for table `guest_carts`
--
ALTER TABLE `guest_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_store` (`session_id`,`store_id`),
  ADD KEY `idx_session_product` (`session_id`,`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_read` (`employee_id`,`is_read`),
  ADD KEY `idx_employee_created` (`employee_id`,`created_at`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order_number` (`order_number`),
  ADD UNIQUE KEY `uq_orders_pending_order_id` (`pending_order_id`),
  ADD KEY `idx_order_employee` (`employee_id`),
  ADD KEY `idx_order_company` (`company_id`),
  ADD KEY `idx_order_store` (`store_id`),
  ADD KEY `idx_order_module` (`module`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_order_payment_status` (`payment_status`),
  ADD KEY `idx_order_scheduled_date` (`scheduled_date`),
  ADD KEY `idx_order_meal_type` (`meal_type`),
  ADD KEY `idx_order_created` (`created_at`),
  ADD KEY `idx_order_delivery_loc` (`delivery_location_id`),
  ADD KEY `idx_order_employee_store_module` (`employee_id`,`store_id`,`module`),
  ADD KEY `idx_order_parent` (`parent_order_id`),
  ADD KEY `idx_order_primary` (`is_primary_order`),
  ADD KEY `idx_order_department` (`department_id`),
  ADD KEY `idx_order_coupon` (`coupon_id`),
  ADD KEY `orders_policy_fk` (`policy_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_item_order` (`order_id`),
  ADD KEY `idx_order_item_product` (`product_id`);

--
-- Indexes for table `order_payments`
--
ALTER TABLE `order_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_payment_order` (`order_id`),
  ADD KEY `idx_order_payment_type` (`payment_type`),
  ADD KEY `idx_order_payment_transaction` (`transaction_id`),
  ADD KEY `idx_order_payment_razorpay` (`razorpay_payment_id`);

--
-- Indexes for table `order_reviews`
--
ALTER TABLE `order_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_review` (`order_id`),
  ADD KEY `idx_order_reviews_employee` (`employee_id`),
  ADD KEY `idx_order_reviews_store` (`store_id`),
  ADD KEY `idx_order_reviews_module` (`module`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_history_order` (`order_id`),
  ADD KEY `idx_status_history_status` (`to_status`),
  ADD KEY `idx_status_history_created` (`created_at`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_email` (`email`),
  ADD KEY `idx_otp_company` (`company_id`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_otp_reset_token` (`reset_token`),
  ADD KEY `idx_otp_expires` (`expires_at`);

--
-- Indexes for table `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pending_order_employee` (`employee_id`),
  ADD KEY `idx_pending_order_razorpay` (`razorpay_order_id`),
  ADD KEY `idx_pending_order_status` (`status`),
  ADD KEY `idx_pending_order_expires` (`expires_at`),
  ADD KEY `pending_orders_store_fk` (`store_id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_client_policy_code` (`client_id`,`policy_code`),
  ADD KEY `idx_policy_client` (`client_id`),
  ADD KEY `idx_policy_type` (`policy_type`),
  ADD KEY `idx_policy_active` (`is_active`);

--
-- Indexes for table `premeal_schedules`
--
ALTER TABLE `premeal_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_premeal_schedule` (`store_id`,`product_id`,`day_of_week`,`deleted_at`),
  ADD KEY `idx_premeal_schedule_client` (`client_id`),
  ADD KEY `idx_premeal_schedule_store` (`store_id`),
  ADD KEY `idx_premeal_schedule_product` (`product_id`),
  ADD KEY `idx_premeal_schedule_day` (`day_of_week`),
  ADD KEY `idx_premeal_schedule_active` (`is_active`),
  ADD KEY `idx_premeal_schedule_store_day` (`store_id`,`day_of_week`,`is_active`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_client` (`client_id`),
  ADD KEY `idx_product_category` (`category_id`),
  ADD KEY `idx_product_active` (`is_active`),
  ADD KEY `idx_product_available` (`is_available`),
  ADD KEY `idx_product_qsr` (`qsr_enabled`),
  ADD KEY `idx_product_kot` (`kot_enabled`),
  ADD KEY `idx_product_premeal` (`premeal_enabled`),
  ADD KEY `idx_product_breakfast` (`breakfast`),
  ADD KEY `idx_product_lunch` (`lunch`),
  ADD KEY `idx_product_dinner` (`dinner`),
  ADD KEY `idx_product_featured` (`is_featured`),
  ADD KEY `idx_product_popular` (`is_popular`),
  ADD KEY `idx_product_vegetarian` (`is_vegetarian`);

--
-- Indexes for table `product_imports`
--
ALTER TABLE `product_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pi_client` (`client_id`),
  ADD KEY `idx_pi_status` (`status`),
  ADD KEY `idx_pi_created` (`created_at`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refund_order` (`order_id`),
  ADD KEY `idx_refund_store` (`store_id`),
  ADD KEY `idx_refund_company` (`company_id`),
  ADD KEY `idx_refund_employee` (`employee_id`),
  ADD KEY `idx_refund_method` (`refund_method`),
  ADD KEY `idx_refund_status` (`status`),
  ADD KEY `idx_refund_date` (`created_at`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_st_client` (`client_id`),
  ADD KEY `idx_st_company` (`company_id`),
  ADD KEY `idx_st_store` (`store_id`),
  ADD KEY `idx_st_product` (`product_id`),
  ADD KEY `idx_st_created` (`created_at`),
  ADD KEY `idx_st_reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `store_code` (`store_code`),
  ADD KEY `idx_store_client` (`client_id`),
  ADD KEY `idx_store_company` (`company_id`),
  ADD KEY `idx_store_code` (`store_code`),
  ADD KEY `idx_store_active` (`is_active`),
  ADD KEY `idx_store_operational` (`is_operational`),
  ADD KEY `idx_store_location` (`latitude`,`longitude`);

--
-- Indexes for table `store_documents`
--
ALTER TABLE `store_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_documents_store` (`store_id`),
  ADD KEY `idx_store_documents_client` (`client_id`);

--
-- Indexes for table `store_products`
--
ALTER TABLE `store_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_store_product` (`store_id`,`product_id`,`deleted_at`),
  ADD KEY `idx_store_products_client` (`client_id`),
  ADD KEY `idx_store_products_store` (`store_id`),
  ADD KEY `idx_store_products_product` (`product_id`),
  ADD KEY `idx_store_products_active` (`is_active`);

--
-- Indexes for table `store_staff`
--
ALTER TABLE `store_staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_store_staff_code` (`store_id`,`staff_code`),
  ADD UNIQUE KEY `uk_store_staff_email` (`store_id`,`email`),
  ADD KEY `idx_staff_store` (`store_id`),
  ADD KEY `idx_staff_email` (`email`),
  ADD KEY `idx_staff_phone` (`phone`);

--
-- Indexes for table `support_inquiries`
--
ALTER TABLE `support_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_topic` (`topic`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_transaction_order` (`order_id`);

--
-- Indexes for table `wallet_credits`
--
ALTER TABLE `wallet_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `wallet_recharges`
--
ALTER TABLE `wallet_recharges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_razorpay_order_id` (`razorpay_order_id`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_deletion_requests`
--
ALTER TABLE `account_deletion_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_users`
--
ALTER TABLE `client_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_departments`
--
ALTER TABLE `company_departments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_documents`
--
ALTER TABLE `company_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_policies`
--
ALTER TABLE `company_policies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_users`
--
ALTER TABLE `company_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_policies`
--
ALTER TABLE `employee_policies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_carts`
--
ALTER TABLE `guest_carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_payments`
--
ALTER TABLE `order_payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_reviews`
--
ALTER TABLE `order_reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pending_orders`
--
ALTER TABLE `pending_orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `premeal_schedules`
--
ALTER TABLE `premeal_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_imports`
--
ALTER TABLE `product_imports`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_documents`
--
ALTER TABLE `store_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_products`
--
ALTER TABLE `store_products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_staff`
--
ALTER TABLE `store_staff`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_inquiries`
--
ALTER TABLE `support_inquiries`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_credits`
--
ALTER TABLE `wallet_credits`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_recharges`
--
ALTER TABLE `wallet_recharges`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `banners`
--
ALTER TABLE `banners`
  ADD CONSTRAINT `banners_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `banners_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `client_users`
--
ALTER TABLE `client_users`
  ADD CONSTRAINT `client_users_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_departments`
--
ALTER TABLE `company_departments`
  ADD CONSTRAINT `company_departments_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_documents`
--
ALTER TABLE `company_documents`
  ADD CONSTRAINT `fk_company_documents_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_company_documents_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_policies`
--
ALTER TABLE `company_policies`
  ADD CONSTRAINT `company_policies_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_policies_ibfk_2` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_users`
--
ALTER TABLE `company_users`
  ADD CONSTRAINT `company_users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_company_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_coupon_fk` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usage_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_locations`
--
ALTER TABLE `delivery_locations`
  ADD CONSTRAINT `delivery_locations_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `company_departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_policies`
--
ALTER TABLE `employee_policies`
  ADD CONSTRAINT `employee_policies_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_policies_ibfk_2` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_company_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `orders_coupon_fk` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_delivery_loc_fk` FOREIGN KEY (`delivery_location_id`) REFERENCES `delivery_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_department_fk` FOREIGN KEY (`department_id`) REFERENCES `company_departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `orders_parent_fk` FOREIGN KEY (`parent_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_policy_fk` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_payments`
--
ALTER TABLE `order_payments`
  ADD CONSTRAINT `order_payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_reviews`
--
ALTER TABLE `order_reviews`
  ADD CONSTRAINT `fk_order_reviews_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_reviews_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD CONSTRAINT `otp_verifications_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD CONSTRAINT `pending_orders_employee_fk` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pending_orders_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `policies`
--
ALTER TABLE `policies`
  ADD CONSTRAINT `policies_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `premeal_schedules`
--
ALTER TABLE `premeal_schedules`
  ADD CONSTRAINT `premeal_schedules_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `premeal_schedules_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `premeal_schedules_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stores_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `store_products`
--
ALTER TABLE `store_products`
  ADD CONSTRAINT `store_products_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_products_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_products_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_staff`
--
ALTER TABLE `store_staff`
  ADD CONSTRAINT `store_staff_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_inquiries`
--
ALTER TABLE `support_inquiries`
  ADD CONSTRAINT `fk_support_inquiries_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_support_inquiries_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_support_inquiries_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_recharges`
--
ALTER TABLE `wallet_recharges`
  ADD CONSTRAINT `wallet_recharges_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
