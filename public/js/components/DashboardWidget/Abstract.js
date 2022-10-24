export default {
	props: [
		"config",
		"width",
		"height",
		"configMode"
	],
	emits: [
		"setConfig",
		"change" // TODO(chris): do we need this?
	]
}
