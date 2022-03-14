/**
 * This function is executed when the page has been loaded completely and prepares some rendering for the
 * administration pages.
 *
 * TODO separate into independent functions (consider performance)
 * TODO load collection role info into variable
 * TODO sort collections
 */

$(document).ready(function () {

    $.getJSON(window.opusBaseUrl + "/admin/autocomplete/collectionroles", function (data) {
        $('.collections').data( 'roles', data);
    });

    $('.collections').on('keydown', function(event) {
        if (event.key == "Enter") {
            event.preventDefault();
        }
    }).autocomplete({
        source: window.opusBaseUrl + "/admin/autocomplete/collection",
        minLength: 3,
        select: function (event, ui) {

            // TODO check if list for role exists
            // TODO if list for role does not exist create
            // TODO if list is empty remove it

            $('#CollectionIdsList')
                .append( $( "<li>" )
                    .append(ui.item.Name)
                    .append( $("<button>").attr("class", "remove-me").text('Remove')));
        },
        create: function() {

            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {

                var label = item.Name;

                if (item.Number) {
                    label = item.Number + " " + label;
                }

                return $("<li>")
                    .attr("data-value", item.Id)
                    .append($("<div>").text(label))
                    .appendTo(ul);
            };

            $(this).data('ui-autocomplete')._renderMenu = function (ul, items) {
                var that = this, currentRole = 0;
                $.each(items, function (index, item) {
                    var li;
                    if (item.RoleId != currentRole) {
                        currentRole = item.RoleId;
                        roles = $('.collections').data('roles');
                        roleLabel = roles[currentRole];
                        ul.append("<li class='ui-autocomplete-category'>" + roleLabel + "</li>");
                    }

                    li = that._renderItemData(ul, item);

                    if (item.RoleId) {
                        li.attr("aria-label", item.RoleId + " : " + item.Name);
                    }
                })
            }

        }
    });

});

$(document).on('click', ".remove-me", function(event) {
    var entry = $(this).parent();
    entry.remove();
    event.preventDefault();
});
