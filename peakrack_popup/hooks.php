<?php

/**
 * Frontend hooks for the PeakRack Popup addon.
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
