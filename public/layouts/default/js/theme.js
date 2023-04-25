$(document).ready(function () {
    // JavaScript detection
    $("html").removeClass("no-js").addClass("js");

    // New window selector
    $('a.new-window').each(function () {
        if (! $(this).attr('target', '_blank')) {
            $(this).attr('target', '_blank');
        }
    })

    // normalize box-heights
    var max = 0;
        $('#adminMenuContainer ul > li > *').each(function () {
            if ($(this).height() > max) {
                max = $(this).height();
            }
        });
    $('#adminMenuContainer ul > li > *').height(max);

    // ActionBox
    var $actionbox       = $('#actionboxContainer');
    var $actionboxSpacer = $('<div />', {
        "class": "actionbox-spacer",
        "height": $actionbox.outerHeight()
    });
    if ($actionbox.size()) {
        $(window).scroll(function () {
            if (! $actionbox.hasClass('fixed') && $(window).scrollTop() > $actionbox.offset().top) {
                $actionbox.before($actionboxSpacer);
                $actionbox.addClass("fixed");
            } else if ($actionbox.hasClass('fixed') && $(window).scrollTop() < $actionboxSpacer.offset().top) {
                $actionbox.removeClass("fixed");
                $actionboxSpacer.remove();
            }
        });
        $(window).trigger("scroll");
    }

    // DropDown behaviour
    $(".dropdown > dt > a").click(function (event) {
        $(this).closest(".dropdown").toggleClass("dropdown-open");
        event.preventDefault();
    });
    $(".dropdown > dd a").click(function (event) {
        $(this).closest(".dropdown").toggleClass("dropdown-open");
        $('html,body').animate({scrollTop:$(this.hash).offset().top - ($actionbox.outerHeight() + 10)}, 0);
        event.preventDefault();
    });
    $(document).mouseup(function (event) {
        if ($(".dropdown-open") && ! $(event.target).parents().hasClass("dropdown-open")) {
            $(".dropdown-open").removeClass("dropdown-open");
        }
    });

    // Styling placeholder
    $('[placeholder]').addClass("blur");
    $('[placeholder]').focus(function () {
        $(this).removeClass("blur");
    });
    $('[placeholder]').blur(function () {
        if ( ! $(this).val() ) {
              $(this).addClass('blur');
        } else {
              $(this).removeClass('blur');
        }
    });

    // add autocomplete to GND subject input
    $('.subject').autocomplete({
        source: window.opusBaseUrl + "/admin/autocomplete/subject",
        minLength: 2,
        select: function (event, ui) {
            // automaticaly set external key field
            if (typeof ui.item.extkey !== 'undefinded') {
                var elemId = "#" + this.id.replace('Value', 'ExternalKey');
                $(elemId).val(ui.item.extkey);
            }
        }
    });
});