<?php
/**
 * The translator external functionality of the plugin.
 *
 * @link       www.faboba.com
 * @since      1.3
 *
 * @package    Falang
 */

namespace Falang\Translator;


class TranslatorYandex extends TranslatorDefault {


    function __construct()
    {
        parent::__construct();

        $this->token= Falang()->get_model()->get_option('yandex_key');

        //add script to page
        $this->script = 'translatorYandex.js';

    }

    public function installScripts ($from,$to) {

        parent::installScripts($from,$to);

        $inline_script = "var translator = {'from' : '".strtolower($from). "','to' : '".strtolower($to). "'};\n";
        $inline_script .= "var YandexKey = '".$this->token."';\n";
        wp_add_inline_script('translatorService',$inline_script,'before');
    }


    /*
     * @from 1.3.49
     *
     * NOT WORKING PB WITH KEY
     *
     * Translate content to target language
     * support html translation
     * use for elmentor and other builder
     *
     * Documentation
     * https://translate.yandex.com/developers/keys
     *
     * param sting @text : text to translate (html format)
     * param string @targetLocal language locale ex de_DE
     *
     */
    public function translate($text,$targetLocale){
        $url = "https://translate.api.cloud.yandex.net/translate/v2/translate";

        $targetLanguageCode = $this->languageCodeToISO($targetLocale);
        $sourceLanguageCode = $this->languageCodeToISO(Falang()->get_model()->get_default_language()->locale);

        $token= Falang()->get_model()->get_option('yandex_key');

        $postfields = array();
        $postfields['sourceLanguageCode'] = $sourceLanguageCode;
        $postfields['targetLanguageCode'] = $targetLanguageCode;
        $postfields['format'] = 'HTML';
        $postfields['texts'] = array($text);

        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Api-Key '.$token;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($postfields));
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //Set curl options relating to SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);



        if( ! $result = curl_exec($ch))
        {
            $error = curl_error($ch);
            $response          = new \stdClass();
            $response->success = false;
            $response->data = 'error unknown';
            return $response;
        }
        curl_close($ch);

        $response          = new \stdClass();
        $response->success = true;
        $response->data = $result;

        return $response;
    }

}