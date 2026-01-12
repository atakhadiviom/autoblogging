<?php
/**
 * Uninstall script for AI Blog Writer
 * 
 * This file runs when the plugin is deleted
 * It cleans up all plugin data from the database
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('aibw_settings');

// Remove any transients (if used)
delete_transient('aibw_api_status');

// Note: We intentionally keep generated posts and metadata
// as they may be valuable content. Only plugin settings are removed.