<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $this->config->item('nama_aplikasi');?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Administrator PORTAL Ujian Nasional 2014">
    <meta name="keywords" content="portal un, portal un banten, ujian nasional, pengumuman ujian nasional 2014">
	<meta name="author" content="Deddy Rusdiansyah">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url();?>asset/favicon.ico" />
    <!-- Bootstrap -->

     <link  rel="stylesheet"  href="<?php echo base_url();?>asset/css/bootstrap-checkbox.css" >
    
    <link href="<?php echo base_url();?>asset/css/bootstrap.css" rel="stylesheet" media="screen">
    <link href="<?php echo base_url();?>asset/css/bootstrap-box.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="<?php echo base_url();?>asset/css/bootstrap-responsive.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>asset/css/custom.css" />
    <link type="text/css" href="<?php echo base_url();?>asset/css/custom-theme/jquery-ui-1.10.0.custom.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo base_url();?>asset/css/bootstrap-notify.css" />
    <link type="text/css" href="<?php echo base_url();?>asset/css/font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo base_url();?>asset/css/docs.css" rel="stylesheet">
	
 
    <link href="<?php echo base_url();?>asset/js/google-code-prettify/prettify.css" rel="stylesheet">
  
<script src="<?php echo base_url(); ?>asset/js/jquery-1.9.0.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>asset/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>asset/js/jquery-ui-1.10.0.custom.min.js" type="text/javascript"></script>

 <script type="text/javascript" src="<?php echo base_url();?>asset/js/bootstrap-checkbox.js"></script>
   

    <script src="<?php echo base_url();?>asset/js/jquery.js"></script>
    <script src="<?php echo base_url();?>asset/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url();?>asset/js/jquery-ui-1.10.0.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo base_url();?>asset/js/google-code-prettify/prettify.js" type="text/javascript"></script>
    <script src="<?php echo base_url();?>asset/js/docs.js" type="text/javascript"></script>
    
   
	
	<script src="<?php echo base_url();?>asset/js/ajaxfileupload.js" type="text/javascript"></script>

    <script src="<?php echo base_url();?>asset/js/bootstrap-notify.js" type="text/javascript"></script>
    
    <script type="text/javascript" src="<?php echo base_url();?>asset/tinymce/tinymce.min.js"></script>
	

    <script src="<?php echo base_url(); ?>asset/js/bootstrap-scrollspy.js"></script> 
 

    <script src="<?php echo base_url(); ?>asset/js/ui.datepicker-id.js"></script>

    <script type="text/javascript">
$(document).ready(function(){
    
});
$(function () {
    //####### Buttons
    $('button,.button,#sampleButton').button();
});
</script>

  </head>
  <body>
  
    <div class='notifications bottom-right'></div>
    <div class='notifications top-right'></div>
      <div class="navbar  navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a href="<?php echo base_url(); ?>" class="brand">easyRDF</a>
				
				<a data-toggle="collapse" data-target=".nav-collapse" class="btn btn-navbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				
				<div class="collapse nav-collapse">
              <ul class="nav pull-left">
                                
                        <li class="dropdown" id="preview-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user"></i> Examples <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                                 <li><a href="<?php echo base_url(); ?>index.php/rdf/basic"><i class="icon-star"></i> Basic</a></li>
                                   <li><a href="<?php echo base_url(); ?>index.php/rdf/basic/sparql"><i class="icon-star"></i> Sparql</a></li>
                                    <li><a href="<?php echo base_url(); ?>index.php/rdf/basic/foafinfo"><i class="icon-star"></i> Foafinfo</a></li>     
                       <li><a href="<?php echo base_url(); ?>index.php/rdf/basic/foafmaker"><i class="icon-star"></i> Foafmaker</a></li>
                        <li><a href="<?php echo base_url(); ?>index.php/rdf/basic/converter"><i class="icon-star"></i> Converter</a></li>
                      
                        </ul>
                        </li>
          </ul>
					<ul class="nav pull-right">
						<li class="active"><a href="#"><i class="icon-home"></i> Home</a></li>
                       	
						          
                        <li class="dropdown" id="preview-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user"></i>  Hello,  <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                                 <li><a href="#"><i class="icon-plus"></i> User</a></li>
                                            <li><a href="#"><i class="icon-download"></i> Backup Database</a></li>
                           <li><a href="#"><i class="icon-off"></i> Logout</a></li>	
                      
                      
                        </ul>
                        </li>
					</ul>
				</div>
			</div>
		</div>
	</div> <!-- end navbar -->

	<!-- FEATURED PRODUCT -->
	<section>
		<div class="container">
        <div class="bootstrap-widget">
				<?php echo $content;?>
		</div>                
		</div> <!-- end container -->
	</section>

	
	<div class="container">		
		<!-- FOOTER -->
		<section>
			<p class="text-center muted">&copy; Copyright 2014 - <br>Created By : <a href="http://ndesostyle.wordpress.com" target="_blank">Slamet Nurhadi</a><br>Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </section>
	</div> <!-- end container -->
    
  </body>
</html>
