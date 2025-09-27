function translateService(fieldName,sourceText){

    var translatedText;

    var data = {
        action : 'service_translate',//use to find the filter
        service: 'deepl',
        sourceLanguageCode: translator.from,
        targetLanguageLocale: translator.to,
        text: [sourceText]
    };

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data : data,
        beforeSend: function() {
            jQuery('body').addClass('waiting');
        },
        success: function (result) {
            if (result.success) {
                console.log(result);
                translatedText = result.data;
                setTranslation(fieldName,translatedText);
            } else {
                console.log('Error : '+ result);
                translatedText = '--error--';
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            translatedText = "ERROR "+xhr.responseJSON["code"]+": "+xhr.responseJSON["message"];
        },
        complete: function() {
            jQuery('body').removeClass('waiting');
        }
    });
}
