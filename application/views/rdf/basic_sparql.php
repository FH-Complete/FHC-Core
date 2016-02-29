<style type="text/css">
.table tr th {
	text-align:center;
	background: -moz-linear-gradient(top, #FAFAFA 0%, #E9E9E9 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #FAFAFA), color-stop(100%, #E9E9E9));background: -webkit-linear-gradient(top, #FAFAFA 0%, #E9E9E9 100%);
	background: -o-linear-gradient(top, #FAFAFA 0%, #E9E9E9 100%);
	background: -ms-linear-gradient(top, #FAFAFA 0%, #E9E9E9 100%); 
	background: linear-gradient(top, #FAFAFA 0%, #E9E9E9 100%);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr = '#FAFAFA', endColorstr = '#E9E9E9');-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='#FAFAFA', endColorstr='#E9E9E9')";
	border: 1px solid #D5D5D5;
}
</style>
<div class="bootstrap-widget-header">
<i class="icon-home icon-white"></i><h3></h3>
</div>
<div class="bootstrap-widget-content">
<div class="container">
<div class="pull-left">

</div>
</div>




<table class="table table-condensed table-hover table-bordered table-striped">
<thead>
	<tr>
    	
		<th class="span4">Label</th>
		<th class="span4">Country</th>
  
 
	</tr>
</thead>            
<tbody>

<?php
 	EasyRdf_Namespace::set('category', 'http://dbpedia.org/resource/Category:');
    EasyRdf_Namespace::set('dbpedia', 'http://dbpedia.org/resource/');
    EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
    EasyRdf_Namespace::set('dbp', 'http://dbpedia.org/property/');

    $sparql = new EasyRdf_Sparql_Client('http://dbpedia.org/sparql');


    $result = $sparql->query(
        'SELECT * WHERE {'.
        '  ?country rdf:type dbo:Country .'.
        '  ?country rdfs:label ?label .'.
        '  ?country dc:subject category:Member_states_of_the_United_Nations .'.
        '  FILTER ( lang(?label) = "en" )'.
        '} ORDER BY ?label'
    );
    
?>
 Total number of countries: <?= $result->numRows() ?>   
 <?php foreach ($result as $row ): ?>  
<tr>
    	<td> <a href="http://dbpedia.org/resource/<?php echo $row->label ?>"><?php echo $row->label ?></a></td>
        <td><a href="http://dbpedia.org/resource/">  <?php echo $row->country ?></td>
    
		
        
	</tr> 
	     <?php endforeach; ?>


</tbody>
</table>
  
                   
               
       </div>
