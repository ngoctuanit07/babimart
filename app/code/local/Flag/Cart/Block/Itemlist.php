<?php
class Flag_Cart_Block_Itemlist extends Mage_Checkout_Block_Cart
{
	public function getItemCart()
    {
        $html = '';
        foreach ($this->getItems() as $_item) {
            $html.= $this->getItemHtml($_item);
        }
        return $html;
    }
}