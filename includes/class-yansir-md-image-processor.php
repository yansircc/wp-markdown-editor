<?php
/**
 * Image to figure tag converter
 *
 * This file contains the image processor that converts standard img tags
 * to semantic HTML5 figure elements with figcaption when the image has
 * a title attribute, enhancing content structure and accessibility.
 *
 * @package    Yansir_MD
 * @since      1.0.0
 * @license    GPL-3.0+
 */
class Yansir_MD_Image_Processor {
    
    /**
     * 处理 HTML 中的图片标签
     */
    public function process($html) {
        // 使用 DOMDocument 来处理 HTML
        $dom = new DOMDocument('1.0', 'UTF-8');
        
        // 避免 HTML5 标签警告
        libxml_use_internal_errors(true);
        
        // 加载 HTML，添加 UTF-8 编码声明
        // 使用 mb_encode_numericentity 替代已弃用的 mb_convert_encoding
        $html = mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // 获取所有 img 标签
        $images = $dom->getElementsByTagName('img');
        $images_to_process = array();
        
        // 收集需要处理的图片（避免在循环中修改 DOM）
        foreach ($images as $img) {
            $images_to_process[] = $img;
        }
        
        // 处理每个图片
        foreach ($images_to_process as $img) {
            $this->processImage($dom, $img);
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
     * 处理单个图片
     */
    private function processImage($dom, $img) {
        // 检查是否已经在 figure 标签中
        if ($img->parentNode && $img->parentNode->nodeName === 'figure') {
            return;
        }
        
        // 获取图片属性
        $src = $img->getAttribute('src');
        $alt = $img->getAttribute('alt');
        $title = $img->getAttribute('title');
        
        // 如果没有 title，检查是否有特殊格式的 alt
        // 支持 ![alt文字](url "标题") 语法
        if (empty($title) && strpos($alt, '|') !== false) {
            list($alt_text, $title) = explode('|', $alt, 2);
            $alt = trim($alt_text);
            $title = trim($title);
        }
        
        // 创建 figure 元素
        $figure = $dom->createElement('figure');
        
        // 克隆 img 元素
        $new_img = $img->cloneNode(true);
        
        // 如果有 title，移除 img 的 title 属性（将显示为 figcaption）
        if (!empty($title)) {
            $new_img->removeAttribute('title');
        }
        
        // 将 img 添加到 figure
        $figure->appendChild($new_img);
        
        // 如果有 title，创建 figcaption
        if (!empty($title)) {
            $figcaption = $dom->createElement('figcaption');
            $figcaption->appendChild($dom->createTextNode($title));
            $figure->appendChild($figcaption);
        }
        
        // 替换原始的 img 元素
        $img->parentNode->replaceChild($figure, $img);
    }
    
    /**
     * 预处理 Markdown，支持扩展的图片语法
     */
    public function preprocessMarkdown($markdown) {
        // 支持 ![alt|title](url) 语法作为 ![alt](url "title") 的替代
        $markdown = preg_replace_callback(
            '/!\[([^\]]+)\]\(([^\s\)]+)(?:\s+"([^"]+)")?\)/',
            function($matches) {
                $alt = $matches[1];
                $url = $matches[2];
                $title = isset($matches[3]) ? $matches[3] : '';
                
                // 如果 alt 中包含 |，分割为 alt 和 title
                if (empty($title) && strpos($alt, '|') !== false) {
                    list($alt, $title) = explode('|', $alt, 2);
                    $alt = trim($alt);
                    $title = trim($title);
                }
                
                // 重新构建 Markdown
                if (!empty($title)) {
                    return "![$alt]($url \"$title\")";
                } else {
                    return "![$alt]($url)";
                }
            },
            $markdown
        );
        
        return $markdown;
    }
}