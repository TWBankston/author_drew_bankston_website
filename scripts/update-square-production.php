<?php
/**
 * Update Square Integration to Production Mode
 * 
 * Upload this to the WordPress root and visit:
 * https://www.drewbankston.com/update-square-production.php
 * 
 * ⚠️ DELETE THIS FILE AFTER USE!
 */

// Load WordPress
require_once(__DIR__ . '/../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Production credentials
$production_config = array(
    'dbt_square_app_id'       => 'sq0idp-e4nyHcAY55_oXM4VHMikDg',
    'dbt_square_access_token' => 'EAAAlzr9_DJN8kN44zgde3ZdRIa5lGiSj2-he2h8X4OeDRYRXVW_WzQtxQB3h-Eo',
    'dbt_square_location_id'  => 'B9B3R7F2X5HMJ',
    'dbt_square_sandbox'      => '0', // 0 = production, 1 = sandbox
);

// Update each option
foreach ($production_config as $option_name => $option_value) {
    $updated = update_option($option_name, $option_value);
    if ($updated || get_option($option_name) === $option_value) {
        echo "✅ Updated {$option_name}<br>";
    } else {
        echo "❌ Failed to update {$option_name}<br>";
    }
}

echo "<br><hr><br>";
echo "<h2>🎉 Square Production Configuration Complete!</h2>";
echo "<p><strong>Current Settings:</strong></p>";
echo "<ul>";
echo "<li>App ID: " . get_option('dbt_square_app_id') . "</li>";
echo "<li>Location ID: " . get_option('dbt_square_location_id') . "</li>";
echo "<li>Sandbox Mode: " . (get_option('dbt_square_sandbox') ? 'ON (Sandbox)' : 'OFF (Production)') . "</li>";
echo "<li>Access Token: " . substr(get_option('dbt_square_access_token'), 0, 10) . "... (hidden)</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>✅ Verify settings look correct above</li>";
echo "<li>✅ Test a payment at: <a href='https://www.drewbankston.com/books/'>https://www.drewbankston.com/books/</a></li>";
echo "<li>✅ DELETE THIS FILE after testing!</li>";
echo "</ol>";

echo "<p><a href='/wp-admin/options-general.php?page=dbt-square' style='padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px;'>View Square Settings</a></p>";
