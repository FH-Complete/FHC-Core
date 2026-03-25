import FormInput from '../Form/Input.js';
import draggable from "../../directives/draggable.js";
import ApiCoursePicker from '../../api/factory/tempus/coursepicker.js';

export default {
	components: {
		FormInput
	},
	directives: {
		draggable
	},
	data() {
		return {
			searchparam: '',
			courses: null,
			abortController: null,
		}
	},
	methods: {
		async loadCourses() {

			const query = (this.searchparam ?? '').trim();

			if (!query)
			{
				this.courses = [];
				return;
			}

			if (query.length < 3)
				return;

			if (this.abortController)
			{
				this.abortController.abort();
			}

			this.abortController = new AbortController();
			const signal = this.abortController.signal;

			this.$api.call(ApiCoursePicker.search(query), { signal })
				.then(result => {
					this.courses = result.data.map(entry => ({
						lehreinheit_id: entry.lehreinheit_id,
						lektoren: entry.lektor,
						studiengang: entry.studiengang,
						semester: entry.semester,
						stundenblockung: entry.stundenblockung,
						wochenrythmus: entry.wochenrythmus,
						showname: `${entry.lehrfach} ${entry.lehrform}`,
						orig: {
							type: 'lehreinheit',
							lehreinheit_id: entry.lehreinheit_id,
							blockung: entry.stundenblockung,
							entry,
						}
					}));
				})
				.catch(this.$fhcAlert.handleSystemError)
		},
		dragLehreinheitCollection(course) {
			const orig = course.orig;
			return {
				type: 'lehreinheit',
				id: orig.lehreinheit_id,
				orig: orig,
				stundenblockung: course.stundenblockung,
			};
		},
		keydown: function(evt, course) {

			switch(evt.key)
			{
				case "1":
					course.tag = '#Singleweek';
					course.mode = 'single';
					break
				case "2":
					course.tag = '#Multiweek';
					course.mode = 'multi';
					break
			}
		}
	},
	template: `
	<div class="course-picker">
		<div class="p-2">
			<form-input
				:label="$p.t('ui', 'suche')"
				type="text"
				v-model="searchparam"
				@input="loadCourses"
			/>
		</div>
		<div class="overflow-auto px-2 pb-2 flex-grow-1">
			<div
				v-for="course in courses"
				:key="course.lehreinheit_id"
				class="course-picker-row"
				v-draggable:move.noimage="dragLehreinheitCollection(course)"
				tabindex="0"
			>
				<div class="d-flex justify-content-between align-items-start">
					<span class="fw-semibold small">{{ course.showname }}</span>
				</div>
				<div class="text-muted">
					<span>{{ course.studiengang }} - {{ course.semester }}</span>
				</div>
				<div class="text-muted">
					<span>{{ course.lektoren }}</span>
				</div>
				<div class="text-muted">
					<span>WR: {{ course.wochenrythmus }} Bl: {{ course.stundenblockung }}</span>
				</div>
			</div>
		</div>
		<div class="mt-auto px-2 py-2 small text-muted border-top">
			Drag & Drop on Calendar
		</div>
	</div>
`
}
