<?php

/**
 * Optional maintenance cron for PeakRack Popup.
 *
 * Usage:
 * php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/lib/Popup.php';

try {
    peakrackPopupCreateTables();

    $updated = Capsule::table('mod_peakrack_popups')
        ->where('enabled', 1)
        ->where('archived', 0)
        ->whereNotNull('end_at')
        ->where('end_at', '<', peakrackPopupNow())
        ->update([
            'enabled' => 0,
            'updated_at' => peakrackPopupNow(),
        ]);

    echo 'PeakRack Popup cron completed. Disabled expired popups: ' . (int) $updated . PHP_EOL;
} catch (\Throwable $e) {
    fwrite(STDERR, 'PeakRack Popup cron failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
