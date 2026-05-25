# PeakRack Popup for WHMCS

> 官方仓库：https://github.com/Techshrr/whmcs_peakrack_popup
> 许可证：MIT License

PeakRack Popup 是一个用于 WHMCS 客户区弹窗和通知的插件。

## 项目说明

插件提供后台页面，用于创建在 WHMCS 客户区显示的 Text、HTML 和 Image 弹窗。它会把弹窗记录、样式预设、展示规则、定向规则和事件计数保存到模块表中。

使用本插件不需要修改 WHMCS 模板文件。前台输出通过 WHMCS hook 注入，并使用带 `prp-` 前缀的作用域 CSS 类。

## 功能特性

- 客户区弹窗后台增删改查。
- 支持 Text、HTML 和 Image 内容类型。
- 可复用样式预设，包含展示位置、主题、强调色、尺寸、动画、自定义 CSS 和可选 HTML 模板。
- 弹窗内容支持中文和英文独立字段。
- 受众规则支持所有访客、仅访客、已登录客户和客户组 ID。
- 支持页面、语言、星期、URL 片段、未付款发票、产品、产品组、服务器、附加服务、TLD 和服务到期日限制。
- 支持展示日期、每日时间段、频率、总展示次数限制和每客户展示次数限制。
- 支持上传 JPG、PNG、GIF、WEBP 图片，最大 4 MB。
- 可通过 tracking 端点记录展示、点击和关闭计数。
- 提供可选 cron 文件，用于禁用已过期弹窗。

## 环境要求

- WHMCS 9.0.x
- PHP 8.3 或更高版本
- MySQL 5.7 / 8.0

## 安装方法

1. 从官方仓库下载最新版本。
2. 将插件目录上传到：

   `modules/addons/peakrack_popup/`

3. 登录 WHMCS 后台。
4. 进入 **System Settings > Addon Modules** 并启用 **PeakRack Popup**。
5. 打开 **Addons > PeakRack Popup**，编辑默认禁用的示例弹窗，或创建新弹窗。

## 配置说明

| 配置项 | 说明 | 默认值 |
|---|---|---|
| Popup enabled | 控制单个弹窗是否可显示 | 默认示例为关闭 |
| Content format | 选择 Text、HTML 或 Image 内容 | Text |
| Language fields | 中文和英文标题、正文、按钮独立字段 | 空 |
| Audience | 选择访客范围 | 所有访客 |
| Page rules | 匹配客户区路径和简单规则 | `*` |
| Schedule and daily time range | 控制日期和每日时间范围 | 空 |
| Frequency | 控制重复展示方式 | 每次访问 |
| Display limits | 限制总展示次数或每客户展示次数 | 空 |
| Permanent close | 允许关闭按钮或勾选确认永久关闭 | 关闭 |
| Style | 选择可复用样式预设 | 默认样式 |
| Image upload | 将校验后的图片保存到模块资源目录 | 空 |
| Tracking | 通过 `track.php` 记录展示、点击和关闭 | 前端 token 启用 |

## 使用说明

管理员创建或编辑弹窗，选择内容类型、受众、页面规则、时间规则、频率和样式，然后启用弹窗。前台 hook 会根据当前客户区请求匹配已启用弹窗，并显示优先级最高的一条。

HTML 弹窗会在客户区渲染 HTML 内容，因此只应允许可信管理员编辑。

## 可选 Cron

如需在数据库中自动禁用已过期弹窗，可以运行：

`php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php`

即使不配置该 cron，前台渲染时也会检查日期规则。

## 升级说明

请查看 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 英文文档

请查看 [README.md](README.md)。

## 安全说明

请勿提交生产环境凭据、API Key、数据库密码、支付密钥、WHMCS 授权信息、客户数据、身份证件或私有签名密钥。

安全问题报告方式请查看 [SECURITY.md](SECURITY.md)。

## 许可证

本项目基于 MIT License 发布。完整许可证请查看 [LICENSE](LICENSE)。
