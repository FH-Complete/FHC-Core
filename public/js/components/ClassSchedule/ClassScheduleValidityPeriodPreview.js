import ClassScheduleCalendarSelector from "./ClassScheduleCalendarSelector.js";

export default {
  name: "ClassScheduleValidityPeriodPreview",
  components: {
    ClassScheduleCalendarSelector,
  },
  props: {
    classScheduleValidityPeriod: {
      type: Object,
      required: true,
    },
    classTimeSlots: {
      type: Array,
      required: true,
    },
    classTimeSlotTypes: {
      type: Array,
      required: true,
    },
  },
  data: () => {
    return {};
  },
  computed: {
    classScheduleValidityPeriodStartDate() {
      if (!this.$props.classScheduleValidityPeriod) return null;

      let dateParts = this.$props.classScheduleValidityPeriod.gueltig_von
        .split("-")
        .reverse();
      return dateParts.join("/");
    },
    classScheduleValidityPeriodEndDate() {
      if (!this.$props.classScheduleValidityPeriod) return null;
      let dateParts = this.$props.classScheduleValidityPeriod.gueltig_bis
        .split("-")
        .reverse();
      return dateParts.join("/");
    },
    classScheduleValidityPeriodStudyPlan() {
      return this.$props.classScheduleValidityPeriod.studienplan_bezeichnung;
    },
    parsedClassTimeSlots() {
      let classTimeSlotsGroupedByWeek = [];

      this.$props.classTimeSlots.forEach((slot) => {
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

      return classTimeSlotsGroupedByWeek;
    },
  },
  methods: {
    showClassScheduleValidityPeriod(classScheduleValidityPeriodId) {

      this.$router.push({
        name: "validityPeriodOverview",
        params: {
          classTimeSlotValidityPeriodId: classScheduleValidityPeriodId,
        },
      });
    },
  },
  async created() {},
  template: /* html */ `
   <div class="container mt-4">
     <div class='py-3 d-flex align-items-center justify-content-between'>
      <div>
        <h2>{{ classScheduleValidityPeriodStartDate }} - {{ classScheduleValidityPeriodEndDate }}</h2>
        <h4>{{ classScheduleValidityPeriodStudyPlan }}</h4>
      </div>
      <a 
        @click="showClassScheduleValidityPeriod($props.classScheduleValidityPeriod.unterrichtszeitengueltigkeit_id)"
        class="ml-auto btn btn-link" 
      ><i class="fa fa-eye fs-5"></i></a>
    </div>
    <div class='py-3 mb-4'>
      <div v-if="parsedClassTimeSlots && parsedClassTimeSlots.length > 0">
        <div v-for="(classTimeSlotsPerWeek, index) in parsedClassTimeSlots" :key="index" class="row border-top rounded pt-1 pb-5">
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
      <div v-else class="d-flex align-items-center justify-content-center border rounded p-2">
          <p class="m-0">{{ $p.t("ui", "noClassScheduleValidityPeriodTimeSlotsFound") }}</p>
      </div>
    </div>
</div>
  `,
};
