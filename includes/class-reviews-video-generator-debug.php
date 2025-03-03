<?php
/**
 * The debugging functionality of the plugin.
 *
 * @since      0.1.0
 */
class Reviews_Video_Generator_Debug {

    /**
     * Log a message to the debug log file.
     *
     * @since    0.1.0
     * @param    string    $message    The message to log.
     * @param    string    $level      The log level (debug, info, warning, error).
     * @param    array     $context    Additional context data.
     */
    public static function log($message, $level = 'debug', $context = array()) {
        // Only log if WP_DEBUG is enabled
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        // Format the log message
        $timestamp = current_time('mysql');
        $formatted_message = "[{$timestamp}] [{$level}] {$message}";
        
        // Add context if provided
        if (!empty($context)) {
            $formatted_message .= " | Context: " . json_encode($context);
        }
        
        // Log to the debug.log file
        error_log($formatted_message);
        
        // Also log to our custom log file
        $log_dir = RVG_PLUGIN_DIR . 'logs';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/rvg-debug.log';
        file_put_contents($log_file, $formatted_message . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Log a debug message.
     *
     * @since    0.1.0
     * @param    string    $message    The message to log.
     * @param    array     $context    Additional context data.
     */
    public static function debug($message, $context = array()) {
        self::log($message, 'debug', $context);
    }
    
    /**
     * Log an info message.
     *
     * @since    0.1.0
     * @param    string    $message    The message to log.
     * @param    array     $context    Additional context data.
     */
    public static function info($message, $context = array()) {
        self::log($message, 'info', $context);
    }
    
    /**
     * Log a warning message.
     *
     * @since    0.1.0
     * @param    string    $message    The message to log.
     * @param    array     $context    Additional context data.
     */
    public static function warning($message, $context = array()) {
        self::log($message, 'warning', $context);
    }
    
    /**
     * Log an error message.
     *
     * @since    0.1.0
     * @param    string    $message    The message to log.
     * @param    array     $context    Additional context data.
     */
    public static function error($message, $context = array()) {
        self::log($message, 'error', $context);
    }
    
    /**
     * Log an exception.
     *
     * @since    0.1.0
     * @param    Exception    $exception    The exception to log.
     * @param    string       $message      Additional message.
     */
    public static function exception($exception, $message = '') {
        $context = array(
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        );
        
        $log_message = empty($message) ? 'Exception: ' . $exception->getMessage() : $message;
        self::error($log_message, $context);
    }
}
