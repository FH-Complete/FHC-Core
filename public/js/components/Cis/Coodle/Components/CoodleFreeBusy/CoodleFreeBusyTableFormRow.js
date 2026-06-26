export default {
	name: "CoodleFreeBusyTableFormRow",
	props: {
		scheduleFormDataModelValue: Object,
		scheduleTypeOptions: Array,
		uid: Number | null,
	},
	emits: ["update:scheduleFormDataModelValue", "submitForm", "cancelForm"],
	computed: {
		scheduleFormData: {
			get() {
				return this.$props.scheduleFormDataModelValue;
			},
			set(newValue) {
				this.$emit("update:scheduleFormDataModelValue", newValue);
			},
		},
	},
	watch: {
		"scheduleFormData.type": {
			handler(newValue) {
				if (newValue.toLowerCase() !== "sogo") return;

				// todo: use base urls from db if appropriate
				this.scheduleFormData.url = "sogo url placeholder";
			},
		},
	},
	template: /*html*/ `
	<tr>
		<td class="border-1 py-2 px-2">
			<input v-model="scheduleFormData.description" id="scheduleDescriptionInput" type="text" class="w-100" />
		</td>
		<td class="border-1 py-2 px-2">
			<select v-model="scheduleFormData.type" id="scheduleTypeInput" class="w-100 h-100">
				<option value="null">{{ "--Select--" }}</option>
				<option v-for="option in $props.scheduleTypeOptions" :value="option.value">{{ option.text }}</option>
			</select>
		</td>
		<td class="border-1 py-2 px-2">
			<input v-model="scheduleFormData.url" id="scheduleUrlInput" type="text" class="w-100" />
		</td>
		<td class="border-1 py-2 px-2">
			<div class="d-flex flex-row align-items-center justify-content-center">
				<input
					v-model="scheduleFormData.isActive"
					id="scheduleIsActiveInput"
					type="checkbox"
					style="height:15px; width:15px;"
				/>
			</div>
		</td>
		<td class="border-1 py-2 px-2">
			<div class="w-100 d-flex flex-row gap-1 align-items-center justify-content-evenly">
			<div @click="$emit('cancelForm')" type="button" class="action py-1 px-1">
				<i class="fa-solid fa-circle-left"></i>
			</div>
			<div @click="$emit('submitForm')" type="button" class="action py-1 px-1">
				<i class="fa-solid fa-floppy-disk"></i>
			</div>
		</td>
	</tr>
	`,
};
