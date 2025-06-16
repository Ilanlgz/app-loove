<?php

/**
 * Modern Error Handler for Loove Dating App
 * Handles errors, exceptions, and logging with style
 */
class ErrorHandler {
    private static $logFile;
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        // Set log file path
        self::$logFile = BASE_PATH . '/logs/app.log';
        
        // Create logs directory if it doesn't exist
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Set error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        self::$initialized = true;
        
        // Log initialization
        self::logInfo("ErrorHandler initialized successfully");
    }
    
    public static function handleError($severity, $message, $file, $line) {
        // Don't handle errors that are suppressed with @
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED'
        ];
        
        $errorType = $errorTypes[$severity] ?? 'UNKNOWN ERROR';
        
        // Log the error
        self::logError("{$errorType}: {$message}", $file, $line);
        
        // In development, show detailed error
        if (defined('APP_ENV') && APP_ENV === 'development') {
            self::displayDevelopmentError($errorType, $message, $file, $line);
        }
        
        return true;
    }
    
    public static function handleException($exception) {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        // Log the exception
        self::logError("EXCEPTION: {$message}", $file, $line, $trace);
        
        // Display appropriate error page
        if (defined('APP_ENV') && APP_ENV === 'development') {
            self::displayDevelopmentException($exception);
        } else {
            self::displayProductionError();
        }
    }
    
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError("FATAL ERROR: {$error['message']}", $error['file'], $error['line']);
            
            if (defined('APP_ENV') && APP_ENV === 'development') {
                self::displayDevelopmentError('FATAL ERROR', $error['message'], $error['file'], $error['line']);
            } else {
                self::displayProductionError();
            }
        }
    }
    
    public static function logError($message, $file = '', $line = 0, $trace = '') {
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip = self::getClientIP();
        $url = $_SERVER['REQUEST_URI'] ?? '';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => 'ERROR',
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'url' => $url,
            'ip' => $ip,
            'user_agent' => $userAgent
        ];
        
        if ($trace) {
            $logEntry['trace'] = $trace;
        }
        
        self::writeLog($logEntry);
    }
    
    public static function logInfo($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => 'INFO',
            'message' => $message,
            'context' => $context
        ];
        
        self::writeLog($logEntry);
    }
    
    public static function logAppAction($message, $userId = null) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getClientIP();
        $url = $_SERVER['REQUEST_URI'] ?? '';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => 'ACTION',
            'message' => $message,
            'user_id' => $userId ?? ($_SESSION['user_id'] ?? null),
            'ip' => $ip,
            'url' => $url
        ];
        
        self::writeLog($logEntry);
    }
    
    private static function writeLog($logEntry) {
        if (!self::$logFile) {
            return;
        }
        
        $formattedEntry = sprintf(
            "[%s] %s: %s %s\n",
            $logEntry['timestamp'],
            $logEntry['level'],
            $logEntry['message'],
            isset($logEntry['file']) ? "in {$logEntry['file']}:{$logEntry['line']}" : ''
        );
        
        // Add additional context if available
        if (isset($logEntry['url']) && $logEntry['url']) {
            $formattedEntry .= "URL: {$logEntry['url']}\n";
        }
        if (isset($logEntry['ip']) && $logEntry['ip']) {
            $formattedEntry .= "IP: {$logEntry['ip']}\n";
        }
        if (isset($logEntry['trace'])) {
            $formattedEntry .= "Trace:\n{$logEntry['trace']}\n";
        }
        $formattedEntry .= "---\n";
        
        file_put_contents(self::$logFile, $formattedEntry, FILE_APPEND | LOCK_EX);
    }
    
    private static function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return 'Unknown';
    }
    
    private static function displayDevelopmentError($type, $message, $file, $line) {
        if (headers_sent()) {
            return;
        }
        
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        
        echo self::generateErrorPage($type, $message, $file, $line, true);
    }
    
    private static function displayDevelopmentException($exception) {
        if (headers_sent()) {
            return;
        }
        
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        
        $type = get_class($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTrace();
        
        echo self::generateErrorPage($type, $message, $file, $line, true, $trace);
    }
    
    private static function displayProductionError() {
        if (headers_sent()) {
            return;
        }
        
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        
        echo self::generateErrorPage('Application Error', 'Something went wrong', '', 0, false);
    }
    
    private static function generateErrorPage($type, $message, $file, $line, $isDevelopment, $trace = null) {
        $title = $isDevelopment ? "Loove - Erreur de développement" : "Loove - Erreur";
        $safeMessage = htmlspecialchars($message);
        $safeFile = htmlspecialchars($file);
        
        $html = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap' rel='stylesheet'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        
        .error-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 10px;
        }
        
        .error-subtitle {
            color: #cbd5e1;
            font-size: 16px;
        }
        
        .error-details {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .error-message {
            color: #fecaca;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .error-location {
            color: #cbd5e1;
            font-size: 13px;
        }
        
        .trace {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            font-size: 12px;
            color: #e2e8f0;
        }
        
        .back-link {
            display: inline-block;
            background: linear-gradient(135deg, #e94057, #ff6b9d);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 20px;
            transition: transform 0.2s ease;
        }
        
        .back-link:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 640px) {
            .error-container {
                padding: 24px;
                margin: 16px;
            }
            .error-title { font-size: 24px; }
            .error-icon { font-size: 48px; }
        }
    </style>
</head>
<body>
    <div class='error-container'>
        <div class='error-header'>
            <div class='error-icon'>⚠️</div>
            <h1 class='error-title'>{$type}</h1>
            <p class='error-subtitle'>" . ($isDevelopment ? 'Erreur de développement détectée' : 'Une erreur inattendue s\'est produite') . "</p>
        </div>";
        
        if ($isDevelopment) {
            $html .= "<div class='error-details'>
                <div class='error-message'>{$safeMessage}</div>";
            
            if ($file && $line) {
                $html .= "<div class='error-location'>Fichier: {$safeFile} ligne {$line}</div>";
            }
            
            if ($trace && is_array($trace)) {
                $html .= "<div class='trace'><strong>Stack Trace:</strong><br>";
                foreach ($trace as $index => $step) {
                    $stepFile = htmlspecialchars($step['file'] ?? 'Unknown');
                    $stepLine = $step['line'] ?? 0;
                    $stepFunction = htmlspecialchars(($step['class'] ?? '') . ($step['type'] ?? '') . ($step['function'] ?? ''));
                    $html .= "#{$index} {$stepFile}({$stepLine}): {$stepFunction}()<br>";
                }
                $html .= "</div>";
            }
            
            $html .= "</div>";
        } else {
            $html .= "<div class='error-details'>
                <div class='error-message'>Nous nous excusons pour ce désagrément. Notre équipe technique a été notifiée.</div>
                <div class='error-location'>Veuillez réessayer dans quelques minutes.</div>
            </div>";
        }
        
        $html .= "<div style='text-align: center;'>
            <a href='javascript:history.back()' class='back-link'>← Retour</a>
        </div>
    </div>
</body>
</html>";
        
        return $html;
    }
}
?>
