window.addEventListener("DOMContentLoaded", function () {
	
	document.querySelectorAll(wp_ajax.selector).forEach((select) => {
		jQuery( select ).multipleSelect({
			selectAll: true,
			displayTitle: true,
			minimumCountSelected: 1,
			filter: true,
			filterPlaceholder: `escribe la categoría`,
			filterAcceptOnEnter: true,
			showClear: true,
			animate: 'slide',
			placeholder: 'selecciona',

			onClick: onSelectionChange,
			onCheckAll: () => setTimeout(onSelectionChange, 0),
			onUncheckAll: () => setTimeout(onSelectionChange, 0),
			formatSelectAll: function () {
    			return 'Seleccionar todo';
  			},
			formatAllSelected: function () {
    			return 'Todas';
  			},
			formatCountSelected: function (count, total) {
    			return count + ' de ' + total + ' seleccionadas';
  			},
			
			onAfterCreate: () => {
				onSelectionChange();
				hideDropOnStart();
			}
		});
 
	});
	

	function hideDropOnStart(){
		const msDrop = document.getElementsByClassName("ms-drop");
		Array.from(msDrop).map((div) => {
			div.style.display="none";
		})
	}
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
			//console.log("Un canvi");
			document.querySelector(".ajax_mn_content").innerHTML = html;
		});
	}
	
});
