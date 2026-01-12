<?php
/**
 * Plugin Validation Script
 * Run this to verify the plugin structure before installation
 */

echo "AI Blog Writer Plugin Validation\n";
echo "=================================\n\n";

$files = [
    'ai-blog-writer.php' => 'Main plugin file',
    'uninstall.php' => 'Uninstall script',
    'includes/class-aibw-api-handler.php' => 'API Handler',
    'includes/class-aibw-post-generator.php' => 'Post Generator',
    'includes/class-aibw-settings.php' => 'Settings Manager',
    'includes/class-aibw-admin.php' => 'Admin Interface',
    'assets/js/admin.js' => 'Admin JavaScript',
    'assets/css/admin.css' => 'Admin CSS',
    'README.md' => 'Documentation',
    'SETUP_GUIDE.md' => 'Setup Guide'
];

$missing = [];
$found = 0;

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ {$description}: {$file}\n";
        $found++;
    } else {
        echo "❌ {$description}: {$file} - MISSING\n";
        $missing[] = $file;
    }
}

echo "\n";
echo "Summary:\n";
echo "--------\n";
echo "Found: {$found}/" . count($files) . " files\n";

if (count($missing) > 0) {
    echo "Missing files:\n";
    foreach ($missing as $file) {
        echo "  - {$file}\n";
    }
    echo "\n❌ Plugin validation FAILED\n";
    exit(1);
} else {
    echo "✅ Plugin validation PASSED\n";
    echo "\nNext steps:\n";
    echo "1. Zip the plugin: zip -r ai-blog-writer.zip . -x \"*.git*\" \"validate-plugin.php\"\n";
    echo "2. Upload to WordPress Admin → Plugins → Add New → Upload Plugin\n";
    echo "3. Activate and configure\n";
    exit(0);
}