import FhcCalendar from "../../Calendar/Calendar.js";
import CalendarDate from "../../../composables/CalendarDate.js";


export default{
    props:{
        ort_kurzbz: {
            type: String,
            required: true,
        }
    },
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null,
            calendarWeek: new CalendarDate(new Date()),
            
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
		updateRange: function(data){
			this.calendarWeek = new CalendarDate(data.start);
			Vue.nextTick(() => {
				this.loadEvents();
			});
		},

        // returns the string YYYY-MM-DD if param is instance of CalendarDate and null otherwise
        calendarDateToString: function(calendarDate){
            
            return calendarDate instanceof CalendarDate? 
            [calendarDate.y, calendarDate.m+1, calendarDate.d].join('-'):
            null; 
            
        },

		loadEvents: function(){

			// bundles the room_events and the reservierungen together into the this.events array
			Promise.allSettled([
				this.$fhcApi.factory.stundenplan.getRoomInfo(this.ort_kurzbz, this.weekFirstDay, this.weekLastDay),
				this.$fhcApi.factory.stundenplan.getOrtReservierungen(this.ort_kurzbz, this.weekFirstDay, this.weekLastDay)
			]).then((result) => {
				let events = [];
				result.forEach((promise_result) => {
					if(promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success"){
						
						let data = promise_result.value.data;
						// adding additional information to the events 
						if (data && data.forEach) {
							data.forEach((el, i) => {
								el.id = i;
								if (el.type === 'reservierung') {
									el.color = '#' + (el.farbe || 'FFFFFF');
								} else {
									el.color = '#' + (el.farbe || 'CCCCCC');
								}

								el.start = new Date(el.datum + ' ' + el.beginn);
								el.end = new Date(el.datum + ' ' + el.ende);
								
							});
						}
						events = events.concat(data);
					}
				})
				this.events = events;
			})
		},
		
    },
    template: /*html*/`
    <div>
		<fhc-calendar @change:range="updateRange" v-slot="{event,day}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
            <a class="text-decoration-none text-dark" href="#" :title="event.orig.title + ' - ' + event.orig.lehrfach_bez + ' [' + event.orig.ort_kurzbz+']'"   >
                <div type="button" class="d-flex flex-column align-items-center justify-content-evenly h-100" >

                        <!-- render content for stundenplan -->
                        <span >{{event.orig.topic}}</span>
                        <span v-for="gruppe in event.orig.gruppe" >{{gruppe.kuerzel}} </span>
                        <span v-for="lektor in event.orig.lektor" >{{lektor.kurzbz}}</span>
                        <!-- add the beschreibung if the event is a reservierung
                        <span v-if="event.orig.type === 'reservierung'">{{event.orig.beschreibung}}</span>-->
                        
                </div>
            </a>
        </fhc-calendar>
    </div>
    `,
};