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
            
            // 获取 Markdown 内容（优先使用 post_content_filtered）
            <?php 
            global $post, $wpdb;
            $markdown_content = '';
            if ($post) {
                // 直接从数据库获取原始内容，避免任何过滤
                $raw_content = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_content_filtered FROM {$wpdb->posts} WHERE ID = %d",
                    $post->ID
                ));
                
                if (!empty($raw_content)) {
                    // 只进行 JavaScript 转义，不进行 HTML 实体编码
                    $markdown_content = esc_js($raw_content);
                } elseif (!empty($post->post_content)) {
                    // 如果没有 Markdown 内容，使用 HTML 内容
                    $markdown_content = esc_js($post->post_content);
                }
            }
            ?>
            
            var markdownContent = '<?php echo $markdown_content; ?>';
            if (markdownContent) {
                $('#yansir-md-editor').val(markdownContent);
                window.yansirMD.syncContent();
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
        
        // 如果启用了 Markdown，保存原始 Markdown 内容
        if ($enabled === 'yes') {
            global $yansir_md_temp_content;
            if (!empty($yansir_md_temp_content)) {
                // 直接更新数据库，绕过所有过滤器
                global $wpdb;
                $wpdb->update(
                    $wpdb->posts,
                    array('post_content_filtered' => $yansir_md_temp_content),
                    array('ID' => $post_id),
                    array('%s'),
                    array('%d')
                );
                
                // 清空临时内容
                $yansir_md_temp_content = '';
            }
        }
    }
    
    public function save_post($data, $postarr) {
        // 检查是否启用了 Markdown
        if (isset($_POST['yansir_md_enabled']) && $_POST['yansir_md_enabled'] === 'yes') {
            // 获取 Markdown 内容
            if (isset($_POST['yansir_md_content'])) {
                // 使用 wp_unslash 移除转义
                $markdown = wp_unslash($_POST['yansir_md_content']);
                
                // 解析 Markdown 并保存到 post_content
                $parser = new Yansir_MD_Parser($this->version);
                $data['post_content'] = $parser->parse($markdown);
                
                // 暂时不保存到 post_content_filtered，稍后通过钩子保存
                // 这样可以绕过 WordPress 的过滤
                $data['post_content_filtered'] = '';
                
                // 使用全局变量临时存储 Markdown 内容
                global $yansir_md_temp_content;
                $yansir_md_temp_content = $markdown;
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