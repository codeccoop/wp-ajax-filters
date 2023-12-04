function Pager(form, onChange) {
  this.el = form.querySelector(".waf-pager");
  this.perPage = Number(this.el.dataset.perpage);
  console.log(this.el);
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
  } else {
    visible = Array.apply(null, Array(3)).map((_, i) => this.current - 1 + i);
  }

  return visible.filter((p) => p <= this.pages - 1);
};

Pager.prototype.render = function () {
  // if (this.pages <= 1) return;
  const nav = document.createElement("nav");
  nav.classList.add("waf-nav-pager");
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

export default Pager;
