import listsZeitsperren from './Details/listsZeitsperren.js';
import MitarbeiterZeitsperren from './Details/ZeitsperrenMitarbeiteruid.js';
export const DEFAULT_INTERVAL_DAYS = 14;

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
					key: 'stg',
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
		route(view) {
			this.activeView = this.activeView === view ? null : view;
			this.$router.push({
				name: 'ZeitsperrenMa',
				params: {
					type: this.activeView,
					days: DEFAULT_INTERVAL_DAYS
				}
			});
		},
	},
	template: `
		<div class="base-zeitsperrenma">
			<div class="row g-1 flex-shrink-0">
				<div
					class="col"
					v-for="button in buttons"
					:key="button.key"
				>
					<button
						class="btn w-100"
						:class="button.class"
						@click="route(button.key)"
					>
						{{ $p.t(button.title) }}
					</button>
				</div>
			</div>

			<div class="zeitsperren-list-container">
				<lists-zeitsperren
					:type="propsViewData.type"
					:id="propsViewData.id"
					:tage="propsViewData.days"
				/>
			</div>
	</div>
	`,
}