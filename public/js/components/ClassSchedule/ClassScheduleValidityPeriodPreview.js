import ClassScheduleCalendarSelector from "./ClassScheduleCalendarSelector.js";
import ApiClassroomHour from "../../../js/api/factory/classroomHour.js";

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
    return {
      classRoomHours: [],
    };
  },
  computed: {
    classScheduleValidityPeriodStartDate() {
      if (!this.$props.classScheduleValidityPeriod) return null;

      let dateParts = this.$props.classScheduleValidityPeriod.gueltig_von
        .split("-")
        .reverse();
      return dateParts.join(".");
    },
    classScheduleValidityPeriodEndDate() {
      if (!this.$props.classScheduleValidityPeriod) return null;
      let dateParts = this.$props.classScheduleValidityPeriod.gueltig_bis
        .split("-")
        .reverse();
      return dateParts.join(".");
    },
    classScheduleValidityPeriodStudyPlan() {
      return this.$props.classScheduleValidityPeriod.studienplan_bezeichnung;
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
  async created() {
    let getAllClassroomHoursResponse = await this.$api.call(
      ApiClassroomHour.getAllClassroomHours(),
    );
    if (getAllClassroomHoursResponse.meta.status === "success") {
      this.classroomHours = getAllClassroomHoursResponse.data.map((hour) => {
        return {
          ...hour,
          beginn: hour.beginn.substring(0, 5),
          ende: hour.ende.substring(0, 5),
        };
      });
    } else {
      this.$fhcAlert.alertError(this.$p.t("ui", "errorFetchingClassroomHours"));
    }
  },
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
      <div v-if="$props.classTimeSlots && $props.classTimeSlots.length > 0">
        <div class="row border-top rounded pt-1 pb-5">
          <class-schedule-calendar-selector
            :classroom-hours="this.classroomHours.map(hour => hour.beginn + '-' + hour.ende)"
            :class-time-slot-types="this.classTimeSlotTypes" 
            :edited-overlays="$props.classTimeSlots.map((slot) => {
              return {
                databaseId: slot.id,
                id: slot.identifier,
                weekday: parseInt(slot.wochentag) === 0 ? 7 : slot.wochentag,
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
