import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";
import ApiClassroomHour from "../../../js/api/factory/classroomHour.js";

import ClassScheduleCalendarSelector from "./ClassScheduleCalendarSelector.js";

export default {
  name: "ClassScheduleValidityPeriodForm",
  components: {
    ClassScheduleCalendarSelector,
  },
  props: {
    classTimeSlotTypes: {
      type: [Array, null],
      required: true,
    },
    classTimeSlotValidityPeriod: {
      type: Object,
      required: true,
    },
    editedClassTimeSlots: {
      type: Array,
      required: false,
      default: () => [],
    },
    classroomHours: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  emits: ["hideForm", "classTimeSlotsCreated", "classTimeSlotsEdited"],
  watch: {
    editedClassTimeSlots: {
      handler(newVal) {
        this.editedOverlays = newVal.map((slot) => {
          return {
            databaseId: slot.id,
            id: slot.identifier,
            weekday: slot.wochentag === 0 ? 7 : slot.wochentag,
            type: slot.unterrichtszeitentyp_kurzbz,
            startTime: slot.startTime,
            endTime: slot.endTime,
          };
        });
      },
      immediate: true,
    },
  },
  data: () => {
    return {
      editedOverlays: [],
      classTimeSlots: [],
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
    async createClassTimeSlots() {
      let response = await this.$api.call(
        ApiClassSchedule.createClassTimeSlotsForValidityPeriod(
          this.id,
          this.$props.classTimeSlotValidityPeriod
            .unterrichtszeitengueltigkeit_id,
          {
            unterrichtszeiten: this.classTimeSlots.map((slot) => {
              slot.wochentag = parseInt(slot.wochentag) === 7 ? 0 : slot.wochentag;
              return slot; 
            }),
          },
        ),
      );
      if (response.meta.status === "success") {
        this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
        this.classTimeSlots = [];
        this.$emit("classTimeSlotsCreated");
      } else {
        this.$fhcAlert.handleSystemError(response.meta.message);
      }
    },
    async updateClassTimeSlots() {
      let response = await this.$api.call(
        ApiClassSchedule.updateClassTimeSlotsForValidityPeriod(
          this.id,
          this.$props.classTimeSlotValidityPeriod
            .unterrichtszeitengueltigkeit_id,
          {
            unterrichtszeiten: this.classTimeSlots.map((slot) => {
              slot.wochentag = parseInt(slot.wochentag) === 7 ? 0 : slot.wochentag;
              return slot;
            }),
          },
        ),
      );
      if (response.meta.status === "success") {
        this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
        this.classTimeSlots = [];
        this.$emit("classTimeSlotsEdited");
      } else {
        this.$fhcAlert.handleSystemError(response.meta.message);
      }
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
    handleOverlaysChanged(newOverlays) {
      this.classTimeSlots = newOverlays.map((overlay) => {
        return {
          id: overlay.databaseId || null,
          identifier: overlay.id,
          wochentag: overlay.weekday,
          startTime: overlay.startingTimeSlot.split("-")[0],
          endTime: overlay.endingTimeSlot.split("-")[1],
          classTimeSlotTypeShortcode: overlay.type,
        };
      });
    },
  },
  template: `
  <div class='row'>
    <div class='col-12'>
      <class-schedule-calendar-selector
        :classroom-hours="this.$props.classroomHours"
        :default-class-time-slot-type="this.$props.classTimeSlotTypes.find(type => type.is_default)"
        :class-time-slot-types="this.$props.classTimeSlotTypes" 
        :edited-overlays="this.editedOverlays"
        @overlaysChanged="handleOverlaysChanged"
      />
    </div>
    <div class="col-12 d-flex justify-content-end gap-2">
      <button type="button" class="btn btn-secondary" @click="hideForm">{{$p.t('ui', 'abbrechen')}}</button>
      <button v-if="!areClassTimeSlotsEdited" type="button" class="btn btn-primary" @click="createClassTimeSlots">{{$p.t('ui', 'speichern')}}</button>
      <button v-else type="button" class="btn btn-primary" @click="updateClassTimeSlots">{{$p.t('ui', 'btnAktualisieren')}}</button>
    </div>
  </div>
  `,
};
