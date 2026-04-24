import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";
import ClassScheduleValidityPeriodPreview from "./ClassScheduleValidityPeriodPreview.js";

export default {
  name: "ClassScheduleOrgUnitGroupedValidityPeriodsOverview",
  components: {
    ClassScheduleValidityPeriodPreview,
  },
  data: () => {
    return {
      organizationalUnitShortCode: null,
      studyPlanId: null,
      classScheduleValidityPeriods: [],
      classScheduleTimeSlots: [],
      classTimeSlotTypes: [],
    };
  },
  computed: {},
  methods: {},
  async created() {
    this.organizationalUnitShortCode = this.$route.params.organizationalUnitShortCode;
    this.studyPlanId = parseInt(this.$route.params.studyPlanId);

    let getAllClassTimeValidityPeriodsPerOrganizationalUnitResponse =
      await this.$api.call(
        ApiClassSchedule.getAllClassTimeValidityPeriodsPerOrganizationalUnit(
          this.organizationalUnitShortCode,
        ),
      );
    if (
      getAllClassTimeValidityPeriodsPerOrganizationalUnitResponse.meta
        .status === "success"
    ) {
      this.classScheduleValidityPeriods =
        getAllClassTimeValidityPeriodsPerOrganizationalUnitResponse.data.filter(
          (period) => {
            if (!this.studyPlanId) return true;

            return period.studienplan_id === this.studyPlanId;
          }
        );
    } else {
      console.error(
        "Error fetching class time slot validity periods:",
        getAllClassTimeValidityPeriodsPerOrganizationalUnitResponse.meta
          .message,
      );
    }

    for (let validityPeriod of this.classScheduleValidityPeriods) {
      if (this.studyPlanId && validityPeriod.studienplan_id !== this.studyPlanId) {
        continue;
      }

      let getClassTimeSlotsForValidityPeriodResponse = await this.$api.call(
        ApiClassSchedule.getClassTimeSlotsForValidityPeriod(
          validityPeriod.unterrichtszeitengueltigkeit_id,
        ),
      );
      if (
        getClassTimeSlotsForValidityPeriodResponse.meta.status === "success"
      ) {
        this.classScheduleTimeSlots = [
          ...this.classScheduleTimeSlots,
          ...getClassTimeSlotsForValidityPeriodResponse.data,
        ];
      } else {
        console.error(
          `Error fetching class time slots for validity period ${validityPeriod.unterrichtszeitengueltigkeit_id}:`,
          getClassTimeSlotsForValidityPeriodResponse.meta.message,
        );
      }
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
  template: /* html */ `
    <div class="container mt-4">
      <div class="my-2">
        <h2 v-if="!this.studyPlanId">{{ $p.t("ui", "classScheduleOrgUnitGroupedValidityPeriodsOverviewTitle") }}</h2>
        <h2 v-else>{{ $p.t("ui", "classScheduleStudyPlanGroupedValidityPeriodsOverviewTitle") }}</h2>
      </div>
     <class-schedule-validity-period-preview
        v-for="validityPeriod in classScheduleValidityPeriods"
        :key="validityPeriod.unterrichtszeitengueltigkeit"
        :class-schedule-validity-period="validityPeriod"
        :class-time-slots="classScheduleTimeSlots.filter(slot => slot.unterrichtszeitengueltigkeit_id === validityPeriod.unterrichtszeitengueltigkeit_id)"
        :class-time-slot-types="classTimeSlotTypes"
        class="shadow"
      />
    </div>
  `,
};
