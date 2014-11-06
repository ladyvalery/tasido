<?php 
/* 	Travel Theme's part for showing blog or page in the front page
	Copyright: 2013, D5 Creation, www.d5creation.com
	Based on the Simplest D5 Framework for WordPress
	Since Travel 1.0
*/

?>
 <br />
 <div class="featured-contents">
 <div id="content">
 <?php if (have_posts()) : while (have_posts()) : the_post();?>
 <div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
 <?php if (!is_page()): ?>
 <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('fpage-thumb'); ?></a>
 <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title();?></a></h2>
 <?php endif; ?>
 <div class="entrytext">
 <?php travel_content(); ?>
 <?php  wp_link_pages( array( 'before' => '<div class="page-link"><span>' . 'Pages:' . '</span>', 'after' => '</div>' ) ); ?>
 <div class="clear"> </div>
 </div></div>
 <?php endwhile;  if (!is_page()): ?>

<div id="page-nav">
<div class="alignleft"><?php previous_posts_link('&laquo; Previous Entries') ?></div>
<div class="alignright"><?php next_posts_link('Next Entries &raquo;','') ?></div>
</div>
 
<?php endif; endif; ?>
 
</div>
<?php get_sidebar( 'others' ); ?>
</div>
<div class="clear"></div><div class="sep2">sep</div>