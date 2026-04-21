import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";
import ApiStudienPlan from "../../../js/api/factory/studienplan.js";
import ApiOrganizationalUnit from "../../../js/api/factory/organizationalUnit.js";

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
          this.classTimeSlotValidityPeriodFormData = {
            id: validityPeriodData.unterrichtszeitengueltigkeit_id,
            organizationalUnitShortCode: validityPeriodData.oe_kurzbz,
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
  },
  data: () => {
    return {
      isFormVisible: false,
      isEditInProgress: false,
      organizationalUnits: [],
      studyPlans: [],
      classTimeSlotTypes: [],
      classTimeSlotValidityPeriodFormData: {
        id: null,
        organizationalUnitShortCode: null,
        studyPlanId: null,
        classTimeSlotTypeShortcode: null,
        validityPeriodFrom: null,
        validityPeriodTo: null,
        semester: null,
        description: null,
      },
    };
  },
  methods: {
    createClassTimeSlotValidityPeriod() {
      return this.$refs.classTimeSlotValidityPeriodData
        .call(
          ApiClassSchedule.createClassTimeSlotValidityPeriod(
            this.id,
            this.classTimeSlotValidityPeriodFormData,
          ),
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
            this.classTimeSlotValidityPeriodFormData,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$refs.classTimeSlotValidityPeriodModal.hide();
          this.resetClassTimeSlotValidityPeriodModal();
          window.scrollTo(0, 0);
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
        organizationalUnitShortCode: null,
        studyPlanId: null,
        classTimeSlotTypeShortcode: null,
        validityPeriodFrom: null,
        validityPeriodTo: null,
        semester: null,
        description: null,
      };
    },
  },
  async created() {
    let getAllOrganizationalUnitsResponse = await this.$api.call(
      ApiOrganizationalUnit.getAllOrganizationalUnits(),
    );
    if (getAllOrganizationalUnitsResponse.meta.status === "success") {
      this.organizationalUnits = getAllOrganizationalUnitsResponse.data;
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
  template: `
  <bs-modal ref="classTimeSlotValidityPeriodModal" @hideBsModal="() => { $emit('hideBsModal'); resetClassTimeSlotValidityPeriodModal(); }" size="md">
    <template #title>
      <p v-if="!classTimeSlotValidityPeriodFormData.id" class="fw-bold mt-3">{{$p.t('ui', 'addClassTimeSlotValidityPeriodModalTitle')}}</p>
      <p v-else class="fw-bold mt-3">{{$p.t('ui', 'editClassTimeSlotValidityPeriodModalTitle')}}</p>
    </template>
    <core-form class="row g-3" ref="classTimeSlotValidityPeriodData">
      <form-validation />
      <div class="row mb-3">
        <form-input
          type="select"
          name="organizationalUnitShortCode"  
          :label="$p.t('lehre/organisationseinheit') + ' *'"
          v-model="classTimeSlotValidityPeriodFormData.organizationalUnitShortCode"
          >
          <option
            v-for="organizationalUnit in organizationalUnits"
            :key="organizationalUnit.oe_kurzbz"
            :value="organizationalUnit.oe_kurzbz"
            >
            {{organizationalUnit.bezeichnung}}
          </option>
        </form-input>
      </div>
      <div class="row mb-3">
        <form-input
          type="select"
          name="studyPlan"  
          :label="$p.t('lehre/studienplan')"
          v-model="classTimeSlotValidityPeriodFormData.studyPlanId"
          >
          <option
            v-for="studyPlan in studyPlans"
            :key="studyPlan.studienplan_id"
            :value="studyPlan.studienplan_id"
            >
            {{studyPlan.orgform_kurzbz}} - {{studyPlan.sprache}} - {{studyPlan.semesterwochen}}
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
          <option
            v-for="classTimeSlotType in classTimeSlotTypes"
            :key="classTimeSlotType.unterrichtszeitentyp_kurzbz"
            :value="classTimeSlotType.unterrichtszeitentyp_kurzbz"
            >
            {{classTimeSlotType.unterrichtszeitentyp_kurzbz}} - {{classTimeSlotType.bezeichnung_mehrsprachig[0].value}} / ({{classTimeSlotType.bezeichnung_mehrsprachig[1].value}})
          </option>
        </form-input>
      </div>
      <div class="row mb-3">
        <div class="col-12 mb-3">
          <label>{{$p.t('ui', 'validityPeriod')}}</label>
        </div>
        <div class="col">
          <form-input
            type="date"
            name="validityPeriodFrom"  
            :label="$p.t('ui/von') + ' *'"
            v-model="classTimeSlotValidityPeriodFormData.validityPeriodFrom"
            />
        </div>
        <div class="col">
          <form-input
            type="date"
            name="validityPeriodTo"  
            :label="$p.t('global/bis') + ' *'"
            v-model="classTimeSlotValidityPeriodFormData.validityPeriodTo"
            />
        </div>
      </div>
      <div class="row mb-3">
        <form-input
          type="select"
          id="ausbildungssemester"
          name="semester"
          :label="$p.t('lehre', 'ausbildungssemester')+ '*'"
          v-model="classTimeSlotValidityPeriodFormData.semester"
          >
          <option v-for="sem in Array.from({length:8}).map((u,i) => i+1)" :key="sem" :value="sem">{{sem}}. Semester</option>
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
