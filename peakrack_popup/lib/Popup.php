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
    static $ready = false;
    if ($ready) {
        return;
    }

    $schema = Capsule::schema();

    if (!$schema->hasTable('mod_peakrack_popups')) {
        $schema->create('mod_peakrack_popups', static function ($table): void {
            $table->increments('id');
            $table->string('name', 150);
            $table->boolean('enabled')->default(false)->index();
            $table->boolean('archived')->default(false)->index();
            $table->string('type', 40)->default('notice')->index();
            $table->string('content_format', 20)->default('text');
            $table->string('display_mode', 40)->default('modal');
            $table->unsignedInteger('style_id')->default(0)->index();
            $table->string('audience', 40)->default('all')->index();
            $table->text('client_group_ids')->nullable();
            $table->text('page_rules')->nullable();
            $table->text('language_rules')->nullable();
            $table->text('days_of_week')->nullable();
            $table->text('url_contains')->nullable();
            $table->boolean('requires_unpaid_invoice')->default(false)->index();
            $table->text('active_product_ids')->nullable();
            $table->text('active_product_group_ids')->nullable();
            $table->text('active_server_ids')->nullable();
            $table->text('active_addon_ids')->nullable();
            $table->text('active_tlds')->nullable();
            $table->text('missing_product_ids')->nullable();
            $table->text('missing_addon_ids')->nullable();
            $table->text('missing_tlds')->nullable();
            $table->string('frequency', 40)->default('daily');
            $table->string('hide_permanently', 30)->default('disabled');
            $table->string('theme', 40)->default('blue');
            $table->string('accent_color', 20)->nullable();
            $table->string('popup_width', 30)->nullable();
            $table->string('popup_height', 30)->nullable();
            $table->string('animation', 30)->default('fade');
            $table->integer('animation_ms')->default(180);
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
            $table->string('time_start', 5)->nullable();
            $table->string('time_end', 5)->nullable();
            $table->unsignedInteger('display_limit')->default(0);
            $table->unsignedInteger('per_client_display_limit')->default(0);
            $table->string('due_mode', 20)->default('disabled');
            $table->string('due_operator', 10)->default('lte');
            $table->integer('due_days')->default(0);
            $table->text('due_product_ids')->nullable();
            $table->text('due_statuses')->nullable();
            $table->dateTime('start_at')->nullable()->index();
            $table->dateTime('end_at')->nullable()->index();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('close_count')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    } else {
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'archived', static function ($table): void {
            $table->boolean('archived')->default(false)->index();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'content_format', static function ($table): void {
            $table->string('content_format', 20)->default('text');
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'style_id', static function ($table): void {
            $table->unsignedInteger('style_id')->default(0)->index();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'language_rules', static function ($table): void {
            $table->text('language_rules')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'days_of_week', static function ($table): void {
            $table->text('days_of_week')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'url_contains', static function ($table): void {
            $table->text('url_contains')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'requires_unpaid_invoice', static function ($table): void {
            $table->boolean('requires_unpaid_invoice')->default(false)->index();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'active_product_ids', static function ($table): void {
            $table->text('active_product_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'active_product_group_ids', static function ($table): void {
            $table->text('active_product_group_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'active_server_ids', static function ($table): void {
            $table->text('active_server_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'active_addon_ids', static function ($table): void {
            $table->text('active_addon_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'active_tlds', static function ($table): void {
            $table->text('active_tlds')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'missing_product_ids', static function ($table): void {
            $table->text('missing_product_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'missing_addon_ids', static function ($table): void {
            $table->text('missing_addon_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'missing_tlds', static function ($table): void {
            $table->text('missing_tlds')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'hide_permanently', static function ($table): void {
            $table->string('hide_permanently', 30)->default('disabled');
        });
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
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'popup_width', static function ($table): void {
            $table->string('popup_width', 30)->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'popup_height', static function ($table): void {
            $table->string('popup_height', 30)->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'animation', static function ($table): void {
            $table->string('animation', 30)->default('fade');
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'animation_ms', static function ($table): void {
            $table->integer('animation_ms')->default(180);
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'time_start', static function ($table): void {
            $table->string('time_start', 5)->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'time_end', static function ($table): void {
            $table->string('time_end', 5)->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'display_limit', static function ($table): void {
            $table->unsignedInteger('display_limit')->default(0);
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'per_client_display_limit', static function ($table): void {
            $table->unsignedInteger('per_client_display_limit')->default(0);
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'due_mode', static function ($table): void {
            $table->string('due_mode', 20)->default('disabled');
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'due_operator', static function ($table): void {
            $table->string('due_operator', 10)->default('lte');
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'due_days', static function ($table): void {
            $table->integer('due_days')->default(0);
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'due_product_ids', static function ($table): void {
            $table->text('due_product_ids')->nullable();
        });
        peakrackPopupEnsureColumn('mod_peakrack_popups', 'due_statuses', static function ($table): void {
            $table->text('due_statuses')->nullable();
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

    if (!$schema->hasTable('mod_peakrack_popup_styles')) {
        $schema->create('mod_peakrack_popup_styles', static function ($table): void {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('slug', 80)->unique();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true)->index();
            $table->boolean('is_system')->default(false)->index();
            $table->string('display_mode', 40)->default('modal');
            $table->string('theme', 40)->default('blue');
            $table->string('accent_color', 20)->nullable();
            $table->string('popup_width', 30)->nullable();
            $table->string('popup_height', 30)->nullable();
            $table->string('animation', 30)->default('fade');
            $table->integer('animation_ms')->default(180);
            $table->mediumText('custom_css')->nullable();
            $table->mediumText('html_template')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    peakrackPopupSeedDefaultStyles();
    peakrackPopupBackfillDefaultEnglish();
    $ready = true;
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

function peakrackPopupDefaultStyles(): array
{
    return [
        [
            'name' => 'Centered Modal',
            'slug' => 'centered-modal',
            'description' => 'General purpose centered popup with overlay.',
            'display_mode' => 'modal',
            'theme' => 'blue',
            'accent_color' => '#2563eb',
            'popup_width' => '620px',
            'popup_height' => '',
            'animation' => 'fade',
            'animation_ms' => 180,
            'custom_css' => '',
            'html_template' => '',
            'sort_order' => 10,
        ],
        [
            'name' => 'Poster Image',
            'slug' => 'poster-image',
            'description' => 'Image-first poster popup for visual campaigns.',
            'display_mode' => 'poster',
            'theme' => 'blue',
            'accent_color' => '#e11d48',
            'popup_width' => '580px',
            'popup_height' => '',
            'animation' => 'fade',
            'animation_ms' => 200,
            'custom_css' => "{root} .prp-panel{border-radius:8px;overflow:hidden}\n{root} .prp-poster-close{background:#e11d48}",
            'html_template' => '',
            'sort_order' => 20,
        ],
        [
            'name' => 'Top Banner',
            'slug' => 'top-banner',
            'description' => 'Compact top banner for important notices.',
            'display_mode' => 'top_bar',
            'theme' => 'green',
            'accent_color' => '#16a34a',
            'popup_width' => '980px',
            'popup_height' => '',
            'animation' => 'slide_top',
            'animation_ms' => 180,
            'custom_css' => "{root} .prp-panel{border-radius:6px}\n{root} .prp-accent{height:3px}",
            'html_template' => '',
            'sort_order' => 30,
        ],
        [
            'name' => 'Bottom Banner',
            'slug' => 'bottom-banner',
            'description' => 'Bottom banner for low-interruption messages.',
            'display_mode' => 'bottom_bar',
            'theme' => 'slate',
            'accent_color' => '#334155',
            'popup_width' => '980px',
            'popup_height' => '',
            'animation' => 'slide_bottom',
            'animation_ms' => 180,
            'custom_css' => '',
            'html_template' => '',
            'sort_order' => 40,
        ],
        [
            'name' => 'Right Corner',
            'slug' => 'right-corner',
            'description' => 'Floating bottom-right popup.',
            'display_mode' => 'corner_right',
            'theme' => 'orange',
            'accent_color' => '#f97316',
            'popup_width' => '',
            'popup_height' => '',
            'animation' => 'slide_right',
            'animation_ms' => 180,
            'custom_css' => "{root} .prp-panel{border-radius:8px;box-shadow:0 18px 42px rgba(15,23,42,.20)}",
            'html_template' => '',
            'sort_order' => 50,
        ],
        [
            'name' => 'Plain Center',
            'slug' => 'plain-center',
            'description' => 'Centered popup without overlay.',
            'display_mode' => 'modal_plain',
            'theme' => 'blue',
            'accent_color' => '#2563eb',
            'popup_width' => '560px',
            'popup_height' => '',
            'animation' => 'fade',
            'animation_ms' => 160,
            'custom_css' => "{root} .prp-panel{border-radius:6px}",
            'html_template' => '',
            'sort_order' => 60,
        ],
    ];
}

function peakrackPopupSeedDefaultStyles(): void
{
    try {
        if (!Capsule::schema()->hasTable('mod_peakrack_popup_styles') || Capsule::table('mod_peakrack_popup_styles')->count() > 0) {
            return;
        }

        $now = peakrackPopupNow();
        foreach (peakrackPopupDefaultStyles() as $style) {
            Capsule::table('mod_peakrack_popup_styles')->insert(array_merge($style, [
                'enabled' => 1,
                'is_system' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    } catch (\Throwable $e) {
        // Default styles are a convenience and should not block popup rendering.
    }
}

function peakrackPopupStyleById(int $styleId): ?array
{
    if ($styleId <= 0 || !peakrackPopupTableReady('mod_peakrack_popup_styles')) {
        return null;
    }

    try {
        $row = Capsule::table('mod_peakrack_popup_styles')
            ->where('id', $styleId)
            ->where('enabled', 1)
            ->first();
    } catch (\Throwable $e) {
        return null;
    }

    if (!$row) {
        return null;
    }

    return is_array($row) ? $row : get_object_vars($row);
}

function peakrackPopupBackfillDefaultEnglish(): void
{
    try {
        if (!Capsule::schema()->hasColumn('mod_peakrack_popups', 'title_en')) {
            return;
        }

        Capsule::table('mod_peakrack_popups')
            ->whereIn('name', ['Sample promotion popup', 'Sample popup'])
            ->where(static function ($query): void {
                $query->whereNull('title_en')->orWhere('title_en', '');
            })
            ->update([
                'title_en' => 'Sample popup',
                'body_en' => "Use Text, HTML, or Image type content and configure the display rules before enabling this popup.",
                'button_label_en' => 'Open client area',
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
        'name' => 'Sample popup',
        'enabled' => 0,
        'archived' => 0,
        'type' => 'notice',
        'content_format' => 'text',
        'display_mode' => 'modal',
        'style_id' => 1,
        'audience' => 'all',
        'client_group_ids' => '',
        'page_rules' => '*',
        'language_rules' => '',
        'days_of_week' => '',
        'url_contains' => '',
        'requires_unpaid_invoice' => 0,
        'active_product_ids' => '',
        'active_product_group_ids' => '',
        'active_server_ids' => '',
        'active_addon_ids' => '',
        'active_tlds' => '',
        'missing_product_ids' => '',
        'missing_addon_ids' => '',
        'missing_tlds' => '',
        'frequency' => 'daily',
        'hide_permanently' => 'close',
        'theme' => 'blue',
        'accent_color' => '#2563eb',
        'popup_width' => '',
        'popup_height' => '',
        'animation' => 'fade',
        'animation_ms' => 180,
        'title' => '示例弹窗',
        'body' => "这里可以使用 Text、HTML 或 Image 类型内容。\n启用前请先配置展示规则、样式和时间限制。",
        'title_en' => 'Sample popup',
        'body_en' => "Use Text, HTML, or Image type content and configure the display rules before enabling this popup.",
        'coupon_code' => '',
        'button_label' => '打开客户区',
        'button_label_en' => 'Open client area',
        'button_url' => 'clientarea.php',
        'image_url' => '',
        'open_new_tab' => 0,
        'priority' => 10,
        'delay_seconds' => 1,
        'auto_close_seconds' => 0,
        'time_start' => '',
        'time_end' => '',
        'display_limit' => 0,
        'per_client_display_limit' => 0,
        'due_mode' => 'disabled',
        'due_operator' => 'lte',
        'due_days' => 0,
        'due_product_ids' => '',
        'due_statuses' => 'Active',
        'start_at' => null,
        'end_at' => null,
        'view_count' => 0,
        'click_count' => 0,
        'close_count' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

function peakrackPopupTableReady(string $tableName = 'mod_peakrack_popups'): bool
{
    try {
        return Capsule::schema()->hasTable($tableName);
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

function peakrackPopupNormalizeTokenCsv(mixed $value, string $allowedPattern = '/^[a-z0-9_.-]+$/i'): string
{
    $items = [];
    foreach (preg_split('/[,\s]+/', (string) $value) ?: [] as $item) {
        $item = strtolower(trim($item));
        $item = ltrim($item, '.');
        if ($item !== '' && preg_match($allowedPattern, $item) && !in_array($item, $items, true)) {
            $items[] = $item;
        }
    }

    return implode(',', $items);
}

function peakrackPopupCsvItems(mixed $value): array
{
    $items = [];
    foreach (preg_split('/[,\s]+/', (string) $value) ?: [] as $item) {
        $item = trim($item);
        if ($item !== '' && !in_array($item, $items, true)) {
            $items[] = $item;
        }
    }

    return $items;
}

function peakrackPopupIntItems(mixed $value): array
{
    return array_values(array_filter(array_map('intval', peakrackPopupCsvItems($value)), static fn(int $id): bool => $id > 0));
}

function peakrackPopupNormalizeTime(mixed $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $value, $matches)) {
        return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
    }

    return '';
}

function peakrackPopupNormalizeCssSize(mixed $value): string
{
    $value = strtolower(trim((string) $value));
    if ($value === '' || $value === 'auto') {
        return '';
    }

    if (preg_match('/^\d{2,4}px$/', $value) || preg_match('/^\d{1,3}%$/', $value) || preg_match('/^\d{2,4}$/', $value)) {
        return rtrim($value, 'px%') === $value ? $value . 'px' : $value;
    }

    return '';
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

    if (preg_match('#^modules/addons/peakrack_popup/assets/images/[a-z0-9._-]+\.(?:jpe?g|png|gif|webp)$#i', $url)) {
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

function peakrackPopupClientText(string $language, string $key): string
{
    $texts = [
        'zh' => [
            'close' => '关闭',
            'copy' => '复制',
            'copied' => '已复制',
            'popup' => '弹窗',
            'later' => '稍后再说',
            'auto_close' => '{seconds}秒后自动关闭',
            'permanent' => '不再显示这个弹窗',
        ],
        'en' => [
            'close' => 'Close',
            'copy' => 'Copy',
            'copied' => 'Copied',
            'popup' => 'Popup',
            'later' => 'Maybe later',
            'auto_close' => 'Auto-closes in {seconds}s',
            'permanent' => 'Do not show this popup again',
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

function peakrackPopupLanguageMatches(array $popup, array $vars): bool
{
    $allowed = array_map('strtolower', peakrackPopupCsvItems($popup['language_rules'] ?? ''));
    if (!$allowed) {
        return true;
    }

    return in_array(peakrackPopupClientLanguage($vars), $allowed, true);
}

function peakrackPopupDaysMatch(array $popup): bool
{
    $days = peakrackPopupCsvItems($popup['days_of_week'] ?? '');
    if (!$days) {
        return true;
    }

    $today = (string) (int) date('N');
    return in_array($today, $days, true);
}

function peakrackPopupTimeRangeMatches(array $popup): bool
{
    $start = peakrackPopupNormalizeTime($popup['time_start'] ?? '');
    $end = peakrackPopupNormalizeTime($popup['time_end'] ?? '');
    if ($start === '' || $end === '') {
        return true;
    }

    $now = date('H:i');
    if ($start <= $end) {
        return $now >= $start && $now <= $end;
    }

    return $now >= $start || $now <= $end;
}

function peakrackPopupUrlContainsMatches(array $popup): bool
{
    $rules = array_filter(array_map('trim', preg_split('/[\n,]+/', (string) ($popup['url_contains'] ?? '')) ?: []));
    if (!$rules) {
        return true;
    }

    $candidates = peakrackPopupRequestCandidates();
    foreach ($rules as $rule) {
        $needle = strtolower($rule);
        foreach ($candidates as $candidate) {
            if ($needle !== '' && str_contains($candidate, $needle)) {
                return true;
            }
        }
    }

    return false;
}

function peakrackPopupClientHasUnpaidInvoice(int $clientId): bool
{
    if ($clientId <= 0) {
        return false;
    }

    try {
        return Capsule::table('tblinvoices')
            ->where('userid', $clientId)
            ->where('status', 'Unpaid')
            ->count() > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function peakrackPopupClientHasActiveProduct(int $clientId, array $productIds): bool
{
    if ($clientId <= 0 || !$productIds) {
        return false;
    }

    try {
        return Capsule::table('tblhosting')
            ->where('userid', $clientId)
            ->where('domainstatus', 'Active')
            ->whereIn('packageid', $productIds)
            ->count() > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function peakrackPopupClientHasActiveProductGroup(int $clientId, array $groupIds): bool
{
    if ($clientId <= 0 || !$groupIds) {
        return false;
    }

    try {
        return Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.userid', $clientId)
            ->where('tblhosting.domainstatus', 'Active')
            ->whereIn('tblproducts.gid', $groupIds)
            ->count() > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function peakrackPopupClientHasActiveProductServer(int $clientId, array $serverIds): bool
{
    if ($clientId <= 0 || !$serverIds) {
        return false;
    }

    try {
        return Capsule::table('tblhosting')
            ->where('userid', $clientId)
            ->where('domainstatus', 'Active')
            ->whereIn('server', $serverIds)
            ->count() > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function peakrackPopupClientHasActiveAddon(int $clientId, array $addonIds): bool
{
    if ($clientId <= 0 || !$addonIds) {
        return false;
    }

    try {
        return Capsule::table('tblhostingaddons')
            ->where('userid', $clientId)
            ->where('status', 'Active')
            ->whereIn('addonid', $addonIds)
            ->count() > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

function peakrackPopupClientHasActiveTld(int $clientId, array $tlds): bool
{
    if ($clientId <= 0 || !$tlds) {
        return false;
    }

    try {
        $rows = Capsule::table('tbldomains')
            ->where('userid', $clientId)
            ->where('status', 'Active')
            ->get(['domain'])
            ->all();
    } catch (\Throwable $e) {
        return false;
    }

    $normalizedTlds = array_map(static fn(string $tld): string => '.' . ltrim(strtolower($tld), '.'), $tlds);
    foreach ($rows as $row) {
        $domain = strtolower((string) (is_object($row) ? ($row->domain ?? '') : ($row['domain'] ?? '')));
        foreach ($normalizedTlds as $tld) {
            if ($domain !== '' && str_ends_with($domain, $tld)) {
                return true;
            }
        }
    }

    return false;
}

function peakrackPopupDisplayLimitsMatch(array $popup, array $vars): bool
{
    $displayLimit = max(0, (int) ($popup['display_limit'] ?? 0));
    if ($displayLimit > 0 && (int) ($popup['view_count'] ?? 0) >= $displayLimit) {
        return false;
    }

    $perClientLimit = max(0, (int) ($popup['per_client_display_limit'] ?? 0));
    $clientId = peakrackPopupCurrentClientId($vars);
    if ($perClientLimit <= 0 || $clientId <= 0) {
        return true;
    }

    try {
        return Capsule::table('mod_peakrack_popup_events')
            ->where('popup_id', (int) ($popup['id'] ?? 0))
            ->where('client_id', $clientId)
            ->where('event', 'view')
            ->count() < $perClientLimit;
    } catch (\Throwable $e) {
        return true;
    }
}

function peakrackPopupDueDateMatches(array $popup, array $vars): bool
{
    $mode = peakrackPopupValidChoice($popup['due_mode'] ?? 'disabled', ['disabled', 'before', 'on', 'after'], 'disabled');
    if ($mode === 'disabled') {
        return true;
    }

    $clientId = peakrackPopupCurrentClientId($vars);
    if ($clientId <= 0) {
        return false;
    }

    $productIds = peakrackPopupIntItems($popup['due_product_ids'] ?? '');
    $statuses = array_map('strtolower', peakrackPopupCsvItems($popup['due_statuses'] ?? 'Active'));
    $operator = peakrackPopupValidChoice($popup['due_operator'] ?? 'lte', ['lt', 'lte', 'eq', 'gte', 'gt'], 'lte');
    $days = max(0, (int) ($popup['due_days'] ?? 0));
    $today = strtotime(date('Y-m-d')) ?: time();

    try {
        $query = Capsule::table('tblhosting')
            ->where('userid', $clientId)
            ->whereNotNull('nextduedate')
            ->where('nextduedate', '!=', '0000-00-00');

        if ($productIds) {
            $query->whereIn('packageid', $productIds);
        }

        $rows = $query->get(['nextduedate', 'domainstatus'])->all();
    } catch (\Throwable $e) {
        return false;
    }

    foreach ($rows as $row) {
        $status = strtolower((string) (is_object($row) ? ($row->domainstatus ?? '') : ($row['domainstatus'] ?? '')));
        if ($statuses && !in_array($status, $statuses, true)) {
            continue;
        }

        $dueAt = strtotime((string) (is_object($row) ? ($row->nextduedate ?? '') : ($row['nextduedate'] ?? '')));
        if ($dueAt === false) {
            continue;
        }

        $daysUntil = (int) floor(($dueAt - $today) / 86400);
        if ($mode === 'on' && $daysUntil === 0) {
            return true;
        }

        $value = $mode === 'before' ? $daysUntil : -$daysUntil;
        if ($value < 0) {
            continue;
        }

        if (
            ($operator === 'lt' && $value < $days)
            || ($operator === 'lte' && $value <= $days)
            || ($operator === 'eq' && $value === $days)
            || ($operator === 'gte' && $value >= $days)
            || ($operator === 'gt' && $value > $days)
        ) {
            return true;
        }
    }

    return false;
}

function peakrackPopupAdvancedRulesMatch(array $popup, array $vars): bool
{
    $clientId = peakrackPopupCurrentClientId($vars);

    if (!peakrackPopupLanguageMatches($popup, $vars) || !peakrackPopupDaysMatch($popup) || !peakrackPopupTimeRangeMatches($popup) || !peakrackPopupUrlContainsMatches($popup)) {
        return false;
    }

    if (!empty($popup['requires_unpaid_invoice']) && !peakrackPopupClientHasUnpaidInvoice($clientId)) {
        return false;
    }

    $activeProductIds = peakrackPopupIntItems($popup['active_product_ids'] ?? '');
    if ($activeProductIds && !peakrackPopupClientHasActiveProduct($clientId, $activeProductIds)) {
        return false;
    }

    $activeProductGroupIds = peakrackPopupIntItems($popup['active_product_group_ids'] ?? '');
    if ($activeProductGroupIds && !peakrackPopupClientHasActiveProductGroup($clientId, $activeProductGroupIds)) {
        return false;
    }

    $activeServerIds = peakrackPopupIntItems($popup['active_server_ids'] ?? '');
    if ($activeServerIds && !peakrackPopupClientHasActiveProductServer($clientId, $activeServerIds)) {
        return false;
    }

    $activeAddonIds = peakrackPopupIntItems($popup['active_addon_ids'] ?? '');
    if ($activeAddonIds && !peakrackPopupClientHasActiveAddon($clientId, $activeAddonIds)) {
        return false;
    }

    $activeTlds = peakrackPopupCsvItems($popup['active_tlds'] ?? '');
    if ($activeTlds && !peakrackPopupClientHasActiveTld($clientId, $activeTlds)) {
        return false;
    }

    $missingProductIds = peakrackPopupIntItems($popup['missing_product_ids'] ?? '');
    if ($missingProductIds && peakrackPopupClientHasActiveProduct($clientId, $missingProductIds)) {
        return false;
    }

    $missingAddonIds = peakrackPopupIntItems($popup['missing_addon_ids'] ?? '');
    if ($missingAddonIds && peakrackPopupClientHasActiveAddon($clientId, $missingAddonIds)) {
        return false;
    }

    $missingTlds = peakrackPopupCsvItems($popup['missing_tlds'] ?? '');
    if ($missingTlds && peakrackPopupClientHasActiveTld($clientId, $missingTlds)) {
        return false;
    }

    return peakrackPopupDisplayLimitsMatch($popup, $vars) && peakrackPopupDueDateMatches($popup, $vars);
}

function peakrackPopupObjectToArray(object|array $row): array
{
    return is_array($row) ? $row : get_object_vars($row);
}

function peakrackPopupActivePopup(array $vars): ?array
{
    try {
        peakrackPopupCreateTables();
    } catch (\Throwable $e) {
        // Frontend rendering must fail closed if a restrictive DB user cannot run migrations.
    }

    if (!peakrackPopupTableReady()) {
        return null;
    }

    $now = peakrackPopupNow();

    try {
        $rows = Capsule::table('mod_peakrack_popups')
            ->where('enabled', 1)
            ->where('archived', 0)
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
        if (peakrackPopupAudienceMatches($popup, $vars) && peakrackPopupPageMatches($popup) && peakrackPopupAdvancedRulesMatch($popup, $vars)) {
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

function peakrackPopupStyleCss(mixed $css, string $rootId): string
{
    $css = trim((string) $css);
    if ($css === '') {
        return '';
    }

    $root = '#' . preg_replace('/[^A-Za-z0-9_-]/', '', $rootId);
    $css = str_replace('{root}', $root, $css);
    $css = str_ireplace('</style', '/* blocked style close */', $css);

    return "\n" . $css . "\n";
}

function peakrackPopupRenderStyleTemplate(string $template, array $tokens): string
{
    $template = trim($template);
    if ($template === '') {
        return '';
    }

    $hasCloseToken = str_contains($template, '{close}');
    $html = strtr($template, $tokens);
    if (!$hasCloseToken && isset($tokens['{close}'])) {
        $html = $tokens['{close}'] . $html;
    }

    return $html;
}

function peakrackPopupRender(array $popup, array $vars): string
{
    $id = (int) ($popup['id'] ?? 0);
    if ($id <= 0) {
        return '';
    }

    $isPreview = !empty($vars['_peakrack_preview']);
    $language = peakrackPopupClientLanguage($vars);
    $selectedStyle = peakrackPopupStyleById((int) ($popup['style_id'] ?? 0));
    $mode = peakrackPopupValidChoice($selectedStyle['display_mode'] ?? ($popup['display_mode'] ?? 'modal'), ['modal', 'poster', 'modal_plain', 'top_bar', 'bottom_bar', 'corner', 'corner_right', 'corner_left', 'left_side', 'right_side'], 'modal');
    $mode = $mode === 'corner' ? 'corner_right' : $mode;
    $isPoster = $mode === 'poster';
    $contentFormat = peakrackPopupValidChoice($popup['content_format'] ?? 'text', ['text', 'html', 'image'], 'text');
    $frequency = peakrackPopupValidChoice($popup['frequency'] ?? 'daily', ['every_page', 'session', 'daily', 'once'], 'daily');
    $hidePermanently = peakrackPopupValidChoice($popup['hide_permanently'] ?? 'disabled', ['disabled', 'close', 'checkbox'], 'disabled');
    $theme = peakrackPopupValidChoice($selectedStyle['theme'] ?? ($popup['theme'] ?? 'blue'), ['blue', 'green', 'orange', 'red', 'slate'], 'blue');
    $title = peakrackPopupLocalizedValue($popup, 'title', $language);
    $body = peakrackPopupLocalizedValue($popup, 'body', $language);
    $buttonLabel = peakrackPopupLocalizedValue($popup, 'button_label', $language);
    $buttonUrl = peakrackPopupSafeLink($popup['button_url'] ?? '');
    $imageUrl = peakrackPopupSafeImageUrl($popup['image_url'] ?? '');
    $delay = max(0, min(60, (int) ($popup['delay_seconds'] ?? 0)));
    $autoClose = max(0, min(3600, (int) ($popup['auto_close_seconds'] ?? 0)));
    $showButton = $buttonLabel !== '' && $buttonUrl !== '';
    $showHeading = $title !== '';
    $showActions = $showButton || $contentFormat !== 'image';
    $showInnerContent = $showHeading || $body !== '' || $showActions || $autoClose > 0 || $hidePermanently === 'checkbox';
    $animation = peakrackPopupValidChoice($selectedStyle['animation'] ?? ($popup['animation'] ?? 'fade'), ['fade', 'slide_left', 'slide_right', 'slide_top', 'slide_bottom', 'none'], 'fade');
    $animationMs = max(0, min(5000, (int) ($selectedStyle['animation_ms'] ?? ($popup['animation_ms'] ?? 180))));
    $popupWidth = peakrackPopupNormalizeCssSize(($selectedStyle['popup_width'] ?? '') !== '' ? $selectedStyle['popup_width'] : ($popup['popup_width'] ?? ''));
    $popupHeight = peakrackPopupNormalizeCssSize(($selectedStyle['popup_height'] ?? '') !== '' ? $selectedStyle['popup_height'] : ($popup['popup_height'] ?? ''));
    $openNew = peakrackPopupBool($popup['open_new_tab'] ?? 0) === 1;
    $updatedAt = (string) ($popup['updated_at'] ?? '');
    $version = substr(sha1($updatedAt . '|' . $title . '|' . $body . '|' . $buttonLabel . '|' . $contentFormat), 0, 12);
    $token = $isPreview ? '' : peakrackPopupTrackingToken($id);
    $trackUrl = $isPreview ? '' : peakrackPopupTrackUrl($vars);
    $accent = peakrackPopupNormalizeAccentColor($selectedStyle['accent_color'] ?? ($popup['accent_color'] ?? ''), peakrackPopupThemeAccent($theme));
    $rootId = 'peakrack-popup-' . $id . ($isPreview ? '-preview' : '');
    $styleCss = peakrackPopupStyleCss($selectedStyle['custom_css'] ?? '', $rootId);
    $copyLabel = peakrackPopupClientText($language, 'copy');
    $copiedLabel = peakrackPopupClientText($language, 'copied');
    $closeLabel = peakrackPopupClientText($language, 'close');
    $laterLabel = peakrackPopupClientText($language, 'later');
    $autoCloseTemplate = peakrackPopupClientText($language, 'auto_close');
    $autoCloseLabel = str_replace('{seconds}', (string) $autoClose, $autoCloseTemplate);
    $permanentLabel = peakrackPopupClientText($language, 'permanent');
    $rootStyles = ['--prp-accent: ' . $accent, '--prp-animation-ms: ' . ($animationMs / 1000) . 's'];
    if ($animation !== 'none') {
        $rootStyles[] = '--prp-animation-name: prp-' . str_replace('_', '-', $animation);
    } else {
        $rootStyles[] = '--prp-animation-name: none';
    }
    if ($popupWidth !== '') {
        $rootStyles[] = '--prp-width: ' . $popupWidth;
    }
    if ($popupHeight !== '') {
        $rootStyles[] = '--prp-height: ' . $popupHeight;
    }
    $labelAttribute = $title !== ''
        ? ' aria-labelledby="' . peakrackPopupE($rootId) . '-title"'
        : ' aria-label="' . peakrackPopupE(peakrackPopupClientText($language, 'popup')) . '"';
    $closeHtml = '<button type="button" class="prp-close" aria-label="' . peakrackPopupE($closeLabel) . '">&times;</button>';
    $mainImageHtml = $imageUrl !== '' ? '<div class="prp-image-main"><img src="' . peakrackPopupE($imageUrl) . '" alt=""></div>' : '';
    $posterImageHtml = $imageUrl !== '' ? '<div class="prp-poster-media"><img src="' . peakrackPopupE($imageUrl) . '" alt=""></div>' : '';
    $sideImageHtml = $imageUrl !== '' ? '<div class="prp-media"><img src="' . peakrackPopupE($imageUrl) . '" alt=""></div>' : '';
    $titleHtml = $title !== '' ? '<h3 class="prp-title" id="' . peakrackPopupE($rootId) . '-title">' . peakrackPopupE($title) . '</h3>' : '';
    $bodyHtml = '';
    if ($body !== '') {
        $bodyHtml = $contentFormat === 'html'
            ? '<div class="prp-body prp-html">' . $body . '</div>'
            : '<p class="prp-body">' . peakrackPopupE($body) . '</p>';
    }
    $buttonHtml = $showButton
        ? '<a class="prp-button" href="' . peakrackPopupE($buttonUrl) . '"' . ($openNew ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . peakrackPopupE($buttonLabel) . '</a>'
        : '';
    $secondaryCloseHtml = $contentFormat !== 'image' ? '<button type="button" class="prp-secondary">' . peakrackPopupE($laterLabel) . '</button>' : '';
    $imageCloseHtml = $contentFormat === 'image' && !$isPoster ? '<button type="button" class="prp-image-close">' . peakrackPopupE($closeLabel) . '</button>' : '';
    $actionsHtml = $showActions ? '<div class="prp-actions">' . $buttonHtml . $secondaryCloseHtml . '</div>' : '';
    $autoCloseHtml = $autoClose > 0 ? '<p class="prp-autoclose" data-autoclose-message aria-live="polite">' . peakrackPopupE($autoCloseLabel) . '</p>' : '';
    $permanentHtml = $hidePermanently === 'checkbox' ? '<label class="prp-permanent"><input type="checkbox" data-permanent-checkbox value="1"> ' . peakrackPopupE($permanentLabel) . '</label>' : '';
    $headingHtml = $showHeading ? '<div class="prp-heading">' . $titleHtml . '</div>' : '';
    $contentHtml = '<div class="prp-content">' . $headingHtml . $bodyHtml . $actionsHtml . $autoCloseHtml . $permanentHtml . '</div>';
    $styleTemplateHtml = peakrackPopupRenderStyleTemplate((string) ($selectedStyle['html_template'] ?? ''), [
        '{close}' => $closeHtml,
        '{image}' => $isPoster ? $posterImageHtml : $mainImageHtml,
        '{main_image}' => $mainImageHtml,
        '{poster_image}' => $posterImageHtml,
        '{side_image}' => $sideImageHtml,
        '{title}' => $titleHtml,
        '{body}' => $bodyHtml,
        '{button}' => $buttonHtml,
        '{secondary_close}' => $secondaryCloseHtml,
        '{actions}' => $actionsHtml,
        '{auto_close}' => $autoCloseHtml,
        '{permanent_close}' => $permanentHtml,
        '{image_close}' => $imageCloseHtml,
        '{content}' => $contentHtml,
        '{accent}' => '<div class="prp-accent"></div>',
    ]);

    ob_start();
    ?>
    <style>
        .prp-root[hidden]{display:none!important}
        .prp-root{position:fixed;z-index:2147483000;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#172033}
        .prp-root *{box-sizing:border-box}
        .prp-root[data-mode="modal"]{inset:0;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.42);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px)}
        .prp-root[data-mode="poster"]{inset:0;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.48);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
        .prp-root[data-mode="modal_plain"]{inset:0;display:flex;align-items:center;justify-content:center;padding:24px;pointer-events:none}
        .prp-root[data-mode="top_bar"]{top:16px;left:16px;right:16px;display:flex;justify-content:center;pointer-events:none}
        .prp-root[data-mode="bottom_bar"]{left:16px;right:16px;bottom:16px;display:flex;justify-content:center;pointer-events:none}
        .prp-root[data-mode="corner_right"]{right:20px;bottom:20px;width:min(390px,calc(100vw - 32px));pointer-events:none}
        .prp-root[data-mode="corner_left"]{left:20px;bottom:20px;width:min(390px,calc(100vw - 32px));pointer-events:none}
        .prp-root[data-mode="left_side"]{left:20px;top:50%;width:min(390px,calc(100vw - 32px));transform:translateY(-50%);pointer-events:none}
        .prp-root[data-mode="right_side"]{right:20px;top:50%;width:min(390px,calc(100vw - 32px));transform:translateY(-50%);pointer-events:none}
        .prp-panel{position:relative;width:100%;max-width:var(--prp-width,620px);height:var(--prp-height,auto);max-height:calc(100vh - 48px);overflow:auto;border:1px solid rgba(148,163,184,.22);border-radius:12px;background:#fff;box-shadow:0 22px 60px rgba(15,23,42,.18),0 2px 8px rgba(15,23,42,.06);pointer-events:auto;animation:var(--prp-animation-name,prp-fade) var(--prp-animation-ms,.18s) ease-out}
        .prp-root[data-mode="poster"] .prp-panel{max-width:var(--prp-width,580px);border-radius:8px;box-shadow:0 26px 70px rgba(15,23,42,.26)}
        .prp-root[data-mode="top_bar"] .prp-panel,.prp-root[data-mode="bottom_bar"] .prp-panel{max-width:980px;border-radius:10px}
        .prp-root[data-mode="corner_right"] .prp-panel,.prp-root[data-mode="corner_left"] .prp-panel,.prp-root[data-mode="left_side"] .prp-panel,.prp-root[data-mode="right_side"] .prp-panel{max-width:none}
        .prp-accent{height:4px;background:linear-gradient(90deg,var(--prp-accent),color-mix(in srgb,var(--prp-accent) 24%,#fff))}
        .prp-root[data-mode="poster"] .prp-accent{display:none}
        .prp-close{position:absolute;z-index:5;top:12px;right:12px;display:flex;align-items:center;justify-content:center;width:34px;height:34px;border:1px solid transparent;border-radius:999px;background:rgba(255,255,255,.94);color:#475467;font-size:22px;line-height:1;cursor:pointer;box-shadow:0 8px 18px rgba(15,23,42,.16);transition:background .16s ease,color .16s ease,border-color .16s ease,transform .16s ease}
        .prp-close:hover{background:#f8fafc;border-color:#e2e8f0;color:#0f172a}
        .prp-inner{display:block;padding:28px}
        .prp-inner.prp-has-media{display:grid;grid-template-columns:178px minmax(0,1fr);gap:24px}
        .prp-media{width:178px;min-width:178px;aspect-ratio:1.18;border-radius:10px;overflow:hidden;background:#f4f7fb}
        .prp-media img{width:100%;height:100%;object-fit:cover;display:block}
        .prp-image-main{position:relative;display:flex;align-items:center;justify-content:center;background:#fff;overflow:hidden;line-height:0}
        .prp-image-main img{display:block;width:auto;max-width:100%;height:auto;max-height:calc(100vh - 116px);object-fit:contain;background:transparent}
        .prp-poster-media{position:relative;display:flex;align-items:center;justify-content:center;min-height:300px;background:#fff;overflow:hidden;line-height:0}
        .prp-poster-media img{display:block;width:auto;max-width:100%;height:auto;min-height:0;max-height:410px;object-fit:contain;background:transparent}
        .prp-poster-media::after{content:"";position:absolute;left:-8%;right:-8%;bottom:-44px;height:96px;border-radius:50% 50% 0 0;background:#fff}
        .prp-content{min-width:0;padding-right:34px}
        .prp-heading{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin:0 0 12px}
        .prp-title{margin:0;font-size:22px;line-height:1.28;font-weight:750;color:#111827;letter-spacing:0}
        .prp-body{margin:0;color:#4b5563;font-size:14px;line-height:1.7;white-space:pre-line;overflow-wrap:anywhere}
        .prp-body.prp-html{white-space:normal}
        .prp-copy{border:0;border-radius:8px;background:var(--prp-accent);color:#fff;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap}
        .prp-copy:hover{filter:brightness(.96)}
        .prp-actions{display:flex;align-items:center;justify-content:flex-start;gap:12px;margin-top:20px;text-align:left}
        .prp-button{display:inline-flex;align-items:center;justify-content:center;min-width:128px;min-height:40px;border-radius:8px;background:var(--prp-accent);color:#fff!important;padding:9px 18px;text-decoration:none!important;font-size:14px;font-weight:750;box-shadow:0 10px 20px color-mix(in srgb,var(--prp-accent) 20%,transparent);transition:filter .16s ease,transform .16s ease}
        .prp-button:hover{filter:brightness(.96);color:#fff!important;transform:translateY(-1px)}
        .prp-secondary{border:0;background:transparent;color:#667085;font-size:13px;cursor:pointer;padding:8px 2px}
        .prp-secondary:hover{color:#111827}
        .prp-image-close{display:block;width:100%;min-height:46px;border:0;border-radius:0;background:var(--prp-accent);color:#fff;font-size:15px;font-weight:750;cursor:pointer}
        .prp-image-close:hover{filter:brightness(.96)}
        .prp-poster-close{display:block;width:100%;min-height:46px;border:0;border-radius:0;background:var(--prp-accent);color:#fff;font-size:15px;font-weight:750;cursor:pointer}
        .prp-poster-close:hover{filter:brightness(.96)}
        .prp-permanent{display:flex;align-items:center;gap:7px;margin-top:12px;color:#667085;font-size:12px;line-height:1.45}
        .prp-permanent input{margin:0}
        .prp-autoclose{display:flex;align-items:center;gap:7px;margin:12px 0 0;color:#667085;font-size:12px;line-height:1.45}
        .prp-autoclose::before{content:"";width:6px;height:6px;border-radius:999px;background:var(--prp-accent);opacity:.72;flex:0 0 auto}
        .prp-root[data-mode="top_bar"] .prp-inner,.prp-root[data-mode="bottom_bar"] .prp-inner{grid-template-columns:minmax(0,1fr) auto;align-items:center;padding:15px 18px}
        .prp-root[data-mode="top_bar"] .prp-media,.prp-root[data-mode="bottom_bar"] .prp-media{display:none}
        .prp-root[data-mode="top_bar"] .prp-heading,.prp-root[data-mode="bottom_bar"] .prp-heading{gap:6px;margin-bottom:4px}
        .prp-root[data-mode="top_bar"] .prp-title,.prp-root[data-mode="bottom_bar"] .prp-title{font-size:17px}
        .prp-root[data-mode="top_bar"] .prp-body,.prp-root[data-mode="bottom_bar"] .prp-body{line-height:1.5}
        .prp-root[data-mode="top_bar"] .prp-actions,.prp-root[data-mode="bottom_bar"] .prp-actions{margin-top:0;flex-direction:row;flex-shrink:0}
        .prp-root[data-mode="top_bar"] .prp-autoclose,.prp-root[data-mode="bottom_bar"] .prp-autoclose{margin-top:8px}
        .prp-root[data-mode="top_bar"] .prp-content,.prp-root[data-mode="bottom_bar"] .prp-content{padding-right:38px}
        .prp-root[data-mode="corner_right"] .prp-inner,.prp-root[data-mode="corner_left"] .prp-inner,.prp-root[data-mode="left_side"] .prp-inner,.prp-root[data-mode="right_side"] .prp-inner{padding:24px}
        .prp-root[data-mode="corner_right"] .prp-title,.prp-root[data-mode="corner_left"] .prp-title,.prp-root[data-mode="left_side"] .prp-title,.prp-root[data-mode="right_side"] .prp-title{font-size:19px}
        .prp-root[data-mode="poster"] .prp-inner{display:block;padding:0}
        .prp-root[data-mode="poster"] .prp-content{padding:30px 34px 26px}
        .prp-root[data-mode="poster"] .prp-heading{margin-bottom:14px}
        .prp-root[data-mode="poster"] .prp-title{font-size:21px;line-height:1.32;text-transform:uppercase}
        .prp-root[data-mode="poster"] .prp-body{font-size:14px;color:#293241}
        .prp-root[data-mode="poster"] .prp-close{top:10px;right:10px;background:rgba(17,24,39,.72);border-color:rgba(255,255,255,.45);color:#fff}
        .prp-root[data-mode="poster"][data-has-inner-content="0"] .prp-poster-media::after{display:none}
        .prp-root[data-content-format="image"] .prp-panel{width:100%;max-width:min(var(--prp-width,960px),calc(100vw - 48px));overflow:hidden;background:#fff}
        .prp-root[data-content-format="image"] .prp-close{background:rgba(17,24,39,.72);border-color:rgba(255,255,255,.45);color:#fff}
        .prp-root[data-content-format="image"] .prp-close:hover{background:rgba(17,24,39,.88);border-color:rgba(255,255,255,.72);color:#fff}
        .prp-root[data-content-format="image"] .prp-inner{padding:18px 24px 20px}
        .prp-root[data-content-format="image"] .prp-heading{margin-bottom:10px}
        .prp-root[data-content-format="image"] .prp-actions{margin-top:14px}
        @keyframes prp-fade{from{opacity:0;transform:translateY(8px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
        @keyframes prp-slide-left{from{opacity:0;transform:translateX(-24px)}to{opacity:1;transform:translateX(0)}}
        @keyframes prp-slide-right{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:translateX(0)}}
        @keyframes prp-slide-top{from{opacity:0;transform:translateY(-24px)}to{opacity:1;transform:translateY(0)}}
        @keyframes prp-slide-bottom{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        @supports not (background:color-mix(in srgb,#000 10%,#fff)){
            .prp-accent{background:var(--prp-accent)}
            .prp-button{box-shadow:0 8px 18px rgba(37,99,235,.18)}
        }
        @media (prefers-reduced-motion:reduce){.prp-panel{animation:none}.prp-button{transition:none}.prp-button:hover{transform:none}}
        @media (max-width:640px){
            .prp-root[data-mode="modal"],.prp-root[data-mode="poster"],.prp-root[data-mode="modal_plain"]{align-items:flex-end;padding:12px;background:rgba(15,23,42,.34)}
            .prp-root[data-mode="top_bar"],.prp-root[data-mode="bottom_bar"]{left:10px;right:10px}
            .prp-root[data-mode="corner_right"],.prp-root[data-mode="corner_left"],.prp-root[data-mode="left_side"],.prp-root[data-mode="right_side"]{left:10px;right:10px;bottom:10px;top:auto;width:auto;transform:none}
            .prp-panel{max-width:none;border-radius:12px}
            .prp-inner,.prp-root[data-mode="corner_right"] .prp-inner,.prp-root[data-mode="corner_left"] .prp-inner,.prp-root[data-mode="left_side"] .prp-inner,.prp-root[data-mode="right_side"] .prp-inner{display:block;padding:22px 18px 18px}
            .prp-media{width:100%;min-width:0;margin-bottom:15px;max-height:190px}
            .prp-root[data-content-format="image"] .prp-panel{width:100%;max-width:none}
            .prp-image-main img{max-height:calc(100vh - 132px)}
            .prp-poster-media{min-height:220px;max-height:260px}
            .prp-poster-media img{max-height:260px}
            .prp-root[data-mode="poster"] .prp-content{padding:24px 20px 20px}
            .prp-content{padding-right:24px}
            .prp-title{font-size:20px}
            .prp-actions{flex-direction:column;align-items:stretch;text-align:center}
            .prp-button{width:100%;max-width:none}
            .prp-secondary{width:100%}
            .prp-copy{width:100%}
        }
        <?php echo $styleCss; ?>
    </style>
    <div id="<?php echo peakrackPopupE($rootId); ?>"
        class="prp-root"
        hidden
        lang="<?php echo peakrackPopupE($language === 'zh' ? 'zh-CN' : 'en'); ?>"
        data-id="<?php echo $id; ?>"
        data-mode="<?php echo peakrackPopupE($mode); ?>"
        data-content-format="<?php echo peakrackPopupE($contentFormat); ?>"
        data-frequency="<?php echo peakrackPopupE($frequency); ?>"
        data-hide-permanently="<?php echo peakrackPopupE($hidePermanently); ?>"
        data-has-inner-content="<?php echo $showInnerContent ? '1' : '0'; ?>"
        data-version="<?php echo peakrackPopupE($version); ?>"
        data-lang="<?php echo peakrackPopupE($language); ?>"
        data-delay="<?php echo $delay; ?>"
        data-auto-close="<?php echo $autoClose; ?>"
        data-auto-close-template="<?php echo peakrackPopupE($autoCloseTemplate); ?>"
        data-track-url="<?php echo peakrackPopupE($trackUrl); ?>"
        data-track-token="<?php echo peakrackPopupE($token); ?>"
        data-copy-label="<?php echo peakrackPopupE($copyLabel); ?>"
        data-copied-label="<?php echo peakrackPopupE($copiedLabel); ?>"
        style="<?php echo peakrackPopupE(implode('; ', $rootStyles)); ?>;">
        <div class="prp-panel" role="<?php echo in_array($mode, ['modal', 'poster', 'modal_plain'], true) ? 'dialog' : 'status'; ?>" aria-modal="<?php echo in_array($mode, ['modal', 'poster'], true) ? 'true' : 'false'; ?>"<?php echo $labelAttribute; ?>>
            <?php if ($styleTemplateHtml !== ''): ?>
                <?php echo $styleTemplateHtml; ?>
            <?php else: ?>
            <div class="prp-accent"></div>
            <button type="button" class="prp-close" aria-label="<?php echo peakrackPopupE($closeLabel); ?>">&times;</button>
            <?php if ($isPoster && $imageUrl !== ''): ?>
                <div class="prp-poster-media"><img src="<?php echo peakrackPopupE($imageUrl); ?>" alt=""></div>
            <?php elseif ($contentFormat === 'image' && $imageUrl !== ''): ?>
                <div class="prp-image-main"><img src="<?php echo peakrackPopupE($imageUrl); ?>" alt=""></div>
            <?php endif; ?>
            <?php if ($showInnerContent): ?>
            <div class="prp-inner <?php echo $imageUrl !== '' && !$isPoster && $contentFormat !== 'image' ? 'prp-has-media' : 'prp-no-media'; ?>">
                <?php if ($imageUrl !== '' && !$isPoster && $contentFormat !== 'image'): ?>
                    <div class="prp-media"><img src="<?php echo peakrackPopupE($imageUrl); ?>" alt=""></div>
                <?php endif; ?>
                <div class="prp-content">
                    <?php if ($showHeading): ?>
                    <div class="prp-heading">
                        <?php if ($title !== ''): ?>
                            <h3 class="prp-title" id="<?php echo peakrackPopupE($rootId); ?>-title"><?php echo peakrackPopupE($title); ?></h3>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($body !== ''): ?>
                        <?php if ($contentFormat === 'html'): ?>
                            <div class="prp-body prp-html"><?php echo $body; ?></div>
                        <?php else: ?>
                            <p class="prp-body"><?php echo peakrackPopupE($body); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($showActions): ?>
                    <div class="prp-actions">
                        <?php if ($showButton): ?>
                            <a class="prp-button" href="<?php echo peakrackPopupE($buttonUrl); ?>"<?php echo $openNew ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo peakrackPopupE($buttonLabel); ?></a>
                        <?php endif; ?>
                        <?php if ($contentFormat !== 'image'): ?>
                            <button type="button" class="prp-secondary"><?php echo peakrackPopupE($laterLabel); ?></button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($autoClose > 0): ?>
                        <p class="prp-autoclose" data-autoclose-message aria-live="polite"><?php echo peakrackPopupE($autoCloseLabel); ?></p>
                    <?php endif; ?>
                    <?php if ($hidePermanently === 'checkbox'): ?>
                        <label class="prp-permanent"><input type="checkbox" data-permanent-checkbox value="1"> <?php echo peakrackPopupE($permanentLabel); ?></label>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($contentFormat === 'image' && !$isPoster): ?>
                <button type="button" class="prp-image-close"><?php echo peakrackPopupE($closeLabel); ?></button>
            <?php endif; ?>
            <?php if ($isPoster): ?>
                <button type="button" class="prp-poster-close"><?php echo peakrackPopupE($closeLabel); ?></button>
            <?php endif; ?>
            <?php endif; ?>
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
        var permanentMode = root.getAttribute('data-hide-permanently') || 'disabled';
        var delay = Math.max(0, parseInt(root.getAttribute('data-delay') || '0', 10));
        var autoClose = Math.max(0, parseInt(root.getAttribute('data-auto-close') || '0', 10));
        var key = 'peakrack_popup_' + id + '_' + language + '_' + version;
        var permanentKey = 'peakrack_popup_permanent_' + id;
        var closeButtons = root.querySelectorAll('.prp-close,.prp-secondary,.prp-image-close,.prp-poster-close');
        var copyButton = root.querySelector('.prp-copy');
        var primaryButton = root.querySelector('.prp-button');
        var permanentCheckbox = root.querySelector('[data-permanent-checkbox]');
        var autoCloseMessage = root.querySelector('[data-autoclose-message]');
        var copyLabel = root.getAttribute('data-copy-label') || 'Copy';
        var copiedLabel = root.getAttribute('data-copied-label') || 'Copied';
        var autoCloseTemplate = root.getAttribute('data-auto-close-template') || '{seconds}s';
        var autoCloseTimer = null;
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
            if (permanentMode !== 'disabled' && read(window.localStorage, permanentKey) === '1') {
                return false;
            }

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

        function markPermanentIfNeeded() {
            if (permanentMode === 'close' || (permanentMode === 'checkbox' && permanentCheckbox && permanentCheckbox.checked)) {
                write(window.localStorage, permanentKey, '1');
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

        function updateAutoCloseMessage(seconds) {
            if (!autoCloseMessage) {
                return;
            }
            autoCloseMessage.textContent = autoCloseTemplate.replace('{seconds}', String(Math.max(0, seconds)));
        }

        function stopAutoCloseTimer() {
            if (autoCloseTimer !== null) {
                window.clearInterval(autoCloseTimer);
                autoCloseTimer = null;
            }
        }

        function startAutoCloseTimer() {
            if (autoClose <= 0) {
                return;
            }

            var remaining = autoClose;
            updateAutoCloseMessage(remaining);
            autoCloseTimer = window.setInterval(function () {
                remaining -= 1;
                if (remaining <= 0) {
                    stopAutoCloseTimer();
                    close();
                    return;
                }
                updateAutoCloseMessage(remaining);
            }, 1000);
        }

        function show() {
            if (shown || !shouldShow()) {
                return;
            }
            shown = true;
            root.hidden = false;
            track('view');

            startAutoCloseTimer();
        }

        function close() {
            if (root.hidden) {
                return;
            }
            stopAutoCloseTimer();
            markPermanentIfNeeded();
            markSeen();
            root.hidden = true;
            track('close');
        }

        function isModalMode() {
            var mode = root.getAttribute('data-mode') || '';
            return mode === 'modal' || mode === 'poster' || mode === 'modal_plain';
        }

        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].addEventListener('click', close);
        }

        root.addEventListener('click', function (event) {
            if (event.target === root && isModalMode()) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!root.hidden && event.key === 'Escape') {
                close();
            }
        });

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

        window.setTimeout(show, delay * 1000);
    })();
    </script>
    <?php
    return trim((string) ob_get_clean());
}
