<?php
/**
 * Yansir MD 卸载脚本
 */

// 如果不是通过 WordPress 卸载，退出
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 删除选项
delete_option('yansir_md_enable_footnotes');
delete_option('yansir_md_enable_figure');
delete_option('yansir_md_links_new_tab');

// 清理所有文章的 Markdown 元数据
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_yansir_md_enabled'");