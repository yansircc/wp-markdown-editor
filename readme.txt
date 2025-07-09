=== Yansir Markdown ===
Contributors: yansir
Tags: markdown, editor, visual editor, markdown editor, writing
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 5.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A minimalist visual Markdown editor for WordPress with real-time preview and Media Library integration.

== Description ==

Yansir Markdown is a modern WordPress Markdown editor plugin that provides a visual editing experience, making Markdown writing more intuitive and efficient.

= Core Features =

* **Visual Editing** - Based on SimpleMDE, see your formatting (headers, bold, etc.) in real-time
* **Media Library Integration** - Native WordPress Media Library support for easy image management
* **Drag & Drop Upload** - Drag and drop or paste images directly into the editor
* **Live Preview** - Built-in preview mode for WYSIWYG experience
* **Smart Fixes** - Automatically handles common Markdown escaping issues
* **Optional Extensions** - Enable footnotes, figure tags, and new tab links as needed
* **Lightweight** - Zero build process, CDN loaded, fast and responsive
* **Flexible Control** - Enable or disable Markdown per post/page

= How It Works =

1. Install and activate the plugin
2. When editing a post or page, check "Enable Markdown Editor" in the sidebar
3. Start writing in visual Markdown with instant formatting
4. Your content is saved as Markdown and converted to HTML on display

= Perfect For =

* Bloggers who prefer Markdown syntax
* Technical writers needing code blocks and formatting
* Content creators wanting a distraction-free writing experience
* Anyone looking for a simpler, faster editing workflow

== Installation ==

1. Upload the `yansir-md` folder to the `/wp-content/plugins/` directory
2. Run `composer install` in the plugin directory to install PHP dependencies
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > Yansir Markdown to configure optional features
5. Start using Markdown by checking "Enable Markdown Editor" when editing posts/pages

== Frequently Asked Questions ==

= How do I enable the Markdown editor? =

When creating or editing a post/page, look for the "Markdown Editor" meta box in the sidebar. Check "Enable Markdown Editor" and the page will refresh with the visual Markdown editor.

= Can I use Markdown on some posts and the classic editor on others? =

Yes! The Markdown editor is enabled per-post. You can choose which posts use Markdown and which use the standard WordPress editor.

= How do I add images? =

Click the image button in the toolbar to open the WordPress Media Library. You can also drag and drop images directly into the editor, or paste them from your clipboard.

= What Markdown syntax is supported? =

All standard Markdown syntax is supported including headers, bold, italic, links, images, lists, code blocks, blockquotes, and horizontal rules. With settings enabled, you can also use footnotes.

= Is my content stored as Markdown or HTML? =

Your content is stored as Markdown in the database and converted to HTML when displayed to visitors. This keeps your content portable and future-proof.

= Can I switch back to the regular editor? =

Yes, simply uncheck "Enable Markdown Editor" and your content will be preserved. Note that some Markdown-specific formatting may need adjustment.

== Screenshots ==

1. Visual Markdown editor with real-time formatting
2. Media Library integration for easy image insertion
3. Live preview mode showing rendered output
4. Settings page for optional features
5. Per-post Markdown enable/disable control

== Changelog ==

= 1.0.0 =
* Initial release
* SimpleMDE visual editor integration
* WordPress Media Library support
* Smart escaping fixes
* Optional footnotes, figure tags, and link target features
* Per-post Markdown control
* Clean uninstall handling

== Upgrade Notice ==

= 1.0.0 =
Initial release of Yansir Markdown. A lightweight, visual Markdown editor for WordPress.

== Advanced Features ==

= Footnotes (Enable in Settings) =

Add footnotes to your content:
`This is some text[^1].`
`[^1]: This is the footnote content.`

= Figure Tags (Enable in Settings) =

Images with titles are automatically wrapped in semantic HTML5 figure tags:
`![Image description](image.jpg "Image caption")`

= External Links in New Tab (Enable in Settings) =

When enabled, all external links automatically open in new tabs with proper security attributes.

== Developer Information ==

= Available Filters =

* `yansir_md_before_parse` - Modify Markdown before parsing
* `yansir_md_after_parse` - Modify HTML after parsing

= Requirements =

* WordPress 5.0 or higher
* PHP 5.6 or higher (PHP 7.4+ recommended)
* Modern browser (Chrome, Firefox, Safari, Edge)

= Contributing =

Visit our [GitHub repository](https://github.com/yansir/yansir-md) to contribute or report issues.

== Privacy Policy ==

This plugin does not collect any personal data. All content is stored locally in your WordPress database.

== Credits ==

* [SimpleMDE](https://simplemde.com/) - Markdown editor interface
* [Parsedown](https://parsedown.org/) - PHP Markdown parser
* WordPress Community