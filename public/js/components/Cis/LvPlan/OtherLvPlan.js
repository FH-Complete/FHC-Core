import FormForm from "../../Form/Form.js";
import FormInput from "../../Form/Input.js";
import FhcCalendar from "../../Calendar/LvPlan.js";

import ApiLvPlan from "../.././../api/factory/lvPlan.js";
import ApiOtherLvPlan from "../.././../api/factory/otherLvPlan.js";
import ApiAuthinfo from "../../../api/factory/authinfo.js";

export const DEFAULT_MODE_LVPLAN = "Week";

export default {
  name: "OtherLvPlan",
  components: {
    FormForm,
    FormInput,
    FhcCalendar,
  },
  props: {
    viewData: Object,
    propsViewData: Object,
  },
  data() {
    return {
      localProps: {},
      studiensemester_kurzbz: null,
      studiensemester_start: null,
      studiensemester_ende: null,
      isOtherPersonMitarbeiter: false,
      isOtherPersonStudent: false,
      currentStgBezeichnung: null,
      listVerband: [],
      listGroup: [],
      rangeIntervalFirst: null,
      otherPersonData: {
        fullName: "",
        photo: "",
      },
    };
  },
  computed: {
    currentDay() {
      if (
        !this.propsViewData?.focus_date ||
        isNaN(new Date(this.propsViewData?.focus_date))
      )
        return luxon.DateTime.now().setZone(this.viewData.timezone).toISODate();
      return this.propsViewData?.focus_date;
    },
    currentMode() {
      if (
        !this.propsViewData?.mode ||
        !["day", "week", "month"].includes(
          this.propsViewData?.mode.toLowerCase(),
        )
      )
        return DEFAULT_MODE_LVPLAN;
      return this.propsViewData?.mode;
    },
    downloadLinks() {
      if (
        !this.studiensemester_start ||
        !this.studiensemester_ende ||
        !this.propsViewData.otherUid
      )
        return false;

      const type = this.isOtherPersonStudent
        ? "student"
        : this.isOtherPersonMitarbeiter
          ? "lektor"
          : null;

      if (!type) return;

      const opts = { zone: this.viewData.timezone };
      const start = luxon.DateTime.fromISO(
        this.studiensemester_start,
        opts,
      ).toUnixInteger();
      const ende = luxon.DateTime.fromISO(
        this.studiensemester_ende,
        opts,
      ).toUnixInteger();

      const download_link =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        "cis/private/lvplan/stpl_kalender.php" +
        "?type=" +
        type +
        "&pers_uid=" +
        this.propsViewData.otherUid +
        "&begin=" +
        start +
        "&ende=" +
        ende;

      return [
        {
          title: "excel",
          icon: "fa-solid fa-file-excel",
          link: download_link + "&format=excel",
        },
        {
          title: "csv",
          icon: "fa-solid fa-file-csv",
          link: download_link + "&format=csv",
        },
        {
          title: "ical1",
          icon: "fa-regular fa-calendar",
          link: download_link + "&format=ical&version=1&target=ical",
        },
        {
          title: "ical2",
          icon: "fa-regular fa-calendar",
          link: download_link + "&format=ical&version=2&target=ical",
        },
      ];
    },
    get_image_base64_src: function () {
      if (!this.otherPersonData.photo?.length) {
        return "";
      }
      return "data:image/jpeg;base64," + this.otherPersonData.photo;
    },
  },
  watch: {
    "propsViewData.otherUid": {
      handler() {
        this.$router.go();
      },
    },
  },
  methods: {
    handleChangeDate(day, newMode) {
      return this.handleChangeMode(newMode, day);
    },
    handleChangeMode(newMode, day) {
      const mode = newMode[0].toUpperCase() + newMode.slice(1);
      const focus_date = day.toISODate();

      this.$router.push({
        name: "OtherLvPlan",
        params: {
          mode,
          focus_date,
        },
      });
    },
    updateRange(rangeInterval) {
      this.$api
        .call(
          ApiLvPlan.studiensemesterDateInterval(
            rangeInterval.end.startOf("week").toISODate(),
          ),
        )
        .then((res) => {
          this.studiensemester_kurzbz = res.data.studiensemester_kurzbz;
          this.studiensemester_start = res.data.start;
          this.studiensemester_ende = res.data.ende;
        });
    },
    getPromiseFunc(start, end) {
      return [
        this.$api.call(
          ApiLvPlan.eventsPersonal(
            start.toISODate(),
            end.toISODate(),
            this.propsViewData.otherUid,
          ),
        ),
        this.$api.call(
          ApiLvPlan.getLvPlanReservierungen(
            start.toISODate(),
            end.toISODate(),
            this.propsViewData.otherUid,
          ),
        ),
      ];
    },
  },
  async created() {
    const authInfoResponse = await this.$api.call(ApiAuthinfo.getAuthInfo());
    const authId = authInfoResponse.data.uid;
    if (authId === this.propsViewData.otherUid) {
      this.$router.push({ name: "MyLvPlan" });
    }

    const userDataResponse = await this.$api.call(
      ApiOtherLvPlan.getBasicUserAttributesForLvPlanDisplay(
        this.propsViewData.otherUid,
      ),
    );

    const userData = userDataResponse.data;
    this.isOtherPersonMitarbeiter = !!userData.is_mitarbeiter;
    this.isOtherPersonStudent = !!userData.is_student;
    this.otherPersonData.fullName = userData.vorname + " " + userData.nachname;
    this.otherPersonData.photo = userData.foto;
  },
  template: `
    <div class="d-flex flex-column h-100">
        <h2>
            <div class="d-flex flex-row justify-content-between align-items-center">
			          <span>    
                    {{ $p.t('lehre/stundenplan') + (studiensemester_kurzbz ? " " + studiensemester_kurzbz : "") }}
                </span>
                <div @click="this.$router.push({name: 'ProfilView', params: {uid: propsViewData.otherUid}})" type="button" class="d-flex flex-row align-items-center gap-3">
                    <span v-if="otherPersonData.fullName?.length">
                        {{ otherPersonData.fullName }}
                    </span>
                    <img v-if="otherPersonData.photo?.length" alt="profile picture" class=" img-thumbnail " style=" max-height:60px; "  :src="get_image_base64_src"/>
                </div>
            </div>
		</h2>
		<hr>
        <fhc-calendar
            ref="calendar"
            :timezone="viewData.timezone"
            :get-promise-func="getPromiseFunc"
            :date="currentDay"
            :mode="currentMode"
            @update:date="handleChangeDate"
            @update:mode="handleChangeMode"
            @update:range="updateRange"
            class="responsive-calendar"
        >
            <div
                v-if="downloadLinks"
                class="d-flex gap-1 justify-items-start"
                >
                <div v-for="{ title, icon, link } in downloadLinks">
                    <a
                        :href="link"
                        :aria-label="title"
                        class="py-1 btn btn-outline-secondary"
                    >
                        <div class="d-flex flex-column">
                            <i aria-hidden="true" :class="icon"></i>
                            <span style="font-size:.5rem">{{ title }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </fhc-calendar>
    </div>
    `,
};
