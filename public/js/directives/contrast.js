const rgbToHex = (value) => {
	const hex = value.toString(16);
	return hex.length === 1 ? '0' + hex : hex;
};

function getContrastYIQ(hexcolor) {
	
	var r = parseInt(hexcolor.substring(1, 3), 16);
	var g = parseInt(hexcolor.substring(3, 5), 16);
	var b = parseInt(hexcolor.substring(5, 7), 16);
	var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
	return (yiq >= 128) ? 'black' : 'white';
}

export default {
	mounted(element, binding){
		const bgColor = window.getComputedStyle(element).backgroundColor;
		let rgbRegEx = new RegExp(/^rgba?\s*\(([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*,?\s*([0-9]+.?[0-9]?)?\s*\)/);
		let isRgb = bgColor.match(rgbRegEx);
		if (isRgb) {
			if (isRgb.length < 3) {
				console.error("Invalid RGB color format");
			}
			let r = parseInt(isRgb[1], 10);
			let g = parseInt(isRgb[2], 10);
			let b = parseInt(isRgb[3], 10);
			let hexColor = `#${rgbToHex(r)}${rgbToHex(g)}${rgbToHex(b)}`;
			element.style.color = getContrastYIQ(hexColor);
		}
		else
		{
			element.style.color = getContrastYIQ(bgColor);
		}
	},
}