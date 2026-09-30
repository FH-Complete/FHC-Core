export default {
	name: "WidgetsReportError",
	props: {
		errors: {
			type: [ Object, Boolean ],
			required: true
		},
	},
	computed: {
		myClass() {
			if (this.errors !== true)
				return 'w-100 overflow-scroll';

			return [
				'alert',
				'alert-danger',
				'm-0',
				'h-100',
				'w-100',
				'border-0',
				'rounded-0',
				'd-flex',
				'justify-content-center',
				'align-items-center'
			];
		},
	},
	template: /*html*/ `
	<div class="widgets-report-error" :class="myClass">
		<template v-if="errors === true">
			{{ $p.t('ui/errorConfigFehlt') }}
		</template>
		<template v-else>
			<template v-for="error in errors" :key="error">
				<div v-if="error.message" class="alert alert-danger m-1">
					{{ error.message }}
				</div>
				<template v-else-if="error.messages">
					<div v-for="msg in error.messages" :key="msg" class="alert alert-danger m-1">
						{{ msg }}
					</div>
				</template>
			</template>
		</template>
	</div>
	`,
};
