import FhcFragment from "../Fragment.js";

let _uuid = {};

export default {
	inheritAttrs: false,
	components: {
		FhcFragment
	},
	inject: {
		registerToForm: {
			from: '$registerToForm',
			default: null
		},
		clearValidationForName: {
			from: '$clearValidationForName',
			default: null
		}
	},
	props: {
		bsFeedback: Boolean,
		noAutoClass: Boolean,
		noFeedback: Boolean,
		inputGroup: Boolean,
		type: String,
		name: String,
		containerClass: [String, Array, Object],
		label: String,
		// NOTE(chris): remove these from $attrs array to prevent doubled event listeners
		onInput: [Array, Function],
		'onUpdate:modelValue': [Array, Function]
	},
	data() {
		return {
			valid: undefined,
			feedback: [],
			modelValueDummy: undefined
		}
	},
	computed: {
		hasContainer() {
			if (!this.bsFeedback)
				return true;
			if (this.containerClass)
				return true;
			for (const prop in this.autoContainerClass)
				if (Object.hasOwn(this.autoContainerClass, prop))
					return true;
			return false;
		},
		acc() {
			if (!this.containerClass)
				return {};
			if (typeof this.containerClass === 'string' || this.containerClass instanceof String)
				return this.containerClass.split(' ').reduce((a,c) => {a[c] = true; return a}, {});
			if (Array.isArray(this.containerClass))
				return this.containerClass.reduce((a,c) => {a[c] = true; return a}, {});
			return this.containerClass;
		},
		autoContainerClass() {
			if (this.noAutoClass)
				return this.acc;
			
			const acc = {...this.acc};
			
			if (this.inputGroup)
				acc['input-group-item'] = true;

			if (this.lcType == 'radio' || this.lcType == 'checkbox')
				acc['form-check'] = true;
			
			if (this.inputGroup && acc['form-check']) {
				acc['input-group-item'] = false;
				acc['form-check'] = false;
				acc['input-group-text'] = true;
			}
			return acc;
		},
		lcType() {
			if (!this.type)
				return 'text';
			return this.type.toLowerCase();
		},
		tag() {
			switch (this.lcType) {
				case 'textarea':
				case 'select':
					return this.lcType;
				case 'datepicker':
					return 'VueDatePicker';
				case 'autocomplete':
					return 'PvAutocomplete';
				case 'uploadimage':
					return 'UploadImage';
				case 'uploadfile':
				case 'uploaddms':
					return 'UploadDms';
				default:
					return 'input';
			}
		},
		validationClass() {
			const classes = [];
			if (this.valid)
				classes.push('is-valid');
			else if (this.valid === false)
				classes.push('is-invalid');
			
			if (!this.noAutoClass) {
				let c = this.$attrs.class ? this.$attrs.class.split(' ') : [];
				switch (this.lcType) {
					// TODO(chris): complete list!
					case 'select':
						if (!c.includes('form-select'))
							classes.push('form-select');
						break;
					case 'range':
						if (!c.includes('form-range'))
							classes.push('form-range');
						break;
					case 'radio':
					case 'checkbox':
						// TODO(chris): maybe different handling?
						if (!c.includes('form-check-input') && !c.includes('btn-check'))
							classes.push('form-check-input');
						break;
					case 'color':
						if (!c.includes('form-control-color'))
							classes.push('form-control-color');
						if (!c.includes('form-control'))
							classes.push('form-control');
						break;
					case 'autocomplete':
					case 'datepicker':
						classes.push('p-0');
						classes.push('border-0');
						if (!c.includes('form-control'))
							classes.push('form-control');
						break;
					case 'text':
					case 'number':
					case 'password':
					case 'textarea':
						if (!c.includes('form-control'))
							classes.push('form-control');
						break;
				}
			}

			return classes;
		},
		feedbackClass() {
			if (!this.feedback || this.feedback === true)
				return '';
			if (!this.bsFeedback)
				return {
					'valid-tooltip': this.valid === true,
					'invalid-tooltip': this.valid === false
				};
			return {
				'valid-feedback': this.valid === true,
				'invalid-feedback': this.valid === false
			};
		},
		modelValueCmp: {
			get() {
				if (!this.$attrs.hasOwnProperty('modelValue'))
					return this.modelValueDummy;
				return this.$attrs.modelValue;
			},
			set(v) {
				if (!this.$attrs.hasOwnProperty('modelValue'))
					this.modelValueDummy = v;
				this.$emit('update:modelValue', v);
			}
		},
		idCmp() {
			let uuid = this.$attrs.id;
			if (this.lcType == 'datepicker')
				uuid = this.$attrs.uid;
			if (!uuid && this.label)
				uuid = 'fhc-form-input';
			if (!uuid)
				return undefined;
			if (this.lcType == 'datepicker')
				uuid = 'dp-input-' + uuid;
			if (_uuid[uuid] === undefined)
				_uuid[uuid] = 0;
			return uuid + '-' + (_uuid[uuid]++);
		}
	},
	methods: {
		clearValidation() {
			this.valid = undefined;
			this.feedback = [];
		},
		clearValidationForThisName() {
			if (this.valid === undefined)
				return;
			if (this.clearValidationForName && this.name)
				this.clearValidationForName(this.name);
			else
				this.clearValidation();
		},
		setFeedback(valid, feedback) {
			if (!feedback)
				feedback = [];
			if (!Array.isArray(feedback))
				feedback = [feedback];
			this.valid = valid;
			// NOTE(chris): On a list of radios/checkboxes only add the feedback message to the last item
			if (this.name && (this.lcType == 'radio' || this.lcType == 'checkbox')) {
				const selector = 'input[type="' + this.lcType + '"][name="' + this.name + '"]';
				if ([...this.$el.parentNode.querySelectorAll(selector)].pop() != this.$refs.input)
					return;
			}
			this.feedback = feedback;
		},
		_loadComponents() {
			if (this.tag == 'VueDatePicker' && !this._.components.VueDatePicker) {
				this._.components.VueDatePicker = Vue.defineAsyncComponent(() => import("../vueDatepicker.js.php"));
			} else if (this.tag == 'PvAutocomplete' && !this._.components.PvAutocomplete) {
				this._.components.PvAutocomplete = Vue.defineAsyncComponent(() => import(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/public/js/components/primevue/autocomplete/autocomplete.esm.min.js"));
			} else if (this.tag == 'UploadImage' && !this._.components.UploadImage) {
				this._.components.UploadImage = Vue.defineAsyncComponent(() => import("./Upload/Image.js"));
			} else if (this.tag == 'UploadDms' && !this._.components.UploadDms) {
				this._.components.UploadDms = Vue.defineAsyncComponent(() => import("./Upload/Dms.js"));
			}
		}
	},
	beforeMount() {
		this._loadComponents();
	},
	beforeUpdate() {
		this._loadComponents();
	},
	mounted() {
		if (this.registerToForm)
			this.registerToForm(this);
	},
	template: `
	<component :is="!hasContainer ? 'FhcFragment' : 'div'" class="position-relative" :class="autoContainerClass">
		<label v-if="label && lcType != 'radio' && lcType != 'checkbox'" :class="!noAutoClass && 'form-label'" :for="idCmp">{{label}}</label>
		<input v-if="tag == 'input'" :type="lcType" ref="input" v-model="modelValueCmp" v-bind="$attrs" :id="idCmp" :name="name" :class="validationClass" :modelValue="undefined" @input="clearValidationForThisName(); $emit('input', $event)">
		<textarea v-else-if="tag == 'textarea'" ref="input" v-model="modelValueCmp" v-bind="$attrs" :id="idCmp" :name="name" :class="validationClass" :modelValue="undefined" @input="clearValidationForThisName(); $emit('input', $event)"></textarea>
		<select v-else-if="tag == 'select'" ref="input" v-model="modelValueCmp" v-bind="$attrs" :id="idCmp" :name="name" :class="validationClass" :modelValue="undefined" @input="clearValidationForThisName(); $emit('input', $event)">
			<slot></slot>
		</select>
		<component
			v-else-if="tag == 'VueDatePicker'"
			ref="input"
			:is="tag"
			:type="type"
			v-model="modelValueCmp"
			v-bind="$attrs"
			:uid="idCmp ? idCmp.substr(9) : idCmp"
			:name="name"
			:class="validationClass"
			:input-class-name=
			"[...Object.entries({'form-control': !noAutoClass, 'is-valid': valid === true, 'is-invalid': valid === false}).reduce((a,[k,v]) => {if(v) a.push(k);return a}, []), ...($attrs['input-class-name'] ? $attrs['input-class-name'].split(' ') : [])].join(' ')"
			@update:model-value="clearValidationForThisName"
			>
			<slot></slot>
		</component>
		<component
			v-else-if="tag == 'PvAutocomplete'"
			ref="input"
			v-model="modelValueCmp"
			:id="idCmp"
			:input-props="{name}"
			:class="validationClass"
			:input-class="[...Object.entries({'form-control': !noAutoClass, 'is-valid': valid === true, 'is-invalid': valid === false}).reduce((a,[k,v]) => {if(v) a.push(k);return a}, []), ...($attrs['input-class'] ? $attrs['input-class'].split(' ') : [])].join(' ')"
			@update:model-value="clearValidationForThisName"
			>
			<slot></slot>
			<template #chip="data"><slot name="chip" v-bind="data"></slot></template>
			<template #header="data"><slot name="header" v-bind="data"></slot></template>
			<template #footer="data"><slot name="footer" v-bind="data"></slot></template>
			<template #option="data"><slot name="option" v-bind="data"></slot></template>
			<template #optiongroup="data"><slot name="optiongroup" v-bind="data"></slot></template>
			<template #content="data"><slot name="content" v-bind="data"></slot></template>
			<template #loader="data"><slot name="loader" v-bind="data"></slot></template>
			<template #empty="data"><slot name="empty" v-bind="data"></slot></template>
			<template #dropdownicon="data"><slot name="dropdownicon" v-bind="data"></slot></template>
			<template #removetokenicon="data"><slot name="removetokenicon" v-bind="data"></slot></template>
			<template #loadingicon="data"><slot name="loadingicon" v-bind="data"></slot></template>
		</component>
		<component
			v-else-if="tag == 'UploadDms'"
			ref="input"
			:is="tag"
			:type="type"
			v-model="modelValueCmp"
			v-bind="$attrs"
			:id="idCmp"
			:name="name"
			:class="validationClass"
			:input-class="validationClass"
			:no-list="inputGroup"
			@update:model-value="clearValidationForThisName"
			>
			<slot></slot>
		</component>
		<component
			v-else
			ref="input"
			:is="tag"
			:type="type"
			v-model="modelValueCmp"
			v-bind="$attrs"
			:id="idCmp"
			:name="name"
			:class="validationClass"
			@update:model-value="clearValidationForThisName"
			>
			<slot></slot>
		</component>
		<label v-if="label && (lcType == 'radio' || lcType == 'checkbox')" :for="idCmp" :class="!noAutoClass && 'form-check-label'">{{label}}</label>
		<div v-if="valid !== undefined && feedback.length && !noFeedback" :class="feedbackClass">
			<template v-for="(msg, i) in feedback" :key="i">
				<hr v-if="i" class="m-0">
				{{msg}}
			</template>
		</div>
	</component>
	`
}
