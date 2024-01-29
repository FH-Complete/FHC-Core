import action from "./action.js";

export default {
    props: [ "res", "actions" ],
    components: {
        action: action
    },
    emits: [ 'actionexecuted' ],
    template: `
        <ul class="searchbar_actions" v-if="this.actions.length > 0">
          <li v-for="(action, index) in this.actions" :key="action.label">            
            <action :res="this.res" :action="action" :cssclass="'btn btn-primary btn-sm'" @actionexecuted="$emit('actionexecuted')">
                <i v-if="this.hasicon(index)" :class="this.geticonclass(index)"></i>
                <span class="p-2">{{ action.label }}</span>
            </action>
          </li>
        </ul>
        <div class="mb-3" v-else=""></div>
    `,
    methods: {
        hasicon: function(index) {
            return (typeof this.actions[index].icon !== "undefined");
        },
        geticonclass: function(index) {            
            return this.actions[index].icon;
        }
    }
};