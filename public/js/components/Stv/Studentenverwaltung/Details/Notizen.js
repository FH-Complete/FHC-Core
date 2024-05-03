import NotizComponent from "../../../Notiz/NotizComponent.js";

export default {
	components: {
		NotizComponent
	},
	props: {
		modelValue: Object
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<h3>Notizen</h3>
		
<!--	mit factory als endpoint	-->
		<NotizComponent
			:endpoint="$fhcApi.factory.notiz.person"
			ref="formc"
			typeId="person_id"
			:id="modelValue.person_id"
			notizLayout="twoColumnsFormLeft"
			:showErweitert="false"
			:showDocument="true"
			:showTinyMCE="false"
			:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</NotizComponent>
		
	
<!--		
---------------------------------------------------------------------------------------------
-------------------- DESCRIPTION FOR PARAMETER PROPS ----------------------------------------
---------------------------------------------------------------------------------------------

endpoint: for corecontroller: eg: :endpoint="$fhcApi.factory.notiz.person"
(...prestudent, ...mitarbeiter, ...bestellung, ...lehreinheit, ...projekt, ...projektphase, ...projekttask, ...anrechnung)

for extensions: write own controller extending core NotizController

ref="formc"

typeId: id to which table the notizdata should be connected... eg. person_id, prestudent_id, uid (for mitarbeiter_uid), projekt_kurzbz, projektphase_id, projekttask_id,
	bestellung_id, lehreinheit_id, anrechnung_id

notizLayout: "classicFas", "twoColumnsFormLeft", twoColumnsFormRight"

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


		<NotizComponent
			:endpoint="$fhcApi.factory.notiz.mitarbeiter"
			ref="formc"
			typeId="uid"
			:id= "'ma0068'"
			notizLayout="twoColumnsFormLeft"
			:showErweitert="true"
			:showDocument="true"
			:showTinyMCE="false"
			:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</NotizComponent>
		
		<NotizComponent
			:endpoint="$fhcApi.factory.notiz.prestudent"
			ref="formc"
			typeId="prestudent_id"
			:id="modelValue.prestudent_id"
			notizLayout="twoColumnsFormLeft"
			:showErweitert="true"
			:showDocument="true"
			:showTinyMCE="true"
			:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</NotizComponent>
				
		<NotizComponent
			:endpoint="$fhcApi.factory.notiz.projekt"
			ref="formc"
			typeId="projekt_kurzbz"
			:id="'EA74'" 
			notizLayout="twoColumnsFormLeft"
			:showErweitert="true"
			:showDocument="true"
			:showTinyMCE="true"
			:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</NotizComponent>-->
		
	</div>
	`
};