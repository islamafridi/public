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

class TranslatorAzure extends TranslatorDefault {

    /*
     * @update 1.3.49 add parent constructor
     * */
    function __construct()
    {
        parent::__construct();

        $this->token= Falang()->get_model()->get_option('azure_key');

        //supported language https://docs.microsoft.com/en-us/azure/cognitive-services/translator/language-support
        //add extra language for bing
        $this->addServiceLanguage('fr-ca','fr-ca');//canadian

        $this->script = 'translatorAzure.js';
    }

    public function installScripts ($from,$to)
    {
        parent::installScripts($from,$to);

        $from = $this->languageCodeToISO($from);
        $to   = $this->languageCodeToISO($to);

        $inline_script = "var translator = {'from' : '".strtolower($from). "','to' : '".strtolower($to). "'};\n";
        $inline_script .= "var azureKey = '".$this->token."';\n";
        wp_add_inline_script('translatorService',$inline_script,'before');
    }

    /*
     * @from 1.3.49
     *
     * Translate content to target language
     * support html translation
     * use for elmentor and other builder
     *
     * Documentation
     * https://stackoverflow.com/questions/13215130/using-the-azure-microsoft-translator-api-with-php-and-curl
     *
     * param sting @text : text to translate (html format)
     * param string @targetLocal language locale ex de_DE*
     *
     */
    public function translate($text,$targetLocale){
        $url = "https://api.cognitive.microsofttranslator.com/translate?api-version=3.0";
        $location = 'global';

        $targetLanguageCode = $this->languageCodeToISO($targetLocale);
        $sourceLanguageCode = $this->languageCodeToISO(Falang()->get_model()->get_default_language()->locale);

        //$this->token don't work here
        $token= Falang()->get_model()->get_option('azure_key');

        $requestBody = array (
            array (
                'Text' => $text,
            ),
        );
        $content = json_encode($requestBody);

        $url .= '&from='. $sourceLanguageCode;
        $url .= '&to='. $targetLanguageCode;
        $url .= '&textType=html';

        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Content-Length: ' . strlen($content);
        $header[] = 'Ocp-Apim-Subscription-Region: ' . $location;
        $header[] = 'Ocp-Apim-Subscription-Key: ' . $token;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$content);
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

        if (isset($resultDecoded->error)){
            $response->success= false;
            $response->data = $resultDecoded->error->message;
            return $response;
        }

        //decode the translation returned
        try {
            $response->success = true;
            $response->data = $resultDecoded[0]->translations[0]->text;
        } catch (\Exception $e) {
            $response->success= false;
            $response->data  = $e->getMessage();

        }
        return $response;

    }

}