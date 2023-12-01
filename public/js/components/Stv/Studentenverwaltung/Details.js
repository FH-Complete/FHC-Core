import FhcTabs from "../../Tabs.js";

export default {
	components: {
		FhcTabs
	},
	props: {
		students: Array
	},
	template: `
	<div class="stv-details h-100 pb-3 d-flex flex-column">
		<div v-if="!students?.length" class="justify-content-center d-flex h-100 align-items-center">
			Bitte StudentIn auswählen!
		</div>
		<template v-else>
			<fhc-tabs v-if="students.length == 1" :modelValue="students[0]" config-url="/components/stv/config/student" :default="$route.params.tab"></fhc-tabs>
			<fhc-tabs v-else :modelValue="students" config-url="/components/stv/config/students" :default="$route.params.tab"></fhc-tabs>
		</template>
	</div>`
};