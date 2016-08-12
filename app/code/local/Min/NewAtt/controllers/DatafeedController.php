<?php

class Min_NewAtt_DatafeedController extends Mage_Core_Controller_Front_Action
{
	const LIMIT = 50;
    /**
     *
     */
    public function reportwssAction()
    {
        $passwordCon = Mage::getStoreConfig('min_newatt/report/password_wss');
        $pass = $this->getRequest()->getParam('password');
        if ($pass !== $passwordCon) {
			echo 'Password incorrect!';
			die();
		}
		$helper = Mage::helper('min_newatt/report');
		$productsCollection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		$categoryModel = Mage::getModel('catalog/category');
		$modelMedia = Mage::getModel('catalog/product_media_config');

		$data = array();

		foreach ($productsCollection as $_product) {
			$_product->load();
			if ($_product->getPrice() <= 0) {
				continue;
			}
			$id = $_product->getId();
			$url = $_product->getProductUrl();
			$bigimage = $modelMedia->getMediaUrl($_product->getImage());
			$smallimage = $modelMedia->getMediaUrl($_product->getSmallImage());
			$price = number_format($_product->getPrice(), 0, '', '');
			$finalPrice = number_format($_product->getFinalPrice(), 0, '', '');
			$discount = $finalPrice < $price ? 100 - round(($finalPrice / $price) * 100) : 0;
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
			$data[$id] = array(
				//SKU sản phẩm
				'simple_sku' => $_product->getSku(),
				//SKU sản phẩm cha nếu có
				'parent_sku' => '',
				//Có sẵn hàng hay không
				'availability_instock' => $_product->stock_item->is_in_stock == 1 ? true : false,
				//Brand name
				'brand' => '',
				//Tên sản phẩm
				'product_name' => $_product->getName(),
				//Mô tả sản phẩm
				'description' => $description,
				//Mệnh giá tiền sử dụng VND/USD
				'currency' => 'VND',
				//Giá sản phẩm khi chưa khuyến mãi (format US currency -> xx,xxx,xxx.xx)
				'price' => $helper->currency($_product->getPrice()),
				//Số tiền khuyến mãi (format US currency -> xx,xxx,xxx.xx)
				'discount' => $discount,
				//Giá sau khi khuyến mãi (nếu không có khuyến mãi thì để bằng giá ban đầu, hoặc không điền (format US currency -> xx,xxx,xxx.xx)
				'discounted_price' => $helper->currency($_product->getPrice()),
				//Category1 cha của cha
				'parent_of_parent_of_cat1' => $cateIdTree['level_4'][0],
				//Category1 cha
				'parent_of_cat_1' => $cateIdTree['level_3'][0],
				//Category1 sản phẩm
				'category_1' => $cateIdTree['level_2'][0],
				//Category2 cha của cha (nếu có)
				'parent_of_parent_of_cat2' => '',
				//Category2 cha (nếu có)
				'parent_of_cat_2' => '',
				//Category2 sản phẩm (nếu có)
				'category_2' => '',
				//Category3 cha của cha (nếu có)
				'parent_of_parent_of_cat3' => '',
				//Category3 cha (nếu có)
				'parent_of_cat3' => '',
				//Category3 sản phẩm (nếu có)
				'category_3' => '',
				//Ảnh sản phẩm (Ảnh đại diện)
				'picture_url' => $bigimage,
				//Ảnh sản phẩm 2
				'picture_url2' => $smallimage,
				//Ảnh sản phẩm 3
				'picture_url3' => '',
				//Ảnh sản phẩm 4
				'picture_url4' => '',
				//Ảnh sản phẩm 5
				'picture_url5' => '',
				//Đường dẫn đến bài viết sản phẩm
				'URL' => $url,
				//Thông tin khuyến mãi
				'promotion' => '',
				//Thời gian giao hàng
				'delivery_period' => ''
			);
		}
		header("HTTP/1.1 200 OK");
		$helper->setData($data);
		$helper->display();
		die();
        
    }
	
	/**
     *
     */
    public function getPageAction()
    {
        $this->_validate();
		$productsCollection = $this->_getProducts();
			$result = array(
				'feed_url'=> "http://babimart.com/min/datafeed/exportwss?password=hgkdshgkjdshgiue9889hwakjh4jnguy45t4389435tnieyvn83kssgjfdhgh874yt4",
				'total_page'=> ceil($productsCollection->getSize() / self::LIMIT),
				'page_param'=> "pagenum"
			);
			echo json_encode($result);
		die();
    }
	
	protected function _validate()
	{
		$passwordCon = Mage::getStoreConfig('min_newatt/report/password_wss');
        $pass = $this->getRequest()->getParam('password');
        if ($pass !== $passwordCon) {
			echo 'Password incorrect!';
			die();
		}
	}
	
	/**
     *
     */
    public function exportwssAction()
    {
        $this->_validate();
		$helper = Mage::helper('min_newatt/report');
		$productsCollection = $this->_getProducts();
		$page = (int)$this->getRequest()->getParam('pagenum');
		$page = $page > 0 ? $page : 1;
		$productsCollection->setPage($page,self::LIMIT);
		$stores = array(
		'default','hcm'
		);
		$data = array();
		foreach($stores as $storeCode){
			$store = $this->_getStoreByCode($storeCode);
			$storeId = $store ? $store->getId() : Mage::app()->getStore()->getId();
			$storeName = $store ? $store->getName() : Mage::app()->getStore()->getName();
			$tmp = $this->_buildInfoProducts($productsCollection, $storeId, $storeName, $storeCode);
			$data = array_merge($data, $tmp);
		}
		header("HTTP/1.1 200 OK");
		$helper->setData($data);
		$helper->display();
		die();
    }
	
	protected function _buildInfoProducts($products, $storeId, $storeName, $storeCode)
	{
		$data = array();
		$helper = Mage::helper('min_newatt/report');
		$modelMedia = Mage::getModel('catalog/product_media_config');
		$categoryModel = Mage::getModel('catalog/category');
		foreach ($products as $_product) {
			$_product->setStoreId($storeId)->load();
			$id = $_product->getId();
			$url = $_product->getProductUrl();
			$url = explode('?', $url);
			$url = $url[0].'?___store='.$storeCode;
			$bigimage = $modelMedia->getMediaUrl($_product->getImage());
			$smallimage = $modelMedia->getMediaUrl($_product->getSmallImage());
			$price = number_format($_product->getPrice(), 0, '', '');
			$finalPrice = number_format($_product->getFinalPrice(), 0, '', '');
			$discount = $finalPrice < $price ? 100 - round(($finalPrice / $price) * 100) : 0;
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
			$data[] = array(
				'store_address' => $storeName,
				//SKU sản phẩm
				'simple_sku' => $_product->getSku(),
				//SKU sản phẩm cha nếu có
				'parent_sku' => '',
				//Có sẵn hàng hay không
				'availability_instock' => $_product->stock_item->is_in_stock == 1 ? true : false,
				//Brand name
				'brand' => '',
				//Tên sản phẩm
				'product_name' => $_product->getName(),
				//Mô tả sản phẩm
				'description' => $description,
				//Mệnh giá tiền sử dụng VND/USD
				'currency' => 'VND',
				//Giá sản phẩm khi chưa khuyến mãi (format US currency -> xx,xxx,xxx.xx)
				'price' => $helper->currency($_product->getPrice()),
				//Số tiền khuyến mãi (format US currency -> xx,xxx,xxx.xx)
				'discount' => $discount,
				//Giá sau khi khuyến mãi (nếu không có khuyến mãi thì để bằng giá ban đầu, hoặc không điền (format US currency -> xx,xxx,xxx.xx)
				'discounted_price' => $helper->currency($finalPrice),
				//Category1 cha của cha
				'parent_of_parent_of_cat1' => $cateIdTree['level_4'][0],
				//Category1 cha
				'parent_of_cat_1' => $cateIdTree['level_3'][0],
				//Category1 sản phẩm
				'category_1' => $cateIdTree['level_2'][0],
				//Category2 cha của cha (nếu có)
				'parent_of_parent_of_cat2' => '',
				//Category2 cha (nếu có)
				'parent_of_cat_2' => '',
				//Category2 sản phẩm (nếu có)
				'category_2' => '',
				//Category3 cha của cha (nếu có)
				'parent_of_parent_of_cat3' => '',
				//Category3 cha (nếu có)
				'parent_of_cat3' => '',
				//Category3 sản phẩm (nếu có)
				'category_3' => '',
				//Ảnh sản phẩm (Ảnh đại diện)
				'picture_url' => $bigimage,
				//Ảnh sản phẩm 2
				'picture_url2' => $smallimage,
				//Ảnh sản phẩm 3
				'picture_url3' => '',
				//Ảnh sản phẩm 4
				'picture_url4' => '',
				//Ảnh sản phẩm 5
				'picture_url5' => '',
				//Đường dẫn đến bài viết sản phẩm
				'URL' => $url,
				//Thông tin khuyến mãi
				'promotion' => '',
				//Thời gian giao hàng
				'delivery_period' => ''
			);
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
		foreach($stores as $id){
			$store = Mage::app()->getStore($id);
			if($store->getCode()==$storeCode) {
				return $store;
			}
		}
		return false;
	}
}