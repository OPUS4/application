/**
 * Combobox without jQuery UI.
 *
 * Replaces the former jQuery UI widget behavior for <select class="combobox">.
 */
(function () {
    "use strict";

    function escapeRegex(text)
    {
        return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    function Combobox(selectElement)
    {
        this.select  = selectElement;
        this.wrapper = null;
        this.input   = null;
        this.toggle  = null;
        this.menu    = null;

        this.items              = [];
        this.activeIndex        = -1;
        this.isOpen             = false;
        this.wasOpenOnMouseDown = false;

        this.boundHandleDocumentMouseDown = this.handleDocumentMouseDown.bind(this);
        this.boundHandleWindowResize      = this.handleWindowResize.bind(this);

        this.build();
    }

    Combobox.prototype.build = function () {
        var select         = this.select;
        var selectedOption = select.options[select.selectedIndex];
        var value          = selectedOption ? selectedOption.text : "";

        this.wrapper           = document.createElement("span");
        this.wrapper.className = "custom-combobox";

        select.insertAdjacentElement("afterend", this.wrapper);
        select.style.display = "none";

        this.input              = document.createElement("input");
        this.input.type         = "text";
        this.input.value        = value || "";
        this.input.title        = "";
        this.input.name         = select.name || "";
        this.input.id           = select.id || "";
        this.input.autocomplete = "off";
        this.input.className    = "custom-combobox-input";

        this.toggle           = document.createElement("button");
        this.toggle.type      = "button";
        this.toggle.tabIndex  = -1;
        this.toggle.title     = "Show All Items";
        this.toggle.className = "custom-combobox-toggle";
        this.toggle.innerHTML = "<span class=\"custom-combobox-icon\" aria-hidden=\"true\"></span>";

        this.menu                 = document.createElement("ul");
        this.menu.className       = "custom-combobox-menu";
        this.menu.style.position  = "absolute";
        this.menu.style.display   = "none";
        this.menu.style.zIndex    = "10000";
        this.menu.style.margin    = "0";
        this.menu.style.padding   = "0";
        this.menu.style.listStyle = "none";

        this.wrapper.appendChild(this.input);
        this.wrapper.appendChild(this.toggle);
        document.body.appendChild(this.menu);

        this.updateMultipleOptionsState();
        this.bindEvents();
    };

    Combobox.prototype.bindEvents = function () {
        var self = this;

        this.input.addEventListener("input", function () {
            self.open(self.input.value || "");
        });

        this.input.addEventListener("keydown", function (event) {
            if (! self.isOpen && event.key === "ArrowDown") {
                event.preventDefault();
                self.open("");
                return;
            }

            if (! self.isOpen) {
                return;
            }

            if (event.key === "ArrowDown") {
                event.preventDefault();
                self.moveActive(1);
            } else if (event.key === "ArrowUp") {
                event.preventDefault();
                self.moveActive(-1);
            } else if (event.key === "Enter") {
                if (self.activeIndex >= 0 && self.items[self.activeIndex]) {
                    event.preventDefault();
                    self.applySelection(self.items[self.activeIndex]);
                }
            } else if (event.key === "Escape") {
                event.preventDefault();
                self.close();
            }
        });

        this.input.addEventListener("blur", function () {
            window.setTimeout(function () {
                var activeElement = document.activeElement;
                if (self.wrapper.contains(activeElement) || self.menu.contains(activeElement)) {
                    return;
                }
                self.close();
            }, 120);
        });

        this.toggle.addEventListener("mousedown", function (event) {
            // Keep focus on input so blur timeout does not immediately close the menu.
            event.preventDefault();
            self.wasOpenOnMouseDown = self.isOpen;
        });

        this.toggle.addEventListener("click", function () {
            self.input.focus();

            if (self.wasOpenOnMouseDown) {
                self.close();
                return;
            }

            self.open("");
        });

        this.menu.addEventListener("mousedown", function (event) {
            event.preventDefault();

            var item = event.target.closest("li[data-index]");
            if (! item) {
                return;
            }

            var index = Number(item.getAttribute("data-index"));
            if (! isNaN(index) && self.items[index]) {
                self.applySelection(self.items[index]);
            }
        });

        this.menu.addEventListener("mousemove", function (event) {
            var item = event.target.closest("li[data-index]");
            if (! item) {
                return;
            }

            var index = Number(item.getAttribute("data-index"));
            if (! isNaN(index)) {
                self.setActiveIndex(index);
            }
        });

        this.select.addEventListener("change", function () {
            var selectedOption = self.select.options[self.select.selectedIndex];
            self.input.value   = selectedOption ? selectedOption.text : "";
        });

        document.addEventListener("mousedown", this.boundHandleDocumentMouseDown);
        window.addEventListener("resize", this.boundHandleWindowResize);
    };

    Combobox.prototype.handleDocumentMouseDown = function (event) {
        if (this.wrapper.contains(event.target) || this.menu.contains(event.target)) {
            return;
        }

        this.close();
    };

    Combobox.prototype.handleWindowResize = function () {
        if (this.isOpen) {
            this.positionMenu();
        }
    };

    Combobox.prototype.getOptionItems = function () {
        var result  = [];
        var options = this.select.options;

        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            if (! option || ! option.value) {
                continue;
            }

            result.push({
                label: option.text,
                value: option.text,
                option: option
            });
        }

        return result;
    };

    Combobox.prototype.updateMultipleOptionsState = function () {
        var options = this.select.options;
        var count   = 0;

        for (var i = 0; i < options.length; i++) {
            if (options[i] && options[i].value) {
                count++;
            }
        }

        var hasMultiple = count > 1;
        this.wrapper.classList.toggle("combobox-has-multiple", hasMultiple);
        this.input.classList.toggle("combobox-has-multiple", hasMultiple);
    };

    Combobox.prototype.filterItems = function (term) {
        var allItems = this.getOptionItems();
        if (! term) {
            return allItems;
        }

        var matcher = new RegExp(escapeRegex(term), "i");
        return allItems.filter(function (item) {
            return matcher.test(item.label);
        });
    };

    Combobox.prototype.open = function (term) {
        this.items       = this.filterItems(term);
        this.activeIndex = -1;
        this.renderMenu();

        if (! this.items.length) {
            this.close();
            return;
        }

        this.positionMenu();
        this.menu.style.display = "block";
        this.isOpen             = true;
    };

    Combobox.prototype.close = function () {
        this.menu.style.display = "none";
        this.menu.innerHTML     = "";
        this.items              = [];
        this.activeIndex        = -1;
        this.isOpen             = false;
    };

    Combobox.prototype.positionMenu = function () {
        var rect              = this.input.getBoundingClientRect();
        this.menu.style.left  = (window.scrollX + rect.left) + "px";
        this.menu.style.top   = (window.scrollY + rect.bottom) + "px";
        this.menu.style.width = rect.width + "px";
    };

    Combobox.prototype.renderMenu = function () {
        this.menu.innerHTML = "";

        for (var i = 0; i < this.items.length; i++) {
            var item     = this.items[i];
            var li       = document.createElement("li");
            li.className = "custom-combobox-option";
            li.setAttribute("data-index", String(i));
            li.setAttribute("role", "option");

            var wrapper         = document.createElement("div");
            wrapper.className   = "custom-combobox-option-label";
            wrapper.textContent = item.label;

            li.appendChild(wrapper);
            this.menu.appendChild(li);
        }
    };

    Combobox.prototype.moveActive = function (delta) {
        if (! this.items.length) {
            return;
        }

        var next = this.activeIndex + delta;
        if (next < 0) {
            next = this.items.length - 1;
        }
        if (next >= this.items.length) {
            next = 0;
        }

        this.setActiveIndex(next);
    };

    Combobox.prototype.setActiveIndex = function (index) {
        this.activeIndex = index;

        var wrappers = this.menu.querySelectorAll(".custom-combobox-option-label");
        for (var i = 0; i < wrappers.length; i++) {
            var isActive = i === this.activeIndex;
            wrappers[i].classList.toggle("is-active", isActive);
        }

        if (this.activeIndex < 0 || this.activeIndex >= wrappers.length) {
            return;
        }

        var activeItem = wrappers[this.activeIndex];
        var itemTop    = activeItem.offsetTop;
        var itemBottom = itemTop + activeItem.offsetHeight;
        var viewTop    = this.menu.scrollTop;
        var viewBottom = viewTop + this.menu.clientHeight;

        // Keep keyboard/mouse navigation inside the dropdown scroll area
        // without scrolling the whole page.
        if (itemTop < viewTop) {
            this.menu.scrollTop = itemTop;
        } else if (itemBottom > viewBottom) {
            this.menu.scrollTop = itemBottom - this.menu.clientHeight;
        }
    };

    Combobox.prototype.applySelection = function (item) {
        this.input.value     = item.value;
        this.select.value    = item.option.value;
        item.option.selected = true;

        this.select.dispatchEvent(new Event("change", { bubbles: true }));
        this.close();
    };

    Combobox.prototype.destroy = function () {
        document.removeEventListener("mousedown", this.boundHandleDocumentMouseDown);
        window.removeEventListener("resize", this.boundHandleWindowResize);

        if (this.menu && this.menu.parentNode) {
            this.menu.parentNode.removeChild(this.menu);
        }

        if (this.wrapper && this.wrapper.parentNode) {
            this.wrapper.parentNode.removeChild(this.wrapper);
        }

        this.select.style.display = "";
    };

    function initComboboxes(root)
    {
        var scope   = root || document;
        var selects = scope.querySelectorAll("select.combobox");

        for (var i = 0; i < selects.length; i++) {
            var select = selects[i];
            if (select.dataset.comboboxBound === "1") {
                continue;
            }

            select.dataset.comboboxBound = "1";
            new Combobox(select);
        }
    }

    function startComboboxes()
    {
        initComboboxes(document);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", startComboboxes);
    } else {
        startComboboxes();
    }
})();
