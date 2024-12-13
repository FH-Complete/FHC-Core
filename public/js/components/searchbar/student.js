import action from "./action.js";
import actions from "./actions.js";

export default {
    props: [ "res", "actions" ],
    components: {
        action: action,
        actions: actions
    },
    emits: [ 'actionexecuted' ],
    template: `
        <div class="searchbar_result searchbar_student">
        
            <div class="searchbar_grid">
                <div class="searchbar_icon">
                    <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                        <img v-if="(typeof res.foto !== 'undefined') && (res.foto !== null)"
                             :src="studentImage"
                             class="rounded" style="max-height: 120px; max-width: 90px;" />
                        <i v-else class="fas fa-user-circle fa-5x"></i>
                    </action>
                </div>
        
                <div class="searchbar_data">
                    <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                        <span class="fw-bold">{{ res.name }}</span>
                    </action>
        
                    <div class="mb-3"></div>
                
                    <div class="searchbar_table">
        
                        <div class="searchbar_tablerow">
                            <div class="searchbar_tablecell">Studiengang</div>
                            <div class="searchbar_tablecell">
                                {{ res.studiengang }}
                            </div>
                        </div>

						<div class="searchbar_tablerow">
                            <div class="searchbar_tablecell">Verband</div>
                            <div class="searchbar_tablecell">
                                {{ res.verband }}
                            </div>
                        </div>
        
                        <div class="searchbar_tablerow">
                            <div class="searchbar_tablecell">Matrikelnummer</div>
                            <div class="searchbar_tablecell">
                                {{ res.matrikelnr }}
                            </div>
                        </div>
        
                        <div class="searchbar_tablerow">
                            <div class="searchbar_tablecell">EMail</div>
                            <div class="searchbar_tablecell">
                                <a :href="this.mailtourl">
                                    {{ res.email }}
                                </a>
                            </div>
                        </div>
        
                    </div>
        
                    <actions :res="this.res" :actions="this.actions.childactions"
                             @actionexecuted="$emit('actionexecuted')"></actions>
        
                </div>
            </div>
        
        </div>
    `,
    methods: {
    },
    computed: {
        mailtourl: function() {
            return 'mailto:' + this.res.email;
        },
		studentImage: function () {
			if (!this.res.foto) return;
			return 'data:image/jpeg;base64,'.concat(this.res.foto);
		},
    }
};