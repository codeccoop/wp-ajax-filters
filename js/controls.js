const noop = () => {};

function bindControl({ state, control, onChange = noop }) {
  const input = control.querySelector("input");

  let _value = "";
  Object.defineProperty(state, input.name, {
    get: () => _value,
    set: (to) => {
      if (String(to) == _value) return;
      _value = to;
      onChange();
    },
  });

  input.addEventListener("input", (ev) => (state[input.name] = ev.target.value));
  return jQuery(input);
}

function bindHidden({ state, control }) {
  const input = control.querySelector("input");
  Object.defineProperty(state, input.name, {
    value: input.value,
    enumerable: false,
    writable: false,
    configurable: false,
  });

  return jQuery(input);
}

function bindSubmit({ state, control, onClick = noop }) {
  const input = control.querySelector("input");
  input.addEventListener("click", () => onClick(state));
  return jQuery(input);
}

function bindMultiSelect({ state, control, onChange = noop }) {
  const label = control.querySelector("label");
  const select = control.querySelector("select");

  let _value = [];
  Object.defineProperty(state, select.name, {
    get() {
      return _value;
    },
    set(to) {
      if (to === _value) return;
      _value = to;
      onChange();
    },
  });

  const multiSelect = MultiSelect({
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

  return multiSelect;
}

function MultiSelect({ el, placeholder, onChange, ...settings }) {
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

export default {
  bindMultiSelect,
  bindControl,
  bindHidden,
  bindSubmit,
};
