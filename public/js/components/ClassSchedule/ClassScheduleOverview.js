// Import the Core Filter- and Core RESTClient Component to build your table and handle data
import { CoreFilterCmpt } from "../filter/Filter.js";
import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";

import ClassScheduleTypeModal from "./ClassScheduleTypeModal.js";
import ClassScheduleValidityPeriodModal from "./ClassScheduleValidityPeriodModal.js";

export default {
  name: "ClassScheduleOverview",
  components: {
    CoreFilterCmpt,
    ClassScheduleTypeModal,
    ClassScheduleValidityPeriodModal,
  },
  props: {
    permissions: Object,
  },
  provide() {
    return {
      cisRoot: this.cisRoot,
      hasLehreUnterrichtszeitenTypWPermission:
        this.permissions["lehre/unterrichtszeiten_typ_w"] || false,
    };
  },
  data: () => {
    return {
      phrasesLoaded: false,
      editorParams: null,
      classTimeSlotValidityPeriods: [],
      classTimeSlotValidityPeriodId: null,
      editedClassTimeSlotValidityPeriodId: null,
      mondayClassTimeSlots: [],
      isClassTimeSlotTypeModalVisible: false,
      isClassTimeSlotValidityPeriodModalVisible: false,
    };
  },
  computed: {
    tabulatorOptions() {
      const options = {
        ajaxURL: "dummy",
        ajaxRequestFunc: () =>
          this.$api.call(ApiClassSchedule.getAllClassTimeValidityPeriods()),
        ajaxResponse: (url, params, response) => response.data,
        persistenceID: "core_class_schedule_validity_periods",
        selectableRows: true,
        columns: [
          {
            title: "gueltig von",
            field: "gueltig_von",
            width: 150,
          },
          { title: "gueltig bis", field: "gueltig_bis", width: 150 },
          { title: "orgform kurzbz", field: "orgform_kurzbz", width: 150 },
          {
            title: "ausbildungssemester",
            field: "ausbildungssemester",
            width: 150,
          },
          {
            title: "typ",
            field: "unterrichtszeitentyp_kurzbz",
          },
          {
            field: "unterrichtszeitengueltigkeit_id",
            visible: false,
          },
          {
            title: "Aktionen",
            field: "actions",
            minWidth: 150,
            maxWidth: 150,
            formatter: (cell, formatterParams, onRendered) => {
              let container = document.createElement("div");
              container.className = "d-flex gap-2";

              let button = document.createElement("button");

              button = document.createElement("button");
              button.className = "btn btn-outline-secondary btn-action";
              button.innerHTML = '<i class="fa fa-eye"></i>';
              button.title = this.$p.t(
                "classSchedule",
                "btn_showClassTimeSlotValidityPeriod",
              );
              button.addEventListener("click", (event) =>
                this.$router.push({
                  name: "validityPeriodOverview",
                  params: {
                    classTimeSlotValidityPeriodId:
                      cell.getData().unterrichtszeitengueltigkeit_id,
                  },
                }),
              );
              container.append(button);

              button = document.createElement("button");
              button.className = "btn btn-outline-secondary btn-action";
              button.innerHTML = '<i class="fa fa-edit"></i>';
              button.title = this.$p.t(
                "classSchedule",
                "btn_editClassTimeSlotValidityPeriod",
              );
              button.addEventListener("click", (event) =>
                this.editClassTimeSlotValidityPeriod(
                  cell.getData().unterrichtszeitengueltigkeit_id,
                ),
              );
              container.append(button);

              button = document.createElement("button");
              button.className =
                "btn btn-outline-secondary btn-action bg-danger";
              button.innerHTML = '<i class="fa fa-xmark text-white"></i>';
              button.title = this.$p.t(
                "classSchedule",
                "btn_deleteClassTimeSlotValidityPeriod",
              );
              button.addEventListener("click", () => {
                let isDeletionConfirmed = confirm(
                  this.$p.t(
                    "ui",
                    "deleteClassTimeSlotValidityPeriodConfirmation",
                  ),
                );
                if (!isDeletionConfirmed) return;

                this.deleteClassTimeSlotValidityPeriod(
                  cell.getData().unterrichtszeitengueltigkeit_id,
                );
              });
              container.append(button);

              return container;
            },
            frozen: true,
          },
        ],
      };
      return options;
    },
    tabulatorEvents() {
      const events = [
        {
          event: "tableBuilt",
          handler: async () => {
            const setHeader = (field, text) => {
              const col =
                this.$refs.classTimeSlotValidityPeriodsTable.tabulator.getColumn(
                  field,
                );
              if (!col) return;

              const el = col.getElement();
              if (!el || !el.querySelector) return;

              const titleEl = el.querySelector(".tabulator-col-title");
              if (titleEl) {
                titleEl.textContent = text;
              }
            };

            setHeader("nummer", this.$p.t("wawi", "nummer"));
            setHeader("anmerkung", this.$p.t("global", "anmerkung"));
            setHeader("retouram", this.$p.t("wawi", "retourdatum"));
            setHeader("beschreibung", this.$p.t("global", "beschreibung"));
            setHeader("kaution", this.$p.t("infocenter", "kaution"));
            setHeader("ausgegebenam", this.$p.t("wawi", "ausgabedatum"));
            setHeader("person_id", this.$p.t("person", "person_id"));
            setHeader("uid", this.$p.t("person", "uid"));
          },
        },
      ];
      return events;
    },
  },
  methods: {
    showClassTimeSlotValidityPeriodModal() {
      this.isClassTimeSlotValidityPeriodModalVisible = true;
    },
    editClassTimeSlotValidityPeriod(classTimeSlotValidityPeriodId) {
      this.editedClassTimeSlotValidityPeriodId = classTimeSlotValidityPeriodId;
    },
    deleteClassTimeSlotValidityPeriod(classTimeSlotValidityPeriodId) {
      return this.$api
        .call(
          ApiClassSchedule.deleteClassTimeSlotValidityPeriod(
            this.id,
            classTimeSlotValidityPeriodId,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successDelete"));
          window.scrollTo(0, 0);
          this.$refs.classTimeSlotValidityPeriodsTable.reloadTable();
        })
        .catch((error) => {
          console.error(
            "Error deleting class time slot validity period:",
            error,
          );
          this.$fhcAlert.handleSystemError(error);
        });
    },
    showClassTimeSlotTypeModal() {
      this.isClassTimeSlotTypeModalVisible = true;
    },
    resetClassTimeSlotTypeModal() {
      this.isClassTimeSlotTypeModalVisible = false;
    },
    resetClassTimeSlotValidityPeriodModal() {
      this.isClassTimeSlotValidityPeriodModalVisible = false;
    },
  },
  async created() {
    let getAllClassTimeValidityPeriodsResponse = await this.$api.call(
      ApiClassSchedule.getAllClassTimeValidityPeriods(),
    );
    if (getAllClassTimeValidityPeriodsResponse.meta.status === "success") {
      this.classTimeSlotValidityPeriods =
        getAllClassTimeValidityPeriodsResponse.data;
    } else {
      console.error(
        "Error fetching class time slot validity periods:",
        getAllClassTimeValidityPeriodsResponse.meta.message,
      );
    }
  },
  mounted() {
    this.$p
      .loadCategory(["global", "lehre", "ui", "gruppenmanagement"])
      .then(() => {
        this.phrasesLoaded = true;
      });
  },
  template: `
  <div class="container mt-4">
    <h1 class='mb-5'>{{ $p.t("ui", "classScheduleOverviewHeading") }}</h1>
    <div class="row mb-3">
      <div class="col d-flex justify-content-end">
        <a class="btn btn-primary mb-3" @click="showClassTimeSlotTypeModal">{{$p.t('ui', 'addClassTimeSlotTypeButton')}}</a>
      </div>
    </div>
    <class-schedule-type-modal 
      :isVisible="isClassTimeSlotTypeModalVisible" 
      @hideBsModal="resetClassTimeSlotTypeModal"
    />
    <class-schedule-validity-period-modal 
      :isVisible="isClassTimeSlotValidityPeriodModalVisible" 
      :editedClassTimeSlotValidityPeriodId="editedClassTimeSlotValidityPeriodId"
      @hideBsModal="() => { resetClassTimeSlotValidityPeriodModal(); editedClassTimeSlotValidityPeriodId = null; }"
      @classTimeSlotValidityPeriodCreated="() => { 
        $refs.classTimeSlotValidityPeriodsTable.reloadTable();
        resetClassTimeSlotValidityPeriodModal();
        this.editedClassTimeSlotValidityPeriodId = null;
      }"
      @classTimeSlotValidityPeriodUpdated="() => { 
        $refs.classTimeSlotValidityPeriodsTable.reloadTable();
        resetClassTimeSlotValidityPeriodModal();
        this.editedClassTimeSlotValidityPeriodId = null;
      }"
    />
    <core-filter-cmpt  
        v-if="phrasesLoaded"
        ref="classTimeSlotValidityPeriodsTable"
        table-only	 
        :side-menu="false"	 
        :tabulator-options="tabulatorOptions"
        :tabulator-events="[{ event: 'cellEdited'}]"	 
        :new-btn-label="$p.t('ui', 'addClassTimeSlotValidityPeriodButton')"
        new-btn-show
        reload
        @click:new="showClassTimeSlotValidityPeriodModal"
    ></core-filter-cmpt>
  </div>
  `,
};
