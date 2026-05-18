<?php

/**
 * Shared runtime helpers for the PeakRack Popup addon.
 *
 * Target runtime: WHMCS 9.x / PHP 8.3.
 */

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('No direct access');
}

function peakrackPopupCreateTables(): void
{
    $schema = Capsule::schema();

    if (!$schema->hasTable('mod_peakrack_popups')) {
        $schema->create('mod_peakrack_popups', static function ($table): void {
            $table->increments('id');
            $table->string('name', 150);
            $table->boolean('enabled')->default(false)->index();
            $table->string('type', 40)->default('promotion')->index();
            $table->string('display_mode', 40)->default('modal');
            $table->string('audience', 40)->default('all')->index();
            $table->text('client_group_ids')->nullable();
            $table->text('page_rules')->nullable();
            $table->string('frequency', 40)->default('daily');
            $table->string('theme', 40)->default('blue');
            $table->string('accent_color', 20)->nullable();
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->string('title_en', 200)->nullable();
            $table->text('body_en')->nullable();
            $table->string('coupon_code', 80)->nullable();
            $table->string('button_label', 100)->nullable();
            $table->string('button_label_en', 100)->nullable();
            $table->text('button_url')->nullable();
            $table->text('image_url')->nullable();
            $table->boolean('open_new_tab')->default(false);
            $table->integer('priority')->default(0)->index();
            $table->integer('delay_seconds')->default(0);
            $table->integer('auto_close_seconds')->default(0);
            $table->dateTime('start_at')->nullable()->index();
            $table->dateTime('end_at')->nullable()->index();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('close_count')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    } else {
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'title_en', static function ($table): void {
            $table->string('title_en', 200)->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'body_en', static function ($table): void {
            $table->text('body_en')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'button_label_en', static function ($table): void {
            $table->string('button_label_en', 100)->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'accent_color', static function ($table): void {
            $table->string('accent_color', 20)->nullable();
        });
    }

    if (!$schema->hasTable('mod_peakrack_popup_events')) {
        $schema->create('mod_peakrack_popup_events', static function ($table): void {
            $table->increments('id');
            $table->unsignedInteger('popup_id')->index();
            $table->string('event', 20)->index();
            $table->unsignedInteger('client_id')->nullable()->index();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    peakrackPopupBackfillDefaultEnglish();
}

function peakrackPopupEnsureColumn(string $tableName, string $columnName, callable $definition): void
{
    $schema = Capsule::schema();
    if ($schema->hasColumn($tableName, $columnName)) {
        return;
    }

    $schema->table($tableName, static function ($table) use ($definition): void {
        $definition($table);
    });
}

function peakrackPopupBackfillDefaultEnglish(): void
{
    try {
        if (!Capsule::schema()->hasColumn('mod_peakrack_popups', 'title_en')) {
            return;
        }

        Capsule::table('mod_peakrack_popups')
            ->where('name', 'Sample promotion popup')
            ->where(static function ($query): void {
                $query->whereNull('title_en')->orWhere('title_en', '');
            })
            ->update([
                'title_en' => 'Limited-time offer',
                'body_en' => "Use this popup for package deals, coupons, domain launches, maintenance notices, or urgent announcements.\nEdit the campaign content before enabling it.",
                'button_label_en' => 'View offer',
                'accent_color' => '#2563eb',
                'updated_at' => peakrackPopupNow(),
            ]);
    } catch (\Throwable $e) {
        // Schema migrations should not block the addon page if a host has a restrictive DB user.
    }
}

function peakrackPopupSeedDefaultIfEmpty(): void
{
    if (Capsule::table('mod_peakrack_popups')->count() > 0) {
        return;
    }

    $now = peakrackPopupNow();
    Capsule::table('mod_peakrack_popups')->insert([
        'name' => 'Sample promotion popup',
        'enabled' => 0,
        'type' => 'promotion',
        'display_mode' => 'modal',
        'audience' => 'all',
        'client_group_ids' => '',
        'page_rules' => '*',
        'frequency' => 'daily',
        'theme' => 'blue',
        'accent_color' => '#2563eb',
        'title' => '限时优惠',
        'body' => "这里可以发布资源套餐、优惠码、域名上新、线路维护或紧急公告。\n启用前请先按你的活动内容编辑。",
        'title_en' => 'Limited-time offer',
        'body_en' => "Use this popup for package deals, coupons, domain launches, maintenance notices, or urgent announcements.\nEdit the campaign content before enabling it.",
        'coupon_code' => 'PEAKRACK',
        'button_label' => '立即查看',
        'button_label_en' => 'View offer',
        'button_url' => 'cart.php',
        'image_url' => '',
        'open_new_tab' => 0,
        'priority' => 10,
        'delay_seconds' => 1,
        'auto_close_seconds' => 0,
        'start_at' => null,
        'end_at' => null,
        'view_count' => 0,
        'click_count' => 0,
        'close_count' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

function peakrackPopupTableReady(): bool
{
    try {
        return Capsule::schema()->hasTable('mod_peakrack_popups');
    } catch (\Throwable $e) {
        return false;
    }
}

function peakrackPopupNow(): string
{
    return date('Y-m-d H:i:s');
}

function peakrackPopupE(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function peakrackPopupBool(mixed $value): int
{
    return in_array($value, [1, '1', true, 'true', 'on', 'yes'], true) ? 1 : 0;
}

function peakrackPopupNormalizeLines(mixed $value): string
{
    $raw = str_replace(["\r\n", "\r"], "\n", (string) $value);
    $items = [];

    foreach (preg_split('/[\n,]+/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '' && !in_array($line, $items, true)) {
            $items[] = $line;
        }
    }

    return implode("\n", $items);
}

function peakrackPopupNormalizeIntCsv(mixed $value): string
{
    $items = [];
    foreach (preg_split('/[,\s]+/', (string) $value) ?: [] as $item) {
        $id = (int) $item;
        if ($id > 0 && !in_array((string) $id, $items, true)) {
            $items[] = (string) $id;
        }
    }

    return implode(',', $items);
}

function peakrackPopupDateTimeForDb(mixed $value): ?string
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function peakrackPopupDateTimeForInput(mixed $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}

function peakrackPopupValidChoice(mixed $value, array $allowed, string $default): string
{
    $value = (string) $value;
    return in_array($value, $allowed, true) ? $value : $default;
}

function peakrackPopupSafeLink(mixed $value): string
{
    $url = trim((string) $value);
    if ($url === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '/')) {
        return $url;
    }

    if (preg_match('/^[a-z0-9_\-\/]+\.php(?:[?#].*)?$/i', $url)) {
        return $url;
    }

    if (preg_match('/^(cart|clientarea|announcements|networkissues|submitticket|supporttickets|domainchecker)\.php(?:[?#].*)?$/i', $url)) {
        return $url;
    }

    return '';
}

function peakrackPopupSafeImageUrl(mixed $value): string
{
    $url = trim((string) $value);
    if ($url === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '/')) {
        return $url;
    }

    return '';
}

function peakrackPopupNormalizeLanguage(mixed $value): string
{
    if (is_array($value) || is_object($value)) {
        return '';
    }

    $value = strtolower(trim((string) $value));
    if ($value === '') {
        return '';
    }

    $compact = str_replace(['-', '_', ' '], '', $value);
    if (
        str_contains($compact, 'zh')
        || str_contains($compact, 'cn')
        || str_contains($compact, 'hans')
        || str_contains($compact, 'hant')
        || str_contains($compact, 'chinese')
        || str_contains($compact, '中文')
        || str_contains($compact, '简体')
        || str_contains($compact, '繁體')
    ) {
        return 'zh';
    }

    if (str_contains($compact, 'en') || str_contains($compact, 'english')) {
        return 'en';
    }

    return 'en';
}

function peakrackPopupClientLanguage(array $vars = []): string
{
    $candidates = [
        $_GET['language'] ?? null,
        $_GET['lang'] ?? null,
        $vars['language'] ?? null,
        $vars['clientlanguage'] ?? null,
        $vars['locale'] ?? null,
        $_SESSION['Language'] ?? null,
        $_SESSION['language'] ?? null,
        $_SESSION['locale'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        $language = peakrackPopupNormalizeLanguage($candidate);
        if ($language !== '') {
            return $language;
        }
    }

    return 'en';
}

function peakrackPopupLocalizedValue(array $popup, string $field, string $language): string
{
    $baseValue = trim((string) ($popup[$field] ?? ''));
    $englishValue = trim((string) ($popup[$field . '_en'] ?? ''));

    if ($language === 'en') {
        return $englishValue !== '' ? $englishValue : $baseValue;
    }

    return $baseValue !== '' ? $baseValue : $englishValue;
}

function peakrackPopupTypeLabel(string $type, string $language): string
{
    $labels = [
        'zh' => [
            'promotion' => '优惠活动',
            'coupon' => '优惠码',
            'domain' => '域名优惠',
            'maintenance' => '维护通知',
            'urgent' => '紧急公告',
            'group_buy' => '拼团优惠',
            'notice' => '通知',
        ],
        'en' => [
            'promotion' => 'Promotion',
            'coupon' => 'Coupon',
            'domain' => 'Domain deal',
            'maintenance' => 'Maintenance',
            'urgent' => 'Urgent notice',
            'group_buy' => 'Group deal',
            'notice' => 'Notice',
        ],
    ];

    return $labels[$language][$type] ?? $labels[$language]['notice'];
}

function peakrackPopupClientText(string $language, string $key): string
{
    $texts = [
        'zh' => [
            'close' => '关闭',
            'copy' => '复制',
            'copied' => '已复制',
            'coupon' => '优惠码',
            'later' => '稍后再说',
        ],
        'en' => [
            'close' => 'Close',
            'copy' => 'Copy',
            'copied' => 'Copied',
            'coupon' => 'Coupon code',
            'later' => 'Maybe later',
        ],
    ];

    return $texts[$language][$key] ?? $texts['en'][$key] ?? $key;
}

function peakrackPopupCurrentClientId(array $vars = []): int
{
    $candidates = [
        $vars['clientid'] ?? null,
        $vars['userid'] ?? null,
        $vars['clientsdetails']['userid'] ?? null,
        $vars['clientsdetails']['id'] ?? null,
        $_SESSION['uid'] ?? null,
    ];

    if (isset($vars['client']) && is_object($vars['client']) && isset($vars['client']->id)) {
        $candidates[] = $vars['client']->id;
    }

    foreach ($candidates as $candidate) {
        $id = (int) $candidate;
        if ($id > 0) {
            return $id;
        }
    }

    return 0;
}

function peakrackPopupCurrentClientGroupId(array $vars = []): int
{
    $candidates = [
        $vars['clientsdetails']['groupid'] ?? null,
        $vars['client']['groupid'] ?? null,
    ];

    if (isset($vars['client']) && is_object($vars['client']) && isset($vars['client']->groupid)) {
        $candidates[] = $vars['client']->groupid;
    }

    foreach ($candidates as $candidate) {
        $id = (int) $candidate;
        if ($id > 0) {
            return $id;
        }
    }

    $clientId = peakrackPopupCurrentClientId($vars);
    if ($clientId <= 0) {
        return 0;
    }

    try {
        return (int) Capsule::table('tblclients')->where('id', $clientId)->value('groupid');
    } catch (\Throwable $e) {
        return 0;
    }
}

function peakrackPopupIsLoggedIn(array $vars = []): bool
{
    return !empty($vars['loggedin']) || peakrackPopupCurrentClientId($vars) > 0;
}

function peakrackPopupAudienceMatches(array $popup, array $vars): bool
{
    $audience = peakrackPopupValidChoice($popup['audience'] ?? 'all', ['all', 'guests', 'clients', 'client_groups'], 'all');
    $loggedIn = peakrackPopupIsLoggedIn($vars);

    if ($audience === 'all') {
        return true;
    }

    if ($audience === 'guests') {
        return !$loggedIn;
    }

    if ($audience === 'clients') {
        return $loggedIn;
    }

    $groupId = peakrackPopupCurrentClientGroupId($vars);
    if (!$loggedIn || $groupId <= 0) {
        return false;
    }

    $allowed = array_filter(array_map('trim', explode(',', (string) ($popup['client_group_ids'] ?? ''))));
    return in_array((string) $groupId, $allowed, true);
}

function peakrackPopupRequestCandidates(): array
{
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $phpSelf = (string) ($_SERVER['PHP_SELF'] ?? '');
    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    $path = (string) (parse_url($requestUri, PHP_URL_PATH) ?: $scriptName ?: $phpSelf);
    $path = '/' . ltrim($path, '/');
    $basename = basename($path);

    $items = [
        strtolower($path),
        strtolower(ltrim($path, '/')),
        strtolower($basename),
    ];

    if ($query !== '') {
        $items[] = strtolower($path . '?' . $query);
        $items[] = strtolower(ltrim($path, '/') . '?' . $query);
        $items[] = strtolower($basename . '?' . $query);
    }

    return array_values(array_unique(array_filter($items)));
}

function peakrackPopupWildcardMatches(string $pattern, string $value): bool
{
    $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
    return (bool) preg_match($regex, $value);
}

function peakrackPopupPageMatches(array $popup): bool
{
    $rules = trim((string) ($popup['page_rules'] ?? ''));
    if ($rules === '' || $rules === '*') {
        return true;
    }

    $candidates = peakrackPopupRequestCandidates();
    foreach (preg_split('/[\n,]+/', $rules) ?: [] as $rule) {
        $rule = strtolower(trim($rule));
        if ($rule === '' || $rule === '*' || $rule === 'all') {
            return true;
        }

        foreach ($candidates as $candidate) {
            if ($candidate === $rule || str_starts_with($candidate, $rule . '?')) {
                return true;
            }

            if (str_contains($rule, '*') && peakrackPopupWildcardMatches($rule, $candidate)) {
                return true;
            }

            if (!str_contains($rule, '*') && str_contains($candidate, $rule)) {
                return true;
            }
        }
    }

    return false;
}

function peakrackPopupObjectToArray(object|array $row): array
{
    return is_array($row) ? $row : get_object_vars($row);
}

function peakrackPopupActivePopup(array $vars): ?array
{
    if (!peakrackPopupTableReady()) {
        return null;
    }

    $now = peakrackPopupNow();

    try {
        $rows = Capsule::table('mod_peakrack_popups')
            ->where('enabled', 1)
            ->where(static function ($query) use ($now): void {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(static function ($query) use ($now): void {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->limit(25)
            ->get();
    } catch (\Throwable $e) {
        return null;
    }

    foreach ($rows as $row) {
        $popup = peakrackPopupObjectToArray($row);
        if (peakrackPopupAudienceMatches($popup, $vars) && peakrackPopupPageMatches($popup)) {
            return $popup;
        }
    }

    return null;
}

function peakrackPopupTrackingSecret(): string
{
    global $cc_encryption_hash;

    $secret = (string) ($cc_encryption_hash ?? '');
    if ($secret !== '') {
        return $secret;
    }

    if (defined('ROOTDIR')) {
        return hash('sha256', (string) ROOTDIR);
    }

    return hash('sha256', __DIR__);
}

function peakrackPopupTrackingToken(int $popupId, ?string $day = null): string
{
    $day = $day ?: date('Ymd');
    return hash_hmac('sha256', $popupId . '|' . $day, peakrackPopupTrackingSecret());
}

function peakrackPopupVerifyTrackingToken(int $popupId, string $token): bool
{
    if ($popupId <= 0 || $token === '') {
        return false;
    }

    $today = peakrackPopupTrackingToken($popupId, date('Ymd'));
    $yesterday = peakrackPopupTrackingToken($popupId, date('Ymd', strtotime('-1 day')));

    return hash_equals($today, $token) || hash_equals($yesterday, $token);
}

function peakrackPopupTrackUrl(array $vars): string
{
    $webRoot = trim((string) ($vars['WEB_ROOT'] ?? ''));
    $webRoot = $webRoot === '/' ? '' : rtrim($webRoot, '/');

    return $webRoot . '/modules/addons/peakrack_popup/track.php';
}

function peakrackPopupThemeAccent(string $theme): string
{
    return match ($theme) {
        'green' => '#16a34a',
        'orange' => '#f97316',
        'red' => '#dc2626',
        'slate' => '#334155',
        default => '#2563eb',
    };
}

function peakrackPopupNormalizeAccentColor(mixed $value, string $fallback = '#2563eb'): string
{
    $value = strtolower(trim((string) $value));
    if (preg_match('/^#[0-9a-f]{6}$/', $value)) {
        return $value;
    }

    if (preg_match('/^[0-9a-f]{6}$/', $value)) {
        return '#' . $value;
    }

    return $fallback;
}

function peakrackPopupAccent(array $popup, string $theme): string
{
    $fallback = peakrackPopupThemeAccent($theme);
    return peakrackPopupNormalizeAccentColor($popup['accent_color'] ?? '', $fallback);
}

function peakrackPopupRender(array $popup, array $vars): string
{
    $id = (int) ($popup['id'] ?? 0);
    if ($id <= 0) {
        return '';
    }

    $language = peakrackPopupClientLanguage($vars);
    $mode = peakrackPopupValidChoice($popup['display_mode'] ?? 'modal', ['modal', 'top_bar', 'bottom_bar', 'corner'], 'modal');
    $frequency = peakrackPopupValidChoice($popup['frequency'] ?? 'daily', ['every_page', 'session', 'daily', 'once'], 'daily');
    $theme = peakrackPopupValidChoice($popup['theme'] ?? 'blue', ['blue', 'green', 'orange', 'red', 'slate'], 'blue');
    $type = peakrackPopupValidChoice($popup['type'] ?? 'notice', ['promotion', 'coupon', 'domain', 'maintenance', 'urgent', 'group_buy', 'notice'], 'notice');
    $title = peakrackPopupLocalizedValue($popup, 'title', $language);
    $body = peakrackPopupLocalizedValue($popup, 'body', $language);
    $coupon = trim((string) ($popup['coupon_code'] ?? ''));
    $buttonLabel = peakrackPopupLocalizedValue($popup, 'button_label', $language);
    $buttonUrl = peakrackPopupSafeLink($popup['button_url'] ?? '');
    $imageUrl = peakrackPopupSafeImageUrl($popup['image_url'] ?? '');
    $delay = max(0, min(60, (int) ($popup['delay_seconds'] ?? 0)));
    $autoClose = max(0, min(3600, (int) ($popup['auto_close_seconds'] ?? 0)));
    $openNew = peakrackPopupBool($popup['open_new_tab'] ?? 0) === 1;
    $updatedAt = (string) ($popup['updated_at'] ?? '');
    $version = substr(sha1($updatedAt . '|' . $title . '|' . $body . '|' . $coupon . '|' . $buttonLabel), 0, 12);
    $token = peakrackPopupTrackingToken($id);
    $trackUrl = peakrackPopupTrackUrl($vars);
    $accent = peakrackPopupAccent($popup, $theme);
    $rootId = 'peakrack-popup-' . $id;
    $typeLabel = peakrackPopupTypeLabel($type, $language);
    $copyLabel = peakrackPopupClientText($language, 'copy');
    $copiedLabel = peakrackPopupClientText($language, 'copied');
    $couponLabel = peakrackPopupClientText($language, 'coupon');
    $closeLabel = peakrackPopupClientText($language, 'close');
    $laterLabel = peakrackPopupClientText($language, 'later');
    $labelAttribute = $title !== ''
        ? ' aria-labelledby="' . peakrackPopupE($rootId) . '-title"'
        : ' aria-label="' . peakrackPopupE($typeLabel) . '"';

    ob_start();
    ?>
    <style>
        .prp-root[hidden]{display:none!important}
        .prp-root{position:fixed;z-index:2147483000;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#172033}
        .prp-root *{box-sizing:border-box}
        .prp-root[data-mode="modal"]{inset:0;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.42);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px)}
        .prp-root[data-mode="top_bar"]{top:16px;left:16px;right:16px;display:flex;justify-content:center;pointer-events:none}
        .prp-root[data-mode="bottom_bar"]{left:16px;right:16px;bottom:16px;display:flex;justify-content:center;pointer-events:none}
        .prp-root[data-mode="corner"]{right:20px;bottom:20px;width:min(390px,calc(100vw - 32px));pointer-events:none}
        .prp-panel{position:relative;width:100%;max-width:620px;overflow:hidden;border:1px solid rgba(148,163,184,.22);border-radius:12px;background:#fff;box-shadow:0 22px 60px rgba(15,23,42,.18),0 2px 8px rgba(15,23,42,.06);pointer-events:auto;animation:prp-in .18s ease-out}
        .prp-root[data-mode="top_bar"] .prp-panel,.prp-root[data-mode="bottom_bar"] .prp-panel{max-width:980px;border-radius:10px}
        .prp-root[data-mode="corner"] .prp-panel{max-width:none}
        .prp-accent{height:4px;background:linear-gradient(90deg,var(--prp-accent),color-mix(in srgb,var(--prp-accent) 24%,#fff))}
        .prp-close{position:absolute;top:12px;right:12px;display:flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid transparent;border-radius:999px;background:rgba(255,255,255,.92);color:#667085;font-size:21px;line-height:1;cursor:pointer;transition:background .16s ease,color .16s ease,border-color .16s ease}
        .prp-close:hover{background:#f8fafc;border-color:#e2e8f0;color:#0f172a}
        .prp-inner{display:block;padding:28px}
        .prp-inner.prp-has-media{display:grid;grid-template-columns:178px minmax(0,1fr);gap:24px}
        .prp-media{width:178px;min-width:178px;aspect-ratio:1.18;border-radius:10px;overflow:hidden;background:#f4f7fb}
        .prp-media img{width:100%;height:100%;object-fit:cover;display:block}
        .prp-content{min-width:0;padding-right:34px}
        .prp-heading{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin:0 0 12px}
        .prp-kicker{display:inline-flex;align-items:center;max-width:100%;margin:0;padding:4px 8px;border-radius:999px;background:color-mix(in srgb,var(--prp-accent) 10%,#fff);color:var(--prp-accent);font-size:11px;line-height:1.25;font-weight:700;text-transform:none}
        .prp-title{margin:0;font-size:22px;line-height:1.28;font-weight:750;color:#111827;letter-spacing:0}
        .prp-body{margin:0;color:#4b5563;font-size:14px;line-height:1.7;white-space:pre-line;overflow-wrap:anywhere}
        .prp-coupon{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:12px;width:min(100%,430px);margin-top:18px;padding:12px 14px;border:1px dashed color-mix(in srgb,var(--prp-accent) 48%,#cbd5e1);border-radius:10px;background:color-mix(in srgb,var(--prp-accent) 4%,#fff)}
        .prp-coupon-label{display:block;margin-bottom:3px;color:#667085;font-size:11px;font-weight:700;text-transform:none;letter-spacing:0}
        .prp-coupon-code{display:block;color:#111827;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:15px;font-weight:800;letter-spacing:.02em;overflow-wrap:anywhere}
        .prp-copy{border:0;border-radius:8px;background:var(--prp-accent);color:#fff;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap}
        .prp-copy:hover{filter:brightness(.96)}
        .prp-actions{display:flex;align-items:center;justify-content:flex-start;gap:12px;margin-top:20px;text-align:left}
        .prp-button{display:inline-flex;align-items:center;justify-content:center;min-width:128px;min-height:40px;border-radius:8px;background:var(--prp-accent);color:#fff!important;padding:9px 18px;text-decoration:none!important;font-size:14px;font-weight:750;box-shadow:0 10px 20px color-mix(in srgb,var(--prp-accent) 20%,transparent);transition:filter .16s ease,transform .16s ease}
        .prp-button:hover{filter:brightness(.96);color:#fff!important;transform:translateY(-1px)}
        .prp-secondary{border:0;background:transparent;color:#667085;font-size:13px;cursor:pointer;padding:8px 2px}
        .prp-secondary:hover{color:#111827}
        .prp-root[data-mode="top_bar"] .prp-inner,.prp-root[data-mode="bottom_bar"] .prp-inner{grid-template-columns:minmax(0,1fr) auto;align-items:center;padding:15px 18px}
        .prp-root[data-mode="top_bar"] .prp-media,.prp-root[data-mode="bottom_bar"] .prp-media{display:none}
        .prp-root[data-mode="top_bar"] .prp-heading,.prp-root[data-mode="bottom_bar"] .prp-heading{gap:6px;margin-bottom:4px}
        .prp-root[data-mode="top_bar"] .prp-title,.prp-root[data-mode="bottom_bar"] .prp-title{font-size:17px}
        .prp-root[data-mode="top_bar"] .prp-body,.prp-root[data-mode="bottom_bar"] .prp-body{line-height:1.5}
        .prp-root[data-mode="top_bar"] .prp-coupon,.prp-root[data-mode="bottom_bar"] .prp-coupon{margin-top:10px;padding:9px 11px}
        .prp-root[data-mode="top_bar"] .prp-actions,.prp-root[data-mode="bottom_bar"] .prp-actions{margin-top:0;flex-direction:row;flex-shrink:0}
        .prp-root[data-mode="top_bar"] .prp-content,.prp-root[data-mode="bottom_bar"] .prp-content{padding-right:38px}
        .prp-root[data-mode="corner"] .prp-inner{padding:24px}
        .prp-root[data-mode="corner"] .prp-title{font-size:19px}
        @keyframes prp-in{from{opacity:0;transform:translateY(8px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
        @supports not (background:color-mix(in srgb,#000 10%,#fff)){
            .prp-kicker{background:#eef2ff}
            .prp-accent{background:var(--prp-accent)}
            .prp-coupon{border-color:#c7d2fe;background:#f8fafc}
            .prp-button{box-shadow:0 8px 18px rgba(37,99,235,.18)}
        }
        @media (prefers-reduced-motion:reduce){.prp-panel{animation:none}.prp-button{transition:none}.prp-button:hover{transform:none}}
        @media (max-width:640px){
            .prp-root[data-mode="modal"]{align-items:flex-end;padding:12px;background:rgba(15,23,42,.34)}
            .prp-root[data-mode="top_bar"],.prp-root[data-mode="bottom_bar"]{left:10px;right:10px}
            .prp-root[data-mode="corner"]{left:10px;right:10px;bottom:10px;width:auto}
            .prp-panel{max-width:none;border-radius:12px}
            .prp-inner,.prp-root[data-mode="corner"] .prp-inner{display:block;padding:22px 18px 18px}
            .prp-media{width:100%;min-width:0;margin-bottom:15px;max-height:190px}
            .prp-content{padding-right:24px}
            .prp-title{font-size:20px}
            .prp-actions{flex-direction:column;align-items:stretch;text-align:center}
            .prp-button{width:100%;max-width:none}
            .prp-secondary{width:100%}
            .prp-coupon{grid-template-columns:1fr;width:100%}
            .prp-copy{width:100%}
        }
    </style>
    <div id="<?php echo peakrackPopupE($rootId); ?>"
        class="prp-root"
        hidden
        lang="<?php echo peakrackPopupE($language === 'zh' ? 'zh-CN' : 'en'); ?>"
        data-id="<?php echo $id; ?>"
        data-mode="<?php echo peakrackPopupE($mode); ?>"
        data-frequency="<?php echo peakrackPopupE($frequency); ?>"
        data-version="<?php echo peakrackPopupE($version); ?>"
        data-lang="<?php echo peakrackPopupE($language); ?>"
        data-delay="<?php echo $delay; ?>"
        data-auto-close="<?php echo $autoClose; ?>"
        data-track-url="<?php echo peakrackPopupE($trackUrl); ?>"
        data-track-token="<?php echo peakrackPopupE($token); ?>"
        data-copy-label="<?php echo peakrackPopupE($copyLabel); ?>"
        data-copied-label="<?php echo peakrackPopupE($copiedLabel); ?>"
        style="--prp-accent: <?php echo peakrackPopupE($accent); ?>;">
        <div class="prp-panel" role="<?php echo $mode === 'modal' ? 'dialog' : 'status'; ?>" aria-modal="<?php echo $mode === 'modal' ? 'true' : 'false'; ?>"<?php echo $labelAttribute; ?>>
            <div class="prp-accent"></div>
            <button type="button" class="prp-close" aria-label="<?php echo peakrackPopupE($closeLabel); ?>">&times;</button>
            <div class="prp-inner <?php echo $imageUrl !== '' ? 'prp-has-media' : 'prp-no-media'; ?>">
                <?php if ($imageUrl !== ''): ?>
                    <div class="prp-media"><img src="<?php echo peakrackPopupE($imageUrl); ?>" alt=""></div>
                <?php endif; ?>
                <div class="prp-content">
                    <div class="prp-heading">
                        <span class="prp-kicker"><?php echo peakrackPopupE($typeLabel); ?></span>
                        <?php if ($title !== ''): ?>
                            <h3 class="prp-title" id="<?php echo peakrackPopupE($rootId); ?>-title"><?php echo peakrackPopupE($title); ?></h3>
                        <?php endif; ?>
                    </div>
                    <?php if ($body !== ''): ?>
                        <p class="prp-body"><?php echo peakrackPopupE($body); ?></p>
                    <?php endif; ?>
                    <?php if ($coupon !== ''): ?>
                        <div class="prp-coupon">
                            <div>
                                <span class="prp-coupon-label"><?php echo peakrackPopupE($couponLabel); ?></span>
                                <span class="prp-coupon-code"><?php echo peakrackPopupE($coupon); ?></span>
                            </div>
                            <button type="button" class="prp-copy" data-copy="<?php echo peakrackPopupE($coupon); ?>"><?php echo peakrackPopupE($copyLabel); ?></button>
                        </div>
                    <?php endif; ?>
                    <div class="prp-actions">
                        <?php if ($buttonLabel !== '' && $buttonUrl !== ''): ?>
                            <a class="prp-button" href="<?php echo peakrackPopupE($buttonUrl); ?>"<?php echo $openNew ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo peakrackPopupE($buttonLabel); ?></a>
                        <?php endif; ?>
                        <button type="button" class="prp-secondary"><?php echo peakrackPopupE($laterLabel); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var root = document.getElementById(<?php echo json_encode($rootId); ?>);
        if (!root || root.dataset.bound === '1') {
            return;
        }
        root.dataset.bound = '1';

        var id = root.getAttribute('data-id') || '';
        var version = root.getAttribute('data-version') || 'v1';
        var language = root.getAttribute('data-lang') || 'en';
        var frequency = root.getAttribute('data-frequency') || 'daily';
        var delay = Math.max(0, parseInt(root.getAttribute('data-delay') || '0', 10));
        var autoClose = Math.max(0, parseInt(root.getAttribute('data-auto-close') || '0', 10));
        var key = 'peakrack_popup_' + id + '_' + language + '_' + version;
        var closeButtons = root.querySelectorAll('.prp-close,.prp-secondary');
        var copyButton = root.querySelector('.prp-copy');
        var primaryButton = root.querySelector('.prp-button');
        var copyLabel = root.getAttribute('data-copy-label') || 'Copy';
        var copiedLabel = root.getAttribute('data-copied-label') || 'Copied';
        var shown = false;

        function today() {
            return new Date().toISOString().slice(0, 10);
        }

        function read(storage, itemKey) {
            try {
                return storage.getItem(itemKey);
            } catch (e) {
                return null;
            }
        }

        function write(storage, itemKey, value) {
            try {
                storage.setItem(itemKey, value);
            } catch (e) {}
        }

        function shouldShow() {
            if (frequency === 'every_page') {
                return true;
            }

            if (frequency === 'session') {
                return read(window.sessionStorage, key) !== '1';
            }

            if (frequency === 'once') {
                return read(window.localStorage, key) !== '1';
            }

            return read(window.localStorage, key) !== today();
        }

        function markSeen() {
            if (frequency === 'session') {
                write(window.sessionStorage, key, '1');
            } else if (frequency === 'once') {
                write(window.localStorage, key, '1');
            } else if (frequency === 'daily') {
                write(window.localStorage, key, today());
            }
        }

        function track(eventName) {
            var url = root.getAttribute('data-track-url') || '';
            var token = root.getAttribute('data-track-token') || '';
            if (!url || !token) {
                return;
            }

            var body = 'id=' + encodeURIComponent(id) + '&event=' + encodeURIComponent(eventName) + '&token=' + encodeURIComponent(token);
            try {
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(url, new Blob([body], {type: 'application/x-www-form-urlencoded'}));
                    return;
                }
            } catch (e) {}

            try {
                window.fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: body,
                    keepalive: true
                });
            } catch (e) {}
        }

        function show() {
            if (shown || !shouldShow()) {
                return;
            }
            shown = true;
            root.hidden = false;
            track('view');

            if (autoClose > 0) {
                window.setTimeout(close, autoClose * 1000);
            }
        }

        function close() {
            if (root.hidden) {
                return;
            }
            markSeen();
            root.hidden = true;
            track('close');
        }

        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].addEventListener('click', close);
        }

        if (primaryButton) {
            primaryButton.addEventListener('click', function () {
                markSeen();
                track('click');
            });
        }

        if (copyButton) {
            copyButton.addEventListener('click', function () {
                var value = copyButton.getAttribute('data-copy') || '';
                var done = function () {
                    copyButton.textContent = copiedLabel;
                    window.setTimeout(function () {
                        copyButton.textContent = copyLabel;
                    }, 1800);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(value).then(done).catch(done);
                    return;
                }

                var input = document.createElement('textarea');
                input.value = value;
                input.style.position = 'fixed';
                input.style.left = '-9999px';
                document.body.appendChild(input);
                input.select();
                try {
                    document.execCommand('copy');
                } catch (e) {}
                document.body.removeChild(input);
                done();
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && root.getAttribute('data-mode') === 'modal') {
                close();
            }
        });

        window.setTimeout(show, delay * 1000);
    })();
    </script>
    <?php
    return trim((string) ob_get_clean());
}
