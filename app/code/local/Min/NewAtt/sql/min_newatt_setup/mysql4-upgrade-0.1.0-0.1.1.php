<?php
$this->startSetup();
Mage::app()->setUpdateMode(false);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

//Switch to admin store (workaround to successfully save a category)
//manufacturer
$category = Mage::getResourceModel('catalog/category_collection')
    ->addFieldToFilter('name', 'Thương hiệu')
    ->getFirstItem() ;

//update categor
if (!$category->getId()) {
    $category = Mage::getModel('catalog/category');
    $data = array(
        'store_id'=>0,
        'path'=>"1/3",
        'name'=>'Thương hiệu',
        'is_active'=>'1',
        'url_key'=>'thuong-hieu',
        'description'=>'',
        'meta_title'=>'',
        'meta_keywords'=>'',
        'meta_description'=>'',
        'include_in_menu'=>'1',
        'catproduct'=>'1',
        'display_mode'=>'PRODUCTS',
        'landing_page'=>'',
        'is_anchor'=>'0',
        'custom_use_parent_settings'=>'1',
        'custom_apply_to_products'=>'0',
        'custom_design'=>'',
        'custom_design_from'=>'',
        'custom_design_to'=>'',
        'page_layout'=>'',
        'custom_layout_update'=>'',
        'available_sort_by'=>null,
        'default_sort_by'=>null,
        'filter_price_range'=>null,
        'attribute_set_id'=>'12',
        'posted_products'=>array(),
    );
    $category->setData($data)
        ->save();
}
$this->endSetup();
?>