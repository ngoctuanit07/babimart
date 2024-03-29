<?php

/**
 * News image helper
 * 
 * @author Van NH <hoangvan87@gmail.com>
 */
class Cakiem8x_News_Helper_Image extends Mage_Core_Helper_Abstract {
    /**
     * Media path to extension images
     * 
     * @var string 
     */

    const MEDIA_PATH = 'news';

    /**
     * Maximum size for image in bytes
     * Default value is 1M
     * 
     * @var int 
     */
    const MAX_FILE_SIZE = 1048576;

    /**
     * Manimum image height in pixels
     * 
     * @var int 
     */
    const MIN_HEIGHT = 50;

    /**
     * Maximum image height in pixels
     * 
     * @var int 
     */
    const MAX_HEIGHT = 800;

    /**
     * Manimum image width in pixels
     * 
     * @var int 
     */
    const MIN_WIDTH = 50;

    /**
     * Maximum image width in pixels
     * 
     * @var int 
     */
    const MAX_WIDTH = 800;

    /**
     * Array of image size limitation
     *
     * @var array
     */
    protected $_imageSize = array(
        'minwidth' => self::MIN_WIDTH
        , 'minheight' => self::MIN_HEIGHT
        , 'maxwidth' => self::MAX_WIDTH
        , 'maxheight' => self::MAX_HEIGHT
    );

    /**
     * Array of allowed file extentions
     * 
     * @var array 
     */
    protected $_allowedExtensions = array('jpg', 'png', 'gif');

    /**
     * Return base media directory for news Item images
     * 
     * @return string
     */
    public function getBaseDir() {
        return Mage::getBaseDir('media') . DS . self::MEDIA_PATH;
    }

    /**
     * Return base Url for news Item images
     * 
     * @return string
     */
    public function getBaseUrl() {
        return Mage::getBaseUrl() . DS . self::MEDIA_PATH;
    }

    /**
     * Remove news item image by image filebname
     * 
     * @param string $imageFile
     * @return boolean
     */
    public function removeImage($imageFile) {
        $io = new Varien_Io_File();
        $io->open(array('path' => $this->getBaseDir()));
        if ($io->fileExists($imageFile)) {
            return $io->rm($imageFile);
        }

        return false;
    }

    /**
     * Upload image and return uploaded image file name or false
     * 
     * @throws Mage_Core_Exception
     * @param string $scope the request key for file
     * @return boolean|string
     */
    public function uploadImage($scope) {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->addValidator('ImageSize', true, $this->_imageSize);
        $adapter->addValidator('Size', true, self::MAX_FILE_SIZE);

        if ($adapter->isUploaded($scope)) {
            // Validate image
            if (!$adapter->isValid($scope)) {
                Mage::throwException(Mage::helper('cakiem8x_news')->__('Uploaded image is not valid'));
            }

            $upload = new Varien_File_Uploader($scope);
            $upload->setAllowCreateFolders(true);
            $upload->setAllowedExtensions($this->_allowedExtensions);
            $upload->setAllowRenameFiles(true);
            $upload->setFilesDispersion(false);
            if ($upload->save($this->getBaseDir())) {
                return $upload->getUploadedFileName();
            }
        }
        return false;
    }

    /**
     * Return URL for resized News Item Image
     * 
     * @param Cakiem8x_News_Model_News $item
     * @param interger $width
     * @param interger $height
     * @return boolean|string
     */
    public function resize(Cakiem8x_News_Model_News $item, $width, $height = null) {
        if (!$item->getImage()) {
            return false;
        }

        if ($width < self::MIN_WIDTH || $width > self::MAX_WIDTH) {
            return false;
        }

        $width = (int) $width;
        if (!is_null($height)) {
            if ($height < self::MIN_HEIGHT || $height > self::MAX_HEIGHT) {
                return false;
            }
            $height = (int) $height;
        }

        $imageFile = $item->getImage();
        $cacheDir = $this->getBaseDir() . DS . 'cache' . DS . $width;
        $cacheUrl = $this->getBaseUrl() . '/' . 'cache' . '/' . $width . '/';

        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($cacheDir);
        $io->open(array('path' => $cacheDir));
        if ($io->fileExists($imageFile)) {
            return $cacheUrl . $imageFile;
        }

        try {
            $image = new Varien_Image($this->getBaseDir() . DS . $imageFile);
            $image->resize($width, $height);
            $image->save($cacheDir . DS . $imageFile);
            return $cacheUrl . $imageFile;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * Removes folder with cached images
     * 
     * @return boolean
     */
    public function flushImagesCache() {
        $cacheDir = $this->getBaseDir() . DS . 'cache' . DS;
        $io = new Varien_Io_File();

        if ($io->fileExists($cacheDir, false)) {
            return $io->rmdir($cacheDir);
        }

        return false;
    }

}