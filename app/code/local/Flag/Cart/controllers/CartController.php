<?php
require_once  'app/code/community/EM/Ajaxcart/controllers/CartController.php';
class Flag_Cart_CartController extends EM_Ajaxcart_CartController
{
    public $html = array();
    public function addAction()
    {
        $this->html['error'] = true;
		$this->initTheme();
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->html['msg'] = $this->__('Cannot add the item to shopping cart.');
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);
			
            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
			
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $msg = $this->__("Bạn vừa thêm ").$product->getName(). $this->__(" vào giỏ hàng");
                    $this->html['msg_success'] = $msg;
                    $html = $this->getLayout()->createBlock('checkout/cart')->setTemplate('checkout/popup/cart.phtml')->toHtml();
                    $this->html['popup'] = $html;
//                    $this->_getSession()->addSuccess($msg);
                    $this->html['error'] = false;
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->html['msg'] = $this->__('Cannot add the item to shopping cart.');
//            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
        } catch (Exception $e) {
            $this->html['msg'] = $this->__('Cannot add the item to shopping cart.');
//            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
        }
        $this->_goBack();
    }

    public function couponPostAction(){
        $this->html['error'] = true;
        $this->html['cancel'] = false;
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->html['msg'] = $this->__('Cannot apply the coupon code.');
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->html['msg'] = $this->__('Cannot apply the coupon code.');
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->html['msg'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode));
                    $this->html['totals'] = $this->getLayout()->createBlock("checkout/cart_totals")->setTemplate("checkout/popup/totals.phtml")->setCouponCode(1)->toHtml();
                    $this->html['error'] = false;
                } else {
                    $this->html['msg'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode));
                }
            } else {
                $this->html['error'] = false;
                $this->html['totals'] = $this->getLayout()->createBlock("checkout/cart_totals")->setTemplate("checkout/popup/totals.phtml")->toHtml();
                $this->html['msg'] = $this->__('Coupon code was canceled.');
                $this->html['cancel'] = true;
            }

        } catch (Mage_Core_Exception $e) {
            $this->html['msg'] = $this->__('Cannot apply the coupon code.');
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->html['msg'] = $this->__('Cannot apply the coupon code.');
            Mage::logException($e);
        }
        $this->_goBack();
    }
    protected function _goBack(){
        $total = $this->getLayout()->createBlock('checkout/cart_sidebar')->getSummaryCount();
        $html_total = $this->__('( Đang có '). $total . $this->__(' sản phẩm )') ;
        $this->html['total_items'] = $html_total;
        if($total==0)
            $this->html['is_Empty'] = true;
            else
                $this->html['is_Empty'] = false;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($this->html));
    }
    public function deleteAction()
    {
        $this->html['error'] = true;
        $this->html['msg'] = $this->__('Cannot remove item');
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                Mage::getSingleton('checkout/cart')->removeItem($id)
                  ->save();
                $this->_getSession()->setCartWasUpdated(true);
                $layout = $this->getLayout();
                $coupon =  $this->_getCart()->getQuote()->getCouponCode() ?  true : false;
                $this->html['totals'] = $layout->createBlock("checkout/cart_totals")->setTemplate("checkout/popup/totals.phtml")->setCouponCode($coupon)->toHtml();
                $this->html['error'] = false;
                $this->html['popup'] = $layout->createBlock("flag_cart/itemlist")->getItemCart();
            } catch (Exception $e) {
                $this->html['msg'] = $this->__('Cannot remove item');
//               Mage::getSingleton('checkout/cart')->addError($this->__('Cannot remove item'));
            }
        }
        $this->_goBack();
    }
    public function updateAction(){
        $this->html['error'] = true;
        $this->html['msg'] = $this->__('Cannot update shopping cart.');
        try {
            $cartData = $this->getRequest()->getParam('cart');
            $id = array_keys($cartData);
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
                foreach($cart->getItems()->getData() as $item){
                    if($item['item_id']==$id[0]){
                        $this->html['price'] = Mage::helper('checkout')->formatPrice($item['row_total']);
                        break;
                    }
                }
                $this->html['msg'] = $this->__('Update cart success.');
                $coupon =  $cart->getQuote()->getCouponCode() ?  true : false;
                $this->html['totals'] = $this->getLayout()->createBlock("checkout/cart_totals")->setTemplate("checkout/popup/totals.phtml")->setCouponCode($coupon)->toHtml();
                $this->html['error'] = false;
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->html['msg'] = $this->__('Cannot update shopping cart.');
//            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->html['msg'] = $this->__('Cannot update shopping cart.');
//            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
        $this->_goBack();
    }
}
