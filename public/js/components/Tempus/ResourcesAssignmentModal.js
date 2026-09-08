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
import BsModal from "../Bootstrap/Modal.js";
import FormInput from "../Form/Input.js";
import ApiOperationalResourceToCalender from "../../api/factory/operationalResourceToCalender.js";

export default {
  name: "ResourcesAssignmentModal",
  components: {
    BsModal,
    FormInput,
  },
  emits: ["saveFinished"],
  data() {
    return {
      calendar: null,
      availableResources: [],
      filteredAvailableResources: [],
      selectedAvailableResource: null,
      assignedResources: [],
      areFormButtonsDisplayed: false,
    };
  },
  computed: {
    dropdownParsedAvailableResources() {
      return this.availableResources
        .map((unit) => ({
          label: unit.beschreibung,
          value: unit.betriebsmittel_id,
          data: unit,
        }))
        .sort((a, b) => a.label?.localeCompare(b.label));
    },
  },
  watch: {
    selectedAvailableResource(newValue) {
      if (!newValue) return;

      this.assignedResources.push({
        betriebsmittel_kalender_id: null,
        betriebsmittel_id: newValue.data.betriebsmittel_id,
        beschreibung: newValue.data.beschreibung,
        anmerkung: "",
        isNoteTextareaShown: false,
      });

      this.areFormButtonsDisplayed = true;
    },
  },
  methods: {
    async open(calendar) {
      if (!calendar?.kalender_id) return;

      this.calendar = calendar;
      this.availableResources =
        await this.fetchSchedulableResourcesByCalender(calendar.kalender_id);
      this.filteredAvailableResources = [
        ...this.dropdownParsedAvailableResources,
      ];
      this.assignedResources =
        await this.fetchAssignedResourcesByCalender(calendar.kalender_id);

      this.show();
    },
    show() {
      this.$refs.modal.show();
    },
    hide() {
      this.$refs.modal.hide();
    },
    reset() {
      this.calendar = null;
      this.availableResources = [];
      this.filteredAvailableResources = [];
      this.selectedAvailableResource = null;
      this.assignedResources = [];
      this.areFormButtonsDisplayed = false;
    },
    async fetchAssignedResourcesByCalender(calenderId) {
      const result = await this.$api.call(
        ApiOperationalResourceToCalender.getAssignedResourcesByCalender(
          calenderId,
        ),
      );

      if (result.meta.status === "success") {
        return result.data
          .filter((unit) => !!unit)
          .map((unit) => ({
            isNoteTextareaShown:
              unit.anmerkung && unit.anmerkung.trim() !== "",
            ...unit,
          }));
      }

      this.$fhcAlert.alertError(
        this.$p.t("ui", "failed_assigned_resources_fetch_error_message"),
      );
      return [];
    },
    async fetchSchedulableResourcesByCalender(calendarId) {
      const result = await this.$api.call(
        ApiOperationalResourceToCalender.getSchedulableResourcesByCalendar(
          calendarId,
        ),
      );

      if (result.meta.status === "success") return result.data;

      this.$fhcAlert.alertError(
        this.$p.t("ui", "failed_schedulable_resources_fetch_error_message"),
      );
      return [];
    },
    filterAvailableResources(event) {
      const query = event.query.toLowerCase();
      const unassignedResources = this.dropdownParsedAvailableResources.filter(
        (unit) =>
          !this.assignedResources.some(
            (assigned) => assigned.betriebsmittel_id === unit.value,
          ),
      );

      this.filteredAvailableResources = query
        ? unassignedResources.filter((unit) =>
            unit.label.toLowerCase().includes(query),
          )
        : unassignedResources;
    },
    toggleAssignedResourceNoteInput(resource) {
      const assignedResource = this.assignedResources.find(
        (assigned) => assigned.betriebsmittel_id === resource.betriebsmittel_id,
      );

      if (assignedResource) {
        assignedResource.isNoteTextareaShown =
          !assignedResource.isNoteTextareaShown;
      }

      this.areFormButtonsDisplayed = true;
    },
    removeAssignedResource(resource) {
      this.assignedResources = this.assignedResources.filter(
        (assigned) =>
          assigned.betriebsmittel_id !== resource.betriebsmittel_id,
      );
      this.areFormButtonsDisplayed = true;
    },
    async refreshResourcesAssignmentModalData() {
      if (!this.calendar?.kalender_id) return;

      this.availableResources =
        await this.fetchSchedulableResourcesByCalender(
          this.calendar.kalender_id,
        );
      this.filteredAvailableResources = [
        ...this.dropdownParsedAvailableResources,
      ];
      this.assignedResources = await this.fetchAssignedResourcesByCalender(
        this.calendar.kalender_id,
      );
      this.selectedAvailableResource = null;
      this.areFormButtonsDisplayed = false;
    },
    async saveAssignedResourcesToCalendarItem() {
      if (!this.calendar?.kalender_id) return;

      const result = await this.$api.call(
        ApiOperationalResourceToCalender.storeResourcesToCalendarRelationship(
          this.calendar.kalender_id,
          this.assignedResources,
        ),
      );

      if (result.meta.status === "success") {
        this.$fhcAlert.alertSuccess(
          this.$p.t("ui", "assigned_resources_save_success_message"),
        );
        await this.refreshResourcesAssignmentModalData();
      } else {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "failed_assigned_resources_save_error_message"),
        );
      }

      this.$emit("saveFinished");
      this.hide();
    },
  },
  template: `
    <bs-modal
      ref="modal"
      @hide-bs-modal="reset"
      class="bootstrap-prompt"
      data-cy="resourcesAssignmentModal"
    >
      <template #title>{{ $p.t('ui', 'resource_assignment_modal_title') }}</template>
      <template #default>
        <div class="mb-5">
          <form-input
            v-if="availableResources.length"
            @item-select="selectedAvailableResource = $event.value"
            :label="$p.t('ui', 'available_resources_label')"
            :suggestions="filteredAvailableResources"
            :option-value="option => option.value"
            :option-label="option => option.label"
            @complete="filterAvailableResources"
            dropdown
            force-selection
            type="autocomplete"
            name="availableResources"
            :close-on-select="false"
          />
        </div>
        <div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-2 mx-auto text-bold fw-1">
              {{ $p.t('ui', 'assigned_resources_subtitle') }}
            </h6>
          </div>
          <div v-if="assignedResources.length" class="mb-4">
            <div
              v-for="resource in assignedResources"
              :key="resource.betriebsmittel_id"
              class="shadow-sm p-2 mb-2 bg-body rounded"
            >
              <div class="d-flex justify-content-between align-items-center mb-1">
                <p class="m-0">{{ resource.beschreibung }}</p>
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <a href="#" @click.prevent="toggleAssignedResourceNoteInput(resource)" class="ms-auto">
                    <i class="fa fa-edit text-primary"></i>
                  </a>
                  <a href="#" @click.prevent="removeAssignedResource(resource)" class="ms-auto">
                    <i class="fa fa-trash text-danger"></i>
                  </a>
                </div>
              </div>
              <form-input
                v-if="resource.isNoteTextareaShown"
                v-model="resource.anmerkung"
                @input="areFormButtonsDisplayed = true"
                :placeholder="$capitalize($p.t('global/anmerkung'))"
                :rows="1"
                class="flex-grow-1"
                type="textarea"
                name="anmerkung"
              />
            </div>
          </div>
          <div v-else class="d-flex align-items-center justify-content-center mb-2">
            <p class="text-muted mb-0">{{ $p.t('ui', 'no_assigned_resources') }}</p>
          </div>
          <div
            v-if="areFormButtonsDisplayed"
            class="d-flex justify-content-end gap-2"
          >
            <button type="button" class="btn btn-secondary" @click="refreshResourcesAssignmentModalData">
              {{ $p.t('ui', 'abbrechen') }}
            </button>
            <button type="button" class="btn btn-primary" @click="saveAssignedResourcesToCalendarItem">
              {{ $p.t('ui', 'speichern') }}
            </button>
          </div>
        </div>
      </template>
    </bs-modal>
  `,
};
