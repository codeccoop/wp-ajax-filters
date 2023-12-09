function Pager({ onChange }) {
  this.el = document.querySelector(".waf-pager");

  let _attached = Boolean(this.el);
  let _current = _attached ? 0 : null;
  Object.defineProperty(this, "current", {
    get: () => _current,
    set: (val) => {
      if (val === _current || _current === null) return;
      _current = Math.max(0, Math.min(this.pages - 1, val));
      onChange(_current);
    },
  });

  let _pages = _attached ? 0 : null;
  Object.defineProperty(this, "pages", {
    get: () => _pages,
    set: (val) => {
      if (val == _pages || _pages === null) return;
      _pages = Number(val);
    },
  });

  this.perPage = _attached ? Number(this.el.dataset.perpage) : null;
  this.onChange = onChange || (() => {});
}

Pager.prototype.getVisible = function () {
  const vector = Array.apply(null, Array(3));
  let visible;
  if (this.current <= 1) {
    visible = vector.map((_, i) => i);
  } else if (this.current === this.pages - 1) {
    visible = vector.map((_, i) => this.pages - (3 - i));
  } else {
    visible = Array.apply(null, Array(3)).map((_, i) => this.current - 1 + i);
  }

  visible = visible.filter((p) => p <= this.pages - 1);
  if (this.current > 2) visible = [0, -1].concat(visible);
  if (this.current < this.pages - 2) visible = visible.concat([-1, this.pages - 1]);
  return visible;
};

Pager.prototype.render = function () {
  if (this.current === null) return;
  const nav = document.createElement("nav");
  nav.classList.add("waf-nav-pager");
  const pageList = this.getVisible().reduce((list, p) => {
    const page = document.createElement("li");
    if (p === -1) {
      page.classList.add("waf-ellipsis");
      page.textContent = "...";
    } else {
      page.classList.add("waf-page");
      page.textContent = p + 1;
      if (p === this.current) page.classList.add("current");
      page.addEventListener("click", () => (this.current = p));
    }
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

export default Pager;
