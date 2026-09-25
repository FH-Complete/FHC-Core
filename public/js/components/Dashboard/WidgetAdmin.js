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
	provide() {
		return {
			imageSrc(src) {
				if (!src)
					return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'skin/images/fh_technikum_wien_illustration_klein.png';
				else if (src[0] == '/')
					return FHC_JS_DATA_STORAGE_OBJECT.app_root + src.substr(1);
				return src;
			},
		};
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
			data.arguments = {};
			this.currentData = JSON.parse(JSON.stringify(data));
			this.save();
		},
		remove(widget) {
			this.$refs.list.$refs.table.tabulator.deleteRow(widget.widget_id);
			this.originalData = this.currentData = null;
		},
		save() {
			this.select(this.currentData);
			this.$nextTick(() => this.$refs.list.updateOrAddDataAndSelect(this.originalData));
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
					:key="currentData.widget_id"
					v-model="currentData"
					:original-data="originalData"
					:unsaved-progress="unsavedProgress"
					@remove="remove"
					@saved="save"
				/>
				<div v-else class="h-100 d-flex justify-content-center align-items-center">
					{{ $p.t('dashboard/no_widget_selected') }}
				</div>
			</template>
		</vertical-split>
	</div>`
}
