jQuery(document).ready(function ($) {
    let apk_upload_progress;

    $('#at_apt_advanced_options').change(function () {
        if (this.checked) {
            $('.at-import-table').fadeIn(150);
        } else {
            $('.at-import-table').fadeOut(150);
        }
    });

    $(document).on('submit', '#at-apt-importer-form', async function (e) {
        e.preventDefault();

        var form = $(this);
        var inputs = form.find("input, select, button, textarea");
        var serialized_data = form.find(":input:not([type='submit']):not([type='button'])").map(function () {
            if (this.type === 'checkbox') {
                return { name: this.name, value: this.checked ? true : false };
            } else {
                return { name: this.name, value: $(this).val() };
            }
        }).get();

        $('.at-importer-results').hide();
        $('.process-log').hide();
        $('.process-error-log').hide();

        if ($('.process-log')) {
            $('.process-log').empty();
        }

        if ($('.process-error-log')) {
            $('.process-error-log').empty();
        }

        let apt_url;
        let advanced_options;
        let post_status;
        let post_title_start;
        let post_title_end;
        let mod_feature;
        let import_screenshots;
        let is_get_apk;
        let post_thumbnail_quality;

        $.each(serialized_data, function (index, element) {
            switch (element.name) {
                case "at_apt_url":
                    apt_url = element.value;
                    break;
                case "at_apt_advanced_options":
                    advanced_options = element.value;
                    break;
                case "at_apt_post_status":
                    post_status = element.value;
                    break;
                case "at_apt_post_title_start":
                    post_title_start = element.value;
                    break;
                case "at_apt_post_title_end":
                    post_title_end = element.value;
                    break;
                case "at_apt_mod_feature":
                    mod_feature = element.value;
                    break;
                case "at_apt_import_screenshots":
                    import_screenshots = element.value;
                    break;
                case "is_get_apk":
                    is_get_apk = element.value;
                    break;
                case "at_apt_post_thumbnail_quality":
                    post_thumbnail_quality = element.value;
                    break;
            }
        });

        inputs.prop("disabled", true);
        append_success_info('APK Extraction started...');

        try {
            const create_post_data = {
                action: 'at_apt_create_post',
                nonce: apktemplates_ajax_vars.nonce,
                apt_url: apt_url,
                is_advanced_options: advanced_options,
                post_status: post_status,
                post_title_start: post_title_start,
                post_title_end: post_title_end,
                mod_feature: mod_feature,
                import_screenshots: import_screenshots,
                is_get_apk: is_get_apk,
                post_thumbnail_quality: post_thumbnail_quality,
            };

            let apk_info = await at_ajax_request(apktemplates_ajax_vars.ajax_url, 'POST', create_post_data);

            apk_info = JSON.parse(apk_info);
            const status = apk_info?.status;

            if (status === 'success') {
                append_success_info(apk_info?.data?.message);

                if (apk_info?.data?.is_apk_exist) {
                    append_warning_info('APK file already exist.');
                }

                if (apk_info?.data?.get_apk && apk_info?.data?.has_apk_file && !apk_info?.data?.is_apk_exist) {
                    await apt_upload_apk(apk_info?.data?.post_id, apk_info?.data?.apk_file_url, apk_info?.data?.apk_file_version, apk_info?.data?.apk_file_size);
                } else {
                    reset_apt_importer_form();
                }
            } else if (status === 'error') {
                handle_ajax_error(apk_info?.data?.message);
                reset_apt_importer_form();
            }

        } catch (error) {
            handle_ajax_error('Error: Syntax Error, Try again!');
            reset_apt_importer_form();
        }
    });

    $(document).on('click', '#at-apt-search-submit', async function (e) {
        var search_query = $('#at-apt-search-query').val();

        if (search_query.length < 3) {
            show_snackbar("Enter atleast 3 letters");
            return;
        }

        $('.at-apk-results-container').html('<div class="at-apk-no-results"><span class="spinner is-active"></span></div>');

        try {
            const search_post_data = {
                action: 'at_apt_search_posts',
                nonce: apktemplates_ajax_vars.nonce,
                search_query: search_query
            };

            let search_results = await at_ajax_request(apktemplates_ajax_vars.ajax_url, 'POST', search_post_data);

            search_results = JSON.parse(search_results);

            let results = search_results.data;

            if (search_results.status === "error") {
                $('.at-apk-results-container').html('<div class="at-apk-no-results"><p>' + results.message + '</p></div>');
                return;
            }

            let list_html = results.map(result => (
                '<article>' +
                '   <img src="' + (result.apk_thumbnail || '') + '" referrerPolicy="no-referrer">' +
                '   <div class="at-apk-info">' +
                '       <h3 class="at-mb-0-5">' + (result.apk_name || 'Unknown App') + '</h3>' +
                '       <p class="at-apk-dev at-mb-0-5">' + ((result.apk_developer) || 'Unknown Developer') + '</p>' +
                '       <div class="at-apk-rating at-mb-0-5">' +
                '           <span><i class="fa fa-star"></i> ' + ((result.apk_rating) || 'N/A') + '</span>' +
                '       </div>' +
                '       <div class="at-apk-size">' +
                '           <span><i class="fa fa-download"></i> ' + (result.apk_downloads || '') + '</span>' +
                '       </div>' +
                '   </div>' +
                '   <button class="at-btn at-btn-success at-w-full" data-apt-url="https://' + (result.apk_url || '') + '">Get URL</button>' +
                '</article>'
            ));


            $('.at-apk-no-results').remove();
            $('.at-apk-results-container').html(list_html);

        } catch (error) {
            if (error.status === 500) {
                alert(`Internal Server Error (500)!. ${error.responseText}`);
            } else if (error.status === 524) {
                alert('Server connection timeout.');
            } else {
                alert(`Error: ${error}`);
            }
        } finally {

        }

    });

    $('.at-apk-results-container').on('click', '.at-btn-success', function () {
        const gp_url = $(this).data('apt-url');
        $('#at-apt-url').val(gp_url);
        $('#at-importer').animate({ scrollTop: 0 }, 800);
        $('#at-apt-url').focus();
    });

    async function apt_upload_apk(post_id, apk_url, apk_version, apk_size) {
        append_success_info('APK file found!');
        append_success_info('APK file started uploading!');
        append_success_info('<strong>Progress: </strong><span id="progress">0%</span>');
        append_success_info('<strong>Uploading: </strong><span id="uploaded-size">0MB of 0MB</span>');
        append_success_info('<strong>Downloading: </strong><span id="downloaded-size">0MB of 0MB</span>');

        try {
            const create_post_data = {
                action: 'at_apt_upload_apk',
                nonce: apktemplates_ajax_vars.nonce,
                post_id: post_id,
                apk_url: apk_url,
                apk_version: apk_version,
                apk_size: apk_size
            };

            const beforeSend = function (xhr) {
                apk_upload_progress = setInterval(function () {
                    get_upload_process(apk_url, apk_version);
                }, 2500);
            };

            let apk_file_info = await at_ajax_request(apktemplates_ajax_vars.ajax_url, 'POST', create_post_data, beforeSend);

            apk_file_info = JSON.parse(apk_file_info);
            const status = apk_file_info?.status;

            if (status === 'error') {
                handle_ajax_error(apk_file_info?.data?.message);
            } else if (status === 'warning') {
                append_warning_info(apk_file_info?.data?.message)
            } else {
                append_success_info(apk_file_info?.data?.message);
                clearInterval(apk_upload_progress);
            }

        } catch (error) {
            handle_ajax_error('Error: Syntax Error, Try again!');
        } finally {
            clearInterval(apk_upload_progress);
            reset_apt_importer_form();
        }
    }

    async function get_upload_process(apk_url, apk_version) {
        try {
            const process_data = {
                action: 'at_get_process',
                nonce: apktemplates_ajax_vars.nonce,
                apk_url: apk_url,
                apk_version: apk_version
            };

            let process_info = await at_ajax_request(apktemplates_ajax_vars.ajax_url, 'POST', process_data);

            process_info = JSON.parse(process_info);

            const is_import_file = process_info.is_file_import;

            if (process_info?.progress >= 100 && !is_import_file) {
                $('#progress').html(process_info?.progress + '%');
                $('#uploaded-size').html(process_info?.uploaded_size + ' of ' + process_info?.total_size);
                $('#downloaded-size').html(process_info?.downloaded_size + ' of ' + process_info?.download_total_size);
                clearInterval(apk_upload_progress);
            }

            $('#progress').html(process_info?.progress + '%');
            $('#uploaded-size').html(process_info?.uploaded_size + ' of ' + process_info?.total_size);
            $('#downloaded-size').html(process_info?.downloaded_size + ' of ' + process_info?.download_total_size);
        } catch (error) {
            handle_ajax_error('Error: Syntax Error, Try again!');
            clearInterval(apk_upload_progress);
            reset_apt_importer_form();
        }
    }


    async function at_ajax_request(url, type, data, beforeSend = null) {
        try {
            const ajaxOptions = {
                url: url,
                type: type,
                data: data,
            };

            if (beforeSend) {
                ajaxOptions.beforeSend = beforeSend;
            }

            const response = await $.ajax(ajaxOptions);
            return response;
        } catch (error) {
            reset_apt_importer_form();
			alert(`Error: ${error.message || error}`);
        }
    }

    function append_success_info(info_text) {
        $('.at-importer-results').show();
        $('.process-log').show();
        $('.process-log').append(`<li><span class="at-success-tick"><i class="fa fa-check-circle "></i></span>${'  ' + info_text}</li>`);
    }

    function append_warning_info(info_text) {
        $('.at-importer-results').show();
        $('.process-log').show();
        $('.process-log').append(`<li><span class="at-warning-tick"><i class="fa fa-exclamation-circle "></i></span>${'  ' + info_text}</li>`);
    }

    function handle_ajax_error(info_text) {
        $('.at-importer-results').show();
        $('.process-error-log').show();
        $('.process-error-log').append(`<li><span class="at-danger-tick"><i class="fa fa-times-circle"></i></span>${'  ' + info_text}</li>`);
    }

    function show_snackbar(message) {
        var snackbar = $('<div id="at-snackbar">' + message + '</div>');

        $('body').append(snackbar);

        snackbar.css({
            'position': 'fixed',
            'bottom': '7%',
            'left': '50%',
            'transform': 'translateX(-50%)',
            'background-color': '#111',
            'color': '#fff',
            'font-size': '15px',
            'font-weight': '500',
            'padding': '15px',
            'border-radius': '2px',
            'box-shadow': '0 0 10px rgba(0, 0, 0, 0.2)',
            'z-index': '999',
            'opacity': '0',
            'transition': 'opacity 0.3s',
        });

        setTimeout(function () {
            snackbar.css('opacity', '1');
        }, 300);

        setTimeout(function () {
            snackbar.css('opacity', '0');
            setTimeout(function () {
                snackbar.remove();
            }, 300);
        }, 4000);
    }

    function reset_apt_importer_form() {
        var form = $('#at-apt-importer-form');
        var inputs = form.find("input, select, button, textarea");
        $('#at-apt-url').val('');
        $('#at-apt-url').prop('disabled', null);
        inputs.prop("disabled", null);
        return;
    }
});