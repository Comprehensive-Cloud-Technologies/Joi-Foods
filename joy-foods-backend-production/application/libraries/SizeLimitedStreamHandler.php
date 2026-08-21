<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SizeLimitedStreamHandler extends StreamHandler
{
    private $maxFileSize;

    public function __construct($stream, $level = Logger::DEBUG, $bubble = true, $maxFileSize = 10000)
    {
        // Initialize the StreamHandler with the given parameters
        parent::__construct($stream, $level, $bubble);
        $this->maxFileSize = $maxFileSize;

        // Check file size and rotate if needed
        $this->checkFileSize();
    }

    protected function write(array $record): void
    {
        // Check file size before writing a new record
        $this->checkFileSize();
        parent::write($record);
    }

    private function checkFileSize(): void
    {
        // Check if the log file exists and if its size exceeds the maximum file size
        if (file_exists($this->url) && filesize($this->url) >= $this->maxFileSize) {
            $this->rotate();
        }
    }

    private function rotate(): void
    {
        // Generate a new file name with a timestamp
        $date = new \DateTime();
        $newFileName = $this->url . '.' . $date->format('YmdHis').'.log';

        // Rename the current log file to the new file name
        rename($this->url, $newFileName);

        // Re-open the log file for writing
        $this->stream = fopen($this->url, 'a');
    }
}
