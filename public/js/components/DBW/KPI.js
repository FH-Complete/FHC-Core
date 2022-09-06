export default {
	props: [
		"configMode",
		"config"
	],
	methods: {
		changeConfig() {
			this.config.display = parseInt(this.$refs.display.value);
			this.$emit('config', this.config);
		}
	},
	template: `<div class="dbw-kpi">
		<div>KPI Widget</div>
		<div v-if="configMode">
			<select ref="display" class="form-control">
				<option value="0" :selected="config.display == 0">H1</option>
				<option value="1" :selected="config.display == 1">H2</option>
				<option value="2" :selected="config.display == 2">H3</option>
			</select>
			<button class="btn btn-default" @click="changeConfig">Save</button>
		</div>
		<template v-else>
			<span v-for="val in config.data" :class="'h' + (1 + parseInt(config.display))">{{val}}</span>
		</template>
	</div>`
}
