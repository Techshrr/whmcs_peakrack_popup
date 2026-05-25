<?php
// SPDX-License-Identifier: MIT

/**
 * PeakRack Popup for WHMCS
 *
 * Official repository:
 * https://github.com/Techshrr/whmcs_peakrack_popup
 *
 * Copyright (c) 2026 PeakRack.
 * Licensed under the MIT License.
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
