import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";
import ApiStudienPlan from "../../../js/api/factory/studienplan.js";
import ApiStudienSemester from "../../../js/api/factory/studiensemester.js";
import ApiOrganizationalUnit from "../../../js/api/factory/organizationalUnit.js";
import { formatDate } from "../../helpers/DateHelpers.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import FormValidation from "../Form/Validation.js";

export default {
  name: "ClassScheduleValidityPeriodModal",
  components: {
    BsModal,
    CoreForm,
    FormInput,
    FormValidation,
  },
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    },
    editedClassTimeSlotValidityPeriodId: {
      type: Number,
      default: null,
    },
  },
  emits: [
    "hideBsModal",
    "classTimeSlotValidityPeriodCreated",
    "classTimeSlotValidityPeriodUpdated",
  ],
  watch: {
    isVisible(newValue) {
      if (newValue) {
        this.$refs.classTimeSlotValidityPeriodModal.show();
      } else {
        this.$refs.classTimeSlotValidityPeriodModal.hide();
      }
    },
    editedClassTimeSlotValidityPeriodId(newValue) {
      if (!newValue) return;

      return this.$api
        .call(ApiClassSchedule.getClassTimeValidityPeriod(newValue))
        .then((response) => {
          let validityPeriodData = response.data[0];
          let organizationalUnit = this.organizationalUnits.find(
            (unit) => unit.oe_kurzbz === validityPeriodData.oe_kurzbz,
          );
          if (!organizationalUnit) {
            console.error(
              "Organizational unit not found for validity period:",
              validityPeriodData,
            );
            this.$fhcAlert.alertError(this.$p.t("ui", "errorLoadingData"));
            return;
          }

          this.editedClassTimeSlotValidityPeriod = validityPeriodData;

          this.classTimeSlotValidityPeriodFormData = {
            id: validityPeriodData.unterrichtszeitengueltigkeit_id,
            organizationalUnit,
            studyPlanId: validityPeriodData.studienplan_id,
            classTimeSlotTypeShortcode:
              validityPeriodData.unterrichtszeitentyp_kurzbz,
            validityPeriodFrom: validityPeriodData.gueltig_von,
            validityPeriodTo: validityPeriodData.gueltig_bis,
            semester: validityPeriodData.ausbildungssemester,
            description: validityPeriodData.anmerkung,
          };
          this.$refs.classTimeSlotValidityPeriodModal.show();
        })
        .catch((error) => {
          console.error(
            "Error fetching class time slot validity period details:",
            error,
          );
          this.$fhcAlert.handleSystemError(error);
        });
    },
    "classTimeSlotValidityPeriodFormData.organizationalUnit"() {
      this.updateClassTimeSlotValidityPeriodFormDataWatcher();
    },
    "classTimeSlotValidityPeriodFormData.validityPeriodFrom"() {
      this.updateClassTimeSlotValidityPeriodFormDataWatcher();
    },
    "classTimeSlotValidityPeriodFormData.validityPeriodTo"() {
      this.updateClassTimeSlotValidityPeriodFormDataWatcher();
    },
  },
  data: () => {
    return {
      editedValidityPeriod: null,
      isFormVisible: false,
      organizationalUnits: [],
      filteredOrganizationalUnits: [],
      studyPlans: [],
      studySemesters: [],
      studySemestersByNumber: [],
      classTimeSlotTypes: [],
      classTimeSlotValidityPeriodFormData: {
        id: null,
        organizationalUnit: null,
        studyPlanId: null,
        classTimeSlotTypeShortcode: null,
        validityPeriodFrom: null,
        validityPeriodTo: null,
        semester: null,
        description: null,
      },
    };
  },
  computed: {
    isEditMode() {
      return !!this.$props.editedClassTimeSlotValidityPeriodId;
    },
    isStudyPlanSelectDisabled() {
      return (
        !this.classTimeSlotValidityPeriodFormData.organizationalUnit
          ?.oe_kurzbz ||
        !this.classTimeSlotValidityPeriodFormData.validityPeriodFrom ||
        !this.classTimeSlotValidityPeriodFormData.validityPeriodTo
      );
    },
    formattedValidityPeriodFrom() {
      if (!this.classTimeSlotValidityPeriodFormData.validityPeriodFrom)
        return null;

      return formatDate(
        this.classTimeSlotValidityPeriodFormData.validityPeriodFrom,
      );
    },
    formattedValidityPeriodTo() {
      if (!this.classTimeSlotValidityPeriodFormData.validityPeriodTo)
        return null;
      return formatDate(
        this.classTimeSlotValidityPeriodFormData.validityPeriodTo,
      );
    },
    userLanguage() {
      return Vue.ref(FHC_JS_DATA_STORAGE_OBJECT.user_language);
    },
  },
  methods: {
    updateClassTimeSlotValidityPeriodFormDataWatcher() {
      if (
        !this.classTimeSlotValidityPeriodFormData.organizationalUnit
          ?.oe_kurzbz ||
        !this.classTimeSlotValidityPeriodFormData.validityPeriodFrom ||
        !this.classTimeSlotValidityPeriodFormData.validityPeriodTo
      ) {
        this.classTimeSlotValidityPeriodFormData.studyPlanId = null;
        return;
      }

      this.refetchFilterableOptions();
    },
    async refetchFilterableOptions() {
      this.studyPlans = await this.getTargetedStudyPlans(
        this.classTimeSlotValidityPeriodFormData.organizationalUnit?.oe_kurzbz,
        this.formattedValidityPeriodFrom,
        this.formattedValidityPeriodTo,
      );

      if (this.isEditMode) {
        let isStudyPlanStillValid = this.studyPlans.some(
          (plan) =>
            plan.studienplan_id ===
            this.editedClassTimeSlotValidityPeriod.studienplan_id,
        );

        if (!isStudyPlanStillValid) {
          let editedStudyPlan = await this.getStudyPlan(
            this.editedClassTimeSlotValidityPeriod.studienplan_id,
          );
          if (editedStudyPlan) {
            this.studyPlans.push(editedStudyPlan);
          } else {
            console.error(
              "Edited study plan not found:",
              this.editedClassTimeSlotValidityPeriod.studienplan_id,
            );
          }
        }
      }

      let studySemestersByDates =
        await this.getStudySemestersByOrganizationalUnitAndDates(
          this.classTimeSlotValidityPeriodFormData.organizationalUnit
            ?.oe_kurzbz,
          this.formattedValidityPeriodFrom,
          this.formattedValidityPeriodTo,
        );

      let studySemesters = new Array(
        ...new Set(
          studySemestersByDates
            .map((s) => s.semester_numbers)
            .flat()
            .sort((a, b) => a - b),
        ),
      );

      this.studySemestersByNumber = studySemesters;
      if (this.classTimeSlotValidityPeriodFormData.studyPlanId) {
        let studySemestersByStudyProgramId =
          await this.getStudySemestersByStudyPlanAndDates(
            this.classTimeSlotValidityPeriodFormData.studyPlanId,
            this.formattedValidityPeriodFrom,
            this.formattedValidityPeriodTo,
          );
        studySemestersByStudyProgramId = new Array(
          ...new Set(
            studySemestersByStudyProgramId
              .map((s) => s.semester_numbers)
              .flat()
              .sort((a, b) => a - b),
          ),
        );

        studySemesters = studySemestersByStudyProgramId;
      }

      this.studySemestersByNumber = studySemesters;
    },
    async getTargetedStudyPlans(
      organizationalUnitShortCode,
      validityPeriodFrom,
      validityPeriodTo,
    ) {
      if (
        !organizationalUnitShortCode ||
        !validityPeriodFrom ||
        !validityPeriodTo
      ) {
        return [];
      }

      let getAllStudyPlansResponse = await this.$api.call(
        ApiStudienPlan.getStudyPlansByOrganizationalUnitAndSemesterDates(
          organizationalUnitShortCode,
          validityPeriodFrom,
          validityPeriodTo,
        ),
      );
      if (getAllStudyPlansResponse.meta.status !== "success") {
        console.error(
          "Error fetching study plans:",
          getAllStudyPlansResponse.meta.message,
        );
      }

      return getAllStudyPlansResponse.data?.length
        ? getAllStudyPlansResponse.data
        : [];
    },
    async getStudyPlan(studienplan_id) {
      if (!studienplan_id) {
        return null;
      }

      let getStudyPlanResponse = await this.$api.call(
        ApiStudienPlan.getStudyPlan(studienplan_id),
      );
      if (getStudyPlanResponse.meta.status !== "success") {
        console.error(
          "Error fetching study plan details:",
          getStudyPlanResponse.meta.message,
        );
        return null;
      }

      return getStudyPlanResponse.data ? getStudyPlanResponse.data : null;
    },
    async getStudySemestersByOrganizationalUnitAndDates(
      organizationalUnitShortCode,
      validityPeriodFrom,
      validityPeriodTo,
    ) {
      if (
        !organizationalUnitShortCode ||
        !validityPeriodFrom ||
        !validityPeriodTo
      ) {
        return [];
      }

      let getStudySemestersResponse = await this.$api.call(
        ApiStudienSemester.getStudySemestersByOrganizationalUnitAndDates(
          organizationalUnitShortCode,
          validityPeriodFrom,
          validityPeriodTo,
        ),
      );
      if (getStudySemestersResponse.meta.status !== "success") {
        console.error(
          "Error fetching study semesters by organizational unit and dates:",
          getStudySemestersResponse.meta.message,
        );
      }

      return getStudySemestersResponse.data?.length
        ? getStudySemestersResponse.data
        : [];
    },
    async getStudySemestersByStudyPlanAndDates(
      studyProgramId,
      validityPeriodFrom,
      validityPeriodTo,
    ) {
      if (!studyProgramId || !validityPeriodFrom || !validityPeriodTo) {
        return [];
      }

      let getStudySemestersResponse = await this.$api.call(
        ApiStudienSemester.getStudySemestersByStudyPlanAndDates(
          studyProgramId,
          validityPeriodFrom,
          validityPeriodTo,
        ),
      );
      if (getStudySemestersResponse.meta.status !== "success") {
        console.error(
          "Error fetching study semesters:",
          getStudySemestersResponse.meta.message,
        );
      }

      return getStudySemestersResponse.data?.length
        ? getStudySemestersResponse.data
        : [];
    },
    createClassTimeSlotValidityPeriod() {
      return this.$refs.classTimeSlotValidityPeriodData
        .call(
          ApiClassSchedule.createClassTimeSlotValidityPeriod(this.id, {
            ...this.classTimeSlotValidityPeriodFormData,
            organizationalUnitShortCode:
              this.classTimeSlotValidityPeriodFormData.organizationalUnit
                ?.oe_kurzbz,
          }),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$refs.classTimeSlotValidityPeriodModal.hide();
          this.resetClassTimeSlotValidityPeriodModal();
          window.scrollTo(0, 0);
          this.$emit("classTimeSlotValidityPeriodCreated");
        })
        .catch((error) => {
          console.error(
            "Error creating class time slot validity period:",
            error,
          );
          this.$fhcAlert.handleSystemError(error);
        });
    },
    updateClassTimeSlotValidityPeriod() {
      return this.$refs.classTimeSlotValidityPeriodData
        .call(
          ApiClassSchedule.updateClassTimeSlotValidityPeriod(
            this.id,
            this.classTimeSlotValidityPeriodFormData.id,
            {
              ...this.classTimeSlotValidityPeriodFormData,
              organizationalUnitShortCode:
                this.classTimeSlotValidityPeriodFormData.organizationalUnit
                  ?.oe_kurzbz,
            },
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$refs.classTimeSlotValidityPeriodModal.hide();
          this.resetClassTimeSlotValidityPeriodModal();
          window.scrollTo(0, 0);

          this.editedClassTimeSlotValidityPeriod = null;

          this.$emit("classTimeSlotValidityPeriodUpdated");
        })
        .catch((error) => {
          console.error(
            "Error updating class time slot validity period:",
            error,
          );
          this.$fhcAlert.handleSystemError(error);
        });
    },
    resetClassTimeSlotValidityPeriodModal() {
      this.$refs.classTimeSlotValidityPeriodData?.clearValidation();
      this.classTimeSlotValidityPeriodFormData = {
        id: null,
        organizationalUnit: null,
        studyPlanId: null,
        classTimeSlotTypeShortcode: null,
        validityPeriodFrom: null,
        validityPeriodTo: null,
        semester: null,
        description: null,
      };
    },
    filterOrganizationalUnits(event) {
      const query = event.query.toLowerCase();
      if (!query) {
        return (this.filteredOrganizationalUnits = [
          ...this.organizationalUnits,
        ]);
      }

      return (this.filteredOrganizationalUnits =
        this.organizationalUnits.filter((unit) => {
          let label = `${unit.bezeichnung} (${unit.organisationseinheittyp_kurzbz})`;
          return label.toLowerCase().includes(query);
        }));
    },
    getClassTimeSlotTypeLabel(classTimeSlotType) {
      if (!classTimeSlotType) return "";
      return this.userLanguage?.value === "English"
        ? classTimeSlotType.bezeichnung_mehrsprachig[1].value
        : classTimeSlotType.bezeichnung_mehrsprachig[0].value;
    },
  },
  async created() {
    let getAllOrganizationalUnitsResponse = await this.$api.call(
      ApiOrganizationalUnit.getAllOrganizationalUnits(),
    );
    if (getAllOrganizationalUnitsResponse.meta.status === "success") {
      this.organizationalUnits = getAllOrganizationalUnitsResponse.data.sort(
        (a, b) => a.bezeichnung.localeCompare(b.bezeichnung),
      );
    } else {
      console.error(
        "Error fetching organizational units:",
        getAllOrganizationalUnitsResponse.meta.message,
      );
    }

    let getAllStudyPlansResponse = await this.$api.call(
      ApiStudienPlan.getAllStudyPlans(),
    );
    if (getAllStudyPlansResponse.meta.status === "success") {
      this.studyPlans = getAllStudyPlansResponse.data;
    } else {
      console.error(
        "Error fetching study plans:",
        getAllStudyPlansResponse.meta.message,
      );
    }

    let getAllClassTimeSlotTypesResponse = await this.$api.call(
      ApiClassSchedule.getAllClassScheduleTypes("filter[aktiv]=true"),
    );
    if (getAllClassTimeSlotTypesResponse.meta.status === "success") {
      this.classTimeSlotTypes = getAllClassTimeSlotTypesResponse.data.map(
        (type) => {
          let descriptions = [];
          for (let item of type.bezeichnung_mehrsprachig) {
            let [lang, value] = item.split(":");
            descriptions.push({ lang, value });
          }
          return {
            ...type,
            bezeichnung_mehrsprachig: descriptions,
          };
        },
      );
    } else {
      console.error(
        "Error fetching class time slot types:",
        getAllClassTimeSlotTypesResponse.meta.message,
      );
    }
  },
  template: /*html*/ `
  <bs-modal ref="classTimeSlotValidityPeriodModal" @hideBsModal="() => { $emit('hideBsModal'); resetClassTimeSlotValidityPeriodModal(); }" size="md">
    <template #title>
      <p v-if="!classTimeSlotValidityPeriodFormData.id" class="fw-bold mt-3">{{$p.t('ui', 'addClassTimeSlotValidityPeriodModalTitle')}}</p>
      <p v-else class="fw-bold mt-3">{{$p.t('ui', 'editClassTimeSlotValidityPeriodModalTitle')}}</p>
    </template>
    <core-form class="row g-3" ref="classTimeSlotValidityPeriodData">
      <form-validation />
      <div class="row mb-3">
        <form-input
          v-model="classTimeSlotValidityPeriodFormData.organizationalUnit"
          :label="$capitalize($p.t('lehre/organisationseinheit')) + ' *'"
          :suggestions="filteredOrganizationalUnits"
          :optionValue="(option) => option.kurzbz"
          :optionLabel="(option) => option.bezeichnung + ' (' + option.organisationseinheittyp_kurzbz + ')'" 
          @complete="filterOrganizationalUnits"
          dropdown
          forceSelection
          type="autocomplete"
          name="organizationalUnitShortCode"  
          >
        </form-input>
      </div>
      <div class="row mb-3">
        <div class="col-12 mb-3">
          <label>{{$p.t('ui', 'validityPeriod')}}</label>
        </div>
        <div class="col">
          <form-input
            v-model="classTimeSlotValidityPeriodFormData.validityPeriodFrom"
            :label="$p.t('ui/von') + ' *'"
						:teleport="true"
						:enable-time-picker="false"
            type="datePicker"
            name="validityPeriodFrom"  
            format="dd.MM.yyyy"
            auto-apply
            />
        </div>
        <div class="col">
          <form-input
            v-model="classTimeSlotValidityPeriodFormData.validityPeriodTo"
            :label="$p.t('global/bis') + ' *'"
            :teleport="true"
						:enable-time-picker="false"
            type="datePicker"
            name="validityPeriodTo"  
            format="dd.MM.yyyy"
            auto-apply
            />
        </div>
      </div>
      <div class="row mb-3">
        <form-input
          v-model="classTimeSlotValidityPeriodFormData.studyPlanId"
          :label="$p.t('lehre/studienplan')"
          :disabled="isStudyPlanSelectDisabled"
          type="select"
          name="studyPlan"  
          >
          <option :value="null"> - </option>
          <option
            v-for="studyPlan in studyPlans"
            :key="studyPlan.studienplan_id"
            :value="studyPlan.studienplan_id"
            >
            {{studyPlan.bezeichnung}}
          </option>
        </form-input>
      </div>
      <div class="row mb-3">
        <form-input
          type="select"
          id="ausbildungssemester"
          name="semester"
          :disabled="isStudyPlanSelectDisabled"
          :label="$p.t('lehre', 'ausbildungssemester')+ '*'"
          v-model="classTimeSlotValidityPeriodFormData.semester"
          >
          <option
            v-for="studySemester in studySemestersByNumber"
            :key="studySemester"
            :value="studySemester"
            >
            {{studySemester}}
          </option>
        </form-input>
      </div>
      <div class="row mb-3">
        <form-input
          type="select"
          name="classTimeSlotType"  
          :label="$p.t('ui/classTimeSlotType')"
          v-model="classTimeSlotValidityPeriodFormData.classTimeSlotTypeShortcode"
          >
          <option :value="null"> - </option>
          <option
            v-for="classTimeSlotType in classTimeSlotTypes"
            :key="classTimeSlotType.unterrichtszeitentyp_kurzbz"
            :value="classTimeSlotType.unterrichtszeitentyp_kurzbz"
            >
            {{ getClassTimeSlotTypeLabel(classTimeSlotType) }}
          </option>
        </form-input>
      </div>
      <div class="row mb-3">
        <form-input
          type="textarea"
          name="beschreibung"  
          :label="$p.t('global/beschreibung')"
          v-model="classTimeSlotValidityPeriodFormData.description"
          >
        </form-input>
      </div>
    </core-form>
    
    <template #footer>
      <button v-if="!classTimeSlotValidityPeriodFormData.id" type="button" class="btn btn-primary" @click="createClassTimeSlotValidityPeriod">{{$p.t('ui', 'speichern')}}</button>
      <button v-else type="button" class="btn btn-primary" @click="updateClassTimeSlotValidityPeriod">{{$p.t('ui', 'btnAktualisieren')}}</button>
    </template>
  </bs-modal>
  `,
};
