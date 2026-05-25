# 升级说明

本文档用于说明如何从旧版本升级本模块。

## 升级前准备

1. 备份 WHMCS 文件。
2. 备份 WHMCS 数据库。
3. 复制一份 `modules/addons/peakrack_popup/`。
4. 升级前阅读 [CHANGELOG.md](CHANGELOG.md)。
5. 确认本次升级是否涉及数据库变更。

## 升级步骤

1. 从官方仓库下载最新版本：

   https://github.com/Techshrr/whmcs_peakrack_popup

2. 将插件文件替换到：

   `modules/addons/peakrack_popup/`

3. 除非发布说明另有要求，请保留 `modules/addons/peakrack_popup/assets/images/` 中已有上传图片。
4. 登录 WHMCS 后台。
5. 打开 **Addons > PeakRack Popup**，检查弹窗、样式和上传路径。
6. 如果客户区显示没有更新，请清理 WHMCS 模板缓存。

## 数据库迁移

本版本不需要手动执行数据库迁移。

插件会在后台页面、前台 hook、升级 hook 或 cron 文件运行时从模块代码更新数据库结构。

## 版本升级说明

### 从 1.5.x 升级到 1.6.x

- 无破坏性变更。
- 原有弹窗和样式会保留。
- 图片上传目录为 `modules/addons/peakrack_popup/assets/images/`。

### 从 1.3.x 升级到 1.4.x

- 旧版弹窗分类不再由后台界面和前台渲染使用。
- 旧数据库字段会保留用于兼容。

## 回滚方法

如需回滚：

1. 恢复旧版本 `modules/addons/peakrack_popup/` 目录。
2. 如果升级修改过模块表，恢复数据库备份。
3. 如果上传图片被修改，恢复图片备份。
4. 清理 WHMCS 模板缓存。
5. 检查 WHMCS 活动日志是否有错误。

## 注意事项

不要覆盖生产环境密钥、本地配置文件、自定义模板、回调密钥或支付凭据，除非升级说明明确要求。