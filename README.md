# Yansir MD - 极简 WordPress Markdown 编辑器

一个极简的 WordPress Markdown 编辑器插件，专注于核心功能，提供流畅的写作体验。

## 特性

- 🚀 极简设计，插件大小 < 1MB
- ✍️ 实时预览 Markdown 渲染效果
- 📸 支持拖拽/粘贴上传图片到媒体库
- 📝 可选的脚注功能（Markdown Extra）
- ⚡ 零配置，开箱即用
- 🎯 每篇文章独立控制是否启用 Markdown

## 安装

1. 下载插件并解压到 `wp-content/plugins/` 目录
2. 在 WordPress 后台激活插件
3. 在编写文章时，勾选侧边栏的"启用 Markdown 编辑器"

## 使用

### 基础语法

- 标题：`# H1`, `## H2`, `### H3`
- 粗体：`**粗体文字**`
- 斜体：`*斜体文字*`
- 链接：`[链接文字](http://example.com)`
- 图片：`![alt文字](图片地址)`
- 代码：`` `行内代码` `` 或 ` ```代码块``` `
- 列表：`- 项目` 或 `1. 项目`
- 引用：`> 引用文字`
- 分割线：`---`

### 脚注（需在设置中启用）

```markdown
这是一段文字[^1]。

[^1]: 这是脚注内容。
```

## 技术栈

- 原生 JavaScript（无额外依赖）
- Parsedown & Parsedown Extra（PHP Markdown 解析器）
- WordPress 原生 API

## 开源协议

GPL v3

## 作者

Yansir