<?php

namespace ShopGo\Paytabs\Block\System\Config\Button;

use Magento\Framework\App\Config\ScopeConfigInterface;
use ShopGo\Paytabs\Helper\Data;

class CheckAccountButton extends \Magento\Config\Block\System\Config\Form\Field
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
        $this->setTemplate('system/config/check_account_button.phtml');
    }


    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'paytabs_account_checker',
                'label' => __('Run'),
                'onclick' => 'javascript:checkPaytabsAccount(); return false;',
            ]
        );

        return $button->toHtml();
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxCheckUrl()
    {
        $response=$this->_helper->validateSecretKey();
        if($response['response_code']=="4000"){
            return  "Credentials Verified";
        }
        else
            return  $response['result'];
    }
}