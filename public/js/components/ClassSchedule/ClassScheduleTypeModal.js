import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";

export default {
  name: "ClassScheduleTypeModal",
  components: {
    BsModal,
    CoreForm,
    FormInput,
  },
  inject: {
    hasLehreUnterrichtszeitenTypWPermission:
      "hasLehreUnterrichtszeitenTypWPermission",
  },
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    },
  },
  emits: [
    "hideBsModal",
    "classTimeSlotTypeCreated",
    "classTimeSlotTypeUpdated",
  ],
  watch: {
    isVisible(newValue) {
      if (newValue) {
        this.$refs.classScheduleTypeModal.show();
      } else {
        this.$refs.classScheduleTypeModal.hide();
      }
    },
  },
  data: () => {
    return {
      isFormVisible: false,
      isEditInProgress: false,
      editedClassScheduleType: null,
      classTimeSlotTypeFormData: {
        isActive: true,
        shortCode: "",
        descriptions: [
          { lang: "de", value: "" },
          { lang: "en", value: "" },
        ],
        backgroundColor: "#ffffff",
      },
      classScheduleTypes: [],
    };
  },
  methods: {
    async getAllClassScheduleTypes() {
      let getAllClassScheduleTypeResponse = await this.$api.call(
        ApiClassSchedule.getAllClassScheduleTypes(),
      );
      if (getAllClassScheduleTypeResponse.meta.status === "success") {
        this.classScheduleTypes = getAllClassScheduleTypeResponse.data.map(
          (type) => {
            let descriptions = [{
              lang: "de",
              value: type.bezeichnung_mehrsprachig[0] || "",
            }, {
              lang: "en",
              value: type.bezeichnung_mehrsprachig[1] || "",
            }];
            
            return {
              ...type,
              bezeichnung_mehrsprachig: descriptions,
            };
          },
        );
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "errorFetchingClassScheduleTimeSlotTypes"),
        );
      }
    },
    createClassTimeSlotType() {
      return this.$refs.classTimeSlotTypeForm
        .call(
          ApiClassSchedule.createClassTimeSlotType({
            isActive: this.classTimeSlotTypeFormData.isActive,
            shortCode: this.classTimeSlotTypeFormData.shortCode,
            descriptions: this.classTimeSlotTypeFormData.descriptions,
            backgroundColor: this.classTimeSlotTypeFormData.backgroundColor,
          }),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$emit("classTimeSlotTypeCreated");
          this.getAllClassScheduleTypes();
          this.resetClassTimeSlotTypeForm();
        });
    },
    editClassTimeSlotType(classScheduleType) {
      this.isEditInProgress = true;
      this.isFormVisible = true;
      this.editedClassScheduleType = classScheduleType;
      this.classTimeSlotTypeFormData = {
        isActive: classScheduleType.aktiv,
        shortCode: classScheduleType.unterrichtszeitentyp_kurzbz,
        descriptions: classScheduleType.bezeichnung_mehrsprachig.map(
          (desc) => ({
            lang: desc.lang,
            value: desc.value,
          }),
        ),
        backgroundColor: classScheduleType.hintergrundfarbe || "#ffffff",
      };
    },
    updateClassTimeSlotType() {
      return this.$refs.classTimeSlotTypeForm
        .call(
          ApiClassSchedule.updateClassTimeSlotType(
            this.classTimeSlotTypeFormData.shortCode,
            {
              isActive: this.classTimeSlotTypeFormData.isActive,
              descriptions: this.classTimeSlotTypeFormData.descriptions,
              backgroundColor: this.classTimeSlotTypeFormData.backgroundColor,
            },
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$emit("classTimeSlotTypeUpdated");
          this.getAllClassScheduleTypes();
          this.resetClassTimeSlotTypeForm();
        });
    },
    deleteClassTimeSlotType(classScheduleTypeShortCode) {
      let isConfirmed = confirm(
        this.$p.t("ui", "confirmDeleteClassTimeSlotType"),
      );
      if (!isConfirmed) {
        return Promise.resolve();
      }

      return this.$api
        .call(
          ApiClassSchedule.deleteClassTimeSlotType(
            this.id,
            classScheduleTypeShortCode,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successDelete"));
          window.scrollTo(0, 0);
          this.getAllClassScheduleTypes();
        })
        .catch((error) => {
          this.$fhcAlert.handleSystemError(error);
        });
    },
    showClassTimeSlotTypeForm() {
      this.isFormVisible = true;
    },
    hideClassTimeSlotTypeForm() {
      this.resetClassTimeSlotTypeForm();
    },
    resetClassTimeSlotTypeForm() {
      this.isEditInProgress = false;
      this.isFormVisible = false;
      this.classTimeSlotTypeFormData = {
        isActive: true,
        shortCode: "",
        descriptions: [
          { lang: "de", value: "" },
          { lang: "en", value: "" },
        ],
        backgroundColor: "#ffffff",
      };
    },
  },
  async created() {
    await this.getAllClassScheduleTypes();
  },
  template: /* html */ `
  <bs-modal ref="classScheduleTypeModal" size="md" @hideBsModal="() => { $emit('hideBsModal'); resetClassTimeSlotTypeForm(); }">
			<template #title>
				<p v-if="hasLehreUnterrichtszeitenTypWPermission"  class="fw-bold mt-3">{{$p.t('ui', 'editClassTimeSlotTypeModalTitle')}}</p>
        <p v-else class="fw-bold mt-3">{{$p.t('ui', 'existingClassScheduleTypesLabel')}}</p>
			</template>
      <div v-if="!isFormVisible && hasLehreUnterrichtszeitenTypWPermission" class="row mb-3">
        <div class="col d-flex justify-content-end">
          <button @click="showClassTimeSlotTypeForm" type="button" class="btn btn-primary">{{$p.t('global', 'create')}}</button>
        </div>
      </div>
			<core-form v-else-if="isFormVisible && hasLehreUnterrichtszeitenTypWPermission" ref="classTimeSlotTypeForm" class="row g-3 pb-3">
        <div v-if="!isEditInProgress" class="row mb-3">
					<form-input
            v-model="classTimeSlotTypeFormData.shortCode"
						:label="$p.t('ui/shortName')"
						type="text"
						name="shortCode"  
						>
					</form-input>
				</div>
        <div v-else class="row mb-3">
          <p class="mb-0"><span class="fw-bold">{{$p.t('ui/shortName')}}:</span> {{editedClassScheduleType.unterrichtszeitentyp_kurzbz}}</p>
        </div>
        <div class="row">
					<form-input
            v-for="description in classTimeSlotTypeFormData.descriptions"
            :key="description.lang"
						v-model="description.value"
						:name="description.lang"  
						:label="$p.t('ui/description') + ' (' + description.lang + ')'"
						type="textarea"
            class="mb-3"
						>
					</form-input>
				</div>
        <div class="row mb-3">
					<form-input
            v-model="classTimeSlotTypeFormData.backgroundColor"
						:label="$p.t('ui/backgroundColor')"
            type="color"
						name="backgroundColor"  
						>
					</form-input>
				</div>
        <div class="row mb-3">
          <div class="col d-flex align-items-center justify-content-end">
            <form-input
              v-model="classTimeSlotTypeFormData.isActive"
              :label="$p.t('ui/isActive')"
              type="checkbox"
              name="isActive"  
              >
            </form-input>
          </div>
				</div>
        <div class="col d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" @click="hideClassTimeSlotTypeForm">{{$p.t('ui', 'abbrechen')}}</button>
          <button type="button" class="btn btn-primary" @click="isEditInProgress ? updateClassTimeSlotType() : createClassTimeSlotType()">{{$p.t('ui', 'speichern')}}</button>
        </div>
			</core-form>
      <div class="row mb-3">
        <p v-if="hasLehreUnterrichtszeitenTypWPermission" class="fw-bold mb-2">{{$p.t('ui', 'existingClassScheduleTypesLabel')}}</p>
        <div
          >
          <div
            v-for="classScheduleType in classScheduleTypes"
            :key="classScheduleType.unterrichtszeitentyp_kurzbz"
            :value="classScheduleType.unterrichtszeitentyp_kurzbz"
            :class='{"opacity-50": !classScheduleType.aktiv}'
            class=" shadow-sm p-2 mb-2 bg-body rounded"
            >
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span 
                :style="{ backgroundColor: classScheduleType.hintergrundfarbe }"
                class="badge me-2 text-black">
                {{classScheduleType.unterrichtszeitentyp_kurzbz}}
              </span>
              <div v-if="hasLehreUnterrichtszeitenTypWPermission" class="d-flex justify-content-between align-items-center gap-2">
                <a href="#" @click.prevent="editClassTimeSlotType(classScheduleType)"><i class="fa fa-edit"></i></a>
                <a href="#" @click.prevent="deleteClassTimeSlotType(classScheduleType.unterrichtszeitentyp_kurzbz)"><i class="fa fa-trash text-danger"></i></a>
              </div>
            </div>
            <p>
              {{classScheduleType.bezeichnung_mehrsprachig[0].value}} / {{classScheduleType.bezeichnung_mehrsprachig[1].value}}
            </p>
          </div>
        </div>
      </div>
		</bs-modal>
  `,
};
