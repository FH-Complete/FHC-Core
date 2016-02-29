
<div class="bootstrap-widget-header">
<i class="icon-home icon-white"></i><h3></h3>
</div>
<div class="bootstrap-widget-content">
<div class="container">
<div class="pull-left">

</div>
</div>


<form name="my-form" id="my-form" action="#" method="post" enctype="multipart/form-data">

    <div class="control-group">
    <label class="control-label" for="kategori">FOAF</label>
    <div class="controls">
      <input type="text" id="uri" name="uri" class="span3" value="http://njh.me/foaf.rdf">
    </div>
  </div>
    <div class="control-group">
  <div class="controls">
    <button type="submit" name="simpan" class="btn btn-info btn-small"><i class="icon-ok-circle"></i> Simpan</button>
    </div>
    </div>
</form>
 <h3>FOAF me</h3>
<?php 

  echo $this->load->view('rdf/html_tag_helpers');


  if(isset($_REQUEST['uri'])): ?>




    <?php $graph = EasyRdf_Graph::newAndLoad($_REQUEST['uri']); ?>

          <?php if ($graph->type() == 'foaf:PersonalProfileDocument'): ?>
                <?php echo $person = $graph->primaryTopic(); ?>
          <?php elseif ($graph->type() == 'foaf:Person'): ?>
                <?php echo $person = $graph->resource(); ?>
    <?php endif; ?>

<?php endif; ?>
<!--   Start -->

   <?php if (isset($person)): ?>


<table class="table table-condensed table-hover table-bordered table-striped">
<thead>
    <tr>
        
        <th class="span4">Name</th>
        <th class="span4">Homepage</th>
  
 
    </tr>
</thead>            
<tbody>


 
<tr>
     <td><?= $person->get('foaf:name') ?> </td>
    <td><a href="<?= $person->get('foaf:homepage') ?>"> <?= $person->get('foaf:homepage') ?></td>
        
    </tr> 
</tbody>
</table>



<!-- NEw Format -->
 <h3>Known Person</h3>

 <?php foreach ($person->all('foaf:knows') as $friend) { 
  $label = $friend->label(); 

   if (!$label) {
                $label = $friend->getUri();
            }
  ?>


<table class="table table-condensed table-hover table-bordered table-striped">
            
<tbody>



    <tr>
      <?php if($friend->isBNode()): ?>
          <td><?= $label ?></td>
    <?php else: ?>

  <?php  echo "<li>".link_to_self($label, 'uri='.urlencode($friend))."</li>"; ?>
     
           </td>
   <?php endif; ?>
    </tr> 

      


</tbody>
</table>

      

<?php 
  
} ?>


</div>




<!--endif paling atas-->
<?php endif; ?>