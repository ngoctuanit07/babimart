<?php
// sync product to category new by att
$this->startSetup();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

// manufacturer.
/*
$_products = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter('manufacturer', array('neq' => NULL));
if (count($_products)) {
    $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');
    foreach ($_products as $product) {
        $ids = explode(',', $product->getManufacturer());
        if (is_array($ids) && count($ids)) {
            $cateIds = $product->getCategoryIds();
            foreach ($ids as $id) {
                $text = $attribute->getSource()->getOptionText($id);
                $category = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('name', $text)
                    ->getFirstItem();
                if ($category->getId()) {

                    $new_cateId = array(
                        $category->getId()
                    );
                        $cateIds = array_unique(array_merge($cateIds,$new_cateId));
                }
            }
            $product->setCategoryIds($cateIds)->save();
        }
    }
}*/
$this->endSetup();
?>