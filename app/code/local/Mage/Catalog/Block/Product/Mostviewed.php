<?php
/**
 * Created by PhpStorm.
 * User: mb471_253
 * Date: 25/9/14
 * Time: 10:32 PM
 */

class Mage_Catalog_Block_Product_Mostviewed extends Mage_Catalog_Block_Product_Abstract{
    public function __construct(){
        parent::__construct();
        $storeId    = Mage::app()->getStore()->getId();
        $products = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addAttributeToSelect('*')
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addViewsCount();
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize(5)->setCurPage(1);
        $this->setProductCollection($products);
    }
}