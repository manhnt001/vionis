<?php


get_header(); ?>

<?php do_action( 'flatsome_before_page' ); ?>


<div id="content" role="main" class="content-area">

	<main id="primary" class="site-main">

        <div class="container" style="margin-bottom: 40px;">
            <?php if ( have_posts() ) : ?>
            <div class="row large-columns-2 medium-columns-2 small-columns-1">
                <?php while ( have_posts() ) : the_post(); ?>
                    <div class="col">
                        <a href="<?php the_permalink(); ?>">
                        <div class="box sv-item box-vertical box-text-bottom box-blog-post has-hover">
                            <div class="box-image" style="width: 45%;">
                                <div class="image-cover" style="padding-top: 100%;">
                                    <?php the_post_thumbnail('full'); ?>
                                </div>
                            </div>
                            <div class="box-text">
                                <h3>
                                    <?php the_title() ?>
                                </h3>
                                <div class="desc">
                                    <?php the_field( 'desc' );  ?>
                                </div>
                                <div class="is-divider"></div>
                                <div class="row padding-bot row-xsmall align-middle">
                                    <div class="col large-6 sv-price">
                                        <?php the_field( 'price' ); ?>
                                    </div>

                                    <div class="col large-6 sv-price">
                                        <button class="btn-tour" href="<?php the_permalink(); ?>">Đặt xe ngay</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endwhile; // end of the loop. ?>
            </div>

            <?php flatsome_posts_pagination(); ?>

            <?php else : ?>

                <?php get_template_part( 'template-parts/posts/content','none'); ?>

            <?php endif; ?>



        </div>
       
    </main>

</div>

<?php do_action( 'flatsome_after_page' ); ?>

<?php get_footer(); ?>
