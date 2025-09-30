export default {
	name: "FilterItem",
	props: {
		modelValue: Object,
		filterConfig: Array
	},
	emits: [
		'update:modelValue',
		'remove'
	],
	data() {
		return {
			//type: this.modelValue.type
		};
	},
	computed: {
		value: {
			get() {
				return this.modelValue;
			},
			set(value) {
				this.$emit('update:modelValue', value);
			}
		},
		filterid: {
			get() {
				return this.modelValue.filterid
			},
			set(filterid) {
				const config = this.filterConfig.find(config => config.id == filterid);
				const dynamic = Object.fromEntries(
					Object.keys(config.dynamic || {}).map(key => {
						return [
							key,
							config.dynamic[key].default
						];
					})
				);
				this.value = {
					filterid,
					type: config.type,
					...(config.fixed || {}),
					...dynamic
				};
			}
		},
		currentConfig() {
			return this.filterConfig.find(config => config.id == this.filterid);
		}
	},
	methods: {
		update() {
			this.value = this.value;
		}
	},
	template: /* html */`
	<div class="stv-list-filter-item input-group">
		<label
			class="input-group-text col-4"
			for="stv-list-filter-konto-count-0"
		>
			{{ $p.t('stv/filter_for') }}
		</label>
		<select
			v-model="filterid"
			id="stv-list-filter-konto-count-0"
			class="form-select"
		>
			<option
				v-for="(filter, i) in filterConfig"
				:key="i"
				:value="filter.id"
			>
				{{ filter.label }}
			</option>
		</select>
		<template v-for="(conf, key) in currentConfig?.dynamic" :key="key">
			<select
				v-if="conf.type == 'select'"
				v-model="modelValue[key]"
				class="form-select"
				@input="update"
			>
				<option
					v-for="(label, value) in conf.values"
					:key="conf.value_key ? label[conf.value_key] : value"
					:value="conf.value_key ? label[conf.value_key] : value"
				>
					{{ conf.label_key ? label[conf.label_key] : label }}
				</option>
			</select>
			<template v-else-if="conf.type == 'bool'">
				<div class="input-group-text">
					<label class="form-check-label">
						<input
							v-model="modelValue[key]"
							type="checkbox"
							class="form-check-input me-1"
							@input="update"
						>
						{{ conf.label }}
					</label>
				</div>
			</template>
		</template>
		<button
			class="btn btn-outline-secondary"
			:title="$p.t('ui/entfernen')"
			:aria-label="$p.t('ui/entfernen')"
			@click="$emit('remove')"
		><i class="fa fa-times"></i></button>
	</div>`
};
