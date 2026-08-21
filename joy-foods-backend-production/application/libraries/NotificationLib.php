<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * NotificationLib
 *
 * Central library for creating in-app notifications and triggering
 * push notifications (FCM). Provides template methods for each
 * order status change event.
 *
 * Usage:
 *   $this->load->library('NotificationLib');
 *   $this->notificationlib->orderConfirmed($employee_id, $order);
 *
 * @category  Libraries
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-02-24
 */
class NotificationLib
{
    private $CI;
    private $logger;
    private $fcm;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Notifications_model', 'notifications_model');
        $this->CI->load->library('monolog');
        $this->CI->load->library('FirebaseNotificationLib');
        $this->logger = new Monolog();
        $this->fcm = new FirebaseNotificationLib();
    }

    /**
     * Create an in-app notification and trigger FCM push
     *
     * @param int    $employee_id Employee ID
     * @param string $type        Notification type (ORDER_CONFIRMED, ORDER_READY, etc.)
     * @param string $title       Notification title
     * @param string $message     Notification message
     * @param array  $order_data  Order context [order_id, order_number, module, image]
     * @return int Notification ID
     */
    public function send($employee_id, $type, $title, $message, $order_data = [])
    {
        $data = [
            'employee_id' => $employee_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'order_id' => $order_data['order_id'] ?? null,
            'order_number' => $order_data['order_number'] ?? null,
            'module' => $order_data['module'] ?? null,
            'data' => !empty($order_data) ? json_encode($order_data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $notification_id = $this->CI->notifications_model->create($data);

        $this->logger->info('In-app notification created', [
            'notification_id' => $notification_id,
            'employee_id' => $employee_id,
            'type' => $type,
            'order_id' => $order_data['order_id'] ?? null,
            'order_number' => $order_data['order_number'] ?? null
        ], 'notifications');

        // Trigger FCM push notification
        $image = $order_data['image'] ?? null;
        $this->send_fcm($employee_id, $title, $message, $order_data, $image);

        return $notification_id;
    }

    /**
     * Send FCM push notification via FirebaseNotificationLib
     *
     * @param int         $employee_id Employee ID
     * @param string      $title       Push title
     * @param string      $message     Push body
     * @param array       $data        Data payload for deep linking
     * @param string|null $image       Image URL for rich notification
     * @return bool
     */
    public function send_fcm($employee_id, $title, $message, $data = [], $image = null)
    {
        // Get FCM token from employees table
        $employee = $this->CI->db
            ->select('fcm_token')
            ->where('id', $employee_id)
            ->get('employees')
            ->row();

        if (empty($employee) || empty($employee->fcm_token)) {
            $this->logger->info('FCM: No token found, skipping push', [
                'employee_id' => $employee_id
            ], 'notifications');
            return false;
        }

        $fcm_token = $employee->fcm_token;

        // Add click action for Flutter routing
        $fcm_data = array_merge($data, [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ]);

        $response = $this->fcm->send_to_single($fcm_token, $title, $message, $fcm_data, 0, null, [], $image);

        if (!empty($response['status']) && $response['status'] === true) {
            $this->logger->info('FCM: Push sent successfully', [
                'employee_id' => $employee_id,
                'title' => $title
            ], 'notifications');
            return true;
        }

        $this->logger->error('FCM: Push failed', [
            'employee_id' => $employee_id,
            'title' => $title,
            'error' => $response['message'] ?? 'Unknown error'
        ], 'notifications');
        return false;
    }

    // ------------------------------------------------------------------
    // Template methods - called from controllers after status changes
    // ------------------------------------------------------------------

    /**
     * Order confirmed / approved by store
     *
     * @param int    $employee_id Employee ID
     * @param object $order       Order object (needs: id, order_number, module, prep_time)
     * @return int Notification ID
     */
    public function orderConfirmed($employee_id, $order)
    {
        $title = 'Order Approved';
        $message = "Your order #{$order->order_number} has been approved";
        if (!empty($order->prep_time)) {
            $message .= " and will be ready in {$order->prep_time} minutes";
        }

        return $this->send($employee_id, 'ORDER_CONFIRMED', $title, $message, [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module
        ]);
    }

    /**
     * Order is ready for pickup
     *
     * @param int    $employee_id Employee ID
     * @param object $order       Order object
     * @return int Notification ID
     */
    public function orderReady($employee_id, $order)
    {
        $title = 'Order Ready';
        $message = "Your order #{$order->order_number} is ready for pickup";

        return $this->send($employee_id, 'ORDER_READY', $title, $message, [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module
        ]);
    }

    /**
     * Order completed / delivered
     *
     * @param int    $employee_id Employee ID
     * @param object $order       Order object
     * @return int Notification ID
     */
    public function orderCompleted($employee_id, $order)
    {
        $title = 'Order Completed';
        $message = "Your order #{$order->order_number} has been delivered. Enjoy your meal!";

        return $this->send($employee_id, 'ORDER_COMPLETED', $title, $message, [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module
        ]);
    }

    /**
     * Order cancelled by employee
     *
     * @param int    $employee_id Employee ID
     * @param object $order       Order object
     * @return int Notification ID
     */
    public function orderCancelled($employee_id, $order)
    {
        $title = 'Order Cancelled';
        $message = "Your order #{$order->order_number} has been cancelled";

        return $this->send($employee_id, 'ORDER_CANCELLED', $title, $message, [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module
        ]);
    }

    /**
     * Order rejected by store
     *
     * @param int         $employee_id Employee ID
     * @param object      $order       Order object
     * @param string|null $reason      Rejection reason
     * @return int Notification ID
     */
    public function orderRejected($employee_id, $order, $reason = null)
    {
        $title = 'Order Rejected';
        $message = "Your order #{$order->order_number} was rejected";
        if (!empty($reason)) {
            $message .= ": {$reason}";
        }

        return $this->send($employee_id, 'ORDER_REJECTED', $title, $message, [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module
        ]);
    }
}
