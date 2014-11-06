<?php 
/* 	Travel Theme's Blog Posts Showung Template
	Copyright: 2013, D5 Creation, www.d5creation.com
	Based on the Simplest D5 Framework for WordPress
	Since Travel 1.0
*/

get_header(); ?>

<div id="container">
<div id="content">

<?php  global $more; $more = 0;
$wp_query = new WP_Query(array( 'type' => 'post', 'paged' => ( get_query_var('paged') ? get_query_var('paged') : 1 ) )); if ( $wp_query->have_posts()) : while ($wp_query->have_posts()) : $wp_query->the_post();  ?>
<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
 <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title();?></a></h2>
 <span class="postmetadata"><h3><?php the_time('F j, Y'); ?></h3><div class="content-ver-sep"> </div><h2>By: <?php the_author_posts_link() ?></h2><h5><?php comments_popup_link('No Comments Yet&#187;', '1 Comment &#187;', '% Comments &#187;'); ?></h5>Posted in <?php the_category(', ') ?><?php the_tags('<br />Tags: ', ', ', ''); ?><br /><h5><?php edit_post_link('Edit'); ?></h5></span>	
 <div class="entrytext"><div class="thumb"><?php the_post_thumbnail(); ?></div>
 <?php travel_content(); ?>
 <div class="clear"> </div>
 </div></div>
 <?php  wp_link_pages( array( 'before' => '<div class="page-link"><span>' . 'Pages:' . '</span>', 'after' => '</div>' ) ); ?>
 <div class="content-ver-sep"></div><br />
 <?php endwhile; ?>

<div id="page-nav">
<div class="alignleft"><?php previous_posts_link('&laquo; Previous Entries') ?></div>
<div class="alignright"><?php next_posts_link('Next Entries &raquo;','') ?></div>
</div>
  
 
 <?php else: ?>
 
 <h1 class="arc-post-title">Sorry, we couldn't find anything that matched your search.</h1>
		
		<h3 class="arc-src"><span>You Can Try Another Search...</span></h3>
		<?php get_search_form(); ?><br />
		<p><a href="<?php echo home_url(); ?>" title="Browse the Home Page">&laquo; Or Return to the Home Page</a></p><br />
		 
<?php endif; wp_reset_query(); ?>
 

</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>