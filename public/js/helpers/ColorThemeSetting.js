let theme = localStorage.getItem("theme");
if (theme == 'dark') {
	document.body.setAttribute("data-bs-theme", theme); 
	document.documentElement.classList.add('dark');
}