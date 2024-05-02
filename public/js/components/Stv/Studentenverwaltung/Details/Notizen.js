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
		
		<!--oldversion ohne factory-->
<!--		<NotizComponent
			endpoint="api/frontend/v1/stv/Notiz/"
			ref="formc"
			typeId="person_id"
			:id="modelValue.person_id"
			notizLayout="twoColumnsFormLeft"
			:showErweitert="true"
			:showDocument="true"
			:showTinyMCE="false"
			:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</NotizComponent>-->
	
<!--	mit factory als endpoint	-->
		<NotizComponent
			:endpoint="$fhcApi.factory.notiz.person"
			ref="formc"
			typeId="person_id"
			:id="modelValue.person_id"
			notizLayout="twoColumnsFormLeft"
			:showErweitert="true"
			:showDocument="true"
			:showTinyMCE="true"
			:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
			>
		</NotizComponent>
		
<!--		
---------------------------------------------------------------------------------------------
-------------------- DESCRIPTION FOR PARAMETER PROPS ----------------------------------------
---------------------------------------------------------------------------------------------

notizLayout: "classicFas", "twoColumnsFormLeft", twoColumnsFormRight"

showErweitert: if true: section with following fields will be displayed:
	'verfasser', 'bearbeiter', 'von', 'bis'

showDocument: if true: section with documentHandling will be displayed

showTinyMCE: if true: section with WYSIWYG Editor for Text will be displayed

typeId: id to which table the notizdata should be connected... eg. person_id, prestudent_id, mitarbeiter_uid, projekt_kurzbz, projektphase_id, projekttask_id,
	bestellung_id, lehreinheit_id, anrechnung_id, uid  
	in progress for extensions

visibleColumns: list, which fields shoult be showed as default in filter component
		fullVersion: :visibleColumns="['titel','text','bearbeiter','verfasser','von','bis','dokumente','erledigt','notiz_id','notizzuordnung_id','id','lastupdate']"

---------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------	
-->	




<!--

---------------------------------------------------------------------------------------------
------------------------ SOME TESTDATA	-----------------------------------------------------	
---------------------------------------------------------------------------------------------


<br><br>
		<h3>Test prestudentId</h3>
		<NotizComponent
			ref="formc"
			typeId="prestudent_id"
			:id="modelValue.prestudent_id"
			>
		</NotizComponent>
		
		<br><br>
		<h3>Test mitarbeiter_uid</h3>
		<NotizComponent
			ref="formc"
			typeId="uid"
			:id="'ma0068'"
			>
		</NotizComponent>-->
		
<!--		<br><br>
		<h3>Test projekt</h3>
		<NotizComponent
			ref="formc"
			typeId="projekt_kurzbz"
			:id="'Studentenausweis'"
			>
		</NotizComponent>-->
		
	</div>
	`
};