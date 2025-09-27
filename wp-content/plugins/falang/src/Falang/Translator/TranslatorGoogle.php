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

class TranslatorGoogle extends TranslatorDefault {

    function __construct()
    {
        parent::__construct();

        $this->token= Falang()->get_model()->get_option('google_key');

        //supported language https://cloud.google.com/translate/docs/languages
        //add extra language for google
        $this->addServiceLanguage('el','el');//greek

        $this->script = 'translatorGoogle.js';
    }

    public function installScripts ($from,$to)
    {
        parent::installScripts($from,$to);

        $inline_script = "var translator = {'from' : '".strtolower($from). "','to' : '".strtolower($to). "'};\n";
        $inline_script .= "var googleKey = '".$this->token."';\n";
        wp_add_inline_script('translatorService',$inline_script,'before');
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

        $targetLanguageCode = $this->languageCodeToISO($targetLocale);
        $sourceLanguageCode = $this->languageCodeToISO(Falang()->get_model()->get_default_language()->locale);

        //$this->token don't work here
        $token= Falang()->get_model()->get_option('google_key');

        $postfields = array();
        $postfields['key'] = $token;
        $postfields['source'] = $sourceLanguageCode;
        $postfields['target'] = $targetLanguageCode;
        $postfields['format'] = 'html';
        $postfields['q'] = $text;

        $url = "https://translation.googleapis.com/language/translate/v2";
        $url .= '?key='.$token;

        $header = array();
        $header[] = 'Content-Type: application/json; charset=utf-8';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($postfields));
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_ORIGIN"]);
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

        $resultDecoded = json_decode($result);

        $response          = new \stdClass();

        if (isset($resultDecoded->error)){
            $response->success= false;
            $response->data = $resultDecoded->error->message;
            return $response;
        }

        //decode the translation returned
        try {
            $response->success = true;
            $response->data = $resultDecoded->data->translations[0]->translatedText;
        } catch (\Exception $e) {
            $response->success= false;
            $response->data  = $e->getMessage();
        }
        return $response;

    }
}