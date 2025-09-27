/*
*
* @upcate 1.3.54 make it working async
* @update 1.3.55 change param's order for setTranslation
*
* value : string field name to get the source for translation
* action : string copy or translate
* */
function copyToTranslation(fieldname,action) {
    try {
        if (document.getElementById('edit-translation')
            || document.getElementById('edit-term-translation')
            || document.getElementById('edit-string-translation')
            || document.getElementById('edit-option-translation')) {
            $local_doc =
            innerHTML="";
            if (action=="copy") {
                srcEl = document.getElementById("original_value_"+fieldname);
                setTranslation(fieldname,srcEl.innerHTML)
            }
            if (action=="translate") {
                srcEl = document.getElementById("original_value_"+fieldname);
                translateService(fieldname,srcEl.innerHTML);//call the translation async
            }
        }
    }
    catch(e){
        console.log('error in copyToTranslation');
        console.log(e);
    }
}

/*
* from : 1.3.54
* @update 1.3.55 change param's order
* Set the translation in field
* */
function setTranslation(fieldname,value){
    srcEl = document.getElementById(fieldname);

    //both need to be done in case we are
    if (srcEl != null) {
        //don't work for editor in visual mode but work in text mode and for other field type
        srcEl.value = value.trim();//set the text area
        try {
            //save the content in visual mode
            if (!tinymce.get(fieldname).hidden){
                //visual mode
                tinymce.get(fieldname).setContent(value.trim()); //set the editor in visual mode
            }
        } catch(e){
            //nothing to do
        }
    }
}

//add delete ajax action for post,menu,term
jQuery( document ).ready(function($) {
    jQuery(".ajax-delete-action").on("click", function (e) {

        var result = confirm('You are about to permanently delete this translation. Are you sure?');
        if (result != true) {
            return false;
        }

        var ajaxurl = $(this).attr('href');

        $('html, body').css("cursor", "wait");

        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {},
            success: function (response) {
                $('html, body').css("cursor", "auto");
                if (response.success) {
                    //display toast message

                    alert(response.message);
                } else {
                    //display toast error
                    //TODO display error for user
                    //var logMsg = '<div id="message" class="updated" style="display:block !important;"><p>' +
                    //    'Error during options save' +
                    //    '</p></div>';
                    //jQuery('#ajax-response').append( logMsg );
                    console.log("response", response);

                }

            },
            error: function (e, xhr, error) {
                $('html, body').css("cursor", "auto");
                console.log("error", xhr, error);
                console.log(e.responseText);
                console.log("ajaxurl", ajaxurl);
                //console.log("params", params);
            }
        })
        return false;
    });
});

jQuery( document ).ready(function($) {

    // Attach behaviour to toggle button.
    jQuery(document).on('click', '#toogle-source-panel', function()
    {
        var referenceHide = this.getAttribute('data-hide-reference');
        var referenceShow = this.getAttribute('data-show-reference');

        if ($(this).text() === referenceHide)
        {
            $(this).text(referenceShow);
        }
        else
        {
            $(this).text(referenceHide);
        }

        $('.col-source').toggle();
        $('.col-action').toggle();
        $('.col-target').toggleClass('full-width');

        return false;
    });
});

//add ajax hide notice system
jQuery( document ).ready(function($) {
    $('.falang-notice--dismissible').on('click', '.falang-notice__dismiss, .falang-notice-dismiss', function (event) {
        event.preventDefault();
        var $wrapperElm = $(this).closest('.falang-notice--dismissible');
        $.post(ajaxurl, {
            action: 'falang_set_admin_notice_viewed',
            plugin_id: $wrapperElm.data('plugin_id'),
            notice_id: $wrapperElm.data('notice_id')
        });
        $wrapperElm.fadeTo(100, 0, function () {
            $wrapperElm.slideUp(100, function () {
                $wrapperElm.remove();
            });
        });
    });
});

//popup system
jQuery( document ).ready(function($) {
    jQuery(".falang-thickbox").click(function(e) {
        tb_show('', this.href, false);
        jQuery("#TB_window").addClass("falang-modal-full");
        return false;
    });
});

