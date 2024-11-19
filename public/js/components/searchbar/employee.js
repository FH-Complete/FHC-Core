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
        <div class="searchbar_result searchbar_employee">
    
          <div class="searchbar_grid">
              <div class="searchbar_icon">
                <action :res="this.res" :action="this.actions.defaultaction" @actionexecuted="$emit('actionexecuted')">
                  <img v-if="(typeof res.photo_url !== 'undefined') && (res.photo_url !== null)" :src="res.photo_url" 
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
                    <div class="searchbar_tablecell searchbar_label">Standard-Kostenstelle</div>
                    <div class="searchbar_tablecell searchbar_value">
                        <ul class="searchbar_inline_ul" v-if="res.standardkostenstelle.length > 0">
                          <li v-for="(stdkst, idx) in res.standardkostenstelle" :key="idx">{{ stdkst }}</li>
                        </ul>
                        <span v-else="">keine</span>
                    </div>
                  </div>

                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell searchbar_label">Organisations-Einheit</div>
                    <div class="searchbar_tablecell searchbar_value">
                        <ul class="searchbar_inline_ul" v-if="res.organisationunit_name.length > 0">
                          <li v-for="(oe, idx) in res.organisationunit_name" :key="idx">{{ oe }}</li>
                        </ul>
                        <span v-else="">keine</span> 
                    </div>
                  </div>
        
                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell searchbar_label">EMail</div>
                    <div class="searchbar_tablecell searchbar_value">
                        <a :href="this.mailtourl">
                          {{ res.email }}
                        </a>
                    </div>
                  </div>
        
                  <div class="searchbar_tablerow">
                    <div class="searchbar_tablecell searchbar_label">Telefon</div>
                    <div class="searchbar_tablecell searchbar_value">
                        <a :href="this.telurl">
                          {{ res.phone }}
                        </a>
                    </div>
                  </div>        
        
                </div>
        
                <actions :res="this.res" :actions="this.actions.childactions" @actionexecuted="$emit('actionexecuted')"></actions>
        
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
        telurl: function() {
            return 'tel:' + this.res.phone;
        }
    }
};