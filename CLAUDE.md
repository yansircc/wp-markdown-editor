# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Yansir Markdown is a minimalist WordPress Markdown editor plugin, created as a lite version of the original Githuber MD. It focuses on core Markdown editing functionality with minimal dependencies and a clean architecture.

## Development Commands

```bash
# Install PHP dependencies (Parsedown library)
composer install

# No build process required - this is a zero-build plugin
# SimpleMDE is loaded from CDN
```

## Architecture

### Plugin Structure
```
yansir-md.php              # Plugin entry point, version 1.0.0
includes/                  # Core PHP classes
├── class-yansir-md.php    # Main orchestrator class
├── class-yansir-md-editor.php     # Editor UI and save logic
├── class-yansir-md-parser.php     # Markdown parsing and display
├── class-yansir-md-settings.php   # Settings page
├── class-yansir-md-footnotes.php  # Footnote processor
├── class-yansir-md-image-processor.php  # Image-to-figure converter
└── class-yansir-md-link-processor.php   # Link target processor
assets/
├── css/editor.css         # Editor and SimpleMDE custom styles
└── js/editor.js          # Editor JavaScript with SimpleMDE integration
```

### Core Classes

**Yansir_MD**: Main plugin class that orchestrates initialization
- Loads dependencies in correct order
- Sets up WordPress hooks for admin and frontend
- Manages text domain loading
- Configures content filtering priorities

**Yansir_MD_Editor**: Handles the editing interface
- Integrates SimpleMDE editor with visual Markdown formatting
- Provides WordPress Media Library integration for images
- Manages per-post Markdown enable/disable via meta box
- Saves Markdown directly to `post_content` and `post_content_filtered`
- Handles AJAX preview and legacy image upload endpoints

**Yansir_MD_Parser**: Converts Markdown to HTML
- Uses Parsedown for basic Markdown parsing
- Parses content on-the-fly during display (not during save)
- Handles the_content filter to convert Markdown to HTML
- Disables wpautop and wptexturize for Markdown posts
- Optionally processes footnotes, images, and links

**Yansir_MD_Settings**: Plugin configuration
- Three settings: enable_footnotes, enable_figure, and links_new_tab
- Settings stored as WordPress options

**Yansir_MD_Link_Processor**: Processes links in parsed HTML
- Adds target="_blank" and rel="noopener noreferrer" to external links
- Only processes links when links_new_tab setting is enabled
- Uses mb_encode_numericentity for PHP 8.2+ compatibility

**Yansir_MD_Image_Processor**: Converts images to semantic HTML5
- Wraps images in figure tags
- Adds figcaption from image title attribute
- Uses mb_encode_numericentity for PHP 8.2+ compatibility

### Data Flow

1. **Editing**: Markdown entered in SimpleMDE editor with live syntax highlighting
2. **Preview**: Real-time preview via SimpleMDE or AJAX request
3. **Saving**: Markdown stored directly in both `post_content` and `post_content_filtered`
4. **Display**: the_content filter dynamically parses Markdown to HTML on page load

### JavaScript Architecture

`assets/js/editor.js` implements a single global object `window.yansirMD` with methods:
- `init()`: Initialize SimpleMDE editor with custom toolbar (image + preview only)
- `preprocessContent()`: Handle escaping issues (e.g., # signs) when loading content
- `postprocessContent()`: Process content before saving
- `uploadImage()`: Handle drag/drop and paste image uploads
- `openMediaLibrary()`: Open WordPress Media Library for image selection
- `renderPreview()`: Custom preview rendering via AJAX
- `syncContent()`: Keep hidden WordPress content field in sync

### SimpleMDE Integration

- **Visual Editing**: Headers appear large, bold text appears bold, etc.
- **Minimal Toolbar**: Only image insert and preview buttons
- **Smart Escaping**: Automatically fixes common Markdown escaping issues
- **Media Library**: Native WordPress media picker for images
- **Drag & Drop**: Full support for image drag/drop and paste

### Key Features

1. **Visual Markdown Editing**: Uses SimpleMDE for WYSIWYG-like Markdown editing
2. **WordPress Media Library**: Native media picker integration
3. **Per-post control**: Meta box to enable/disable Markdown per post
4. **Smart escaping**: Automatically handles # sign escaping issues
5. **Optional footnotes**: `[^1]` syntax when enabled
6. **Optional figure tags**: Converts images with titles to semantic HTML5
7. **Optional link targets**: Opens external links in new tabs when enabled
8. **Clean uninstall**: Removes all options and post meta on uninstall

### Important Implementation Details

- Plugin constants defined: `YANSIR_MD_VERSION`, `YANSIR_MD_PLUGIN_DIR`, `YANSIR_MD_PLUGIN_URL`
- Text domain: `yansir-md`
- Minimum PHP: 5.6 (for Parsedown compatibility)
- PHP 8.2+ compatible (no deprecated functions)
- AJAX nonce: `yansir_md_nonce`
- Post meta key: `_yansir_md_enabled`
- Option keys: `yansir_md_enable_footnotes`, `yansir_md_enable_figure`, `yansir_md_links_new_tab`
- SimpleMDE loaded from CDN (v1.11.2)

### Extension Points

WordPress filters available:
- `yansir_md_before_parse`: Modify Markdown before parsing
- `yansir_md_after_parse`: Modify HTML after parsing

### Common Development Tasks

- **Add new setting**: Update `class-yansir-md-settings.php` register_settings() and render_settings_page()
- **Modify editor behavior**: Edit `assets/js/editor.js` SimpleMDE configuration
- **Change parsing behavior**: Modify `class-yansir-md-parser.php` parse() method
- **Add frontend styles**: Edit preview styles in `assets/css/editor.css`
- **Customize toolbar**: Modify toolbar array in `editor.js` init() method