<?php
/**
 * License key generator for SmartApply Pro
 */

class LicenseGenerator {
    private static $prefix;
    private static $keyLength;
    
    public function __construct() {
        self::$prefix = LICENSE_KEY_PREFIX;
        self::$keyLength = LICENSE_KEY_LENGTH;
    }
    
    /**
     * Generate a unique license key
     * @return string Formatted license key
     */
    public static function generateLicenseKey() {
        // Characters to use in license key (excluding similar looking characters)
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        
        $key = self::$prefix ?: LICENSE_KEY_PREFIX;
        $length = self::$keyLength ?: LICENSE_KEY_LENGTH;
        
        // Generate random characters
        for ($i = 0; $i < $length; $i++) {
            // Add separator every 4 characters for readability
            if ($i > 0 && $i % 4 === 0) {
                $key .= '-';
            }
            
            // Use secure random generation
            $randomIndex = random_int(0, strlen($chars) - 1);
            $key .= $chars[$randomIndex];
        }
        
        return $key;
    }
    
    /**
     * Validate license key format
     * @param string $licenseKey License key to validate
     * @return bool True if format is valid
     */
    public static function validateLicenseKeyFormat($licenseKey) {
        if (empty($licenseKey)) {
            return false;
        }
        
        $prefix = self::$prefix ?: LICENSE_KEY_PREFIX;
        
        // Check if it starts with the correct prefix
        if (!str_starts_with($licenseKey, $prefix)) {
            return false;
        }
        
        // Remove prefix and check the rest
        $keyPart = substr($licenseKey, strlen($prefix));
        
        // Should contain only valid characters and separators
        if (!preg_match('/^[A-Z0-9\-]+$/', $keyPart)) {
            return false;
        }
        
        // Check if it has reasonable length
        if (strlen($keyPart) < 10 || strlen($keyPart) > 50) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate multiple unique license keys
     * @param int $count Number of keys to generate
     * @return array Array of unique license keys
     */
    public static function generateMultipleLicenseKeys($count) {
        $keys = [];
        $attempts = 0;
        $maxAttempts = $count * 10; // Prevent infinite loops
        
        while (count($keys) < $count && $attempts < $maxAttempts) {
            $key = self::generateLicenseKey();
            
            // Ensure uniqueness
            if (!in_array($key, $keys)) {
                $keys[] = $key;
            }
            
            $attempts++;
        }
        
        return $keys;
    }
    
    /**
     * Create a license key with custom parameters
     * @param array $options Options for key generation
     * @return string Generated license key
     */
    public static function generateCustomLicenseKey($options = []) {
        $oldPrefix = self::$prefix;
        $oldLength = self::$keyLength;
        
        // Set custom parameters temporarily
        if (isset($options['prefix'])) {
            self::$prefix = $options['prefix'];
        }
        if (isset($options['length'])) {
            self::$keyLength = $options['length'];
        }
        
        $key = self::generateLicenseKey();
        
        // Restore original parameters
        self::$prefix = $oldPrefix;
        self::$keyLength = $oldLength;
        
        return $key;
    }
    
    /**
     * Extract information from license key
     * @param string $licenseKey License key to analyze
     * @return array Extracted information
     */
    public static function analyzeLicenseKey($licenseKey) {
        if (!self::validateLicenseKeyFormat($licenseKey)) {
            return [
                'valid' => false,
                'error' => 'Invalid license key format'
            ];
        }
        
        $prefix = self::$prefix ?: LICENSE_KEY_PREFIX;
        
        return [
            'valid' => true,
            'prefix' => $prefix,
            'keyPart' => substr($licenseKey, strlen($prefix)),
            'length' => strlen($licenseKey),
            'generatedAt' => date('c') // Placeholder - in real implementation might extract timestamp
        ];
    }
}
?>