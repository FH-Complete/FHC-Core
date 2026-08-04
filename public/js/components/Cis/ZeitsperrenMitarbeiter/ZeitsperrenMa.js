import listsZeitsperren from './Details/listsZeitsperren.js';
import MitarbeiterZeitsperren from './Details/ZeitsperrenMitarbeiteruid.js';

export default {
	name: 'ZeitsperrenMa',
	components: {
		listsZeitsperren,
		MitarbeiterZeitsperren
	},
	props: {
		propsViewData: Object
	},
	data(){
		return {
			activeView: null,
			buttons: [
				{
					key: 'all',
					title: 'zeitsperren/btn_all_current',
					class: 'btn-primary'
				},
				{
					key: 'fix',
					title: 'zeitsperren/btn_all_fix',
					class: 'btn-secondary'
				},
				{
					key: 'lector',
					title: 'zeitsperren/btn_fix_lect',
					class: 'btn-secondary'
				},
				{
					key: 'oe',
					title: 'zeitsperren/btn_oes',
					class: 'btn-secondary'
				},
				{
					key: 'ass',
					title: 'zeitsperren/btn_all_ass',
					class: 'btn-secondary'
				},
				{
					key: 'lecStg',
					title: 'zeitsperren/btn_lect_stg',
					class: 'btn-secondary'
				}
			],
		}
	},
	methods: {
		show(view) {
			this.activeView = this.activeView === view ? null : view;
		},
	},
	//TODO(only show main page if there is no prop
	template: `
		<div class="base-zeitsperren w-100 h-100">

			<template v-if="!propsViewData.maUid && !propsViewData.type">

				<div class="row g-1">
					<div
						class="col"
						v-for="button in buttons"
						:key="button.key"
					>
						<button
							class="btn w-100"
							:class="button.class"
							@click="show(button.key)"
						>
							{{ $p.t(button.title) }}
						</button>
					</div>
				</div>

				<lists-zeitsperren
					v-if="activeView"
					ref="listTimeLocks"
					:type="activeView"
				/>

		</template>
		<template v-else>

			<lists-zeitsperren
						:type="propsViewData.type"
						:maUid="propsViewData.maUid"
						:tage="propsViewData.days"
					/>
		</template>

	</div>

	`,
}