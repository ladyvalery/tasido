<?php
/*
	Travel Theme's Front Page to Display the Home Page if Selected
	Copyright: 2013, D5 Creation, www.d5creation.com
	Based on the Simplest D5 Framework for WordPress
	Since travel 1.0
*/
?>

<?php get_header(); ?>
</div><div class="vspace"> </div>
<div class="label-text"><h3><?php echo of_get_option('fpheading', 'Test Place, Sample Country'); ?></h3></div>
<div id="container">
<?php get_template_part( 'featured-box' ); ?> 
<?php if (of_get_option('fpostex', '1') != '1'): get_template_part( 'fcontent' ); endif;?>
<div class="content-ver-sep"></div>
</div>
<?php get_footer(); ?>