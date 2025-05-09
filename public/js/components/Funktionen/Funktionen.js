import { Modal } from './Modal.js';
import { ModalDialog } from './ModalDialog.js';
import { Toast } from './Toast.js';
import {OrgChooser} from "./OrgChooser.js";
import { usePhrasen } from '../../mixins/Phrasen.js';

import ApiFunktion from '../../api/factory/funktionen/person.js';
import ApiPerson from "../../../extensions/FHC-Core-Personalverwaltung/js/api/factory/person.js";

export const Funktionen = {
	name: 'FunctionComponent',
	components: {
		Modal,
		ModalDialog,
		Toast,
		OrgChooser,
		"datepicker": VueDatePicker
	},
	props: {
		modelValue: { type: Object, default: () => ({}), required: false},
		config: { type: Object, default: () => ({}), required: false},
		readonlyMode: { type: Boolean, required: false, default: false },
		personID: { type: Number, required: false },
		personUID: { type: String, required: false },
		writePermission: { type: Boolean, required: false },
		showDvCompany: { tpye: Boolean, required: false, default: true }
	},
	emits: ['updateHeader'],
	setup( props, { emit } ) {

		const $api = Vue.inject('$api');
		//const fhcAlert = Vue.inject('$fhcAlert');


		const readonly = Vue.ref(false);

		const { t } = usePhrasen();

		//const { personID: currentPersonID , personUID: currentPersonUID  } = Vue.toRefs(props);
		const currentPersonID = Vue.computed(() => { return props.personID });
		const currentPersonUID = Vue.computed(() => { return props.personUID });

		const dialogRef = Vue.ref();

		const isFetching = Vue.ref(false);

		const theModel = Vue.computed({
			get: () => props.modelValue,
			set: (value) => emit('update:modelValue', value),
		});

		const unternehmen = Vue.ref();
		const jobfunctionList = Vue.ref([]);
		const jobfunctionDefList = Vue.ref([]);
		const orgUnitList = Vue.ref([{value: '', label: '-'}]);

		const aktivChecked = Vue.ref(true);

		const table = Vue.ref(null); // reference to your table element
		const tabulator = Vue.ref(null); // variable to hold your table
		const tableData = Vue.reactive([]); // data for table to display


		const full = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
		const route = ( props.readonlyMode !== true ) ? VueRouter.useRoute() : null;

		const convertArrayToObject = (array, key) => {
			const initialValue = {};
			return array.reduce((obj, item) => {
				return {
					...obj,
					[item[key]]: item,
				};
			}, initialValue);
		};

		const fetchData = async () => {
			if (currentPersonID.value==null && theModel.value.personID==null) {
				jobfunctionList.value = [];
				return;
			}
			isFetching.value = true

			// fetch data and map them for easier access
			try {
				const response = await $api.call(
					ApiFunktion.getAllUserFunctions(theModel.value.personUID || currentPersonUID.value));
				if(response.error === 1) {
					let rawList = [];
					tableData.value = [];
					jobfunctionList.value = convertArrayToObject(rawList, 'benutzerfunktion_id');
				} else {
					let rawList = response.retval;
					tableData.value = response.retval;
					jobfunctionList.value = convertArrayToObject(rawList, 'benutzerfunktion_id');
				}
			} catch (error) {
				console.log(error)
			} finally {
				isFetching.value = false
			}

		}

		const createShape = () => {
			return {
				benutzerfunktion_id: 0,
				uid: theModel.value.personUID || currentPersonUID.value,
				oe_kurzbz: "",
				funktion_kurzbz: "",
				datum_von: "",
				datum_bis: "",
				bezeichnung: "",
				wochenstunden: 0,
			}
		}

		const currentValue = Vue.ref(createShape());
		const preservedValue = Vue.ref(createShape());

		Vue.watch([currentPersonID, currentPersonUID], ([id,uid]) => {
			fetchData();
		});

		Vue.watch(unternehmen, (unternehmen_kurzbz) => {
			fetchOrgUnits(unternehmen_kurzbz);
			fetchFunctions();
		});

		const toggleMode = async () => {
			if (!readonly.value) {
				// cancel changes?
				if (hasChanged.value) {
					const ok = await dialogRef.value.show();
					if (ok) {
						console.log("ok=", ok);
						currentValue.value = preservedValue.value;
					} else {
						return
					}
				}
			} else {
				// switch to edit mode and preserve data
				preservedValue.value = {...currentValue.value};
			}
			readonly.value = !readonly.value;
		}

		Vue.onMounted(async () => {
			currentValue.value = createShape();
			await fetchData();

			const dateFormatter = (cell) => {
				return cell.getValue()?.replace(/(.*)-(.*)-(.*)/, '$3.$2.$1');
			}

			const dvFormatter = (cell) => {
				if( props.readonlyMode === true ) {
					return (cell.getValue() != null) ? cell.getValue() : '';
				}
				const url = fullPath + route.params.id + '/' + route.params.uid + '/contract/' + cell.getRow().getData().dienstverhaeltnis_id;
				return cell.getValue() != null ? `<a href="${url}">` + cell.getValue() + '</a>' : '';
			}

			// helper
			const createDomButton = (classValue, clickHandler) => {
				const nodeBtn = document.createElement("button");
				const classAttrBtn = document.createAttribute("class");
				classAttrBtn.value = "btn btn-outline-secondary btn-sm";
				nodeBtn.setAttributeNode(classAttrBtn);
				nodeBtn.addEventListener("click", clickHandler);
				const nodeI = document.createElement("i");
				const classAttrI = document.createAttribute("class");
				classAttrI.value = classValue;
				nodeI.setAttributeNode(classAttrI);
				nodeBtn.appendChild(nodeI);
				return nodeBtn;
			}

			const btnFormatter = (cell) => {

				const nodeDiv = document.createElement("div");
				const classAttrDiv = document.createAttribute("class");
				classAttrDiv.value = "d-grid gap-2 d-md-flex justify-content-end align-middle";
				nodeDiv.setAttributeNode(classAttrDiv);
				// delete button
				const nodeBtnDel = createDomButton("fa fa-xmark",() => { showDeleteModal(cell.getValue()) })
				// edit button
				const nodeBtnEdit = createDomButton("fa fa-pen",() => { showEditModal(cell.getValue()) })

				if( cell.getRow().getData().dienstverhaeltnis_unternehmen === null ) {
					nodeDiv.appendChild(nodeBtnEdit);
					nodeDiv.appendChild(nodeBtnDel);
				}

				return nodeDiv;
			}


			const columnsDef = [
				{ title: t('person','dv_unternehmen'), field: "dienstverhaeltnis_unternehmen", formatter: dvFormatter, sorter:"string", headerFilter:"list", headerFilterParams: {valuesLookup:true, autocomplete:true, sort:"asc"}, visible: props.showDvCompany },
				{ title: t('person','zuordnung_taetigkeit'), field: "funktion_beschreibung", hozAlign: "left", headerFilter:"list", headerFilterParams: {valuesLookup:true, autocomplete:true, sort:"asc"} },
				{ title: t('lehre','organisationseinheit'), field: "funktion_oebezeichnung", headerFilter:"list", headerFilterParams: {valuesLookup:true, autocomplete:true, sort:"asc"} },
				{ title: t('person','wochenstunden'), field: "wochenstunden", hozAlign: "right", width: 140, headerFilter:true },
				{ title: t('ui','from'), field: "datum_von", hozAlign: "center", formatter: dateFormatter, width: 140, sorter:"string", headerFilter:true, headerFilterFunc:customHeaderFilter },
				{ title: t('global','bis'), field: "datum_bis", hozAlign: "center", formatter: dateFormatter, width: 140, sorter:"string", headerFilter:true, headerFilterFunc:customHeaderFilter },
				{ title: t('ui','bezeichnung'), field: "bezeichnung", hozAlign: "left", headerFilter:"list", headerFilterParams: {valuesLookup:true, autocomplete:true, sort:"asc"} }
			];

			if( props.readonlyMode === false) {
				columnsDef.push({ title: "", field: "benutzerfunktion_id", formatter: btnFormatter, hozAlign: "right", width: 100, headerSort: false, frozen: true });
			}

			let tabulatorOptions = {
				height: "100%",
				layout: "fitColumns",
				movableColumns: true,
				reactiveData: true,
				columns: columnsDef,
				//data: tableData.value,
				data: jobfunctionListArray.value,
			};

			tabulator.value = new Tabulator(
				table.value,
				tabulatorOptions
			);

			function customHeaderFilter(headerValue, rowValue, rowData, filterParams){
				//headerValue - the value of the header filter element
				//rowValue - the value of the column in this row
				//rowData - the data for the row being filtered
				//filterParams - params object passed to the headerFilterFuncParams property

				const validDate = function(d){
					return d instanceof Date && isFinite(d);
				}

				const date1 = new Date(rowValue);
				date1.setHours(0,0,0,0);
				let [day, month, year] = headerValue.split('.')
				if (year < 1000) return true;  // prevents dates like 17.5.2
				const date2 = new Date(+year, +month - 1, +day);

				return  !(validDate(date2)) || ((date2 - date1) == 0); //must return a boolean, true if it passes the filter.
			}

			tabulator.value.on('tableBuilt', () => {
				//tabulator.value.setData(tableData.value);

			})

		})

		const jobfunctionListArray = Vue.computed(() => {
			let temp = (jobfunctionList.value ? Object.values(jobfunctionList.value) : []);
			let filtered = temp.filter((e) => ( !aktivChecked.value || (aktivChecked.value && e.aktiv) ));
			return filtered;
		});

		// Workaround to update tabulator
		Vue.watch(jobfunctionListArray, (newVal, oldVal) => {
			console.log('jobfunctionList changed');
			tabulator.value?.setData(jobfunctionListArray.value);
		}, {deep: true})

		// Modal
		const modalRef = Vue.ref();
		const confirmDeleteRef = Vue.ref();

		const showAddModal = () => {
			currentValue.value = createShape();
			// reset form state
			frmState.orgetBlurred=false;
			frmState.funktionBlurred=false;
			frmState.beginnBlurred=false;
			// call bootstrap show function
			modalRef.value.show();
		}

		const hideModal = () => {
			modalRef.value.hide();
		}

		const showEditModal = async (id) => {
			currentValue.value = { ...jobfunctionList.value[id] };
			// fetch company
			isFetching.value = true;
			try {
				const res = await $api.call(ApiFunktion.getCompanyByOrget(currentValue.value.oe_kurzbz));
				if (res.error == 0) {
					unternehmen.value = res.retval[0].oe_kurzbz;
				} else {
					console.log('company not found for orget!');
				}

			} catch (error) {
				console.log(error)
			} finally {
				isFetching.value = false
			}
			//delete currentValue.value.bezeichnung;
			modalRef.value.show();
		}

		const showDeleteModal = async (id) => {
			currentValue.value = { ...jobfunctionList.value[id] };
			const ok = await confirmDeleteRef.value.show();

			if (ok) {

				try {
					const res = await $api.call(ApiPerson.deletePersonJobFunction(id));
					if (res.error == 0) {
						delete jobfunctionList.value[id];
						showDeletedToast();
						theModel.value.updateHeader();
					}
				} catch (error) {
					console.log(error)
				} finally {
					isFetching.value = false
				}

			}
		}


		const okHandler = async () => {
			if (!validate()) {

				console.log("form invalid");

			} else {

				// submit
				try {
					let payload = {...currentValue.value};
					delete payload.dienstverhaeltnis_id;
					delete payload.dienstverhaeltnis_unternehmen;
					delete payload.fachbereich_bezeichnung;
					delete payload.funktion_oebezeichnung;
					delete payload.aktiv;
					delete payload.funktion_beschreibung;
					const r = await $api.call(ApiPerson.upsertPersonJobFunction(payload));
					if (r.error == 0) {
						// fetch all data because of all the references in the changed record
						await fetchData();
						console.log('job function successfully saved');
						theModel.value.updateHeader();
						showToast();
					}
				} catch (error) {
					console.log(error)
				} finally {
					isFetching.value = false
				}

				hideModal();
			}
		}

		// -------------
		// form handling
		// -------------

		const jobFunctionFrm = Vue.ref();

		const frmState = Vue.reactive({ beginnBlurred: false, orgetBlurred: false, funktionBlurred: false, wasValidated: false });

		const notEmpty = (n) => {
			return !!n && n.trim() != "";
		}

		const validate = () => {
			frmState.orgetBlurred = true;
			frmState.funktionBlurred = true;
			frmState.beginnBlurred = true;
			return notEmpty(currentValue.value.datum_von) &&
				notEmpty(currentValue.value.oe_kurzbz) &&
				notEmpty(currentValue.value.funktion_kurzbz);
		}


		const hasChanged = Vue.computed(() => {
			return Object.keys(currentValue.value).some(field => currentValue.value[field] !== preservedValue.value[field])
		});

		const formatDate = (d) => {
			if (d != null && d != '') {
				return d.substring(8, 10) + "." + d.substring(5, 7) + "." + d.substring(0, 4);
			} else {
				return ''
			}
		}

		// Toast
		const toastRef = Vue.ref();
		const deleteToastRef = Vue.ref();

		const showToast = () => {
			toastRef.value.show();
		}

		const showDeletedToast = () => {
			deleteToastRef.value.show();
		}

		const fetchOrgUnits = async (unternehmen_kurzbz) => {
			if( unternehmen_kurzbz === '' ) {
				return;
			}
			const response = await $api.call(
				ApiFunktion.getOrgetsForCompany(unternehmen_kurzbz));
			const orgets = response.retval;
			orgets.unshift({
				value: '',
				label: t('ui','bitteWaehlen'),
			});
			orgUnitList.value = await  orgets;
		}

		const fetchFunctions = async (uid, unternehmen_kurzbz) => {
			if(unternehmen_kurzbz === '' || uid === '' ) {
				return;
			}
			const response = await $api.call(ApiFunktion.getAllFunctions());
			const benutzerfunktionen = response.retval;
			benutzerfunktionen.unshift({
				value: '',
				label: t('ui','bitteWaehlen'),
			});
			jobfunctionDefList.value = benutzerfunktionen;
		}

		const unternehmenSelectedHandler = (e) => {
			console.log('unternehmen selected: ',e);
			unternehmen.value = e;
		}

		const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
		const fullPath = `/${ciPath}/extensions/FHC-Core-Personalverwaltung/Employees/`;

		return {
			jobfunctionList, orgUnitList, jobfunctionListArray,
			jobfunctionDefList,
			currentValue,
			readonly,
			frmState,
			dialogRef,
			toastRef, deleteToastRef,
			jobFunctionFrm,
			modalRef,
			fullPath,
			route,
			aktivChecked,
			unternehmen,
			tabulator,
			table,

			toggleMode, formatDate, notEmpty,
			showToast, showDeletedToast,
			showAddModal, hideModal, okHandler,
			showDeleteModal, showEditModal, confirmDeleteRef, t, unternehmenSelectedHandler,
		}
	},
	template: `
    <div class="row" v-if="readonlyMode === false">

        <div class="toast-container position-absolute top-0 end-0 pt-4 pe-2">
          <Toast ref="toastRef">
            <template #body><h4>{{ t('person','funktionGespeichert') }}</h4></template>
          </Toast>
        </div>

        <div class="toast-container position-absolute top-0 end-0 pt-4 pe-2">
            <Toast ref="deleteToastRef">
                <template #body><h4>{{ t('person', 'funktionGeloescht') }}</h4></template>
            </Toast>
        </div>
    </div>
    <div class="row pt-md-4">      
         <div class="col">
             <div class="card">
                <div class="card-header">
                    <div class="h5"><h5>{{ t('person','funktionen') }}</h5></div>        
                </div>

                <div class="card-body">
                    <div class="d-grid d-md-flex justify-content-between pt-2 pb-3" v-if="readonlyMode === false">
                        <button type="button" class="btn btn-sm btn-primary me-3" @click="showAddModal()">
                            <i class="fa fa-plus"></i> {{ t('person','funktion') }}
                        </button>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" role="switch" id="aktivChecked" v-model="aktivChecked">
                            <label class="form-check-label" for="aktivChecked">Nur aktive anzeigen</label>
                        </div>
                    </div>

                    <!-- TABULATOR -->
                    <div ref="table" class="fhc-tabulator"></div>
                
                </div>
             </div>
         </div>
    </div>
            

    <!-- detail modal -->
    <Modal :title="t('person','funktion')" ref="modalRef" v-if="readonlyMode === false">
        <template #body>
            <form class="row g-3" ref="jobFunctionFrm">
                            
                <div class="col-md-8">
					<template v-if="showDvCompany">
						<label class="form-label">{{ t('core','unternehmen') }}</label><br>
						<org-chooser  @org-selected="unternehmenSelectedHandler" :oe="unternehmen" class="form-select form-select-sm"></org-chooser>
					</template>
                </div>
                <div class="col-md-4">
                </div>
                <!--  -->
                <div class="col-md-8">
                    <label class="required form-label">{{ t('lehre','organisationseinheit') }}</label><br>
                    <select id="oe_kurzbz" v-model="currentValue.oe_kurzbz" 
                        @blur="frmState.orgetBlurred = true"
                        class="form-select form-select-sm" aria-label=".form-select-sm " 
                        :class="{ 'form-control-plaintext': readonly, 'form-control': !readonly, 'is-invalid': !notEmpty(currentValue.oe_kurzbz) && frmState.orgetBlurred}"
                        >
                        <option v-for="(item, index) in orgUnitList" :value="item.value">
                            {{ item.label }}
                        </option>                       
                    </select>
                </div>
                <div class="col-md-4">
                </div>
                <!--  -->
                <div class="col-md-8">
                    <label for="funktion_kurzbz" class="required form-label">{{ t('person','funktion') }}</label><br>
                    <select id="_kurzbz" v-model="currentValue.funktion_kurzbz" 
                        @blur="frmState.funktionBlurred = true"
                        class="form-select form-select-sm" aria-label=".form-select-sm " 
                        :class="{ 'form-control-plaintext': readonly, 'form-control': !readonly, 'is-invalid': !notEmpty(currentValue.funktion_kurzbz) && frmState.funktionBlurred}"
                        >
                        <option v-for="(item, index) in jobfunctionDefList" :value="item.value">
                            {{ item.label }}
                        </option>                       
                    </select>
                </div>
                <div class="col-md-4"></div>
                <!-- -->
                <div class="col-md-2">
                    <label for="uid" class="form-label">{{ t('person','wochenstunden') }}</label>
                    <input type="number"  :readonly="readonly" class="form-control-sm" :class="{ 'form-control-plaintext': readonly, 'form-control': !readonly}" id="bank" v-model="currentValue.wochenstunden">
                </div>
                <!--  -->
                <div class="col-md-3">
                    <label for="beginn" class="required form-label">{{ t('ui','from') }}</label>
                    <datepicker id="beginn"
                        :teleport="true" 
                        @blur="frmState.beginnBlurred = true" 
                        :input-class-name="(!notEmpty(currentValue.datum_von) && frmState.beginnBlurred) ? 'dp-invalid-input' : ''" 
                        v-model="currentValue.datum_von"
                        v-bind:enable-time-picker="false"
                        text-input 
                        locale="de"
                        format="dd.MM.yyyy"
                        auto-apply 
                        model-type="yyyy-MM-dd"></datepicker>
                </div>
                <div class="col-md-3">
                    <label for="ende" class="form-label">{{ t('global','bis') }}</label>
                    <datepicker id="ende"
                        :teleport="true" 
                        v-model="currentValue.datum_bis"
                        v-bind:enable-time-picker="false"
                        text-input 
                        locale="de"
                        format="dd.MM.yyyy"
                        auto-apply 
                        model-type="yyyy-MM-dd"></datepicker>                   
                </div>
                <div class="col-md-3">
                </div>
                <!-- -->
                <div class="col-md-8">
                    <label for="bezeichnung" class="form-label">{{ t('ui','bezeichnung') }}</label>
                    <input type="text"  :readonly="readonly" class="form-control-sm" :class="{ 'form-control-plaintext': readonly, 'form-control': !readonly}" id="bezeichnung" v-model="currentValue.bezeichnung">
                </div>
                <div class="col-md-4">
                </div>
                
                
                <!-- changes -->
                <div class="col-8">
                    <div class="modificationdate">{{ currentValue.insertamum }}/{{ currentValue.insertvon }}, {{ currentValue.updateamum }}/{{ currentValue.updatevon }} [id={{ currentValue.benutzerfunktion_id }}]</div>
                </div>
                
            </form>
        </template>
        <template #footer>
            <button type="button" class="btn btn-primary" @click="okHandler()" >
                {{ t('ui','speichern') }}
            </button>
        </template>

    </Modal>

    <ModalDialog :title="t('global','warnung')" ref="dialogRef" v-if="readonlyMode === false">
      <template #body>
        {{ t('person','funktionNochNichtGespeichert') }}
      </template>
    </ModalDialog>

    <ModalDialog :title="t('global','warnung')" ref="confirmDeleteRef" v-if="readonlyMode === false">
        <template #body>
            {{ t('person','funktion') }} '{{ currentValue?.funktion_kurzbz }} ({{ currentValue?.datum_von }}-{{ currentValue?.datum_bis }})' {{ t('person', 'wirklichLoeschen') }}?
        </template>
    </ModalDialog>
    `
}

export default Funktionen;