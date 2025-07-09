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
├── class-yansir-md-image-processor.php  # Image-to-figure converter
└── class-yansir-md-link-processor.php   # Link target processor
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
- Three settings: enable_footnotes, enable_figure, and links_new_tab
- Settings stored as WordPress options

**Yansir_MD_Link_Processor**: Processes links in parsed HTML
- Adds target="_blank" and rel="noopener noreferrer" to external links
- Only processes links when links_new_tab setting is enabled
- Integrates with the parser's filter system

### Data Flow

1. **Editing**: Markdown entered in custom textarea editor
2. **Preview**: AJAX request to `yansir_md_preview` action
3. **Saving**: Markdown stored in `post_content_filtered`, parsed HTML in `post_content`
4. **Display**: Parser checks if post has Markdown enabled, then parses from `post_content_filtered`

### JavaScript Architecture

`assets/js/editor.js` implements a single global object `window.yansirMD` with methods:
- `init()`: Initialize SimpleMDE editor with visual Markdown formatting
- `preprocessContent()`: Handle escaping issues (e.g., # signs)
- `postprocessContent()`: Process content before saving
- `uploadImage()`: Handle drag/drop, paste, and button click image uploads
- `renderPreview()`: Custom preview rendering via AJAX
- `syncContent()`: Keep hidden WordPress content field in sync

### Key Features

1. **Visual Markdown Editing**: Uses SimpleMDE for WYSIWYG-like Markdown editing with syntax highlighting
2. **Per-post control**: Meta box to enable/disable Markdown per post
3. **Image uploads**: Drag/drop, paste, or click button to upload images to media library
4. **Smart escaping**: Automatically handles # sign escaping issues
5. **Optional footnotes**: `[^1]` syntax when enabled
6. **Optional figure tags**: Converts images with titles to semantic HTML5
7. **Optional link targets**: Opens external links in new tabs when enabled
8. **Clean uninstall**: Removes all options and post meta on uninstall

### Important Implementation Details

- Plugin constants defined: `YANSIR_MD_VERSION`, `YANSIR_MD_PLUGIN_DIR`, `YANSIR_MD_PLUGIN_URL`
- Text domain: `yansir-md`
- Minimum PHP: 5.6 (for ParsedownExtra compatibility)
- AJAX nonce: `yansir_md_nonce`
- Post meta key: `_yansir_md_enabled`
- Option keys: `yansir_md_enable_footnotes`, `yansir_md_enable_figure`, `yansir_md_links_new_tab`

### Extension Points

WordPress filters available:
- `yansir_md_before_parse`: Modify Markdown before parsing
- `yansir_md_after_parse`: Modify HTML after parsing

### Common Development Tasks

- **Add new setting**: Update `class-yansir-md-settings.php` register_settings() and render_settings_page()
- **Modify editor UI**: Edit `class-yansir-md-editor.php` editor_init_script() method
- **Change parsing behavior**: Modify `class-yansir-md-parser.php` parse() method
- **Add frontend styles**: Edit preview styles in `assets/css/editor.css`