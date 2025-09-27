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

class TranslatorDeepl extends TranslatorDefault {

    function __construct()
    {
        parent::__construct();

        $this->token= Falang()->get_model()->get_option('deepl_key');

        $this->script = 'translatorDeepl.js';
    }

    public function installScripts ($from,$to)
    {
        parent::installScripts($from,$to);

        $inline_script = "var translator = {'from' : '".strtolower($from). "','to' : '".strtolower($to). "'};\n";
        $inline_script .= "var deeplKey = '".$this->token."';\n";
        wp_add_inline_script('translatorService',$inline_script,'before');
    }

    /*
     * @from 1.3.54
     * @update 1.3.58 get the local and not lang code , fix the bug with elementor
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
        //isocode are put in the js and get here
        $targetLanguageCode = $this->languageCodeToISO($targetLocale);
        $sourceLanguageCode = $this->languageCodeToISO(Falang()->get_model()->get_default_language()->locale);

        //$this->token don't work here
        $token= Falang()->get_model()->get_option('deepl_key');

        //get pro of free url
        $url = "https://api.deepl.com/v2/translate";
        $serviceFree = Falang()->get_model()->get_option('deepl_free',true);
        if ($serviceFree){
            $url = "https://api-free.deepl.com/v2/translate";
        }

        $postfields = array();
        $postfields['source_lang'] = strtoupper($sourceLanguageCode);
        $postfields['target_lang'] = strtoupper($targetLanguageCode);
        $postfields['text'] = [$text];//an array of text is necessary
        $postfields['tag_handling'] = 'html';

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Authorization: DeepL-Auth-Key '.$token;

        $data = json_encode($postfields);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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
            $response->data = $resultDecoded->translations[0]->text;
        } catch (\Exception $e) {
            $response->success= false;
            $response->data  = $e->getMessage();
        }

        return $response;
    }
}