import {CoreFilterCmpt} from '../../components/filter/Filter.js';
import CoreFormInput from "../../components/Form/Input.js";

// Fields used to restructure table data for dataTree
const idField = 'lehrveranstaltung_id';
const parentIdField = 'lehrveranstaltung_template_id';
const STUDIENSEMESTER_DROPDOWN_STARTDATE = '2011-01-01';

export default {
	components: {
		CoreFilterCmpt,
		CoreFormInput
	},
	data: function() {
		return {
			table: null,
			studiensemester: [],
			selectedStudiensemester: '',
			cbDataTreeStartExpanded: false	// checkbox expand dataTree or not
		}
	},
	computed: {
		tabulatorOptions() {
                        const fhcValuesLookup = function(cell) {
                            var values = {};
                            const field = cell.getField();
                            const data = cell.getTable().getData();
                            const collectvalues = function(rows, field) {
                                var values = {};
                                var childvalues = {};
                                for(const row of rows) {
                                    const rowvalue = (row[field] !== null) ? row[field] : '';
                                    values[rowvalue] = rowvalue;
                                    if(row['_children'] && row['_children'].length > 0) {
                                      childvalues = collectvalues(row['_children'], field);
                                      values = {...values, ...childvalues}
                                    }
                                }
                                return values;
                            }
                            values = collectvalues(data, field);
                            const vals = Object.keys(values).sort();
                            if(vals.indexOf('') === -1) {
                                vals.unshift('');
                            }
                            return vals;
                        };
                        const fhctreefilter = function(headerValue, rowValue, rowData, filterParams){
                            if (rowData['_children'] && rowData['_children'].length > 0) {
                               for (var i in rowData['_children']) {
                                  return rowValue == headerValue || 
                                            fhctreefilter(
                                                headerValue, 
                                                rowData['_children'][i][filterParams.field], 
                                                rowData['_children'][i], 
                                                filterParams
                                            );
                               }
                            }

                            return rowValue == headerValue;
                        };
			const self = this;
			return {
				// NOTE: data is set on table built to await preselected actual Studiensemester
				ajaxResponse(url, params, response) {
					return self.prepDataTreeData(response.data) // Prepare data for dataTree view
				},
				layout: 'fitColumns',
				autoResize: false, // prevent auto resizing of table
				resizableColumnFit: true, //maintain the fit of columns when resizing
				index: 'lehrveranstaltung_id',
				selectable: true,
				selectableRangeMode: 'click',
				dataTree: true,
				dataTreeStartExpanded: self.cbDataTreeStartExpanded,
				dataTreeChildIndent: 15, //indent child rows by 15 px
				persistence:{
					filter: false, //persist filter sorting
				},
				columns: [
					{title: 'LV-ID', field: 'lehrveranstaltung_id', headerFilter: true, visible: false},
					{title: 'LV Kurzbz', field: 'kurzbz', headerFilter: true, visible:false, width: 70},
					{title: 'STG Kurzbz', field: 'stg_typ_kurzbz', headerFilter: "list", headerFilterParams: {valuesLookup: fhcValuesLookup}, headerFilterFunc: fhctreefilter, headerFilterFuncParams: {field: 'stg_typ_kurzbz'}, visible:true, width: 80},
					{title: 'OrgEinheit', field: 'lv_oe_bezeichnung', headerFilter: true, visible: false, width: 250},
					{title: 'Lehrtyp Kurzbz', field: 'lehrtyp_kurzbz', headerFilter: true, visible:false, width: 70},
					{title: 'Studiengangtyp', field: 'stg_typ_bezeichnung', headerFilter: "list", headerFilterParams: {valuesLookup: fhcValuesLookup}, headerFilterFunc: fhctreefilter, headerFilterFuncParams: {field: 'stg_typ_bezeichnung'}, width: 150},
					{title: 'OrgForm', field: 'orgform_kurzbz', headerFilter: "list", headerFilterParams: {valuesLookup: fhcValuesLookup}, headerFilterFunc: fhctreefilter, headerFilterFuncParams: {field: 'orgform_kurzbz'}, width: 70},
					{title: 'Semester', field: 'semester', headerFilter: true, width: 50},
					{title: 'Lehrveranstaltung', field: 'lv_bezeichnung', headerFilter: true, minWidth: 250},
					{title: 'Lehrveranstaltung ENG', field: 'bezeichnung_english', headerFilter: true, minWidth: 250},
					{title: 'ECTS', field: 'ects', headerFilter: true, width: 50, hozAlign: 'right'},
					{title: 'Lehrform', field: 'lehrform_kurzbz', headerFilter: true, width: 50},
					{title: 'Sprache', field: 'sprache', headerFilter: true, width: 100},
					{title: 'Aktiv', field: 'aktiv', width: 50,
						formatter:"tickCross",
						headerFilter:"tickCross",
						headerFilterParams:{"tristate": true},
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title: 'Studienplan', field: 'studienplan_bezeichnung', headerFilter: true, visible:true, width: 220},
					{title: 'OE Kurzbz', field: 'lv_oe_kurzbz', headerFilter: true, visible:false, minWidth: 80},
					{
						title: this.$p.t('global/aktionen'),
						field: 'actions',
						width: 140,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary';
							button.innerHTML = '<i class="fa fa-external-link"></i> ' + this.$p.t('global/verwalten');
							button.addEventListener('click', (event) => this.openAdminLvTemplate(event, cell.getRow()));
							container.append(button);

							return container;
						},
						frozen: true
					}
				]
			}
		},
		urlToAdminAllTemplates() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root +
				'vilesci/lehre/lehrveranstaltung.php?stg_kz=99999&semester=-1&orgform=-1';
		}
	},
	methods: {
		async loadAndSetStudiensemester(){
			const result = await this.$fhcApi
				.get('api/frontend/v1/organisation/Studiensemester/getAll', {start: STUDIENSEMESTER_DROPDOWN_STARTDATE})
				.then(result => this.studiensemester = result.data )
				.then(() => this.$fhcApi.get('api/frontend/v1/organisation/Studiensemester/getAktNext') ) // Get actual Studiensemester
				.then(result =>  this.selectedStudiensemester = result.data[0].studiensemester_kurzbz ) // Preselect Studiensemester
				.catch(error => this.$fhcAlert.handleSystemError(error) );
		},
		async onTableBuilt(){

			this.table = this.$refs.lvTemplateUebersichtTable.tabulator;

			// Await Studiensemester
			await this.loadAndSetStudiensemester();

			// Set table data
			this.table.setData(
				this.$fhcApi.getUri() +
				'/api/frontend/v1/education/Lehrveranstaltung/getTemplateLvTree' +
				'?studiensemester_kurzbz=' + this.selectedStudiensemester
			);

			// Await phrases categories
			await this.$p.loadCategory(['lehre']);

			// Replace column titles with phrasen
			this.table.updateColumnDefinition('lv_bezeichnung', {title: this.$p.t('lehre', 'lehrveranstaltung')});

		},
		onChangeStudiensemester(){
			// Reset table data
			this.table.setData(
				this.$fhcApi.getUri() +
				'/api/frontend/v1/education/Lehrveranstaltung/getTemplateLvTree' +
				'?studiensemester_kurzbz=' + this.selectedStudiensemester
			);
		},
		openAdminLvTemplate(event, row){
			const url = FHC_JS_DATA_STORAGE_OBJECT.app_root +
				'vilesci/lehre/lehrveranstaltung.php?stg_kz=&semester=-1&orgform=-1&lehrveranstaltung_id=' +
				row.getData().lehrveranstaltung_id;

			window.open(url, '_blank').focus();
		},
		prepDataTreeData(data){
			let toDelete = [];

			// loop through all data
			for (let childIdx = 0; childIdx < data.length; childIdx++)
			{
				let child = data[childIdx];

				// if it has parent id, it is a child
				if (child[parentIdField])
				{
					// append the child on the right place. If parent found, mark original sw child on 0 level for deleting
					if (this._appendChild(data, child)) toDelete.push(childIdx);
				}
			}

			// delete the marked children from 0 level
			for (let counter = 0; counter < toDelete.length; counter++)
			{
				// decrease index by counter as index of data array changes after every deletion
				data.splice(toDelete[counter] - counter, 1);
			}

			return data;
		},
		_appendChild(data, child) {
			// get parent id
			let parentId = child[parentIdField];

			// loop thorugh all data
			for (let parentIdx = 0; parentIdx < data.length; parentIdx++)
			{
				let parent = data[parentIdx];

				// if it's the parent
				if (parent[idField] == parentId)
				{
					// create children array if not done yet
					if (!parent._children) parent._children = [];

					// if child is not included in children array, append the child
					if (!parent._children.includes(child)) parent._children.push(child);

					// parent found
					return true;
				}
				// search children for parents
				else if (parent._children) this._appendChild(parent._children, child);
			}

			// parent not found
			return false;
		},
		reloadTabulator() {
			if (this.table !== null && this.table !== undefined)
			{
				for (let option in this.tabulatorOptions)
				{
					if (this.table.options.hasOwnProperty(option))
						this.table.options[option] = this.tabulatorOptions[option];
				}
				this.$refs.lvTemplateUebersichtTable.reloadTable();
			}
		},
	},
	template: `
<div class="lvTemplateUebersicht overflow-hidden">
	<div class="row d-flex mb-3">
		<div class="col-10 h2 mb-4">{{ $p.t('lehre/lvTemplatesUebersicht') }}</div>
		<div class="col-2 ms-auto align-self-end">
			<core-form-input
				type="select"
				v-model="selectedStudiensemester"
				name="studiensemester"
				@change="onChangeStudiensemester">
				<option 
				v-for="(studSem, index) in studiensemester"
				:key="index" 
				:value="studSem.studiensemester_kurzbz">
					{{studSem.studiensemester_kurzbz}}
				</option>
			</core-form-input>
		</div>
	</div>
	<div class="row mb-5">
		<div class="col">
			<core-filter-cmpt
				ref="lvTemplateUebersichtTable"
				uniqueId="lvTemplateUebersichtTable"
				table-only
				:side-menu="false"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="[{event: 'tableBuilt', handler: onTableBuilt}]">
				<template v-slot:actions>
					<a type="button" class="btn btn-primary" :href="urlToAdminAllTemplates" target="_blank"><i class="fa fa-external-link me-2"></i>{{ $p.t('lehre/lvTemplatesVerwalten') }}</a>
					<div class="form-check form-check-inline">
						<input
							class="form-check-input"
							type="checkbox"
							v-model="cbDataTreeStartExpanded"
							:checked="cbDataTreeStartExpanded"
							@change="reloadTabulator">
						<label class="form-check-label">Templates {{ $p.t('global/aufgeklappt') }}</label>
					</div>
				</template>
			</core-filter-cmpt>						
		</div>
	</div>

</div>
`
};
