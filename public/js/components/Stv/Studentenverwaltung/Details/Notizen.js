import CoreNotiz from "../../../Notiz/Notiz.js";

export default {
	components: {
		CoreNotiz
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-notizen h-100 pb-3 overflow-hidden">
<!--	mit factory als endpoint	-->
		<core-notiz
			:endpoint="$fhcApi.factory.notiz.person"
			ref="formc"
			notiz-layout="twoColumnsFormLeft"
			type-id="person_id"
			:id="modelValue.person_id"
			show-document
			show-tiny-mce
			:visible-columns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</core-notiz>
		
	
<!--		
---------------------------------------------------------------------------------------------
-------------------- DESCRIPTION FOR PARAMETER PROPS ----------------------------------------
---------------------------------------------------------------------------------------------

endpoint: for corecontroller: eg: :endpoint="$fhcApi.factory.notiz.person"
(...prestudent, ...mitarbeiter, ...bestellung, ...lehreinheit, ...projekt, ...projektphase, ...projekttask, ...anrechnung)

for extensions: write own controller extending core NotizController

ref="formc"

type-id: id to which table the notizdata should be connected... eg. person_id, prestudent_id, uid (for mitarbeiter_uid), projekt_kurzbz, projektphase_id, projekttask_id,
	bestellung_id, lehreinheit_id, anrechnung_id

notizLayout: "classicFas", "twoColumnsFormLeft", twoColumnsFormRight, popupModal"

showErweitert: if true: section with following fields will be displayed:
	'verfasser', 'bearbeiter', 'von', 'bis'

showDocument: if true: section with documentHandling will be displayed

showTinyMCE: if true: section with WYSIWYG Editor for Text will be displayed

visibleColumns: list, which fields shoult be showed as default in filter component
		fullVersion: :visibleColumns="['titel','text','bearbeiter','verfasser','von','bis','dokumente','erledigt','notiz_id','notizzuordnung_id','id','lastupdate']"
		

---------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------	
-->	




<!--

---------------------------------------------------------------------------------------------
------------------------ SOME TESTDATA	-----------------------------------------------------	
---------------------------------------------------------------------------------------------


		<core-notiz
			:endpoint="$fhcApi.factory.notiz.mitarbeiter"
			ref="formc"
			type-id="uid"
			:id= "'ma0068'"
			notiz-layout="twoColumnsFormLeft"
			show-document
			show-tiny-mce
			show-erweitert
			:visible-columns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</core-notiz>
		
		<core-notiz
			:endpoint="$fhcApi.factory.notiz.prestudent"
			ref="formc"
			type-id="prestudent_id"
			:id="modelValue.prestudent_id"
			notiz-layout="twoColumnsFormLeft"
			:show-erweitert="true"
			:show-document="true"
			:showTinyMCE="true"
			:visible-columns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</core-notiz>
				
		<core-notiz
			:endpoint="$fhcApi.factory.notiz.projekt"
			ref="formc"
			type-id="projekt_kurzbz"
			:id="'EA74'" 
			notiz-layout="twoColumnsFormLeft"
			:show-erweitert="true"
			:show-document="true"
			:showTinyMCE="true"
			:visible-columns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</core-notiz>-->
		
	</div>
	`
};