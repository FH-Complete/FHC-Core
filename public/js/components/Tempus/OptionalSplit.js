import HorizontalSplit from '../horizontalsplit/horizontalsplit.js';

export default {
	name: 'OptionalSplit',
	components: { HorizontalSplit },
	props: {
		split: { type: Boolean, default: true },
		defaultRatio: { type: Array, default: () => [85, 15] },
	},
	template: `
		<horizontal-split v-if="split" class="h-100" :defaultRatio="defaultRatio">
			<template #left><slot name="left"></slot></template>
			<template #right><slot name="right"></slot></template>
		</horizontal-split>
		<div v-else class="h-100">
			<slot name="left"></slot>
		</div>
	`
};