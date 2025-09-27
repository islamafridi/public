function translateService(fieldName,sourceText){
	var translatedText;

    var data = {
		    from: translator.from,
		    to:  translator.to,
            translateMode: 'html',
            platform: 'api',
            text: sourceText
        };


	jQuery.ajax({
        url: "https://api-b2b.backenster.com/b1/api/v3/translate",
        dataType: 'json',
        headers:  {
            'authorization': 'Bearer '+LingvanexKey,
            'Content-Type':'application/x-www-form-urlencoded'
        },
        data: data,
        type: 'POST',
        beforeSend: function() {
            jQuery('body').addClass('waiting');
        },
		success: function (response) {
            console.log(response);
			translatedText = response.result;
            setTranslation(fieldName,translatedText);
		},
		error: function (xhr) {
			translatedText = "ERROR : "+xhr.responseJSON["err"];
		},
        complete: function() {
            jQuery('body').removeClass('waiting');
        }
    });
      
	return translatedText;
}