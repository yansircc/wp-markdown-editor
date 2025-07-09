<?php
/**
 * Footnote processor for Markdown content
 *
 * This file contains the footnotes processor that handles footnote syntax
 * in Markdown content, collecting footnote definitions and converting them
 * to HTML footnotes with proper links and formatting.
 *
 * @package    Yansir_MD
 * @since      1.0.0
 * @license    GPL-3.0+
 */
class Yansir_MD_Footnotes {
    
    private $footnotes = array();
    private $footnote_counter = 0;
    
    /**
     * 处理 Markdown 中的脚注
     */
    public function process($markdown) {
        // 重置脚注
        $this->footnotes = array();
        $this->footnote_counter = 0;
        
        // 第一步：收集脚注定义 [^1]: 脚注内容
        $markdown = preg_replace_callback(
            '/^\[\^(\w+)\]:\s*(.*)$/m',
            array($this, 'collect_footnote_definition'),
            $markdown
        );
        
        // 第二步：替换脚注引用 [^1]
        $markdown = preg_replace_callback(
            '/\[\^(\w+)\]/',
            array($this, 'replace_footnote_reference'),
            $markdown
        );
        
        return $markdown;
    }
    
    /**
     * 收集脚注定义
     */
    private function collect_footnote_definition($matches) {
        $id = $matches[1];
        $content = $matches[2];
        
        $this->footnotes[$id] = array(
            'content' => $content,
            'number' => null
        );
        
        // 返回空字符串，移除定义行
        return '';
    }
    
    /**
     * 替换脚注引用
     */
    private function replace_footnote_reference($matches) {
        $id = $matches[1];
        
        if (!isset($this->footnotes[$id])) {
            return $matches[0]; // 如果没有找到定义，保持原样
        }
        
        // 如果还没有分配编号，分配一个
        if ($this->footnotes[$id]['number'] === null) {
            $this->footnote_counter++;
            $this->footnotes[$id]['number'] = $this->footnote_counter;
        }
        
        $number = $this->footnotes[$id]['number'];
        
        // 返回一个临时标记，稍后会被替换为 HTML
        return "%%%FOOTNOTE_REF_{$number}%%%";
    }
    
    /**
     * 生成脚注 HTML
     */
    public function append_footnotes_html($html) {
        if (empty($this->footnotes)) {
            return $html;
        }
        
        // 替换脚注引用标记
        foreach ($this->footnotes as $id => $footnote) {
            if ($footnote['number'] !== null) {
                $number = $footnote['number'];
                $ref_html = '<sup id="fnref-' . $number . '"><a href="#fn-' . $number . '" class="footnote-ref">' . $number . '</a></sup>';
                $html = str_replace("%%%FOOTNOTE_REF_{$number}%%%", $ref_html, $html);
            }
        }
        
        // 添加脚注列表
        $footnotes_html = '<div class="footnotes"><hr><ol>';
        
        // 按编号排序脚注
        $sorted_footnotes = array();
        foreach ($this->footnotes as $id => $footnote) {
            if ($footnote['number'] !== null) {
                $sorted_footnotes[$footnote['number']] = $footnote;
            }
        }
        ksort($sorted_footnotes);
        
        foreach ($sorted_footnotes as $number => $footnote) {
            $footnotes_html .= '<li id="fn-' . $number . '">';
            $footnotes_html .= '<p>' . esc_html($footnote['content']);
            $footnotes_html .= ' <a href="#fnref-' . $number . '" class="footnote-backref">↩</a></p>';
            $footnotes_html .= '</li>';
        }
        
        $footnotes_html .= '</ol></div>';
        
        return $html . $footnotes_html;
    }
}