(function ($) {
    // Quicklink
    window.addEventListener('load', function () {
        quicklink.listen({
            ignores: [/(#|wp|wpnonce|php|login)/],
        });
    });

    function minWin(href) {
        var d = document.documentElement,
            h = 500,
            w = 500,
            myWindow = window.open(
                href,
                "myWindow",
                "scrollbars=1,height=" +
                    Math.min(h, screen.availHeight) +
                    ",width=" +
                    Math.min(w, screen.availWidth) +
                    ",left=" +
                    Math.max(0, (d.clientWidth - w) / 2 + window.screenX) +
                    ",top=" +
                    Math.max(0, (d.clientHeight - h) / 2 + window.screenY)
            );
        if (myWindow.screenY >= screen.availHeight - myWindow.outerHeight) {
            myWindow.moveTo(
                myWindow.screenX,
                screen.availHeight - myWindow.outerHeight
            );
        }
        if (myWindow.screenX >= screen.availWidth - myWindow.outerWidth) {
            myWindow.moveTo(
                screen.availWidth - myWindow.outerWidth,
                myWindow.screenY
            );
        }
    }
    window.minWin = minWin;

    $(document).ready(function () {
        $('#spoiler-btn').click(function () {
            $('#spoiler').toggleClass("open");
            return false;
        });
        $('.app-faq-heading').click(function () {
            $('.app-faq').toggleClass("open");
            return false;
        });

        var tabContainers = $('.tab-content .tab-pane');
        tabContainers.hide().filter(':first').show();
        $('.tabs li a')
            .click(function () {
                tabContainers.hide();
                tabContainers.filter(this.hash).show();
                $('.tabs li a').removeClass('active');
                $(this).addClass('active');
                return false;
            })
            .filter(':first')
            .click();

        $('#catmenu_btn, .modal_close, #catmenu_link').on('click', function () {
            $('#catmenu_modal').toggleClass('open');
            setTimeout(function () {
                $('html').toggleClass('mdl');
            }, 50);
            return false;
        });

        let url = window.location.href;
        $('.modal_catmenu li a').each(function () {
            if (this.href === url) {
                $(this).addClass('active');
            }
        });

        var $root = $('html, body');
        $('a.anchor').click(function () {
            var href = $.attr(this, 'href');
            $root.animate(
                {
                    scrollTop: $(href).offset().top,
                },
                500
            );
            return false;
        });

        $('.head_menu_btn, .hmenu_close').on('click', function () {
            $('.hmenu').toggleClass('open').removeClass('sm');
            $('.submenu').removeClass('open');
            setTimeout(function () {
                $('html').toggleClass('hm');
            }, 50);
            return false;
        });

        $('.toggle_submenu').on('click', function () {
            $('.hmenu').toggleClass('sm');
            $(this).parent('.submenu').toggleClass('open');
            return false;
        });

        $('#qsearch_btn, .qsearch_close').on('click', function () {
            $('#qsearch_modal').toggleClass('open');
            setTimeout(function () {
                $('html').toggleClass('qs');
                $('#searchinput').focus();
            }, 50);
            return false;
        });

        $('button[type="submit"]').click(function () {
            $('#searchinput').each(function () {
                if ($(this).val() != '') {
                    $(this).val($.trim($(this).val().toLowerCase()));
                }
            });
        });

        $('.app-panel-close').on('click', function () {
            $('html').removeClass('ap-open');
            return false;
        });

        $('.ui-dialog-buttonset button').click(function () {
            $('.ui-dialog').hide();
        });

        $('.ui-dialog-titlebar-close').click(function () {
            $('.ui-dialog').hide();
        });
    });

    /* Loading animation */
    window.ShowLoading = function (text) {
        $('body').append(
            '<div id="loading-layer" style="display:none">' + text + '</div>'
        );
        var left = ($(window).width() - $('#loading-layer').width()) / 2;
        var top = ($(window).height() - $('#loading-layer').height()) / 2;
        $('#loading-layer').css({
            left: left + 'px',
            top: top + 'px',
            position: 'fixed',
            zIndex: '99',
        });
        $('#loading-layer').fadeTo('slow', 0.6);
    };

    window.HideLoading = function () {
        $('#loading-layer').fadeOut('slow', function () {
            $('#loading-layer').remove();
        });
    };

    /* Rating */
    var star_rating = $('#rating-layer');

    if (star_rating.length > 0) {
        var ls = localStorage.getItem(
            'post_rating-' + star_rating.data('post_id')
        );
        if (ls) {
            star_rating.attr('data-rated', 'true');
        }
    }

    var is_rated = star_rating.attr('data-rated') === 'true';

    if (is_rated) {
        disable_star_ratings();
    } else {
        enable_star_ratings();
    }

    function disable_star_ratings() {
        var rating_items = star_rating.find('li a');
        rating_items.each(function () {
            $(this).css('pointer-events', 'none');
            $(this).css('opacity', '0.5');
        });
    }

    function enable_star_ratings() {
        var rating_items = star_rating.find('li a');
        rating_items.each(function () {
            $(this).css('pointer-events', 'auto');
            $(this).css('opacity', '1');
        });
    }

    window.doRate = function (rating, post_id) {
        ShowLoading('Loading.Please, wait...');
        $.ajax({
            type: 'POST',
            url: apktemplates_ajax_vars.ajax_url,
            dataType: 'json',
            data: {
                action: 'apkt_save_post_rating',
                nonce: apktemplates_ajax_vars.nonce,
                post_id: post_id,
                rating: rating,
            },
            success: function (response) {
                if (response.status) {
                    HideLoading();
                    $('.current-rating').css(
                        'width',
                        response.data.new_rating * 20 + '%'
                    );
                    $('.rate_num .fbold').text(response.data.new_rating);
                    $('#vote-num').text(response.data.new_votes);
                    localStorage.setItem(
                        'post_rating-' + post_id,
                        rating
                    );
                    disable_star_ratings();
                } else {
                    $('#dlepopup').text(response.errorInfo);
                    $('#ui-dialog').css('display', 'block');
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                $('#dlepopup').text(errorThrown);
                $('#ui-dialog').css('display', 'block');
            },
            complete: function () {
                HideLoading();
            },
        });
    };
})(jQuery);
