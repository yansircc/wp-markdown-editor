<?php
/**
 * Main plugin orchestrator class
 *
 * This file contains the main plugin class that coordinates all components
 * of the Yansir Markdown plugin, including dependency loading, hook setup,
 * and initialization of admin and public functionality.
 *
 * @package    Yansir_MD
 * @since      1.0.0
 * @license    GPL-3.0+
 */
class Yansir_MD {
    
    private $version;
    
    public function __construct() {
        $this->version = YANSIR_MD_VERSION;
    }
    
    public function run() {
        $this->load_dependencies();
        $this->load_textdomain();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    private function load_textdomain() {
        // WordPress 4.6+ 会自动加载托管在 WordPress.org 的插件翻译
        // 不再需要手动调用 load_plugin_textdomain()
    }
    
    private function load_dependencies() {
        require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md-footnotes.php';
        require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md-image-processor.php';
        require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md-link-processor.php';
        require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md-editor.php';
        require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md-parser.php';
        require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md-settings.php';
    }
    
    private function define_admin_hooks() {
        $editor = new Yansir_MD_Editor($this->version);
        $settings = new Yansir_MD_Settings($this->version);
        
        // 替换编辑器
        add_filter('wp_default_editor', array($editor, 'set_default_editor'));
        add_action('admin_enqueue_scripts', array($editor, 'enqueue_scripts'));
        add_action('admin_print_footer_scripts', array($editor, 'editor_init_script'));
        
        // 保存文章时处理 Markdown
        add_filter('wp_insert_post_data', array($editor, 'save_post'), 10, 2);
        
        // 添加元框
        add_action('add_meta_boxes', array($editor, 'add_meta_box'));
        add_action('save_post', array($editor, 'save_meta_box'));
        
        // 设置页面
        add_action('admin_menu', array($settings, 'add_settings_page'));
        add_action('admin_init', array($settings, 'register_settings'));
        
        // AJAX 处理
        add_action('wp_ajax_yansir_md_preview', array($editor, 'ajax_preview'));
        add_action('wp_ajax_yansir_md_upload_image', array($editor, 'ajax_upload_image'));
    }
    
    private function define_public_hooks() {
        $parser = new Yansir_MD_Parser($this->version);
        
        // 前端显示时解析 Markdown（优先级设为 5，在 wpautop 之前）
        add_filter('the_content', array($parser, 'parse_content'), 5);
    }
}