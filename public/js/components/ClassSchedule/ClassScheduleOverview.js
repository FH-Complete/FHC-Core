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
        ajaxRequestFunc: async () =>
          await this.getParsedClassTimeSlotValidityPeriodData(),
        ajaxResponse: (url, params, response) => response,
        persistenceID: "core_class_schedule_validity_periods",
        selectableRows: true,
        columns: [
          {
            title: this.$p.t("ui", "zeitraum"),
            formatter: (cell, formatterParams, onRendered) => {
              const data = cell.getData();
              const validFrom = new Date(data.gueltig_von).toLocaleDateString(
                "de-AT",
                {
                  day: "2-digit",
                  month: "2-digit",
                  year: "numeric",
                },
              );
              data.gueltig_von;
              const validTo = new Date(data.gueltig_bis).toLocaleDateString(
                "de-AT",
                {
                  day: "2-digit",
                  month: "2-digit",
                  year: "numeric",
                },
              );
              return `${validFrom ? validFrom : "?"} - ${validTo ? validTo : "?"}`;
            },
          },
          {
            title: this.$p.t("global", "typ"),
            field: "unterrichtszeiten_typ_bezeichnung_mehrsprachig",
            width: 150,
          },
          {
            title: this.$p.t("lehre", "sem"),
            field: "ausbildungssemester",
            width: 150,
          },
          {
            title: this.$p.t("global", "actions"),
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
        groupBy: [
          "organisationseinheit_bezeichnung_extended",
          "studienplan_bezeichnung",
        ],
        groupHeader: [
          function (value, count, data) {
            let container = document.createElement("span");

            container.className =
              "d-flex align-items-center justify-content-between";
            container.style.display = "inline-block";
            container.style.width = "100%";

            let label = document.createElement("span");
            label.textContent =
              value + " (" + count + " item" + (count > 1 ? "s" : "") + ")";
            container.append(label);

            let button = document.createElement("button");
            button.className =
              "btn btn-sm btn-outline-secondary fhc-btn-for-org-unit-grouping";
            button.style.marginLeft = "10px";
            button.innerHTML = '<i class="fa fa-eye"></i>';

            button.dataset.organizationalUnitShortCode = data[0].oe_kurzbz;

            container.append(button);
            return container;
          },
          function (value, count, data) {
            let container = document.createElement("span");

            container.className =
              "d-flex align-items-center justify-content-between";
            container.style.display = "inline-block";
            container.style.width = "100%";

            let label = document.createElement("span");
            label.textContent =
              value + " (" + count + " item" + (count > 1 ? "s" : "") + ")";
            container.append(label);

            let button = document.createElement("button");
            button.className =
              "btn btn-sm btn-outline-secondary fhc-btn-for-org-unit-and-study-plan-grouping";
            button.style.marginLeft = "10px";
            button.innerHTML = '<i class="fa fa-eye"></i>';
            button.title = 22;

            button.dataset.organizationalUnitShortCode = data[0].oe_kurzbz;
            button.dataset.studyPlanId = data[0].studienplan_id;

            container.append(button);
            return container;
          },
        ],
      };
      return options;
    },
    tabulatorEvents() {
      const events = [
        {
          event: "renderComplete",
          handler: async () => {
            document
              .querySelectorAll(".fhc-btn-for-org-unit-grouping")
              .forEach((button) => {
                button.addEventListener("click", (e) => {
                  let organizationalUnitShortCode =
                    button.dataset.organizationalUnitShortCode;

                  this.$router.push({
                    name: "validityPeriodsOverviewByOrganizationUnit",
                    params: {
                      organizationalUnitShortCode,
                    },
                  });
                });
              });

            document
              .querySelectorAll(".fhc-btn-for-org-unit-and-study-plan-grouping")
              .forEach((button) => {
                button.addEventListener("click", (e) => {
                  let organizationalUnitShortCode =
                    button.dataset.organizationalUnitShortCode;
                  let studyPlanId = button.dataset.studyPlanId;
                  this.$router.push({
                    name: "validityPeriodsOverviewByOrganizationUnitAndStudyPlan",
                    params: {
                      organizationalUnitShortCode,
                      studyPlanId,
                    },
                  });
                });
              });
          },
        },
      ];
      return events;
    },
  },
  methods: {
    test() {
      alert("test");
    },
    async getParsedClassTimeSlotValidityPeriodData() {
      let getAllClassTimeValidityPeriodsResponse = await this.$api.call(
        ApiClassSchedule.getAllClassTimeValidityPeriods(),
      );

      if (getAllClassTimeValidityPeriodsResponse.meta.status === "success") {
        let generalWord = this.$p.t("ui", "general");
        return getAllClassTimeValidityPeriodsResponse.data.map(
          function (period) {
            period.organisationseinheit_bezeichnung_extended =
              period.organisationseinheit_bezeichnung +
              " - " +
              period.organisationseinheit_organisationseinheittyp_kurzbz;
            if (!period.studienplan_bezeichnung) {
              period.studienplan_bezeichnung = generalWord;
            }
            period.unterrichtszeiten_typ_bezeichnung_mehrsprachig =
              period.unterrichtszeiten_typ_bezeichnung_mehrsprachig[0]?.split(
                ":",
              )[1];
            return {
              ...period,
            };
          },
        );
      } else {
        console.error(
          "Error fetching class time slot validity periods:",
          getAllClassTimeValidityPeriodsResponse.meta.message,
        );
      }
    },
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
      .loadCategory(["global", "lehre", "ui", "gruppenmanagement", "core"])
      .then(() => {
        this.phrasesLoaded = true;
      });
  },
  template: /* html */ `
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
        :tabulator-events="tabulatorEvents"	 
        :new-btn-label="$p.t('ui', 'addClassTimeSlotValidityPeriodButton')"
        new-btn-show
        reload
        @click:new="showClassTimeSlotValidityPeriodModal"
    ></core-filter-cmpt>
  </div>
  `,
};
