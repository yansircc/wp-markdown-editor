<?php
/**
 * Yansir MD 核心类
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
        // 在 init 钩子上加载文本域，符合 WordPress 6.7+ 的要求
        add_action('init', function() {
            load_plugin_textdomain('yansir-md', false, dirname(plugin_basename(YANSIR_MD_PLUGIN_DIR)) . '/languages/');
        });
    }
    
    private function load_dependencies() {
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
    }
    
    private function define_public_hooks() {
        $parser = new Yansir_MD_Parser($this->version);
        
        // 前端显示时解析 Markdown
        add_filter('the_content', array($parser, 'parse_content'), 1);
    }
}