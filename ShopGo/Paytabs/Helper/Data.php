<?php

namespace ShopGo\Paytabs\Helper;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DEBUG       = 'payment/paytabs/debug';
    const XML_PATH_MEREMAIL    = 'payment/paytabs/username';
    const XML_PATH_SECRET      = 'payment/paytabs/secretkey';
    const PAYTABS              = 'https://www.paytabs.com';

    protected  $Pthost         =  self::PAYTABS;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Reader $configReader

    )
    {
        parent::__construct($context, $scopeConfig);

    }

    public function getDebugStatus()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    public function getConfigValue($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function checkAccount()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MEREMAIL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }

    public function validateSecretKey() 
    {
        $authentication_URL = $this->Pthost."/apiv2/validate_secret_key";

        $fields = array(
            'merchant_email' => $this->scopeConfig->getValue(self::XML_PATH_MEREMAIL,\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'secret_key' => $this->scopeConfig->getValue(self::XML_PATH_SECRET,\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        );
        $fields_string = "";
        foreach($fields as $key=>$value) {
            $fields_string .= urlencode($key).'='.urlencode($value).'&';
        }
        $fields_string = substr($fields_string, 0, strrpos($fields_string, '&'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$authentication_URL);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $ch_result = curl_exec($ch);
        $ch_error = curl_error($ch);

        $dec = json_decode($ch_result,true);
        return $dec;
    }

    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }


    public function _getccPhone($code){
        $countries = array(
          "AF" => '+93',//array("AFGHANISTAN", "AF", "AFG", "004"),
          "AL" => '+355',//array("ALBANIA", "AL", "ALB", "008"),
          "DZ" => '+213',//array("ALGERIA", "DZ", "DZA", "012"),
          "AS" => '+376',//array("AMERICAN SAMOA", "AS", "ASM", "016"),
          "AD" => '+376',//array("ANDORRA", "AD", "AND", "020"),
          "AO" => '+244',//array("ANGOLA", "AO", "AGO", "024"),
          "AG" => '+1-268',//array("ANTIGUA AND BARBUDA", "AG", "ATG", "028"),
          "AR" => '+54',//array("ARGENTINA", "AR", "ARG", "032"),
          "AM" => '+374',//array("ARMENIA", "AM", "ARM", "051"),
          "AU" => '+61',//array("AUSTRALIA", "AU", "AUS", "036"),
          "AT" => '+43',//array("AUSTRIA", "AT", "AUT", "040"),
          "AZ" => '+994',//array("AZERBAIJAN", "AZ", "AZE", "031"),
          "BS" => '+1-242',//array("BAHAMAS", "BS", "BHS", "044"),
          "BH" => '+973',//array("BAHRAIN", "BH", "BHR", "048"),
          "BD" => '+880',//array("BANGLADESH", "BD", "BGD", "050"),
          "BB" => '1-246',//array("BARBADOS", "BB", "BRB", "052"),
          "BY" => '+375',//array("BELARUS", "BY", "BLR", "112"),
          "BE" => '+32',//array("BELGIUM", "BE", "BEL", "056"),
          "BZ" => '+501',//array("BELIZE", "BZ", "BLZ", "084"),
          "BJ" =>'+229',// array("BENIN", "BJ", "BEN", "204"),
          "BT" => '+975',//array("BHUTAN", "BT", "BTN", "064"),
          "BO" => '+591',//array("BOLIVIA", "BO", "BOL", "068"),
          "BA" => '+387',//array("BOSNIA AND HERZEGOVINA", "BA", "BIH", "070"),
          "BW" => '+267',//array("BOTSWANA", "BW", "BWA", "072"),
          "BR" => '+55',//array("BRAZIL", "BR", "BRA", "076"),
          "BN" => '+673',//array("BRUNEI DARUSSALAM", "BN", "BRN", "096"),
          "BG" => '+359',//array("BULGARIA", "BG", "BGR", "100"),
          "BF" => '+226',//array("BURKINA FASO", "BF", "BFA", "854"),
          "BI" => '+257',//array("BURUNDI", "BI", "BDI", "108"),
          "KH" => '+855',//array("CAMBODIA", "KH", "KHM", "116"),
          "CA" => '+1',//array("CANADA", "CA", "CAN", "124"),
          "CV" => '+238',//array("CAPE VERDE", "CV", "CPV", "132"),
          "CF" => '+236',//array("CENTRAL AFRICAN REPUBLIC", "CF", "CAF", "140"),
          "CM" => '+237',//array("CENTRAL AFRICAN REPUBLIC", "CF", "CAF", "140"),
          "TD" => '+235',//array("CHAD", "TD", "TCD", "148"),
          "CL" => '+56',//array("CHILE", "CL", "CHL", "152"),
          "CN" => '+86',//array("CHINA", "CN", "CHN", "156"),
          "CO" => '+57',//array("COLOMBIA", "CO", "COL", "170"),
          "KM" => '+269',//array("COMOROS", "KM", "COM", "174"),
          "CG" => '+242',//array("CONGO", "CG", "COG", "178"),
          "CR" => '+506',//array("COSTA RICA", "CR", "CRI", "188"),
          "CI" => '+225',//array("COTE D'IVOIRE", "CI", "CIV", "384"),
          "HR" => '+385',//array("CROATIA (local name: Hrvatska)", "HR", "HRV", "191"),
          "CU" => '+53',//array("CUBA", "CU", "CUB", "192"),
          "CY" => '+357',//array("CYPRUS", "CY", "CYP", "196"),
          "CZ" => '+420',//array("CZECH REPUBLIC", "CZ", "CZE", "203"),
          "DK" => '+45',//array("DENMARK", "DK", "DNK", "208"),
          "DJ" => '+253',//array("DJIBOUTI", "DJ", "DJI", "262"),
          "DM" => '+1-767',//array("DOMINICA", "DM", "DMA", "212"),
          "DO" => '+1-809',//array("DOMINICAN REPUBLIC", "DO", "DOM", "214"),
          "EC" => '+593',//array("ECUADOR", "EC", "ECU", "218"),
          "EG" => '+20',//array("EGYPT", "EG", "EGY", "818"),
          "SV" => '+503',//array("EL SALVADOR", "SV", "SLV", "222"),
          "GQ" => '+240',//array("EQUATORIAL GUINEA", "GQ", "GNQ", "226"),
          "ER" => '+291',//array("ERITREA", "ER", "ERI", "232"),
          "EE" => '+372',//array("ESTONIA", "EE", "EST", "233"),
          "ET" => '+251',//array("ETHIOPIA", "ET", "ETH", "210"),
          "FJ" => '+679',//array("FIJI", "FJ", "FJI", "242"),
          "FI" => '+358',//array("FINLAND", "FI", "FIN", "246"),
          "FR" => '+33',//array("FRANCE", "FR", "FRA", "250"),
          "GA" => '+241',//array("GABON", "GA", "GAB", "266"),
          "GM" => '+220',//array("GAMBIA", "GM", "GMB", "270"),
          "GE" => '+995',//array("GEORGIA", "GE", "GEO", "268"),
          "DE" => '+49',//array("GERMANY", "DE", "DEU", "276"),
          "GH" => '+233',//array("GHANA", "GH", "GHA", "288"),
          "GR" => '+30',//array("GREECE", "GR", "GRC", "300"),
          "GD" => '+1-473',//array("GRENADA", "GD", "GRD", "308"),
          "GT" => '+502',//array("GUATEMALA", "GT", "GTM", "320"),
          "GN" => '+224',//array("GUINEA", "GN", "GIN", "324"),
          "GW" => '+245',//array("GUINEA-BISSAU", "GW", "GNB", "624"),
          "GY" => '+592',//array("GUYANA", "GY", "GUY", "328"),
          "HT" => '+509',//array("HAITI", "HT", "HTI", "332"),
          "HN" => '+504',//array("HONDURAS", "HN", "HND", "340"),
          "HK" => '+852',//array("HONG KONG", "HK", "HKG", "344"),
          "HU" => '+36',//array("HUNGARY", "HU", "HUN", "348"),
          "IS" => '+354',//array("ICELAND", "IS", "ISL", "352"),
          "IN" => '+91',//array("INDIA", "IN", "IND", "356"),
          "ID" => '+62',//array("INDONESIA", "ID", "IDN", "360"),
          "IR" => '+98',//array("IRAN, ISLAMIC REPUBLIC OF", "IR", "IRN", "364"),
          "IQ" => '+964',//array("IRAQ", "IQ", "IRQ", "368"),
          "IE" => '+353',//array("IRELAND", "IE", "IRL", "372"),
          "IL" => '+972',//array("ISRAEL", "IL", "ISR", "376"),
          "IT" => '+39',//array("ITALY", "IT", "ITA", "380"),
          "JM" => '+1-876',//array("JAMAICA", "JM", "JAM", "388"),
          "JP" => '+81',//array("JAPAN", "JP", "JPN", "392"),
          "JO" => '+962',//array("JORDAN", "JO", "JOR", "400"),
          "KZ" => '+7',//array("KAZAKHSTAN", "KZ", "KAZ", "398"),
          "KE" => '+254',//array("KENYA", "KE", "KEN", "404"),
          "KI" => '+686',//array("KIRIBATI", "KI", "KIR", "296"),
          "KP" => '+850',//array("KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", "KP", "PRK", "408"),
          "KR" => '+82',//array("KOREA, REPUBLIC OF", "KR", "KOR", "410"),
          "KW" => '+965',//array("KUWAIT", "KW", "KWT", "414"),
          "KG" => '+996',//array("KYRGYZSTAN", "KG", "KGZ", "417"),
          "LA" => '+856',//array("LAO PEOPLE'S DEMOCRATIC REPUBLIC", "LA", "LAO", "418"),
          "LV" => '+371',//array("LATVIA", "LV", "LVA", "428"),
          "LB" => '+961',//array("LEBANON", "LB", "LBN", "422"),
          "LS" => '+266',//array("LESOTHO", "LS", "LSO", "426"),
          "LR" => '+231',//array("LIBERIA", "LR", "LBR", "430"),
          "LY" => '+218',//array("LIBYAN ARAB JAMAHIRIYA", "LY", "LBY", "434"),
          "LI" => '+423',//array("LIECHTENSTEIN", "LI", "LIE", "438"),
          "LU" => '+352',//array("LUXEMBOURG", "LU", "LUX", "442"),
          "MO" => '+389',//array("MACAU", "MO", "MAC", "446"),
          "MG" => '+261',//array("MADAGASCAR", "MG", "MDG", "450"),
          "MW" => '+265',//array("MALAWI", "MW", "MWI", "454"),
          "MY" => '+60',//array("MALAYSIA", "MY", "MYS", "458"),     
          "MX" => '+52',//array("MEXICO", "MX", "MEX", "484"),
          "MC" => '+377',//array("MONACO", "MC", "MCO", "492"),
          "MA" => '+212',//array("MOROCCO", "MA", "MAR", "504"),
          "NP" => '+977',//array("NEPAL", "NP", "NPL", "524"),
          "NL" => '+31',//array("NETHERLANDS", "NL", "NLD", "528"),
          "NZ" => '+64',//array("NEW ZEALAND", "NZ", "NZL", "554"),
          "NI" => '+505',//array("NICARAGUA", "NI", "NIC", "558"),
          "NE" => '+227',//array("NIGER", "NE", "NER", "562"),
          "NG" => '+234',//array("NIGERIA", "NG", "NGA", "566"),
          "NO" => '+47',//array("NORWAY", "NO", "NOR", "578"),
          "OM" => '+968',//array("OMAN", "OM", "OMN", "512"),
          "PK" => '+92',//array("PAKISTAN", "PK", "PAK", "586"),
          "PA" => '+507',//array("PANAMA", "PA", "PAN", "591"),
          "PG" => '+675',//array("PAPUA NEW GUINEA", "PG", "PNG", "598"),
          "PY" =>'+595',// array("PARAGUAY", "PY", "PRY", "600"),
          "PE" =>'+51',// array("PERU", "PE", "PER", "604"),
          "PH" =>'+63',// array("PHILIPPINES", "PH", "PHL", "608"),
          "PL" => '48',//array("POLAND", "PL", "POL", "616"),
          "PT" => '+351',//array("PORTUGAL", "PT", "PRT", "620"),
          "QA" => '+974',//array("QATAR", "QA", "QAT", "634"),
          "RU" => '+7',//array("RUSSIAN FEDERATION", "RU", "RUS", "643"),
          "RW" => '+250',//array("RWANDA", "RW", "RWA", "646"),
          "SA" => '+966',//array("SAUDI ARABIA", "SA", "SAU", "682"),
          "SN" => '+221',//array("SENEGAL", "SN", "SEN", "686"),
          "SG" => '+65',//array("SINGAPORE", "SG", "SGP", "702"),
          "SK" => '+421',//array("SLOVAKIA (Slovak Republic)", "SK", "SVK", "703"),
          "SI" => '+386',//array("SLOVENIA", "SI", "SVN", "705"),
          "ZA" => '+27',//array("SOUTH AFRICA", "ZA", "ZAF", "710"),
          "ES" => '+34',//array("SPAIN", "ES", "ESP", "724"),
          "LK" => '+94',//array("SRI LANKA", "LK", "LKA", "144"),
          "SD" => '+249',//array("SUDAN", "SD", "SDN", "736"),
          "SZ" => '+268',//array("SWAZILAND", "SZ", "SWZ", "748"),
          "SE" => '+46',//array("SWEDEN", "SE", "SWE", "752"),
          "CH" => '+41',//array("SWITZERLAND", "CH", "CHE", "756"),
          "SY" => '+963',//array("SYRIAN ARAB REPUBLIC", "SY", "SYR", "760"),
          "TZ" => '+255',//array("TANZANIA, UNITED REPUBLIC OF", "TZ", "TZA", "834"),
          "TH" => '+66',//array("THAILAND", "TH", "THA", "764"),
          "TG" => '+228',//array("TOGO", "TG", "TGO", "768"),
          "TO" => '+676',//array("TONGA", "TO", "TON", "776"),
          "TN" => '+216',//array("TUNISIA", "TN", "TUN", "788"),
          "TR" => '+90',//array("TURKEY", "TR", "TUR", "792"),
          "TM" => '+993',//array("TURKMENISTAN", "TM", "TKM", "795"),
          "UA" => '+380',//array("UKRAINE", "UA", "UKR", "804"),
          "AE" => '+971',//array("UNITED ARAB EMIRATES", "AE", "ARE", "784"),
          "GB" => '+44',//array("UNITED KINGDOM", "GB", "GBR", "826"),
          "US" => '+1'//array("UNITED STATES", "US", "USA", "840"),
          
        );
         return $countries[$code];
    }
}