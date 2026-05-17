<?php

/**
 * PeakRack Popup addon module for WHMCS.
 *
 * Target runtime: WHMCS 9.x / PHP 8.3.
 */

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('No direct access');
}

require_once __DIR__ . '/lib/Popup.php';

function peakrack_popup_config(): array
{
    return [
        'name' => 'PeakRack Popup',
        'description' => 'Manage promotional, maintenance, emergency, domain, coupon, and group-buy popups for the WHMCS client area.',
        'version' => '1.2.2',
        'author' => 'PeakRack',
        'language' => 'english',
        'fields' => [],
    ];
}

function peakrack_popup_activate(): array
{
    try {
        peakrackPopupCreateTables();
        peakrackPopupSeedDefaultIfEmpty();

        return [
            'status' => 'success',
            'description' => 'PeakRack Popup has been activated. A disabled bilingual sample popup was created for editing.',
        ];
    } catch (\Throwable $e) {
        return [
            'status' => 'error',
            'description' => 'Activation failed: ' . $e->getMessage(),
        ];
    }
}

function peakrack_popup_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'PeakRack Popup has been deactivated. Popup data and statistics were kept.',
    ];
}

function peakrack_popup_upgrade($vars): void
{
    peakrackPopupCreateTables();
}

function peakrack_popup_output(array $vars): void
{
    $language = peakrack_popup_admin_language();
    $message = '';
    $messageType = 'success';

    try {
        peakrackPopupCreateTables();
    } catch (\Throwable $e) {
        echo '<div class="alert alert-danger">Database setup failed: ' . peakrackPopupE($e->getMessage()) . '</div>';
        return;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!peakrack_popup_verify_admin_token()) {
            $message = peakrack_popup_t($language, 'token_failed');
            $messageType = 'danger';
        } else {
            [$message, $messageType] = peakrack_popup_handle_post($language);
        }
    }

    $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    $editing = $editId > 0 ? peakrack_popup_find($editId) : peakrack_popup_default_form();
    if (!$editing) {
        $editing = peakrack_popup_default_form();
        $message = peakrack_popup_t($language, 'not_found');
        $messageType = 'warning';
    }

    $popups = peakrack_popup_all_rows();
    echo peakrack_popup_render_admin($editing, $popups, $message, $messageType, $language);
}

function peakrack_popup_admin_language(): string
{
    $requested = $_POST['prp_admin_lang'] ?? $_GET['prp_admin_lang'] ?? null;
    $language = peakrackPopupNormalizeLanguage($requested);

    if ($language !== '') {
        $_SESSION['peakrack_popup_admin_lang'] = $language;
        return $language;
    }

    $sessionLanguage = peakrackPopupNormalizeLanguage($_SESSION['peakrack_popup_admin_lang'] ?? null);
    if ($sessionLanguage !== '') {
        return $sessionLanguage;
    }

    $browserLanguage = peakrackPopupNormalizeLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null);
    return $browserLanguage !== '' ? $browserLanguage : 'en';
}

function peakrack_popup_t(string $language, string $key): string
{
    $texts = [
        'zh' => [
            'title' => 'PeakRack 弹窗管理',
            'subtitle' => '用于优惠套餐、优惠码、域名上新、线路维护、紧急公告、拼团优惠等客户区弹窗。',
            'new_popup' => '新增弹窗',
            'stats_total' => '弹窗总数',
            'stats_enabled' => '已启用',
            'stats_views' => '展示',
            'stats_clicks' => '点击',
            'edit_popup' => '编辑弹窗',
            'create_popup' => '新增弹窗',
            'enabled' => '启用',
            'campaign_settings' => '活动设置',
            'admin_name' => '后台名称',
            'priority' => '优先级',
            'priority_help' => '同一页面命中多个弹窗时，数值越大越优先。',
            'type' => '类型',
            'style' => '样式',
            'theme' => '主题色',
            'accent_color' => '弹窗颜色',
            'accent_color_help' => '控制顶部色条、类型标签、优惠码边框和按钮颜色。',
            'frequency' => '展示频率',
            'popup_content' => '弹窗内容',
            'content_help' => '中文客户看中文内容，英文客户看 English 内容。英文内容为空时会回退到中文内容。',
            'zh_content' => '中文内容',
            'en_content' => 'English Content',
            'title_field' => '标题',
            'body_field' => '正文',
            'button_text' => '按钮文字',
            'shared_assets' => '共享素材和跳转',
            'coupon_code' => '优惠码',
            'image_url' => '图片 URL',
            'button_url' => '按钮链接',
            'new_tab' => '按钮新窗口打开',
            'rules' => '展示规则',
            'audience' => '受众',
            'client_group_ids' => '客户组 ID',
            'client_group_help' => '仅受众选择“指定客户组”时生效。',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'delay_seconds' => '延迟显示秒数',
            'auto_close_seconds' => '自动关闭秒数',
            'auto_close_help' => '0 表示不自动关闭。',
            'page_rules' => '页面规则',
            'page_rules_help' => '每行一个：*、cart.php、clientarea.php?action=*。',
            'save' => '保存弹窗',
            'clear' => '清空表单',
            'language_behavior' => '语言规则',
            'language_behavior_body' => '后台语言按钮只影响管理界面。前台弹窗会读取客户区当前语言；WHMCS 为 English 时使用英文标题、正文和按钮。',
            'usage' => '使用建议',
            'usage_body' => '紧急公告建议用顶部横幅并设置高优先级；优惠码活动建议用居中弹窗或右下角浮窗；维护通知可以限制在 networkissues.php、clientarea.php 或购物车页。',
            'usage_body_2' => '前台只会展示命中的最高优先级弹窗，避免多个弹窗同时打扰客户。',
            'list' => '弹窗列表',
            'name' => '名称',
            'status' => '状态',
            'type_style' => '类型/样式',
            'rule_column' => '规则',
            'stats' => '统计',
            'actions' => '操作',
            'empty' => '暂无弹窗。',
            'disabled' => '停用',
            'audience_label' => '受众',
            'page_label' => '页面',
            'edit' => '编辑',
            'disable' => '停用',
            'enable' => '启用',
            'reset' => '清零',
            'delete' => '删除',
            'delete_confirm' => '确定删除这个弹窗吗？',
            'saved' => '弹窗已保存。',
            'created' => '新弹窗已创建。',
            'toggled' => '弹窗状态已更新。',
            'deleted' => '弹窗已删除。',
            'stats_reset' => '统计数据已重置。',
            'missing_toggle' => '未找到要切换的弹窗。',
            'no_action' => '没有可执行的操作。',
            'operation_failed' => '操作失败：',
            'token_failed' => '安全令牌验证失败，请刷新页面后重试。',
            'not_found' => '未找到要编辑的弹窗，已切换到新增模式。',
        ],
        'en' => [
            'title' => 'PeakRack Popup Manager',
            'subtitle' => 'Manage client-area popups for promotions, coupons, domain launches, maintenance, urgent notices, and group-buy campaigns.',
            'new_popup' => 'New Popup',
            'stats_total' => 'Total',
            'stats_enabled' => 'Enabled',
            'stats_views' => 'Views',
            'stats_clicks' => 'Clicks',
            'edit_popup' => 'Edit Popup',
            'create_popup' => 'Create Popup',
            'enabled' => 'Enabled',
            'campaign_settings' => 'Campaign Settings',
            'admin_name' => 'Internal Name',
            'priority' => 'Priority',
            'priority_help' => 'When multiple popups match a page, the highest priority wins.',
            'type' => 'Type',
            'style' => 'Display Mode',
            'theme' => 'Theme',
            'accent_color' => 'Popup Color',
            'accent_color_help' => 'Controls the top accent bar, type label, coupon border, and button color.',
            'frequency' => 'Frequency',
            'popup_content' => 'Popup Content',
            'content_help' => 'Chinese clients see Chinese content; English clients see English content. Empty English fields fall back to Chinese.',
            'zh_content' => 'Chinese Content',
            'en_content' => 'English Content',
            'title_field' => 'Title',
            'body_field' => 'Body',
            'button_text' => 'Button Text',
            'shared_assets' => 'Shared Assets and Link',
            'coupon_code' => 'Coupon Code',
            'image_url' => 'Image URL',
            'button_url' => 'Button URL',
            'new_tab' => 'Open button in a new tab',
            'rules' => 'Display Rules',
            'audience' => 'Audience',
            'client_group_ids' => 'Client Group IDs',
            'client_group_help' => 'Only used when audience is set to specific client groups.',
            'start_at' => 'Start Time',
            'end_at' => 'End Time',
            'delay_seconds' => 'Delay Seconds',
            'auto_close_seconds' => 'Auto-close Seconds',
            'auto_close_help' => 'Use 0 to keep the popup open until the visitor closes it.',
            'page_rules' => 'Page Rules',
            'page_rules_help' => 'One rule per line: *, cart.php, clientarea.php?action=*.',
            'save' => 'Save Popup',
            'clear' => 'Clear Form',
            'language_behavior' => 'Language Behavior',
            'language_behavior_body' => 'The admin language switch only affects this management UI. The client popup follows the current WHMCS client-area language; English uses the English title, body, and button text.',
            'usage' => 'Usage Notes',
            'usage_body' => 'Use a top banner with high priority for urgent notices; use a modal or corner popup for coupon campaigns; limit maintenance notices to networkissues.php, clientarea.php, or cart pages.',
            'usage_body_2' => 'Only the highest-priority matching popup is shown on the frontend, so visitors are not hit by multiple popups at once.',
            'list' => 'Popup List',
            'name' => 'Name',
            'status' => 'Status',
            'type_style' => 'Type/Mode',
            'rule_column' => 'Rules',
            'stats' => 'Stats',
            'actions' => 'Actions',
            'empty' => 'No popups yet.',
            'disabled' => 'Disabled',
            'audience_label' => 'Audience',
            'page_label' => 'Pages',
            'edit' => 'Edit',
            'disable' => 'Disable',
            'enable' => 'Enable',
            'reset' => 'Reset',
            'delete' => 'Delete',
            'delete_confirm' => 'Delete this popup?',
            'saved' => 'Popup saved.',
            'created' => 'New popup created.',
            'toggled' => 'Popup status updated.',
            'deleted' => 'Popup deleted.',
            'stats_reset' => 'Statistics reset.',
            'missing_toggle' => 'Popup not found.',
            'no_action' => 'No action was performed.',
            'operation_failed' => 'Operation failed: ',
            'token_failed' => 'Security token validation failed. Refresh the page and try again.',
            'not_found' => 'The popup was not found. Switched to create mode.',
        ],
    ];

    return $texts[$language][$key] ?? $texts['en'][$key] ?? $key;
}

function peakrack_popup_options(string $language, string $group): array
{
    $options = [
        'zh' => [
            'type' => [
                'promotion' => '优惠套餐',
                'coupon' => '优惠码',
                'domain' => '域名优惠',
                'maintenance' => '线路维护',
                'urgent' => '紧急公告',
                'group_buy' => '拼团优惠',
                'notice' => '普通通知',
            ],
            'display_mode' => [
                'modal' => '居中弹窗',
                'top_bar' => '顶部横幅',
                'bottom_bar' => '底部横幅',
                'corner' => '右下角浮窗',
            ],
            'theme' => [
                'blue' => '蓝色',
                'green' => '绿色',
                'orange' => '橙色',
                'red' => '红色',
                'slate' => '深灰',
            ],
            'frequency' => [
                'every_page' => '每次访问都显示',
                'session' => '每个会话一次',
                'daily' => '每天一次',
                'once' => '每个浏览器一次',
            ],
            'audience' => [
                'all' => '所有访客',
                'guests' => '仅未登录访客',
                'clients' => '仅已登录客户',
                'client_groups' => '指定客户组',
            ],
        ],
        'en' => [
            'type' => [
                'promotion' => 'Promotion',
                'coupon' => 'Coupon',
                'domain' => 'Domain Deal',
                'maintenance' => 'Maintenance',
                'urgent' => 'Urgent Notice',
                'group_buy' => 'Group Deal',
                'notice' => 'Notice',
            ],
            'display_mode' => [
                'modal' => 'Centered Modal',
                'top_bar' => 'Top Banner',
                'bottom_bar' => 'Bottom Banner',
                'corner' => 'Bottom-right Popup',
            ],
            'theme' => [
                'blue' => 'Blue',
                'green' => 'Green',
                'orange' => 'Orange',
                'red' => 'Red',
                'slate' => 'Slate',
            ],
            'frequency' => [
                'every_page' => 'Every Visit',
                'session' => 'Once per Session',
                'daily' => 'Once per Day',
                'once' => 'Once per Browser',
            ],
            'audience' => [
                'all' => 'All Visitors',
                'guests' => 'Guests Only',
                'clients' => 'Logged-in Clients',
                'client_groups' => 'Specific Client Groups',
            ],
        ],
    ];

    return $options[$language][$group] ?? $options['en'][$group] ?? [];
}

function peakrack_popup_verify_admin_token(): bool
{
    if (function_exists('check_token')) {
        return (bool) check_token('WHMCS.admin.default');
    }

    return true;
}

function peakrack_popup_admin_token_field(): string
{
    if (!function_exists('generate_token')) {
        return '';
    }

    $token = (string) generate_token('plain');
    return '<input type="hidden" name="token" value="' . peakrackPopupE($token) . '">';
}

function peakrack_popup_admin_url(string $language, array $params = []): string
{
    $query = array_merge(['module' => 'peakrack_popup', 'prp_admin_lang' => $language], $params);
    return 'addonmodules.php?' . http_build_query($query);
}

function peakrack_popup_handle_post(string $language): array
{
    $action = (string) ($_POST['prp_action'] ?? '');

    try {
        if ($action === 'save_popup') {
            $id = (int) ($_POST['id'] ?? 0);
            $data = peakrack_popup_data_from_post();

            if ($id > 0 && peakrack_popup_find($id)) {
                $data['updated_at'] = peakrackPopupNow();
                Capsule::table('mod_peakrack_popups')->where('id', $id)->update($data);
                return [peakrack_popup_t($language, 'saved'), 'success'];
            }

            $now = peakrackPopupNow();
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $data['view_count'] = 0;
            $data['click_count'] = 0;
            $data['close_count'] = 0;
            Capsule::table('mod_peakrack_popups')->insert($data);
            return [peakrack_popup_t($language, 'created'), 'success'];
        }

        if ($action === 'toggle_popup') {
            $id = (int) ($_POST['id'] ?? 0);
            $row = peakrack_popup_find($id);
            if (!$row) {
                return [peakrack_popup_t($language, 'missing_toggle'), 'warning'];
            }

            Capsule::table('mod_peakrack_popups')->where('id', $id)->update([
                'enabled' => empty($row['enabled']) ? 1 : 0,
                'updated_at' => peakrackPopupNow(),
            ]);
            return [peakrack_popup_t($language, 'toggled'), 'success'];
        }

        if ($action === 'delete_popup') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Capsule::table('mod_peakrack_popups')->where('id', $id)->delete();
                Capsule::table('mod_peakrack_popup_events')->where('popup_id', $id)->delete();
            }
            return [peakrack_popup_t($language, 'deleted'), 'success'];
        }

        if ($action === 'reset_stats') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Capsule::table('mod_peakrack_popups')->where('id', $id)->update([
                    'view_count' => 0,
                    'click_count' => 0,
                    'close_count' => 0,
                    'updated_at' => peakrackPopupNow(),
                ]);
                Capsule::table('mod_peakrack_popup_events')->where('popup_id', $id)->delete();
            }
            return [peakrack_popup_t($language, 'stats_reset'), 'success'];
        }
    } catch (\Throwable $e) {
        return [peakrack_popup_t($language, 'operation_failed') . $e->getMessage(), 'danger'];
    }

    return [peakrack_popup_t($language, 'no_action'), 'warning'];
}

function peakrack_popup_data_from_post(): array
{
    $type = peakrackPopupValidChoice($_POST['type'] ?? 'promotion', ['promotion', 'coupon', 'domain', 'maintenance', 'urgent', 'group_buy', 'notice'], 'promotion');
    $displayMode = peakrackPopupValidChoice($_POST['display_mode'] ?? 'modal', ['modal', 'top_bar', 'bottom_bar', 'corner'], 'modal');
    $audience = peakrackPopupValidChoice($_POST['audience'] ?? 'all', ['all', 'guests', 'clients', 'client_groups'], 'all');
    $frequency = peakrackPopupValidChoice($_POST['frequency'] ?? 'daily', ['every_page', 'session', 'daily', 'once'], 'daily');
    $theme = peakrackPopupValidChoice($_POST['theme'] ?? 'blue', ['blue', 'green', 'orange', 'red', 'slate'], 'blue');
    $accentColor = peakrackPopupNormalizeAccentColor($_POST['accent_color'] ?? '', peakrackPopupThemeAccent($theme));

    return [
        'name' => peakrack_popup_limit(trim((string) ($_POST['name'] ?? 'Untitled popup')), 150),
        'enabled' => peakrackPopupBool($_POST['enabled'] ?? 0),
        'type' => $type,
        'display_mode' => $displayMode,
        'audience' => $audience,
        'client_group_ids' => peakrackPopupNormalizeIntCsv($_POST['client_group_ids'] ?? ''),
        'page_rules' => peakrackPopupNormalizeLines($_POST['page_rules'] ?? '*'),
        'frequency' => $frequency,
        'theme' => $theme,
        'accent_color' => $accentColor,
        'title' => peakrack_popup_limit(trim((string) ($_POST['title'] ?? '')), 200),
        'body' => trim((string) ($_POST['body'] ?? '')),
        'title_en' => peakrack_popup_limit(trim((string) ($_POST['title_en'] ?? '')), 200),
        'body_en' => trim((string) ($_POST['body_en'] ?? '')),
        'coupon_code' => peakrack_popup_limit(trim((string) ($_POST['coupon_code'] ?? '')), 80),
        'button_label' => peakrack_popup_limit(trim((string) ($_POST['button_label'] ?? '')), 100),
        'button_label_en' => peakrack_popup_limit(trim((string) ($_POST['button_label_en'] ?? '')), 100),
        'button_url' => trim((string) ($_POST['button_url'] ?? '')),
        'image_url' => trim((string) ($_POST['image_url'] ?? '')),
        'open_new_tab' => peakrackPopupBool($_POST['open_new_tab'] ?? 0),
        'priority' => max(-1000, min(1000, (int) ($_POST['priority'] ?? 0))),
        'delay_seconds' => max(0, min(60, (int) ($_POST['delay_seconds'] ?? 0))),
        'auto_close_seconds' => max(0, min(3600, (int) ($_POST['auto_close_seconds'] ?? 0))),
        'start_at' => peakrackPopupDateTimeForDb($_POST['start_at'] ?? ''),
        'end_at' => peakrackPopupDateTimeForDb($_POST['end_at'] ?? ''),
    ];
}

function peakrack_popup_limit(string $value, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length);
    }

    return substr($value, 0, $length);
}

function peakrack_popup_default_form(): array
{
    return [
        'id' => 0,
        'name' => '',
        'enabled' => 0,
        'type' => 'promotion',
        'display_mode' => 'modal',
        'audience' => 'all',
        'client_group_ids' => '',
        'page_rules' => '*',
        'frequency' => 'daily',
        'theme' => 'blue',
        'accent_color' => '#2563eb',
        'title' => '',
        'body' => '',
        'title_en' => '',
        'body_en' => '',
        'coupon_code' => '',
        'button_label' => '',
        'button_label_en' => '',
        'button_url' => '',
        'image_url' => '',
        'open_new_tab' => 0,
        'priority' => 0,
        'delay_seconds' => 0,
        'auto_close_seconds' => 0,
        'start_at' => '',
        'end_at' => '',
        'view_count' => 0,
        'click_count' => 0,
        'close_count' => 0,
    ];
}

function peakrack_popup_find(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $row = Capsule::table('mod_peakrack_popups')->where('id', $id)->first();
    return $row ? peakrackPopupObjectToArray($row) : null;
}

function peakrack_popup_all_rows(): array
{
    return array_map(
        static fn($row): array => peakrackPopupObjectToArray($row),
        Capsule::table('mod_peakrack_popups')
            ->orderBy('enabled', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->all()
    );
}

function peakrack_popup_select(string $name, string $current, array $options): string
{
    $html = '<select name="' . peakrackPopupE($name) . '" class="form-control">';
    foreach ($options as $value => $label) {
        $selected = $current === (string) $value ? ' selected' : '';
        $html .= '<option value="' . peakrackPopupE($value) . '"' . $selected . '>' . peakrackPopupE($label) . '</option>';
    }
    $html .= '</select>';

    return $html;
}

function peakrack_popup_hidden_language(string $language): string
{
    return '<input type="hidden" name="prp_admin_lang" value="' . peakrackPopupE($language) . '">';
}

function peakrack_popup_render_admin(array $editing, array $popups, string $message, string $messageType, string $language): string
{
    $token = peakrack_popup_admin_token_field();
    $hiddenLanguage = peakrack_popup_hidden_language($language);
    $isEdit = (int) ($editing['id'] ?? 0) > 0;
    $totalViews = array_sum(array_map(static fn(array $row): int => (int) ($row['view_count'] ?? 0), $popups));
    $totalClicks = array_sum(array_map(static fn(array $row): int => (int) ($row['click_count'] ?? 0), $popups));
    $enabledCount = count(array_filter($popups, static fn(array $row): bool => !empty($row['enabled'])));
    $formAction = peakrack_popup_admin_url($language, $isEdit ? ['edit' => (int) $editing['id']] : []);
    $baseAction = peakrack_popup_admin_url($language);
    $t = static fn(string $key): string => peakrack_popup_t($language, $key);
    $typeLabels = peakrack_popup_options($language, 'type');
    $modeLabels = peakrack_popup_options($language, 'display_mode');
    $frequencyLabels = peakrack_popup_options($language, 'frequency');
    $audienceLabels = peakrack_popup_options($language, 'audience');

    ob_start();
    ?>
    <style>
        .prp-admin{max-width:1420px;color:#1f2937}
        .prp-admin *{box-sizing:border-box}
        .prp-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:18px}
        .prp-eyebrow{margin-bottom:4px;color:#64748b;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .prp-head h2{margin:0 0 6px;font-size:24px;font-weight:700;color:#111827}
        .prp-head p{margin:0;max-width:760px;color:#6b7280;font-size:13px;line-height:1.5}
        .prp-head-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}
        .prp-lang{display:inline-flex;border:1px solid #cfd8e3;border-radius:6px;background:#fff;overflow:hidden}
        .prp-lang a{display:inline-flex;align-items:center;padding:7px 10px;color:#475569;text-decoration:none;font-size:12px;font-weight:700}
        .prp-lang a.active{background:#2563eb;color:#fff}
        .prp-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
        .prp-stat{border:1px solid #d8e0ea;border-radius:6px;background:#fff;padding:14px 16px}
        .prp-stat-label{display:block;color:#6b7280;font-size:12px;font-weight:700;text-transform:uppercase}
        .prp-stat-value{display:block;margin-top:5px;font-size:21px;font-weight:700;color:#111827}
        .prp-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:18px;align-items:start}
        .prp-card{border:1px solid #d8e0ea;border-radius:6px;background:#fff;margin-bottom:18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
        .prp-card-head{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:14px 16px;border-bottom:1px solid #e7edf3;background:#fbfcfe}
        .prp-card-title{margin:0;font-size:15px;font-weight:700;color:#111827}
        .prp-card-desc{margin:4px 0 0;color:#64748b;font-size:12px;line-height:1.45}
        .prp-card-body{padding:16px}
        .prp-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px}
        .prp-form-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px 16px}
        .prp-color-input{display:flex;gap:10px;align-items:center}
        .prp-color-input input[type=color]{width:58px;height:34px;padding:2px;border:1px solid #cfd8e3;border-radius:6px;background:#fff}
        .prp-color-input code{display:inline-block;min-width:78px;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;border-radius:5px;padding:6px 8px}
        .prp-language-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .prp-language-panel{border:1px solid #e2e8f0;border-radius:6px;background:#fff}
        .prp-language-title{margin:0;padding:10px 12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-size:13px;font-weight:700;color:#334155}
        .prp-language-body{padding:12px;display:grid;gap:12px}
        .prp-field label{display:block;margin-bottom:6px;font-weight:700;color:#374151}
        .prp-field .help{margin-top:5px;color:#6b7280;font-size:12px;line-height:1.45}
        .prp-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .prp-note{margin:0;color:#64748b;font-size:12px;line-height:1.55}
        .prp-table-wrap{overflow-x:auto}
        .prp-table{width:100%;border-collapse:collapse;min-width:980px}
        .prp-table th,.prp-table td{border-bottom:1px solid #e5e7eb;padding:10px 8px;text-align:left;vertical-align:middle}
        .prp-table th{font-size:12px;color:#64748b;text-transform:uppercase;background:#f8fafc}
        .prp-badge{display:inline-block;border-radius:999px;padding:3px 8px;font-size:12px;font-weight:700}
        .prp-badge-on{background:#dcfce7;color:#166534}
        .prp-badge-off{background:#f1f5f9;color:#475569}
        .prp-muted{color:#64748b;font-size:12px;line-height:1.45}
        .prp-inline-form{display:inline}
        .prp-danger{color:#b91c1c}
        @media (max-width:1100px){.prp-grid,.prp-stats,.prp-language-grid{grid-template-columns:1fr}.prp-form-grid,.prp-form-grid-3{grid-template-columns:1fr}.prp-head{display:block}.prp-head-actions{justify-content:flex-start;margin-top:12px}}
    </style>
    <div class="prp-admin">
        <div class="prp-head">
            <div>
                <div class="prp-eyebrow">PeakRack Popup</div>
                <h2><?php echo peakrackPopupE($t('title')); ?></h2>
                <p><?php echo peakrackPopupE($t('subtitle')); ?></p>
            </div>
            <div class="prp-head-actions">
                <div class="prp-lang">
                    <a class="<?php echo $language === 'zh' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url('zh', $isEdit ? ['edit' => (int) $editing['id']] : [])); ?>">中文</a>
                    <a class="<?php echo $language === 'en' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url('en', $isEdit ? ['edit' => (int) $editing['id']] : [])); ?>">English</a>
                </div>
                <a class="btn btn-primary" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language)); ?>"><?php echo peakrackPopupE($t('new_popup')); ?></a>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo peakrackPopupE($messageType); ?>"><?php echo peakrackPopupE($message); ?></div>
        <?php endif; ?>

        <div class="prp-stats">
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_total')); ?></span><span class="prp-stat-value"><?php echo count($popups); ?></span></div>
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_enabled')); ?></span><span class="prp-stat-value"><?php echo $enabledCount; ?></span></div>
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_views')); ?></span><span class="prp-stat-value"><?php echo $totalViews; ?></span></div>
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_clicks')); ?></span><span class="prp-stat-value"><?php echo $totalClicks; ?></span></div>
        </div>

        <div class="prp-grid">
            <div>
                <form method="post" action="<?php echo peakrackPopupE($formAction); ?>">
                    <?php echo $token . $hiddenLanguage; ?>
                    <input type="hidden" name="prp_action" value="save_popup">
                    <input type="hidden" name="id" value="<?php echo (int) ($editing['id'] ?? 0); ?>">

                    <div class="prp-card">
                        <div class="prp-card-head">
                            <div>
                                <h3 class="prp-card-title"><?php echo peakrackPopupE($isEdit ? $t('edit_popup') . ' #' . (int) $editing['id'] : $t('create_popup')); ?></h3>
                            </div>
                            <label style="margin:0;font-weight:700"><input type="checkbox" name="enabled" value="1" <?php echo !empty($editing['enabled']) ? 'checked' : ''; ?>> <?php echo peakrackPopupE($t('enabled')); ?></label>
                        </div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('admin_name')); ?></label>
                                    <input class="form-control" type="text" name="name" value="<?php echo peakrackPopupE($editing['name'] ?? ''); ?>" required>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('priority')); ?></label>
                                    <input class="form-control" type="number" name="priority" value="<?php echo (int) ($editing['priority'] ?? 0); ?>">
                                    <div class="help"><?php echo peakrackPopupE($t('priority_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('type')); ?></label>
                                    <?php echo peakrack_popup_select('type', (string) ($editing['type'] ?? 'promotion'), peakrack_popup_options($language, 'type')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('style')); ?></label>
                                    <?php echo peakrack_popup_select('display_mode', (string) ($editing['display_mode'] ?? 'modal'), peakrack_popup_options($language, 'display_mode')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('theme')); ?></label>
                                    <?php echo peakrack_popup_select('theme', (string) ($editing['theme'] ?? 'blue'), peakrack_popup_options($language, 'theme')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('accent_color')); ?></label>
                                    <?php $currentColor = peakrackPopupNormalizeAccentColor($editing['accent_color'] ?? '', peakrackPopupThemeAccent((string) ($editing['theme'] ?? 'blue'))); ?>
                                    <div class="prp-color-input">
                                        <input type="color" name="accent_color" value="<?php echo peakrackPopupE($currentColor); ?>">
                                        <code><?php echo peakrackPopupE($currentColor); ?></code>
                                    </div>
                                    <div class="help"><?php echo peakrackPopupE($t('accent_color_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('frequency')); ?></label>
                                    <?php echo peakrack_popup_select('frequency', (string) ($editing['frequency'] ?? 'daily'), peakrack_popup_options($language, 'frequency')); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="prp-card">
                        <div class="prp-card-head">
                            <div>
                                <h3 class="prp-card-title"><?php echo peakrackPopupE($t('popup_content')); ?></h3>
                                <p class="prp-card-desc"><?php echo peakrackPopupE($t('content_help')); ?></p>
                            </div>
                        </div>
                        <div class="prp-card-body">
                            <div class="prp-language-grid">
                                <div class="prp-language-panel">
                                    <h4 class="prp-language-title"><?php echo peakrackPopupE($t('zh_content')); ?></h4>
                                    <div class="prp-language-body">
                                        <div class="prp-field">
                                            <label><?php echo peakrackPopupE($t('title_field')); ?></label>
                                            <input class="form-control" type="text" name="title" value="<?php echo peakrackPopupE($editing['title'] ?? ''); ?>">
                                        </div>
                                        <div class="prp-field">
                                            <label><?php echo peakrackPopupE($t('body_field')); ?></label>
                                            <textarea class="form-control" name="body" rows="5"><?php echo peakrackPopupE($editing['body'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="prp-field">
                                            <label><?php echo peakrackPopupE($t('button_text')); ?></label>
                                            <input class="form-control" type="text" name="button_label" value="<?php echo peakrackPopupE($editing['button_label'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="prp-language-panel">
                                    <h4 class="prp-language-title"><?php echo peakrackPopupE($t('en_content')); ?></h4>
                                    <div class="prp-language-body">
                                        <div class="prp-field">
                                            <label><?php echo peakrackPopupE($t('title_field')); ?></label>
                                            <input class="form-control" type="text" name="title_en" value="<?php echo peakrackPopupE($editing['title_en'] ?? ''); ?>">
                                        </div>
                                        <div class="prp-field">
                                            <label><?php echo peakrackPopupE($t('body_field')); ?></label>
                                            <textarea class="form-control" name="body_en" rows="5"><?php echo peakrackPopupE($editing['body_en'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="prp-field">
                                            <label><?php echo peakrackPopupE($t('button_text')); ?></label>
                                            <input class="form-control" type="text" name="button_label_en" value="<?php echo peakrackPopupE($editing['button_label_en'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h4 class="prp-card-title" style="margin:0 0 12px"><?php echo peakrackPopupE($t('shared_assets')); ?></h4>
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('coupon_code')); ?></label>
                                    <input class="form-control" type="text" name="coupon_code" value="<?php echo peakrackPopupE($editing['coupon_code'] ?? ''); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('image_url')); ?></label>
                                    <input class="form-control" type="text" name="image_url" value="<?php echo peakrackPopupE($editing['image_url'] ?? ''); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('button_url')); ?></label>
                                    <input class="form-control" type="text" name="button_url" value="<?php echo peakrackPopupE($editing['button_url'] ?? ''); ?>" placeholder="cart.php or https://example.com">
                                </div>
                                <div class="prp-field" style="display:flex;align-items:end">
                                    <label style="margin:0;font-weight:700"><input type="checkbox" name="open_new_tab" value="1" <?php echo !empty($editing['open_new_tab']) ? 'checked' : ''; ?>> <?php echo peakrackPopupE($t('new_tab')); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="prp-card">
                        <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('rules')); ?></h3></div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('audience')); ?></label>
                                    <?php echo peakrack_popup_select('audience', (string) ($editing['audience'] ?? 'all'), peakrack_popup_options($language, 'audience')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('client_group_ids')); ?></label>
                                    <input class="form-control" type="text" name="client_group_ids" value="<?php echo peakrackPopupE($editing['client_group_ids'] ?? ''); ?>" placeholder="1,2,3">
                                    <div class="help"><?php echo peakrackPopupE($t('client_group_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('start_at')); ?></label>
                                    <input class="form-control" type="datetime-local" name="start_at" value="<?php echo peakrackPopupE(peakrackPopupDateTimeForInput($editing['start_at'] ?? '')); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('end_at')); ?></label>
                                    <input class="form-control" type="datetime-local" name="end_at" value="<?php echo peakrackPopupE(peakrackPopupDateTimeForInput($editing['end_at'] ?? '')); ?>">
                                </div>
                            </div>
                            <div class="prp-form-grid-3" style="margin-top:14px">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('delay_seconds')); ?></label>
                                    <input class="form-control" type="number" min="0" max="60" name="delay_seconds" value="<?php echo (int) ($editing['delay_seconds'] ?? 0); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('auto_close_seconds')); ?></label>
                                    <input class="form-control" type="number" min="0" max="3600" name="auto_close_seconds" value="<?php echo (int) ($editing['auto_close_seconds'] ?? 0); ?>">
                                    <div class="help"><?php echo peakrackPopupE($t('auto_close_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('page_rules')); ?></label>
                                    <textarea class="form-control" name="page_rules" rows="3"><?php echo peakrackPopupE($editing['page_rules'] ?? '*'); ?></textarea>
                                    <div class="help"><?php echo peakrackPopupE($t('page_rules_help')); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="prp-actions" style="margin-bottom:22px">
                        <button class="btn btn-primary" type="submit"><?php echo peakrackPopupE($t('save')); ?></button>
                        <a class="btn btn-default" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language)); ?>"><?php echo peakrackPopupE($t('clear')); ?></a>
                    </div>
                </form>
            </div>

            <div>
                <div class="prp-card">
                    <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('language_behavior')); ?></h3></div>
                    <div class="prp-card-body">
                        <p class="prp-note"><?php echo peakrackPopupE($t('language_behavior_body')); ?></p>
                    </div>
                </div>
                <div class="prp-card">
                    <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('usage')); ?></h3></div>
                    <div class="prp-card-body">
                        <p class="prp-note"><?php echo peakrackPopupE($t('usage_body')); ?></p>
                        <p class="prp-note" style="margin-top:10px"><?php echo peakrackPopupE($t('usage_body_2')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="prp-card">
            <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('list')); ?></h3></div>
            <div class="prp-card-body">
                <div class="prp-table-wrap">
                    <table class="prp-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo peakrackPopupE($t('name')); ?></th>
                            <th><?php echo peakrackPopupE($t('status')); ?></th>
                            <th><?php echo peakrackPopupE($t('type_style')); ?></th>
                            <th><?php echo peakrackPopupE($t('rule_column')); ?></th>
                            <th><?php echo peakrackPopupE($t('stats')); ?></th>
                            <th><?php echo peakrackPopupE($t('actions')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$popups): ?>
                            <tr><td colspan="7" class="prp-muted"><?php echo peakrackPopupE($t('empty')); ?></td></tr>
                        <?php endif; ?>
                        <?php foreach ($popups as $row): ?>
                            <tr>
                                <td><?php echo (int) $row['id']; ?></td>
                                <td>
                                    <strong><?php echo peakrackPopupE($row['name'] ?? ''); ?></strong><br>
                                    <span class="prp-muted">ZH: <?php echo peakrackPopupE($row['title'] ?? ''); ?></span><br>
                                    <span class="prp-muted">EN: <?php echo peakrackPopupE($row['title_en'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="prp-badge <?php echo !empty($row['enabled']) ? 'prp-badge-on' : 'prp-badge-off'; ?>"><?php echo peakrackPopupE(!empty($row['enabled']) ? $t('enabled') : $t('disabled')); ?></span><br>
                                    <span class="prp-muted"><?php echo peakrackPopupE($t('priority')); ?> <?php echo (int) ($row['priority'] ?? 0); ?></span>
                                </td>
                                <td>
                                    <?php echo peakrackPopupE($typeLabels[$row['type'] ?? ''] ?? (string) ($row['type'] ?? '')); ?><br>
                                    <span class="prp-muted"><?php echo peakrackPopupE($modeLabels[$row['display_mode'] ?? ''] ?? (string) ($row['display_mode'] ?? '')); ?> / <?php echo peakrackPopupE($frequencyLabels[$row['frequency'] ?? ''] ?? (string) ($row['frequency'] ?? '')); ?></span>
                                </td>
                                <td>
                                    <span class="prp-muted"><?php echo peakrackPopupE($t('audience_label')); ?>: <?php echo peakrackPopupE($audienceLabels[$row['audience'] ?? 'all'] ?? (string) ($row['audience'] ?? 'all')); ?></span><br>
                                    <span class="prp-muted"><?php echo peakrackPopupE($t('page_label')); ?>: <?php echo peakrackPopupE(str_replace("\n", ', ', (string) ($row['page_rules'] ?? '*'))); ?></span>
                                </td>
                                <td>
                                    V <?php echo (int) ($row['view_count'] ?? 0); ?> /
                                    C <?php echo (int) ($row['click_count'] ?? 0); ?> /
                                    X <?php echo (int) ($row['close_count'] ?? 0); ?>
                                </td>
                                <td>
                                    <a class="btn btn-xs btn-default" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['edit' => (int) $row['id']])); ?>"><?php echo peakrackPopupE($t('edit')); ?></a>
                                    <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>">
                                        <?php echo $token . $hiddenLanguage; ?>
                                        <input type="hidden" name="prp_action" value="toggle_popup">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <button class="btn btn-xs btn-default" type="submit"><?php echo peakrackPopupE(!empty($row['enabled']) ? $t('disable') : $t('enable')); ?></button>
                                    </form>
                                    <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>">
                                        <?php echo $token . $hiddenLanguage; ?>
                                        <input type="hidden" name="prp_action" value="reset_stats">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <button class="btn btn-xs btn-default" type="submit"><?php echo peakrackPopupE($t('reset')); ?></button>
                                    </form>
                                    <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>" onsubmit="return confirm('<?php echo peakrackPopupE($t('delete_confirm')); ?>');">
                                        <?php echo $token . $hiddenLanguage; ?>
                                        <input type="hidden" name="prp_action" value="delete_popup">
                                        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                        <button class="btn btn-xs btn-link prp-danger" type="submit"><?php echo peakrackPopupE($t('delete')); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var theme = document.querySelector('.prp-admin select[name="theme"]');
            var color = document.querySelector('.prp-admin input[name="accent_color"]');
            var code = document.querySelector('.prp-admin .prp-color-input code');
            var palette = {blue:'#2563eb',green:'#16a34a',orange:'#f97316',red:'#dc2626',slate:'#334155'};
            if (!theme || !color || !code) {
                return;
            }
            color.addEventListener('input', function () {
                code.textContent = color.value;
            });
            theme.addEventListener('change', function () {
                if (palette[theme.value]) {
                    color.value = palette[theme.value];
                    code.textContent = color.value;
                }
            });
        })();
        </script>
    </div>
    <?php
    return (string) ob_get_clean();
}
