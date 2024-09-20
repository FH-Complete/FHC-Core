import TemplateFrame from "./template/frame.js";

export default {
	components: {
		TemplateFrame
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object
	},
	computed: {
		equipment() {
			if (!this.res.equipment)
				return "";
			return this.res.equipment.replace(new RegExp('<br />', 'ig'), '');
		},
		address() {
			let address = this.res.zip || '';
			if (this.res.city)
				address += (address ? ' ' : '') + this.res.city;
			if (this.res.street)
				address += (address ? ', ' : '') + this.res.street;
			if (this.res.floor)
				address += (address ? ' / ' : '') + this.res.floor + ' Stockwerk';

			return address || 'N/A';
		}
	},
	template: `
	<template-frame
		class="searchbar-result-room"
		:res="res"
		:actions="actions"
		:title="res.ort_kurzbz"
		image-fallback="fas fa-door-open fa-4x p-4 text-white bg-primary"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Standort</div>
				<div class="searchbar_tablecell">
					{{ address }}
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Sitzplätze</div>
				<div class="searchbar_tablecell">
					<template v-if="res.max_person !== null && res.workplaces !== null">
						{{ res.max_person }}, davon {{ res.workplaces }} PC-Plätze
					</template>
					<template v-else>
						N/A
					</template>
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Gebäude</div>
				<div class="searchbar_tablecell">
					{{ res.building }}
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">Zusatz Informationen</div>
				<div class="searchbar_tablecell">
					<div class="no-margin-paragraphs" v-html="equipment"></div>
				</div>
			</div>
		</div>
	</template-frame>`
};