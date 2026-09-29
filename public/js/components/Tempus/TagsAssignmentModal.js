import BsModal from "../Bootstrap/Modal.js";
import FormInput from "../Form/Input.js";
import CoreTag from "../Tag/Tag.js";
import ApiTempusTag from "../../api/factory/tempus/tag.js";

export default {
	name: "TagsAssignmentModal",
	components: {
		BsModal,
		FormInput,
		CoreTag,
	},
	data() {
		return {
			calendar: null,
			availableTags: [],
			filteredAvailableTags: [],
			selectedAvailableTag: null,
			assignedTags: [],
			tagEndpoint: ApiTempusTag,
			tagModalEscapeHandler: null,
		};
	},
	computed: {
		dropdownParsedAvailableTags() {
			return this.availableTags.map((tag) => ({
				label: tag.bezeichnung,
				value: tag.tag_typ_kurzbz,
				data: tag,
			}));
		},
	},
	methods: {
		async open(calendar, tagToEdit = null) {
			if (!calendar?.eindeutige_kalender_gruppen_id) return;

			this.calendar = calendar;
			this.availableTags = await this.fetchAvailableTags();
			this.filteredAvailableTags = [...this.dropdownParsedAvailableTags];
			this.assignedTags = await this.fetchAssignedTagsByCalender(
				calendar.eindeutige_kalender_gruppen_id,
			);

			this.show();

			if (tagToEdit)
				this.editTag(tagToEdit);
		},
		show() {
			this.$refs.modal.show();
		},
		hide() {
			this.$refs.modal.hide();
		},
		reset() {
			this.removeTagModalEscapeHandler();
			this.calendar = null;
			this.availableTags = [];
			this.filteredAvailableTags = [];
			this.selectedAvailableTag = null;
			this.assignedTags = [];
		},
		prepareTagModal() {
			const tagModal = this.$refs.tagComponent?.$refs.tagModal;
			const tagModalElement = tagModal?.$el;

			if (!tagModalElement) return;

			tagModalElement.addEventListener(
				"shown.bs.modal",
				() => {
					this.tagModalEscapeHandler = (event) => {
						if (event.key !== "Escape") return;

						event.preventDefault();
						event.stopImmediatePropagation();
						tagModal.hide();
					};
					document.addEventListener("keydown", this.tagModalEscapeHandler, true);
				},
				{ once: true },
			);
			tagModalElement.addEventListener(
				"hidden.bs.modal",
				() => this.restoreAssignmentModalFocus(),
				{ once: true },
			);
		},
		removeTagModalEscapeHandler() {
			if (!this.tagModalEscapeHandler) return;

			document.removeEventListener(
				"keydown",
				this.tagModalEscapeHandler,
				true,
			);
			this.tagModalEscapeHandler = null;
		},
		restoreAssignmentModalFocus() {
			this.removeTagModalEscapeHandler();
			this.$refs.modal?.$el?.focus();
		},
		async fetchAvailableTags() {
			const result = await this.$api.call(ApiTempusTag.getTags());

			if (result.meta.status === "success") return result.data;

			this.$fhcAlert.alertError(
				this.$p.t("ui", "failed_available_tags_fetch_error_message"),
			);
			return [];
		},
		async fetchAssignedTagsByCalender(calendarGroupId) {
			const result = await this.$api.call(
				ApiTempusTag.getTagsByCalendar(calendarGroupId),
			);

			if (result.meta.status === "success") {
				return result.data.filter((tag) => !!tag);
			}

			this.$fhcAlert.alertError(
				this.$p.t("ui", "failed_assigned_tags_fetch_error_message"),
			);
			return [];
		},
		filterAvailableTags(event) {
			const query = event.query.toLowerCase();
			const unassignedTags = this.dropdownParsedAvailableTags.filter(
				(tag) =>
					!this.assignedTags.some(
						(assigned) => assigned.id === tag.value,
					),
			);

			this.filteredAvailableTags = query
				? unassignedTags.filter((tag) =>
					tag.label.toLowerCase().includes(query),
				)
				: unassignedTags;
		},
		selectTag(event) {
			this.selectedAvailableTag = event.value;
			this.prepareTagModal();
			this.$refs.tagComponent?.openModal(event.value.data);
		},
		editTag(tag) {
			this.prepareTagModal();
			this.$refs.tagComponent?.editTag(tag.id);
		},
		async handleCalendarTagChange() {
			this.removeTagModalEscapeHandler();

			const calendarGroupId = this.calendar?.eindeutige_kalender_gruppen_id;
			if (!calendarGroupId) return;

			this.assignedTags = await this.fetchAssignedTagsByCalender(calendarGroupId);
			this.calendar.tags = this.assignedTags
		},
	},
	template: /* html */`
	<bs-modal
		ref="modal"
		@hide-bs-modal="reset"
		body-class="p-4"
		class="bootstrap-prompt"
		data-cy="tagsAssignmentModal"
	>
		<template #title>{{ $p.t('ui', 'tags_assignment_modal_title') }}</template>
		<template #default>
		<div class="mb-5">
			<form-input
			v-if="availableTags.length"
			@item-select="selectTag"
			:label="$capitalize($p.t('ui', 'tags'))"
			:suggestions="filteredAvailableTags"
			:option-value="option => option.value"
			:option-label="option => option.label"
			@complete="filterAvailableTags"
			dropdown
			force-selection
			type="autocomplete"
			name="availableTags"
			:close-on-select="false"
			>
			<template #option="{ option }">
				<span :class="['tag', option.data.style]">{{ option.label }}</span>
			</template>
			</form-input>
		</div>
		<div>
			<div class="d-flex align-items-center justify-content-between mb-2">
			<h6 class="mb-1 mx-auto text-bold fw-1">
				{{ $p.t('ui', 'assigned_tags_subtitle') }}
			</h6>
			</div>
			<div v-if="assignedTags.length">
			<span
				v-for="tag in assignedTags"
				:key="tag.id"
				:class="[tag.style, { tag_done: tag.done }]"
				@click="editTag(tag)"
				class="tag"
			>{{ tag.beschreibung }}</span>
			</div>
			<div v-else class="d-flex align-items-center justify-content-center mb-2">
			<p class="text-muted mb-0">{{ $p.t('ui', 'no_assigned_tags') }}</p>
			</div>
		</div>
		</template>
	</bs-modal>
	<core-tag
		v-if="calendar?.eindeutige_kalender_gruppen_id"
		ref="tagComponent"
		:is-list-item-shown="false"
		:endpoint="tagEndpoint"
		:values="[calendar.eindeutige_kalender_gruppen_id]"
		zuordnung_typ="eindeutige_kalender_gruppen_id"
		@added="handleCalendarTagChange"
		@deleted="handleCalendarTagChange"
		@updated="handleCalendarTagChange"
	/>
  `,
};
