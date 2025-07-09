<?php
/**
 * Yansir MD 解析器类
 */
class Yansir_MD_Parser {
    
    private $version;
    private $parser;
    
    public function __construct($version) {
        $this->version = $version;
        $this->init_parser();
    }
    
    private function init_parser() {
        $enable_footnotes = get_option('yansir_md_enable_footnotes', 'no');
        
        if ($enable_footnotes === 'yes') {
            // 使用 Parsedown Extra 以支持脚注
            $this->parser = new ParsedownExtra();
            // 对于 ParsedownExtra，安全模式可能导致一些问题
            $this->parser->setSafeMode(false);
        } else {
            // 使用基础 Parsedown
            $this->parser = new Parsedown();
            // 安全模式
            $this->parser->setSafeMode(true);
        }
    }
    
    public function parse($markdown) {
        // 确保内容不为空
        if (empty($markdown)) {
            return '';
        }
        
        // 确保内容是字符串
        $markdown = (string) $markdown;
        
        // 应用过滤器，允许其他插件修改 Markdown
        $markdown = apply_filters('yansir_md_before_parse', $markdown);
        
        // 再次检查过滤后的内容
        if (empty($markdown)) {
            return '';
        }
        
        // 使用 try-catch 避免解析错误
        try {
            // 解析 Markdown
            $html = $this->parser->text($markdown);
        } catch (Exception $e) {
            // 如果解析失败，返回原始内容的 HTML 转义版本
            $html = wp_kses_post($markdown);
        }
        
        // 应用过滤器，允许其他插件修改解析后的 HTML
        $html = apply_filters('yansir_md_after_parse', $html);
        
        return $html;
    }
    
    public function parse_content($content) {
        global $post;
        
        // 检查是否为文章页面
        if (!is_singular() || !$post) {
            return $content;
        }
        
        // 检查是否启用了 Markdown
        if (get_post_meta($post->ID, '_yansir_md_enabled', true) !== 'yes') {
            return $content;
        }
        
        // 获取 Markdown 内容
        $markdown = $post->post_content_filtered;
        if (empty($markdown)) {
            return $content;
        }
        
        // 解析并返回
        return $this->parse($markdown);
    }
}