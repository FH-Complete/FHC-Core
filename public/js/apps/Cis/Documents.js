import Phrasen from '../../plugin/Phrasen.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";

const app = Vue.createApp({
	name: 'DocumentsApp',
	data() {
		return {
			inscriptiontable: null,
			inscriptiontableFilters: {},
			studienerfolgsbestaetigungtable: null,
			studienerfolgsbestaetigungtableFilters: {},
			abschlussdokumentetable: null
		};
	},
	computed: {
		inscriptiontableFilter() {
			const filter = [];
			for (var k in this.inscriptiontableFilters)
				if (this.inscriptiontableFilters[k])
					filter.push({
						field: k,
						type: '=',
						value: this.inscriptiontableFilters[k]
				});
			return filter;
		},
		inscriptiontableEmpty() {
			// NOTE(chris): empty result on filter
			if (this.inscriptiontableFilters.Stsem)
				return this.$p.t('tools', 'studienbeitragFuerSSNochNichtBezahlt', {stsem: this.inscriptiontableFilters.Stsem});
			if (this.inscriptiontableFilters.Stg)
				return this.$p.t('tools', 'studienbeitragFuerStgNochNichtBezahlt', {stsem: this.inscriptiontableFilters.Stg});
			
			return this.$p.t('tools', 'studienbeitragNochNichtBezahlt');
		},
		studienerfolgsbestaetigungtableFilter() {
			const filter = [];
			for (var k in this.studienerfolgsbestaetigungtableFilters)
				if (this.studienerfolgsbestaetigungtableFilters[k])
					filter.push({
						field: k,
						type: '=',
						value: this.studienerfolgsbestaetigungtableFilters[k]
				});
			return filter;
		}
	},
	methods: {
		changeFilter(table, field, evt) {
			this[table + 'Filters'][field] = evt.target.value;
			this[table].clearFilter();
			if (this[table + 'Filter'].length)
				this[table].setFilter(this[table + 'Filter']);
		}
	},
	mounted() {
		this.inscriptiontable = new Tabulator(this.$refs.inscriptiontable, {
			layout: 'fitDataStretch',
			placeholder: this.$p.t('tools', 'studienbeitragNochNichtBezahlt')
		});
		this.studienerfolgsbestaetigungtable = new Tabulator(this.$refs.studienerfolgsbestaetigungtable, {
			layout: 'fitDataStretch'
		});
		this.abschlussdokumentetable = new Tabulator(this.$refs.abschlussdokumentetable, {
			layout: 'fitDataStretch',
			placeholder: this.$p.t('tools', 'nochKeineAbschlussdokumenteVorhanden')
		});

		// NOTE(chris): empty result on filter
		const div = Vue.h(
			'div',
			{
				class: 'position-absolute top-0 left-0 w-100 h-100 d-flex justify-content-center align-items-center fw-bold text-muted'
			},
			[
				this.inscriptiontableEmpty
			]
		);
		this.inscriptiontable.on('dataSorted', (sorters, rows) => {
			if (!rows.length) {
				div.children = [this.inscriptiontableEmpty];
				Vue.render(div, this.inscriptiontable.element.querySelector('.tabulator-tableholder'));
			} else {
				Vue.render(null, this.inscriptiontable.element.querySelector('.tabulator-tableholder'));
			}
		});
	}
});

setScrollbarWidth();

app.use(Phrasen, {reload: true});
app.mount('#content');
