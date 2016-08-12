<?php

/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 3/14/2015
 * Time: 5:00 PM
 */
class Min_NewAtt_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function buildCate($subcats)
    {
        $html = "";
        foreach ($subcats as $subCatid) {
            $_category = Mage::getModel('catalog/category')->load($subCatid);
            if ($_category->getIsActive()) {
                $caturl = $_category->getURL();
                $catname = $_category->getName();
                if ($catname != 'Root Catalog' && $catname != 'Thương hiệu') {

                    $class = 'menu-item-link menu-item-depth-0';
                    $html .= '<li class="' . $class . '">';
                    $html .= '
                                    <a  href="' . $caturl . '" title="' . $catname . '">' . $catname . '</a>';
                    $html .= $class == 'menu-item-link menu-item-depth-' . ($_category->getLevel() - 1) . '  menu-item-parent' ? '<a href="#" class="arrow"><span>&gt;</span></a>' : '';
                    $html .= "</li>";

                }
            }
        }
        return $html;
    }

    public function getDetailOrder($id)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($id);
        $items = $order->getAllVisibleItems();
        $listProductId = array();
        foreach ($items as $product):
            $id = $product->getProductId();
            $listProductId[$id]['id'] = (int)$id;
            $listProductId[$id]['price'] = (int)number_format($product->getPrice(), 0, '', '');
            $listProductId[$id]['quantity'] = (int)number_format($product->getData('qty_ordered'), 0, '', '');
        endforeach;
        return $listProductId;
    }

    public function buildInfoProductsFb($storeCode)
    {
        $products = $this->_getProducts();
        $data = array();
        $helper = Mage::helper('min_newatt/report');
        $categoryModel = Mage::getModel('catalog/category');
        $store = $this->_getStoreByCode($storeCode);
        $helperImg = Mage::helper('catalog/image');
        foreach ($products as $_product) {
           
           $_product = $_product->setStoreId($store->getId())->load();
            $id = $_product->getId();
            $url = $_product->getProductUrl();
            $url = explode('?', $url);
            $url = $url[0] . '?___store=' . $storeCode;
            $bigImage =  (string)Mage::helper('catalog/image')->init($_product,'small_image')->resize(1000, 1000);
            $finalPrice = number_format($_product->getFinalPrice(), 0, '', '');
            $categoryIds = $_product->getCategoryids();
            $cateIdTree = array();
            foreach ($categoryIds as $key => $categoryId) {
                $categoryObj = $categoryModel->load($categoryId);
                $parent = $categoryObj->getParentCategory();
                if ($categoryObj->getName() !== 'Root Catalog' && $categoryObj->getName() !== 'Thương hiệu' && $parent->getId() !== '3477') {
                    $level = 'level_' . $categoryObj->getLevel();
                    $cateIdTree[$level][0] = $categoryObj->getName();
                }
            }
            $description = $_product->getMetaDescription();
            if($cateIdTree['level_3'][0] && $cateIdTree['level_2'][0]){
                $categories = $cateIdTree['level_2'][0] . ' > ' . $cateIdTree['level_3'][0];
            }
            if($cateIdTree['level_3'][0] && !$cateIdTree['level_2'][0]){
                $categories = $cateIdTree['level_2'][0];
            }
            if(!$cateIdTree['level_3'][0] && !$cateIdTree['level_2'][0]){
                $categories = $cateIdTree['level_3'][0];
            }
            $data[] = array(
                'id' => $id,
                'title' => $_product->getName(),
                'description' => $description,
                'google_product_category' => $categories,
                'product_type' => $cateIdTree['level_2'][0],
                'link' => $url,
                'image_link' => $bigImage,
                'condition' => 'new',
                'availability' => $_product->stock_item->is_in_stock == 1 ? 'in stock' : 'out stock',
                'sale_price' => $helper->currency($finalPrice) . ' VND',
                'gtin' => '',
                'brand' => $_product->getManufacturer() ? $_product->getAttributeText('manufacturer') : '',
                'mpn' => '',
                'item_group_id' => '',
                'gender' => '',
            );
//            if ($_product->getManufacturer()) {
  //              break;
    //        }
        }
        return $data;
    }

    protected function _getProducts()
    {
        return Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('price', array('gt' => '0'));
    }

    protected function _getStoreByCode($storeCode)
    {
        $stores = array_keys(Mage::app()->getStores());
        foreach ($stores as $id) {
            $store = Mage::app()->getStore($id);
            if ($store->getCode() == $storeCode) {
                return $store;
            }
        }
        return false;
    }
}
