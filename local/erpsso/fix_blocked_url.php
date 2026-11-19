<?php
/**
 * Run this ONCE from the command line to unblock erp.local in Moodle's
 * cURL security settings. This fixes the core OAuth2 login error:
 *   "Could not upgrade OAuth 2 token … The URL is blocked"
 *
 * Usage (from your Moodle root):
 *   php local\erpsso\fix_blocked_url.php --host=erp.local
 *   php local\erpsso\fix_blocked_url.php --host=erp.local --dryrun
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options] = cli_get_params(
    ['host' => 'erp.local', 'dryrun' => false, 'help' => false],
    ['h' => 'help']
);

if ($options['help']) {
    echo "Usage: php fix_blocked_url.php --host=erp.local [--dryrun]\n";
    exit(0);
}

$host   = trim((string) $options['host']);
$dryrun = (bool) $options['dryrun'];

echo "Host    : $host\n";
echo "Dry run : " . ($dryrun ? 'YES' : 'NO') . "\n\n";

// --- Remove from blocked list ---
$blocked = array_filter(array_map('trim', explode("\n", (string) get_config('core', 'curlsecurityblockedhosts'))));
$new_blocked = array_values(array_filter($blocked, function($line) use ($host) {
    return strcasecmp(ltrim($line, '*.'), $host) !== 0 && strcasecmp($line, $host) !== 0;
}));

if (count($new_blocked) < count($blocked)) {
    echo "Removing '$host' from blocked list.\n";
    if (!$dryrun) {
        set_config('curlsecurityblockedhosts', implode("\n", $new_blocked));
    }
} else {
    echo "'$host' was not in the blocked list.\n";
}

// --- Add to allowed list ---
$allowed = array_filter(array_map('trim', explode("\n", (string) get_config('core', 'curlsecurityalloweddomains'))));
$already = array_filter($allowed, fn($l) => strcasecmp(trim($l), $host) === 0);

if ($already) {
    echo "'$host' is already in the allowed list.\n";
} else {
    $allowed[] = $host;
    echo "Adding '$host' to allowed list.\n";
    if (!$dryrun) {
        set_config('curlsecurityalloweddomains', implode("\n", $allowed));
    }
}

echo "\nDone.\n";
if (!$dryrun) {
    echo "Now run: php admin\\cli\\purge_caches.php\n";
}