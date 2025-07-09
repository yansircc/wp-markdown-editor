<?php
/**
 * Plugin settings and configuration class
 *
 * This file contains the settings class that handles plugin configuration
 * options, settings page rendering, and option registration for features
 * like footnotes, figure tags, and link targets.
 *
 * @package    Yansir_MD
 * @since      1.0.0
 * @license    GPL-3.0+
 */
class Yansir_MD_Settings {
    
    private $version;
    
    public function __construct($version) {
        $this->version = $version;
    }
    
    public function add_settings_page() {
        add_options_page(
            'Yansir Markdown 设置',
            'Yansir Markdown',
            'manage_options',
            'yansir-md-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('yansir_md_settings', 'yansir_md_enable_footnotes', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => 'no'
        ));
        register_setting('yansir_md_settings', 'yansir_md_enable_figure', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => 'no'
        ));
        register_setting('yansir_md_settings', 'yansir_md_links_new_tab', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => 'no'
        ));
    }
    
    public function sanitize_checkbox($input) {
        return ($input === 'yes') ? 'yes' : 'no';
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Yansir Markdown 设置</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('yansir_md_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">启用脚注功能</th>
                        <td>
                            <label>
                                <input type="checkbox" name="yansir_md_enable_footnotes" value="yes" 
                                    <?php checked(get_option('yansir_md_enable_footnotes'), 'yes'); ?>>
                                启用 Markdown Extra 的脚注语法
                            </label>
                            <p class="description">
                                启用后可以使用 [^1] 这样的脚注语法<br>
                                示例：这是一段文字[^1]。<br>
                                [^1]: 这是脚注内容。
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">使用 Figure 标签显示图片</th>
                        <td>
                            <label>
                                <input type="checkbox" name="yansir_md_enable_figure" value="yes" 
                                    <?php checked(get_option('yansir_md_enable_figure'), 'yes'); ?>>
                                将图片转换为 figure 标签，title 属性显示为 figcaption
                            </label>
                            <p class="description">
                                启用后，图片语法会转换为语义化的 HTML5 figure 元素<br>
                                示例：![alt文字](图片地址 "标题文字")<br>
                                将转换为：&lt;figure&gt;&lt;img src="..." alt="..."&gt;&lt;figcaption&gt;标题文字&lt;/figcaption&gt;&lt;/figure&gt;
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">在新标签页打开链接</th>
                        <td>
                            <label>
                                <input type="checkbox" name="yansir_md_links_new_tab" value="yes" 
                                    <?php checked(get_option('yansir_md_links_new_tab'), 'yes'); ?>>
                                所有外部链接在新标签页中打开
                            </label>
                            <p class="description">
                                启用后，文章中的所有链接（除脚注链接外）都会添加 target="_blank" 属性<br>
                                同时添加 rel="noopener noreferrer" 以提高安全性
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>使用说明</h2>
            <p>1. 在编写文章时，勾选侧边栏的"启用 Markdown 编辑器"选项</p>
            <p>2. 使用 Markdown 语法编写内容</p>
            <p>3. 点击"预览"按钮查看渲染效果</p>
            
            <h3>支持的 Markdown 语法</h3>
            <ul>
                <li>标题：# H1, ## H2, ### H3 等</li>
                <li>粗体：**粗体文字**</li>
                <li>斜体：*斜体文字*</li>
                <li>链接：[链接文字](http://example.com)</li>
                <li>图片：![alt文字](图片地址)</li>
                <li>代码：`行内代码` 或 ```代码块```</li>
                <li>列表：- 项目1 或 1. 项目1</li>
                <li>引用：> 引用文字</li>
                <li>分割线：---</li>
                <?php if (get_option('yansir_md_enable_footnotes') === 'yes'): ?>
                <li>脚注：[^1] 和 [^1]: 脚注内容</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php
    }
}