import VerticalSplit from "../verticalsplit/verticalsplit.js";
import WidgetNew from "./WidgetAdmin/New.js";
import WidgetList from "./WidgetAdmin/List.js";
import WidgetEdit from "./WidgetAdmin/Edit.js";

export default {
	name: 'WidgetAdmin',
	components: {
		VerticalSplit,
		WidgetNew,
		WidgetList,
		WidgetEdit,
	},
	data() {
		return {
			originalData: null,
			currentData: null,
		};
	},
	computed: {
		unsavedProgress() {
			if (JSON.stringify(this.originalData) != JSON.stringify(this.currentData))
				return true;
			
			return false;
		},
	},
	methods: {
		select(data) {
 			this.originalData = data;
			this.currentData = JSON.parse(JSON.stringify(data));
		},
		create(data) {
			// TODO(chris): modify data with base info
			this.currentData = JSON.parse(JSON.stringify(data));
			this.save();
		},
		save() {
			this.select(this.currentData);
			this.$nextTick(() => {
				this.$refs.list.$refs.table.tabulator
					.updateOrAddData([this.originalData])
					.then(res => {
						res[0].select();
					})
					.catch(this.$fhcAlert.handleSystemError);
			});
		},
	},
	template: /* html */`
	<div class="widgets-admin">
		<widget-new ref="new" @create="create" />
		<vertical-split class="h-100 position-relative" use-div-height>
			<template #top>
				<widget-list
					ref="list"
					:unsaved-progress="unsavedProgress"
					@new="$refs.new?.show"
					@select="select"
				/>
			</template>
			<template #bottom>
				<widget-edit
					v-if="currentData"
					ref="edit"
					v-model="currentData"
					:original-data="originalData"
					:unsaved-progress="unsavedProgress"
					@saved="save"
				/>
				<div v-else class="h-100 d-flex justify-content-center align-items-center">
					Please select a widget
				</div>
			</template>
		</vertical-split>
	</div>`
}
