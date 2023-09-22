window.addEventListener("DOMContentLoaded", function () {
  function _bindHiddenField(state, field) {
    const input = field.querySelector("input");
    Object.defineProperty(state, input.name, {
      value: input.value,
      enumerable: false,
      writable: false,
      configurable: false,
    });
    state[input.name] = input.value;

    this.hidden.push(jQuery(input));
  }

  function _bindMultiSelect(state, field) {
    const self = this;
    const label = field.querySelector("label");
    const select = field.querySelector("select");

    let _value = [];
    Object.defineProperty(state, select.name, {
      get() {
        return _value;
      },
      set(to) {
        if (to === _value) return;
        _value = to;
        self.render();
      },
    });

    const multiSelect = _multiSelect({
      el: select,
      placeholder: label.textContent,
      onChange: (field, ev) => {
        if (ev.isMulti) {
          if (ev.selected) state[field] = ev.value;
          else state[field] = [];
        } else {
          if (ev.selected) state[field] = state[field].concat([ev.value]);
          else state[field] = state[field].filter((val) => val !== ev.value);
        }
      },
    });

    this.selectors.push(multiSelect);
  }

  function _multiSelect({ el, placeholder, onChange, ...settings }) {
    return jQuery(el).multipleSelect({
      selectAll: true,
      classes: "waf-multi-select",
      classPrefix: "waf",
      minimumCountSelected: 1,
      showClear: true,
      animate: "slide",
      placeholder: placeholder,
      onClick: (ev) => onChange(el.name, ev),
      onCheckAll: () => onChange(el.name, new MultiEvent(el.children, true)),
      onUncheckAll: () => onChange(el.name, new MultiEvent(el.children, false)),
      ...settings,
    });
  }

  function MultiEvent(options, selected) {
    const event = { isMulti: true, selected };
    if (selected) event.value = Array.from(options).map((opt) => opt.value);
    else event.value = [];
    return event;
  }

  function FilterForm() {
    this.hidden = [];
    this.selectors = [];
    this.el = document.querySelector(".waf-filter-form");
    this.state = Array.from(this.el.querySelectorAll(".waf-filter-field")).reduce(
      (state, field) => this.bindFieldToState(state, field),
      {}
    );
  }

  FilterForm.prototype.bindFieldToState = function (state, field) {
    switch (field.dataset.type) {
      case "hidden":
        _bindHiddenField.call(this, state, field);
        break;
      default:
        _bindMultiSelect.call(this, state, field);
        break;
    }

    return state;
  };

  FilterForm.prototype.serializeState = function () {
    const query = new URLSearchParams();
    query.append("action", "waf_tax_filter");
    query.append("nonce", _wafTaxSearchSafeguard.nonce);

    const sval = (val) => {
      if (Array.isArray(val)) {
        return val.join(",");
      } else {
        return String(val);
      }
    };

    this.hidden.forEach((el) => {
      query.append(el.attr("name"), sval(this.state[el.attr("name")]));
    });

    this.selectors.forEach((sel) =>
      query.append(sel.attr("name"), sval(this.state[sel.attr("name")]))
    );

    return query.toString();
  };

  FilterForm.prototype.fetch = function (query) {
    const url = `${_wafTaxSearchSafeguard.url}?${query}`;
    return fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
      cache: "no-cache",
    }).then((res) => res.text());
  };

  FilterForm.prototype.render = function () {
    const query = this.serializeState();
    this.fetch(query).then((html) => {
      const selector = this.el.getAttribute("aria-controls");
      const container = document.querySelector(selector);
      this.el.dispatchEvent(
        new CustomEvent("waf:fetch", {
          detail: {
            content: html,
            el: container,
          },
        })
      );
      document.querySelector(selector).innerHTML = html;
      this.el.dispatchEvent(
        new CustomEvent("waf:render", {
          detail: {
            content: html,
            el: container,
          },
        })
      );
    });
  };

  const form = new FilterForm();
  form.render();
});
