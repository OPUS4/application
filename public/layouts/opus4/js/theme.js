/**
 * This function is executed when the page has been loaded completely and prepares some rendering for the
 * administration pages.
 *
 * TODO separate into independent functions (consider performance)
 */
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

    /*
     * ActionBox
     *
     * The code here fixes a menu bar at the top of the screen when the user scrolls down, in order to keep the functions
     * always accessible.
     */
    var $actionbox       = $('.fixedMenubar');
    var $actionboxSpacer = $('<div />', {
        "class": "actionbox-spacer",
        "height": $actionbox.outerHeight()
    });
    if ($actionbox.length) {
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

    // Add autocomplete to GND subject input
    // Response can contain: 'value', 'extkey' and 'label'
    $('.subject').autocomplete({
        source: window.opusBaseUrl + "/admin/autocomplete/subject",
        minLength: 2,
        select: function (event, ui) {
            // automaticaly set external key field or clear value
            var elemId = "#" + this.id.replace('Value', 'ExternalKey');
            if (typeof ui.item.extkey !== 'undefined' && ui.item.extkey) {
                $(elemId).val(ui.item.extkey);
            } else {
                $(elemId).val(null);
            }
        }
    });

    // handle change of enrichment type in enrichment key create / edit form
    $("#admin_enrichmentkey_type").change(function () {
        var optionsElement = $("#admin_enrichmentkey_options");
        if (optionsElement) {
            // Konfigurationseinstellung für Enrichment Type löschen
            optionsElement.val("");

            var enrichmentTypeSelected = $(this).val();
            if (enrichmentTypeSelected === "") {
                // bei Auswahl der Defaultauswahl wird keine Beschreibung angezeigt
                optionsElement.next(".hint").html("");
            } else {
                // hole die Beschreibung für den ausgewählten Enrichment Type vom Server
                $.get(window.opusBaseUrl + "/admin/autocomplete/enrichmentTypeDescription", { typeName: enrichmentTypeSelected }, function (data) {
                    var optionsElement = $("#admin_enrichmentkey_options");
                    if (optionsElement) {
                        optionsElement.next(".hint").html(data.typeName);
                    }
                });
            }
        }
    });

    // handle change of enrichment key in document metadata form
    $("select.enrichmentKeyName").change(function () {
        var that = $(this);

        var name         = that.attr("id");
        var inputElement = $("#" + name.replace("KeyName", "Value"));
        if (inputElement) {
            // Wert des Formularfelds löschen, so dass beim Umschalten des EnrichmentKeys kein Wert erscheint
            inputElement.val("");
        }

        var form = that.closest("form");
        // add input element to support currentAnchor mechanism
        var input = $("<input>")
            .attr("type", "hidden")
            .attr("name", "Document[Enrichments][SelectionChanged]");
        form.append(input);

        form.submit();
    });

});
