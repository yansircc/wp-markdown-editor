<?php
/**
 * 链接处理类 - 为链接添加 target="_blank" 属性
 */
class Yansir_MD_Link_Processor {
    
    /**
     * 处理 HTML 中的链接
     */
    public function process($html) {
        // 使用 DOMDocument 来处理 HTML
        $dom = new DOMDocument('1.0', 'UTF-8');
        
        // 避免 HTML5 标签警告
        libxml_use_internal_errors(true);
        
        // 加载 HTML，添加 UTF-8 编码声明
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // 获取所有 a 标签
        $links = $dom->getElementsByTagName('a');
        
        // 处理每个链接
        foreach ($links as $link) {
            $this->processLink($link);
        }
        
        // 获取处理后的 HTML
        $processed_html = $dom->saveHTML();
        
        // 提取 body 内容
        if (preg_match('/<body>(.*)<\/body>/s', $processed_html, $matches)) {
            $processed_html = $matches[1];
        }
        
        // 清理错误
        libxml_clear_errors();
        
        return $processed_html;
    }
    
    /**
     * 处理单个链接
     */
    private function processLink($link) {
        // 获取 href 属性
        $href = $link->getAttribute('href');
        
        // 如果没有 href，跳过
        if (empty($href)) {
            return;
        }
        
        // 检查是否是脚注链接（以 #fn- 或 #fnref- 开头）
        if (strpos($href, '#fn-') === 0 || strpos($href, '#fnref-') === 0) {
            return;
        }
        
        // 检查是否是页内锚点链接
        if (strpos($href, '#') === 0) {
            return;
        }
        
        // 检查是否已经有 target 属性
        if ($link->hasAttribute('target')) {
            return;
        }
        
        // 添加 target="_blank" 和安全属性
        $link->setAttribute('target', '_blank');
        
        // 获取现有的 rel 属性
        $rel = $link->getAttribute('rel');
        $rel_values = empty($rel) ? array() : explode(' ', $rel);
        
        // 添加 noopener 和 noreferrer
        if (!in_array('noopener', $rel_values)) {
            $rel_values[] = 'noopener';
        }
        if (!in_array('noreferrer', $rel_values)) {
            $rel_values[] = 'noreferrer';
        }
        
        // 设置更新后的 rel 属性
        $link->setAttribute('rel', implode(' ', $rel_values));
    }
    
    /**
     * 使用正则表达式的备用方法（如果 DOMDocument 出现问题）
     */
    public function processWithRegex($html) {
        // 匹配所有 <a> 标签
        $pattern = '/<a\s+([^>]*?)href=(["\'])([^"\']+)\2([^>]*?)>/i';
        
        $html = preg_replace_callback($pattern, function($matches) {
            $before_href = $matches[1];
            $quote = $matches[2];
            $href = $matches[3];
            $after_href = $matches[4];
            
            // 检查是否是脚注或锚点链接
            if (strpos($href, '#fn-') === 0 || strpos($href, '#fnref-') === 0 || strpos($href, '#') === 0) {
                return $matches[0];
            }
            
            // 检查是否已经有 target="_blank"
            if (strpos($matches[0], 'target=') !== false) {
                return $matches[0];
            }
            
            // 检查现有的 rel 属性
            if (preg_match('/rel=(["\'])([^"\']*)\1/i', $matches[0], $rel_match)) {
                $rel_values = explode(' ', $rel_match[2]);
                if (!in_array('noopener', $rel_values)) {
                    $rel_values[] = 'noopener';
                }
                if (!in_array('noreferrer', $rel_values)) {
                    $rel_values[] = 'noreferrer';
                }
                $new_rel = implode(' ', $rel_values);
                $matches[0] = str_replace($rel_match[0], 'rel=' . $rel_match[1] . $new_rel . $rel_match[1], $matches[0]);
            } else {
                // 没有 rel 属性，添加一个
                $after_href .= ' rel="noopener noreferrer"';
            }
            
            // 添加 target="_blank"
            return '<a ' . $before_href . 'href=' . $quote . $href . $quote . $after_href . ' target="_blank">';
        }, $html);
        
        return $html;
    }
}