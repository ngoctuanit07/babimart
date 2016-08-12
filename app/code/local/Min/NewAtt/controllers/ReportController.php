<?php

class Min_NewAtt_ReportController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     */
    public function indexAction()
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=products-data.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array(
            'ProductID',
            'name',
            'url',
            'bigimage',
            'smallimage',
            'price',
            'categoryid1',
            'categoryid2',
            'categoryid3',
            'categoryid4',
            'description',
            'retailprice',
            'discount',
        ));
        $productsCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $categoryModel = Mage::getModel('catalog/category');
        $modelMedia = Mage::getModel('catalog/product_media_config');
        foreach ($productsCollection as $_product) {
            $_product->load();
            if ($_product->getPrice() > 0) {
                $id = $_product->getId();
                $name = $_product->getName();
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
                $row = array(
                    'ProductID' => $id,
                    'name' => $name,
                    'url' => $url,
                    'bigimage' => $bigimage,
                    'smallimage' => $smallimage,
                    'price' => $price,
                    'categoryid1' => $cateIdTree['level_2'][0],
                    'categoryid2' => $cateIdTree['level_3'][0],
                    'categoryid3' => $cateIdTree['level_4'][0],
                    'categoryid4' => $cateIdTree['level_5'][0],
                    'description' => htmlspecialchars($description),
                    'retailprice' => $finalPrice,
                    'discount' => $discount
                );
                fputcsv($output, $row);
            }
        }
    }

    /**
     *
     */
    public function checkPass()
    {
        $passwordCon = Mage::getStoreConfig('min_newatt/report/password_wss');
        $pass = $this->getRequest()->getParam('password');
        if ($pass !== $passwordCon) {
            echo 'Password incorrect';
            die;
        }
    }

    /**
     *
     */
    public function exportAction()
    {
        $this->checkPass();
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '180');
        try {
            $filePath = Mage::getBaseDir() . '/var/export/' . date('Y_m_d_H_i_s') . '.csv';
            $output = fopen($filePath, 'w');
            fputcsv($output, array(
                'ID',
                'ID2',
                'Item title',
                'Final URL',
                'Image URL',
                'Item subtitle',
                'Item description',
                'Item category',
                'Price',
                'Sale price',
                'Contextual keywords',
                'Item address',
                'Tracking template',
                'Custom parameter'
            ));
            $productsCollection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $categoryModel = Mage::getModel('catalog/category');
            $modelMedia = Mage::getModel('catalog/product_media_config');
            foreach ($productsCollection as $_product) {
                $_product->load();
                if ($_product->getPrice() <= 0) {
                    continue;
                }
                $id = $_product->getId();
                $name = $_product->getName();
                $url = $_product->getProductUrl();
                $bigimage = $modelMedia->getMediaUrl($_product->getImage());
                $price = number_format($_product->getPrice(), 0, '', ',');
                $finalPrice = number_format($_product->getFinalPrice(), 0, '', ',');
                $finalPrice = $finalPrice == $price ? '' : $finalPrice;
                $categoryIds = $_product->getCategoryids();
                $categoryId = '';
                $categoryName = '';
                $categoryIds = array_reverse($categoryIds);
                foreach ($categoryIds as $key => $categoryId) {
                    $categoryObj = $categoryModel->load($categoryId);
                    if ($this->checkCategory($categoryObj)) {
                        $categoryName = $categoryObj->getName();
                        break;
                    }
                }
                $description = $_product->getMetaDescription();
                $row = array(
                    'ID' => $id,
                    'ID2' => $categoryId,
                    'Item title' => $name,
                    'Final URL' => $url,
                    'Image URL' => $bigimage,
                    'Item subtitle' => $name,
                    'Item description' => $description,
                    'Item category' => $categoryName,
                    'Price' => $price ? $price . ' VND' : $price,
                    'Sale price' => $finalPrice ? $finalPrice . ' VND' : $finalPrice,
                    'Contextual keywords' => '',
                    'Item address' => 'Hanoi,Hanoi,Vietnam',
                    'Tracking template' => ''
                );
                fputcsv($output, $row);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die;
        }
        fclose($output);
        $this->_prepareDownloadResponse('data-export-' . date('Y_m_d') . '.csv', array(
            'value' => $filePath,
            'type' => 'filename'
        ));

    }

    public function checkCategory($categoryObj)
    {
        $path = explode('/', $categoryObj->getPath());
        if ($categoryObj->getName() !== 'Root Catalog' &&
            $categoryObj->getName() !== 'Thương hiệu' &&
            !in_array('3477', $path) && $categoryObj->getIsActive()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $fileName
     * @param $content
     * @param string $contentType
     * @param null $contentLength
     * @return $this
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null)
    {
        $isFile = false;
        $file = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                $isFile = true;
                $file = $content['value'];
                $contentLength = filesize($file);
            }
        }
        echo "\xEF\xBB\xBF";
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setHeader('Last-Modified', date('r'));

        if (!is_null($content)) {
            if ($isFile) {
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $ioAdapter = new Varien_Io_File();
                if (!$ioAdapter->fileExists($file)) {
                    Mage::throwException(Mage::helper('core')->__('File not found'));
                }
                $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
                $ioAdapter->streamOpen($file, 'r');
                while ($buffer = $ioAdapter->streamRead()) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
                if (!empty($content['rm'])) {
                    $ioAdapter->rm($file);
                }

                exit(0);
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        exit(0);
    }

    public function exportfbAction()
    {
        $this->checkPass();
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '180');
        try {
            header('Content-Encoding: UTF-8');
            header('Content-type: text/csv; charset=UTF-8');
            $filePath = Mage::getBaseDir() . '/var/report/' . date('Y_m_d_H_i_s') . '.csv';
            $output = fopen($filePath, 'w');

            fputcsv($output, array(
                'id',
                'title',
                'description',
                'google_product_category',
                'product_type',
                'link',
                'image_link',
                'condition',
                'availability',
                'sale_price',
                'gtin',
                'brand',
                'mpn',
                'item_group_id',
                'gender' => '',
            ));
            $code = $this->getRequest()->getParam('code', 'default');
            $storeName = $code == 'default' ? 'hn': 'hcm';
            $helper = Mage::helper('min_newatt/data');
            $products = $helper->buildInfoProductsFb($code);
            foreach ($products as $product) {
                fputcsv($output, $product);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
            die;
        }
        fclose($output);
        $this->_prepareDownloadResponse('fb-data-export-' . $storeName . '-' . date('Y_m_d') . '.csv', array(
            'value' => $filePath,
            'type' => 'filename'
        ));
        exit(0);

    }
}