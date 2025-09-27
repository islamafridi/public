jQuery(document).ready(function($) {
    $('#download-table tbody').sortable({
        items: 'tr',
        cursor: 'move',
        handle: '.handle',
    });

    $('#add-row').on('click', function() {
        var row = $('.empty-d-row').clone(true);
        row.removeClass('empty-d-row').css('display', 'table-row');;
        row.find('input').val('');
        row.insertBefore('#download-table tbody > tr:first');
        return false;
    });

    $('#download-table').on('click', '.remove-btn', function() {
        $(this).parents('tr').remove();
        return false;
    });
	
	/* Screenshots JS */
    $('.upload-screenshot-btn').on('click', function() {
        var button = $(this);
        var target_field_id = button.data('target');
        var custom_uploader = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#' + target_field_id).val(attachment.url);
        });

        custom_uploader.open();
    });

    $('#add-screenshot-btn').on('click', function() {
        var row = $('.empty-screenshot-row').clone(true);
        row.removeClass('empty-screenshot-row').css('display', 'table-row');
        var counter = $('.upload-screenshot-btn').length + 1;
        row.find('input').attr('id', 'screenshot-url-' + counter);
        row.find('.upload-screenshot-btn').attr('id', 'screenshot-btn-' + counter);
        row.find('.upload-screenshot-btn').attr('data-target', 'screenshot-url-' + counter);
        row.insertBefore('#screenshots-table tbody>tr:last');
        return false;
    });

    $('.remove-screenshot-btn').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });

    /* Banner Image Thumbnail JS */
    var file_frame;

    $.fn.upload_second_featured_image = function (button) {
        var button_id = button.attr('id');
        var field_id = button_id.replace('_button', '');

        if (file_frame) {
            file_frame.open();
            return;
        }

        file_frame = wp.media.frames.file_frame = wp.media({
            title: $(this).data('uploader_title'),
            button: {
                text: $(this).data('uploader_button_text'),
            },
            multiple: false
        });

        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $("#upload_banner_image").val(attachment.url);
            $("#banner_background").html('<span class="components-responsive-wrapper"><div><img src="' + attachment.url + '" alt="" class="components-responsive-wrapper__content" style="width: 100%; height: auto;"></div></span>');
            $('#add_banner').text('Replace');
            $('#remove_banner').show();
        });

        file_frame.open();
    };

    $('#add_banner, #banner_background').click(function (event) {
        event.preventDefault();
        $.fn.upload_second_featured_image($(this));
    });


    $('#remove_banner').click(function (event) {
        event.preventDefault();
        if ($('#upload_banner_image').val() !== '') {
            $('#upload_banner_image').val('');
            $('#banner_container img').attr('src', '');
            $('#banner_background').attr('class', 'components-button editor-post-featured-image__toggle');
            $('#banner_background').html('Add Banner Image');
            $('#add_banner').text('Add Banner');
            $(this).hide();
        }
    });

    $('.range-input').on('input', function(e) {
        $('.tooltip').text(e.target.value);
    });    
});