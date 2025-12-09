import FilterItem from './Filter/Item.js';

import ApiStvApp from '../../../../api/factory/stv/app.js';

export default {
	name: "ListPrestudentsFilter",
	components: {
		FilterItem
	},
	emits: [
		'change'
	],
	data() {
		return {
			filters: [],
			filterConfig: [// TODO(chris): get from BE!
				{
					name: 'stv/konto_filter_count_0',
					type: 'konto',
					fixed: {
						missing: true,
						usestdsem: true
					},
					dynamic: {
						buchungstyp_kurzbz: {
							type: 'select',
							values: {
								test1: 'Test1',
								test2: 'Test2'
							}
						}
					}
				},
				{
					name: 'stv/konto_filter_missing_counter',
					type: 'konto_counter',
					dynamic: {
						buchungstyp_kurzbz: {
							type: 'select',
							values: {
								test1: 'Test1',
								test2: 'Test2'
							}
						},
						samestg: {
							type: 'bool',
							label: 'stv/konto',
							default: true
						}
					}
				}
			]
		}
	},
	computed: {
		cleanFilters() {
			return this.filters.filter(filter => {
				if (!filter.type)
					return false;
				if (Object.values(filter).some(v => v === undefined))
					return false;
				return true;
			});
		}
	},
	watch: {
		cleanFilters(n) {
			this.$emit('change', n);
		}
	},
	methods: {
		add() {
			this.filters.push({});
		},
		remove(index) {
			this.filters.splice(index, 1);
		},
		clearFilters() {
			this.filters = [];
		}
	},
	created() {
		this.$api
			.call(ApiStvApp.configFilter())
			.then(result => {
				this.filterConfig = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
	<div class="stv-list-filter h-100">
		<button class="btn btn-outline-dark" type="button" @click="add">
			<span class="fa-solid fa-plus" aria-hidden="true"></span>
			{{ $p.t('filter/filter') }}
		</button>
		<filter-item
			v-for="(filter, i) in filters"
			:key="i"
			v-model="filters[i]"
			:filter-config="filterConfig"
			class="mt-3"
			@remove="remove(i)"
		/>
	</div>`
};
