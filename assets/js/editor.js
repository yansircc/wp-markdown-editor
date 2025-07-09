/**
 * Yansir MD 编辑器 JavaScript - SimpleMDE 版本
 */
window.yansirMD = {
    simplemde: null,
    originalContent: '',
    
    init: function() {
        var self = this;
        
        // 获取原始内容
        this.originalContent = jQuery('#content').val();
        
        // 处理转义字符 - 在加载内容前处理 # 号转义
        this.originalContent = this.preprocessContent(this.originalContent);
        
        // 初始化 SimpleMDE
        this.simplemde = new SimpleMDE({
            element: document.getElementById("yansir-md-editor"),
            spellChecker: false,
            status: false,
            toolbar: [
                {
                    name: "image",
                    action: function(editor) {
                        // 触发文件选择对话框
                        var input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/*';
                        input.onchange = function(e) {
                            if (e.target.files && e.target.files[0]) {
                                self.uploadImage(e.target.files[0]);
                            }
                        };
                        input.click();
                    },
                    className: "fa fa-picture-o",
                    title: "插入图片"
                },
                "|",
                "preview"
            ],
            renderingConfig: {
                singleLineBreaks: false,
                codeSyntaxHighlighting: true
            },
            initialValue: this.originalContent,
            previewRender: function(plainText, preview) {
                // 使用自定义预览渲染
                self.renderPreview(plainText, preview);
                return "加载中...";
            }
        });
        
        // 监听变化事件
        this.simplemde.codemirror.on("change", function() {
            self.syncContent();
        });
        
        // 初始化拖拽上传
        this.initDragDrop();
        
        // 表单提交前处理
        jQuery('#post').on('submit', this.beforeSubmit.bind(this));
    },
    
    /**
     * 预处理内容 - 处理转义问题
     */
    preprocessContent: function(content) {
        // 如果内容开头是 # 且后面跟着空格，说明可能是被错误转义的标题
        // 移除开头的反斜杠转义
        content = content.replace(/^\\#\s/gm, '# ');
        content = content.replace(/\n\\#\s/g, '\n# ');
        
        return content;
    },
    
    /**
     * 后处理内容 - 在保存前处理特殊情况
     */
    postprocessContent: function(content) {
        // 这里可以添加保存前的处理逻辑
        // 比如某些特殊字符的转义
        return content;
    },
    
    initDragDrop: function() {
        var self = this;
        var cm = this.simplemde.codemirror;
        var wrapper = cm.getWrapperElement();
        
        // 阻止默认拖拽行为
        wrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            wrapper.classList.add('dragging');
        });
        
        wrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            wrapper.classList.remove('dragging');
        });
        
        // 处理拖放
        wrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            wrapper.classList.remove('dragging');
            
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
        cm.on('paste', function(editor, e) {
            if (e.clipboardData && e.clipboardData.items) {
                var items = e.clipboardData.items;
                for (var i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        e.preventDefault();
                        var file = items[i].getAsFile();
                        self.uploadImage(file);
                    }
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
        var cm = this.simplemde.codemirror;
        var placeholder = '![上传中...](...)';
        cm.replaceSelection(placeholder);
        
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
                    var content = self.simplemde.value();
                    content = content.replace(placeholder, markdown);
                    self.simplemde.value(content);
                } else {
                    // 移除占位符
                    var content = self.simplemde.value();
                    content = content.replace(placeholder, '');
                    self.simplemde.value(content);
                    alert('图片上传失败: ' + (response.data.message || '未知错误'));
                }
            },
            error: function() {
                // 移除占位符
                var content = self.simplemde.value();
                content = content.replace(placeholder, '');
                self.simplemde.value(content);
                alert('图片上传失败');
            }
        });
    },
    
    renderPreview: function(plainText, preview) {
        var self = this;
        
        jQuery.ajax({
            url: yansir_md.ajax_url,
            type: 'POST',
            data: {
                action: 'yansir_md_preview',
                nonce: yansir_md.nonce,
                content: plainText
            },
            success: function(response) {
                if (response.success) {
                    preview.innerHTML = response.data.html;
                } else {
                    preview.innerHTML = '<p>预览失败</p>';
                }
            },
            error: function() {
                preview.innerHTML = '<p>预览加载错误</p>';
            }
        });
    },
    
    syncContent: function() {
        var content = this.simplemde.value();
        // 同步到原始的 content 字段
        jQuery('#content').val(content);
    },
    
    beforeSubmit: function() {
        // 获取编辑器内容 - 确保获取的是 Markdown 而不是 HTML
        var content = this.simplemde.value();
        
        // 后处理内容
        content = this.postprocessContent(content);
        
        // 确保内容被同步
        jQuery('#content').val(content);
        
        // 添加一个隐藏字段存储 Markdown 内容
        if (!jQuery('#yansir_md_content').length) {
            jQuery('<input>').attr({
                type: 'hidden',
                id: 'yansir_md_content',
                name: 'yansir_md_content'
            }).appendTo('#post');
        }
        
        jQuery('#yansir_md_content').val(content);
    }
};