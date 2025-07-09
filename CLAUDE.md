# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Yansir MD is a minimalist WordPress Markdown editor plugin, created as a lite version of the original Githuber MD. It focuses on core Markdown editing functionality with minimal dependencies and a clean architecture.

## Development Commands

```bash
# Install PHP dependencies (Parsedown library)
composer install

# No build process required - this is a zero-build plugin
# JavaScript and CSS are vanilla/hand-written
```

## Architecture

### Plugin Structure
```
yansir-md.php              # Plugin entry point, version 1.0.0
includes/                  # Core PHP classes
├── class-yansir-md.php    # Main orchestrator class
├── class-yansir-md-editor.php     # Editor UI and save logic
├── class-yansir-md-parser.php     # Markdown parsing
├── class-yansir-md-settings.php   # Settings page
├── class-yansir-md-footnotes.php  # Footnote processor
└── class-yansir-md-image-processor.php  # Image-to-figure converter
assets/
├── css/editor.css         # Editor styles
└── js/editor.js          # Editor JavaScript (vanilla)
```

### Core Classes

**Yansir_MD**: Main plugin class that orchestrates initialization
- Loads dependencies in correct order
- Sets up WordPress hooks for admin and frontend
- Manages text domain loading

**Yansir_MD_Editor**: Handles the editing interface
- Replaces WordPress editor with Markdown textarea
- Provides AJAX endpoints for preview and image upload
- Manages per-post Markdown enable/disable via meta box
- Saves Markdown to `post_content_filtered`, HTML to `post_content`

**Yansir_MD_Parser**: Converts Markdown to HTML
- Uses Parsedown for basic Markdown
- Optionally processes footnotes (custom implementation)
- Optionally converts images to figure/figcaption tags
- Applies WordPress filters for extensibility

**Yansir_MD_Settings**: Plugin configuration
- Two settings: enable_footnotes and enable_figure
- Settings stored as WordPress options

### Data Flow

1. **Editing**: Markdown entered in custom textarea editor
2. **Preview**: AJAX request to `yansir_md_preview` action
3. **Saving**: Markdown stored in `post_content_filtered`, parsed HTML in `post_content`
4. **Display**: Parser checks if post has Markdown enabled, then parses from `post_content_filtered`

### JavaScript Architecture

`assets/js/editor.js` implements a single global object `window.yansirMD` with methods:
- `init()`: Initialize editor
- `togglePreview()`: Switch between edit/preview modes
- `uploadImage()`: Handle drag/drop and paste image uploads
- `syncContent()`: Keep hidden WordPress content field in sync

### Key Features

1. **Per-post control**: Meta box to enable/disable Markdown per post
2. **Image uploads**: Drag/drop or paste images, uploaded to media library
3. **Optional footnotes**: `[^1]` syntax when enabled
4. **Optional figure tags**: Converts images with titles to semantic HTML5
5. **Clean uninstall**: Removes all options and post meta on uninstall

### Important Implementation Details

- Plugin constants defined: `YANSIR_MD_VERSION`, `YANSIR_MD_PLUGIN_DIR`, `YANSIR_MD_PLUGIN_URL`
- Text domain: `yansir-md`
- Minimum PHP: 5.6 (for ParsedownExtra compatibility)
- AJAX nonce: `yansir_md_nonce`
- Post meta key: `_yansir_md_enabled`
- Option keys: `yansir_md_enable_footnotes`, `yansir_md_enable_figure`

### Extension Points

WordPress filters available:
- `yansir_md_before_parse`: Modify Markdown before parsing
- `yansir_md_after_parse`: Modify HTML after parsing

### Common Development Tasks

- **Add new setting**: Update `class-yansir-md-settings.php` register_settings() and render_settings_page()
- **Modify editor UI**: Edit `class-yansir-md-editor.php` editor_init_script() method
- **Change parsing behavior**: Modify `class-yansir-md-parser.php` parse() method
- **Add frontend styles**: Edit preview styles in `assets/css/editor.css`