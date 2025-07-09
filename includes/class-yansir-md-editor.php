<?php
/**
 * Yansir MD 编辑器类
 */
class Yansir_MD_Editor {
    
    private $version;
    
    public function __construct($version) {
        $this->version = $version;
    }
    
    public function set_default_editor() {
        return 'html';
    }
    
    public function enqueue_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        // 检查当前文章是否启用 Markdown
        global $post;
        if ($post && get_post_meta($post->ID, '_yansir_md_enabled', true) !== 'yes') {
            return;
        }
        
        // 加载 CSS
        wp_enqueue_style(
            'yansir-md-editor',
            YANSIR_MD_PLUGIN_URL . 'assets/css/editor.css',
            array(),
            $this->version
        );
        
        // 加载 JS
        wp_enqueue_script(
            'yansir-md-editor',
            YANSIR_MD_PLUGIN_URL . 'assets/js/editor.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // 传递 AJAX URL
        wp_localize_script('yansir-md-editor', 'yansir_md', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yansir_md_nonce')
        ));
    }
    
    public function editor_init_script() {
        global $post;
        if (!$post || get_post_meta($post->ID, '_yansir_md_enabled', true) !== 'yes') {
            return;
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            // 隐藏默认编辑器
            $('#postdivrich').hide();
            
            // 创建 Markdown 编辑器
            var editorHTML = `
                <div id="yansir-md-editor-container">
                    <div class="yansir-md-toolbar">
                        <button type="button" class="button" onclick="yansirMD.togglePreview()">预览</button>
                    </div>
                    <div class="yansir-md-editor-wrap">
                        <textarea id="yansir-md-editor" class="yansir-md-editor"></textarea>
                        <div id="yansir-md-preview" class="yansir-md-preview" style="display:none;"></div>
                    </div>
                </div>
            `;
            
            $('#postdivrich').after(editorHTML);
            
            // 初始化编辑器
            window.yansirMD.init();
        });
        </script>
        <?php
    }
    
    public function add_meta_box() {
        add_meta_box(
            'yansir_md_meta_box',
            'Markdown 编辑器',
            array($this, 'render_meta_box'),
            array('post', 'page'),
            'side',
            'high'
        );
    }
    
    public function render_meta_box($post) {
        $enabled = get_post_meta($post->ID, '_yansir_md_enabled', true);
        wp_nonce_field('yansir_md_meta_box', 'yansir_md_meta_box_nonce');
        ?>
        <p>
            <label>
                <input type="checkbox" name="yansir_md_enabled" value="yes" <?php checked($enabled, 'yes'); ?>>
                启用 Markdown 编辑器
            </label>
        </p>
        <p class="description">勾选后将使用 Markdown 编辑器替代默认编辑器</p>
        <?php
    }
    
    public function save_meta_box($post_id) {
        if (!isset($_POST['yansir_md_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['yansir_md_meta_box_nonce'], 'yansir_md_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $enabled = isset($_POST['yansir_md_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_yansir_md_enabled', $enabled);
    }
    
    public function save_post($data, $postarr) {
        // 检查是否启用了 Markdown
        if (isset($_POST['yansir_md_enabled']) && $_POST['yansir_md_enabled'] === 'yes') {
            // 获取 Markdown 内容
            if (isset($_POST['yansir_md_content'])) {
                $markdown = wp_unslash($_POST['yansir_md_content']);
                
                // 保存 Markdown 到 post_content_filtered
                $data['post_content_filtered'] = $markdown;
                
                // 解析 Markdown 并保存到 post_content
                $parser = new Yansir_MD_Parser($this->version);
                $data['post_content'] = $parser->parse($markdown);
            }
        }
        
        return $data;
    }
    
    public function ajax_preview() {
        check_ajax_referer('yansir_md_nonce', 'nonce');
        
        $markdown = wp_unslash($_POST['content']);
        $parser = new Yansir_MD_Parser($this->version);
        $html = $parser->parse($markdown);
        
        wp_send_json_success(array('html' => $html));
    }
}