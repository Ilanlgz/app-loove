<?php

class Logger {
    private $logFile;

    public function __construct($logFile = 'app.log') {
        $this->logFile = $logFile;
    }

    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }

    public function logError($errorMessage) {
        $this->log("ERROR: $errorMessage");
    }

    public function logAction($actionMessage) {
        $this->log("ACTION: $actionMessage");
    }
}