const uuid41 = () => {
	const b = crypto.getRandomValues(new Uint16Array(8));
	const d = [].map.call(b, a => a.toString(16).padStart(4, '0')).join('');
	const vr = (((b[5] >> 12) & 3) | 8).toString(16);
	return `${d.substr(0, 8)}-${d.substr(8, 4)}-4${d.substr(13, 3)}-${vr}${d.substr(17, 3)}-${d.substr(20, 12)}`;
};

export default uuid41;