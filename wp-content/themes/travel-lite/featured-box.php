<?php
/* 	Travel Theme's Featured Box to show the Featured Items of Front Page
	Copyright: 2013, D5 Creation, www.d5creation.com
	Based on the Simplest D5 Framework for WordPress
	Since travel 1.0
*/
?>

<div class="featured-boxs">
<?php foreach (range(1, 3) as $fboxn) { ?>
<span class="featured-box">
<h2><?php echo of_get_option('featured01-title' . $fboxn, 'Featured'); ?><span> <?php echo of_get_option('featured02-title' . $fboxn, 'Image'); ?></span></h2>
<img src="<?php echo of_get_option('featured-image' . $fboxn, get_template_directory_uri() . '/images/featured-image'. $fboxn . '.jpg'); ?>"/>
<p><?php echo of_get_option('featured-description' . $fboxn, 'A Smart way of Natural Presence. This is a Test Description and you can change it from the Theme Options.'); ?></p>
</span>
<?php } ?>
</div> <!-- featured-boxs -->


<div class="featured-contents">
<?php foreach (range(1, 3) as $fboxn2) { ?>
<span class="featured-content">
<h2 <?php if ( of_get_option('fcontent-special' . $fboxn2, 'HOT') != '' ): echo 'style="padding:0 70px 10px 10px;"'; endif; ?> ><span><?php echo of_get_option('fcontent01-title' . $fboxn2, 'Travel'); ?></span> <?php echo of_get_option('fcontent02-title' . $fboxn2, 'Content'); ?></h2>
<img class="fcon-image" src="<?php echo of_get_option('fcontent-image' . $fboxn2, get_template_directory_uri() . '/images/fcontent-image'. $fboxn2 . '.jpg'); ?>"/>
<p><?php echo of_get_option('fcontent-description' . $fboxn2, '<b>ASmart way of Natural Presence</b><p>A Smart way of Natural Presence. This is a Test Content and you can change it from the Theme Options.</p>'); ?></p>
</span>
<?php  } ?>

</div> <!-- featured-contents -->

<div class="sep2">sep</div>