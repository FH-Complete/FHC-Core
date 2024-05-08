export default {
	render() {
		return (this.$slots && this.$slots.default) ? this.$slots.default() : null;
	}
};