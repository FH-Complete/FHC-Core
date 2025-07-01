import TemplateFrame from "./template/frame.js";

export default {
	components: {
		TemplateFrame
	},
	emits: [ 'actionexecuted' ],
	props: {
		mode: String,
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
				address += (address ? ' / ' : '') + this.$p.t('search/result_address_floor', this.res);

			return address || this.$p.t('search/result_address_none');
		}
	},
	template: `
	<template-frame
		class="searchbar-result-room"
		:res="res"
		:actions="actions"
		:title="res.ort_kurzbz"
		image-fallback="fas fa-door-open fa-4x"
		@actionexecuted="$emit('actionexecuted')"
		>
		<div class="searchbar_table">
			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_room_address') }}</div>
				<div class="searchbar_tablecell">
					{{ address }}
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_workplaces') }}</div>
				<div v-if="mode == 'simple'" class="searchbar_tablecell">
					{{ res.sitzplaetze }}
				</div>
				<div v-else class="searchbar_tablecell">
					<template v-if="res.max_person !== null && res.workplaces !== null">
						{{ $p.t('search/result_workplaces_pc', res) }}
					</template>
					<template v-else>
						{{ $p.t('search/result_workplaces_none') }}
					</template>
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_building') }}</div>
				<div class="searchbar_tablecell">
					{{ res.building }}
				</div>
			</div>

			<div class="searchbar_tablerow">
				<div class="searchbar_tablecell">{{ $p.t('search/result_equipment') }}</div>
				<div class="searchbar_tablecell">
					<div v-if="mode == 'simple'" class="no-margin-paragraphs" v-html="res.austattung.replace('<br />','')"></div>
					<div v-else class="no-margin-paragraphs" v-html="equipment"></div>
				</div>
			</div>
		</div>
	</template-frame>`
};