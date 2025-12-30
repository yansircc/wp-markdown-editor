#!/bin/bash

# Yansir Markdown 插件构建脚本
# 用途：打包插件为 zip 文件，文件名包含版本号

set -e  # 遇到错误立即退出

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 开始构建 Yansir Markdown 插件...${NC}"

# 获取脚本所在目录（插件根目录）
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_FOLDER_NAME="$(basename "$PLUGIN_DIR")"  # 28-wp-markdown-editor
PLUGIN_NAME="yansir-md"  # zip 文件名前缀

# 从主文件中提取版本号
VERSION=$(grep "Version:" "$PLUGIN_DIR/yansir-md.php" | head -1 | awk '{print $3}')

if [ -z "$VERSION" ]; then
    echo -e "${RED}❌ 错误：无法从 yansir-md.php 中读取版本号${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 插件版本: ${VERSION}${NC}"
echo -e "${YELLOW}📁 目录名称: ${PLUGIN_FOLDER_NAME}${NC}"

# 构建目标文件名和路径
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"
ZIP_PATH="${PLUGIN_DIR}/${ZIP_NAME}"

# 如果已存在同名文件，先删除
if [ -f "$ZIP_PATH" ]; then
    echo -e "${YELLOW}⚠️  删除已存在的文件: ${ZIP_NAME}${NC}"
    rm "$ZIP_PATH"
fi

# 切换到插件的父目录
PARENT_DIR="$(dirname "$PLUGIN_DIR")"
cd "$PARENT_DIR"

# 压缩插件目录（保持目录结构：zip 内部会有 28-wp-markdown-editor/ 文件夹）
echo -e "${YELLOW}📝 正在压缩文件...${NC}"
zip -r "$ZIP_PATH" "$PLUGIN_FOLDER_NAME" \
    -x "*/.git/*" \
    -x "*/.git" \
    -x "*/node_modules/*" \
    -x "*/.DS_Store" \
    -x "*/.vscode/*" \
    -x "*/composer.lock" \
    -x "*/build.sh" \
    -x "*/.editorconfig" \
    -x "*/.gitignore" \
    -x "*/${PLUGIN_NAME}-*.zip" \
    -x "*/docs/*" \
    -x "*/README.md" \
    -x "*/CLAUDE.md" \
    -x "*/phpcs.xml" \
    -x "*/.wordpress-org/*" \
    -q  # 安静模式，不显示文件列表

# 检查是否成功
if [ -f "$ZIP_PATH" ]; then
    FILE_SIZE=$(ls -lh "$ZIP_PATH" | awk '{print $5}')
    echo -e "${GREEN}✅ 构建成功！${NC}"
    echo -e "${GREEN}📦 文件: ${ZIP_NAME}${NC}"
    echo -e "${GREEN}📍 位置: ${ZIP_PATH}${NC}"
    echo -e "${GREEN}💾 大小: ${FILE_SIZE}${NC}"
else
    echo -e "${RED}❌ 构建失败${NC}"
    exit 1
fi
