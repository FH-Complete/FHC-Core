<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
  <html>
  <body>
    <h2><xsl:value-of select="MODEL/PROPERTIES/PROJECTNAME"/> Database to PHP Array</h2>
    <pre><code>
&lt;?php

$datatypes=array
(	
	<xsl:for-each select="PERModelPG83/UserDataTypes/PERUserDataTypePG83">
	'<xsl:value-of select="Id"/>'  => array
	(
    		"id" => "<xsl:value-of select="Id"/>" ,
  	  	"name" => "<xsl:value-of select="Name"/>" ,
    		"caption" => "<xsl:value-of select="Caption"/>" ,
		"length" => "<xsl:value-of select="InternalLength"/>" ,
    		"default" => "<xsl:value-of select="Default"/>" ,
    		"comments" => "<xsl:value-of select="Comments"/>" ,
    		"ordinal" => "<xsl:value-of select="Ordinal"/>"
	),
    </xsl:for-each>
);

$schemas=array
(	
	<xsl:for-each select="PERModelPG83/Schemas/PERSchemaPG83">
	'<xsl:value-of select="Id"/>'  => array
	(
    		"id" => "<xsl:value-of select="Id"/>" ,
    		"name" => "<xsl:value-of select="Name"/>" ,
    		"caption" => "<xsl:value-of select="Caption"/>" ,
    		"comments" => "<xsl:value-of select="Comments"/>" ,
    		"ordinal" => "<xsl:value-of select="Ordinal"/>"
	),
    </xsl:for-each>
);

$tabellen=array
(	
	<xsl:for-each select="PERModelPG83/Entities/PEREntityPG83">
	'<xsl:value-of select="Id"/>'  => array
	(
	    	"id" => "<xsl:value-of select="Id"/>" ,
	    	"name" => "<xsl:value-of select="Name"/>" ,
	    	"caption" => "<xsl:value-of select="Caption"/>" ,
	    	"comments" => "<xsl:value-of select="Comments"/>" ,
	    	"storage" => "<xsl:value-of select="STORAGE"/>" ,
	    	"schemaid" => "<xsl:value-of select="Schema/Id"/>" ,
	    	"attribute"  => array
	    	(
			<xsl:for-each select="Attributes/PERAttributePG83">
			'<xsl:value-of select="Id"/>' => array
			(
				"id" => "<xsl:value-of select="Id"/>" ,
				"name" => "<xsl:value-of select="Name"/>" ,
				"caption" => "<xsl:value-of select="Caption"/>" ,
				"ordinal" => "<xsl:value-of select="Ordinal"/>" ,
				"pk" => "<xsl:value-of select="PKForeignKeys/Id"/>" ,
				"datatypeid" => "<xsl:value-of select="DataType/Id"/><xsl:value-of select="UserDataType/Id"/>" ,
				"datatypeparam1" => "<xsl:value-of select="DataTypeParam1"/>" ,
				"datatypeparam2" => "<xsl:value-of select="DataTypeParam2"/>" ,
				"length" => "<xsl:value-of select="DataTypeParam1"/>",
				"unique" => "<xsl:value-of select="Unique"/>",
				"notnull" => "<xsl:value-of select="NotNull"/>",
				"defaultvalue" => "<xsl:value-of select="DefaultValue"/>",
				"checkconstraintname" => "<xsl:value-of select="CheckConstraintName"/>",
				"checkconstraint" => "<xsl:value-of select="CheckConstraint"/>",
				"description" => "<xsl:value-of select="DESCRIPTION"/>"
			),
			</xsl:for-each>
	   	),
		"keyconstraint"  => array
	    	(
			<xsl:for-each select="Keys/PERKeyConstraintPG83">
			'<xsl:value-of select="Id"/>' => array
			(
				"id" => "<xsl:value-of select="Id"/>" ,
				"name" => "<xsl:value-of select="Name"/>" ,
				"caption" => "<xsl:value-of select="Caption"/>" ,
				"ordinal" => "<xsl:value-of select="Ordinal"/>" ,
				"comments" => "<xsl:value-of select="Comments"/>" ,
				"keyconstraintitem"  => array
			    	(
					<xsl:for-each select="KeyItems/PERKeyConstraintItemPG83">
			   		'<xsl:value-of select="Id"/>' => array
			   		(
		   				"id" => "<xsl:value-of select="Id"/>" ,
		   				"name" => "<xsl:value-of select="Name"/>" ,
		   				"caption" => "<xsl:value-of select="Caption"/>" ,
		   				"ordinal" => "<xsl:value-of select="Ordinal"/>" ,
		   				"comments" => "<xsl:value-of select="Comments"/>" ,
		   				"description" => "<xsl:value-of select="DESCRIPTION"/>",
						"attribute"  => array
					    	(
							<xsl:for-each select="Attribute/Id">"id" => "<xsl:value-of select="."/>",</xsl:for-each>
				   		),
						"foreignkeys"  => array
					    	(
							<xsl:for-each select="ForeignKeys/Id">"id" => "<xsl:value-of select="."/>",</xsl:for-each>
				   		)
		   			),
		   			</xsl:for-each>
		   		),
			),
			</xsl:for-each>
		),
   	),
    	</xsl:for-each>
);

$relations=array
(	
	<xsl:for-each select="PERModelPG83/Relations/PERRelationPG83">
	'<xsl:value-of select="Id"/>'  => array
	(
	    	"id" => "<xsl:value-of select="Id"/>" ,
	    	"name" => "<xsl:value-of select="Name"/>" ,
	    	"caption" => "<xsl:value-of select="Caption"/>" ,
	    	"comments" => "<xsl:value-of select="Comments"/>" ,
	    	"notes" => "<xsl:value-of select="Notes"/>",
	    	"identifying" => "<xsl:value-of select="Identifying"/>",
	    	"mandatoryparent" => "<xsl:value-of select="MandatoryParent"/>",
	    	"mandatorychild" => "<xsl:value-of select="MandatoryChild"/>",
	    	"cardinalitychild" => "<xsl:value-of select="CardinalityChild"/>",
	    	"refintegrityparentupdate" => "<xsl:value-of select="RefIntegrityParentUpdate"/>" ,
	    	"refintegrityparentdelete" => "<xsl:value-of select="RefIntegrityParentDelete"/>" ,
	    	"refintegritychildupdate" => "<xsl:value-of select="RefIntegrityChildUpdate"/>" ,
	    	"refintegritychildinsert" => "<xsl:value-of select="RefIntegrityChildInsert"/>" ,
		"key"  => array
	    	(
			<xsl:for-each select="Key/Id">"id" => "<xsl:value-of select="."/>",</xsl:for-each>
   		),
	    	"foreignkeys"  => array
	    	(
			<xsl:for-each select="ForeignKeys/PERForeignKeyPG83">
			'<xsl:value-of select="Id"/>' => array
			(
				"id" => "<xsl:value-of select="Id"/>",
				"name" => "<xsl:value-of select="Name"/>",
				"comments" => "<xsl:value-of select="Comments"/>",
				"notes" => "<xsl:value-of select="Notes"/>",
				"deferred" => "<xsl:value-of select="Deferred"/>",
				"deferrable" => "<xsl:value-of select="Deferrable"/>",
				"matchtype" => "<xsl:value-of select="MatchType"/>",
				"createindextofk" => "<xsl:value-of select="CreateIndexToFK"/>",
				"attrparent"  => array
			    	(
					<xsl:for-each select="AttrParent/Id">"id" => "<xsl:value-of select="."/>",</xsl:for-each>
		   		),
				"attrchild"  => array
			    	(
					<xsl:for-each select="AttrChild/Id">"id" => "<xsl:value-of select="."/>",</xsl:for-each>
		   		),
				"KeyConstraintItem"  => array
			    	(
					<xsl:for-each select="KeyConstraintItem/Id">"id" => "<xsl:value-of select="."/>",</xsl:for-each>
		   		)
			),
			</xsl:for-each>
	   	)
   	),
    	</xsl:for-each>
);

$datatypes['{ECB8F02F-B683-4252-8508-ED9D064C9AF3}']=array
(
    	"id" => "{ECB8F02F-B683-4252-8508-ED9D064C9AF3}",
    	"name" => "Character Varying",
    	"caption" => "varchar",
	"length" => "1",
	"default" => "''" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{5F0FB0CB-62A1-4BDC-A4DA-882CACFC296A}']=array
(
    	"id" => "{5F0FB0CB-62A1-4BDC-A4DA-882CACFC296A}",
    	"name" => "Serial",
    	"caption" => "serial",
	"length" => "0",
	"default" => "0" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{342E3F36-138D-40F7-B1B2-D9489C848835}']=array
(
    	"id" => "{342E3F36-138D-40F7-B1B2-D9489C848835}",
    	"name" => "Timestamp",
    	"caption" => "timestamp",
	"length" => "0",
	"default" => "0" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{F6C99ABF-677B-48C6-920E-F375B79C336D}']=array
(
    	"id" => "{F6C99ABF-677B-48C6-920E-F375B79C336D}",
    	"name" => "Big Integer",
    	"caption" => "bigint",
	"length" => "0",
	"default" => "0" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{8D91E2A4-12F5-40E3-BAC2-BFCF7BE1C8B7}']=array
(
    	"id" => "{8D91E2A4-12F5-40E3-BAC2-BFCF7BE1C8B7}",
    	"name" => "Text",
    	"caption" => "text",
	"length" => "0",
	"default" => "''" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{361EF147-269D-4247-8F7C-5A3876A3999A}']=array
(
    	"id" => "{361EF147-269D-4247-8F7C-5A3876A3999A}",
    	"name" => "Integer",
    	"caption" => "integer",
	"length" => "0",
	"default" => "0" ,
    	"comments" => "" ,
    	"ordinal" => "0"
);
$datatypes['{3AA5E900-D254-4FBD-AD67-AD230407284C}']=array
(
    	"id" => "{3AA5E900-D254-4FBD-AD67-AD230407284C}",
    	"name" => "Small Integer",
    	"caption" => "smallint",
	"length" => "0",
	"default" => "0" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{D64069A5-B04A-490B-B0A2-5144DEA81A2E}']=array
(
    	"id" => "{D64069A5-B04A-490B-B0A2-5144DEA81A2E}",
    	"name" => "Boolean",
    	"caption" => "boolean",
	"length" => "0",
	"default" => "true" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{E12D6C2A-E13A-4877-B9C4-AA7BA668C316}']=array
(
    	"id" => "{E12D6C2A-E13A-4877-B9C4-AA7BA668C316}",
    	"name" => "Character",
    	"caption" => "char",
	"length" => "1",
	"default" => "''" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{5EBD99F4-5263-4410-9892-11DB7C2DF84B}']=array
(
    	"id" => "{5EBD99F4-5263-4410-9892-11DB7C2DF84B}",
    	"name" => "Date",
    	"caption" => "date",
	"length" => "0",
	"default" => "'1970-01-01'" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{3DD56C5A-B10A-4E02-8CB2-C7B4880B63DD}']=array
(
    	"id" => "{3DD56C5A-B10A-4E02-8CB2-C7B4880B63DD}",
    	"name" => "Time",
    	"caption" => "time",
	"length" => "0",
	"default" => "'00:00:00'" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{E62BD1D3-18CA-4571-9A16-606FF04DC894}']=array
(
    	"id" => "{E62BD1D3-18CA-4571-9A16-606FF04DC894}",
    	"name" => "Numeric",
    	"caption" => "numeric",
	"length" => "0",
	"default" => "'0'" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['{36A9DD5B-49C4-4BB7-86DB-2D4BCCAF58AF}']=array
(
    	"id" => "{36A9DD5B-49C4-4BB7-86DB-2D4BCCAF58AF}",
    	"name" => "Real",
    	"caption" => "real",
	"length" => "0",
	"default" => "'0'" ,
    	"comments" => "",
    	"ordinal" => "0"
);
$datatypes['']=array
(
    	"id" => "",
    	"name" => "",
    	"caption" => "",
	"length" => "0",
	"default" => "" ,
    	"comments" => "",
    	"ordinal" => "0"
);

?&gt;
  	</code></pre>
  </body>
  </html>
</xsl:template>
</xsl:transform>
