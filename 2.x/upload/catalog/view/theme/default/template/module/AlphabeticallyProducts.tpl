<style>
.box-alphabeticallyproducts {
	margin: 0 0 20px;
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
<div class="box-alphabeticallyproducts">  
  	<h3><?php echo $title; ?></h3>     		  
  	<div valign="top">	
	 	<?php if ( $cyr_letters ) {
		  	foreach ($cyr_letters as $cyr_letter) { ?>
				<a href="<?php echo $href.$cyr_letter; ?>"><?php echo $cyr_letter; ?></a>
		  	<?php } 
		  	if ( !isset($setting['line']) || !$setting['line'] ) { ?>
	  			<div style="clear: both;"></div>
  			<?php } else { ?>
	  			<div class="wraplett"></div>
	  		<?php }
		} ?>
	  	<?php if ( $lat_letters ) {
	  		foreach ($lat_letters as $lat_letter) { ?>
				<a href="<?php echo $href.$lat_letter; ?>"><?php echo $lat_letter; ?></a>
	  		<?php } 
	  		if ( !isset($setting['line']) || !$setting['line'] ) { ?>
	  			<div style="clear: both;"></div>
  			<?php } else { ?>
	  			<div class="wraplett"></div>
	  		<?php }
	  	} ?>
	  	<?php if ( $numbers ) { ?>
	   		<a href="<?php echo $href.$numbers; ?>"><?php echo $numbers; ?></a>
	  	<?php } ?>
  	</div>		  		  
  	<div style="clear: both;"></div>      
</div>    