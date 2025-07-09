<?php
/**
 * ParsedownExtra 包装类，修复一些兼容性问题
 */
class Yansir_MD_ParsedownExtra extends ParsedownExtra {
    
    protected function blockSetextHeader($Line, array $Block = null) {
        $Block = parent::blockSetextHeader($Line, $Block);
        
        // 修复 null 值问题
        if (!$Block || !isset($Block['element']['text'])) {
            return $Block;
        }
        
        if (preg_match('/[ ]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['text'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];
            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);
            $Block['element']['text'] = substr($Block['element']['text'], 0, $matches[0][1]);
        }
        
        return $Block;
    }
}