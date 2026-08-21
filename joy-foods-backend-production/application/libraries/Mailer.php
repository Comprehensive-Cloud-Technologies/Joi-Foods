<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Mailer Library
 *
 * Handles email sending operations for the Joy Foods application.
 *
 * @category  Libraries
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @version   1.0.0
 */
class Mailer
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('email');
    }

    /**
     * Configure email settings
     */
    private function configure()
    {
        $config = array(
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://' . config_item('email_host'),
            'smtp_port' => config_item('email_port'),
            'smtp_user' => config_item('email_username'),
            'smtp_pass' => config_item('email_password'),
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n",
            'wordwrap'  => TRUE
        );

        $this->CI->email->initialize($config);
    }

    /**
     * Send OTP email for password reset
     *
     * @param string $to_email Recipient email address
     * @param string $otp_code The OTP code
     * @param string $employee_name Employee's name
     * @param string $company_name Company name
     * @return bool True on success, false on failure
     */
    public function send_otp($to_email, $otp_code, $employee_name = '', $company_name = '')
    {
        $this->configure();

        $this->CI->email->from(config_item('email_from'), config_item('application_name'));
        $this->CI->email->to($to_email);
        $this->CI->email->subject('Password Reset OTP - ' . config_item('application_name'));

        $message = $this->get_otp_template($otp_code, $employee_name, $company_name);
        $this->CI->email->message($message);

        if ($this->CI->email->send()) {
            return true;
        } else {
            log_message('error', 'Email sending failed: ' . $this->CI->email->print_debugger(['headers']));
            return false;
        }
    }

    /**
     * Get OTP email template
     *
     * @param string $otp_code The OTP code
     * @param string $employee_name Employee's name
     * @param string $company_name Company name
     * @return string HTML email template
     */
    private function get_otp_template($otp_code, $employee_name, $company_name)
    {
        $app_name = config_item('application_name');
        $greeting = !empty($employee_name) ? "Hello {$employee_name}," : "Hello,";
        $company_text = !empty($company_name) ? " for {$company_name}" : "";

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset OTP</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <table role='presentation' style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td align='center' style='padding: 40px 0;'>
                        <table role='presentation' style='width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='padding: 30px 40px; background-color: #4CAF50; border-radius: 8px 8px 0 0;'>
                                    <h1 style='margin: 0; color: #ffffff; font-size: 24px;'>{$app_name}</h1>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style='padding: 40px;'>
                                    <p style='margin: 0 0 20px; color: #333333; font-size: 16px;'>{$greeting}</p>

                                    <p style='margin: 0 0 20px; color: #333333; font-size: 16px;'>
                                        We received a request to reset your password{$company_text}. Use the OTP below to proceed:
                                    </p>

                                    <!-- OTP Box -->
                                    <div style='text-align: center; margin: 30px 0;'>
                                        <div style='display: inline-block; padding: 20px 40px; background-color: #f8f9fa; border: 2px dashed #4CAF50; border-radius: 8px;'>
                                            <span style='font-size: 32px; font-weight: bold; color: #4CAF50; letter-spacing: 8px;'>{$otp_code}</span>
                                        </div>
                                    </div>

                                    <p style='margin: 0 0 10px; color: #666666; font-size: 14px;'>
                                        <strong>This OTP will expire in 10 minutes.</strong>
                                    </p>

                                    <p style='margin: 0 0 20px; color: #666666; font-size: 14px;'>
                                        If you did not request a password reset, please ignore this email or contact support if you have concerns.
                                    </p>

                                    <hr style='border: none; border-top: 1px solid #eeeeee; margin: 30px 0;'>

                                    <p style='margin: 0; color: #999999; font-size: 12px;'>
                                        This is an automated message. Please do not reply to this email.
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='padding: 20px 40px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;'>
                                    <p style='margin: 0; color: #999999; font-size: 12px;'>
                                        &copy; " . date('Y') . " {$app_name}. All rights reserved.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    /**
     * Send support inquiry notification to client
     *
     * @param string $to_email Client email address
     * @param array  $data     Inquiry details (topic, subject, message, employee_name, employee_email, employee_phone, company_name, inquiry_id)
     * @return bool True on success, false on failure
     */
    public function send_support_inquiry($to_email, $data = [])
    {
        $this->configure();

        $this->CI->email->from(config_item('email_from'), config_item('application_name'));
        $this->CI->email->to($to_email);
        $this->CI->email->subject('New Support Inquiry #' . ($data['inquiry_id'] ?? '') . ' - ' . ($data['subject'] ?? 'Support Request'));

        $message = $this->get_support_inquiry_template($data);
        $this->CI->email->message($message);

        if ($this->CI->email->send()) {
            return true;
        } else {
            log_message('error', 'Support inquiry email failed: ' . $this->CI->email->print_debugger(['headers']));
            return false;
        }
    }

    /**
     * Get support inquiry email template
     *
     * @param array $data Inquiry details
     * @return string HTML email template
     */
    private function get_support_inquiry_template($data)
    {
        $app_name = config_item('application_name');
        $inquiry_id = $data['inquiry_id'] ?? '-';
        $topic = htmlspecialchars($data['topic'] ?? '-');
        $subject = htmlspecialchars($data['subject'] ?? '-');
        $msg = nl2br(htmlspecialchars($data['message'] ?? '-'));
        $employee_name = htmlspecialchars($data['employee_name'] ?? '-');
        $employee_email = htmlspecialchars($data['employee_email'] ?? '-');
        $employee_phone = htmlspecialchars($data['employee_phone'] ?? '-');
        $company_name = htmlspecialchars($data['company_name'] ?? '-');
        $date = date('d M Y, h:i A');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>New Support Inquiry</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <table role='presentation' style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td align='center' style='padding: 40px 0;'>
                        <table role='presentation' style='width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='padding: 30px 40px; background-color: #556ee6; border-radius: 8px 8px 0 0;'>
                                    <h1 style='margin: 0; color: #ffffff; font-size: 24px;'>{$app_name}</h1>
                                    <p style='margin: 5px 0 0; color: rgba(255,255,255,0.8); font-size: 14px;'>New Support Inquiry Received</p>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style='padding: 40px;'>
                                    <p style='margin: 0 0 20px; color: #333333; font-size: 16px;'>
                                        A new support inquiry has been submitted. Please find the details below:
                                    </p>

                                    <!-- Inquiry Details -->
                                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 25px;'>
                                        <tr>
                                            <td style='padding: 10px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057; width: 140px;'>Inquiry ID</td>
                                            <td style='padding: 10px 15px; border: 1px solid #e9ecef; color: #333;'>#{$inquiry_id}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057;'>Topic</td>
                                            <td style='padding: 10px 15px; border: 1px solid #e9ecef; color: #333;'>{$topic}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057;'>Subject</td>
                                            <td style='padding: 10px 15px; border: 1px solid #e9ecef; color: #333;'>{$subject}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057;'>Submitted</td>
                                            <td style='padding: 10px 15px; border: 1px solid #e9ecef; color: #333;'>{$date}</td>
                                        </tr>
                                    </table>

                                    <!-- Message -->
                                    <p style='margin: 0 0 10px; font-weight: bold; color: #495057; font-size: 14px;'>Message:</p>
                                    <div style='padding: 15px; background-color: #f8f9fa; border-left: 4px solid #556ee6; border-radius: 4px; margin-bottom: 25px;'>
                                        <p style='margin: 0; color: #333333; font-size: 14px; line-height: 1.6;'>{$msg}</p>
                                    </div>

                                    <!-- Employee Details -->
                                    <p style='margin: 0 0 10px; font-weight: bold; color: #495057; font-size: 14px;'>Submitted By:</p>
                                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                                        <tr>
                                            <td style='padding: 8px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057; width: 140px;'>Name</td>
                                            <td style='padding: 8px 15px; border: 1px solid #e9ecef; color: #333;'>{$employee_name}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057;'>Email</td>
                                            <td style='padding: 8px 15px; border: 1px solid #e9ecef; color: #333;'>{$employee_email}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057;'>Phone</td>
                                            <td style='padding: 8px 15px; border: 1px solid #e9ecef; color: #333;'>{$employee_phone}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px 15px; background-color: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; color: #495057;'>Company</td>
                                            <td style='padding: 8px 15px; border: 1px solid #e9ecef; color: #333;'>{$company_name}</td>
                                        </tr>
                                    </table>

                                    <hr style='border: none; border-top: 1px solid #eeeeee; margin: 20px 0;'>

                                    <p style='margin: 0; color: #999999; font-size: 12px;'>
                                        This is an automated notification. Please respond to the employee directly.
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='padding: 20px 40px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;'>
                                    <p style='margin: 0; color: #999999; font-size: 12px;'>
                                        &copy; " . date('Y') . " {$app_name}. All rights reserved.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    /**
     * Send password reset success email
     *
     * @param string $to_email Recipient email address
     * @param string $employee_name Employee's name
     * @return bool True on success, false on failure
     */
    public function send_password_reset_success($to_email, $employee_name = '')
    {
        $this->configure();

        $this->CI->email->from(config_item('email_from'), config_item('application_name'));
        $this->CI->email->to($to_email);
        $this->CI->email->subject('Password Reset Successful - ' . config_item('application_name'));

        $app_name = config_item('application_name');
        $greeting = !empty($employee_name) ? "Hello {$employee_name}," : "Hello,";

        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px;'>
                <h2 style='color: #4CAF50;'>{$app_name}</h2>
                <p>{$greeting}</p>
                <p>Your password has been successfully reset.</p>
                <p>If you did not make this change, please contact our support team immediately.</p>
                <hr style='border: none; border-top: 1px solid #eeeeee; margin: 20px 0;'>
                <p style='color: #999999; font-size: 12px;'>This is an automated message. Please do not reply.</p>
            </div>
        </body>
        </html>
        ";

        $this->CI->email->message($message);

        return $this->CI->email->send();
    }
}
