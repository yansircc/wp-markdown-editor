# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WP Githuber MD is a WordPress plugin that provides an all-in-one Markdown editor solution. It replaces the WordPress editor with a live-preview Markdown editor (Editor.md) and includes features like syntax highlighting, math rendering, and diagram support.

## Development Commands

### Initial Setup
```bash
# Install PHP dependencies
composer install
```

### CSS Development
```bash
# Navigate to SCSS directory and compile
cd assets/scss
compass compile

# Watch for SCSS changes
compass watch
```

### Code Standards
```bash
# Check PHP code standards
phpcs

# Auto-fix code standard issues
phpcbf
```

### Debugging
To enable debug mode:
1. Edit `githuber-md.php` and set `define('GITHUBER_DEBUG_MODE', true);`
2. Install Monolog: `composer require monolog/monolog`
3. After debugging, remove: `composer remove monolog/monolog`

## Architecture

### MVC Structure
- **Controllers** (`src/Controllers/`): Handle WordPress admin functionality and hooks
- **Models** (`src/Models/`): Data management (limited use in this plugin)
- **Views** (`src/Views/`): PHP templates for rendering UI
- **Modules** (`src/Modules/`): Feature modules (Markdown parsers, syntax highlighting, math rendering, etc.)

### Key Classes
- `githuber-md.php`: Plugin entry point, defines constants
- `src/Githuber.php`: Main plugin class, handles initialization
- `src/Controllers/Markdown.php`: Core Markdown editor functionality
- `src/Controllers/Setting.php`: Plugin settings management

### Data Storage
- Markdown content: Stored in `wp_posts.post_content_filtered`
- HTML content: Stored in `wp_posts.post_content`
- Settings: WordPress options table with `githuber_markdown_` prefix

### Module System
Modules in `src/Modules/` are conditionally loaded based on settings:
- MarkdownParser: Basic Markdown parsing
- MarkdownExtra: Extended Markdown features
- Prism: Syntax highlighting
- KaTeX/MathJax: Math rendering
- Mermaid: Diagram support
- FlowChart/SequenceDiagram: Additional diagram types

### Asset Management
- Frontend assets in `assets/` directory
- Vendor libraries pre-bundled in `assets/vendor/`
- CSS compiled from SCSS sources in `assets/scss/`
- No JavaScript build process - files are hand-written

### Important Development Notes

1. **WordPress Integration**: The plugin deeply integrates with WordPress editor, replacing it entirely when enabled
2. **Per-Post Control**: Markdown can be enabled/disabled per post via meta box
3. **Backward Compatibility**: Carefully handles conversion between HTML and Markdown
4. **Image Handling**: Supports clipboard paste and upload to various services (Imgur, SM.ms, media library)
5. **Gutenberg Support**: Has compatibility mode for block editor

### Common Tasks

- **Adding a new module**: Create class in `src/Modules/`, extend `Module_Base`, register in `src/Controllers/Module.php`
- **Modifying editor behavior**: Look in `assets/js/githuber-md.js` and `src/Controllers/Markdown.php`
- **Adding settings**: Update `src/Controllers/Setting.php` and corresponding view in `src/Views/setting/`
- **Styling changes**: Edit SCSS files in `assets/scss/` and compile with Compass