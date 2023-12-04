import WafForm from "./form.js";

window.addEventListener("DOMContentLoaded", function () {
  const form = new WafForm({
    sel: ".waf-search-form",
    action: "waf_search",
    ..._wafSearchSafeguard,
  });
  form.controls.forEach((ctl) => {
    ctl.on("keydown", ({ originalEvent: ev }) => {
      if (ev.keyCode !== 13) return;
      form.submit.trigger("click");
    });
  });
});
