<?php
/**
 * Yansir Markdown 编辑器类
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
        
        // 加载 SimpleMDE CSS
        wp_enqueue_style(
            'simplemde',
            'https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css',
            array(),
            '1.11.2'
        );
        
        // 加载自定义 CSS
        wp_enqueue_style(
            'yansir-md-editor',
            YANSIR_MD_PLUGIN_URL . 'assets/css/editor.css',
            array('simplemde'),
            $this->version
        );
        
        // 加载 SimpleMDE JS
        wp_enqueue_script(
            'simplemde',
            'https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js',
            array(),
            '1.11.2',
            true
        );
        
        // 加载 WordPress 媒体库
        wp_enqueue_media();
        
        // 加载自定义 JS
        wp_enqueue_script(
            'yansir-md-editor',
            YANSIR_MD_PLUGIN_URL . 'assets/js/editor.js',
            array('jquery', 'simplemde'),
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
            
            // 创建 SimpleMDE 编辑器容器
            var editorHTML = `
                <div id="yansir-md-editor-container">
                    <textarea id="yansir-md-editor"></textarea>
                </div>
            `;
            
            $('#postdivrich').after(editorHTML);
            
            // 初始化编辑器
            window.yansirMD.init();
            
            // 获取 Markdown 内容（现在直接从 post_content 获取）
            <?php 
            global $post;
            $markdown_content = '';
            if ($post && !empty($post->post_content)) {
                // 只进行 JavaScript 转义，不进行 HTML 实体编码
                $markdown_content = esc_js($post->post_content);
            }
            ?>
            
            var markdownContent = '<?php echo $markdown_content; ?>';
            if (markdownContent) {
                $('#yansir-md-editor').val(markdownContent);
            }
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
        // 检查是否是自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $data;
        }
        
        // 检查是否启用了 Markdown
        if (isset($_POST['yansir_md_enabled']) && $_POST['yansir_md_enabled'] === 'yes') {
            // 获取 Markdown 内容
            if (isset($_POST['yansir_md_content']) && !empty($_POST['yansir_md_content'])) {
                // 使用 wp_unslash 移除转义
                $markdown = wp_unslash($_POST['yansir_md_content']);
                
                // 直接保存 Markdown 到 post_content（像 markup-markdown 一样）
                $data['post_content'] = $markdown;
                
                // 同时保存到 post_content_filtered 作为备份
                $data['post_content_filtered'] = $markdown;
            }
        }
        
        return $data;
    }
    
    public function ajax_preview() {
        check_ajax_referer('yansir_md_nonce', 'nonce');
        
        // 获取内容并移除转义
        $markdown = wp_unslash($_POST['content']);
        
        // 确保内容没有被过度编码
        $markdown = html_entity_decode($markdown, ENT_QUOTES, 'UTF-8');
        
        $parser = new Yansir_MD_Parser($this->version);
        $html = $parser->parse($markdown);
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function ajax_upload_image() {
        check_ajax_referer('yansir_md_nonce', 'nonce');
        
        if (!isset($_FILES['image'])) {
            wp_send_json_error(array('message' => '没有接收到图片'));
        }
        
        $file = $_FILES['image'];
        
        // 检查是否是图片
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            wp_send_json_error(array('message' => '上传的文件不是有效的图片'));
        }
        
        // 处理上传
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error(array('message' => $upload['error']));
        }
        
        // 添加到媒体库
        $attachment_id = wp_insert_attachment(array(
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        ), $upload['file']);
        
        // 生成图片元数据
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        wp_send_json_success(array(
            'url' => wp_get_attachment_url($attachment_id),
            'id' => $attachment_id
        ));
    }
}