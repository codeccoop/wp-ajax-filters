window.addEventListener("DOMContentLoaded", function () {
	
	document.querySelectorAll(wp_ajax.selector).forEach((select) => {
		jQuery( select ).multipleSelect({
			onClick: onSelectionChange,
			onCheckAll: () => setTimeout(onSelectionChange, 0)
		});
 
	});
	

	function getSelection() {
		return Object.fromEntries(
			Array.from(document.querySelectorAll(wp_ajax.selector)).map((select) => {
				return [select.id, Array.from(select.children).filter(opt => opt.selected).map(opt => opt.value)];
			})
		);
	}

	function onSelectionChange() {
		const query = new URLSearchParams();
		query.append("action", "filter");
		query.append("nonce", wp_ajax.nonce);

		const selection = getSelection();
		console.log(selection);
		Object.keys(selection).forEach((key) =>
			query.append(key, selection[key])
		);

		const url = `${wp_ajax.url}?${query.toString()}`;
		console.log(url);
		fetch(url, {
			method: "GET",
			headers: {
				Accept: "application/json",
			},
			cache: "no-cache",
		}).then((res) => res.text())
		.then((html) => {
			debugger;
			//console.log("Un canvi");
			document.querySelector(".ajax_mn_content").innerText = html;
		});
	}
	
});
