<?php
/**
 * Created by JetBrains PhpStorm.
 * User: DUCTHANG
 * Date: 1/10/15
 * Time: 1:47 AM
 * To change this template use File | Settings | File Templates.
 */
class Flag_Page_Block_Switch extends Mage_Core_Block_Template
{
    public function getActiveStores()
    {
        $result = array();
        $stores = Mage::app()->getStores();
        foreach ($stores as $store)
        {
            if ($store->getIsActive()) {
                $result[$store->getId()] = $store;
            }
        }
        return $result;
    }

    public function getCurrentStoreId()
    {
        return Mage::app()->getStore()->getId();
    }
}