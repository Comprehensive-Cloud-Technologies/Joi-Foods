<?php
defined('BASEPATH') or exit('No direct script access allowed');
// require_once APPPATH . '../vendor/autoload.php';
require_once APPPATH . 'libraries/SizeLimitedStreamHandler.php';

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class Monolog
{
    private $loggers = [];

    public function __construct()
    {
        // Default log file
        $this->initializeLogger('application', 'application.log');
    }

    private function initializeLogger($name, $logFileName)
    {
        // Create a log channel with a specified name
        $logger = new Logger($name);

        // Define log file path
        $logFilePath = APPPATH . 'logs/' . $logFileName;

        // Create a SizeLimitedStreamHandler with a maximum file size of 3MB (3 * 1024 * 1024 bytes)
        $handler = new SizeLimitedStreamHandler($logFilePath, Logger::DEBUG, true, 3 * 1024 * 1024);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $logger->pushHandler($handler);

        // Store the logger instance in the loggers array
        $this->loggers[$name] = $logger;
    }

    private function getLogger($name)
    {
        // Initialize the logger if it doesn't already exist
        if (!isset($this->loggers[$name])) {
            $this->initializeLogger($name, $name . '.log');
        }
        return $this->loggers[$name];
    }

    public function info($message, array $context = [], $logName = 'application')
    {
        $this->getLogger($logName)->info($message, $context);
    }

    public function error($message, array $context = [], $logName = 'application')
    {
        $this->getLogger($logName)->error($message, $context);
    }

    public function warning($message, array $context = [], $logName = 'application')
    {
        $this->getLogger($logName)->warning($message, $context);
    }

    public function debug($message, array $context = [], $logName = 'application')
    {
        $this->getLogger($logName)->debug($message, $context);
    }
}
