import WafForm from "./form.js";

window.addEventListener("DOMContentLoaded", function () {
  const form = new WafForm({
    sel: ".waf-filter-form",
    action: "waf_tax_filter",
    ..._wafTaxFilterSafeguard,
  });
  form.render();
});
