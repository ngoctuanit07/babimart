<?php
// create new cate by att
$this->startSetup();
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


//manufacturer


$category = Mage::getResourceModel('catalog/category_collection')
    ->addFieldToFilter('name', 'Thương hiệu')
    ->getFirstItem() ;
if ($id = $category->getId()) {
    $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');
    if ($attribute->usesSource()) {
        $path = $category->getPath();
        $options = $attribute->getSource()->getAllOptions(false);
        if (count($options)) {
            foreach ($options as $k => $val) {
                $name = $val['label'];
				$categoryCheck = Mage::getResourceModel('catalog/category_collection')
    ->addFieldToFilter('name', $name)
    ->getFirstItem();
	if($categoryCheck->getId()){
		
	}else{
		

                $category = Mage::getModel('catalog/category');
                $data = array(
                    'store_id' => 0,
                    'path' => $path,
                    'name' => $name,
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
$this->endSetup();
?>