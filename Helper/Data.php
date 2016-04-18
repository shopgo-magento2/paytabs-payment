<?php

namespace ShopGo\Paytabs\Helper;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DEBUG       = 'payment/paytabs/debug';
    const XML_PATH_MEREMAIL    = 'payment/paytabs/username';
    const XML_PATH_SECRET      = 'payment/paytabs/secretkey';
    const XML_PATH_STATUS      = 'payment/paytabs/order_status';
    const CREATE_PAY_PAGE      = '/apiv2/create_pay_page';
    const VALIADTE_ACCOUNT     = '/apiv2/validate_secret_key';
    const VERFY_PAYMENT        = '/apiv2/verify_payment';
    const PAYTABS_SITE         = 'https://www.paytabs.com';

    protected $countries = array(
          "AF" => '+93',
          "AL" => '+355',
          "DZ" => '+213',
          "AS" => '+376',
          "AD" => '+376',
          "AO" => '+244',
          "AG" => '+1-268',
          "AR" => '+54',
          "AM" => '+374',
          "AU" => '+61',
          "AT" => '+43',
          "AZ" => '+994',
          "BS" => '+1-242',
          "BH" => '+973',
          "BD" => '+880',
          "BB" => '1-246',
          "BY" => '+375',
          "BE" => '+32',
          "BZ" => '+501',
          "BJ" =>'+229',
          "BT" => '+975',
          "BO" => '+591',
          "BA" => '+387',
          "BW" => '+267',
          "BR" => '+55',
          "BN" => '+673',
          "BG" => '+359',
          "BF" => '+226',
          "BI" => '+257',
          "KH" => '+855',
          "CA" => '+1',
          "CV" => '+238',
          "CF" => '+236',
          "CM" => '+237',
          "TD" => '+235',
          "CL" => '+56',
          "CN" => '+86',
          "CO" => '+57',
          "KM" => '+269',
          "CG" => '+242',
          "CR" => '+506',
          "CI" => '+225',
          "HR" => '+385',
          "CU" => '+53',
          "CY" => '+357',
          "CZ" => '+420',
          "DK" => '+45',
          "DJ" => '+253',
          "DM" => '+1-767',
          "DO" => '+1-809'
          "EC" => '+593',
          "EG" => '+20',
          "SV" => '+503',
          "GQ" => '+240',
          "ER" => '+291',
          "EE" => '+372',
          "ET" => '+251',
          "FJ" => '+679',
          "FI" => '+358',
          "FR" => '+33',
          "GA" => '+241',
          "GM" => '+220',
          "GE" => '+995',
          "DE" => '+49',
          "GH" => '+233',
          "GR" => '+30',
          "GD" => '+1-473',
          "GT" => '+502',
          "GN" => '+224',
          "GW" => '+245',
          "GY" => '+592',
          "HT" => '+509',
          "HN" => '+504',
          "HK" => '+852',
          "HU" => '+36',
          "IS" => '+354',
          "IN" => '+91',
          "ID" => '+62',
          "IR" => '+98',
          "IQ" => '+964',
          "IE" => '+353',
          "IL" => '+972',
          "IT" => '+39',
          "JM" => '+1-876',
          "JP" => '+81',
          "JO" => '+962',
          "KZ" => '+7',
          "KE" => '+254',
          "KI" => '+686',
          "KP" => '+850',
          "KR" => '+82',
          "KW" => '+965',
          "KG" => '+996',
          "LA" => '+856',
          "LV" => '+371',
          "LB" => '+961',
          "LS" => '+266',
          "LR" => '+231',
          "LY" => '+218',
          "LI" => '+423',
          "LU" => '+352',
          "MO" => '+389',
          "MG" => '+261',
          "MW" => '+265',
          "MY" => '+60',
          "MX" => '+52',
          "MC" => '+377',
          "MA" => '+212',
          "NP" => '+977',
          "NL" => '+31',
          "NZ" => '+64',
          "NI" => '+505',
          "NE" => '+227',
          "NG" => '+234',
          "NO" => '+47',
          "OM" => '+968',
          "PK" => '+92',
          "PA" => '+507',
          "PG" => '+675',
          "PY" =>'+595',
          "PE" =>'+51',
          "PH" =>'+63',
          "PL" => '48',
          "PT" => '+351',
          "QA" => '+974',
          "RU" => '+7',
          "RW" => '+250',
          "SA" => '+966',
          "SN" => '+221',
          "SG" => '+65',
          "SK" => '+421',
          "SI" => '+386',
          "ZA" => '+27',
          "ES" => '+34',
          "LK" => '+94',
          "SD" => '+249',
          "SZ" => '+268',
          "SE" => '+46',
          "CH" => '+41',
          "SY" => '+963',
          "TZ" => '+255',
          "TH" => '+66',
          "TG" => '+228',
          "TO" => '+676',
          "TN" => '+216',
          "TR" => '+90',
          "TM" => '+993',
          "UA" => '+380',
          "AE" => '+971',
          "GB" => '+44',
          "US" => '+1'
        );

     /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Reader $configReader
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Reader $configReader
    ) {
        parent::__construct($context, $scopeConfig);
    }

    /**
     * Return debugging mode configuration value
     *
     * @return bool
     */
    public function getDebugStatus()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return configuration field value
     *
     * @param string $xmlPath
     * @return array|bool
     */
    public function getConfigValue($xmlPath)
    {
      return $this->scopeConfig->getValue($xmlPath,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Validate Paytabs customer account
     *
     * @return array
     */
    public function validateSecretKey() 
    {
      $authentication_URL = self::PAYTABS_SITE.self::VALIADTE_ACCOUNT;

      $fields = array(
        'merchant_email' => $this->getConfigValue(self::XML_PATH_MEREMAIL),
        'secret_key'     => $this->getConfigValue(self::XML_PATH_SECRET)
      );

      $fields_string = http_build_query($fields);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$authentication_URL);
      curl_setopt($ch, CURLOPT_POST, count($fields));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
      $ch_result = curl_exec($ch);
      $ch_error  = curl_error($ch);

      $result = json_decode($ch_result,true);
      return $result;
    }

    /**
     * Get controller 'request/response' url
     *
     * @return string
     */
    public function getUrl($route, $params = [])
    {
      return $this->_getUrl($route, $params);
    }

    /**
     * Get country code phone
     *
     * @param string $code
     * @return string
     */
    public function _getccPhone($code)
    {  
      return $this->countries[$code];
    }
}