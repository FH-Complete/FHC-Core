import FhcCalendar from "../../components/Calendar/Calendar.js";
import CalendarDate from "../../composables/CalendarDate.js";
import FhcApi from "../../plugin/FhcApi.js";
import Phrasen from "../../plugin/Phrasen.js";
const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null,
            calendarWeek: new CalendarDate(new Date()),
            events_loaded:false,
        }
	},
    computed:{
        currentDate: function(){
            return new Date(this.calendarWeek.y, this.calendarWeek.m, this.calendarWeek.d);
        },
        weekFirstDay: function(){
            return this.calendarDateToString(this.calendarWeek.cdFirstDayOfWeek);
        },
        weekLastDay: function(){
            return this.calendarDateToString(this.calendarWeek.cdLastDayOfWeek);
        },
    },
    methods:{
        // returns the string YYYY-MM-DD if param is instance of CalendarDate and null otherwise
        calendarDateToString: function(calendarDate){
            
            return calendarDate instanceof CalendarDate? 
            [calendarDate.y, calendarDate.m+1, calendarDate.d].join('-'):
            null; 
            
        },
        
    },
	created() {

        this.$fhcApi.factory.stundenplan.getStunden().then(res =>{
            res.data.forEach(std => {
				this.stunden[std.stunde] = std; // TODO(chris): geht besser
			});


           // old testing room EDV_A6.09
            this.$fhcApi.factory.stundenplan.getRoomInfo('EDV_F4.26', this.weekFirstDay, this.weekLastDay).then(res =>{
                let events;
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {

                        el.id = i;
                        el.color = '#' + (el.farbe || 'CCCCCC');
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.lehrfach;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    
                    this.events = res.data;
                }

                this.$fhcApi.factory.stundenplan.getReservierungen('EDV_F4.26', this.weekFirstDay, this.weekLastDay).then(res => {
                    if (res.data && res.data.forEach) {
                        res.data.forEach((el, i) => {
                            el.reservierung = true;
                            el.color = '#' + (el.farbe || 'ffffff');
                            el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                            el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                            el.title = el.titel;
                            if (el.lehrform)
                                el.title += '-' + el.lehrform;
                        });
                        
                    }
    
                    let reservierungs_events = res.data;
                    console.log(reservierungs_events,"this are the reservierungs events that are getting from the db query")
                    this.events = [...(this.events?this.events:[]),...reservierungs_events];
                    this.events_loaded = true;
                    
                }); 
            });

           
        });

	},
    template: /*html*/`
    <div>
        <fhc-calendar v-if="events_loaded" v-slot="{event,day}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
           
            <a class="text-decoration-none text-dark" href="#" :title="event.orig.title + ' - ' + event.orig.lehrfach_bez + ' [' + event.orig.ort_kurzbz+']'"   >    
                <div class="d-flex flex-column align-items-center justify-content-evenly h-100" :style="{'background-color':event.orig.color}">
                    <template v-if="event.orig.reservierung">    
                        <!-- render content for reservierungen -->
                        <span>{{event.orig.title}}</span>
                        <span>{{event.orig.gruppe_kurzbz?event.orig.gruppe_kurzbz:event.orig.stg}}</span>
                        <span v-for="(item, index) in event.orig.person_kurzbz.split('/')" :key="index">{{item}}</span>
                    </template>
                    <template v-else>
                        <!-- render content for stundenplan -->
                        <span>{{event.orig.lv_info}}</span>	
                        <span v-for="(item, index) in event.orig.stg.split('/')" :key="index">{{item}}</span>
                        <span >{{event.orig.lektor}}</span>
                    </template>
                </div>
            </a>
        </fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.use(FhcApi);
app.use(Phrasen);
app.mount('#content');