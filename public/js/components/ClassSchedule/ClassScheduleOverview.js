import { CoreFilterCmpt } from "../filter/Filter.js";
import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";
import ApiStudienSemester from "../../../js/api/factory/studiensemester.js";

import ClassScheduleTypeModal from "./ClassScheduleTypeModal.js";
import ClassScheduleValidityPeriodModal from "./ClassScheduleValidityPeriodModal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import ApiOrganizationalUnit from "../../../js/api/factory/organizationalUnit.js";

export default {
  name: "ClassScheduleOverview",
  components: {
    CoreFilterCmpt,
    ClassScheduleTypeModal,
    ClassScheduleValidityPeriodModal,
    CoreForm,
    FormInput,
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
  watch: {
    filterData: {
      handler(newValue) {
        this.$refs.classTimeSlotValidityPeriodsTable.tabulator.replaceData();
      },
      deep: true,
    },
    selectedSemester: {
      handler(newValue) {
        this.filterData.validityPeriodFrom = this.selectedSemester.start;
        this.filterData.validityPeriodTo = this.selectedSemester.ende;
      },
    },
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
      organizationalUnits: [],
      filteredOrganizationalUnits: [],
      allSemesters: [],
      filteredSemesters: [],
      filterData: {
        selectedOrganizationalUnit: null,
        validityPeriodFrom: null,
        validityPeriodTo: null,
      },
      selectedSemester: null,
    };
  },
  computed: {
    userLanguage() {
      return Vue.ref(FHC_JS_DATA_STORAGE_OBJECT.user_language);
    },
    tabulatorOptions() {
      const options = {
        ajaxURL: "dummy",
        ajaxRequestFunc: async () => {
          return await this.getParsedClassTimeSlotValidityPeriodData();
        },
        ajaxResponse: (url, params, response) => response,
        persistenceID: "class_schedule_validity_periods_table",
        selectableRows: true,
        maxHeight: "100%",
        columns: [
          {
            title: this.$capitalize(
              this.$p.t("ui", "unterrichtszeitenGueltigkeitId"),
            ),
            field: "unterrichtszeitengueltigkeit_id",
            visible: false,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "zeitraum")),
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
            title: this.$capitalize(this.$p.t("global", "typ")),
            field: "unterrichtszeiten_typ_bezeichnung_mehrsprachig",
            width: 150,
            formatter: (cell, formatterParams, onRendered) => {
              if (
                !cell.getData().unterrichtszeiten_typ_bezeichnung_mehrsprachig
              ) {
                return "";
              }
              return this.userLanguage?.value === "English"
                ? cell.getData()
                    .unterrichtszeiten_typ_bezeichnung_mehrsprachig[1]
                : cell.getData()
                    .unterrichtszeiten_typ_bezeichnung_mehrsprachig[0];
            },
          },
          {
            title: this.$capitalize(this.$p.t("lehre", "sem")),
            field: "ausbildungssemester",
            width: 150,
          },
          {
            title: this.$capitalize(this.$p.t("global", "beschreibung")),
            field: "anmerkung",
            width: 150,
          },
          {
            title: this.$capitalize(this.$p.t("global", "actions")),
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
            label.textContent = value;
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
            label.textContent = value;
            container.append(label);

            let button = document.createElement("button");
            button.className =
              "btn btn-sm btn-outline-secondary fhc-btn-for-org-unit-and-study-plan-grouping";
            button.style.marginLeft = "10px";
            button.innerHTML = '<i class="fa fa-eye"></i>';

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
    dropdownParsedOrganizationalUnits() {
      return this.organizationalUnits
        .filter((unit) => unit.aktiv)
        .map((unit) => {
          return {
            label: `[${unit.organisationseinheittyp_kurzbz}] ${unit.bezeichnung}`,
            value: unit.oe_kurzbz,
          };
        })
        .sort((a, b) => a.label.localeCompare(b.label));
    },
    dropdownParsedSemesters() {
      return this.allSemesters.map((semester) => {
        return {
          label: semester.studiensemester_kurzbz,
          value: semester.studiensemester_kurzbz,
          start: semester.start,
          ende: semester.ende,
        };
      });
    },
    hasLehreUnterrichtszeitenTypRPermission() {
      return this.permissions["lehre/unterrichtszeiten_typ_r"] || false;
    },
  },
  methods: {
    async getParsedClassTimeSlotValidityPeriodData() {
      let getAllClassTimeValidityPeriodsResponse = await this.$api.call(
        ApiClassSchedule.getAllClassTimeValidityPeriods({
          organizationalUnitShortCode:
            this.filterData.selectedOrganizationalUnit?.value,
          validityPeriodFrom: this.filterData.validityPeriodFrom
            ? moment(this.filterData.validityPeriodFrom).format("YYYY-MM-DD")
            : null,
          validityPeriodTo: this.filterData.validityPeriodTo
            ? moment(this.filterData.validityPeriodTo).format("YYYY-MM-DD")
            : null,
        }),
      );

      if (getAllClassTimeValidityPeriodsResponse.meta.status === "success") {
        let generalWord = this.$p.t("ui", "general");
        return getAllClassTimeValidityPeriodsResponse.data.map(
          function (period) {
            period.organisationseinheit_bezeichnung_extended =
              "[" +
              period.organisationseinheit_organisationseinheittyp_kurzbz +
              "] " +
              period.organisationseinheit_bezeichnung;
            if (!period.studienplan_bezeichnung) {
              period.studienplan_bezeichnung = generalWord;
            }
            return {
              ...period,
            };
          },
        );
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "errorFetchingClassScheduleValidityPeriods"),
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
          this.$refs.classTimeSlotValidityPeriodsTable.tabulator.replaceData();
        })
        .catch((error) => {
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
    filterOrganizationalUnits(event) {
      let defaultItem = {
        label: this.$p.t("ui", "dropdownEmptyOption"),
        value: null,
      };

      const query = event.query.toLowerCase();
      if (!query) {
        return (this.filteredOrganizationalUnits = [
          defaultItem,
          ...this.dropdownParsedOrganizationalUnits,
        ]);
      }

      return (this.filteredOrganizationalUnits = [defaultItem]
        .concat(this.dropdownParsedOrganizationalUnits)
        .filter((unit) => {
          return unit.label.toLowerCase().includes(query);
        }));
    },
    filterSemesters(event) {
      let defaultItem = {
        label: this.$p.t("ui", "dropdownEmptyOption"),
        value: null,
      };

      const query = event.query.toLowerCase();
      if (!query) {
        return (this.filteredSemesters = [
          defaultItem,
          ...this.dropdownParsedSemesters,
        ]);
      }

      return (this.filteredSemesters = [defaultItem]
        .concat(this.dropdownParsedSemesters)
        .filter((semester) => {
          return semester.label.toLowerCase().includes(query);
        }));
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
      this.$fhcAlert.alertError(
        this.$p.t("ui", "errorFetchingClassScheduleValidityPeriods"),
      );
    }

    let getAllOrganizationalUnitsResponse = await this.$api.call(
      ApiOrganizationalUnit.getAllOrganizationalUnits(),
    );
    if (getAllOrganizationalUnitsResponse.meta.status === "success") {
      this.organizationalUnits = getAllOrganizationalUnitsResponse.data.sort(
        (a, b) => a.bezeichnung.localeCompare(b.bezeichnung),
      );
    } else {
      this.$fhcAlert.alertError(
        this.$p.t("ui", "errorFetchingOrganizationalUnits"),
      );
    }

    let getAllSemestersResponse = await this.$api.call(
      ApiStudienSemester.getAll("DESC"),
    );
    if (getAllSemestersResponse.meta.status === "success") {
      this.allSemesters = getAllSemestersResponse.data;
    } else {
      this.$fhcAlert.alertError(this.$p.t("ui", "errorFetchingSemesters"));
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
      <div class="col d-flex justify-content-between">
        <a class="btn btn-primary mb-3" @click="showClassTimeSlotValidityPeriodModal">{{$p.t('ui', 'addClassTimeSlotValidityPeriodButton')}}</a>
        <a v-if="hasLehreUnterrichtszeitenTypRPermission" class="btn btn-secondary mb-3" @click="showClassTimeSlotTypeModal">{{$p.t('ui', 'addClassTimeSlotTypeButton')}}</a>
      </div>
    </div>
    <div class="row mb-3" style="height: 65vh;">
      <class-schedule-type-modal
        v-if="hasLehreUnterrichtszeitenTypRPermission"
        :isVisible="isClassTimeSlotTypeModalVisible" 
        @hideBsModal="resetClassTimeSlotTypeModal"
      />
      <class-schedule-validity-period-modal 
        :isVisible="isClassTimeSlotValidityPeriodModalVisible" 
        :editedClassTimeSlotValidityPeriodId="editedClassTimeSlotValidityPeriodId"
        @hideBsModal="() => { resetClassTimeSlotValidityPeriodModal(); editedClassTimeSlotValidityPeriodId = null; }"
        @classTimeSlotValidityPeriodCreated="() => { 
          $refs.classTimeSlotValidityPeriodsTable.tabulator.replaceData();
          resetClassTimeSlotValidityPeriodModal();
          this.editedClassTimeSlotValidityPeriodId = null;
        }"
        @classTimeSlotValidityPeriodUpdated="() => { 
          $refs.classTimeSlotValidityPeriodsTable.tabulator.replaceData();
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
      >
        <template #search>
          <slot name="filterzuruecksetzen">
            <core-form class="d-flex flex-column flex-md-row align-items-md-end gap-3">
              <div>
                <form-input
                  :label="$capitalize($p.t('lehre/organisationseinheit'))"
                  :suggestions="filteredOrganizationalUnits"
                  :optionValue="(option) => option.value"
                  :optionLabel="(option) => option.label" 
                  @complete="filterOrganizationalUnits($event)"
                  @itemSelect="(option) => { filterData.selectedOrganizationalUnit = option.value; }"
                  type="autocomplete"
                  name="organizationalUnitShortCode"
                  dropdown 
                  forceSelection
                  >
                </form-input>
              </div>
              <div>
                <form-input
                  v-model="selectedSemester"
                  :label="$capitalize($p.t('lehre/studiensemester'))"
                  :suggestions="filteredSemesters"
                  :optionValue="(option) => option.value"
                  :optionLabel="(option) => option.label"
                  @complete="filterSemesters($event)"
                  type="autocomplete"
                  name="selectedSemester"
                  dropdown 
                  forceSelection
                  >
                </form-input>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2">
                  <form-input
                    v-model="filterData.validityPeriodFrom"
                    :label="$p.t('ui', 'validityPeriod') + ' ' + $p.t('ui', 'von')"
                    :teleport="true"
                    :enable-time-picker="false"
                    type="datePicker"
                    name="validityPeriodFrom"  
                    format="dd.MM.yyyy"
                    auto-apply
                    />
                    <form-input
                      v-model="filterData.validityPeriodTo"
                      :label="$p.t('ui', 'validityPeriod') + ' ' + $p.t('global', 'bis')"
                      :teleport="true"
                      :enable-time-picker="false"
                      type="datePicker"
                      name="validityPeriodTo"  
                      format="dd.MM.yyyy"
                      auto-apply
                      />
                </div>
              </div>
            </core-form>
          </slot>
        </template>
      </core-filter-cmpt>
    </div>
  </div>
  `,
};
