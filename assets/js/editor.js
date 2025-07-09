/**
 * Yansir MD 编辑器 JavaScript
 */
window.yansirMD = {
    editor: null,
    preview: null,
    isPreviewMode: false,
    
    init: function() {
        this.editor = document.getElementById('yansir-md-editor');
        this.preview = document.getElementById('yansir-md-preview');
        
        // 获取原始内容
        var content = jQuery('#content').val();
        if (content) {
            this.editor.value = content;
        }
        
        // 监听编辑器变化
        this.editor.addEventListener('input', this.onEditorChange.bind(this));
        
        // 初始化拖拽上传
        this.initDragDrop();
        
        // 同步内容到隐藏的表单字段
        this.syncContent();
        
        // 表单提交前同步内容
        jQuery('#post').on('submit', this.beforeSubmit.bind(this));
    },
    
    initDragDrop: function() {
        var self = this;
        var editor = this.editor;
        
        // 阻止默认拖拽行为
        editor.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            editor.classList.add('dragging');
        });
        
        editor.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            editor.classList.remove('dragging');
        });
        
        // 处理拖放
        editor.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            editor.classList.remove('dragging');
            
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                for (var i = 0; i < files.length; i++) {
                    if (files[i].type.indexOf('image') === 0) {
                        self.uploadImage(files[i]);
                    }
                }
            }
        });
        
        // 粘贴上传
        editor.addEventListener('paste', function(e) {
            var items = e.clipboardData.items;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    var file = items[i].getAsFile();
                    self.uploadImage(file);
                }
            }
        });
    },
    
    uploadImage: function(file) {
        var self = this;
        var formData = new FormData();
        
        formData.append('action', 'yansir_md_upload_image');
        formData.append('nonce', yansir_md.nonce);
        formData.append('image', file);
        
        // 插入占位符
        var placeholder = '\n![上传中...](...)\n';
        this.insertAtCursor(placeholder);
        
        jQuery.ajax({
            url: yansir_md.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    var imageUrl = response.data.url;
                    var imageName = file.name.replace(/\.[^/.]+$/, '');
                    var markdown = '![' + imageName + '](' + imageUrl + ')';
                    
                    // 替换占位符
                    self.editor.value = self.editor.value.replace(placeholder, '\n' + markdown + '\n');
                    self.onEditorChange();
                } else {
                    // 移除占位符
                    self.editor.value = self.editor.value.replace(placeholder, '');
                    alert('图片上传失败: ' + (response.data.message || '未知错误'));
                }
            },
            error: function() {
                // 移除占位符
                self.editor.value = self.editor.value.replace(placeholder, '');
                alert('图片上传失败');
            }
        });
    },
    
    insertAtCursor: function(text) {
        var startPos = this.editor.selectionStart;
        var endPos = this.editor.selectionEnd;
        var beforeText = this.editor.value.substring(0, startPos);
        var afterText = this.editor.value.substring(endPos);
        
        this.editor.value = beforeText + text + afterText;
        this.editor.selectionStart = startPos + text.length;
        this.editor.selectionEnd = startPos + text.length;
        this.editor.focus();
    },
    
    togglePreview: function() {
        this.isPreviewMode = !this.isPreviewMode;
        
        if (this.isPreviewMode) {
            this.showPreview();
        } else {
            this.hidePreview();
        }
    },
    
    showPreview: function() {
        var self = this;
        
        jQuery.ajax({
            url: yansir_md.ajax_url,
            type: 'POST',
            data: {
                action: 'yansir_md_preview',
                nonce: yansir_md.nonce,
                content: this.editor.value
            },
            success: function(response) {
                if (response.success) {
                    self.preview.innerHTML = response.data.html;
                    self.preview.style.display = 'block';
                }
            }
        });
    },
    
    hidePreview: function() {
        this.preview.style.display = 'none';
    },
    
    onEditorChange: function() {
        this.syncContent();
        
        // 如果在预览模式，更新预览
        if (this.isPreviewMode) {
            this.showPreview();
        }
    },
    
    syncContent: function() {
        // 同步到原始的 content 字段
        jQuery('#content').val(this.editor.value);
    },
    
    beforeSubmit: function() {
        // 确保内容被同步
        this.syncContent();
        
        // 添加一个隐藏字段存储 Markdown 内容
        if (!jQuery('#yansir_md_content').length) {
            jQuery('<input>').attr({
                type: 'hidden',
                id: 'yansir_md_content',
                name: 'yansir_md_content'
            }).appendTo('#post');
        }
        
        jQuery('#yansir_md_content').val(this.editor.value);
    }
};