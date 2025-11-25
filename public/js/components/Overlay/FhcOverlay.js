export const FhcOverlay = {
	name: 'FhcOverlay',
	props: {
		active: {
			type: Boolean,
			default: false
		}
	},
	template: `	
		<div v-show="active"
			style="
				position: fixed;
				inset: 0;
				display: flex;
				align-items: center;
				justify-content: center;
				background: rgba(255,255,255,0.5);
				z-index: 99999999999;
				pointer-events: none;
			">
			<i class="fa-solid fa-spinner fa-pulse fa-5x"></i>
		</div>
	`
};
export default FhcOverlay;