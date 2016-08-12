<?php
class Min_NewAtt_Helper_Output extends Mage_Catalog_Helper_Output {
    public function productAttribute($product, $attributeHtml, $attributeName)
    {
        switch($attributeName){
            case 'manufacture':
                break;
            default :
                $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeName);
                if ($attribute && $attribute->getId() && ($attribute->getFrontendInput() != 'media_image')
                    && (!$attribute->getIsHtmlAllowedOnFront() && !$attribute->getIsWysiwygEnabled())) {
                    if ($attribute->getFrontendInput() != 'price') {
                        $attributeHtml = $this->escapeHtml($attributeHtml);
                    }
                    if ($attribute->getFrontendInput() == 'textarea') {
                        $attributeHtml = nl2br($attributeHtml);
                    }
                }
                if ($attribute->getIsHtmlAllowedOnFront() && $attribute->getIsWysiwygEnabled()) {
                    if (Mage::helper('catalog')->isUrlDirectivesParsingAllowed()) {
                        $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
                    }
                }

                $attributeHtml = $this->process('productAttribute', $attributeHtml, array(
                    'product'   => $product,
                    'attribute' => $attributeName
                ));
                break;
        }


        return $attributeHtml;
    }
}