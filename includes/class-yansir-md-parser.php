<?php
/**
 * Yansir MD 解析器类
 */
class Yansir_MD_Parser {
    
    private $version;
    private $parser;
    private $footnotes_processor;
    private $image_processor;
    
    public function __construct($version) {
        $this->version = $version;
        $this->init_parser();
    }
    
    private function init_parser() {
        // 始终使用基础 Parsedown
        $this->parser = new Parsedown();
        $this->parser->setSafeMode(true);
        
        // 如果启用脚注，初始化脚注处理器
        $enable_footnotes = get_option('yansir_md_enable_footnotes', 'no');
        if ($enable_footnotes === 'yes') {
            $this->footnotes_processor = new Yansir_MD_Footnotes();
        }
        
        // 如果启用 figure，初始化图片处理器
        $enable_figure = get_option('yansir_md_enable_figure', 'no');
        if ($enable_figure === 'yes') {
            $this->image_processor = new Yansir_MD_Image_Processor();
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
        
        // 如果启用图片处理，预处理 Markdown
        if ($this->image_processor) {
            $markdown = $this->image_processor->preprocessMarkdown($markdown);
        }
        
        // 如果启用脚注，先处理脚注
        if ($this->footnotes_processor) {
            $markdown = $this->footnotes_processor->process($markdown);
        }
        
        // 使用 try-catch 避免解析错误
        try {
            // 解析 Markdown
            $html = $this->parser->text($markdown);
            
            // 如果启用脚注，添加脚注 HTML
            if ($this->footnotes_processor) {
                $html = $this->footnotes_processor->append_footnotes_html($html);
            }
            
            // 如果启用图片处理，处理 HTML 中的图片
            if ($this->image_processor) {
                $html = $this->image_processor->process($html);
            }
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