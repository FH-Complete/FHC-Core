import { parseJsonField } from '../../../helpers/Tempus/modalHelper.js';
import ApiTempusTag from "../../../api/factory/tempus/tag.js";
import CoreTag from '../../../components/Tag/Tag.js';
import { idTagFormatter } from "../../Tag/tagFormatter.js";

export default {
	name: 'TagRow',
	components: {
		CoreTag
	},
	props: {
		event: {
			type: Object,
			required: true,
		}
	},
	data() {
		return {
			tagEndpoint: ApiTempusTag
		};
	},
	computed: {
		tagValues() {
			return this.event.eindeutige_kalender_gruppen_id ? [this.event.eindeutige_kalender_gruppen_id] : [];
		},
		tags() {
			return parseJsonField(this.event.tags);
		},
	},
	watch: {
		tags: {
			handler() {
				this.$nextTick(() => this.renderTags());
			},
			deep: true
		}
	},
	methods: {
		async fetchAssignedTagsByCalender(calendarGroupId) {
			let result = await this.$api.call(
				ApiTempusTag.getTagsByCalendar(calendarGroupId),
			);

			if (result.meta.status === "success")
				return result.data.filter(tag => !!tag);

			this.$fhcAlert.alertError(
				this.$p.t("ui", "failed_assigned_tags_fetch_error_message"),
			);
			return [];
		},
		async onTagsChanged() {
			let id = this.event.eindeutige_kalender_gruppen_id;
			if (!id) return;

			this.event.tags = await this.fetchAssignedTagsByCalender(id);
		},
		renderTags() {
			let el = this.$refs.tagWrapper;
			if (!el) return;

			const tagData = this.tags.map(tag => ({
				...tag,
				notiz_id: tag.id,
				text: tag.notiz,
				bezeichnung: tag.beschreibung,
			}));

			el.replaceChildren(
				idTagFormatter(
					this.event.eindeutige_kalender_gruppen_id,
					tagData,
					this.$refs.tagComponent,
					'eindeutige_kalender_gruppen_id'
				) ?? ''
			);
		},
	},
	mounted() {
		this.renderTags();
	},
	template: /*html*/`
	<tr>
		<th>
			<div class="d-flex align-items-center">
				{{ $p.t('ui','tags') }}:
				<div class="fw-normal">
					<core-tag
						v-if="tagValues.length"
						ref="tagComponent"
						:endpoint="tagEndpoint"
						:values="tagValues"
						zuordnung_typ="eindeutige_kalender_gruppen_id"
						show-hover
						@added="onTagsChanged"
						@deleted="onTagsChanged"
						@updated="onTagsChanged"
					></core-tag>
				</div>
			</div>
		</th>
		<td class="tag-row-cell">
			<div ref="tagWrapper" class="tag-row-wrapper"></div>
		</td>
	</tr>`
};