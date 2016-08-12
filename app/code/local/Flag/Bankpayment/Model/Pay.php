<?php
class Flag_Bankpayment_Model_Pay extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'pay';
    protected $_formBlockType = 'flag_bankpayment/form_pay';
    protected $_infoBlockType = 'flag_bankpayment/info_pay';

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setBankInfo($data->getBankInfo());
        return $this;
    }


    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();

        $no = $info->getBankInfo();
        if(empty($no)){
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Check No and Date are required fields');
        }

        if($errorMsg){
            Mage::throwException($errorMsg);
        }
        return $this;
    }
}