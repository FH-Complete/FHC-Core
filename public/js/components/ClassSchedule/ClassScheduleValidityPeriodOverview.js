import { CoreFilterCmpt } from "../filter/Filter.js";
import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import ClassScheduleValidityPeriodForm from "./ClassScheduleValidityPeriodForm.js";
import ClassScheduleValidityPeriodModal from "./ClassScheduleValidityPeriodModal.js";

export default {
  name: "ClassScheduleValidityPeriodOverview",
  components: {
    BsModal,
    CoreForm,
    FormInput,
    CoreFilterCmpt,
    ClassScheduleValidityPeriodForm,
    ClassScheduleValidityPeriodModal,
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
        console.error(
          "Error fetching class time slot validity period:",
          getClassTimeValidityPeriodResponse.meta.message,
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
        console.error(
          "Error fetching class time slots for validity period:",
          this.classTimeSlots,
        );
      }
    },
    showClassTimeSlotForm() {
      this.isClassTimeSlotFormVisible = true;
    },
    async editClassTimeSlotsForValidityPeriodPerGroup(groupIdentifikator) {
      console.log("Editing class time slots for group:", groupIdentifikator);
      await this.fetchClassTimeSlots();
      console.log(
        this.classTimeSlots.filter(
          (group) => group.groupIdentifikator === groupIdentifikator,
        ),
      );
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
      console.log(this.editedClassTimeSlots);
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
          console.error("Error deleting class time slots per group:", error);
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
          console.error(
            "Error deleting class time slot validity period:",
            error,
          );
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
      console.error(
        "Error fetching class time slot types:",
        getAllClassTimeSlotTypesResponse.meta.message,
      );
    }

    this.fetchClassTimeSlots();
  },
  template: `
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
      <p v-html="classTimeSlotValidityPeriod?.anmerkung"></p>
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
          <div v-for="(classTimeSlotsPerWeek, index) in classTimeSlots" :key="index" class="row border-top rounded p-2 mt-4 mb-2">
            <div class="col-12 d-flex align-items-center justify-content-end gap-2">
                <a class="ml-auto" @click="editClassTimeSlotsForValidityPeriodPerGroup(classTimeSlotsPerWeek.groupIdentifikator)"><i class="fa fa-edit fs-5"></i></a>
                <a class="ml-auto" @click="deleteClassTimeSlotsForValidityPeriodPerGroup(classTimeSlotsPerWeek.groupIdentifikator)"><i class="fa fa-trash text-danger fs-5"></i></a>
            </div>
            <div class="col">
                <h3>Monday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 1)" :key="innerIndex" class="d-flex align-items-center mb-2">
                <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
            <div class="col">
                <h3>Tuesday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 2)" :key="innerIndex" class="d-flex align-items-center mb-2">
                <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
            <div class="col">
                <h3>Wednesday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 3)" :key="innerIndex" class="d-flex align-items-center mb-2">
                 <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
            <div class="col">
                <h3>Thursday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 4)" :key="innerIndex" class="d-flex align-items-center mb-2">
                 <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
            <div class="col">
                <h3>Friday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 5)" :key="innerIndex" class="d-flex align-items-center mb-2">
                 <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
            <div class="col">
                <h3>Saturday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 6)" :key="innerIndex" class="d-flex align-items-center mb-2">
                 <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
            <div class="col">
                <h3>Sunday</h3>
                <div v-for="(classTimeSlot, innerIndex) in classTimeSlotsPerWeek.slots.filter(slot => slot.wochentag === 7)" :key="innerIndex" class="d-flex align-items-center mb-2">
                 <p 
                  class='p-2 m-0 rounded'
                  :style="{backgroundColor: getClassTimeSlotBackgroundColor(classTimeSlot)}"
                  :title="getClassTimeSlotType(classTimeSlot) ? getClassTimeSlotType(classTimeSlot).bezeichnung_mehrsprachig.find(desc => desc.lang === 'de').value : ''"
                  >
                    {{ classTimeSlot.uhrzeit_von }} - {{ classTimeSlot.uhrzeit_bis }}
                </p>
                </div>
            </div>
          </div>
      </div>
      <div v-else class="d-flex align-items-center justify-content-center border rounded p-4 mt-4">
          <p>{{ $p.t("ui", "noClassScheduleValidityPeriodTimeSlotsFound") }}</p>
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
