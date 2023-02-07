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
        $('.collections').data('roles', data);
    });

    $('.collections').on('keydown', function (event) {
        if (event.key == "Enter") {
            event.preventDefault();
        }
    }).autocomplete({
        source: window.opusBaseUrl + "/admin/autocomplete/collection",
        minLength: 3,
        select: function (event, ui) {
            var container = $('#CollectionIdsSelected'); // TODO get element without hard coded name (ColectionIds)
            var listId    = 'CollectionList' + ui.item.RoleId;
            var colList   = $("#" + listId);

            // Check if list for collection role exists
            if (! colList.length) {
                var roleName = $('.collections').data('roles')[ui.item.RoleId];

                var listWrapper = $("<fieldset>").attr('class', 'collectionRole').append($("<legend>").text(roleName));
                colList         = $("<ul>").attr('id', listId);
                listWrapper.append(colList);
                container.append(listWrapper);
            }

            // Add collection to list
            if (colList) {
                colList
                    .append($("<li>")
                        .append("<input name='Collections[]' type='hidden' value='" + ui.item.Id + "'/>")
                        .append(ui.item.Name)
                        .append($("<i>").attr("class", "fa fa-trash remove-me").attr("aria-hidden", "true")));
            }
        },
        create: function () {

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
                var that  = this, currentRole = 0;
                var roles = $('.collections').data('roles');
                $.each(items, function (index, item) {
                    var li;
                    if (item.RoleId != currentRole) {
                        currentRole   = item.RoleId;
                        var roleLabel = roles[currentRole];
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

$(document).on('click', ".remove-me", function (event) {
    event.preventDefault();
    var entry = $(this).parent();
    var list  = entry.parent();
    entry.remove();
    if (! list.children('li').length) {
        list.parent().remove();
    }
});
