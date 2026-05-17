# PeakRack WHMCS 弹窗插件

PeakRack Popup 是一个 WHMCS 客户区弹窗管理插件，目标环境为 WHMCS 9.x / PHP 8.3。

适合这些场景：

- 优惠资源套餐、VPS、独服、云服务器活动
- 优惠码活动，支持一键复制
- 域名后缀上新、注册/续费/转入优惠
- 线路维护、机房维护、网络影响通知
- 紧急公告
- 拼团优惠、组合套餐活动
- 支付、服务、工单、合规类提醒

## 文件结构

仓库根目录保留说明文档，真正用于上传部署的文件放在 `whmcs_peakrack_popup` 发布目录中。

把 `whmcs_peakrack_popup` 目录里的 `modules` 文件夹覆盖上传到 WHMCS 根目录即可，会写入：

- `modules/addons/peakrack_popup/peakrack_popup.php`
- `modules/addons/peakrack_popup/hooks.php`
- `modules/addons/peakrack_popup/track.php`
- `modules/addons/peakrack_popup/lib/Popup.php`
- `modules/addons/peakrack_popup/whmcs.json`

## 安装方法

1. 上传本包里的这个路径到 WHMCS 根目录：

   ```text
   whmcs_peakrack_popup/modules
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

- 后台新增、编辑、启用、停用、删除弹窗。
- 后台界面支持中文 / English 切换。
- 弹窗内容支持中文和英文双语字段。
- 弹窗类型：优惠套餐、优惠码、域名优惠、线路维护、紧急公告、拼团优惠、普通通知。
- 展示样式：居中弹窗、顶部横幅、底部横幅、右下角浮窗。
- 受众规则：所有访客、仅未登录访客、仅已登录客户、指定客户组 ID。
- 页面规则：支持 `*`、`cart.php`、`clientarea.php?action=*` 这类简单规则。
- 时间范围：可设置开始时间和结束时间。
- 展示频率：每次访问、每个会话一次、每天一次、每个浏览器一次。
- 每个弹窗可单独选择强调色，用于顶部色条、类型标签、优惠码边框和按钮。
- 支持优惠码复制、图片、按钮、优先级、延迟显示、自动关闭。
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
- 前台类型标签会显示在标题右侧，文案会跟随弹窗类型，例如优惠活动、紧急公告、维护通知。
- 按钮链接只允许 HTTP(S)、根路径链接或常见 WHMCS PHP 路由。
- 图片链接只允许 HTTP(S) 或根路径链接。
- 默认示例弹窗是停用状态，可以放心保留，编辑确认后再启用。
- 从旧版本升级的已有安装，会在打开插件后台或执行 WHMCS 插件升级钩子时自动补充英文内容字段和弹窗颜色字段。

## 更新记录

### 1.2.1

- 记录早期扁平 `modules` 发布结构，并在 1.2.2 中统一为发布目录结构。
- 补充开源发布所需的中英双语安装和升级说明。

### 1.2.2

- 将开源仓库结构统一为 `whmcs_peakrack_popup/` 发布目录。
- 更新安装说明，让下载 ZIP 和 git clone 后的上传路径保持一致。

详细升级说明见 [UPGRADE.zh-CN.md](UPGRADE.zh-CN.md)。

## 开源协议

MIT License。详见 [LICENSE](LICENSE)。
