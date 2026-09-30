export const debounce = (callback, wait) => {
	let timeoutId = null;
	return (context) => {
		window.clearTimeout(timeoutId);
		timeoutId = window.setTimeout(() => callback(), wait);
	}
}