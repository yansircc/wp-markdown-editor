# Yansir Markdown - 可视化 WordPress Markdown 编辑器

一个现代化的 WordPress Markdown 编辑器插件，提供实时可视化编辑体验，让 Markdown 写作更加直观和高效。

## 核心特性

- 🎨 **可视化编辑** - 基于 SimpleMDE，标题、粗体等格式实时显示
- 🖼️ **媒体库集成** - 原生 WordPress 媒体库支持，轻松管理图片
- 📸 **拖拽上传** - 支持拖拽、粘贴直接上传图片
- 👁️ **实时预览** - 内置预览模式，所见即所得
- 🔧 **智能修复** - 自动处理常见的 Markdown 转义问题
- 📝 **可选扩展** - 脚注、图片标题、链接新标签等可选功能
- ⚡ **轻量高效** - 零构建，CDN 加载，快速响应
- 🎯 **灵活控制** - 每篇文章独立选择是否使用 Markdown

## 安装

1. 下载插件并解压到 `wp-content/plugins/yansir-md` 目录
2. 运行 `composer install` 安装 PHP 依赖
3. 在 WordPress 后台激活插件
4. 访问"设置 > Yansir Markdown"配置可选功能

## 使用指南

### 启用 Markdown 编辑器

1. 创建或编辑文章/页面
2. 在右侧边栏找到"Markdown 编辑器"框
3. 勾选"启用 Markdown 编辑器"
4. 页面会自动刷新并加载可视化 Markdown 编辑器

### 编辑器功能

- **插入图片** 🖼️ - 点击工具栏图片按钮，打开媒体库选择或上传
- **预览模式** 👁️ - 点击预览按钮查看最终渲染效果
- **拖拽上传** - 直接拖拽图片到编辑器上传
- **粘贴图片** - 从剪贴板粘贴图片自动上传

### Markdown 语法

```markdown
# 一级标题
## 二级标题
### 三级标题

**粗体文字** 和 *斜体文字*

[链接文字](https://example.com)

![图片描述](image.jpg)

> 引用文字

- 无序列表项
1. 有序列表项

`行内代码` 和代码块：

```code
代码内容
```

---
分割线
```

### 高级功能

#### 脚注（需在设置中启用）
```markdown
这是一段文字[^1]。

[^1]: 这是脚注内容。
```

#### 图片标题（需在设置中启用）
```markdown
![图片描述](image.jpg "图片标题")
```
启用后会自动转换为 HTML5 的 `<figure>` 和 `<figcaption>` 标签。

#### 链接在新标签打开（需在设置中启用）
启用后所有外部链接会自动添加 `target="_blank"`。

## 技术特性

- **SimpleMDE** - 提供可视化 Markdown 编辑体验
- **Parsedown** - 快速安全的 PHP Markdown 解析器
- **WordPress 媒体库** - 完整的媒体管理集成
- **PHP 8.2+ 兼容** - 支持最新 PHP 版本
- **零构建流程** - 无需编译，开箱即用

## 系统要求

- WordPress 5.0+
- PHP 5.6+（推荐 7.4+）
- 现代浏览器（Chrome, Firefox, Safari, Edge）

## 开发者信息

### 文件结构
```
yansir-md/
├── includes/          # PHP 核心类
├── assets/           # CSS 和 JS 资源
├── vendor/           # Composer 依赖
├── yansir-md.php    # 插件入口
└── uninstall.php    # 卸载脚本
```

### 可用钩子
- `yansir_md_before_parse` - 解析前修改 Markdown
- `yansir_md_after_parse` - 解析后修改 HTML

## 更新日志

### 1.0.0
- 初始版本发布
- SimpleMDE 可视化编辑器集成
- WordPress 媒体库支持
- 智能转义修复
- 可选的脚注、图片标题、链接处理功能

## 开源协议

GPL v3 或更高版本

## 作者

Yansir

## 致谢

- [SimpleMDE](https://simplemde.com/) - Markdown 编辑器
- [Parsedown](https://parsedown.org/) - PHP Markdown 解析器
- WordPress 社区