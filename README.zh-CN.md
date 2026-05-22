# PeakRack WHMCS 弹窗插件

PeakRack Popup 是一个 WHMCS 客户区弹窗管理插件，目标环境为 WHMCS 9.x / PHP 8.3。

插件模型按官方同款语义设计：`Text`、`HTML`、`Image` 三种内容类型，不再使用“优惠码/活动/公告”这类业务分类。

适合这些场景：

- 带标题、正文和可选按钮的文本通知
- 可信管理员 HTML 内容
- 图片主视觉弹窗或海报弹窗
- 线路维护、机房维护、网络影响通知
- 支付、服务、工单、合规类提醒

## 文件结构

仓库根目录改为更适合 GitHub 浏览的浅层结构，真正用于上传部署的插件目录就是 `peakrack_popup`。

把 `peakrack_popup` 上传或覆盖到 WHMCS 的 `modules/addons/peakrack_popup/` 即可，会写入：

- `modules/addons/peakrack_popup/peakrack_popup.php`
- `modules/addons/peakrack_popup/hooks.php`
- `modules/addons/peakrack_popup/track.php`
- `modules/addons/peakrack_popup/cron.php`
- `modules/addons/peakrack_popup/lib/Popup.php`
- `modules/addons/peakrack_popup/assets/images/`
- `modules/addons/peakrack_popup/whmcs.json`

## 安装方法

1. 上传这个插件目录到 WHMCS：

   ```text
   peakrack_popup/ -> modules/addons/peakrack_popup/
   ```

2. 进入 WHMCS 后台 **系统设置 > 插件模块**。
3. 启用 **PeakRack Popup**。
4. 进入 **插件 > PeakRack Popup**。
5. 编辑默认的停用示例弹窗，或者创建新弹窗。
6. 确认受众、页面规则、时间范围后再启用。

启用插件时会创建：

- `mod_peakrack_popups`
- `mod_peakrack_popup_events`

停用插件不会删除数据表，方便保留活动配置和统计数据。

## 主要功能

- 后台新增、编辑、预览、启用、停用、归档、恢复、删除弹窗。
- 独立 Styles 样式管理，可复用弹窗样式预设。
- 后台界面支持中文 / English 切换。
- 弹窗内容支持中文和英文双语字段。
- 弹窗类型为 Text、HTML、Image，并按模式区分必填项。
- 弹窗可以选择可复用 Style。Style 可定义展示样式、主题色、强调色、尺寸、动画、作用域 CSS 和可选 HTML 模板。
- 展示样式：遮罩居中弹窗、海报弹窗、无遮罩居中弹窗、顶部横幅、底部横幅、右下角、左下角、右侧、左侧浮窗。
- 支持自定义宽高、淡入、滑入或无动画。
- 受众规则：所有访客、仅未登录访客、仅已登录客户、指定客户组 ID。
- 页面规则：支持 `*`、`cart.php`、`clientarea.php?action=*` 这类简单规则。
- 高级限制：语言、星期、URL 包含、未付账单、已拥有产品、产品组、服务器、附加服务、域名后缀，以及“不拥有”产品/附加服务/域名后缀。
- 服务到期限制：可按到期前、到期当天、到期后和天数条件展示。
- 时间范围：可设置开始时间和结束时间。
- 每日时间段：例如只在 08:00 到 18:00 展示。
- 展示频率：每次访问、每个会话一次、每天一次、每个浏览器一次。
- 支持总展示上限、单客户展示上限，以及永久关闭规则。
- 每个弹窗可单独选择强调色，用于顶部色条、强调元素和按钮。
- 支持图片 URL 或后台上传图片、按钮、优先级、延迟显示，以及带前台小提示的自动关闭。
- 基础统计：展示次数、按钮点击次数、关闭次数。

## 双语内容

每个弹窗都可以分别填写：

- 中文标题、正文、按钮文字
- English title、body、button text

前台会跟随 WHMCS 客户区当前语言。客户选择 English 时，弹窗会读取英文标题、正文和按钮文字；如果英文内容为空，会回退显示中文内容。

后台右上角的语言切换只影响管理界面，不影响访客实际看到的语言。

## 页面规则示例

```text
*
cart.php
clientarea.php?action=services
clientarea.php?action=*
networkissues.php
```

如果同一个页面命中多个已启用弹窗，前台只显示优先级最高的一个，避免多个弹窗同时打扰客户。

## 统计接口

前台 hook 会生成短期签名，并把展示、点击、关闭事件发送到：

```text
/modules/addons/peakrack_popup/track.php
```

签名基于 WHMCS 的加密哈希和日期生成。这个接口只用于本地计数，不提供弹窗管理能力。

## 注意事项

- 不需要修改 WHMCS 模板文件。
- 前台 CSS 使用 `prp-` 前缀做作用域隔离。
- 后台配置自动关闭秒数后，前台会显示类似 `10秒后自动关闭` 的低干扰实时倒计时提示。
- Text 内容至少需要填写一个标题或正文。HTML 内容需要填写正文。Image 内容需要填写图片 URL 或上传图片，标题、正文和按钮可选。
- 旧版业务分类字段仅保留为升级兼容，不再被后台表单或前台渲染使用。需要优惠码时，可直接写入 Text 或 HTML 内容。
- 按钮链接只允许 HTTP(S)、根路径链接或常见 WHMCS PHP 路由。
- 图片链接只允许 HTTP(S)、根路径链接或本模块上传路径。后台上传图片会保存到 `modules/addons/peakrack_popup/assets/images/`，支持 JPG、PNG、GIF、WEBP，最大 4 MB。
- 默认示例弹窗是停用状态，可以放心保留，编辑确认后再启用。
- 从旧版本升级的已有安装，会在打开插件后台、前台 hook 运行或执行 WHMCS 插件升级钩子时自动补充新增数据库字段。

## Styles 样式

进入 **插件 > PeakRack Popup > Styles** 可以管理可复用弹窗样式。插件会默认写入居中弹窗、海报图片、顶部横幅、底部横幅、右下角浮窗、无遮罩居中等样式。

自定义 CSS 支持 `{root}` 占位符，会替换为当前弹窗根选择器。例如：

```css
{root} .prp-panel{border-radius:4px}
{root} .prp-button{text-transform:uppercase}
```

HTML 模板是可选项。留空时使用默认渲染器；高级布局可以使用 `{close}`、`{image}`、`{title}`、`{body}`、`{actions}`、`{image_close}`、`{content}`、`{accent}` 等占位符。

## 可选 Cron

前台渲染时已经会遵守结束时间。如果你还希望过期弹窗自动在数据库中标记为停用，可加入：

```text
php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php
```

建议用 WHMCS 所在服务器的 cron 用户每 5 到 15 分钟执行一次。

## 原创实现说明

本插件根据公开产品页和公开文档中的功能描述，重新设计为 PeakRack 风格的 WHMCS 客户区弹窗管理器。它不复制 ModulesGarden 的私有源码、模板、品牌、授权检查或 ionCube 编码文件。

## 更新记录

### 1.2.1

- 记录早期扁平 `modules` 发布结构，并在 1.2.2 中统一为发布目录结构。
- 补充开源发布所需的中英双语安装和升级说明。

### 1.2.2

- 将开源仓库结构统一为 `whmcs_peakrack_popup/` 发布目录。
- 更新安装说明，让下载 ZIP 和 git clone 后的上传路径保持一致。

### 1.2.3

- 压平 GitHub 仓库结构，根目录直接显示 `peakrack_popup/`。
- 更新安装和升级文档，改为直接上传插件目录到 `modules/addons/`。

### 1.2.4

- 优化客户区弹窗前台观感，让样式更干净、更贴近 WHMCS/Lagom 客户区界面。
- 减轻边框、阴影和遮罩的厚重感，同时保持弹窗辨识度。
- 将类型标签移到标题上方，优化关闭按钮和 CTA 按钮间距。
- 改善居中弹窗、横幅和右下角浮窗在桌面与移动端的响应式表现，不涉及数据库结构变更。

### 1.2.5

- 后台配置自动关闭秒数后，前台增加低干扰倒计时提示。
- 倒计时文案跟随客户区中英文语言，并每秒自动更新。
- 不涉及数据库结构变更。

### 1.3.0

- 增加预览、归档/恢复、可信 HTML/图片内容格式、永久关闭、展示上限、更多浮窗位置、尺寸和动画设置。
- 增加语言、星期、URL 关键词、未付账单、产品、产品组、服务器、附加服务、域名后缀、不拥有条件、每日时间段和服务到期规则。
- 增加可选 cron，用于把过期弹窗自动标记为停用，同时保留前台渲染时的结束时间检查。

### 1.3.1

- 增加 Text、HTML、Image 和当时仍存在的旧版 Coupon 配置的模式化必填校验。
- 修复图片主视觉弹窗，避免因为残留优惠码字段而显示成优惠码弹窗样式。
- 选择 Image 内容格式时，优惠码字段会自动清空、隐藏并在前台忽略。

### 1.4.0

- 切换为官方同款弹窗类型语义：`Text`、`HTML`、`Image`。
- 从后台表单、校验、列表摘要和前台渲染中移除优惠码/活动/公告等业务分类。
- 移除专用优惠码 UI。优惠码文字仍可直接写入 Text 或 HTML 内容。

### 1.5.0

- 增加独立 Styles 样式管理，贴近官方 Styles 工作流。
- 默认写入居中弹窗、海报图片、顶部横幅、底部横幅、右下角浮窗、无遮罩居中等可复用样式。
- 弹窗现在可以选择可复用 Style；选中后会覆盖展示样式、主题色、强调色、尺寸、动画、作用域 CSS 和可选 HTML 模板。

### 1.6.0

- 为 Image 弹窗增加后台图片上传，同时保留手动填写图片 URL。
- 上传图片会校验扩展名和 MIME 类型，仅允许 JPG、PNG、GIF、WEBP，最大 4 MB，并保存到插件 assets 目录。
- 保存上传图片后，会自动把模块资源路径写入弹窗的图片 URL 字段。

### 1.6.1

- 优化纯图片弹窗，增加更明显的右上角关闭按钮、底部整宽关闭按钮、Esc 关闭和点击遮罩关闭。
- 图片弹窗媒体区域改为白底等比容器，避免上传图片被弹窗深色底色挤出两侧黑边。
- 降低海报图片模式的裁剪风险，并在没有文字内容时隐藏底部装饰弧形。

详细升级说明见 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 开源协议

MIT License。详见 [LICENSE](LICENSE)。
