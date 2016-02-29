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
    	
		<th class="span4">Name</th>
		<th class="span4">Give Name</th>
        <th class="span4">Family Name</th>
 
	</tr>
</thead>            
<tbody>
 <?php
  $foaf = EasyRdf_Graph::newAndLoad('http://njh.me/foaf.rdf');
  $me = $foaf->primaryTopic();
?>
	<tr>
    	
        <td>  <?php echo $me->get('foaf:name') ?></td>
        <td> <?= $me->get('foaf:givenName') ?> </td>
		<td>   <?= $me->get('foaf:familyName') ?></td>
		
        
	</tr>            

</tbody>
</table>
  
                   
               
       </div>