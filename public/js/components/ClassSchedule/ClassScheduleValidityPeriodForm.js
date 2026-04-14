import { CoreFilterCmpt } from "../filter/Filter.js";
import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import ClassScheduleValidityPeriodFormTimeSlot from "./ClassScheduleValidityPeriodFormTimeSlot.js";

export default {
  name: "ClassScheduleValidityPeriodForm",
  components: {
    BsModal,
    CoreForm,
    FormInput,
    CoreFilterCmpt,
    ClassScheduleValidityPeriodFormTimeSlot,
  },
  props: {
    classTimeSlotValidityPeriod: {
      type: Object,
      required: true,
    },
    editedClassTimeSlots: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  emits: ["hideForm", "classTimeSlotsCreated", "classTimeSlotsEdited"],
  watch: {
    editedClassTimeSlots: {
      handler(newVal) {
        console.log("editedClassTimeSlots changed:", newVal);
        this.classTimeSlots = JSON.parse(JSON.stringify(newVal));
        console.log(
          "classTimeSlots updated from editedClassTimeSlots:",
          this.classTimeSlots,
        );
      },
      immediate: true,
    },
  },
  data: () => {
    return {
      classTimeSlots: [],
      classTimeSlotTypes: [],
    };
  },
  computed: {
    areClassTimeSlotsEdited() {
      return this.editedClassTimeSlots && this.editedClassTimeSlots.length > 0;
    },
  },
  methods: {
    hideForm() {
      this.$emit("hideForm");
      this.classTimeSlots = [];
    },
    addClassTimeSlotPerDay(day) {
      this.classTimeSlots.push({
        identifier: Math.random().toString(36).substr(2, 9),
        wochentag: this.getWeekdayNumberFromDay(day),
        startTime: "08:00",
        endTime: "10:00",
        classTimeSlotTypeShortcode: null,
      });
    },
    removeClassTimeSlotPerDay(day, identifier) {
      this.classTimeSlots = this.classTimeSlots.filter(
        (slot) =>
          !(
            slot.wochentag === this.getWeekdayNumberFromDay(day) &&
            slot.identifier === identifier
          ),
      );
    },
    createClassTimeSlots() {
      return this.$refs.classTimeSlotData
        .call(
          ApiClassSchedule.createClassTimeSlotsForValidityPeriod(
            this.id,
            this.$props.classTimeSlotValidityPeriod
              .unterrichtszeitengueltigkeit_id,
            {
              classTimeSlots: this.classTimeSlots,
            },
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.classTimeSlots = [];
          this.$emit("classTimeSlotsCreated");
        })
        .catch((error) => {
          console.error(
            "Error creating class time slot validity period:",
            error,
          );
          this.$fhcAlert.handleSystemError(error);
        });
    },
    editClassTimeSlots() {
      return this.$refs.classTimeSlotData
        .call(
          ApiClassSchedule.editClassTimeSlotsForValidityPeriod(
            this.id,
            this.$props.classTimeSlotValidityPeriod
              .unterrichtszeitengueltigkeit_id,
            {
              classTimeSlots: this.classTimeSlots,
            },
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.classTimeSlots = [];
          this.$emit("classTimeSlotsEdited");
        })
        .catch((error) => {
          console.error(
            "Error editing class time slot validity period:",
            error,
          );
          this.$fhcAlert.handleSystemError(error);
        });
    },
    getWeekdayNumberFromDay(day) {
      const days = {
        monday: 1,
        tuesday: 2,
        wednesday: 3,
        thursday: 4,
        friday: 5,
        saturday: 6,
        sunday: 7,
      };
      return days[day.toLowerCase()] || null;
    },
  },
  async created() {
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
  <core-form ref="classTimeSlotData" class="shadow rounded p-2 row g-3">	
    <div class="d-flex gap-2">
      <div class="col">
        <div class="d-flex align-items-center gap-2">
          <h3>Monday</h3>
          <a class="ml-auto" @click="addClassTimeSlotPerDay('monday')"><i class="fa fa-plus-circle fs-4"></i></a>
        </div>
        <class-schedule-validity-period-form-time-slot
          v-for="(timeSlot, index) in classTimeSlots.filter(slot => slot.wochentag === 1)" 
          :key="index" 
          :class-time-slot-types="classTimeSlotTypes"
          @removeClassTimeSlot="removeClassTimeSlotPerDay('monday', timeSlot.identifier)"
          v-model="timeSlot"
        />
      </div>
      <div class="col">
        <div class="d-flex align-items-center gap-2">
          <h3>Tuesday</h3>
          <a class="ml-auto" @click="addClassTimeSlotPerDay('tuesday')"><i class="fa fa-plus-circle fs-4"></i></a>
        </div>
        <class-schedule-validity-period-form-time-slot
          v-for="(timeSlot, index) in classTimeSlots.filter(slot => slot.wochentag === 2)" 
          :key="index" 
          :class-time-slot-types="classTimeSlotTypes"
          @removeClassTimeSlot="removeClassTimeSlotPerDay('tuesday', timeSlot.identifier)"
          v-model="timeSlot"
        />
      </div>
      <div class="col">
        <div class="d-flex align-items-center gap-2">
          <h3>Wednesday</h3>
          <a class="ml-auto" @click="addClassTimeSlotPerDay('wednesday')"><i class="fa fa-plus-circle fs-4"></i></a>
        </div>
        <class-schedule-validity-period-form-time-slot
          v-for="(timeSlot, index) in classTimeSlots.filter(slot => slot.wochentag === 3)" 
          :key="index" 
          :class-time-slot-types="classTimeSlotTypes"
          @removeClassTimeSlot="removeClassTimeSlotPerDay('wednesday', timeSlot.identifier)"
          v-model="timeSlot"
        />
      </div>
      <div class="col">
        <div class="d-flex align-items-center gap-2">
          <h3>Thursday</h3>
          <a class="ml-auto" @click="addClassTimeSlotPerDay('thursday')"><i class="fa fa-plus-circle fs-4"></i></a>
        </div>
        <class-schedule-validity-period-form-time-slot
          v-for="(timeSlot, index) in classTimeSlots.filter(slot => slot.wochentag === 4)" 
          :key="index" 
          :class-time-slot-types="classTimeSlotTypes"
          @removeClassTimeSlot="removeClassTimeSlotPerDay('thursday', timeSlot.identifier)"
          v-model="timeSlot"
        />
      </div>
      <div class="col">
        <div class="d-flex align-items-center gap-2">
          <h3>Friday</h3>
          <a class="ml-auto" @click="addClassTimeSlotPerDay('friday')"><i class="fa fa-plus-circle fs-4"></i></a>
        </div>
        <class-schedule-validity-period-form-time-slot
          v-for="(timeSlot, index) in classTimeSlots.filter(slot => slot.wochentag === 5)" 
          :key="index" 
          :class-time-slot-types="classTimeSlotTypes"
          @removeClassTimeSlot="removeClassTimeSlotPerDay('friday', timeSlot.identifier)"
          v-model="timeSlot"
        />
      </div>
    </div>
    <div class="col-12 d-flex justify-content-end gap-2">
      <button type="button" class="btn btn-secondary" @click="hideForm">{{$p.t('ui', 'abbrechen')}}</button>
      <button v-if="!areClassTimeSlotsEdited" type="button" class="btn btn-primary" @click="createClassTimeSlots">{{$p.t('ui', 'speichern')}}</button>
      <button v-else type="button" class="btn btn-primary" @click="editClassTimeSlots">{{$p.t('ui', 'btnAktualisieren')}}</button>
    </div>
  </core-form>
  `,
};
