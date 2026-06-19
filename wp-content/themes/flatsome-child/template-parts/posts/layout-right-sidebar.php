<?php
/**
 * Posts layout right sidebar.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

do_action('flatsome_before_blog');
?>

<?php if(!is_single() && flatsome_option('blog_featured') == 'top'){ get_template_part('template-parts/posts/featured-posts'); } ?>

<div class="row row-large">

	<div class="large-9 col">
		<?php if(!is_single() && flatsome_option('blog_featured') == 'content'){ get_template_part('template-parts/posts/featured-posts'); } ?>
		<?php
			if(is_single()){
				get_template_part( 'template-parts/posts/single');
				comments_template();
			} elseif(flatsome_option('blog_style_archive') && (is_archive() || is_search())){
				get_template_part( 'template-parts/posts/archive', flatsome_option('blog_style_archive') );
			} else {
				get_template_part( 'template-parts/posts/archive', flatsome_option('blog_style') );
			}
		?>

		<?php global $post;$idPage = get_the_ID();
            $my_query = new wp_query(array(
                'post_type' =>  $post->post_type,
                'post_status'    => 'publish',
                'posts_per_page' => 6,
                'post__not_in' => array($idPage)
            ));
            if( $my_query->have_posts() ){ 	?>
            <div class="post_related">
                <div class="post_related-title"><?php echo __('có thể bạn quan tâm')?></div>
                <ul class="post_related-list">
                    <?php	while($my_query->have_posts()) : $my_query->the_post() ;?>
                        <li>
                            <a href="<?php  the_permalink() ?>"><?php the_title(); ?></a>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ul>
            </div>
        <?php } ?>
	</div>
	<div class="post-sidebar large-3 col">
		<?php flatsome_sticky_column_open( 'blog_sticky_sidebar' ); ?>
		<?php get_sidebar(); ?>
		<?php flatsome_sticky_column_close( 'blog_sticky_sidebar' ); ?>
	</div>
</div>

<?php
	do_action('flatsome_after_blog');
?>
