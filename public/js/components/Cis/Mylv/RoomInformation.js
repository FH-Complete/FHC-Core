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
				this.loadRoomEvents();
				//this.loadReservierungen();
			});
		},

        // returns the string YYYY-MM-DD if param is instance of CalendarDate and null otherwise
        calendarDateToString: function(calendarDate){
            
            return calendarDate instanceof CalendarDate? 
            [calendarDate.y, calendarDate.m+1, calendarDate.d].join('-'):
            null; 
            
        },

		loadStunden: async function(){
			await this.$fhcApi.factory.stundenplan.getStunden().then(res => {
				res.data.forEach(std => {
					this.stunden[std.stunde] = std; // TODO(chris): geht besser
				});
			});
		},

		loadRoomEvents: async function () {
			await this.$fhcApi.factory.stundenplan.getRoomInfo(this.ort_kurzbz, this.weekFirstDay, this.weekLastDay).then(res => {
				
				if (res.data && res.data.forEach) {
					res.data.forEach((el, i) => {

						el.id = i;
						if (el.type === 'reservierung') {
							el.color = '#' + (el.farbe || 'FFFFFF');
						} else {
							el.color = '#' + (el.farbe || 'CCCCCC');
						}

						el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
						el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
						el.title = el.lehrfach;
						if (el.lehrform)
							el.title += '-' + el.lehrform;
					});
					if (this.events){
						this.events = [...this.events, ...res.data];
					}else{
						this.events = res.data;
					}
				}
			});
		},

		
		loadReservierungen: async function () {
			await this.$fhcApi.factory.stundenplan.getReservierungen(this.ort_kurzbz, this.weekFirstDay, this.weekLastDay).then(res => {
				if (res.data && res.data.forEach) {
					res.data.forEach((el, i) => {

						el.id = i;
						if (el.type === 'reservierung') {
							el.color = '#' + (el.farbe || 'FFFFFF');
						} else {
							el.color = '#' + (el.farbe || 'CCCCCC');
						}

						el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
						el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
						if(el.titel){
							
						}
						el.title = el.lehrfach;
						if (el.lehrform)
							el.title += '-' + el.lehrform;
					});

					if (this.events) {
						this.events = [...this.events, ...res.data];
					} else {
						this.events = res.data;
					}
				}
			});
		}

    },
	async mounted() {

        this.loadStunden();
		this.loadRoomEvents();
		this.loadReservierungen();

	},
    template: /*html*/`
    <div>
		<fhc-calendar @change:range="updateRange" v-slot="{event,day}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
            <a class="text-decoration-none text-dark" href="#" :title="event.orig.title + ' - ' + event.orig.lehrfach_bez + ' [' + event.orig.ort_kurzbz+']'"   >
                <div class="d-flex flex-column align-items-center justify-content-evenly h-100" >

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