<?php
class Min_NewAtt_Model_Observer
{
    public function updateCategory($observer)
    {
        $product = $observer->getProduct();
        $id = $product->getManufacturer();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');
        $text = $attribute->getSource()->getOptionText($id);
        $id_parrent = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', 'Thương hiệu')
            ->getFirstItem()->getId();
        $category = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', $text)
            ->addFieldToFilter('parent_id', $id_parrent)
            ->getFirstItem();
        if ($category->getId()) {
            $cateIds = $product->getCategoryIds();
            $cateNew = array($category->getId(),$category->getParentId().'');
            $cateIdsx = array_unique(array_merge($cateIds,$cateNew));
            $product->setCategoryIds($cateIdsx);
        }
    }
    public function updateCategoryAtt($ob){
        if($ob->getAttribute()->getAttributeCode()=='manufacturer') {
            Mage::app()->setUpdateMode(false);
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $options = $ob->getAttribute()->getOption();
            $options = $options['value'];
            if (count($options)) {
                foreach ($options as $key=>$option) {
                    //Switch to admin store (workaround to successfully save a category)

                    $category = Mage::getResourceModel('catalog/category_collection')
                        ->addFieldToFilter('name', $option[0])
                        ->getFirstItem();

                    //update category
                    $checkKey = (int)$key;
                    if (!$category->getId()&& $checkKey==0) {
                        if($ob->getAttribute()->getAttributeCode()=='manufacturer'){
                            $name = "Thương hiệu";

                        }
                        $categoryId = Mage::getResourceModel('catalog/category_collection')
                            ->addFieldToFilter('name', $name)
                            ->getFirstItem()->getId();
                        $category = Mage::getModel('catalog/category');
                        $data = array(
                            'store_id' => 0,
                            'path' => "1/3/" . $categoryId,
                            'name' => $option[0],
                            'is_active' => '1',
                            'description' => '',
                            'meta_title' => '',
                            'meta_keywords' => '',
                            'meta_description' => '',
                            'include_in_menu' => '0',
                            'catproduct' => '1',
                            'display_mode' => 'PRODUCTS',
                            'landing_page' => '',
                            'is_anchor' => '0',
                            'custom_use_parent_settings' => '1',
                            'custom_apply_to_products' => '0',
                            'custom_design' => '',
                            'custom_design_from' => '',
                            'custom_design_to' => '',
                            'page_layout' => '',
                            'custom_layout_update' => '',
                            'available_sort_by' => null,
                            'default_sort_by' => null,
                            'filter_price_range' => null,
                            'attribute_set_id' => '12',
                            'posted_products' => array(),
                        );
                        $category->setData($data)
                            ->save();
                    }
                }
            }
        }
    }
}
