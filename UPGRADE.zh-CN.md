# 升级说明

## 1.6.1

- 仅更新前台展示，不需要修改数据库。
- Image 弹窗现在有更明确的关闭入口：右上角关闭按钮、底部关闭按钮、Esc 键关闭、点击遮罩关闭。
- 图片和海报媒体区域改为白底等比容器，避免弹窗深色背景形成黑边，也降低图片被裁剪的概率。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。请保留 `assets/images/` 下已经上传的运行图片。
- 插件版本号升级到 `1.6.1`。

## 1.6.0

- 仅更新后台行为，不需要修改数据库。
- Image 弹窗现在可以手动填写图片 URL，也可以直接上传图片文件。
- 上传图片会保存到 `modules/addons/peakrack_popup/assets/images/`。如果要使用上传功能，请确认 Web 服务器用户对该目录有写入权限。
- 已上传图片属于已安装 WHMCS 插件目录中的运行数据，覆盖更新插件文件时请保留。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 插件版本号升级到 `1.6.0`。

## 1.5.0

- 数据库迁移版本。插件会自动创建 `mod_peakrack_popup_styles`，并为弹窗表补充 `style_id` 字段。
- 打开插件后台或前台 hook 运行时，会自动写入默认可复用样式。
- 弹窗可以选择可复用 Style；选中后会覆盖展示样式、主题色、强调色、尺寸、动画、作用域 CSS 和可选 HTML 模板。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 插件版本号升级到 `1.5.0`。

## 1.4.0

- 仅更新后台和前台行为，不需要修改数据库。
- 弹窗 `Type` 改为官方同款内容模型：`Text`、`HTML`、`Image`。
- 优惠码、活动、域名优惠、维护、紧急公告、拼团等旧业务分类不再展示，也不再参与前台渲染。
- 移除专用优惠码字段和优惠码渲染器。需要优惠码时，直接写入 Text 或 HTML 内容即可。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 插件版本号升级到 `1.4.0`。

## 1.3.1

- 仅更新前台渲染和后台校验逻辑，不需要修改数据库。
- Image 内容格式现在必须填写有效图片 URL，并按图片主视觉弹窗渲染；标题、正文和按钮仍然可选。
- 旧版优惠码专用逻辑已弃用，并在 `1.4.0` 中完全移除。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 插件版本号升级到 `1.3.1`。

## 1.3.0

- 数据库迁移版本。打开插件后台或前台 hook 运行时，会自动补充活动、定向、归档、尺寸、动画、展示上限和服务到期限制相关字段。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 可选 cron：如果希望过期弹窗自动在数据库中标记为停用，可加入 `php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php`。
- 升级后请检查 HTML 格式弹窗。HTML 会按可信管理员内容原样渲染。
- 插件版本号升级到 `1.3.0`。

## 1.2.5

- 仅更新前台行为，不需要修改数据库。
- 配置自动关闭的弹窗现在会在客户区显示低干扰实时倒计时提示。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 插件版本号升级到 `1.2.5`。

## 1.2.4

- 仅更新前台展示样式，不需要修改数据库。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 如果测试时弹窗没有重新出现，请清理该 WHMCS 站点的浏览器本地存储，或临时把弹窗频率改成每页显示。
- 插件版本号升级到 `1.2.4`。

## 1.2.3

- 仅调整仓库展示结构：可部署插件目录现在位于仓库根目录 `peakrack_popup/`。
- 已安装站点升级到此版本不需要修改数据库。
- 手动更新时，把 `peakrack_popup/` 覆盖上传到 `modules/addons/peakrack_popup/`。
- 插件版本号升级到 `1.2.3`。

## 1.2.2

- 仅调整仓库发布目录结构：可部署文件现在位于 `whmcs_peakrack_popup/modules`。
- 已安装站点升级到此版本不需要修改数据库。
- 手动更新时，把新的 `whmcs_peakrack_popup/modules` 目录内容覆盖上传到 WHMCS 根目录即可。
- 插件版本号升级到 `1.2.2`。

## 1.2.1

- 保持扁平发布包结构。
- 补充开源发布所需的中英双语安装和升级说明。
