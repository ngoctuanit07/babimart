<?php

class EM_Em0095settings_Model_Mysql4_Megamenupro extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the megamenupro_id refers to the key field in your database table.
        $this->_init('em0095settings/megamenupro', 'megamenupro_id');
    }
}