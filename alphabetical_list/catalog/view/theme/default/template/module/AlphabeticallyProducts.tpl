<style>
.box-alphabeticallyproducts {
overflow: auto;
}
.box-alphabeticallyproducts a {
float: left;
padding: 2px 4px;
}
.box-alphabeticallyproducts .wraplett {
float: left;
padding: 2px 3px;
width: 2px;
height: 2px;
}
</style>
<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">    
	  <div class="box-alphabeticallyproducts">      		  
		  <div valign="top">	
		 <?php if ( $this->config->get('cyr') != 0 ) {
		  foreach ($cyr_letters as $cyr_letter) { ?>
				<a href="<?php echo $href.$cyr_letter ?>"><?php echo $cyr_letter ?></a><?php if ( $this->config->get('line') == 0 ) { ?><?php } ?>
		  <?php } } ?>
		  <?php if ( $this->config->get('line') == 0 ) { ?>
		  <div style="clear: both;"></div>
		  <?php } else { ?>
		  <div class="wraplett"></div>
		  <?php } ?>
		  <?php if ( $this->config->get('lat') != 0 ) {
		  foreach ($lat_letters as $lat_letter) { ?>
				<a href="<?php echo $href.$lat_letter ?>"><?php echo $lat_letter ?></a>
		  <?php } } ?>
		 
		  <?php if ( $this->config->get('num') != 0 ) { ?>
		   <div class="wraplett"></div><a href="<?php echo $href.$numbers ?>"><?php echo $numbers ?></a>
		  <?php } ?>
		  </div>		  		  
		  <div style="clear: both;"></div>      
	  </div>    
  </div>
</div>
