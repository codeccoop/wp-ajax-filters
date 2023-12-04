import Controls from "./controls.js";
import Pager from "./pager.js";

function WafForm({ sel, url, action, nonce }) {
  this.action = action;
  this.nonce = nonce;
  this.url = url;

  this.el = document.querySelector(sel);
  this.submit = null;
  this.hidden = [];
  this.controls = [];

  this.pager = new Pager(this.el, this.render.bind(this));
  this.state = Array.from(this.el.querySelectorAll(".waf-control")).reduce(
    (state, control) => this.bindControlToState(state, control),
    {}
  );
}

WafForm.prototype.serializeState = function () {
  const query = new URLSearchParams();
  query.append("action", this.action);
  query.append("nonce", this.nonce);
  if (this.pager.perPage) {
    query.append("per_page", this.pager.perPage);
    query.append("offset", this.pager.current * this.pager.perPage);
  }

  const sval = (val) => {
    if (Array.isArray(val)) return val.join(",");
    else return String(val);
  };

  this.hidden.concat(this.controls).forEach((el) => {
    query.append(el.attr("name"), sval(this.state[el.attr("name")]));
  });

  return query.toString();
};

WafForm.prototype.fetch = function (query) {
  const url = `${this.url}?${query}`;
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

WafForm.prototype.render = function (page = 0) {
  this.pager.current = page;
  const query = this.serializeState();
  this.fetch(query).then(({ pages, html }) => {
    this.pager.pages = pages;
    this.pager.render();
    const selector = this.el.getAttribute("aria-controls");
    const container = document.querySelector(selector);
    const args = {
      page: this.pager.current,
      pages,
      html,
      el: container,
    };
    this.el.dispatchEvent(new CustomEvent("waf:fetch", { detail: args }));
    container.innerHTML = args.html;
    this.el.dispatchEvent(new CustomEvent("waf:render", { detail: args }));
  });
};

WafForm.prototype.bindControlToState = function (state, control) {
  switch (control.dataset.type) {
    case "hidden":
      this.hidden.push(Controls.bindHidden({ state, control }));
      break;
    case "submit":
      this.submit = Controls.bindSubmit({
        state,
        control,
        onClick: (ev) => {
          this.render();
        },
      });
      break;
    case "select":
      this.controls.push(
        Controls.bindMultiSelect({ state, control, onChange: () => this.render() })
      );
      break;
    default:
      this.controls.push(Controls.bindControl({ state, control }));
      break;
  }

  return state;
};

export default WafForm;
