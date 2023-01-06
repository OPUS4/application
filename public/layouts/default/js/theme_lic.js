/*
 * The MIT License (MIT)
 *
 * Copyright Mathias Bynens <http://mathiasbynens.be/>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

$(document).ready(function () {
    // Placeholder support for old browsers
    // ! http://mths.be/placeholder v1.8.7 by @mathias
    var isInputSupported    = 'placeholder' in document.createElement('input'),
        isTextareaSupported = 'placeholder' in document.createElement('textarea'),
        prototype           = $.fn,
        placeholder;

    if (isInputSupported && isTextareaSupported) {
        placeholder = prototype.placeholder = function () {
            return this;
        };

        placeholder.input = placeholder.textarea = true;
    } else {
        placeholder = prototype.placeholder = function () {
            return this
                .filter((isInputSupported ? 'textarea' : ':input') + '[placeholder]')
                .not('.placeholder')
                .bind('focus.placeholder', clearPlaceholder)
                .bind('blur.placeholder', setPlaceholder)
                .trigger('blur.placeholder').end();
        };

        placeholder.input    = isInputSupported;
        placeholder.textarea = isTextareaSupported;

        $(function () {
            // Look for forms
            $(document).delegate('form', 'submit.placeholder', function () {
                // Clear the placeholder values so they don’t get submitted
                var $inputs = $('.placeholder', this).each(clearPlaceholder);
                setTimeout(function () {
                    $inputs.each(setPlaceholder);
                }, 10);
            });
        });

        // Clear placeholder values upon page reload
        $(window).bind('unload.placeholder', function () {
            $('.placeholder').val('');
        });
    }

    function args(elem)
    {
        // Return an object of element attributes
        var newAttrs      = {},
            rinlinejQuery = /^jQuery\d+$/;
        $.each(elem.attributes, function (i, attr) {
            if (attr.specified && ! rinlinejQuery.test(attr.name)) {
                newAttrs[attr.name] = attr.value;
            }
        });
        return newAttrs;
    }

    function clearPlaceholder()
    {
        var $input = $(this);
        if ($input.val() === $input.attr('placeholder') && $input.hasClass('placeholder')) {
            if ($input.data('placeholder-password')) {
                $input.hide().next().show().focus().attr('id', $input.removeAttr('id').data('placeholder-id'));
            } else {
                $input.val('').removeClass('placeholder');
            }
        }
    }

    function setPlaceholder()
    {
        var $replacement,
            $input     = $(this),
            $origInput = $input,
            id         = this.id;
        if ($input.val() === '') {
            if ($input.is(':password')) {
                if (! $input.data('placeholder-textinput')) {
                    try {
                        $replacement = $input.clone().attr({ 'type': 'text' });
                    } catch (e) {
                        $replacement = $('<input>').attr($.extend(args(this), { 'type': 'text' }));
                    }
                    $replacement
                        .removeAttr('name')
                        // We could just use the `.data(obj)` syntax here, but that wouldn’t work in pre-1.4.3 jQueries
                        .data('placeholder-password', true)
                        .data('placeholder-id', id)
                        .bind('focus.placeholder', clearPlaceholder);
                    $input
                        .data('placeholder-textinput', $replacement)
                        .data('placeholder-id', id)
                        .before($replacement);
                }
                $input = $input.removeAttr('id').hide().prev().attr('id', id).show();
            }
            $input.addClass('placeholder').val($input.attr('placeholder'));
        } else {
            $input.removeClass('placeholder');
        }
    }

    $(function () {
      // Invoke the plugin
        $('input, textarea').placeholder();
    });
});
