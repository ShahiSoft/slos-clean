<?php
/**
 * FixEngine Feature Flags and Configuration
 * 
 * Central configuration for FixEngine rollout and migration safety.
 * 
 * @package SLOSLegalFlowSuite
 * @subpackage FixEngine
 * @since 3.1.2
 */

namespace ShahiLegalFlowSuite\FixEngine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Feature Flags Manager
 * 
 * Controls rollout of FixEngine features and migration locks.
 */
class FeatureFlags {
    
    /**
     * Option keys
     */
    const FIXENGINE_ENABLED = 'slos_fixengine_enabled';
    const MIGRATION_LOCK = 'slos_fixengine_migration_lock';
    const LEGACY_FALLBACK = 'slos_fixengine_legacy_fallback';
    
    /**
     * Check if FixEngine is enabled
     * 
     * @return bool
     */
    public static function is_fixengine_enabled(): bool {
        // Check environment override
        if (defined('SLOS_FIXENGINE_ENABLED')) {
            return (bool) SLOS_FIXENGINE_ENABLED;
        }
        
        // Check if staging/dev environment
        $is_staging = defined('WP_ENV') && in_array(WP_ENV, ['staging', 'development', 'local']);
        
        // Default: enabled only in staging unless explicitly set
        $default = $is_staging;
        
        return (bool) get_option(self::FIXENGINE_ENABLED, $default);
    }
    
    /**
     * Enable FixEngine
     * 
     * @return bool Success
     */
    public static function enable_fixengine(): bool {
        return update_option(self::FIXENGINE_ENABLED, true);
    }
    
    /**
     * Disable FixEngine
     * 
     * @return bool Success
     */
    public static function disable_fixengine(): bool {
        return update_option(self::FIXENGINE_ENABLED, false);
    }
    
    /**
     * Check if migration is locked
     * 
     * @return bool
     */
    public static function is_migration_locked(): bool {
        return (bool) get_option(self::MIGRATION_LOCK, false);
    }
    
    /**
     * Lock migrations (prevent writes during schema changes)
     * 
     * @return bool Success
     */
    public static function lock_migration(): bool {
        return update_option(self::MIGRATION_LOCK, true);
    }
    
    /**
     * Unlock migrations
     * 
     * @return bool Success
     */
    public static function unlock_migration(): bool {
        return delete_option(self::MIGRATION_LOCK);
    }
    
    /**
     * Check if legacy fallback is enabled
     * 
     * @return bool
     */
    public static function is_legacy_fallback_enabled(): bool {
        return (bool) get_option(self::LEGACY_FALLBACK, false);
    }
    
    /**
     * Enable emergency legacy fallback
     * 
     * @return bool Success
     */
    public static function enable_legacy_fallback(): bool {
        return update_option(self::LEGACY_FALLBACK, true);
    }
    
    /**
     * Disable legacy fallback
     * 
     * @return bool Success
     */
    public static function disable_legacy_fallback(): bool {
        return delete_option(self::LEGACY_FALLBACK);
    }
    
    /**
     * Get feature flags status
     * 
     * @return array
     */
    public static function get_status(): array {
        return [
            'fixengine_enabled' => self::is_fixengine_enabled(),
            'migration_locked' => self::is_migration_locked(),
            'legacy_fallback' => self::is_legacy_fallback_enabled(),
            'environment' => defined('WP_ENV') ? WP_ENV : 'production',
        ];
    }
}
