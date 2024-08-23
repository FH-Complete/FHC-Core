import PruefungList from "./Pruefung/Pruefunglist.js";

export default {
	components: {
		PruefungList
	},
	props: {
		modelValue: Object,
		config: Object
	},
	data() {
		return {
			pruefungen: []
		}
	},
	template: `
	<div class="stv-details-pruefung h-100 pb-3">
	
	{{modelValue}}
		<fieldset class="overflow-hidden">
<!--			<legend>{{this.$p.t('lehre', 'pruefung')}}</legend>-->
			<pruefung-list ref="pruefungList" :uid="modelValue.uid"></pruefung-list>
		</fieldset>
	</div>`
};