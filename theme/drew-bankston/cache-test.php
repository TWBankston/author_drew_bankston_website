<?php
/**
 * Cache Test File
 * Access at: https://dbankston.wordkeeper.net/wp-content/themes/drew-bankston/cache-test.php
 */

echo "<h1>Cache Test</h1>";
echo "<p><strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Theme Version:</strong> 2.1.0</p>";
echo "<p><strong>Test ID:</strong> " . uniqid() . "</p>";
echo "<p>If you see this content updating on refresh, PHP caching is working correctly.</p>";
echo "<p>If this content is stale/old, server-side PHP caching (OPcache/LiteSpeed) is active.</p>";


