function loadCss(path) {
	if (!document.head) {
		return new Promise((resolve, reject) => {
			window.addEventListener('load', () => loadCss(path).then(resolve).catch(reject));
		});
	}
	if (path.match(/^\/[^\/]/))
		path = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/\/$/, "") + path;
	
	if (document.querySelector('link[href="' + path + '"]'))
		return Promise.resolve();
	
	return new Promise((resolve, reject) => {
		const link = document.createElement('link');
        link.rel = 'stylesheet';
		link.type = 'text/css';

        link.onload = resolve;
        link.onerror = reject;

		link.href = path;

        document.head.append(link);
	});
}


export {
	loadCss,
};

export default {
	loadCss,
};
