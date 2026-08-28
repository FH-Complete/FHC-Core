import {CoreFilterCmpt} from "../../filter/Filter.js";
import ListNew from './List/New.js';
import CoreTag from '../../Tag/Tag.js';
import {
  buildTagHeaderFilterExpression,
  customTagFilter,
  setTagHeaderFilterValue,
  syncSelectedTagOptionsWithHeaderFilters,
  syncTagHeaderFilterOptions,
  tagHeaderFilter,
} from "../../../tabulator/filters/extendedHeaderFilter.js";
import { addTagInTable, deleteTagInTable, updateTagInTable } from "../../../../js/helpers/TagHelper.js";
import { tagFormatter } from "../../../../js/tabulator/formatter/tags.js";
import ModalLoading from "./List/ModalLoading.js";

import ApiTag from "../../../api/factory/stv/tag.js";
import ListFilter from './List/Filter.js';

import { capitalize } from '../../../helpers/StringHelpers.js';

import draggable from '../../../directives/draggable.js';

import StvColumns from '../../../../../index.ci.php/js/tabulatorcolumns/stv';

import ModuleFilterFilters from '../../../tabulator/filter/filters.js';
Tabulator.extendModule("filter", "filters", ModuleFilterFilters);

export default {
  name: "ListPrestudents",
  components: {
    CoreFilterCmpt,
    ListNew,
    CoreTag,
    ListFilter,
	ModalLoading
  },
  directives: {
    draggable
  },
  inject: {
    lists: {
      from: 'lists',
      required: true
    },
    $reloadList: {
      from: '$reloadList',
      required: true
    },
    currentSemester: {
      from: 'currentSemester',
      required: true
    },
    tagsEnabled: {
      from: 'configStvTagsEnabled',
      default: false
    },
  },
  props: {
    selected: Array,
    studiengangKz: Number,
    studiensemesterKurzbz: String
  },
  emits: [
    'update:selected',
    'filterActive'
  ],
  data() {
    return {
      tagFilterState: {
        initialOptions: [],
        selectedOptions: [],
      },
      tagFilterLabels: {
        tag: "Tag",
        clear: "Clear",
        connectors: {
          AND: "AND",
          OR: "OR",
          NOT: "NOT",
        },
      },
      selectedColumnValues: [],
      tabulatorOptions: {
        columns: StvColumns,
        locale: true,
        rowFormatter(row) {
          if (row.getData().bnaktiv === false) {
            row.getElement().classList.add('text-black','text-opacity-50','fst-italic');
          }
          row.getElement().draggable = true
        },

        ajaxRequestFunc: (url, config, params) => {
          if ( url === '' ) {
            return Promise.resolve({ data: []});
          }
          /**
           * NOTE(chris): Because of a bug in Tabulator
           * we need to get the params from elsewhere.
           * @see https://github.com/olifolkerd/tabulator/issues/4318
           */
          const apiconfig = {
            ...this.tabulatorOptions.ajaxConfig,
            url: this.tabulatorOptions.ajaxURL,
            params: this.tabulatorOptions.ajaxParams
          };
          return this.$api.call(apiconfig);
        },
        ajaxResponse: (url, params, response) => {
          return response?.data;
        },

        layout: 'fitDataStretch',
        layoutColumnsOnNewData: false,
        height: '100%',
        selectableRows: true,
        selectableRowsRangeMode: 'click',
        index: 'prestudent_id',
        persistence: {
          sort: true,
          columns: ["width", "visible"],
          filter: false,
          headerFilter: false,
          group: false,
          page: false,
        },
        persistenceID: 'stv-list-20260223_01'
      },
      tabulatorEvents: [
        {
          event: 'rowSelectionChanged',
          handler: this.rowSelectionChanged
        },
        {
          event: 'dataLoading',
          handler: this.handleDataLoading
        },
        {
          event: 'renderComplete',
          handler: this.handleRenderComplete
        },
        {
          event: 'dataProcessed',
          handler: (data) => {
            this.getAllRows()
            this.autoSelectRows(data)
          }
        },
        {
          event: 'dataLoaded',
          handler: data => {
            if (Array.isArray(data)) {
              this.count = data.length;
              this.allPrestudents = data.map(item => item.prestudent_id);
            } else {
              this.count = 0;
              this.allPrestudents = [];
            }
            syncTagHeaderFilterOptions(
              Array.isArray(data) ? data : [],
              this.tagFilterState.initialOptions,
              this.tagFilterState.selectedOptions,
            );
          }
        },
        {
          event: 'dataFiltered',
          handler: (filters, rows) => {
            this.filteredcount = rows.length;
            syncSelectedTagOptionsWithHeaderFilters(
              filters,
              this.tagFilterState.selectedOptions,
              this.tagsEnabled,
            );
          },
        },
        {
          event: 'rowClick',
          handler: this.handleRowClick // TODO(chris): this should be in the filter component
        },
        {
          event: 'dataTreeRowExpanded',
          handler: (data) => {
            this.getExpandedRows()
          }
        },
        {
          event: 'dataTreeRowCollapsed',
          handler: (data) => {
            this.getExpandedRows()
          }
        },
        {
          event: 'rowMouseDown',
          handler: this.handleMouseDown
        },
        {
          event: "columnWidth",
          handler: (column) => {
            if (column.getField() !== "tags") return;

            column.getCells().forEach((cell) => {
              cell.getElement().firstElementChild?.fitTags?.();
            });
          },
        },
      ],
      focusObj: null, // TODO(chris): this should be in the filter component
      lastSelected: null,
      filter: [],
      count: 0,
      filteredcount: 0,
      selectedcount: 0,
      //tags
      expanded: [],
      tagEndpoint: ApiTag,
      currentEndpoint: null,
      headerFilterActive: false,
      dragSource: [],
      oldScrollUrl: '',
      oldScrollLeft: 0,
      oldScrollTop: 0,
      allPrestudents: [],
      rebuildData: [],
      isLoading: false,
      progress: -1,
      total: -1,
      processed: -1
    }
  },
  computed: {
	generalPresets: function() {
		return [
			{
				"id": null,
				"name": "Standard",
				"displayedColumns": [
					"uid","titelpre","tags","nachname","vorname","titelpost",
					"ersatzkennzeichen","gebdatum","geschlecht",
					"semester_berechnet","verband","gruppe","studiengang",
					"matrikelnr","person_id","status","orgform_kurzbz",
					"studienplan_bezeichnung","prestudent_id","priorisierung_relativ"
				],
				"headerFilters": [],
				"sort": null
			}
		];
	},
    countsToHTML: function() {
      return this.$p.t('global/ausgewaehlt')
        + ': <strong>' + (this.selectedcount || 0) + '</strong>'
        + ' | '
        + this.$p.t('global/gefiltert')
        + ': '
        + '<strong>' + (this.filteredcount || 0) + '</strong>'
        + ' | '
        + this.$p.t('global/gesamt')
        + ': <strong>' + (this.count || 0) + '</strong>';
    },
    selectedDragObject() {
      let items = this.dragSource?.length ? this.dragSource : this.selected;

      return items.map(item => {
        let type, id;
        if (item.uid) {
          type = 'student';
          id = item.uid;
        } else if (item.prestudent_id) {
          type = 'prestudent';
          id = item.prestudent_id;
        } else if (item.person_id) {
          type = 'person';
          id = item.person_id;
        }
        return {
          ...item,
          type,
          id
        };
      });
    },
    downloadConfig() {
      return {
        csv: {
          formatter: 'csv',
          file: this.fileString,
          options: {
            delimiter: ';',
            bom: true,
          },
          rowRange: this.selectedcount > 0 ? "selected" : "all",
        },
      };
    },
    fileString() {
      let today = new Date().toLocaleDateString('en-GB')
        .replace(/\//g, '_');
      return "StudentList_" + today + ".csv";
    },
    selectedPrestudents() {
      if (this.selected && this.selected.length > 0) {
        return this.selected.map(item => item.prestudent_id);
      } else {
        // fallback whole list of prestudents
        return this.allPrestudents || [];
      }
    },

    linkXLS(){
      return FHC_JS_DATA_STORAGE_OBJECT.app_root
      + 'content/statistik/studentenexportextended.xls.php?'
      + '&studiensemester_kurzbz=' + this.currentSemester
      + '&data=' + this.selectedPrestudents.join(";");
    },
  },
  watch: {
    "$p.user_language.value"(n, o) {
      if (n !== o && o !== undefined && this.$refs.table.tableBuilt) {
        this.translateTabulator();
        this.$reloadList();
      }
    },
    "tagFilterState.selectedOptions": {
      handler() {
        const selectedOptions = this.tagFilterState.selectedOptions;
        const combinedFilterStatement =
        buildTagHeaderFilterExpression(selectedOptions);

        setTagHeaderFilterValue(
          combinedFilterStatement,
          this.$refs.table.tabulator,
          );
      },
      deep: true,
    },
  },
  methods: {
    translateTabulator() {
      this.$p
        .loadCategory([
          "global",
          "person",
          "lehre",
          "ui",
          "profilUpdate",
          "admission",
          "stv",
        ])
        .then(() => {
          this.updateTagFilterLabels();

          const translations = {
            uid: capitalize(this.$p.t("person/uid")),
            titelpre: capitalize(this.$p.t("person/titelpre")),
            nachname: capitalize(this.$p.t("person/nachname")),
            vorname: capitalize(this.$p.t("person/vorname")),
            wahlname: capitalize(this.$p.t("person/wahlname")),
            vornamen: capitalize(this.$p.t("person/vornamen")),
            titelpost: capitalize(this.$p.t("person/titelpost")),
            ersatzkennzeichen: capitalize(
              this.$p.t("person/ersatzkennzeichen"),
              ),
            gebdatum: capitalize(this.$p.t("person/geburtsdatum")),
            geschlecht: capitalize(this.$p.t("person/geschlecht")),
            semester_berechnet: capitalize(this.$p.t("lehre/sem")),
            verband: capitalize(this.$p.t("lehre/verb")),
            gruppe: capitalize(this.$p.t("lehre/grp")),
            studiengang: capitalize(this.$p.t("lehre/studiengang")),
            studiengang_kz: capitalize(this.$p.t("lehre/studiengang_kz")),
            matrikelnr: capitalize(this.$p.t("person/personenkennzeichen")),
            person_id: capitalize(this.$p.t("person/person_id")),
            status: capitalize(this.$p.t("global/status")),
            status_datum: capitalize(this.$p.t("profilUpdate/statusDate")),
            status_bestaetigung: capitalize(
              this.$p.t("global/status_bestaetigung"),
              ),
            mail_privat: capitalize(this.$p.t("person/email_private")),
            mail_intern: capitalize(this.$p.t("person/email_intern")),
            anmerkungen: capitalize(this.$p.t("stv/notes_person")),
            anmerkung: capitalize(this.$p.t("stv/notes_prestudent")),
            orgform_kurzbz: capitalize(this.$p.t("lehre/orgform")),
            aufmerksamdurch_kurzbz: capitalize(
              this.$p.t("person/aufmerksamDurch"),
              ),
            punkte: capitalize(this.$p.t("admission/gesamtpunkte")),
            aufnahmegruppe_kurzbz: capitalize(
              this.$p.t("stv/aufnahmegruppe_kurzbz"),
              ),
            dual: capitalize(this.$p.t("lehre/dual_short")),
            matr_nr: capitalize(this.$p.t("person/matrikelnummer")),
            studienplan_bezeichnung: capitalize(this.$p.t("lehre/studienplan")),
            prestudent_id: capitalize(this.$p.t("ui/prestudent_id")),
            priorisierung_relativ: capitalize(this.$p.t("lehre/prioritaet")),
            mentor: capitalize(this.$p.t("stv/mentor")),
            bnaktiv: capitalize(this.$p.t("person/aktiv")),
          };

            /** NOTE(chris):
             * use this approach because updateDefinition
             * on the Tabulator columns is way slower and
             * freezes up the GUI.
             */
            // Overwrite definition for column show/hide
          this.$refs.table.tabulator.getColumns().forEach((col) => {
            const trans = translations[col.getField()];
            if (!trans) return;
            col.getDefinition().title = trans;
          });
            // Overwrite node in dom
          this.$refs.table.tabulator.element
          .querySelectorAll(".tabulator-col[tabulator-field]")
          .forEach((el) => {
            const field = el.getAttribute("tabulator-field");
            if (!translations[field]) return;

            const title = el.querySelector(".tabulator-col-title");
            if (!title) return;

            title.innerText = translations[field];
          });
        });
    },
    reload() {
      this.$refs.table.reloadTable();
    },
    actionNewPrestudent() {
      this.$refs.new.open();
    },
    rowSelectionChanged(data, rows, selected, deselected) {
      this.selectedcount = data.length;

      //in case of empty selection (eg. in future or past semester of selected student without sem)
      if (selected.length == 0 ) {
        this.lastSelected = this.selected;
      }

      if (selected.length > 0 || deselected.length > 0) {
        this.lastSelected = this.selected;

        //for tags
        this.selectedRows = this.$refs.table.tabulator.getSelectedRows();
        this.selectedColumnValues = this.selectedRows.filter(
          row => row.getData().prestudent_id !== undefined
            && row.getData().prestudent_id
        ).map(
          row => row.getData().prestudent_id
        );

        this.$emit('update:selected', data);
      }
    },
    autoSelectRows(data) {
      if (Array.isArray(this.lastSelected) && this.lastSelected.length){
        // NOTE(chris): reselect rows on refresh
        let selected = this.lastSelected.map(el => this.$refs.table.tabulator.getRow(el.prestudent_id))
        // TODO(chris): unselect current item if it's no longer in the table?
        // or maybe reselect only the last one?
        selected = selected.filter(el => el);

        if (selected.length)
          this.$refs.table.tabulator.selectRow(selected);
      } else if(data && this.lastSelected === undefined) {
        // NOTE(chris): select row if it's the only one (preferably only on startup)
        if (data.length == 1) {
          this.$refs.table.tabulator.selectRow(this.$refs.table.tabulator.getRows());
        }
      }
    },
    updateFilter(filter) {
      this.filter = filter;
      this.$emit('filterActive', filter);
      this.updateUrl();
    },
    updateTagFilterLabels() {
      this.tagFilterLabels.clear = this.$p.t("ui/filterdelete");
      this.tagFilterLabels.tag = "Tag";
      this.tagFilterLabels.connectors.AND = this.$p
        .t("ui/andCondition")
        .toUpperCase();
      this.tagFilterLabels.connectors.OR = this.$p
        .t("ui/orCondition")
        .toUpperCase();
      this.tagFilterLabels.connectors.NOT = this.$p
        .t("ui/notCondition")
        .toUpperCase();
    },
    updateUrl(endpoint, first) {
      this.lastSelected = first ? undefined : this.selected;

      if (endpoint === undefined && this.currentEndpoint === null) {
        endpoint = { url: '' };
      } else if (endpoint === undefined) {
        endpoint = JSON.parse(JSON.stringify(this.currentEndpoint));
      } else {
        this.currentEndpoint = JSON.parse(JSON.stringify(endpoint));
      }

      endpoint.url = endpoint.url.replace(
        'CURRENT_SEMESTER',
        encodeURIComponent(this.currentSemester)
      );

      const params = (endpoint?.params !== undefined) ? endpoint.params : {};
      let method = (endpoint?.method !== undefined) ? endpoint.method : 'get';
      if (this.filter.length && !endpoint.url.match(/\/search\//)) {
        params.filter = this.filter;
        method = 'post';
      }

      this.tabulatorOptions.ajaxURL = endpoint.url;
      this.tabulatorOptions.ajaxParams = { ...params };
      this.tabulatorOptions.ajaxConfig = { method };

      if (!this.$refs.table.tableBuilt) {
        if (this.$refs.table.tabulator) {
          this.$refs.table.tabulator.on("tableBuilt", () => {
            this.$refs.table.tabulator.setData(endpoint.url, params, method);
          });
        }
      } else
        this.$refs.table.tabulator.setData(endpoint.url, params, method);
    },
    dragCleanup(evt) {
      this.dragSource = [];
      if (evt.dataTransfer.dropEffect == 'none')
        return; // aborted or wrong target
      
      this.$reloadList();
    },
    onKeydown(e) { // TODO(chris): this should be in the filter component

      if ((e.ctrlKey || e.metaKey) && e.code === "KeyA") {
        e.preventDefault();

        this.$refs.table.tabulator.deselectRow();
        this.$refs.table.tabulator.selectRow('active');
      }

      if (!this.focusObj)
        return;

      // Ignore typing inside editable elements
      if (
        e.target instanceof HTMLInputElement ||
        e.target instanceof HTMLTextAreaElement ||
        e.target.isContentEditable
      )
        return;

      var next;
      switch (e.code) {
        case 'Enter':
        case 'Space':
          e.preventDefault();
          var e2 = new Event('click', e);
          e2.altKey = e.altKey;
          e2.ctrlKey = e.ctrlKey;
          e2.shiftKey = e.shiftKey;
          this.focusObj.dispatchEvent(e2);
          //row.component.toggleSelect();
          break;
        case 'ArrowUp':
          e.preventDefault();
          next = this.focusObj.previousElementSibling;
          if (next)
            this.changeFocus(this.focusObj, next);
          break;
        case 'ArrowDown':
          e.preventDefault();
          next = this.focusObj.nextElementSibling;
          if (next)
            this.changeFocus(this.focusObj, next);
          break;
      }
    },
    changeFocus(a, b) { // TODO(chris): this should be in the filter component
      if (b) {
        b.tabIndex = 0;
        this.focusObj = b;
        b.focus();
      } else {
        this.focusObj = null;
      }
      a.tabIndex = -1;
      return this.focusObj;
    },
    onFocus(e) { // TODO(chris): this should be in the filter component
      if (!this.focusObj) {
        var container, target;
        if (e.target.classList.contains('tabulator-container')) {
          container = e.target;
          target = container.querySelector('.tabulator-row');
        } else if (e.target.classList.contains('tabulator-row')) {
          container = e.target.closest('.tabulator-container');
          target = e.target;
        }
        if (container && target) {
          this.changeFocus(container, target);
        }
      }
    },
    handleRowClick(e, row) { // TODO(chris): this should be in the filter component
      if (this.focusObj) {
        let el = row.getElement();
        if (el != this.focusObj)
          this.changeFocus(this.focusObj, el);
      }
    },
    clearSelection() {
      this.lastSelected = [];
      this.$emit('update:selected',[]);
    },
    //methods tags
    addedTag(addedTag) {
      addTagInTable(addedTag, this.allRows, 'prestudent_id')
      syncTagHeaderFilterOptions(
        this.$refs.table?.tabulator?.getData() || [],
        this.tagFilterState.initialOptions,
        this.tagFilterState.selectedOptions,
      );
    },
    deletedTag(deletedTag) {
      deleteTagInTable(deletedTag, this.allRows);
      syncTagHeaderFilterOptions(
        this.$refs.table?.tabulator?.getData() || [],
        this.tagFilterState.initialOptions,
        this.tagFilterState.selectedOptions,
      );
    },
    updatedTag(updatedTag) {
      updateTagInTable(updatedTag, this.allRows)
      syncTagHeaderFilterOptions(
        this.$refs.table?.tabulator?.getData() || [],
        this.tagFilterState.initialOptions,
        this.tagFilterState.selectedOptions,
      );
    },
    getAllRows() {
      this.allRows = this.$refs.table.tabulator.getRows();
    },
    resetFilter() {
      this.clearSelectedTagHeaderFilters();
      this.$refs.listfilter.resetFilter();
      this.$refs.table.clearFilters();
    },
    clearSelectedTagHeaderFilters() {
      this.tagFilterState.selectedOptions.splice(
        0,
        this.tagFilterState.selectedOptions.length
      );
    },
    handleHeaderFilter(filterActive) {
      this.headerFilterActive = filterActive;
    },
    handleMouseDown(e, row) {
      let data = row.getData();
      let id = data.uid ?? data.prestudent_id ?? data.person_id;

      const isAlreadySelected = this.selected?.some(
        row => (row.uid ?? row.prestudent_id ?? row.person_id) === id
      );

      this.dragSource = 
        isAlreadySelected && this.selected?.length ? this.selected : [data];
    },
    handleDataLoading() {
      this.oldScrollLeft = this.$refs.table.tabulator.rowManager.scrollLeft;
      this.oldScrollTop = this.$refs.table.tabulator.rowManager.scrollTop;
    },
    handleRenderComplete() {
      const table = this.$refs.table.tabulator.element.querySelector(
        '.tabulator-tableholder'
      );
      if (table) {
        const curAjaxUrl = this.$refs.table.tabulator.getAjaxUrl();
        if (this.oldScrollUrl === curAjaxUrl) {
          table.scrollLeft = this.oldScrollLeft;
          table.scrollTop = this.oldScrollTop;
        } else {
          this.oldScrollLeft = table.scrollLeft;
          this.oldScrollTop = table.scrollTop;
        }
        this.oldScrollUrl = this.$refs.table.tabulator.getAjaxUrl();
      }
    },
	async rebuild(selected){
		const maxbulksize = 25;
		const minbulksize = 5;
		let bulksize = minbulksize;
		this.isLoading = true;
		let prestudentIds = [];
		if(!selected.length)
			prestudentIds = this.$refs.table.tabulator
				.getData()
				.map(row => row.prestudent_id);
		else
			prestudentIds = selected.map(item => item.prestudent_id);

		this.progress = 0;
		this.total = prestudentIds.length;
		this.processed = 0;
		this.rebuildData = [
			[],
			[],
			[]
		];

		this.showModal();

		while(this.processed < this.total)
		{
			let iteration_prestudentIds = prestudentIds.slice(
				this.processed,
				(this.processed + bulksize)
			);
			const params = {
				ids: iteration_prestudentIds,
				typeId: 'prestudent_id',
				sem: this.studiensemesterKurzbz
			};

			try
			{
				const result = await this.$api.call(ApiTag.rebuildTagsforTypeId(params));
				this.rebuildData[1] = [...this.rebuildData[1], ...result.data[1]];
				this.rebuildData[2] = [...this.rebuildData[2], ...result.data[2]];
			}
			catch(error) {
				this.$fhcAlert.handleSystemError(error);
			};

			this.processed += iteration_prestudentIds.length;
			this.progress = Math.round(this.processed * 100 / this.total);

			bulksize = bulksize * 2;
			if(bulksize > maxbulksize) bulksize = maxbulksize;
		}

		if(this.rebuildData[1].length > 0)
		{
			this.$fhcAlert.alertSuccess(this.$p.t('tag', 'alertSuccessRebuild', { count: this.rebuildData[1].length }));
		}
		if(this.rebuildData[2].length > 0)
		{
			this.$fhcAlert.alertError(this.$p.t('tag', 'alertErrorRebuild') + this.rebuildData[2].toString());
		}

		this.isLoading = false;
		this.$reloadList();
	},
	showModal(){
		this.$refs.modalLocked.open();
	},
  },
  created() {
    if (this.tagsEnabled) {
      const coltags = {
        title: 'Tags',
        field: 'tags',
        tooltip: false,
        headerFilter: customTagFilter,
        headerFilterParams: {
          listOnEmpty: true,
          autocomplete: true,
          sort: "asc",
          initialOptions: this.tagFilterState.initialOptions,
          selectedOptions: this.tagFilterState.selectedOptions,
          labels: this.tagFilterLabels,
        },
        headerFilterFunc: tagHeaderFilter,
        headerFilterFuncParams: { field: 'tags' },
        formatter: (cell, formatterParams, onRendered) => tagFormatter(cell, this.$refs.tagComponent, onRendered),
        width: 150,
        headerSort: false,
      };
      this.tabulatorOptions.columns.splice(2, 0, coltags);
      this.$p.loadCategory("ui").then(() => {
        this.updateTagFilterLabels();
      });
    }
  },
  // TODO(chris): focusin, focusout, keydown and tabindex should be in the filter component
  // TODO(chris): filter component column chooser has no accessibilty features
  template: /* html */`
  <div class="stv-list h-100 pt-3">
	<modal-loading
		ref="modalLocked"
		:isLoading="isLoading"
		:progress="progress"
		:total="total"
		:processed="processed"
		:message="$p.t('tag','messageModalWait')"
	>
	</modal-loading>

    <div
      class="tabulator-container d-flex flex-column h-100 pe-2"
      :class="{'has-filter': filter.length}"
      tabindex="0"
      @focusin="onFocus"
      @keydown="onKeydown"
      v-draggable:copyLink.capture="selectedDragObject"
      @dragend="dragCleanup"
    >
      <core-filter-cmpt
        ref="table"
		:isUsingPresets="true"
		presetsId="studVwListTable"
		:generalPresets="generalPresets"
        :description="countsToHTML"
        :tabulator-options="tabulatorOptions"
        :tabulator-events="tabulatorEvents"
        table-only
        :side-menu="false"
        reload
        :download="downloadConfig"
        new-btn-show
        :new-btn-label="$p.t('stv/action_new')"
        @click:new="actionNewPrestudent"
        @table-built="translateTabulator"
        :useSelectionSpan="false"
        @headerFilterOn="handleHeaderFilter"
      >

    <!--
      <template #actions>
        <div>
          <button
          class="btn btn-outline-success sm mb-1"
            :title="'Export ' + selectedPrestudents.length + ' prestudent(s) to Excel'"
          >
            <i class="fas fa-file-excel fa-xl"></i>
          </button>
        </div>
       </template>
     -->

       <template #additional>
        <div class="pe-1">
          <a :href="linkXLS" target="_blank">
             <i class="fas fa-file-excel fa-xl text-success"   :title="$p.t('stv', 'text_exportXLS', { count: selectedPrestudents.length })"></i>
          </a>
        </div>
       </template>

      <template #tags>
        <div class="d-flex border rounded align-items-center ps-1">
          <core-tag ref="tagComponent"
            v-if="tagsEnabled"
            :endpoint="tagEndpoint"
            :values="selectedColumnValues"
            @added="addedTag"
            @deleted="deletedTag"
            @updated="updatedTag"
            zuordnung_typ="prestudent_id"
            show-hover
          ></core-tag>

          <button
              v-if="!selected.length"
              class="btn btn-outline btn-sm m-1 btn-hover"
              @click="rebuild(selected)"
              :title="$p.t('tag','rebuild_tags') + ' Stg ' + currentSemester"
              >
                  <i class="fa-solid fa-refresh pe-1"></i> STG
          </button>
          <button
              v-else
              class="btn btn-outline btn-sm m-1 btn-hover"
              @click="rebuild(selected)"
              :title="$p.t('tag','rebuild_tags') + ' ' + $p.t('ui','selection') + ' ' + currentSemester"
              >
                  <i class="fa-solid fa-refresh pe-1"></i> {{selected.length}}
          </button>
        </div>
      </template>

      <template v-if="filter.length || headerFilterActive">
        <div class="d-flex justify-content-center align-items-center gap-2 ps-4 position-absolute start-50 translate-middle-x">
          <p class="text-danger mb-0">
            <strong>{{$p.t('filter','filterActive')}}</strong>
          </p>

          <button
            class="btn btn-outline-danger sm"
            :title="$p.t('filter/filterDelete')"
            @click="resetFilter"
          >
            <span class="fa-solid fa-filter-circle-xmark"></span>
          </button>
        </div>
      </template>

      <template #filter>
        <div class="card mt-2">
          <div class="card-body p-2">
            <list-filter ref="listfilter" @change="updateFilter" :filterActive="filter.length"/>
          </div>
        </div>
      </template>

      </core-filter-cmpt>
    </div>
    <list-new ref="new" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></list-new>
  </div>`
};
