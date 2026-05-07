import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";

import BsModal from "../Bootstrap/Modal.js";
import ClassScheduleValidityPeriodForm from "./ClassScheduleValidityPeriodForm.js";
import ClassScheduleValidityPeriodModal from "./ClassScheduleValidityPeriodModal.js";
import ClassScheduleCalendarSelector from "./ClassScheduleCalendarSelector.js";

export default {
  name: "ClassScheduleValidityPeriodOverview",
  components: {
    BsModal,
    ClassScheduleValidityPeriodForm,
    ClassScheduleValidityPeriodModal,
    ClassScheduleCalendarSelector,
  },
  data: () => {
    return {
      isClassTimeSlotFormVisible: false,
      classTimeSlotValidityPeriodId: null,
      classTimeSlotValidityPeriod: null,
      classTimeSlots: [],
      classTimeSlotTypes: [],
      editedClassTimeSlots: [],
      isClassTimeSlotValidityPeriodModalVisible: false,
      editedClassTimeSlotValidityPeriodId: null,
    };
  },
  computed: {
    classScheduleValidityPeriodStartDate() {
      if (!this.classTimeSlotValidityPeriod) return null;

      let dateParts = this.classTimeSlotValidityPeriod.gueltig_von
        .split("-")
        .reverse();
      return dateParts.join("/");
    },
    classScheduleValidityPeriodEndDate() {
      if (!this.classTimeSlotValidityPeriod) return null;
      let dateParts = this.classTimeSlotValidityPeriod.gueltig_bis
        .split("-")
        .reverse();
      return dateParts.join("/");
    },
  },
  methods: {
    async fetchClassTimeValidityPeriod() {
      let getClassTimeValidityPeriodResponse = await this.$api.call(
        ApiClassSchedule.getClassTimeValidityPeriod(
          this.classTimeSlotValidityPeriodId,
        ),
      );
      if (getClassTimeValidityPeriodResponse.meta.status === "success") {
        this.classTimeSlotValidityPeriod =
          getClassTimeValidityPeriodResponse.data[0];

        if (!this.classTimeSlotValidityPeriod) {
          this.$fhcAlert.alertError(
            this.$p.t("ui", "classTimeSlotValidityPeriodNotFound"),
          );
          this.$router.push({ name: "overview" });
        }
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "errorFetchingClassScheduleValidityPeriod"),
        );
      }
    },
    async fetchClassTimeSlots() {
      let getClassTimeSlotsForValidityPeriodResponse = await this.$api.call(
        ApiClassSchedule.getClassTimeSlotsForValidityPeriod(
          this.classTimeSlotValidityPeriodId,
        ),
      );
      if (
        getClassTimeSlotsForValidityPeriodResponse.meta.status === "success"
      ) {
        let classTimeSlotsGroupedByWeek = [];
        getClassTimeSlotsForValidityPeriodResponse.data.forEach((slot) => {
          let groupIdentifikator = slot["unterrichtszeit_gruppe_identifikator"];
          let existingGroup = classTimeSlotsGroupedByWeek.find(
            (group) => group.groupIdentifikator === groupIdentifikator,
          );
          if (existingGroup) {
            existingGroup.slots.push(slot);
          } else {
            classTimeSlotsGroupedByWeek.push({
              groupIdentifikator,
              slots: [slot],
            });
          }
        });
        this.classTimeSlots = classTimeSlotsGroupedByWeek;
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "errorFetchingClassScheduleTimeSlotForValidityPeriod"),
        );
      }
    },
    showClassTimeSlotForm() {
      this.isClassTimeSlotFormVisible = true;
    },
    async editClassTimeSlotsForValidityPeriodPerGroup(groupIdentifikator) {
      await this.fetchClassTimeSlots();

      this.editedClassTimeSlots =
        this.classTimeSlots
          .filter(
            (group) => group.groupIdentifikator === groupIdentifikator,
          )?.[0]
          .slots.map((slot) => {
            return {
              ...slot,
              id: slot.unterrichtszeit_id,
              startTime: slot.uhrzeit_von,
              endTime: slot.uhrzeit_bis,
              classTimeSlotTypeShortcode: slot.unterrichtszeitentyp_kurzbz,
            };
          }) || [];

      this.showClassTimeSlotForm();
    },
    deleteClassTimeSlotsForValidityPeriodPerGroup(groupIdentifikator) {
      let isDeletionConfirmed = confirm(
        this.$p.t("ui", "confirmDeleteClassTimeSlotsForGroup"),
      );
      if (!isDeletionConfirmed) {
        return;
      }

      return this.$api
        .call(
          ApiClassSchedule.deleteClassTimeSlotsForValidityPeriodPerGroup(
            this.id,
            this.classTimeSlotValidityPeriodId,
            groupIdentifikator,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successDelete"));
          this.classTimeSlots = [];
          this.fetchClassTimeSlots();
        })
        .catch((error) => {
          this.$fhcAlert.handleSystemError(error);
        });
    },
    getClassTimeSlotType(classTimeSlot) {
      let classTimeSlotType = this.classTimeSlotTypes.find(
        (type) =>
          type.unterrichtszeitentyp_kurzbz ===
          classTimeSlot.unterrichtszeitentyp_kurzbz,
      );

      return classTimeSlotType;
    },
    getClassTimeSlotBackgroundColor(classTimeSlot) {
      let classTimeSlotType = this.classTimeSlotTypes.find(
        (type) =>
          type.unterrichtszeitentyp_kurzbz ===
          classTimeSlot.unterrichtszeitentyp_kurzbz,
      );

      return classTimeSlotType ? classTimeSlotType.hintergrundfarbe : "#fff";
    },
    editClassTimeSlotValidityPeriod(classTimeSlotValidityPeriodId) {
      this.editedClassTimeSlotValidityPeriodId = classTimeSlotValidityPeriodId;
    },
    deleteClassTimeSlotValidityPeriod(classTimeSlotValidityPeriodId) {
      let isDeletionConfirmed = confirm(
        this.$p.t("ui", "deleteClassTimeSlotValidityPeriodConfirmation"),
      );
      if (!isDeletionConfirmed) return;

      return this.$api
        .call(
          ApiClassSchedule.deleteClassTimeSlotValidityPeriod(
            this.id,
            classTimeSlotValidityPeriodId,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successDelete"));
          this.$router.push({ name: "overview" });
        })
        .catch((error) => {
          this.$fhcAlert.handleSystemError(error);
        });
    },
    resetClassTimeSlotValidityPeriodModal() {
      this.isClassTimeSlotValidityPeriodModalVisible = false;
    },
  },
  async created() {
    this.classTimeSlotValidityPeriodId =
      this.$route.params.classTimeSlotValidityPeriodId;

    this.fetchClassTimeValidityPeriod();

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
      this.$fhcAlert.alertError(
        this.$p.t("ui", "errorFetchingClassScheduleTimeSlotTypes"),
      );
    }

    this.fetchClassTimeSlots();
  },
  template: /* html */ `
   <div class="container mt-4">
    <div class='mb-5'>
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center gap-3">
          <h1 class='m-0'>
            {{ $p.t("ui", "classScheduleValidityPeriodOverviewHeading") }}
          </h1>
          <span class="m-0 badge bg-secondary">{{ classTimeSlotValidityPeriod?.orgform_kurzbz }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a 
            @click="editClassTimeSlotValidityPeriod(classTimeSlotValidityPeriodId)" 
            class="btn btn-link fs-3 p-0">
            <i class="fa fa-edit"></i></a>
          <a @click="deleteClassTimeSlotValidityPeriod(classTimeSlotValidityPeriodId)" class="btn btn-link text-danger fs-3 p-0"><i class="fa fa-trash"></i></a>
        </div>
      </div>
      <h2>{{ classScheduleValidityPeriodStartDate }} - {{ classScheduleValidityPeriodEndDate }}</h2>
      <h4 class="text-capitalize">{{ $p.t("lehre", "organisationseinheit") }}: {{ classTimeSlotValidityPeriod?.oe_bezeichnung }} ({{ classTimeSlotValidityPeriod?.oe_organisationseinheittyp_kurzbz }})</h4>
      <h4 class="text-capitalize">{{ $p.t("lehre", "studienplan") }}: {{ classTimeSlotValidityPeriod?.studienplan_bezeichnung }}</h4>
      <h5 class="text-capitalize">{{ $p.t("lehre", "ausbildungssemester") }}: 
        <span class="fw-normal">{{ classTimeSlotValidityPeriod?.ausbildungssemester }}</span>
      </h5>
      <h5 class="text-capitalize">{{ $p.t("global", "anmerkung") }}:
        <span class="fw-normal">{{ classTimeSlotValidityPeriod?.anmerkung }}</span>
      </h5>
    </div>
    <div>
      <div v-if='!isClassTimeSlotFormVisible' class="col-12 d-flex justify-content-end">
        <button type="button" class="btn btn-primary" @click="showClassTimeSlotForm">{{$p.t('ui', 'addClassTimeSlotButton')}}</button>
      </div>
      <class-schedule-validity-period-form 
        v-else 
        :class-time-slot-validity-period="classTimeSlotValidityPeriod"
        :edited-class-time-slots="editedClassTimeSlots"
        @classTimeSlotsCreated="() => { isClassTimeSlotFormVisible = false; fetchClassTimeSlots(); this.editedClassTimeSlots = []; }" 
        @classTimeSlotsEdited="() => { isClassTimeSlotFormVisible = false; fetchClassTimeSlots(); this.editedClassTimeSlots = []; }"
        @hideForm="() => { isClassTimeSlotFormVisible = false; this.editedClassTimeSlots = []; }"
        class="mb-4"
      />
      <div>
        <h4>{{ $p.t("ui", "classScheduleValidityPeriodTimeSlots") }}</h4>
      </div>
      <div v-if="classTimeSlots && Object.keys(classTimeSlots).length > 0">
        <div v-for="(classTimeSlotsPerWeek, index) in classTimeSlots" :key="index" class="row border-top rounded p-2 mt-4 mb-2 pt-1 pb-5">
          <div class="col-12 d-flex align-items-center justify-content-end gap-2">
              <a class="ml-auto" @click="editClassTimeSlotsForValidityPeriodPerGroup(classTimeSlotsPerWeek.groupIdentifikator)"><i class="fa fa-edit fs-5"></i></a>
              <a class="ml-auto" @click="deleteClassTimeSlotsForValidityPeriodPerGroup(classTimeSlotsPerWeek.groupIdentifikator)"><i class="fa fa-trash text-danger fs-5"></i></a>
          </div>
          <class-schedule-calendar-selector
            :class-time-slot-types="this.classTimeSlotTypes" 
            :edited-overlays="classTimeSlotsPerWeek.slots.map((slot) => {
              return {
                databaseId: slot.id,
                id: slot.identifier,
                weekday: slot.wochentag,
                type: slot.unterrichtszeitentyp_kurzbz,
                startTime: slot.uhrzeit_von,
                endTime: slot.uhrzeit_bis,
              };
            })"
            :isPreviewMode="true"
          />
        </div>
      </div>
      <div v-else class="d-flex align-items-center justify-content-center border rounded p-4 mt-4">
          <p class="m-0">{{ $p.t("ui", "noClassScheduleValidityPeriodTimeSlotsFound") }}</p>
      </div>
    </div>
    <class-schedule-validity-period-modal 
      :isVisible="isClassTimeSlotValidityPeriodModalVisible" 
      :editedClassTimeSlotValidityPeriodId="editedClassTimeSlotValidityPeriodId"
      @hideBsModal="() => { resetClassTimeSlotValidityPeriodModal(); editedClassTimeSlotValidityPeriodId = null; }"
      @classTimeSlotValidityPeriodUpdated="() => { 
        resetClassTimeSlotValidityPeriodModal();
        this.editedClassTimeSlotValidityPeriodId = null;
        fetchClassTimeValidityPeriod();
      }"
    />
</div>
  `,
};
