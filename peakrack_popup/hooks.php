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

if (!defined('WHMCS')) {
    die('No direct access');
}

require_once __DIR__ . '/lib/Popup.php';

add_hook('ClientAreaFooterOutput', 1, static function (array $vars): string {
    try {
        $popup = peakrackPopupActivePopup($vars);
        if (!$popup) {
            return '';
        }

        return peakrackPopupRender($popup, $vars);
    } catch (\Throwable $e) {
        return '';
    }
});
