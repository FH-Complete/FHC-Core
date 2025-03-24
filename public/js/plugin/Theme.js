const theme_name= FHC_JS_DATA_STORAGE_OBJECT.theme.name;
const theme_modes = FHC_JS_DATA_STORAGE_OBJECT.theme.modes;

const toggleTheme = (theme)=>{
	if (!theme) return;

	for (const theme of theme_modes) {
		document.documentElement.classList.remove(theme);
	}
	document.documentElement.classList.add(theme);

	document.body.setAttribute("data-bs-theme", theme);

	let stylesheet = document.querySelector('link[href*="primevue/resources/themes"]');
	if (theme == "dark") {
		stylesheet.attributes.href.value = stylesheet.attributes.href.value.replace("bootstrap4-light-blue", "bootstrap4-dark-blue");
	} else if (theme == "light") {
		stylesheet.attributes.href.value = stylesheet.attributes.href.value.replace("bootstrap4-dark-blue", "bootstrap4-light-blue");
	}
	else{
		if (stylesheet.attributes.href.value.includes("bootstrap4-dark-blue"))
			stylesheet.attributes.href.value = stylesheet.attributes.href.value.replace("bootstrap4-dark-blue", "bootstrap4-light-blue");
	}

	localStorage.setItem("theme", theme);
}

const initializeTheme = ()=>{
	
	let theme = localStorage.getItem("theme");
	if (!theme || !theme_modes.includes(theme)) {
		// set the first theme mode as default
		theme = theme_modes[0];
		localStorage.setItem("theme",theme);
	}
	toggleTheme(theme);
}


export default {
	install: (app,options)=>{
		
		document.documentElement.classList.add(theme_name);

		initializeTheme();

		app.config.globalProperties.$theme = {
			theme_name,
			theme_modes,
			switchTheme: (theme) => {
				toggleTheme(theme);
			}, 
		} 
	}
}