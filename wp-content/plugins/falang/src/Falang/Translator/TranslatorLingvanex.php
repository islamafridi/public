<?php
/**
 * The translator external functionality of the plugin.
 *
 * @link       www.faboba.com
 * @since      1.3.5
 *
 * @package    Falang
 */
namespace Falang\Translator;

class TranslatorLingvanex extends TranslatorDefault {

    function __construct()
    {
        parent::__construct();

        $this->token= Falang()->get_model()->get_option('lingvanex_key');

        $this->setServiceLanguage();

        $this->script = 'translatorLingvanex.js';
    }

    public function installScripts ($from,$to)
    {
        parent::installScripts($from,$to);

        $inline_script = "var translator = {'from' : '".strtolower($from). "','to' : '".strtolower($to). "'};\n";
        $inline_script .= "var LingvanexKey = '".$this->token."';\n";
        wp_add_inline_script('translatorService',$inline_script,'before');
    }

    //return the language code in specific format aa_AA
    //key is the WordPress language code
    //example en_GB, es_ES, ru_RU
    public function languageCodeToISO ($language){
        $l = strtolower($language);
        if (isset(TranslatorDefault::$languageCodeInISO[$l])){
            return TranslatorDefault::$languageCodeInISO[$l];
        } else {
            return '';
        }
    }

    /*
     * @from 1.3.49
     *
     * Translate content to target language
     * support html translation
     * use for elmentor and other builder
     *
     * param sting @text : text to translate (html format)
     * param string @targetLocal language locale ex de_DE*
     *
     */
    public function translate($text,$targetLocale){
        $url = "https://api-b2b.backenster.com/b1/api/v3/translate";

        $targetLanguageCode = $this->languageCodeToISO($targetLocale);
        $sourceLanguageCode = $this->languageCodeToISO(Falang()->get_model()->get_default_language()->locale);

        //$this->token don't work here
        $token= Falang()->get_model()->get_option('lingvanex_key');

        $postfields = array();
        $postfields['from'] = $sourceLanguageCode;
        $postfields['to'] = $targetLanguageCode;
        $postfields['translateMode'] = 'html';
        $postfields['platform'] = 'api';
        $postfields['data'] = $text;

        $header = array();
        $header[] = 'Authorization: '.$token;
        $header[] = 'accept: application/application/json';
        $header[] = 'Content-Type: application/json';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($postfields));
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //Set curl options relating to SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response          = new \stdClass();

        if( ! $result = curl_exec($ch))
        {
            $error = curl_error($ch);
            $response->success = false;
            $response->data = 'error unknown';
            return $response;
        }
        curl_close($ch);

        $resultDecoded = json_decode($result);

        if (isset($resultDecoded->err)){
            $response->success= false;
            $response->data = $resultDecoded->err;
            return $response;
        }

        //decode the translation returned
        try {
            $response->success = true;
            $response->data = $resultDecoded->result;
        } catch (\Exception $e) {
            $response->success= false;
            $response->data  = $e->getMessage();

        }
        return $response;

    }

    /*
     * @update 1.3.37 add Arabic language
     * */
    private function setServiceLanguage(){
        //wordpress locale lowercase => lingvanex code
        $this->addServiceLanguage('af','af_ZA');//
        $this->addServiceLanguage('ar','ar_SA');//Arabic
        $this->addServiceLanguage('sq','sa_AL');//
        $this->addServiceLanguage('bel','be_BY');//
        $this->addServiceLanguage('bg_bg','bg_BG');//
        $this->addServiceLanguage('zh_cn','zh_Hans_CN');//
        $this->addServiceLanguage('cs_cz','cs_CZ');//
        $this->addServiceLanguage('da_dk','da_DK');//
        $this->addServiceLanguage('nl_nl','nl_NL');//
        $this->addServiceLanguage('fi','fi_FI');//
        $this->addServiceLanguage('de_de','de_DE');//
        $this->addServiceLanguage('de_ch','de_DE');//
        $this->addServiceLanguage('el','el_GR');//
        $this->addServiceLanguage('id_id','id_ID');//
        $this->addServiceLanguage('it_it','it_IT');//
        $this->addServiceLanguage('ja','ja_JP');//
        $this->addServiceLanguage('km','km_KH');//
        $this->addServiceLanguage('ko_kr','ko_KR');//
        $this->addServiceLanguage('lv','lv_LV');//
        $this->addServiceLanguage('lt_lt','lt_LT');//
        $this->addServiceLanguage('pt','pt_PT');//
        $this->addServiceLanguage('pt_br','pt_PT');//
        $this->addServiceLanguage('ro_ro','ro_RO');//
        $this->addServiceLanguage('sl_sl','sl_SL');//
        $this->addServiceLanguage('es_ar','es_ES');//
        $this->addServiceLanguage('es_mx','es_ES');//
        $this->addServiceLanguage('es_es','es_ES');//
        $this->addServiceLanguage('sv_se','sv_SE');//
        $this->addServiceLanguage('th','th_TH');//
        $this->addServiceLanguage('tr_tr','tr_TR');//
        $this->addServiceLanguage('uk','uk_UA');//ukrainian
        $this->addServiceLanguage('vi','vi_VN');//
        $this->addServiceLanguage('en_au','en_US');//English (Australia)
        $this->addServiceLanguage('en_ca','en_US');//English (Canada)
        $this->addServiceLanguage('en_gb','en_US');//English (UK)
        $this->addServiceLanguage('en_us','en_US');//English
        $this->addServiceLanguage('en','en_US');//English
        $this->addServiceLanguage('fr_be','fr_FR');//French
        $this->addServiceLanguage('fr_fr','fr_FR');//French
        $this->addServiceLanguage('fr_ca','fr_CA');//French
        $this->addServiceLanguage('ru_ua','uk_ua');//ukrainian
    }



}