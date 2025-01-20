export default {
    props: { 
        res: {
            type: Object
        }, 
        action: {
            type: Object
        }, 
        cssclass: {
            type: String,
            default: ''
        }
    },
    emits: [ 'actionexecuted' ],
    template: `
		<template v-if="this.renderif()">
			<a :class="this.cssclass" :href="this.getactionhref()"
				@click="(this.action.type === 'function') ? this.execaction() : null">
				<slot>Action</slot>
			</a>
		</template>
		<template v-else>
			<slot>Action</slot>
		</template>
    `,
    methods: {
        getactionhref: function() {
            return (this.action.type === 'link') ? this.action.action(this.res) 
                : 'javascript:void(0);';
        },
        execaction: function() { 
            this.action.action(this.res);
            this.$emit('actionexecuted');
        },
		renderif: function() {
			if(this.action?.renderif === undefined) {
				return true;
			}

			return this.action.renderif(this.res);
		}
    }
};