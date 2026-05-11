<?php

/**
 * Lightweight event counter endpoint for PeakRack Popup.
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/lib/Popup.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$popupId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$event = (string) ($_POST['event'] ?? $_GET['event'] ?? '');
$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$allowed = [
    'view' => 'view_count',
    'click' => 'click_count',
    'close' => 'close_count',
];

if ($popupId <= 0 || !array_key_exists($event, $allowed) || !peakrackPopupVerifyTrackingToken($popupId, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if (!peakrackPopupTableReady()) {
        throw new \RuntimeException('Popup table is not ready.');
    }

    Capsule::table('mod_peakrack_popups')
        ->where('id', $popupId)
        ->increment($allowed[$event]);

    if (Capsule::schema()->hasTable('mod_peakrack_popup_events')) {
        Capsule::table('mod_peakrack_popup_events')->insert([
            'popup_id' => $popupId,
            'event' => $event,
            'client_id' => peakrackPopupCurrentClientId([]) ?: null,
            'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            'created_at' => peakrackPopupNow(),
        ]);
    }

    echo json_encode(['ok' => true], JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false], JSON_UNESCAPED_SLASHES);
}
