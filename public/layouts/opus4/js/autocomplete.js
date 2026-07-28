/**
 * Subject autocomplete (Vanilla JS)
 *
 * Extracted from theme.js for modularization.
 */
function initSubjectAutocomplete()
{
    var endpoint      = window.opusBaseUrl + "/admin/autocomplete/subject";
    var minLength     = 2;
    var debounceDelay = 180;

    var activeInput     = null;
    var items           = [];
    var activeIndex     = -1;
    var isOpen          = false;
    var timer           = null;
    var abortController = null;

    function createList()
    {
        var list       = document.createElement("ul");
        list.id        = "subject-autocomplete-list";
        list.className = "subject-autocomplete-list";
        list.setAttribute("role", "listbox");
        list.style.display = "none";
        document.body.appendChild(list);
        return list;
    }

    var list = createList();

    function closeList()
    {
        isOpen             = false;
        items              = [];
        activeIndex        = -1;
        list.style.display = "none";
        list.innerHTML     = "";

        if (activeInput) {
            activeInput.removeAttribute("aria-activedescendant");
        }
    }

    function positionList()
    {
        if (! activeInput) {
            return;
        }

        var rect         = activeInput.getBoundingClientRect();
        list.style.left  = (window.scrollX + rect.left) + "px";
        list.style.top   = (window.scrollY + rect.bottom) + "px";
        list.style.width = rect.width + "px";
    }

    function updateActiveItem()
    {
        var optionElements = list.querySelectorAll(".subject-autocomplete-item");
        for (var i = 0; i < optionElements.length; i++) {
            optionElements[i].classList.toggle("active", i === activeIndex);
            if (i === activeIndex && activeInput) {
                activeInput.setAttribute("aria-activedescendant", optionElements[i].id);
            }
        }
    }

    function applySelection(item)
    {
        if (! activeInput) {
            return;
        }

        activeInput.value = item.value || "";

        var extKeyInput = document.getElementById(activeInput.id.replace(/-Value$/, "-ExternalKey"));
        if (extKeyInput) {
            extKeyInput.value = (typeof item.extkey !== "undefined" && item.extkey) ? item.extkey : "";
        }

        closeList();
    }

    function renderOptions(newItems)
    {
        items          = newItems;
        activeIndex    = -1;
        list.innerHTML = "";

        if (! items.length) {
            closeList();
            return;
        }

        for (var i = 0; i < items.length; i++) {
            var option       = document.createElement("li");
            option.className = "subject-autocomplete-item";
            option.id        = "subject-autocomplete-item-" + i;
            option.setAttribute("role", "option");
            option.setAttribute("data-index", String(i));
            option.textContent = items[i].label || items[i].value || "";
            list.appendChild(option);
        }

        positionList();
        list.style.display = "block";
        isOpen             = true;
    }

    function fetchSuggestions()
    {
        if (! activeInput) {
            return;
        }

        var term = activeInput.value.trim();
        if (term.length < minLength) {
            closeList();
            return;
        }

        if (abortController) {
            abortController.abort();
        }

        abortController = new AbortController();
        fetch(endpoint + "?term=" + encodeURIComponent(term), {
            signal: abortController.signal,
            headers: { "Accept": "application/json" }
        })
            .then(function (response) {
                if (! response.ok) {
                    throw new Error("Autocomplete request failed");
                }
                return response.json();
            })
            .then(function (data) {
                renderOptions(Array.isArray(data) ? data : []);
            })
            .catch(function (error) {
                if (error && error.name !== "AbortError") {
                    closeList();
                }
            });
    }

    function scheduleFetch()
    {
        if (timer) {
            window.clearTimeout(timer);
        }
        timer = window.setTimeout(fetchSuggestions, debounceDelay);
    }

    function bindInput(input)
    {
        if (input.dataset.subjectAutocompleteBound === "1") {
            return;
        }
        input.dataset.subjectAutocompleteBound = "1";

        input.setAttribute("autocomplete", "off");
        input.setAttribute("aria-controls", list.id);
        input.setAttribute("aria-autocomplete", "list");

        input.addEventListener("focus", function () {
            activeInput = input;
            if (isOpen) {
                positionList();
            }
        });

        input.addEventListener("input", function () {
            activeInput = input;
            scheduleFetch();
        });

        input.addEventListener("keydown", function (event) {
            activeInput = input;

            if (! isOpen || ! items.length) {
                if (event.key === "ArrowDown" && input.value.trim().length >= minLength) {
                    scheduleFetch();
                }
                return;
            }

            if (event.key === "ArrowDown") {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem();
            } else if (event.key === "ArrowUp") {
                event.preventDefault();
                activeIndex = (activeIndex <= 0) ? items.length - 1 : activeIndex - 1;
                updateActiveItem();
            } else if (event.key === "Enter") {
                if (activeIndex >= 0) {
                    event.preventDefault();
                    applySelection(items[activeIndex]);
                }
            } else if (event.key === "Escape") {
                closeList();
            }
        });

        input.addEventListener("blur", function () {
            window.setTimeout(closeList, 100);
        });
    }

    var subjectInputs = document.querySelectorAll("input.subject");
    for (var i = 0; i < subjectInputs.length; i++) {
        bindInput(subjectInputs[i]);
    }

    list.addEventListener("mousedown", function (event) {
        event.preventDefault();
        var option = event.target.closest(".subject-autocomplete-item");
        if (! option) {
            return;
        }
        var index = Number(option.getAttribute("data-index"));
        if (! isNaN(index) && items[index]) {
            applySelection(items[index]);
        }
    });

    document.addEventListener("mousedown", function (event) {
        var clickInsideInput = activeInput && activeInput.contains(event.target);
        var clickInsideList  = list.contains(event.target);
        if (! clickInsideInput && ! clickInsideList) {
            closeList();
        }
    });

    window.addEventListener("resize", function () {
        if (isOpen) {
            positionList();
        }
    });
}

function startSubjectAutocomplete()
{
    if (typeof initSubjectAutocomplete === "function") {
        initSubjectAutocomplete();
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", startSubjectAutocomplete);
} else {
    startSubjectAutocomplete();
}
