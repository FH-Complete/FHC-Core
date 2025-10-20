import ApiCoursePicker from '../../api/factory/coursepicker.js';

export default {
	components: {
	},
	provide() {
		return {
		};
	},
	data() {
		return {
			courses: null
		}
	},
	props: {

	},
	computed: {
	},
	methods: {
		loadCourses: function(){

			Promise.allSettled([
				 this.$api.call(ApiCoursePicker.getCourses("test")),
			]).then((result) => {
				let promise_events = [];
				result.forEach((promise_result) => {
					if (promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success") {
						let data = promise_result.value.data;
						if (data && data.forEach) {

							data.forEach((entry, i) => {
								entry.showname = entry.studiengang_kurzbz+entry.semester+' - ' + entry.kurzbz + ' ' + entry.lektoren.toString();
								entry.tag = '';
								entry.mode = 'single';
							});
						}
						promise_events = promise_events.concat(data);
					}
				})
				this.courses = promise_events;

			});
		},
		dragstart: function(evt, course) {
			const transferdata = {
				type: 'lehreinheit',
				id: course.lehreinheit_id,
				mode: course.mode
			};

			event.dataTransfer.setData('text', JSON.stringify(transferdata));
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
	created() {
		this.loadCourses();
		//document.addEventListener('keydown', function () { console.log("a"); });
	},
	mounted() {
	},
	template: /*html*/`
	<div ref="container" class="fhc-coursechooser">
		<div id="coursechooser">
			<span id="coursechooserheader">Course</span>
			<input type="text" placeholder="Search"/>
			<div v-for="course in courses" class="eckerl" draggable="true" @dragstart="dragstart(event, course)" tabindex="0" @keyup="keydown($event, course)">
				{{course.showname}}
				{{course.tag}}
			</div>
			<span id="coursechooserfooter">Drag & Drop on Calender</span>
		</div>
	</div>`
}
