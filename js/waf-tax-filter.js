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
        if (ev.selected) state[field] = state[field].concat([ev.value]);
        else state[field] = state[field].filter((val) => val !== ev.value);
      },
    });

    this.selectors.push(multiSelect);
  }

  function _multiSelect({ el, placeholder, onChange, ...settings }) {
    return jQuery(el).multipleSelect({
      selectAll: true,
      displayTitle: true,
      minimumCountSelected: 1,
      showClear: true,
      animate: "slide",
      placeholder: placeholder,
      onClick: (ev) => onChange(el.name, ev),
      onCheckAll: (ev) => setTimeout(() => onChange(el.name, ev), 0),
      onUncheckAll: (ev) => setTimeout(() => onChange(el.name, ev), 0),
      ...settings,
    });
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
      const selector = this.el.getAttribute("for");
      document.querySelector(selector).innerHTML = html;
    });
  };

  const form = new FilterForm();
  form.render();

  //const searchField = document.getElementsByClassName("search-field")[0];
  //const searchSubmit = document.getElementsByClassName("search-submit")[0];

  //document.querySelectorAll(wp_ajax.selector).forEach((select) => {
  //  jQuery(select).multipleSelect({
  //    selectAll: true,
  //    displayTitle: true,
  //    minimumCountSelected: 1,
  //    filter: true,
  //    filterPlaceholder: `escribe la categoría`,
  //    filterAcceptOnEnter: true,
  //    showClear: true,
  //    animate: "slide",
  //    placeholder: "selecciona",
  //    //openOnHover: true,

  //    onClick: onSelectionChange,
  //    onCheckAll: () => setTimeout(onSelectionChange, 0),
  //    onUncheckAll: () => setTimeout(onSelectionChange, 0),
  //    formatSelectAll: function () {
  //      return "Seleccionar todo";
  //    },
  //    // formatAllSelected: function () {
  //    // 	return 'Todas';
  //    // },
  //    formatCountSelected: function (count, total) {
  //      return count + " de " + total + " seleccionadas";
  //    },

  //    onAfterCreate: () => {
  //      onSelectionChange();
  //      hideDropOnStart();
  //    },
  //  });
  //});

  //searchSubmit.addEventListener("click", onSelectionChange);

  //function hideDropOnStart() {
  //  const msDrop = document.getElementsByClassName("ms-drop");
  //  Array.from(msDrop).map((div) => {
  //    div.style.display = "none";
  //  });
  //}

  //function getSelection() {
  //  return Object.fromEntries(
  //    Array.from(document.querySelectorAll(wp_ajax.selector)).map((select) => {
  //      return [
  //        select.id,
  //        Array.from(select.children)
  //          .filter((opt) => opt.selected)
  //          .map((opt) => opt.value),
  //      ];
  //    })
  //  );
  //}

  //function onSelectionChange() {
  //  const query = new URLSearchParams();
  //  query.append("action", "filter");
  //  query.append("nonce", wp_ajax.nonce);

  //  const selection = getSelection();
  //  console.log(selection);
  //  Object.keys(selection).forEach((key) => query.append(key, selection[key]));
  //  if (searchField.value !== "") {
  //    query.append("search-field", searchField.value);
  //  }
  //  const url = `${wp_ajax.url}?${query.toString()}`;
  //  console.log(url);
  //  fetch(url, {
  //    method: "GET",
  //    headers: {
  //      Accept: "application/json",
  //    },
  //    cache: "no-cache",
  //  })
  //    .then((res) => res.text())
  //    .then((html) => {
  //      //console.log("Un canvi");
  //      document.querySelector(".ajax_mn_content").innerHTML = html;
  //    });
  //}
});
