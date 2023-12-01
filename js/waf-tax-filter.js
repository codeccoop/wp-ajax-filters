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
      selectAll: false,
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
    this.pager = new Pager(this.render.bind(this));
    this.state = Array.from(this.el.querySelectorAll(".waf-filter-field")).reduce(
      (state, field) => this.bindFieldToState(state, field),
      {}
    );

    this.render();
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
    query.append("per_page", this.pager.perPage);
    if (query.get("per_page") > 0) {
      query.append("offset", this.pager.current * this.pager.perPage);
    }

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
    }).then((res) => {
      return res.text().then((html) => ({ pages: res.headers.get("Waf-Pages"), html }));
    });
  };

  FilterForm.prototype.render = function (page = 0) {
    this.pager.current = page;
    const query = this.serializeState();
    this.fetch(query).then(({ pages, html }) => {
      this.pager.pages = pages;
      this.pager.render();
      const selector = this.el.getAttribute("aria-controls");
      const container = document.querySelector(selector);
      this.el.dispatchEvent(
        new CustomEvent("waf:fetch", {
          detail: {
            page: this.pager.current,
            pages,
            html,
            el: container,
          },
        })
      );
      document.querySelector(selector).innerHTML = html;
      this.el.dispatchEvent(
        new CustomEvent("waf:render", {
          detail: {
            page: this.pager.current,
            pages,
            html,
            el: container,
          },
        })
      );
    });
  };

  function Pager(onChange) {
    this.el = document.querySelector(".waf-filter-pager");
    this.perPage = Number(this.el.dataset.perpage);
    this.onChange = onChange || (() => {});

    let _current = 0;
    Object.defineProperty(this, "current", {
      get: () => _current,
      set: (val) => {
        if (val === _current) return;
        _current = Math.max(0, Math.min(this.pages - 1, val));
        onChange(_current);
      },
    });

    let _pages = 0;
    Object.defineProperty(this, "pages", {
      get: () => _pages,
      set: (val) => {
        if (val == _pages) return;
        _pages = Number(val);
      },
    });
  }

  Pager.prototype.getVisible = function () {
    const vector = Array.apply(null, Array(3));
    let visible;
    if (this.current <= 1) {
      visible = vector.map((_, i) => i);
    } else if (this.current === this.pages - 1) {
      visible = vector.map((_, i) => this.pages - (3 - i));
      console.log(visible);
    } else {
      visible = Array.apply(null, Array(3)).map((_, i) => this.current - 1 + i);
    }

    return visible.filter((p) => p <= this.pages - 1);
  };

  Pager.prototype.render = function () {
    // if (this.pages <= 1) return;
    const nav = document.createElement("nav");
    nav.classList.add("waf-pager");
    const pageList = this.getVisible().reduce((list, p) => {
      const page = document.createElement("li");
      page.classList.add("waf-page");
      if (p === this.current) page.classList.add("current");
      page.textContent = p + 1;
      page.addEventListener("click", () => (this.current = p));
      list.appendChild(page);
      return list;
    }, document.createElement("ul"));
    nav.appendChild(pageList);
    if (this.pages > 3) this.addArrows(nav);
    this.el.innerHTML = "";
    this.el.appendChild(nav);
  };

  Pager.prototype.addArrows = function (nav) {
    nav.prepend(this._addArrow(-1, this.current === 0));
    nav.append(this._addArrow(1, this.current === this.pages - 1));
  };

  Pager.prototype._addArrow = function (mutation, disabled) {
    const arrow = document.createElement("span");
    arrow.classList.add("waf-arrow");
    arrow.textContent = mutation === 1 ? ">" : "<";

    if (!disabled) arrow.addEventListener("click", () => (this.current += mutation));
    else arrow.classList.add("disabled");

    return arrow;
  };

  const form = new FilterForm();
});
