/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import CoreSearchbar from "../searchbar/searchbar.js";
import NavLanguage from "../navigation/Language.js";
import VerticalSplit from "../verticalsplit/verticalsplit.js";
import FhcCalendar from "../Calendar/Tempus.js";
import FhcCoursepicker from "../Tempus/Coursepicker.js";
import LectureSelection from "../Tempus/LectureSelection.js";
import ParkingSlot from "../Tempus/ParkingSlot.js";
import ApiKalender from "../../api/factory/tempus/kalender.js";
import ApiSearchbar from "../../api/factory/searchbar.js";
import ApiRenderers from "../../api/factory/renderers.js";
import ApiTempusConfig from "../../api/factory/tempus/config.js";
import ApiBetriebsmittel from "../../api/factory/betriebsmittel.js";
import ApiOperationalResourceToCalender from "../../api/factory/operationalResourceToCalender.js";
import AppMenu from "../AppMenu.js";
import drop from "../../directives/drop.js";
import AppConfig from "../AppConfig.js";
import BsModal from "../Bootstrap/Modal.js";

import StvVerband from "../Stv/Studentenverwaltung/Verband.js";
import ApiStudiengangTree from "../../api/lehrveranstaltung/studiengangtree.js";
import StvStudiensemester from "../Stv/Studentenverwaltung/Studiensemester.js";
import FormInput from "../../../js/components/Form/Input.js";
import Reservierung from "./Reservierung.js";

export default {
  name: "Tempus",
  components: {
    CoreSearchbar,
    VerticalSplit,
    FhcCalendar,
    FhcCoursepicker,
    LectureSelection,
    ParkingSlot,
    AppConfig,
    AppMenu,
    NavLanguage,
    BsModal,
    StvVerband,
    StvStudiensemester,
    Multiselect: primevue.multiselect,
    FormInput,
    Reservierung,
  },
  props: {
    defaultSemester: String,
    config: Object,
    permissions: Object,
    tempusRoot: String,
    cisRoot: String,
    activeAddons: String, // semicolon separated list of active addons
    viewData: Object,
    logoutUrl: String,
    avatarUrl: String,
  },
  directives: {
    drop,
  },
  provide() {
    return {
      cisRoot: this.cisRoot,
      defaultSemester: this.defaultSemester,
      currentSemester: this.defaultSemester,
      renderers: Vue.computed(() => this.renderers),
      appConfig: Vue.computed(() => this.appconfig),
      contextMenuActions: Vue.computed(() => this.contextMenuActions),
    };
  },
  data() {
    return {
      appconfig: {},
      configEndpoints: ApiTempusConfig,
      endpoint: ApiStudiengangTree,
      raumVorschlaege: [],
      selected: [],
      searchbaroptions: {
        origin: "tempus",
        cssclass: "position-relative",
        calcheightonly: true,
        types: [
          //"student",
          "raum",
          "mitarbeiter",
          "mitarbeiter_ohne_zuordnung",
        ],
        actions: {
          raum: {
            defaultaction: {
              type: "function",
              action: this.setOrt,
            },
            childactions: [],
          },
          employee: {
            defaultaction: {
              type: "function",
              action: (data) => {
                this.setEmp(data);
              },
            },
            childactions: [],
          },
        },
      },
      lv_id: null,
      events: null,
      minimized: false,
      currentlySelectedEvent: null,
      //currentDay: new Date(),
      studiensemesterKurzbz: this.defaultSemester,
      lists: {
        nations: [],
        sprachen: [],
        geschlechter: [],
      },
      renderers: null,
      ort_kurzbz: null,
      view: "room",
      parkedKeys: new Set(),
      lecturers: [],
      overlayCache: [],
      extraBackgrounds: [],
      lastRange: null,
      stg: null,
      show_stg: null,
      semester: null,
      studiensemester_kurzbz: null,
      raumModal: {
        show: false,
        loading: false,
        vorschlaege: [],
        event: null,
      },
      visibleStatusArray: {},
      visibleStatus: ["all"],
      selectedStudiensemester:
        this.studiensemester_kurzbz ?? this.defaultSemester,
      calendarDate: luxon.DateTime.now()
        .setZone(this.config.timezone)
        .toISODate(),
      historyEntries: [],
      previewRole: "planer",
      resourcesAssignmentModal: {
        calendar: null,
        availableResources: [],
        filteredAvailableResources: [],
        selectedAvailableResource: null,
        assignedResources: [],
        areFormButtonsDisplayed: false,
      },
      currentlyUpdatedEvent: null,
    };
  },
  computed: {
    contextMenuActions() {
      return {
        lehreinheit: [
          {
            label: "Raumauswahl",
            icon: "fa-solid fa-door-open",
            action: this.openRaumauswahl,
          },
          {
            label: "Ressourcen zuordnen",
            icon: "fa-solid fa-list",
            action: this.openResourcesAssignmentModal,
          },
          {
            label: "Freischalten für Voransicht",
            icon: "fa-solid fa-chalkboard-user",
            action: (orig) =>
              this.$api
                .call(ApiKalender.syncToLecturer(orig.kalender_id))
                .then(() => this.$refs.calendar.resetEventLoader()),
          },
          {
            label: "Freischalten für Live",
            icon: "fa-solid fa-user-graduate",
            action: (orig) =>
              this.$api
                .call(ApiKalender.syncToStudent(orig.kalender_id))
                .then(() => this.$refs.calendar.resetEventLoader()),
          },
          {
            label: "History",
            icon: "fa-solid fa-clock-rotate-left",
            action: this.openHistory,
          },
          {
            label: "Delete",
            icon: "fa-solid fa-calendar-xmark",
            action: this.deleteEntry,
          },
        ],
        reservierung: [
          {
            label: "Delete",
            icon: "fa-solid fa-calendar-xmark",
            action: this.deleteEntry,
          },
        ],
      };
    },
    currentDay() {
      return luxon.DateTime.now().setZone(this.config.timezone).toISODate();
    },
    currentMode() {
      return "week";
    },
    visibleLecturerUids() {
      if (!this.lecturers.length) return null;
      return this.lecturers
        .filter((lecture) => lecture.showEvents)
        .map((lecture) => lecture.uid);
    },
    visibleStatusOptions() {
      return Object.entries(this.visibleStatusArray).map(([key, label]) => ({
        key,
        label,
      }));
    },
    visibleStatusValue() {
      if (this.visibleStatus.includes("all"))
        return this.visibleStatusOptions.filter(
          (visibleStatus) => visibleStatus.key === "all",
        );
      return this.visibleStatus.map((status) => ({
        key: status,
        label: this.visibleStatusArray[status],
      }));
    },
    dropdownParsedAvailableResources() {
      return this.resourcesAssignmentModal.availableResources
        .map((unit) => {
          return {
            label: unit.beschreibung,
            value: unit.betriebsmittel_id,
            data: unit,
          };
        })
        .sort((a, b) => a.label?.localeCompare(b.label));
    },
  },
  methods: {
    async openRaumauswahl(orig) {
      if (!orig?.kalender_id) return;
      this.raumModal = orig;

      await this.$api
        .call(ApiKalender.getRaumvorschlag(orig.kalender_id))
        .then((result) => {
          this.raumVorschlaege = result.data ?? [];
          this.$refs.raumModal.show();
        });
    },
    async openResourcesAssignmentModal(orig) {
      if (!orig?.kalender_id) return;

      this.resourcesAssignmentModal.calendar = orig;
      this.resourcesAssignmentModal.availableResources =
        await this.fetchSchedulableResourcesByCalender(orig.kalender_id);
      this.resourcesAssignmentModal.filteredAvailableResources = [
        ...this.dropdownParsedAvailableResources,
      ];

      this.resourcesAssignmentModal.assignedResources =
        await this.fetchAssignedResourcesByCalender(orig.kalender_id);

      this.$refs.resourcesAssignmentModal.show();
    },
    async deleteEntry(orig) {
      if (!orig?.kalender_id) return;

      await this.$api
        .call(ApiKalender.deleteEntry(orig?.kalender_id))
        .then((result) => {
          this.$refs.calendar.resetEventLoader();
        });
    },
    async openHistory(orig) {
      if (!orig?.kalender_id) return;
      await this.$api
        .call(ApiKalender.getHistory(orig.kalender_id))
        .then((result) => {
          this.historyEntries = result.data ?? [];
          this.$refs.historyModel.show();
        });
    },
    async selectRaum(ort_kurzbz) {
      const orig = this.raumModal;
      await this.$api
        .call(
          ApiKalender.updateKalenderEvent(orig.kalender_id, {
            ort_kurzbz,
            start_time: orig.von,
            end_time: orig.bis,
          }),
        )
        .then(() => this.$refs.raumModal.hide());
      this.$refs.calendar.resetEventLoader();
    },
    setOrt: function (data) {
      this.ort_kurzbz = data.ort_kurzbz;
      this.$refs.calendar.resetEventLoader();
    },
    onSelectVerbandAndClose(payload) {
      this.onSelectVerband(payload);
      bootstrap.Offcanvas.getOrCreateInstance(this.$refs.verbandMenu).hide();
    },
    onSelectVerband({ link, name }) {
      let stg = null;
      let semester = null;
      this.show_stg = name;
      if (typeof link === "number") stg = link;
      else if (typeof link === "string") {
        [stg, semester] = link.split("/");
      }
      this.stg = stg;
      if (semester !== null) this.semester = semester;

      this.$refs.calendar.resetEventLoader();
    },
    setEmp: function (data) {
      const uid = data.uid;
      const label = data.name;
      if (!this.lecturers.some((l) => l.uid === uid)) {
        this.lecturers.push({
          uid,
          label,
          showEvents: true,
          overlays: { blocks: true, wishes: true },
        });
      }

      this.$refs.calendar.resetEventLoader();
      if (this.lastRange) this.handleRange(this.lastRange);
    },
    jumpToKw(kw) {
      const num = parseInt(kw);
      if (!num) return;

      const date = luxon.DateTime.fromObject(
        {
          weekYear: luxon.DateTime.now().setZone(this.config.timezone).weekYear,
          weekNumber: num,
          weekday: 1,
        },
        { zone: this.config.timezone },
      );
      this.calendarDate = date.toISODate();
    },
    handleChangeDate(newDate) {
      if (newDate && luxon.DateTime.isDateTime(newDate) && newDate.isValid)
        this.calendarDate = newDate.toISODate();
    },
    handleChangeMode() {
      console.log("handleChangeMode");
    },
    toggleStatus(selected) {
      if (!selected || selected.length === 0) {
        this.visibleStatus = ["all"];
        return;
      }
      const hasAll = selected.includes("all");
      const hadAll = this.visibleStatus.includes("all");

      if (hasAll && !hadAll) {
        this.visibleStatus = ["all"];
        return;
      }
      this.visibleStatus = selected.filter((k) => k !== "all");
      if (this.visibleStatus.length === 0) this.visibleStatus = ["all"];
    },
    searchfunction(params) {
      return this.$api.call(ApiSearchbar.search(params));
    },
    getPromiseFunc(start, end) {
      const hasRoom = !!this.ort_kurzbz;
      const hasLektoren = this.lecturers.length > 0;
      const hasStg = !!this.stg;

      const filter = {};

      if (hasRoom) filter.ort = this.ort_kurzbz;
      if (hasStg) filter.stg = this.stg;
      if (hasLektoren) filter.uid = this.lecturers.map((l) => l.uid);

      let response = null;
      if (this.previewRole === "lektor")
        response = [
          this.$api.call(
            ApiKalender.getPlanLecturer(start.toISODate(), end.toISODate()),
          ),
        ];

      if (this.previewRole === "student")
        response = [
          this.$api.call(
            ApiKalender.getPlanStudent(start.toISODate(), end.toISODate()),
          ),
        ];

      response = [
        this.$api.call(
          ApiKalender.getPlan(filter, start.toISODate(), end.toISODate()),
        ),
      ];

      if (response) {
        response[0].then((result) => {
          this.scrollToAndEmphasizeUpdatedEvent();
        });
      }

      return response;
    },
    toDateTime(value, timezone) {
      if (luxon.DateTime.isDateTime(value)) return value;

      if (value?.date?.isValid) return value.date;

      if (typeof value === "number")
        return luxon.DateTime.fromMillis(value, { zone: timezone });

      if (value instanceof Date)
        return luxon.DateTime.fromJSDate(value, { zone: timezone });

      if (typeof value === "string")
        return luxon.DateTime.fromISO(value, { zone: timezone });

      return luxon.DateTime.invalid("invalid datetime");
    },
    getLastEndOfSameDay(startDT, ends) {
      if (!ends?.length) return null;

      const dayKey = startDT.toISODate();
      let lastSameDay = null;

      for (const end of ends) {
        const dt = luxon.DateTime.isDateTime(end)
          ? end
          : luxon.DateTime.fromISO(String(end), { zone: startDT.zoneName });

        if (!dt.isValid) continue;

        if (dt.toISODate() === dayKey) lastSameDay = dt;
      }

      return lastSameDay;
    },
    clampEndToGrid(startDT, durationMin, ends) {
      const calculatedEnd = startDT.plus({ minutes: durationMin });

      const lastGridEndSameDay = this.getLastEndOfSameDay(startDT, ends);

      if (!lastGridEndSameDay) return calculatedEnd;

      return calculatedEnd > lastGridEndSameDay
        ? lastGridEndSameDay
        : calculatedEnd;
    },
    _parseDates(start, end) {
      const startDT = luxon.DateTime.fromISO(start);
      const endDT = luxon.DateTime.fromISO(end);

      if (!startDT.isValid || !endDT.isValid) {
        alert("Ungültiges Datum");
        return null;
      }

      return {
        startDT,
        endDT,
        start_time: startDT.toFormat("yyyy-MM-dd HH:mm"),
        end_time: endDT.toFormat("yyyy-MM-dd HH:mm"),
      };
    },

    _updateKalenderEvent(
      obj,
      startDT,
      endDT,
      start_time,
      end_time,
      onSuccess,
      onError,
    ) {
      const origStart = luxon.DateTime.fromISO(obj.orig.isostart);
      const origEnd = luxon.DateTime.fromISO(obj.orig.isoend);

      if (
        origStart.toMillis() === startDT.toMillis() &&
        origEnd.toMillis() === endDT.toMillis()
      )
        return;

      const updatedInfos = {
        ort_kurzbz: this.ort_kurzbz ? this.ort_kurzbz : obj.orig.ort_kurzbz,
        start_time,
        end_time,
      };

      return this.$api
        .call(
          ApiKalender.updateKalenderEvent(obj.orig.kalender_id, updatedInfos),
        )
        .then(() => {
          if (onSuccess) {
            onSuccess();
            this.$refs.calendar.$refs.calendar.$refs.mode.$refs.view.$refs.grid.disableAutoScroll();
            this.currentlyUpdatedEvent = obj.orig;
          }
        })
        .catch((error) => {
          if (onError) {
            onError(error);
          }
        });
    },

    resizeHandler(payload) {
      if (this.previewRole !== "planer")
        //TODO (david) testzweck
        return;
      const { item, start, end } = payload;
      const obj = item[0];
      if (!obj?.orig?.kalender_id) return;

      const dates = this._parseDates(start, end);
      if (!dates) return;

      if (
        luxon.DateTime.fromISO(obj.orig.isostart).toMillis() ===
          dates.startDT.toMillis() &&
        luxon.DateTime.fromISO(obj.orig.isoend).toMillis() ===
          dates.endDT.toMillis()
      )
        return;

      this.updateKalenderEventElementDisplay(
        obj.orig.kalender_id,
        dates.startDT,
        dates.endDT,
      );

      return this._updateKalenderEvent(
        obj,
        dates.startDT,
        dates.endDT,
        dates.start_time,
        dates.end_time,
        () => {
          this.$refs.calendar.resetEventLoader();
        },
        () => {
          this.$refs.calendar.clearOutCalendarEventEmphasis();
          this.$refs.calendar.resetEventLoader();
        },
      );
    },

    dropHandler(payload) {
      if (this.previewRole !== "planer")
        //TODO (david) testzweck
        return;
      const { item, start, end } = payload;
      if (!item?.length) return alert("Keine Daten gedroppt");

      const obj = item[0];
      if (!obj?.type) return alert("Unbekannter Drop-Typ");

      const dates = this._parseDates(start, end);
      if (!dates) return;

      if (
        luxon.DateTime.fromISO(obj.orig.isostart).toMillis() ===
          dates.startDT.toMillis() &&
        luxon.DateTime.fromISO(obj.orig.isoend).toMillis() ===
          dates.endDT.toMillis()
      )
        return;

      const { startDT, endDT, start_time, end_time } = dates;

      this.updateKalenderEventElementDisplay(
        obj.orig.kalender_id,
        startDT,
        endDT,
      );

      if (obj.type === "reservierung") {
        this.reservierungPending = true;
        this.$refs.reservierung.show(start_time, end_time);
      } else if (obj.type === "lehreinheit") {
        return this.$api
          .call(
            ApiKalender.addKalenderEvent(
              obj.orig.lehreinheit_id,
              this.ort_kurzbz ? this.ort_kurzbz : obj.orig.ort_kurzbz,
              start_time,
              end_time,
            ),
          )
          .then(() => {
            this.$refs.calendar.resetEventLoader();
            this.bcc.postMessage("dropped");
          });
      } else if (obj.type === "kalender") {
        return this._updateKalenderEvent(
          obj,
          startDT,
          endDT,
          start_time,
          end_time,
          () => {
            this.$refs.parking.unpark({
              type: obj.type,
              id: obj.orig.kalender_id,
            });
            this.$refs.calendar.resetEventLoader();
            this.bcc.postMessage("dropped");
          },
          () => {
            this.$refs.calendar.clearOutCalendarEventEmphasis();
            this.updateKalenderEventElementDisplay(
              obj.orig.kalender_id,
              luxon.DateTime.fromISO(obj.orig.isostart),
              luxon.DateTime.fromISO(obj.orig.isoend)
            );
            this.$refs.calendar.clearOutCalendarEventEmphasis();
            this.$refs.calendar.resetEventLoader();
          },
        );
      } else {
        alert("Unbekannter Drop-Typ: " + obj.type);
      }
    },
    handleRange(range) {
      if (!range?.start || !range?.end) return;

      if (this.currentMode === "week") {
        //Workaround because, updateRange is emitting 2 times
        const startDay = range.start.startOf("day");
        const endDay = range.end.startOf("day");

        const days = Math.round(endDay.diff(startDay, "days").days) + 1;
        if (days > 8) return;
      }

      this.lastRange = range;

      const key = `${range.start.toISODate()}_${range.end.toISODate()}_${this.currentMode}`;

      for (const lect of this.lecturers) {
        this.getOverlays(lect.uid, range, key);
      }

      this.rebuildExtraBackgrounds();
    },

    getOverlays(uid, range, rangeKey) {
      if (!this.overlayCache[uid]) this.overlayCache[uid] = {};

      let entry = this.overlayCache[uid][rangeKey];

      if (entry?.loaded || entry?.loading) return;

      entry = this.overlayCache[uid][rangeKey] = {
        blocks: [],
        wishes: [],
        loading: true,
        loaded: false,
      };

      const promises = [];
      const lect = this.lecturers.find((lecture) => lecture.uid === uid);

      if (lect.overlays.wishes) {
        promises.push(
          this.$api
            .call(
              ApiKalender.getLektorZeitwuensche(
                uid,
                range.start.toISODate(),
                range.end.toISODate(),
              ),
            )
            .then((result) => {
              entry.wishes = (result.data || []).map((zeitwunsch) => ({
                class: `bg-lecturer-wish bg-uid-${uid} wish-w-${zeitwunsch.gewicht}`,
                start: zeitwunsch.isostart,
                end: zeitwunsch.isoend,
                label: zeitwunsch.label,
              }));
            }),
        );
      }

      if (lect.overlays.blocks) {
        promises.push(
          this.$api
            .call(
              ApiKalender.getLektorZeitsperren(
                uid,
                range.start.toISODate(),
                range.end.toISODate(),
              ),
            )
            .then((result) => {
              entry.blocks = (result.data || []).map((zeitsperre) => ({
                class: `bg-lecturer-block bg-uid-${uid}`,
                start: zeitsperre.isostart,
                end: zeitsperre.isoend,
                label: zeitsperre.label,
              }));
            }),
        );
      }

      Promise.allSettled(promises).then(() => {
        entry.loading = false;
        entry.loaded = true;
        this.rebuildExtraBackgrounds();
      });
    },

    rebuildExtraBackgrounds() {
      if (!this.lastRange) return;

      const key =
        `${this.lastRange.start.toISODate()}_` +
        `${this.lastRange.end.toISODate()}_` +
        `${this.currentMode}`;
      let res = [];

      for (let lect of this.lecturers) {
        const entry = this.overlayCache[lect.uid]?.[key];
        if (!entry) continue;

        if (lect.overlays.blocks) res.push(...(entry.blocks || []));

        if (lect.overlays.wishes) res.push(...(entry.wishes || []));
      }

      this.extraBackgrounds = res;
    },

    removeLecturer(uid) {
      this.lecturers = this.lecturers.filter((lecture) => lecture.uid !== uid);
      delete this.overlayCache[uid];
      this.$refs.calendar.resetEventLoader();
    },
    clearOrt() {
      this.ort_kurzbz = null;
      this.$refs.calendar.resetEventLoader();
    },
    clearStg() {
      this.stg = null;
      this.show_stg = null;
      this.$refs.calendar.resetEventLoader();
    },
    triggerSync() {
      this.$api
        .call(ApiKalender.sync())
        .then(this.$refs.calendar.resetEventLoader());
    },
    async fetchAssignedResourcesByCalender(calenderId) {
      let getAssignedResources = await this.$api.call(
        ApiOperationalResourceToCalender.getAssignedResourcesByCalenderId(
          calenderId,
        ),
      );
      if (getAssignedResources.meta.status === "success") {
        return getAssignedResources.data
          .filter((unit) => !!unit)
          .map((unit) => {
            return {
              isNoteTextareaShown:
                unit.anmerkung && unit.anmerkung.trim() !== "",
              ...unit,
            };
          })
          .filter((unit) => !!unit);
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "failed_assigned_resources_fetch_error_message"),
        );
      }

      return [];
    },
    async fetchSchedulableResourcesByCalender(calendarID) {
      let getSchedulableResourcesByCalendar = await this.$api.call(
        ApiOperationalResourceToCalender.getSchedulableResourcesByCalendar(
          calendarID,
        ),
      );
      if (getSchedulableResourcesByCalendar.meta.status === "success") {
        return getSchedulableResourcesByCalendar.data;
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "failed_schedulable_resources_fetch_error_message"),
        );
      }

      return [];
    },
    filterAvailableResources(event) {
      this.resourcesAssignmentModal.filteredAvailableResources;
      const query = event.query.toLowerCase();
      if (!query) {
        return (this.resourcesAssignmentModal.filteredAvailableResources = [
          ...this.dropdownParsedAvailableResources.filter((unit) => {
            return !this.resourcesAssignmentModal.assignedResources.some(
              (assigned) => assigned.betriebsmittel_id === unit.value,
            );
          }),
        ]);
      }

      return (this.resourcesAssignmentModal.filteredAvailableResources =
        this.dropdownParsedAvailableResources
          .filter((unit) => {
            return !this.resourcesAssignmentModal.assignedResources.some(
              (assigned) => assigned.betriebsmittel_id === unit.value,
            );
          })
          .filter((unit) => {
            return unit.label.toLowerCase().includes(query);
          }));
    },
    toggleAssignedResourceNoteInput(resource) {
      const index = this.resourcesAssignmentModal.assignedResources.findIndex(
        (assigned) => assigned.betriebsmittel_id === resource.betriebsmittel_id,
      );
      if (index !== -1) {
        this.resourcesAssignmentModal.assignedResources[
          index
        ].isNoteTextareaShown =
          !this.resourcesAssignmentModal.assignedResources[index]
            .isNoteTextareaShown;
      }

      this.resourcesAssignmentModal.areFormButtonsDisplayed = true;
    },
    removeAssignedResource(resource) {
      this.resourcesAssignmentModal.assignedResources =
        this.resourcesAssignmentModal.assignedResources.filter(
          (assigned) =>
            assigned.betriebsmittel_id !== resource.betriebsmittel_id,
        );

      this.resourcesAssignmentModal.areFormButtonsDisplayed = true;
    },
    async refreshResourcesAssignmentModalData(calenderItem) {
      this.resourcesAssignmentModal.availableResources =
        await this.fetchSchedulableResourcesByCalender(
          calenderItem.kalender_id,
        );
      this.resourcesAssignmentModal.filteredAvailableResources = [
        ...this.dropdownParsedAvailableResources,
      ];

      this.resourcesAssignmentModal.assignedResources =
        await this.fetchAssignedResourcesByCalender(calenderItem.kalender_id);
      this.resourcesAssignmentModal.selectedAvailableResource = null;
      this.resourcesAssignmentModal.areFormButtonsDisplayed = false;
    },
    async saveAssignedResourcesToCalendarItem(calenderItem, assignedResources) {
      let getSchedulableResourcesByCalendar = await this.$api.call(
        ApiOperationalResourceToCalender.storeResourcesToCalendarRelationship(
          calenderItem.kalender_id,
          assignedResources,
        ),
      );
      if (getSchedulableResourcesByCalendar.meta.status === "success") {
        this.$fhcAlert.alertSuccess(
          this.$p.t("ui", "assigned_resources_save_success_message"),
        );
        await this.refreshResourcesAssignmentModalData(calenderItem);
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "failed_assigned_resources_save_error_message"),
        );
      }

      this.$refs.calendar.resetEventLoader();
      this.$refs.resourcesAssignmentModal.hide();
    },
    closeResourcesAssignmentModal() {
      this.resourcesAssignmentModal = {
        availableResources: [],
        filteredAvailableResources: [],
        selectedAvailableResource: null,
        assignedResources: [],
        areFormButtonsDisplayed: false,
      };
    },
    scrollToAndEmphasizeUpdatedEvent() {
      if (!this.currentlyUpdatedEvent) return;

      this.$refs.calendar.clearOutCalendarEventEmphasis();

      setTimeout(() => {
        const eventEl = document.querySelector(
          `[data-group-id="event-group-${this.currentlyUpdatedEvent.eindeutige_gruppen_id}"]`,
        );
        if (!eventEl) return;

        const calendar = document.querySelector(".fhc-calendar-base-grid");
        const eventRect = eventEl.getBoundingClientRect();

        const offset = 300;

        const isInsideScrolledView =
          eventEl.offsetLeft < calendar.scrollLeft + calendar.clientWidth &&
          eventEl.offsetLeft + eventEl.offsetWidth > calendar.scrollLeft &&
          eventEl.offsetTop < calendar.scrollTop + calendar.clientHeight &&
          eventEl.offsetTop + eventEl.offsetHeight > calendar.scrollTop;

        const rect = eventEl.getBoundingClientRect();
        if (!isInsideScrolledView) {
          eventEl.scrollIntoView({
            behavior: "smooth",
            inline: "center",
            block: "nearest",
          });
        }

        let timeout = 0;
        let emphasizeUpdateClassName = isInsideScrolledView
          ? "updated-event"
          : "updated-event-long";
        let deemphasizedUpdateClassName = isInsideScrolledView
          ? "deemphasized-event"
          : "deemphasized-event-long";

        if (!isInsideScrolledView) timeout = 300;

        setTimeout(() => {
          eventEl.classList.add(emphasizeUpdateClassName);

          document
            .querySelectorAll(
              ".fhc-calendar-base-grid .fhc-calendar-base-grid-line-event",
            )
            .forEach((el) => {
              if (el !== eventEl) {
                el.classList.add(deemphasizedUpdateClassName);
              }
            });
        }, timeout);

        this.currentlyUpdatedEvent = null;
      }, 100);
    },
    updateKalenderEventElementDisplay(calendarId, startDT, endDT) {
      if (!calendarId) return;

      let startOfDay = startDT.startOf("day");
      let newPotentialStart = startDT.diff(startOfDay).toMillis() ?? 1;
      let newPotentialEnd = endDT.diff(startOfDay).toMillis();

      let element = document.querySelector(`[data-id="event-${calendarId}"]`);
      if (!element) return;

      const calendarView = element.closest(".fhc-calendar-mode-week-view");
      const targetGridLine = calendarView?.querySelector(
        `.fhc-calendar-base-grid-line[data-day="${startOfDay.toISODate()}"]`,
      );
      if (!targetGridLine)
        return alert("Kein gültiger Kalendertag zum Verschieben gefunden");

      setTimeout(() => {
        targetGridLine.appendChild(element);
        element.style.gridRowStart = "t_" + newPotentialStart;
        element.style.gridRowEnd = "t_" + newPotentialEnd;
      }, 100);

      const outerDiv = document.createElement("div");
      outerDiv.className = "spinner-overlay";

      const innerDiv = document.createElement("div");
      innerDiv.className = "spinner";

      outerDiv.appendChild(innerDiv);

      element.appendChild(outerDiv);

      const calendar = document.querySelector(".fhc-calendar-base-grid");
      const eventRect = element.getBoundingClientRect();

      const offset = 300;

      const isInsideScrolledView =
        element.offsetLeft < calendar.scrollLeft + calendar.clientWidth &&
        element.offsetLeft + element.offsetWidth > calendar.scrollLeft &&
        element.offsetTop < calendar.scrollTop + calendar.clientHeight &&
        element.offsetTop + element.offsetHeight > calendar.scrollTop;

      const rect = element.getBoundingClientRect();
      if (!isInsideScrolledView) {
        element.scrollIntoView({
          behavior: "smooth",
          inline: "center",
          block: "nearest",
        });
      }
    },
  },
  watch: {
    lecturers: {
      deep: true,
      handler() {
        this.rebuildExtraBackgrounds();
      },
    },
    "resourcesAssignmentModal.selectedAvailableResource": function (newVal) {
      if (!newVal) return;

      this.resourcesAssignmentModal.assignedResources.push({
        betriebsmittel_kalender_id: null,
        betriebsmittel_id: newVal.data.betriebsmittel_id,
        beschreibung: newVal.data.beschreibung,
        anmerkung: "",
        isNoteTextareaShown: false,
      });

      this.resourcesAssignmentModal.areFormButtonsDisplayed = true;
    },
  },
  mounted() {
    this.reservierungPending = false;
    this.bcc = new BroadcastChannel("fhc-dnd");
    this.bcc.addEventListener("message", (e) => {
      if (e.data === "dropped" && !this.reservierungPending)
        this.$refs.calendar.resetEventLoader();
    });
  },
  beforeUnmount() {
    this.bcc.close();
  },
  async created() {
    await this.$api
      .call(ApiRenderers.loadTempusRenderers())
      .then((res) => res.data)
      .then((data) => {
        for (let rendertype of Object.keys(data)) {
          let modalTitle = null;
          let modalContent = null;
          let calendarEvent = null;
          if (data[rendertype].modalTitle)
            modalTitle = Vue.markRaw(
              Vue.defineAsyncComponent(
                () => import(data[rendertype].modalTitle),
              ),
            );
          if (data[rendertype].modalContent)
            modalContent = Vue.markRaw(
              Vue.defineAsyncComponent(
                () => import(data[rendertype].modalContent),
              ),
            );
          if (data[rendertype].calendarEvent)
            calendarEvent = Vue.markRaw(
              Vue.defineAsyncComponent(
                () => import(data[rendertype].calendarEvent),
              ),
            );

          if (data[rendertype].calendarEventStyles) {
            var head = document.head;
            if (
              !head.querySelector(
                `link[href="${data[rendertype].calendarEventStyles}"]`,
              )
            ) {
              var link = document.createElement("link");
              link.type = "text/css";
              link.rel = "stylesheet";
              link.href = data[rendertype].calendarEventStyles;
              head.appendChild(link);
            }
          }

          if (this.renderers === null) {
            this.renderers = {};
          }
          if (!this.renderers[rendertype]) {
            this.renderers[rendertype] = {};
          }
          this.renderers[rendertype].modalTitle = modalTitle;
          this.renderers[rendertype].modalContent = modalContent;
          this.renderers[rendertype].calendarEvent = calendarEvent;
        }
      });

    this.$api.call(ApiTempusConfig.getHeader()).then((res) => {
      this.visibleStatusArray = res.data.visible_status;
      this.visibleStatus = ["all"];
    });
  },
  template: /*html*/ `
	<div data-cy="tempus" class="tempus">
		<header class="navbar navbar-expand-lg navbar-dark bg-dark flex-md-nowrap p-0 shadow">
			<div class="col-md-4 col-lg-3 col-xl-2 d-flex align-items-center">
				<button
					class="btn btn-outline-light border-0 m-1 collapsed"
					type="button"
					data-bs-toggle="offcanvas"
					data-bs-target="#appMenu"
					aria-controls="appMenu"
					aria-expanded="false"
					:aria-label="$p.t('ui/toggle_nav')"
				>
					<span class="svg-icon svg-icon-apps"></span>
				</button>
				<a class="navbar-brand me-0" :href="tempusRoot">Tempus</a>
			</div>
			<button
				class="btn btn-outline-light border-0 d-md-none m-1 collapsed"
				type="button"
				data-bs-toggle="offcanvas"
				data-bs-target="#sidebarMenu"
				aria-controls="sidebarMenu"
				aria-expanded="false"
				:aria-label="$p.t('ui/toggle_nav')"
			>
				<span class="fa-solid fa-table-list"></span>
			</button>
			<core-searchbar
				ref="searchbar"
				:searchoptions="searchbaroptions"
				:searchfunction="searchfunction"
				class="searchbar position-relative w-100"
				show-btn-submit
			></core-searchbar>
			<div id="nav-user" class="dropdown">
				<button
					id="nav-user-btn"
					class="btn btn-link rounded-0 py-0"
					type="button"
					data-bs-toggle="dropdown"
					data-bs-target="#nav-user-menu"
					aria-expanded="false"
					aria-controls="nav-user-menu"
				>
					<img
						:src="avatarUrl"
						:alt="$p.t('profilUpdate/profilBild')"
						class="bg-light avatar rounded-circle border border-light"
					/>
				</button>
				<ul
					ref="navUserDropdown"
					class="dropdown-menu dropdown-menu-dark dropdown-menu-end rounded-0 text-center m-0"
					aria-labelledby="nav-user-btn"
				>
					<li>
						<button
							type="button"
							class="dropdown-item"
							data-bs-toggle="modal"
							data-bs-target="#configModal"
						>
							{{ $p.t('ui/settings') }}
						</button>
					</li>
					<li><hr class="dropdown-divider m-0"/></li>
					<li>
						<nav-language
							item-class="dropdown-item border-left-dark"
						/>
					</li>
					<li><hr class="dropdown-divider m-0"/></li>
					<li>
						<a class="dropdown-item" :href="logoutUrl">
							{{ $p.t('ui/logout') }}
						</a>
					</li>
				</ul>
			</div>
		</header>
		<div class="container-fluid overflow-hidden heightfull">
			<div class="row h-100">
				<aside id="appMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100">
					<div class="offcanvas-header">
						Tempus
						<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
					</div>
					<div class="offcanvas-body">
						<app-menu app-identifier="tempus" />
					</div>
				</aside>
				<nav id="sidebarMenu" class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100 d-flex flex-column">
					<div class="sidebar-icons d-flex flex-row align-items-start py-2 gap-1 ps-2">
						<button
							class="btn btn-outline-secondary"
							type="button"
							data-bs-toggle="offcanvas"
							data-bs-target="#verbandMenu"
							aria-controls="verbandMenu"
							aria-expanded="false"
							title="Verband"
						>
							<span class="fa-solid fa-university"></span>
						</button>
						<button
							class="btn btn-outline-secondary"
							type="button"
							data-bs-toggle="offcanvas"
							data-bs-target="#verbandMenu"
							aria-controls="verbandMenu"
							aria-expanded="false"
							title="Verband"
						>
							<span class="fa-solid fa-door-open"></span>
						</button>
					</div>
					<div class="px-2 py-1 w-100">
						<!--<Multiselect
							:model-value="visibleStatusValue"
							@update:model-value="val => toggleStatus(val.map(o => o.key))"
							option-label="label"
							:options="visibleStatusOptions"
							placeholder="Status filtern"
							:hide-selected="false"
							:show-toggle-all="false"
							class="w-100"
						/>-->
						
						<div class="d-flex gap-1 py-1" data-cy="previewRoleOptionsHolder">
							<button
								class="btn btn-sm"
								:class="previewRole === 'planer' ? 'btn-dark' : 'btn-outline-dark'"
								@click="previewRole = 'planer'; $refs.calendar.resetEventLoader()"
							>
								<i class="fa-solid fa-pen-ruler me-1"></i>Planer
							</button>
							<button
								class="btn btn-sm"
								:class="previewRole === 'lektor' ? 'btn-primary' : 'btn-outline-primary'"
								@click="previewRole = 'lektor'; $refs.calendar.resetEventLoader()"
							>
								<i class="fa-solid fa-chalkboard-user me-1"></i>Lektor
							</button>
							<button
								class="btn btn-sm"
								:class="previewRole === 'student' ? 'btn-success' : 'btn-outline-success'"
								@click="previewRole = 'student'; $refs.calendar.resetEventLoader()"
							>
								<i class="fa-solid fa-user-graduate me-1"></i>Student
							</button>
							<button
								class="btn btn-sm btn-outline-danger"
								@click="triggerSync"
							>
								<i class="fa-solid fa-rotate me-1"></i>Sync
							</button>
						</div>
					</div>
					<div class="room-selection" v-if="ort_kurzbz">
						<div class="fw-semibold px-2 d-flex align-items-center justify-content-between">
							<span><i class="fa-solid fa-door-open me-2"></i>{{ ort_kurzbz }}</span>
							<button
								type="button"
								class="btn btn-sm btn-link text-danger p-0"
								@click="clearOrt"
								title="Raum entfernen"
							>
								<i class="fa-solid fa-xmark"></i>
							</button>
						</div>
					</div>
					
					<div class="room-selection" v-if="show_stg">
						<div class="fw-semibold px-2 d-flex align-items-center justify-content-between">
							<span><i class="fa-solid fa-university me-2"></i>{{ show_stg }}</span>
							<button
								type="button"
								class="btn btn-sm btn-link text-danger p-0"
								@click="clearStg"
								title="STG entfernen"
							>
								<i class="fa-solid fa-xmark"></i>
							</button>
						</div>
					</div>
					<lecture-selection
							v-if="lecturers.length"
							:lecturers="lecturers"
							@remove="removeLecturer"
					></lecture-selection>
					<div class="d-flex flex-column flex-grow-1" style="min-height: 0">
						<parking-slot
							ref="parking"
							v-model:parked-keys="parkedKeys"
						></parking-slot>
						
						<fhc-coursepicker :stg="stg" @select-lecturer="setEmp" @select-kw="jumpToKw" :studiensemester="selectedStudiensemester"></fhc-coursepicker>

					</div>
					<stv-studiensemester v-model:studiensemester-kurzbz="selectedStudiensemester"></stv-studiensemester>

				</nav>
				<main class="col-md-8 ms-sm-auto col-lg-9 col-xl-10">
					<fhc-calendar
						ref="calendar"
						:timezone="config.timezone"
						:get-promise-func="getPromiseFunc"
						:visible-status="visibleStatus"
						:date="calendarDate"
						:mode="currentMode"
						:parkedEvents="parkedKeys"
						:visible-lecturers="visibleLecturerUids"
						@drop="dropHandler"
						@resize="resizeHandler"
						@update:date="handleChangeDate"
						@update:mode="handleChangeMode"
						:extra-backgrounds="extraBackgrounds"
						@update:range="handleRange"
						class="responsive-calendar"
					/>
				</main>
			</div>
		</div>
		<app-config ref="config" v-model="appconfig" :endpoints="configEndpoints"></app-config>
		<div id="verbandMenu" ref="verbandMenu" class="offcanvas offcanvas-start col-md p-md-0 h-100" tabindex="-1" data-cy="verbandMenu">
			<div class="offcanvas-header justify-content-end px-1 d-md-none">
				<h5 class="offcanvas-title" id="verbandMenuLabel">
					<i class="fa-solid fa-university me-2"></i>Verband
				</h5>
				<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" :aria-label="$p.t('ui/schliessen')"></button>
			</div>
			<stv-verband :endpoint="endpoint" @select-verband="onSelectVerbandAndClose" class="col" style="height:0%"></stv-verband>
		</div>

		<bs-modal ref="raumModal" class="bootstrap-prompt">
			<template #title>Raumauswahl</template>
			<template #default>
				<ul v-if="raumVorschlaege.length" class="list-group">
					<li
						v-for="raum in raumVorschlaege"
						:key="raum.ort_kurzbz"
						class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
						style="cursor:pointer"
						@click="selectRaum(raum.ort_kurzbz)"
					>
						<span><i class="fa-solid fa-door-open me-2"></i>{{ raum.ort_kurzbz }}</span>
						<span class="text-muted" v-tooltip="{ value: raum.details.join('\\n'), class: 'custom-tooltip' }">{{ raum.score }}</span>
					</li>
				</ul>
				<p v-else class="text-muted mb-0">Keine freien Räume gefunden.</p>
			</template>
		</bs-modal>
		
    <bs-modal 
      ref="resourcesAssignmentModal"
      @hideBsModal="closeResourcesAssignmentModal"
      class="bootstrap-prompt"
      data-cy="resourcesAssignmentModal"
    >
			<template #title>{{$p.t('ui', 'resource_assignment_modal_title')}}</template>
			<template #default>
        <div class="mb-5">
          <form-input
            v-if="resourcesAssignmentModal.availableResources.length"
            @itemSelect="(option) => { resourcesAssignmentModal.selectedAvailableResource = option.value; }"
            :label="$p.t('ui', 'available_resources_label')"
            :suggestions="resourcesAssignmentModal.filteredAvailableResources"
            :optionValue="(option) => option.value"
            :optionLabel="(option) => option.label" 
            @complete="filterAvailableResources"
            dropdown
            forceSelection
            type="autocomplete"
            name="availableResources"  
            :closeOnSelect="false"
            >
          </form-input>
        </div>
        <div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-2 mx-auto text-bold fw-1">{{$p.t('ui', 'assigned_resources_subtitle')}}</h6>
          </div>
          <div v-if="resourcesAssignmentModal.assignedResources.length" class="mb-4">
            <div
              v-for="resource in resourcesAssignmentModal.assignedResources"
              :key="resource.betriebsmittel_id"
              class=" shadow-sm p-2 mb-2 bg-body rounded"
            >
              <div class="d-flex justify-content-between align-items-center mb-1">
                <p class="m-0">{{ resource.beschreibung }}</p>
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <a href="#" @click.prevent="toggleAssignedResourceNoteInput(resource)" class="ms-auto"><i class="fa fa-edit text-primary"></i></a>
                  <a href="#" @click.prevent="removeAssignedResource(resource)" class="ms-auto"><i class="fa fa-trash text-danger"></i></a>
                </div>
              </div>
              <form-input
                v-if="resource.isNoteTextareaShown"
                v-model="resource.anmerkung"
                @input="this.resourcesAssignmentModal.areFormButtonsDisplayed = true"
                :placeholder="$capitalize($p.t('global/anmerkung'))"
                :rows="1"
                class="flex-grow-1"
                type="textarea"
                name="anmerkung"  
                >
              </form-input>
            </div>
          </div>
          <div v-else class="d-flex align-items-center justify-content-center mb-2">
            <p class="text-muted mb-0">{{$p.t('ui', 'no_assigned_resources')}}</p>
          </div>
          <div v-if="resourcesAssignmentModal.areFormButtonsDisplayed" class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" @click="refreshResourcesAssignmentModalData(resourcesAssignmentModal.calendar)">{{$p.t('ui', 'abbrechen')}}</button>
            <button type="button" class="btn btn-primary" @click="saveAssignedResourcesToCalendarItem(resourcesAssignmentModal.calendar, resourcesAssignmentModal.assignedResources)">{{$p.t('ui', 'speichern')}}</button>
          </div>
        </div>
			</template>
		</bs-modal>

		<bs-modal ref="historyModel" class="bootstrap-prompt" dialogClass="modal-lg" data-cy="historyModal">
			<template #title>History</template>
			<template #default>
				<table v-if="historyEntries.length" class="table table-bordered table-hover">
					<thead class="table-light">
						<tr>
							<th>Von</th>
							<th>Bis</th>
							<th>Status</th>
							<th>Ort</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="entry in historyEntries" :key="entry.id">
							<td>{{ entry.von }}</td>
							<td>{{ entry.bis }}</td>
							<td>{{ entry.status_kurzbz }}</td>
							<td>{{ entry.ort }}</td>
						</tr>
					</tbody>
				</table>
			</template>
		</bs-modal>
		<reservierung
			ref="reservierung"
			:ort-kurzbz="ort_kurzbz"
			@saved="reservierungPending = false; $refs.calendar.resetEventLoader()"
		></reservierung>
	</div>`,
};
