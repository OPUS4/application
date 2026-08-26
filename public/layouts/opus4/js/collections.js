/**
 * Collection autocomplete (Vanilla JS replacement for jQuery UI autocomplete)
 */
(function () {
    "use strict";

    var minLength     = 3;
    var debounceDelay = 180;
    var roleMap       = {};

    var activeInput        = null;
    var activeItems        = [];
    var activeIndex        = -1;
    var isOpen             = false;
    var debounceTimer      = null;
    var requestController  = null;
    var suggestionElements = [];

    function createSuggestionList()
    {
        var list       = document.createElement("ul");
        list.id        = "collection-autocomplete-list";
        list.className = "collection-autocomplete-list";
        list.setAttribute("role", "listbox");
        list.style.display = "none";
        document.body.appendChild(list);
        return list;
    }

    var suggestionList = null;

    function closeSuggestions()
    {
        isOpen                       = false;
        activeItems                  = [];
        activeIndex                  = -1;
        suggestionElements           = [];
        suggestionList.style.display = "none";
        suggestionList.innerHTML     = "";

        if (activeInput) {
            activeInput.removeAttribute("aria-activedescendant");
        }
    }

    function positionSuggestionList()
    {
        if (! activeInput) {
            return;
        }

        var pageXOffset     = window.pageXOffset || 0;
        var pageYOffset     = window.pageYOffset || 0;
        var viewportWidth   = document.documentElement.clientWidth || window.innerWidth || 0;
        var rect            = activeInput.getBoundingClientRect();
        var minWidth        = Math.ceil(rect.width);
        var desiredWidth    = Math.max(minWidth, 560);
        var maxAllowedWidth = Math.max(minWidth, viewportWidth - 24);
        var finalWidth      = Math.min(desiredWidth, maxAllowedWidth);

        var left    = pageXOffset + rect.left;
        var minLeft = pageXOffset + 12;
        var maxLeft = pageXOffset + viewportWidth - finalWidth - 12;

        if (left > maxLeft) {
            left = maxLeft;
        }
        if (left < minLeft) {
            left = minLeft;
        }

        suggestionList.style.left  = left + "px";
        suggestionList.style.top   = (pageYOffset + rect.bottom) + "px";
        suggestionList.style.width = finalWidth + "px";
    }

    function getRoleLabel(roleId)
    {
        if (roleMap && roleMap[roleId]) {
            return roleMap[roleId];
        }

        return String(roleId);
    }

    function toItemLabel(item)
    {
        if (item.Number) {
            return item.Number + " " + item.Name;
        }

        return item.Name;
    }

    function renderSuggestions(items)
    {
        closeSuggestions();

        if (! items.length) {
            return;
        }

        var currentRole = null;
        var index       = 0;
        var i;

        for (i = 0; i < items.length; i++) {
            var item = items[i];

            if (item.RoleId !== currentRole) {
                currentRole = item.RoleId;

                var category         = document.createElement("li");
                category.className   = "collection-autocomplete-category";
                category.textContent = getRoleLabel(currentRole);
                suggestionList.appendChild(category);
            }

            var option       = document.createElement("li");
            option.className = "collection-autocomplete-item";
            option.setAttribute("role", "option");
            option.setAttribute("data-index", String(index));
            option.id          = "collection-autocomplete-item-" + index;
            option.textContent = toItemLabel(item);
            option.setAttribute("aria-label", item.RoleId + " : " + item.Name);
            suggestionList.appendChild(option);

            activeItems.push(item);
            suggestionElements.push(option);
            index++;
        }

        positionSuggestionList();
        suggestionList.style.display = "block";
        isOpen                       = true;
    }

    function updateActiveSuggestion()
    {
        var i;
        for (i = 0; i < suggestionElements.length; i++) {
            var isActive = i === activeIndex;
            suggestionElements[i].classList.toggle("active", isActive);

            if (isActive && activeInput) {
                activeInput.setAttribute("aria-activedescendant", suggestionElements[i].id);
            }
        }
    }

    function addCollectionItem(item)
    {
        var container = document.getElementById("CollectionIdsSelected");
        if (! container) {
            return;
        }

        var listId  = "CollectionList" + item.RoleId;
        var colList = document.getElementById(listId);

        if (! colList) {
            var roleName          = getRoleLabel(item.RoleId);
            var listWrapper       = document.createElement("fieldset");
            listWrapper.className = "collectionRole";

            var legend         = document.createElement("legend");
            legend.textContent = roleName;
            listWrapper.appendChild(legend);

            colList    = document.createElement("ul");
            colList.id = listId;
            listWrapper.appendChild(colList);
            container.appendChild(listWrapper);
        }

        var entry = document.createElement("li");

        var hidden   = document.createElement("input");
        hidden.name  = "Collections[]";
        hidden.type  = "hidden";
        hidden.value = item.Id;
        entry.appendChild(hidden);

        entry.appendChild(document.createTextNode(item.Name));

        var removeIcon       = document.createElement("i");
        removeIcon.className = "fa fa-trash remove-me";
        removeIcon.setAttribute("aria-hidden", "true");
        entry.appendChild(removeIcon);

        colList.appendChild(entry);
    }

    function applySelection(item)
    {
        if (! activeInput) {
            return;
        }

        addCollectionItem(item);
        activeInput.value = "";
        closeSuggestions();
    }

    function fetchCollectionRoles()
    {
        return fetch(window.opusBaseUrl + "/admin/autocomplete/collectionroles", {
            headers: { "Accept": "application/json" }
        })
            .then(function (response) {
                if (! response.ok) {
                    throw new Error("Collection role request failed");
                }

                return response.json();
            })
            .then(function (data) {
                roleMap = data || {};
            })
            .catch(function () {
                roleMap = {};
            });
    }

    function fetchSuggestionsForInput(input)
    {
        var term = input.value.trim();

        if (term.length < minLength) {
            closeSuggestions();
            return;
        }

        if (requestController) {
            requestController.abort();
        }

        requestController = new AbortController();

        fetch(window.opusBaseUrl + "/admin/autocomplete/collection?term=" + encodeURIComponent(term), {
            signal: requestController.signal,
            headers: { "Accept": "application/json" }
        })
            .then(function (response) {
                if (! response.ok) {
                    throw new Error("Collection autocomplete request failed");
                }

                return response.json();
            })
            .then(function (data) {
                if (activeInput !== input) {
                    return;
                }

                renderSuggestions(Array.isArray(data) ? data : []);
            })
            .catch(function (error) {
                if (error && error.name !== "AbortError") {
                    closeSuggestions();
                }
            });
    }

    function scheduleFetch(input)
    {
        if (debounceTimer) {
            window.clearTimeout(debounceTimer);
        }

        debounceTimer = window.setTimeout(function () {
            fetchSuggestionsForInput(input);
        }, debounceDelay);
    }

    function bindCollectionInput(input)
    {
        if (input.dataset.collectionAutocompleteBound === "1") {
            return;
        }

        input.dataset.collectionAutocompleteBound = "1";
        input.setAttribute("autocomplete", "off");
        input.setAttribute("aria-controls", suggestionList.id);
        input.setAttribute("aria-autocomplete", "list");

        input.addEventListener("focus", function () {
            activeInput = input;
            if (isOpen) {
                positionSuggestionList();
            }
        });

        input.addEventListener("input", function () {
            activeInput = input;
            scheduleFetch(input);
        });

        input.addEventListener("keydown", function (event) {
            activeInput = input;

            if (event.key === "Enter") {
                event.preventDefault();
            }

            if (! isOpen || ! activeItems.length) {
                if (event.key === "ArrowDown" && input.value.trim().length >= minLength) {
                    event.preventDefault();
                    scheduleFetch(input);
                }

                return;
            }

            if (event.key === "ArrowDown") {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % activeItems.length;
                updateActiveSuggestion();
            } else if (event.key === "ArrowUp") {
                event.preventDefault();
                activeIndex = (activeIndex <= 0) ? activeItems.length - 1 : activeIndex - 1;
                updateActiveSuggestion();
            } else if (event.key === "Enter") {
                if (activeIndex >= 0) {
                    applySelection(activeItems[activeIndex]);
                }
            } else if (event.key === "Escape") {
                closeSuggestions();
            }
        });

        input.addEventListener("blur", function () {
            window.setTimeout(closeSuggestions, 120);
        });
    }

    function bindRemoveButtons()
    {
        document.addEventListener("click", function (event) {
            var removeButton = event.target.closest(".remove-me");
            if (! removeButton) {
                return;
            }

            event.preventDefault();

            var entry = removeButton.parentElement;
            if (! entry) {
                return;
            }

            var list = entry.parentElement;
            entry.remove();

            if (list && ! list.querySelector("li")) {
                var fieldset = list.parentElement;
                if (fieldset) {
                    fieldset.remove();
                }
            }
        });
    }

    function initCollections()
    {
        var inputs = document.querySelectorAll(".collections");
        if (! inputs.length) {
            return;
        }

        if (! suggestionList) {
            suggestionList = createSuggestionList();
        }
        bindRemoveButtons();

        var i;
        for (i = 0; i < inputs.length; i++) {
            bindCollectionInput(inputs[i]);
        }

        fetchCollectionRoles();

        suggestionList.addEventListener("mousedown", function (event) {
            event.preventDefault();

            var option = event.target.closest(".collection-autocomplete-item");
            if (! option) {
                return;
            }

            var index = Number(option.getAttribute("data-index"));
            if (! isNaN(index) && activeItems[index]) {
                applySelection(activeItems[index]);
            }
        });

        window.addEventListener("resize", function () {
            if (isOpen) {
                positionSuggestionList();
            }
        });

        window.addEventListener("scroll", function () {
            if (isOpen) {
                positionSuggestionList();
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initCollections);
    } else {
        initCollections();
    }
})();
