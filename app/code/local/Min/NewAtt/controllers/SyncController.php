<?php
class Min_NewAtt_SyncController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
            $_products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('manufacturer', array('neq' => NULL));
            foreach($_products as $product) {
                $product->save();
            }
             echo "Done!";
    }
    public function updatecateAction()
    {
        $category = Mage::getResourceModel('catalog/category_collection')
            ->addFieldToFilter('name', 'Thương hiệu')
            ->getFirstItem() ;
        $child = $category->getChildren();
        $childs =  explode(',',$child);
        try{
            foreach($childs as $id){
                $cate = Mage::getModel('catalog/category')->load($id);
                $cate->_defaultValues['include_in_menu'] = '0';
                $cate->save();
            }
            echo 'Success';
        }catch (Exception $e){
            echo $e;
        }
    }
}