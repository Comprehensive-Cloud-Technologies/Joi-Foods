<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FCM Notification Library for CodeIgniter
 * 
 * This library handles sending push notifications via Firebase Cloud Messaging (FCM)
 * with support for single device, multiple devices, and topics.
 * It also stores individual notifications in the database for in-app notification purposes.
 */
class FirebaseNotificationLib
{

    protected $CI;
    protected $firebaseProjectId;
    protected $jsonKeyFilePath;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Get the CodeIgniter instance
        $this->CI = &get_instance();

        // Load necessary CodeIgniter components
        $this->CI->load->database();
        $this->CI->load->helper('url');


        $this->firebaseProjectId = "joi-foods-prod";
        $this->jsonKeyFilePath = APPPATH . 'config/joi-foods-prod-7b94084f1b9b.json';
    }

    /**
     * Get FCM access token using service account
     * 
     * @return string|null Access token or null on failure
     */
    private function getAccessToken()
    {
        if (!file_exists($this->jsonKeyFilePath)) {
            log_message('error', 'FCM_lib: Service account JSON file not found at ' . $this->jsonKeyFilePath);
            return null;
        }

        // Include Composer autoloader if not already included
        require_once APPPATH . '../vendor/autoload.php';

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            // Create credentials object
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                $scopes,
                $this->jsonKeyFilePath
            );

            $httpHandler = \Google\Auth\HttpHandler\HttpHandlerFactory::build();
            $authToken = $credentials->fetchAuthToken($httpHandler);

            return $authToken['access_token'] ?? null;
        } catch (Exception $e) {
            log_message('error', 'FCM_lib: Failed to get access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification to a single FCM token
     * 
     * @param string $token FCM token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param int $user_id User ID to save notification for (0 to skip saving)
     * @param string $activity Activity type (for app routing)
     * @param array $activity_data Activity data
     * @return array Response status and message
     */
    public function send_to_single($token, $title, $body, $data = [], $user_id = 0, $activity = null, $activity_data = [], $image = null)
    {
        if (empty($token)) {
            return ['status' => false, 'message' => 'FCM token is required'];
        }

        $payload = $this->build_payload($token, $title, $body, $data, false, $image);
        return $this->send_notification($payload);
    }

    /**
     * Send notification to multiple FCM tokens
     * 
     * @param array $tokens Array of FCM tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param array $user_ids Array of user IDs (optional, must match tokens array)
     * @param string $activity Activity type
     * @param array $activity_data Activity data
     * @return array Response with status and results for each token
     */
    public function send_to_multiple($tokens, $title, $body, $data = [], $user_ids = [], $activity = null, $activity_data = [], $image = null)
    {
        if (empty($tokens) || !is_array($tokens)) {
            return ['status' => false, 'message' => 'Array of FCM tokens is required'];
        }

        $results = [];
        $save_notifications = (!empty($user_ids) && count($user_ids) === count($tokens));

        foreach ($tokens as $index => $token) {
            $payload = $this->build_payload($token, $title, $body, $data, false, $image);

            // Send the notification
            $response = $this->send_notification($payload);
            $results[$token] = $response;

            // Save to database if user_id is provided
            if ($save_notifications && isset($user_ids[$index]) && $user_ids[$index] > 0) {
                $this->save_notification($user_ids[$index], $title, $body, $activity, $activity_data);
            }
        }

        return ['status' => true, 'results' => $results];
    }

    /**
     * Send notification to a topic
     * 
     * @param string $topic Topic name to send to
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Response status and message
     */
    public function send_to_topic($topic, $title, $body, $data = [], $image = null)
    {
        if (empty($topic)) {
            return ['status' => false, 'message' => 'Topic name is required'];
        }

        // FCM HTTP v1 API requires plain topic name without /topics/ prefix
        $topic = str_replace('/topics/', '', $topic);
        $topic = ltrim($topic, '/');

        $payload = $this->build_payload($topic, $title, $body, $data, true, $image);

        // Send the notification
        return $this->send_notification($payload);
    }

    /**
     * Build the FCM payload
     * 
     * @param string $target Token or topic
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param bool $is_topic Whether target is a topic
     * @param string|null $image Image URL for rich notification
     * @return array Formatted payload for FCM
     */
    private function build_payload($target, $title, $body, $data = [], $is_topic = false, $image = null)
    {
        $message = [
            ($is_topic ? 'topic' : 'token') => $target,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
            'data' => [],
            'android' => [
                'notification' => [
                    'sound' => 'default'
                ]
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
                    ]
                ]
            ]
        ];

        // Add data payload (FCM data values must be strings)
        if (!empty($data)) {
            $message['data'] = array_map('strval', $data);
        }

        // Image: pass via data, android notification, and apns fcm_options
        if (!empty($image)) {
            $message['data']['image'] = $image;
            $message['android']['notification']['image'] = $image;
            $message['apns']['payload']['aps']['mutable-content'] = 1;
            $message['apns']['fcm_options'] = ['image' => $image];
        }

        // Remove empty data
        if (empty($message['data'])) {
            unset($message['data']);
        }

        return ['message' => $message];
    }

    /**
     * Send notification to FCM
     * 
     * @param array $payload The payload to send
     * @return array Response status and message
     */
    private function send_notification($payload)
    {
        // Get access token
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['status' => false, 'message' => 'Failed to get access token'];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->firebaseProjectId}/messages:send";

        $headers = [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        ];

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for errors
        if ($error = curl_error($ch)) {
            curl_close($ch);
            log_message('error', 'FCM_lib: cURL error: ' . $error);
            return ['status' => false, 'message' => 'cURL error: ' . $error];
        }

        curl_close($ch);

        // Process response
        $response_data = json_decode($response, true);

        if ($http_code == 200) {
            return ['status' => true, 'message' => 'Notification sent successfully', 'data' => $response_data, 'payload' => $payload];
        } else {
            log_message('error', 'FCM_lib: FCM error: ' . $response);
            return ['status' => false, 'message' => 'FCM error', 'data' => $response_data, 'payload' => $payload];
        }
    }
}
