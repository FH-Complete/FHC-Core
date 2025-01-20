// Create a temporary div element, set styles to ensure it's scrollable and off-screen, get scrollbar width from that
function getScrollbarWidth() {
	const div = document.createElement('div');

	div.style.position = 'absolute';
	div.style.top = '-9999px';
	div.style.width = '100px';
	div.style.height = '100px';
	div.style.overflow = 'scroll';

	document.body.appendChild(div);
	const scrollbarWidth = div.offsetWidth - div.clientWidth;
	document.body.removeChild(div);

	return scrollbarWidth;
}

// Detect the browser and set a CSS variable for the scrollbar width since chrome scrollbars mess with 100vw/vh css
export function setScrollbarWidth() {
	const isChromium = /Chrome/.test(navigator.userAgent);
	const isFirefox = /Firefox/.test(navigator.userAgent);

	if (isChromium) {
		const width = getScrollbarWidth() + 'px';
		document.body.style.setProperty('--scrollbar-width', width); // Set the value for Chrome
	} else if (isFirefox) {
		document.body.style.setProperty('--scrollbar-width', '0px'); // Set the value for Firefox or adjust as needed
	}
}