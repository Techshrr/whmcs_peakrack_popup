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

if (!defined('WHMCS')) {
    die('No direct access');
}

require_once __DIR__ . '/lib/Popup.php';

function peakrack_popup_config(): array
{
    return [
        'name' => 'PeakRack Popup',
        'description' => 'Manage Text, HTML, and Image popups for the WHMCS client area.',
        'version' => '1.6.1',
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
    $view = peakrackPopupValidChoice($_POST['prp_view'] ?? ($_GET['view'] ?? 'popups'), ['popups', 'styles'], 'popups');

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

    if ($view === 'styles') {
        $styleId = isset($_GET['style']) ? (int) $_GET['style'] : 0;
        $editingStyle = $styleId > 0 ? peakrack_popup_style_find($styleId) : peakrack_popup_default_style_form();
        if (!$editingStyle) {
            $editingStyle = peakrack_popup_default_style_form();
            $message = peakrack_popup_t($language, 'style_not_found');
            $messageType = 'warning';
        }

        echo peakrack_popup_render_styles_admin($editingStyle, peakrack_popup_all_styles(), $message, $messageType, $language);
        return;
    }

    $previewId = isset($_GET['preview']) ? (int) $_GET['preview'] : 0;
    $preview = $previewId > 0 ? peakrack_popup_find($previewId) : null;
    $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    $editing = $editId > 0 ? peakrack_popup_find($editId) : peakrack_popup_default_form();
    if (!$editing) {
        $editing = peakrack_popup_default_form();
        $message = peakrack_popup_t($language, 'not_found');
        $messageType = 'warning';
    }

    $popups = peakrack_popup_all_rows();
    echo peakrack_popup_render_admin($editing, $popups, peakrack_popup_all_styles(), $message, $messageType, $language, $preview);
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
            'subtitle' => '按 Text / HTML / Image 类型管理 WHMCS 客户区弹窗，并配置展示规则、样式和时间限制。',
            'new_popup' => '新增弹窗',
            'nav_popups' => '弹窗',
            'nav_styles' => '样式',
            'stats_total' => '弹窗总数',
            'stats_enabled' => '已启用',
            'stats_views' => '展示',
            'stats_clicks' => '点击',
            'edit_popup' => '编辑弹窗',
            'create_popup' => '新增弹窗',
            'enabled' => '启用',
            'campaign_settings' => '弹窗设置',
            'admin_name' => '后台名称',
            'priority' => '优先级',
            'priority_help' => '同一页面命中多个弹窗时，数值越大越优先。',
            'type' => '类型',
            'content_format' => '类型',
            'content_format_help' => '官方同款语义：Type 只表示 Text、HTML 或 Image。Text 会自动转义换行；HTML 适合可信管理员自定义内容；Image 适合上传图片或图片 URL 主视觉。',
            'style' => '样式',
            'popup_style' => '弹窗样式',
            'popup_style_help' => '选择独立 Styles 中的样式。选择后，样式会覆盖展示位置、颜色、尺寸和动画；留空则使用下面的本地设置。',
            'style_fallback' => '不使用独立样式',
            'theme' => '主题色',
            'accent_color' => '弹窗颜色',
            'accent_color_help' => '控制顶部色条、强调元素和按钮颜色。',
            'frequency' => '展示频率',
            'hide_permanently' => '永久关闭',
            'popup_content' => '弹窗内容',
            'content_help' => '中文客户看中文内容，英文客户看 English 内容。英文内容为空时会回退到中文内容。',
            'raw_html_note' => 'HTML 内容会原样输出，仅限可信管理员使用。',
            'zh_content' => '中文内容',
            'en_content' => 'English Content',
            'title_field' => '标题',
            'body_field' => '正文',
            'button_text' => '按钮文字',
            'shared_assets' => '图片和跳转',
            'image_url' => '图片 URL',
            'image_upload' => '上传图片',
            'image_upload_help' => '支持 JPG、PNG、GIF、WEBP，最大 4 MB；上传后会自动写入图片 URL。',
            'button_url' => '按钮链接',
            'new_tab' => '按钮新窗口打开',
            'rules' => '展示规则',
            'audience' => '受众',
            'client_group_ids' => '客户组 ID',
            'client_group_help' => '仅受众选择“指定客户组”时生效。',
            'advanced_restrictions' => '高级限制',
            'language_rules' => '语言限制',
            'language_rules_help' => '可填 zh、en，留空表示不限语言。',
            'days_of_week' => '星期限制',
            'days_help' => '1=周一，7=周日；多个用逗号分隔，留空表示每天。',
            'url_contains' => 'URL 包含',
            'url_contains_help' => '每行一个关键词，当前 URL 包含任一关键词才展示。',
            'requires_unpaid_invoice' => '仅未付账单客户',
            'catalog_restrictions' => '产品/附加服务/域名限制',
            'active_product_ids' => '需拥有产品 ID',
            'active_product_group_ids' => '需拥有产品组 ID',
            'active_server_ids' => '需拥有服务器 ID',
            'active_addon_ids' => '需拥有附加服务 ID',
            'active_tlds' => '需拥有域名后缀',
            'missing_product_ids' => '不能拥有产品 ID',
            'missing_addon_ids' => '不能拥有附加服务 ID',
            'missing_tlds' => '不能拥有域名后缀',
            'ids_help' => '多个值用逗号分隔；留空表示不启用该条件。',
            'timing_limits' => '时间与上限',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'time_start' => '每日开始时间',
            'time_end' => '每日结束时间',
            'delay_seconds' => '延迟显示秒数',
            'auto_close_seconds' => '自动关闭秒数',
            'auto_close_help' => '0 表示不自动关闭。',
            'display_limit' => '总展示上限',
            'per_client_display_limit' => '单客户展示上限',
            'limit_help' => '0 表示不限。单客户上限依赖前台 view 事件记录。',
            'size_animation' => '尺寸与动画',
            'popup_width' => '宽度',
            'popup_height' => '高度',
            'size_help' => '可填 520px、80% 或留空自动。',
            'animation' => '动画',
            'animation_ms' => '动画毫秒',
            'due_restrictions' => '服务到期限制',
            'due_mode' => '到期方向',
            'due_operator' => '天数条件',
            'due_days' => '天数',
            'due_product_ids' => '检查产品 ID',
            'due_statuses' => '服务状态',
            'due_help' => '留空产品 ID 表示检查该客户所有服务；状态默认 Active。',
            'page_rules' => '页面规则',
            'page_rules_help' => '每行一个：*、cart.php、clientarea.php?action=*。',
            'save' => '保存弹窗',
            'clear' => '清空表单',
            'language_behavior' => '语言规则',
            'language_behavior_body' => '后台语言按钮只影响管理界面。前台弹窗会读取客户区当前语言；WHMCS 为 English 时使用英文标题、正文和按钮。',
            'usage' => '使用建议',
            'usage_body' => '重要通知建议用顶部横幅并设置高优先级；图片海报建议用 Image 类型和海报弹窗；维护通知可以限制在 networkissues.php、clientarea.php 或购物车页。',
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
            'archived' => '已归档',
            'audience_label' => '受众',
            'page_label' => '页面',
            'edit' => '编辑',
            'preview' => '预览',
            'disable' => '停用',
            'enable' => '启用',
            'archive' => '归档',
            'restore' => '恢复',
            'reset' => '清零',
            'delete' => '删除',
            'delete_confirm' => '确定删除这个弹窗吗？',
            'saved' => '弹窗已保存。',
            'created' => '新弹窗已创建。',
            'toggled' => '弹窗状态已更新。',
            'archived_action' => '弹窗已归档。',
            'restored' => '弹窗已恢复。',
            'deleted' => '弹窗已删除。',
            'stats_reset' => '统计数据已重置。',
            'styles_title' => '样式管理',
            'styles_subtitle' => '管理可供弹窗选择的独立 Style。每个 Style 可定义展示位置、颜色、尺寸、动画和自定义 CSS。',
            'new_style' => '新增样式',
            'edit_style' => '编辑样式',
            'style_name' => '样式名称',
            'style_slug' => '标识',
            'style_desc' => '说明',
            'style_enabled' => '启用样式',
            'style_system' => '系统样式',
            'style_custom_css' => '自定义 CSS',
            'style_custom_css_help' => '使用 {root} 作为当前弹窗根选择器，例如 {root} .prp-panel{border-radius:4px}。',
            'style_html_template' => 'HTML 模板',
            'style_html_template_help' => '留空时使用默认模板。支持 {close}、{image}、{title}、{body}、{actions}、{image_close}、{content}、{accent} 等占位符。',
            'style_sort_order' => '排序',
            'save_style' => '保存样式',
            'style_saved' => '样式已保存。',
            'style_created' => '新样式已创建。',
            'style_deleted' => '样式已删除。',
            'style_not_found' => '未找到要编辑的样式，已切换到新增模式。',
            'style_delete_confirm' => '确定删除这个样式吗？已被弹窗使用时只会停用。',
            'style_preview' => '样式预览',
            'style_usage' => '使用次数',
            'style_uses' => '使用',
            'missing_toggle' => '未找到要切换的弹窗。',
            'no_action' => '没有可执行的操作。',
            'operation_failed' => '操作失败：',
            'token_failed' => '安全令牌验证失败，请刷新页面后重试。',
            'not_found' => '未找到要编辑的弹窗，已切换到新增模式。',
            'previewing' => '正在预览弹窗 #',
            'validation_failed' => '请先修正：',
            'required_text_content' => 'Text 格式至少需要填写一个标题或正文。',
            'required_html_content' => 'HTML 格式至少需要填写一个正文。',
            'required_image_url' => 'Image 格式必须填写有效图片 URL。',
            'image_upload_invalid' => '上传图片必须是 JPG、PNG、GIF 或 WEBP，且文件类型需要和扩展名一致。',
            'image_upload_too_large' => '上传图片不能超过 4 MB。',
            'image_upload_dir_failed' => '无法创建图片上传目录，请检查模块目录权限。',
            'image_upload_failed' => '图片上传失败，请重试或检查模块目录权限。',
            'invalid_button_url' => '按钮链接必须是 HTTP(S)、根路径或 WHMCS PHP 路由。',
            'button_pair_required' => '填写按钮链接时也需要填写按钮文字；填写按钮文字时也需要填写按钮链接。',
            'field_rules' => '字段规则',
            'field_rules_body' => 'Text：标题或正文必填。HTML：正文必填。Image：图片 URL 或上传图片必填，标题/正文/按钮可选。旧版优惠码/活动/公告业务分类已停用。',
        ],
        'en' => [
            'title' => 'PeakRack Popup Manager',
            'subtitle' => 'Manage WHMCS client-area popups by official-style Text, HTML, and Image types with rules, styles, and timing controls.',
            'new_popup' => 'New Popup',
            'nav_popups' => 'Pop-ups',
            'nav_styles' => 'Styles',
            'stats_total' => 'Total',
            'stats_enabled' => 'Enabled',
            'stats_views' => 'Views',
            'stats_clicks' => 'Clicks',
            'edit_popup' => 'Edit Popup',
            'create_popup' => 'Create Popup',
            'enabled' => 'Enabled',
            'campaign_settings' => 'Popup Settings',
            'admin_name' => 'Internal Name',
            'priority' => 'Priority',
            'priority_help' => 'When multiple popups match a page, the highest priority wins.',
            'type' => 'Type',
            'content_format' => 'Type',
            'content_format_help' => 'Official-style semantics: Type means Text, HTML, or Image. Text is escaped and keeps line breaks; HTML is trusted administrator content; Image emphasizes an uploaded image or image URL.',
            'style' => 'Display Mode',
            'popup_style' => 'Popup Style',
            'popup_style_help' => 'Select a reusable Style. When selected, it overrides display mode, color, size, and animation; leave blank to use the local settings below.',
            'style_fallback' => 'No reusable style',
            'theme' => 'Theme',
            'accent_color' => 'Popup Color',
            'accent_color_help' => 'Controls the top accent bar, emphasis elements, and button color.',
            'frequency' => 'Frequency',
            'hide_permanently' => 'Permanent Close',
            'popup_content' => 'Popup Content',
            'content_help' => 'Chinese clients see Chinese content; English clients see English content. Empty English fields fall back to Chinese.',
            'raw_html_note' => 'HTML content is rendered as provided. Use it only for trusted administrator content.',
            'zh_content' => 'Chinese Content',
            'en_content' => 'English Content',
            'title_field' => 'Title',
            'body_field' => 'Body',
            'button_text' => 'Button Text',
            'shared_assets' => 'Image and Link',
            'image_url' => 'Image URL',
            'image_upload' => 'Upload Image',
            'image_upload_help' => 'JPG, PNG, GIF, and WEBP are supported. Maximum size is 4 MB. The Image URL field is filled automatically after upload.',
            'button_url' => 'Button URL',
            'new_tab' => 'Open button in a new tab',
            'rules' => 'Display Rules',
            'audience' => 'Audience',
            'client_group_ids' => 'Client Group IDs',
            'client_group_help' => 'Only used when audience is set to specific client groups.',
            'advanced_restrictions' => 'Advanced Restrictions',
            'language_rules' => 'Language Rules',
            'language_rules_help' => 'Use zh and/or en. Leave blank for all languages.',
            'days_of_week' => 'Days of Week',
            'days_help' => '1=Monday, 7=Sunday. Separate multiple values with commas; blank means every day.',
            'url_contains' => 'URL Contains',
            'url_contains_help' => 'One phrase per line. The current URL must contain one phrase.',
            'requires_unpaid_invoice' => 'Unpaid Invoice Only',
            'catalog_restrictions' => 'Product, Addon, and Domain Restrictions',
            'active_product_ids' => 'Required Product IDs',
            'active_product_group_ids' => 'Required Product Group IDs',
            'active_server_ids' => 'Required Server IDs',
            'active_addon_ids' => 'Required Addon IDs',
            'active_tlds' => 'Required Domain TLDs',
            'missing_product_ids' => 'Excluded Product IDs',
            'missing_addon_ids' => 'Excluded Addon IDs',
            'missing_tlds' => 'Excluded Domain TLDs',
            'ids_help' => 'Separate values with commas. Blank means the condition is disabled.',
            'timing_limits' => 'Timing and Limits',
            'start_at' => 'Start Time',
            'end_at' => 'End Time',
            'time_start' => 'Daily Start Time',
            'time_end' => 'Daily End Time',
            'delay_seconds' => 'Delay Seconds',
            'auto_close_seconds' => 'Auto-close Seconds',
            'auto_close_help' => 'Use 0 to keep the popup open until the visitor closes it.',
            'display_limit' => 'Global Display Limit',
            'per_client_display_limit' => 'Per-client Display Limit',
            'limit_help' => '0 means unlimited. Per-client limits rely on frontend view events.',
            'size_animation' => 'Size and Animation',
            'popup_width' => 'Width',
            'popup_height' => 'Height',
            'size_help' => 'Use 520px, 80%, or leave blank for auto.',
            'animation' => 'Animation',
            'animation_ms' => 'Animation Milliseconds',
            'due_restrictions' => 'Due Date Restrictions',
            'due_mode' => 'Due Direction',
            'due_operator' => 'Day Condition',
            'due_days' => 'Days',
            'due_product_ids' => 'Product IDs to Check',
            'due_statuses' => 'Service Statuses',
            'due_help' => 'Blank product IDs check all services for the client. Status defaults to Active.',
            'page_rules' => 'Page Rules',
            'page_rules_help' => 'One rule per line: *, cart.php, clientarea.php?action=*.',
            'save' => 'Save Popup',
            'clear' => 'Clear Form',
            'language_behavior' => 'Language Behavior',
            'language_behavior_body' => 'The admin language switch only affects this management UI. The client popup follows the current WHMCS client-area language; English uses the English title, body, and button text.',
            'usage' => 'Usage Notes',
            'usage_body' => 'Use a top banner with high priority for important notices; use Image type with poster mode for image-led campaigns; limit maintenance notices to networkissues.php, clientarea.php, or cart pages.',
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
            'archived' => 'Archived',
            'audience_label' => 'Audience',
            'page_label' => 'Pages',
            'edit' => 'Edit',
            'preview' => 'Preview',
            'disable' => 'Disable',
            'enable' => 'Enable',
            'archive' => 'Archive',
            'restore' => 'Restore',
            'reset' => 'Reset',
            'delete' => 'Delete',
            'delete_confirm' => 'Delete this popup?',
            'saved' => 'Popup saved.',
            'created' => 'New popup created.',
            'toggled' => 'Popup status updated.',
            'archived_action' => 'Popup archived.',
            'restored' => 'Popup restored.',
            'deleted' => 'Popup deleted.',
            'stats_reset' => 'Statistics reset.',
            'styles_title' => 'Styles',
            'styles_subtitle' => 'Manage reusable Styles for popups. Each Style can define position, colors, size, animation, and custom CSS.',
            'new_style' => 'New Style',
            'edit_style' => 'Edit Style',
            'style_name' => 'Style Name',
            'style_slug' => 'Slug',
            'style_desc' => 'Description',
            'style_enabled' => 'Enabled',
            'style_system' => 'System Style',
            'style_custom_css' => 'Custom CSS',
            'style_custom_css_help' => 'Use {root} as the current popup root selector, for example {root} .prp-panel{border-radius:4px}.',
            'style_html_template' => 'HTML Template',
            'style_html_template_help' => 'Leave blank to use the default template. Supports placeholders such as {close}, {image}, {title}, {body}, {actions}, {image_close}, {content}, and {accent}.',
            'style_sort_order' => 'Sort Order',
            'save_style' => 'Save Style',
            'style_saved' => 'Style saved.',
            'style_created' => 'New style created.',
            'style_deleted' => 'Style deleted.',
            'style_not_found' => 'The style was not found. Switched to create mode.',
            'style_delete_confirm' => 'Delete this style? If it is used by popups, it will be disabled instead.',
            'style_preview' => 'Style Preview',
            'style_usage' => 'Usage',
            'style_uses' => 'Uses',
            'missing_toggle' => 'Popup not found.',
            'no_action' => 'No action was performed.',
            'operation_failed' => 'Operation failed: ',
            'token_failed' => 'Security token validation failed. Refresh the page and try again.',
            'not_found' => 'The popup was not found. Switched to create mode.',
            'previewing' => 'Previewing popup #',
            'validation_failed' => 'Please fix: ',
            'required_text_content' => 'Text format requires at least one title or body field.',
            'required_html_content' => 'HTML format requires at least one body field.',
            'required_image_url' => 'Image format requires a valid image URL.',
            'image_upload_invalid' => 'Uploaded images must be JPG, PNG, GIF, or WEBP, and the detected file type must match the extension.',
            'image_upload_too_large' => 'Uploaded images must be 4 MB or smaller.',
            'image_upload_dir_failed' => 'Unable to create the image upload directory. Check the addon directory permissions.',
            'image_upload_failed' => 'Image upload failed. Try again or check the addon directory permissions.',
            'invalid_button_url' => 'Button URL must be HTTP(S), root-relative, or a WHMCS PHP route.',
            'button_pair_required' => 'Button text and button URL must be filled together.',
            'field_rules' => 'Field Rules',
            'field_rules_body' => 'Text: title or body is required. HTML: body is required. Image: image URL or uploaded image is required; title, body, and button are optional. Legacy promotion/coupon/notice business categories are no longer used.',
        ],
    ];

    return $texts[$language][$key] ?? $texts['en'][$key] ?? $key;
}

function peakrack_popup_options(string $language, string $group): array
{
    $options = [
        'zh' => [
            'content_format' => [
                'text' => '文本',
                'html' => 'HTML',
                'image' => '图片',
            ],
            'display_mode' => [
                'modal' => '居中弹窗/遮罩',
                'poster' => '海报弹窗',
                'modal_plain' => '居中弹窗/无遮罩',
                'top_bar' => '顶部横幅',
                'bottom_bar' => '底部横幅',
                'corner_right' => '右下角浮窗',
                'corner_left' => '左下角浮窗',
                'right_side' => '右侧浮窗',
                'left_side' => '左侧浮窗',
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
            'hide_permanently' => [
                'disabled' => '不允许永久关闭',
                'close' => '点击关闭即永久隐藏',
                'checkbox' => '勾选后关闭才永久隐藏',
            ],
            'animation' => [
                'fade' => '淡入',
                'slide_left' => '从左滑入',
                'slide_right' => '从右滑入',
                'slide_top' => '从上滑入',
                'slide_bottom' => '从下滑入',
                'none' => '无动画',
            ],
            'due_mode' => [
                'disabled' => '不检查',
                'before' => '到期前',
                'on' => '到期当天',
                'after' => '到期后',
            ],
            'due_operator' => [
                'lt' => '小于',
                'lte' => '小于等于',
                'eq' => '等于',
                'gte' => '大于等于',
                'gt' => '大于',
            ],
            'audience' => [
                'all' => '所有访客',
                'guests' => '仅未登录访客',
                'clients' => '仅已登录客户',
                'client_groups' => '指定客户组',
            ],
        ],
        'en' => [
            'content_format' => [
                'text' => 'Text',
                'html' => 'HTML',
                'image' => 'Image',
            ],
            'display_mode' => [
                'modal' => 'Centered Modal with Overlay',
                'poster' => 'Poster Modal',
                'modal_plain' => 'Centered Modal without Overlay',
                'top_bar' => 'Top Banner',
                'bottom_bar' => 'Bottom Banner',
                'corner_right' => 'Bottom-right Popup',
                'corner_left' => 'Bottom-left Popup',
                'right_side' => 'Right-side Popup',
                'left_side' => 'Left-side Popup',
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
            'hide_permanently' => [
                'disabled' => 'Do not allow permanent close',
                'close' => 'Close button hides permanently',
                'checkbox' => 'Checkbox must be selected',
            ],
            'animation' => [
                'fade' => 'Fade In',
                'slide_left' => 'Slide From Left',
                'slide_right' => 'Slide From Right',
                'slide_top' => 'Slide From Top',
                'slide_bottom' => 'Slide From Bottom',
                'none' => 'No Animation',
            ],
            'due_mode' => [
                'disabled' => 'Disabled',
                'before' => 'Before Due Date',
                'on' => 'On Due Date',
                'after' => 'After Due Date',
            ],
            'due_operator' => [
                'lt' => 'Less Than',
                'lte' => 'Less Than or Equal',
                'eq' => 'Equal To',
                'gte' => 'Greater Than or Equal',
                'gt' => 'Greater Than',
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
            $uploadError = null;
            $uploadedImageUrl = peakrack_popup_uploaded_image_url($language, $uploadError);
            if ($uploadError !== null) {
                return [$uploadError, 'danger'];
            }
            if ($uploadedImageUrl !== '') {
                $data['image_url'] = $uploadedImageUrl;
            }

            $validationErrors = peakrack_popup_validate_data($data, $language);
            if ($validationErrors) {
                return [peakrack_popup_t($language, 'validation_failed') . implode(' ', $validationErrors), 'danger'];
            }

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

        if ($action === 'save_style') {
            $id = (int) ($_POST['id'] ?? 0);
            $data = peakrack_popup_style_data_from_post();

            if ($id > 0 && peakrack_popup_style_find($id)) {
                $data['updated_at'] = peakrackPopupNow();
                Capsule::table('mod_peakrack_popup_styles')->where('id', $id)->update($data);
                return [peakrack_popup_t($language, 'style_saved'), 'success'];
            }

            $now = peakrackPopupNow();
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $data['is_system'] = 0;
            Capsule::table('mod_peakrack_popup_styles')->insert($data);
            return [peakrack_popup_t($language, 'style_created'), 'success'];
        }

        if ($action === 'delete_style') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $uses = (int) Capsule::table('mod_peakrack_popups')->where('style_id', $id)->count();
                if ($uses > 0) {
                    Capsule::table('mod_peakrack_popup_styles')->where('id', $id)->update([
                        'enabled' => 0,
                        'updated_at' => peakrackPopupNow(),
                    ]);
                } else {
                    Capsule::table('mod_peakrack_popup_styles')->where('id', $id)->delete();
                }
            }
            return [peakrack_popup_t($language, 'style_deleted'), 'success'];
        }

        if ($action === 'toggle_popup') {
            $id = (int) ($_POST['id'] ?? 0);
            $row = peakrack_popup_find($id);
            if (!$row) {
                return [peakrack_popup_t($language, 'missing_toggle'), 'warning'];
            }

            Capsule::table('mod_peakrack_popups')->where('id', $id)->update([
                'enabled' => empty($row['enabled']) ? 1 : 0,
                'archived' => 0,
                'updated_at' => peakrackPopupNow(),
            ]);
            return [peakrack_popup_t($language, 'toggled'), 'success'];
        }

        if ($action === 'archive_popup') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Capsule::table('mod_peakrack_popups')->where('id', $id)->update([
                    'enabled' => 0,
                    'archived' => 1,
                    'updated_at' => peakrackPopupNow(),
                ]);
            }
            return [peakrack_popup_t($language, 'archived_action'), 'success'];
        }

        if ($action === 'restore_popup') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Capsule::table('mod_peakrack_popups')->where('id', $id)->update([
                    'archived' => 0,
                    'updated_at' => peakrackPopupNow(),
                ]);
            }
            return [peakrack_popup_t($language, 'restored'), 'success'];
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

function peakrack_popup_image_upload_max_bytes(): int
{
    return 4 * 1024 * 1024;
}

function peakrack_popup_image_upload_mimes(): array
{
    return [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
}

function peakrack_popup_image_upload_extension(string $name): string
{
    return strtolower(pathinfo($name, PATHINFO_EXTENSION));
}

function peakrack_popup_image_upload_mime(string $path): string
{
    if ($path === '' || !is_file($path)) {
        return '';
    }

    if (function_exists('finfo_open')) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        if ($info !== false) {
            $mime = finfo_file($info, $path);
            finfo_close($info);
            return is_string($mime) ? strtolower($mime) : '';
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($path);
        return is_string($mime) ? strtolower($mime) : '';
    }

    return '';
}

function peakrack_popup_image_upload_allowed(string $extension, string $mime): bool
{
    $extension = strtolower(ltrim($extension, '.'));
    $allowed = peakrack_popup_image_upload_mimes();

    if (!isset($allowed[$extension])) {
        return false;
    }

    if ($mime === '') {
        return true;
    }

    return $allowed[$extension] === strtolower($mime);
}

function peakrack_popup_uploaded_image_filename(string $originalName): string
{
    $extension = peakrack_popup_image_upload_extension($originalName);
    if ($extension === 'jpeg') {
        $extension = 'jpg';
    }

    try {
        $token = bin2hex(random_bytes(6));
    } catch (\Throwable $e) {
        $token = substr(sha1(uniqid('', true)), 0, 12);
    }

    return 'peakrack-popup-' . gmdate('Ymd-His') . '-' . $token . '.' . $extension;
}

function peakrack_popup_uploaded_image_url(string $language, ?string &$error = null): string
{
    $error = null;
    $file = $_FILES['image_upload'] ?? null;
    if (!is_array($file)) {
        return '';
    }

    $uploadCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadCode === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($uploadCode === UPLOAD_ERR_INI_SIZE || $uploadCode === UPLOAD_ERR_FORM_SIZE) {
        $error = peakrack_popup_t($language, 'image_upload_too_large');
        return '';
    }

    if ($uploadCode !== UPLOAD_ERR_OK || is_array($file['name'] ?? null) || is_array($file['tmp_name'] ?? null)) {
        $error = peakrack_popup_t($language, 'image_upload_failed');
        return '';
    }

    $name = (string) ($file['name'] ?? '');
    $tmpName = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($name === '' || $tmpName === '' || $size <= 0) {
        $error = peakrack_popup_t($language, 'image_upload_invalid');
        return '';
    }

    if ($size > peakrack_popup_image_upload_max_bytes()) {
        $error = peakrack_popup_t($language, 'image_upload_too_large');
        return '';
    }

    $extension = peakrack_popup_image_upload_extension($name);
    $mime = peakrack_popup_image_upload_mime($tmpName);
    if (!peakrack_popup_image_upload_allowed($extension, $mime)) {
        $error = peakrack_popup_t($language, 'image_upload_invalid');
        return '';
    }

    $imageDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images';
    if (!is_dir($imageDirectory) && !mkdir($imageDirectory, 0755, true) && !is_dir($imageDirectory)) {
        $error = peakrack_popup_t($language, 'image_upload_dir_failed');
        return '';
    }

    $filename = peakrack_popup_uploaded_image_filename($name);
    $target = $imageDirectory . DIRECTORY_SEPARATOR . $filename;

    if (!is_uploaded_file($tmpName) || !move_uploaded_file($tmpName, $target)) {
        $error = peakrack_popup_t($language, 'image_upload_failed');
        return '';
    }

    @chmod($target, 0644);

    return 'modules/addons/peakrack_popup/assets/images/' . $filename;
}

function peakrack_popup_data_from_post(): array
{
    $contentFormat = peakrackPopupValidChoice($_POST['content_format'] ?? 'text', ['text', 'html', 'image'], 'text');
    $displayMode = peakrackPopupValidChoice($_POST['display_mode'] ?? 'modal', ['modal', 'poster', 'modal_plain', 'top_bar', 'bottom_bar', 'corner_right', 'corner_left', 'left_side', 'right_side'], 'modal');
    $audience = peakrackPopupValidChoice($_POST['audience'] ?? 'all', ['all', 'guests', 'clients', 'client_groups'], 'all');
    $frequency = peakrackPopupValidChoice($_POST['frequency'] ?? 'daily', ['every_page', 'session', 'daily', 'once'], 'daily');
    $hidePermanently = peakrackPopupValidChoice($_POST['hide_permanently'] ?? 'disabled', ['disabled', 'close', 'checkbox'], 'disabled');
    $theme = peakrackPopupValidChoice($_POST['theme'] ?? 'blue', ['blue', 'green', 'orange', 'red', 'slate'], 'blue');
    $accentColor = peakrackPopupNormalizeAccentColor($_POST['accent_color'] ?? '', peakrackPopupThemeAccent($theme));
    $animation = peakrackPopupValidChoice($_POST['animation'] ?? 'fade', ['fade', 'slide_left', 'slide_right', 'slide_top', 'slide_bottom', 'none'], 'fade');
    $dueMode = peakrackPopupValidChoice($_POST['due_mode'] ?? 'disabled', ['disabled', 'before', 'on', 'after'], 'disabled');
    $dueOperator = peakrackPopupValidChoice($_POST['due_operator'] ?? 'lte', ['lt', 'lte', 'eq', 'gte', 'gt'], 'lte');

    $data = [
        'name' => peakrack_popup_limit(trim((string) ($_POST['name'] ?? 'Untitled popup')), 150),
        'enabled' => peakrackPopupBool($_POST['enabled'] ?? 0),
        'archived' => peakrackPopupBool($_POST['archived'] ?? 0),
        'type' => 'notice',
        'content_format' => $contentFormat,
        'display_mode' => $displayMode,
        'style_id' => max(0, (int) ($_POST['style_id'] ?? 0)),
        'audience' => $audience,
        'client_group_ids' => peakrackPopupNormalizeIntCsv($_POST['client_group_ids'] ?? ''),
        'page_rules' => peakrackPopupNormalizeLines($_POST['page_rules'] ?? '*'),
        'language_rules' => peakrackPopupNormalizeTokenCsv($_POST['language_rules'] ?? '', '/^(zh|en)$/i'),
        'days_of_week' => peakrackPopupNormalizeTokenCsv($_POST['days_of_week'] ?? '', '/^[1-7]$/'),
        'url_contains' => peakrackPopupNormalizeLines($_POST['url_contains'] ?? ''),
        'requires_unpaid_invoice' => peakrackPopupBool($_POST['requires_unpaid_invoice'] ?? 0),
        'active_product_ids' => peakrackPopupNormalizeIntCsv($_POST['active_product_ids'] ?? ''),
        'active_product_group_ids' => peakrackPopupNormalizeIntCsv($_POST['active_product_group_ids'] ?? ''),
        'active_server_ids' => peakrackPopupNormalizeIntCsv($_POST['active_server_ids'] ?? ''),
        'active_addon_ids' => peakrackPopupNormalizeIntCsv($_POST['active_addon_ids'] ?? ''),
        'active_tlds' => peakrackPopupNormalizeTokenCsv($_POST['active_tlds'] ?? ''),
        'missing_product_ids' => peakrackPopupNormalizeIntCsv($_POST['missing_product_ids'] ?? ''),
        'missing_addon_ids' => peakrackPopupNormalizeIntCsv($_POST['missing_addon_ids'] ?? ''),
        'missing_tlds' => peakrackPopupNormalizeTokenCsv($_POST['missing_tlds'] ?? ''),
        'frequency' => $frequency,
        'hide_permanently' => $hidePermanently,
        'theme' => $theme,
        'accent_color' => $accentColor,
        'popup_width' => peakrackPopupNormalizeCssSize($_POST['popup_width'] ?? ''),
        'popup_height' => peakrackPopupNormalizeCssSize($_POST['popup_height'] ?? ''),
        'animation' => $animation,
        'animation_ms' => max(0, min(5000, (int) ($_POST['animation_ms'] ?? 180))),
        'title' => peakrack_popup_limit(trim((string) ($_POST['title'] ?? '')), 200),
        'body' => trim((string) ($_POST['body'] ?? '')),
        'title_en' => peakrack_popup_limit(trim((string) ($_POST['title_en'] ?? '')), 200),
        'body_en' => trim((string) ($_POST['body_en'] ?? '')),
        'coupon_code' => '',
        'button_label' => peakrack_popup_limit(trim((string) ($_POST['button_label'] ?? '')), 100),
        'button_label_en' => peakrack_popup_limit(trim((string) ($_POST['button_label_en'] ?? '')), 100),
        'button_url' => trim((string) ($_POST['button_url'] ?? '')),
        'image_url' => trim((string) ($_POST['image_url'] ?? '')),
        'open_new_tab' => peakrackPopupBool($_POST['open_new_tab'] ?? 0),
        'priority' => max(-1000, min(1000, (int) ($_POST['priority'] ?? 0))),
        'delay_seconds' => max(0, min(60, (int) ($_POST['delay_seconds'] ?? 0))),
        'auto_close_seconds' => max(0, min(3600, (int) ($_POST['auto_close_seconds'] ?? 0))),
        'time_start' => peakrackPopupNormalizeTime($_POST['time_start'] ?? ''),
        'time_end' => peakrackPopupNormalizeTime($_POST['time_end'] ?? ''),
        'display_limit' => max(0, (int) ($_POST['display_limit'] ?? 0)),
        'per_client_display_limit' => max(0, (int) ($_POST['per_client_display_limit'] ?? 0)),
        'due_mode' => $dueMode,
        'due_operator' => $dueOperator,
        'due_days' => max(0, min(3650, (int) ($_POST['due_days'] ?? 0))),
        'due_product_ids' => peakrackPopupNormalizeIntCsv($_POST['due_product_ids'] ?? ''),
        'due_statuses' => peakrackPopupNormalizeTokenCsv($_POST['due_statuses'] ?? 'Active'),
        'start_at' => peakrackPopupDateTimeForDb($_POST['start_at'] ?? ''),
        'end_at' => peakrackPopupDateTimeForDb($_POST['end_at'] ?? ''),
    ];

    return $data;
}

function peakrack_popup_validate_data(array $data, string $language): array
{
    $errors = [];
    $contentFormat = (string) ($data['content_format'] ?? 'text');
    $title = trim((string) ($data['title'] ?? ''));
    $titleEn = trim((string) ($data['title_en'] ?? ''));
    $body = trim((string) ($data['body'] ?? ''));
    $bodyEn = trim((string) ($data['body_en'] ?? ''));
    $buttonLabel = trim((string) ($data['button_label'] ?? ''));
    $buttonLabelEn = trim((string) ($data['button_label_en'] ?? ''));
    $buttonUrl = trim((string) ($data['button_url'] ?? ''));
    $imageUrl = trim((string) ($data['image_url'] ?? ''));

    if ($contentFormat === 'text' && $title === '' && $titleEn === '' && $body === '' && $bodyEn === '') {
        $errors[] = peakrack_popup_t($language, 'required_text_content');
    }

    if ($contentFormat === 'html' && $body === '' && $bodyEn === '') {
        $errors[] = peakrack_popup_t($language, 'required_html_content');
    }

    if ($contentFormat === 'image' && ($imageUrl === '' || peakrackPopupSafeImageUrl($imageUrl) === '')) {
        $errors[] = peakrack_popup_t($language, 'required_image_url');
    }

    if ($buttonUrl !== '' && peakrackPopupSafeLink($buttonUrl) === '') {
        $errors[] = peakrack_popup_t($language, 'invalid_button_url');
    }

    if (($buttonUrl !== '' && $buttonLabel === '' && $buttonLabelEn === '') || ($buttonUrl === '' && ($buttonLabel !== '' || $buttonLabelEn !== ''))) {
        $errors[] = peakrack_popup_t($language, 'button_pair_required');
    }

    return array_values(array_unique($errors));
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
        'archived' => 0,
        'type' => 'notice',
        'content_format' => 'text',
        'display_mode' => 'modal',
        'style_id' => 0,
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
        'hide_permanently' => 'disabled',
        'theme' => 'blue',
        'accent_color' => '#2563eb',
        'popup_width' => '',
        'popup_height' => '',
        'animation' => 'fade',
        'animation_ms' => 180,
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
        'time_start' => '',
        'time_end' => '',
        'display_limit' => 0,
        'per_client_display_limit' => 0,
        'due_mode' => 'disabled',
        'due_operator' => 'lte',
        'due_days' => 0,
        'due_product_ids' => '',
        'due_statuses' => 'Active',
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
            ->orderBy('archived', 'asc')
            ->orderBy('enabled', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->all()
    );
}

function peakrack_popup_all_styles(): array
{
    if (!peakrackPopupTableReady('mod_peakrack_popup_styles')) {
        return [];
    }

    return array_map(
        static fn($row): array => peakrackPopupObjectToArray($row),
        Capsule::table('mod_peakrack_popup_styles')
            ->orderBy('enabled', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->all()
    );
}

function peakrack_popup_style_find(int $id): ?array
{
    if ($id <= 0 || !peakrackPopupTableReady('mod_peakrack_popup_styles')) {
        return null;
    }

    $row = Capsule::table('mod_peakrack_popup_styles')->where('id', $id)->first();
    return $row ? peakrackPopupObjectToArray($row) : null;
}

function peakrack_popup_style_options(array $styles, string $language): array
{
    $options = ['0' => peakrack_popup_t($language, 'style_fallback')];
    foreach ($styles as $style) {
        if (empty($style['enabled'])) {
            continue;
        }
        $options[(string) (int) $style['id']] = (string) ($style['name'] ?? ('Style #' . (int) $style['id']));
    }

    return $options;
}

function peakrack_popup_style_usage_counts(): array
{
    $counts = [];
    if (!peakrackPopupTableReady('mod_peakrack_popups')) {
        return $counts;
    }

    try {
        $rows = Capsule::table('mod_peakrack_popups')
            ->select('style_id')
            ->selectRaw('COUNT(*) as total')
            ->where('style_id', '>', 0)
            ->groupBy('style_id')
            ->get()
            ->all();
    } catch (\Throwable $e) {
        return $counts;
    }

    foreach ($rows as $row) {
        $row = peakrackPopupObjectToArray($row);
        $counts[(int) ($row['style_id'] ?? 0)] = (int) ($row['total'] ?? 0);
    }

    return $counts;
}

function peakrack_popup_default_style_form(): array
{
    return [
        'id' => 0,
        'name' => '',
        'slug' => '',
        'description' => '',
        'enabled' => 1,
        'is_system' => 0,
        'display_mode' => 'modal',
        'theme' => 'blue',
        'accent_color' => '#2563eb',
        'popup_width' => '',
        'popup_height' => '',
        'animation' => 'fade',
        'animation_ms' => 180,
        'custom_css' => '',
        'html_template' => '',
        'sort_order' => 100,
    ];
}

function peakrack_popup_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?: '';
    $value = trim($value, '-');
    return $value !== '' ? substr($value, 0, 80) : 'style-' . date('YmdHis');
}

function peakrack_popup_style_data_from_post(): array
{
    $theme = peakrackPopupValidChoice($_POST['theme'] ?? 'blue', ['blue', 'green', 'orange', 'red', 'slate'], 'blue');

    return [
        'name' => peakrack_popup_limit(trim((string) ($_POST['name'] ?? 'Untitled style')), 150),
        'slug' => peakrack_popup_slug((string) ($_POST['slug'] ?? ($_POST['name'] ?? 'style'))),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'enabled' => peakrackPopupBool($_POST['enabled'] ?? 0),
        'display_mode' => peakrackPopupValidChoice($_POST['display_mode'] ?? 'modal', ['modal', 'poster', 'modal_plain', 'top_bar', 'bottom_bar', 'corner_right', 'corner_left', 'left_side', 'right_side'], 'modal'),
        'theme' => $theme,
        'accent_color' => peakrackPopupNormalizeAccentColor($_POST['accent_color'] ?? '', peakrackPopupThemeAccent($theme)),
        'popup_width' => peakrackPopupNormalizeCssSize($_POST['popup_width'] ?? ''),
        'popup_height' => peakrackPopupNormalizeCssSize($_POST['popup_height'] ?? ''),
        'animation' => peakrackPopupValidChoice($_POST['animation'] ?? 'fade', ['fade', 'slide_left', 'slide_right', 'slide_top', 'slide_bottom', 'none'], 'fade'),
        'animation_ms' => max(0, min(5000, (int) ($_POST['animation_ms'] ?? 180))),
        'custom_css' => trim((string) ($_POST['custom_css'] ?? '')),
        'html_template' => trim((string) ($_POST['html_template'] ?? '')),
        'sort_order' => max(-1000, min(1000, (int) ($_POST['sort_order'] ?? 100))),
    ];
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

function peakrack_popup_restriction_summary(array $row, string $language): array
{
    $labels = [
        'zh' => [
            'audience' => '受众',
            'pages' => '页面',
            'days' => '星期',
            'languages' => '语言',
            'unpaid' => '未付账单',
            'products' => '产品',
            'groups' => '产品组',
            'servers' => '服务器',
            'addons' => '附加服务',
            'tlds' => '域名',
            'no_products' => '无产品',
            'no_addons' => '无附加服务',
            'no_tlds' => '无域名',
            'due' => '到期',
        ],
        'en' => [
            'audience' => 'Audience',
            'pages' => 'Pages',
            'days' => 'Days',
            'languages' => 'Languages',
            'unpaid' => 'Unpaid',
            'products' => 'Products',
            'groups' => 'Groups',
            'servers' => 'Servers',
            'addons' => 'Addons',
            'tlds' => 'TLDs',
            'no_products' => 'No Products',
            'no_addons' => 'No Addons',
            'no_tlds' => 'No TLDs',
            'due' => 'Due',
        ],
    ];
    $l = $labels[$language] ?? $labels['en'];
    $summary = [];
    $add = static function (string $label, mixed $value) use (&$summary): void {
        $value = trim(str_replace("\n", ', ', (string) $value));
        if ($value !== '') {
            $summary[] = [$label, $value];
        }
    };

    $add($l['audience'], $row['audience'] ?? 'all');
    $add($l['pages'], $row['page_rules'] ?? '*');
    $add($l['days'], $row['days_of_week'] ?? '');
    $add($l['languages'], $row['language_rules'] ?? '');
    if (!empty($row['requires_unpaid_invoice'])) {
        $summary[] = [$l['unpaid'], 'Yes'];
    }
    $add($l['products'], $row['active_product_ids'] ?? '');
    $add($l['groups'], $row['active_product_group_ids'] ?? '');
    $add($l['servers'], $row['active_server_ids'] ?? '');
    $add($l['addons'], $row['active_addon_ids'] ?? '');
    $add($l['tlds'], $row['active_tlds'] ?? '');
    $add($l['no_products'], $row['missing_product_ids'] ?? '');
    $add($l['no_addons'], $row['missing_addon_ids'] ?? '');
    $add($l['no_tlds'], $row['missing_tlds'] ?? '');
    if (($row['due_mode'] ?? 'disabled') !== 'disabled') {
        $summary[] = [$l['due'], trim((string) ($row['due_mode'] ?? '') . ' ' . (string) ($row['due_operator'] ?? '') . ' ' . (string) ($row['due_days'] ?? ''))];
    }

    return $summary;
}

function peakrack_popup_hidden_language(string $language): string
{
    return '<input type="hidden" name="prp_admin_lang" value="' . peakrackPopupE($language) . '">';
}

function peakrack_popup_render_admin(array $editing, array $popups, array $styles, string $message, string $messageType, string $language, ?array $preview = null): string
{
    $token = peakrack_popup_admin_token_field();
    $hiddenLanguage = peakrack_popup_hidden_language($language);
    $isEdit = (int) ($editing['id'] ?? 0) > 0;
    $totalViews = array_sum(array_map(static fn(array $row): int => (int) ($row['view_count'] ?? 0), $popups));
    $totalClicks = array_sum(array_map(static fn(array $row): int => (int) ($row['click_count'] ?? 0), $popups));
    $enabledCount = count(array_filter($popups, static fn(array $row): bool => !empty($row['enabled']) && empty($row['archived'])));
    $formAction = peakrack_popup_admin_url($language, $isEdit ? ['edit' => (int) $editing['id']] : []);
    $baseAction = peakrack_popup_admin_url($language);
    $t = static fn(string $key): string => peakrack_popup_t($language, $key);
    $contentFormatLabels = peakrack_popup_options($language, 'content_format');
    $modeLabels = peakrack_popup_options($language, 'display_mode');
    $styleOptions = peakrack_popup_style_options($styles, $language);
    $styleNames = [];
    foreach ($styles as $style) {
        $styleNames[(int) ($style['id'] ?? 0)] = (string) ($style['name'] ?? '');
    }
    $frequencyLabels = peakrack_popup_options($language, 'frequency');
    $audienceLabels = peakrack_popup_options($language, 'audience');
    $statusFilter = peakrackPopupValidChoice($_GET['status'] ?? 'active', ['active', 'inactive', 'archive', 'all'], 'active');
    $activeCount = count(array_filter($popups, static fn(array $row): bool => !empty($row['enabled']) && empty($row['archived'])));
    $inactiveCount = count(array_filter($popups, static fn(array $row): bool => empty($row['enabled']) && empty($row['archived'])));
    $archiveCount = count(array_filter($popups, static fn(array $row): bool => !empty($row['archived'])));
    $visiblePopups = array_values(array_filter($popups, static function (array $row) use ($statusFilter): bool {
        return match ($statusFilter) {
            'inactive' => empty($row['enabled']) && empty($row['archived']),
            'archive' => !empty($row['archived']),
            'all' => true,
            default => !empty($row['enabled']) && empty($row['archived']),
        };
    }));

    ob_start();
    ?>
    <style>
        .prp-admin{display:flex;flex-direction:column;max-width:1420px;color:#1f2937}
        .prp-admin *{box-sizing:border-box}
        .prp-head{display:flex;justify-content:space-between;gap:18px;align-items:center;margin:0 0 18px;padding:16px 18px;border-radius:6px;background:linear-gradient(90deg,#0f4f86,#2777b8);color:#fff;box-shadow:0 8px 20px rgba(15,79,134,.16)}
        .prp-eyebrow{margin-bottom:4px;color:#b9e63a;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
        .prp-head h2{margin:0 0 5px;font-size:22px;font-weight:750;color:#fff}
        .prp-head p{margin:0;max-width:760px;color:rgba(255,255,255,.78);font-size:13px;line-height:1.5}
        .prp-head-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}
        .prp-lang{display:inline-flex;border:1px solid rgba(255,255,255,.28);border-radius:6px;background:rgba(255,255,255,.12);overflow:hidden}
        .prp-lang a{display:inline-flex;align-items:center;padding:7px 10px;color:#fff;text-decoration:none;font-size:12px;font-weight:700}
        .prp-lang a.active{background:#fff;color:#155e9d}
        .prp-main-nav{display:flex;gap:8px;margin:-8px 0 18px}
        .prp-main-nav a{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#1f4f7a;text-decoration:none;font-weight:700}
        .prp-main-nav a.active{background:#1f6fae;border-color:#1f6fae;color:#fff}
        .prp-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
        .prp-stat{border:1px solid #d8e0ea;border-radius:6px;background:#fff;padding:14px 16px}
        .prp-stat-label{display:block;color:#6b7280;font-size:12px;font-weight:700;text-transform:uppercase}
        .prp-stat-value{display:block;margin-top:5px;font-size:21px;font-weight:700;color:#111827}
        .prp-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:18px;align-items:start;order:3}
        .prp-card{border:1px solid #d8e0ea;border-radius:6px;background:#fff;margin-bottom:18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
        .prp-list-card{order:2;border-color:#2f80c2}
        .prp-card-head{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:14px 16px;border-bottom:1px solid #e7edf3;background:#fbfcfe}
        .prp-list-card>.prp-card-head{background:#2f7db9;color:#fff;border-bottom:0}
        .prp-list-card>.prp-card-head .prp-card-title{color:#fff}
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
        .prp-field.prp-required label::after{content:" *";color:#dc2626}
        .prp-field-rule{margin-top:6px;color:#0369a1;font-size:12px;line-height:1.45}
        .prp-field-guide{grid-column:1/-1;padding:10px 12px;border:1px solid #bfdbfe;border-radius:6px;background:#eff6ff;color:#1e3a8a}
        .prp-field-guide strong{display:block;margin-bottom:4px}
        .prp-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .prp-note{margin:0;color:#64748b;font-size:12px;line-height:1.55}
        .prp-table-wrap{overflow-x:auto}
        .prp-table{width:100%;border-collapse:collapse;min-width:1120px}
        .prp-table th,.prp-table td{border-bottom:1px solid #e5e7eb;padding:11px 8px;text-align:left;vertical-align:top}
        .prp-table th{font-size:12px;color:#64748b;text-transform:uppercase;background:#f8fafc}
        .prp-badge{display:inline-block;border-radius:999px;padding:3px 8px;font-size:12px;font-weight:700}
        .prp-badge-on{background:#dcfce7;color:#166534}
        .prp-badge-off{background:#f1f5f9;color:#475569}
        .prp-muted{color:#64748b;font-size:12px;line-height:1.45}
        .prp-inline-form{display:inline}
        .prp-danger{color:#b91c1c}
        .prp-tabs{display:flex;flex-wrap:wrap;gap:0;margin:0 0 12px;border-bottom:1px solid #dbe4ef}
        .prp-tabs a{display:inline-flex;align-items:center;gap:7px;padding:10px 14px;border:1px solid transparent;border-bottom:0;border-radius:5px 5px 0 0;color:#16476d;text-decoration:none;font-weight:700;background:transparent}
        .prp-tabs a.active{border-color:#dbe4ef;background:#fff;color:#111827}
        .prp-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 7px;border-radius:999px;background:#8dc5f4;color:#fff;font-size:12px;font-weight:800}
        .prp-rule-tags{display:flex;flex-wrap:wrap;gap:6px;max-width:520px}
        .prp-rule-tag{display:inline-flex;align-items:center;gap:4px;max-width:100%;padding:3px 6px;border-radius:4px;background:#fff1f5;color:#bf1744;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:11px;line-height:1.35;box-shadow:0 1px 4px rgba(191,23,68,.12)}
        .prp-rule-tag strong{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#3f3f46}
        .prp-icon-actions{display:flex;flex-wrap:wrap;gap:5px;align-items:center}
        .prp-icon-actions .btn{width:30px;height:28px;display:inline-flex;align-items:center;justify-content:center;padding:0}
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

        <div class="prp-main-nav">
            <a class="active" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language)); ?>"><i class="fa fa-list"></i> <?php echo peakrackPopupE($t('nav_popups')); ?></a>
            <a href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['view' => 'styles'])); ?>"><i class="fa fa-eye"></i> <?php echo peakrackPopupE($t('nav_styles')); ?></a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo peakrackPopupE($messageType); ?>"><?php echo peakrackPopupE($message); ?></div>
        <?php endif; ?>

        <?php if ($preview): ?>
            <div class="alert alert-info"><?php echo peakrackPopupE($t('previewing') . (int) ($preview['id'] ?? 0)); ?></div>
            <?php
            $previewPopup = $preview;
            $previewPopup['enabled'] = 1;
            $previewPopup['archived'] = 0;
            $previewPopup['frequency'] = 'every_page';
            $previewPopup['delay_seconds'] = 0;
            $previewPopup['auto_close_seconds'] = 0;
            echo peakrackPopupRender($previewPopup, ['language' => $language, 'WEB_ROOT' => '', '_peakrack_preview' => true]);
            ?>
        <?php endif; ?>

        <div class="prp-stats">
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_total')); ?></span><span class="prp-stat-value"><?php echo count($popups); ?></span></div>
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_enabled')); ?></span><span class="prp-stat-value"><?php echo $enabledCount; ?></span></div>
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_views')); ?></span><span class="prp-stat-value"><?php echo $totalViews; ?></span></div>
            <div class="prp-stat"><span class="prp-stat-label"><?php echo peakrackPopupE($t('stats_clicks')); ?></span><span class="prp-stat-value"><?php echo $totalClicks; ?></span></div>
        </div>

        <div class="prp-grid">
            <div>
                <form method="post" action="<?php echo peakrackPopupE($formAction); ?>" enctype="multipart/form-data">
                    <?php echo $token . $hiddenLanguage; ?>
                    <input type="hidden" name="prp_action" value="save_popup">
                    <input type="hidden" name="id" value="<?php echo (int) ($editing['id'] ?? 0); ?>">
                    <input type="hidden" name="archived" value="<?php echo (int) ($editing['archived'] ?? 0); ?>">

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
                                <input type="hidden" name="type" value="notice">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('content_format')); ?></label>
                                    <?php echo peakrack_popup_select('content_format', (string) ($editing['content_format'] ?? 'text'), peakrack_popup_options($language, 'content_format')); ?>
                                    <div class="help"><?php echo peakrackPopupE($t('content_format_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('popup_style')); ?></label>
                                    <?php echo peakrack_popup_select('style_id', (string) (int) ($editing['style_id'] ?? 0), $styleOptions); ?>
                                    <div class="help"><?php echo peakrackPopupE($t('popup_style_help')); ?></div>
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
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('hide_permanently')); ?></label>
                                    <?php echo peakrack_popup_select('hide_permanently', (string) ($editing['hide_permanently'] ?? 'disabled'), peakrack_popup_options($language, 'hide_permanently')); ?>
                                </div>
                                <div class="prp-field prp-field-guide">
                                    <strong><?php echo peakrackPopupE($t('field_rules')); ?></strong>
                                    <span data-format-guide><?php echo peakrackPopupE($t('field_rules_body')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="prp-card">
                        <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('size_animation')); ?></h3></div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('popup_width')); ?></label>
                                    <input class="form-control" type="text" name="popup_width" value="<?php echo peakrackPopupE($editing['popup_width'] ?? ''); ?>" placeholder="620px">
                                    <div class="help"><?php echo peakrackPopupE($t('size_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('popup_height')); ?></label>
                                    <input class="form-control" type="text" name="popup_height" value="<?php echo peakrackPopupE($editing['popup_height'] ?? ''); ?>" placeholder="auto">
                                    <div class="help"><?php echo peakrackPopupE($t('size_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('animation')); ?></label>
                                    <?php echo peakrack_popup_select('animation', (string) ($editing['animation'] ?? 'fade'), peakrack_popup_options($language, 'animation')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('animation_ms')); ?></label>
                                    <input class="form-control" type="number" min="0" max="5000" name="animation_ms" value="<?php echo (int) ($editing['animation_ms'] ?? 180); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="prp-card">
                        <div class="prp-card-head">
                            <div>
                                <h3 class="prp-card-title"><?php echo peakrackPopupE($t('popup_content')); ?></h3>
                                <p class="prp-card-desc"><?php echo peakrackPopupE($t('content_help')); ?></p>
                                <p class="prp-card-desc"><?php echo peakrackPopupE($t('raw_html_note')); ?></p>
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
                                    <label><?php echo peakrackPopupE($t('image_url')); ?></label>
                                    <input class="form-control" type="text" name="image_url" value="<?php echo peakrackPopupE($editing['image_url'] ?? ''); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('image_upload')); ?></label>
                                    <input class="form-control" type="file" name="image_upload" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="help"><?php echo peakrackPopupE($t('image_upload_help')); ?></div>
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
                                    <label><?php echo peakrackPopupE($t('language_rules')); ?></label>
                                    <input class="form-control" type="text" name="language_rules" value="<?php echo peakrackPopupE($editing['language_rules'] ?? ''); ?>" placeholder="zh,en">
                                    <div class="help"><?php echo peakrackPopupE($t('language_rules_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('days_of_week')); ?></label>
                                    <input class="form-control" type="text" name="days_of_week" value="<?php echo peakrackPopupE($editing['days_of_week'] ?? ''); ?>" placeholder="1,2,3,4,5">
                                    <div class="help"><?php echo peakrackPopupE($t('days_help')); ?></div>
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

                    <div class="prp-card">
                        <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('advanced_restrictions')); ?></h3></div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('url_contains')); ?></label>
                                    <textarea class="form-control" name="url_contains" rows="3"><?php echo peakrackPopupE($editing['url_contains'] ?? ''); ?></textarea>
                                    <div class="help"><?php echo peakrackPopupE($t('url_contains_help')); ?></div>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('timing_limits')); ?></label>
                                    <div class="prp-form-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));gap:10px">
                                        <input class="form-control" type="time" name="time_start" value="<?php echo peakrackPopupE(peakrackPopupNormalizeTime($editing['time_start'] ?? '')); ?>" title="<?php echo peakrackPopupE($t('time_start')); ?>">
                                        <input class="form-control" type="time" name="time_end" value="<?php echo peakrackPopupE(peakrackPopupNormalizeTime($editing['time_end'] ?? '')); ?>" title="<?php echo peakrackPopupE($t('time_end')); ?>">
                                        <input class="form-control" type="number" min="0" name="display_limit" value="<?php echo (int) ($editing['display_limit'] ?? 0); ?>" placeholder="<?php echo peakrackPopupE($t('display_limit')); ?>">
                                        <input class="form-control" type="number" min="0" name="per_client_display_limit" value="<?php echo (int) ($editing['per_client_display_limit'] ?? 0); ?>" placeholder="<?php echo peakrackPopupE($t('per_client_display_limit')); ?>">
                                    </div>
                                    <div class="help"><?php echo peakrackPopupE($t('limit_help')); ?></div>
                                </div>
                                <div class="prp-field" style="display:flex;align-items:center">
                                    <label style="margin:0;font-weight:700"><input type="checkbox" name="requires_unpaid_invoice" value="1" <?php echo !empty($editing['requires_unpaid_invoice']) ? 'checked' : ''; ?>> <?php echo peakrackPopupE($t('requires_unpaid_invoice')); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="prp-card">
                        <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('catalog_restrictions')); ?></h3></div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid-3">
                                <?php foreach (['active_product_ids', 'active_product_group_ids', 'active_server_ids', 'active_addon_ids', 'active_tlds', 'missing_product_ids', 'missing_addon_ids', 'missing_tlds'] as $field): ?>
                                    <div class="prp-field">
                                        <label><?php echo peakrackPopupE($t($field)); ?></label>
                                        <input class="form-control" type="text" name="<?php echo peakrackPopupE($field); ?>" value="<?php echo peakrackPopupE($editing[$field] ?? ''); ?>">
                                        <div class="help"><?php echo peakrackPopupE($t('ids_help')); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="prp-card">
                        <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('due_restrictions')); ?></h3></div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('due_mode')); ?></label>
                                    <?php echo peakrack_popup_select('due_mode', (string) ($editing['due_mode'] ?? 'disabled'), peakrack_popup_options($language, 'due_mode')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('due_operator')); ?></label>
                                    <?php echo peakrack_popup_select('due_operator', (string) ($editing['due_operator'] ?? 'lte'), peakrack_popup_options($language, 'due_operator')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('due_days')); ?></label>
                                    <input class="form-control" type="number" min="0" max="3650" name="due_days" value="<?php echo (int) ($editing['due_days'] ?? 0); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('due_product_ids')); ?></label>
                                    <input class="form-control" type="text" name="due_product_ids" value="<?php echo peakrackPopupE($editing['due_product_ids'] ?? ''); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('due_statuses')); ?></label>
                                    <input class="form-control" type="text" name="due_statuses" value="<?php echo peakrackPopupE($editing['due_statuses'] ?? 'Active'); ?>">
                                    <div class="help"><?php echo peakrackPopupE($t('due_help')); ?></div>
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

        <div class="prp-card prp-list-card">
            <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('list')); ?></h3></div>
            <div class="prp-card-body">
                <div class="prp-tabs">
                    <a class="<?php echo $statusFilter === 'active' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['status' => 'active'])); ?>">Active <span class="prp-count"><?php echo $activeCount; ?></span></a>
                    <a class="<?php echo $statusFilter === 'inactive' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['status' => 'inactive'])); ?>">Inactive <span class="prp-count"><?php echo $inactiveCount; ?></span></a>
                    <a class="<?php echo $statusFilter === 'archive' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['status' => 'archive'])); ?>">Archive <span class="prp-count"><?php echo $archiveCount; ?></span></a>
                    <a class="<?php echo $statusFilter === 'all' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['status' => 'all'])); ?>">All <span class="prp-count"><?php echo count($popups); ?></span></a>
                </div>
                <div class="prp-table-wrap">
                    <table class="prp-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo peakrackPopupE($t('name')); ?></th>
                            <th><?php echo peakrackPopupE($t('rule_column')); ?></th>
                            <th><?php echo peakrackPopupE($t('delay_seconds')); ?></th>
                            <th><?php echo peakrackPopupE($t('stats_views')); ?></th>
                            <th><?php echo peakrackPopupE($t('start_at')); ?></th>
                            <th><?php echo peakrackPopupE($t('end_at')); ?></th>
                            <th><?php echo peakrackPopupE($t('actions')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$visiblePopups): ?>
                            <tr><td colspan="8" class="prp-muted"><?php echo peakrackPopupE($t('empty')); ?></td></tr>
                        <?php endif; ?>
                        <?php foreach ($visiblePopups as $row): ?>
                            <?php $isArchivedRow = !empty($row['archived']); ?>
                            <tr>
                                <td><?php echo (int) $row['id']; ?></td>
                                <td>
                                    <strong><?php echo peakrackPopupE($row['name'] ?? ''); ?></strong><br>
                                    <span class="prp-badge <?php echo !$isArchivedRow && !empty($row['enabled']) ? 'prp-badge-on' : 'prp-badge-off'; ?>"><?php echo peakrackPopupE($isArchivedRow ? $t('archived') : (!empty($row['enabled']) ? $t('enabled') : $t('disabled'))); ?></span><br>
                                    <span class="prp-muted"><?php echo peakrackPopupE($contentFormatLabels[$row['content_format'] ?? 'text'] ?? (string) ($row['content_format'] ?? 'text')); ?> / <?php echo peakrackPopupE($styleNames[(int) ($row['style_id'] ?? 0)] ?? ($modeLabels[$row['display_mode'] ?? ''] ?? (string) ($row['display_mode'] ?? ''))); ?></span>
                                </td>
                                <td>
                                    <div class="prp-rule-tags">
                                        <?php foreach (peakrack_popup_restriction_summary($row, $language) as [$label, $value]): ?>
                                            <span class="prp-rule-tag"><strong><?php echo peakrackPopupE($label); ?>:</strong> <?php echo peakrackPopupE($value); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td><?php echo (int) ($row['delay_seconds'] ?? 0); ?></td>
                                <td><?php echo (int) ($row['view_count'] ?? 0); ?></td>
                                <td><?php echo peakrackPopupE($row['start_at'] ?: 'Immediate'); ?></td>
                                <td><?php echo peakrackPopupE($row['end_at'] ?: 'Never'); ?></td>
                                <td>
                                    <div class="prp-icon-actions">
                                        <a class="btn btn-xs btn-default" title="<?php echo peakrackPopupE($t('preview')); ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['preview' => (int) $row['id']])); ?>"><i class="fa fa-eye"></i></a>
                                        <a class="btn btn-xs btn-default" title="<?php echo peakrackPopupE($t('edit')); ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['edit' => (int) $row['id']])); ?>"><i class="fa fa-pencil"></i></a>
                                        <?php if (!$isArchivedRow): ?>
                                            <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>">
                                                <?php echo $token . $hiddenLanguage; ?>
                                                <input type="hidden" name="prp_action" value="toggle_popup">
                                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                                <button class="btn btn-xs btn-default" title="<?php echo peakrackPopupE(!empty($row['enabled']) ? $t('disable') : $t('enable')); ?>" type="submit"><i class="fa fa-ban"></i></button>
                                            </form>
                                            <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>">
                                                <?php echo $token . $hiddenLanguage; ?>
                                                <input type="hidden" name="prp_action" value="archive_popup">
                                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                                <button class="btn btn-xs btn-default" title="<?php echo peakrackPopupE($t('archive')); ?>" type="submit"><i class="fa fa-folder-open"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>">
                                                <?php echo $token . $hiddenLanguage; ?>
                                                <input type="hidden" name="prp_action" value="restore_popup">
                                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                                <button class="btn btn-xs btn-default" title="<?php echo peakrackPopupE($t('restore')); ?>" type="submit"><i class="fa fa-folder"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>">
                                            <?php echo $token . $hiddenLanguage; ?>
                                            <input type="hidden" name="prp_action" value="reset_stats">
                                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                            <button class="btn btn-xs btn-default" title="<?php echo peakrackPopupE($t('reset')); ?>" type="submit"><i class="fa fa-refresh"></i></button>
                                        </form>
                                        <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>" onsubmit="return confirm('<?php echo peakrackPopupE($t('delete_confirm')); ?>');">
                                            <?php echo $token . $hiddenLanguage; ?>
                                            <input type="hidden" name="prp_action" value="delete_popup">
                                            <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                            <button class="btn btn-xs btn-default prp-danger" title="<?php echo peakrackPopupE($t('delete')); ?>" type="submit"><i class="fa fa-times"></i></button>
                                        </form>
                                    </div>
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
        (function () {
            var form = document.querySelector('.prp-admin form[action]');
            if (!form) {
                return;
            }

            var format = form.querySelector('[name="content_format"]');
            var image = form.querySelector('[name="image_url"]');
            var buttonUrl = form.querySelector('[name="button_url"]');
            var buttonLabels = [form.querySelector('[name="button_label"]'), form.querySelector('[name="button_label_en"]')].filter(Boolean);
            var bodyFields = [form.querySelector('[name="body"]'), form.querySelector('[name="body_en"]')].filter(Boolean);
            var titleFields = [form.querySelector('[name="title"]'), form.querySelector('[name="title_en"]')].filter(Boolean);
            var guide = form.querySelector('[data-format-guide]');
            var copy = <?php echo json_encode($language === 'zh' ? [
                'text' => 'Text：标题或正文必填。',
                'html' => 'HTML：正文必填，并按可信管理员 HTML 原样渲染。',
                'image' => 'Image：图片 URL 必填，标题、正文和按钮可选。',
                'htmlBody' => 'HTML 格式必填',
                'imageBody' => '可选图片说明',
                'textBody' => '标题或正文必填',
                'imageTitle' => '可选标题',
                'buttonUrl' => '填写按钮文字时必填',
                'buttonDefault' => 'cart.php 或 https://example.com',
            ] : [
                'text' => 'Text: title or body is required.',
                'html' => 'HTML: body is required and rendered as trusted admin HTML.',
                'image' => 'Image: image URL is required; title, body, and button are optional.',
                'htmlBody' => 'Required for HTML format',
                'imageBody' => 'Optional caption/body',
                'textBody' => 'Title or body is required',
                'imageTitle' => 'Optional title',
                'buttonUrl' => 'Required when button text is filled',
                'buttonDefault' => 'cart.php or https://example.com',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

            function wrapper(input) {
                return input ? input.closest('.prp-field') : null;
            }

            function required(input, enabled) {
                if (!input) {
                    return;
                }
                input.required = !!enabled;
                var field = wrapper(input);
                if (field) {
                    field.classList.toggle('prp-required', !!enabled);
                }
            }

            function syncFieldRules() {
                if (!format) {
                    return;
                }

                var isImage = format.value === 'image';
                var isHtml = format.value === 'html';
                var isText = format.value === 'text';

                required(image, isImage);
                bodyFields.forEach(function (field) {
                    required(field, false);
                    field.placeholder = isHtml ? copy.htmlBody : (isImage ? copy.imageBody : copy.textBody);
                });
                titleFields.forEach(function (field) {
                    required(field, false);
                    field.placeholder = isImage ? copy.imageTitle : copy.textBody;
                });

                if (buttonUrl) {
                    buttonUrl.placeholder = buttonLabels.some(function (field) { return field.value.trim() !== ''; }) ? copy.buttonUrl : copy.buttonDefault;
                }

                if (guide) {
                    var parts = [];
                    if (isText) {
                        parts.push(copy.text);
                    }
                    if (isHtml) {
                        parts.push(copy.html);
                    }
                    if (isImage) {
                        parts.push(copy.image);
                    }
                    guide.textContent = parts.join(' ');
                }
            }

            [format, image, buttonUrl].concat(buttonLabels, bodyFields, titleFields).forEach(function (input) {
                if (input) {
                    input.addEventListener('input', syncFieldRules);
                    input.addEventListener('change', syncFieldRules);
                }
            });
            syncFieldRules();
        })();
        </script>
    </div>
    <?php
    return (string) ob_get_clean();
}

function peakrack_popup_render_styles_admin(array $editing, array $styles, string $message, string $messageType, string $language): string
{
    $token = peakrack_popup_admin_token_field();
    $hiddenLanguage = peakrack_popup_hidden_language($language);
    $isEdit = (int) ($editing['id'] ?? 0) > 0;
    $formAction = peakrack_popup_admin_url($language, ['view' => 'styles'] + ($isEdit ? ['style' => (int) $editing['id']] : []));
    $baseAction = peakrack_popup_admin_url($language, ['view' => 'styles']);
    $t = static fn(string $key): string => peakrack_popup_t($language, $key);
    $modeLabels = peakrack_popup_options($language, 'display_mode');
    $themeLabels = peakrack_popup_options($language, 'theme');
    $usage = peakrack_popup_style_usage_counts();
    $previewStyleId = (int) ($editing['id'] ?? 0);
    $previewPopup = [
        'id' => 990001,
        'enabled' => 1,
        'archived' => 0,
        'type' => 'notice',
        'content_format' => 'text',
        'display_mode' => (string) ($editing['display_mode'] ?? 'modal'),
        'style_id' => $previewStyleId,
        'frequency' => 'every_page',
        'hide_permanently' => 'disabled',
        'theme' => (string) ($editing['theme'] ?? 'blue'),
        'accent_color' => (string) ($editing['accent_color'] ?? '#2563eb'),
        'popup_width' => (string) ($editing['popup_width'] ?? ''),
        'popup_height' => (string) ($editing['popup_height'] ?? ''),
        'animation' => (string) ($editing['animation'] ?? 'fade'),
        'animation_ms' => (int) ($editing['animation_ms'] ?? 180),
        'title' => $language === 'zh' ? '样式预览' : 'Style Preview',
        'body' => $language === 'zh' ? '这是当前 Style 的弹窗预览。保存后，弹窗可以在 Look And Feel 中选择这个 Style。' : 'This is a preview of the current Style. After saving, popups can select it in Look And Feel.',
        'title_en' => 'Style Preview',
        'body_en' => 'This is a preview of the current Style. After saving, popups can select it in Look And Feel.',
        'button_label' => $language === 'zh' ? '预览按钮' : 'Preview Button',
        'button_label_en' => 'Preview Button',
        'button_url' => 'clientarea.php',
        'image_url' => '',
        'open_new_tab' => 0,
        'delay_seconds' => 0,
        'auto_close_seconds' => 0,
        'updated_at' => peakrackPopupNow(),
    ];

    ob_start();
    ?>
    <style>
        .prp-admin{display:flex;flex-direction:column;max-width:1420px;color:#1f2937}
        .prp-admin *{box-sizing:border-box}
        .prp-head{display:flex;justify-content:space-between;gap:18px;align-items:center;margin:0 0 18px;padding:16px 18px;border-radius:6px;background:linear-gradient(90deg,#0f4f86,#2777b8);color:#fff;box-shadow:0 8px 20px rgba(15,79,134,.16)}
        .prp-eyebrow{margin-bottom:4px;color:#b9e63a;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
        .prp-head h2{margin:0 0 5px;font-size:22px;font-weight:750;color:#fff}
        .prp-head p{margin:0;max-width:760px;color:rgba(255,255,255,.78);font-size:13px;line-height:1.5}
        .prp-head-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}
        .prp-lang{display:inline-flex;border:1px solid rgba(255,255,255,.28);border-radius:6px;background:rgba(255,255,255,.12);overflow:hidden}
        .prp-lang a{display:inline-flex;align-items:center;padding:7px 10px;color:#fff;text-decoration:none;font-size:12px;font-weight:700}
        .prp-lang a.active{background:#fff;color:#155e9d}
        .prp-main-nav{display:flex;gap:8px;margin:-8px 0 18px}
        .prp-main-nav a{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#1f4f7a;text-decoration:none;font-weight:700}
        .prp-main-nav a.active{background:#1f6fae;border-color:#1f6fae;color:#fff}
        .prp-grid{display:grid;grid-template-columns:minmax(0,1fr) 420px;gap:18px;align-items:start}
        .prp-card{border:1px solid #d8e0ea;border-radius:6px;background:#fff;margin-bottom:18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
        .prp-card-head{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:14px 16px;border-bottom:1px solid #e7edf3;background:#fbfcfe}
        .prp-card-title{margin:0;font-size:15px;font-weight:700;color:#111827}
        .prp-card-desc{margin:4px 0 0;color:#64748b;font-size:12px;line-height:1.45}
        .prp-card-body{padding:16px}
        .prp-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px}
        .prp-field label{display:block;margin-bottom:6px;font-weight:700;color:#374151}
        .prp-field .help{margin-top:5px;color:#6b7280;font-size:12px;line-height:1.45}
        .prp-color-input{display:flex;gap:10px;align-items:center}
        .prp-color-input input[type=color]{width:58px;height:34px;padding:2px;border:1px solid #cfd8e3;border-radius:6px;background:#fff}
        .prp-color-input code{display:inline-block;min-width:78px;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;border-radius:5px;padding:6px 8px}
        .prp-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .prp-table{width:100%;border-collapse:collapse}
        .prp-table th,.prp-table td{border-bottom:1px solid #e5e7eb;padding:10px 8px;text-align:left;vertical-align:top}
        .prp-table th{font-size:12px;color:#64748b;text-transform:uppercase;background:#f8fafc}
        .prp-badge{display:inline-block;border-radius:999px;padding:3px 8px;font-size:12px;font-weight:700}
        .prp-badge-on{background:#dcfce7;color:#166534}
        .prp-badge-off{background:#f1f5f9;color:#475569}
        .prp-muted{color:#64748b;font-size:12px;line-height:1.45}
        .prp-code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:12px}
        .prp-inline-form{display:inline}
        .prp-preview-shell{min-height:260px;position:relative;border:1px dashed #cbd5e1;border-radius:6px;background:#f8fafc;padding:16px;overflow:hidden}
        @media (max-width:1100px){.prp-grid,.prp-form-grid{grid-template-columns:1fr}.prp-head{display:block}.prp-head-actions{justify-content:flex-start;margin-top:12px}}
    </style>
    <div class="prp-admin">
        <div class="prp-head">
            <div>
                <div class="prp-eyebrow">PeakRack Popup</div>
                <h2><?php echo peakrackPopupE($t('styles_title')); ?></h2>
                <p><?php echo peakrackPopupE($t('styles_subtitle')); ?></p>
            </div>
            <div class="prp-head-actions">
                <div class="prp-lang">
                    <a class="<?php echo $language === 'zh' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url('zh', ['view' => 'styles'] + ($isEdit ? ['style' => (int) $editing['id']] : []))); ?>">中文</a>
                    <a class="<?php echo $language === 'en' ? 'active' : ''; ?>" href="<?php echo peakrackPopupE(peakrack_popup_admin_url('en', ['view' => 'styles'] + ($isEdit ? ['style' => (int) $editing['id']] : []))); ?>">English</a>
                </div>
                <a class="btn btn-primary" href="<?php echo peakrackPopupE($baseAction); ?>"><?php echo peakrackPopupE($t('new_style')); ?></a>
            </div>
        </div>

        <div class="prp-main-nav">
            <a href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language)); ?>"><i class="fa fa-list"></i> <?php echo peakrackPopupE($t('nav_popups')); ?></a>
            <a class="active" href="<?php echo peakrackPopupE($baseAction); ?>"><i class="fa fa-eye"></i> <?php echo peakrackPopupE($t('nav_styles')); ?></a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo peakrackPopupE($messageType); ?>"><?php echo peakrackPopupE($message); ?></div>
        <?php endif; ?>

        <div class="prp-grid">
            <div>
                <form method="post" action="<?php echo peakrackPopupE($formAction); ?>">
                    <?php echo $token . $hiddenLanguage; ?>
                    <input type="hidden" name="prp_view" value="styles">
                    <input type="hidden" name="prp_action" value="save_style">
                    <input type="hidden" name="id" value="<?php echo (int) ($editing['id'] ?? 0); ?>">
                    <div class="prp-card">
                        <div class="prp-card-head">
                            <h3 class="prp-card-title"><?php echo peakrackPopupE($isEdit ? $t('edit_style') . ' #' . (int) $editing['id'] : $t('new_style')); ?></h3>
                            <label style="margin:0;font-weight:700"><input type="checkbox" name="enabled" value="1" <?php echo !empty($editing['enabled']) ? 'checked' : ''; ?>> <?php echo peakrackPopupE($t('style_enabled')); ?></label>
                        </div>
                        <div class="prp-card-body">
                            <div class="prp-form-grid">
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('style_name')); ?></label>
                                    <input class="form-control" type="text" name="name" value="<?php echo peakrackPopupE($editing['name'] ?? ''); ?>" required>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('style_slug')); ?></label>
                                    <input class="form-control" type="text" name="slug" value="<?php echo peakrackPopupE($editing['slug'] ?? ''); ?>">
                                </div>
                                <div class="prp-field" style="grid-column:1/-1">
                                    <label><?php echo peakrackPopupE($t('style_desc')); ?></label>
                                    <input class="form-control" type="text" name="description" value="<?php echo peakrackPopupE($editing['description'] ?? ''); ?>">
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
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('style_sort_order')); ?></label>
                                    <input class="form-control" type="number" name="sort_order" value="<?php echo (int) ($editing['sort_order'] ?? 100); ?>">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('popup_width')); ?></label>
                                    <input class="form-control" type="text" name="popup_width" value="<?php echo peakrackPopupE($editing['popup_width'] ?? ''); ?>" placeholder="620px">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('popup_height')); ?></label>
                                    <input class="form-control" type="text" name="popup_height" value="<?php echo peakrackPopupE($editing['popup_height'] ?? ''); ?>" placeholder="auto">
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('animation')); ?></label>
                                    <?php echo peakrack_popup_select('animation', (string) ($editing['animation'] ?? 'fade'), peakrack_popup_options($language, 'animation')); ?>
                                </div>
                                <div class="prp-field">
                                    <label><?php echo peakrackPopupE($t('animation_ms')); ?></label>
                                    <input class="form-control" type="number" min="0" max="5000" name="animation_ms" value="<?php echo (int) ($editing['animation_ms'] ?? 180); ?>">
                                </div>
                                <div class="prp-field" style="grid-column:1/-1">
                                    <label><?php echo peakrackPopupE($t('style_custom_css')); ?></label>
                                    <textarea class="form-control prp-code" name="custom_css" rows="8"><?php echo peakrackPopupE($editing['custom_css'] ?? ''); ?></textarea>
                                    <div class="help"><?php echo peakrackPopupE($t('style_custom_css_help')); ?></div>
                                </div>
                                <div class="prp-field" style="grid-column:1/-1">
                                    <label><?php echo peakrackPopupE($t('style_html_template')); ?></label>
                                    <textarea class="form-control prp-code" name="html_template" rows="4"><?php echo peakrackPopupE($editing['html_template'] ?? ''); ?></textarea>
                                    <div class="help"><?php echo peakrackPopupE($t('style_html_template_help')); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="prp-actions" style="margin-bottom:22px">
                        <button class="btn btn-success" type="submit"><?php echo peakrackPopupE($t('save_style')); ?></button>
                        <a class="btn btn-default" href="<?php echo peakrackPopupE($baseAction); ?>"><?php echo peakrackPopupE($t('clear')); ?></a>
                    </div>
                </form>

                <?php if ($isEdit): ?>
                <div class="prp-card">
                    <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('style_preview')); ?></h3></div>
                    <div class="prp-card-body">
                        <div class="prp-preview-shell">
                            <?php echo peakrackPopupRender($previewPopup, ['language' => $language, 'WEB_ROOT' => '', '_peakrack_preview' => true]); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="prp-card">
                <div class="prp-card-head"><h3 class="prp-card-title"><?php echo peakrackPopupE($t('nav_styles')); ?></h3></div>
                <div class="prp-card-body" style="padding:0">
                    <table class="prp-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo peakrackPopupE($t('name')); ?></th>
                            <th><?php echo peakrackPopupE($t('style_usage')); ?></th>
                            <th><?php echo peakrackPopupE($t('actions')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($styles as $style): ?>
                            <?php $styleId = (int) ($style['id'] ?? 0); ?>
                            <tr>
                                <td><?php echo $styleId; ?></td>
                                <td>
                                    <strong><?php echo peakrackPopupE($style['name'] ?? ''); ?></strong><br>
                                    <span class="prp-muted"><?php echo peakrackPopupE($modeLabels[$style['display_mode'] ?? 'modal'] ?? (string) ($style['display_mode'] ?? 'modal')); ?> / <?php echo peakrackPopupE($themeLabels[$style['theme'] ?? 'blue'] ?? (string) ($style['theme'] ?? 'blue')); ?></span><br>
                                    <span class="prp-badge <?php echo !empty($style['enabled']) ? 'prp-badge-on' : 'prp-badge-off'; ?>"><?php echo peakrackPopupE(!empty($style['enabled']) ? $t('enabled') : $t('disabled')); ?></span>
                                    <?php if (!empty($style['is_system'])): ?> <span class="prp-muted"><?php echo peakrackPopupE($t('style_system')); ?></span><?php endif; ?>
                                </td>
                                <td><?php echo (int) ($usage[$styleId] ?? 0); ?></td>
                                <td>
                                    <a class="btn btn-xs btn-default" href="<?php echo peakrackPopupE(peakrack_popup_admin_url($language, ['view' => 'styles', 'style' => $styleId])); ?>"><?php echo peakrackPopupE($t('edit')); ?></a>
                                    <form class="prp-inline-form" method="post" action="<?php echo peakrackPopupE($baseAction); ?>" onsubmit="return confirm('<?php echo peakrackPopupE($t('style_delete_confirm')); ?>');">
                                        <?php echo $token . $hiddenLanguage; ?>
                                        <input type="hidden" name="prp_view" value="styles">
                                        <input type="hidden" name="prp_action" value="delete_style">
                                        <input type="hidden" name="id" value="<?php echo $styleId; ?>">
                                        <button class="btn btn-xs btn-link text-danger" type="submit"><?php echo peakrackPopupE($t('delete')); ?></button>
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
            theme.addEventListener('change', function () {
                if (palette[theme.value]) {
                    color.value = palette[theme.value];
                    code.textContent = color.value;
                }
            });
            color.addEventListener('input', function () {
                code.textContent = color.value;
            });
        })();
        </script>
    </div>
    <?php
    return (string) ob_get_clean();
}
