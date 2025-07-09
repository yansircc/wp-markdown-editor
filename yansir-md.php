<?php
/**
 * Plugin Name: Yansir Markdown
 * Plugin URI: https://github.com/yansir/yansir-md
 * Description: 极简的 WordPress Markdown 编辑器
 * Version: 1.0.0
 * Author: Yansir
 * Author URI: https://github.com/yansir
 * License: GPL v3
 * Text Domain: yansir-markdown
 */

// 防止直接访问
if (!defined('WPINC')) {
    die;
}

// 定义插件常量
define('YANSIR_MD_VERSION', '1.0.0');
define('YANSIR_MD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YANSIR_MD_PLUGIN_URL', plugin_dir_url(__FILE__));

// 加载 Parsedown
require_once YANSIR_MD_PLUGIN_DIR . 'vendor/autoload.php';

// 加载核心类
require_once YANSIR_MD_PLUGIN_DIR . 'includes/class-yansir-md.php';

// 启动插件
function run_yansir_md() {
    $plugin = new Yansir_MD();
    $plugin->run();
}

// 在插件加载后运行
add_action('plugins_loaded', 'run_yansir_md');

// 激活钩子
register_activation_hook(__FILE__, function() {
    update_option('yansir_md_enable_footnotes', 'no');
    update_option('yansir_md_enable_figure', 'no');
    update_option('yansir_md_links_new_tab', 'no');
});

// 卸载逻辑在 uninstall.php 中处理

// 添加插件设置链接
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'yansir_md_add_settings_link');

function yansir_md_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=yansir-md-settings') . '">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
}