<?php


get_header(); ?>

<section class="section header_page dark" style="background-color: #101d3b;">
        <?php 
        $bg_image_url = get_site_url().'/wp-content/uploads/2023/06/Tour-du-lich-chau-au-mua-he-2023-top-ten-travel-1.jpeg';
        if (has_post_thumbnail()) {
            $bg_image_url = get_the_post_thumbnail_url(get_the_ID(), 'hazo-banner');
        }
        ?>
        <div class="bg section-bg fill bg-fill bg-loaded" style="background-image:url('<?php echo esc_url($bg_image_url); ?>'); background-position: 50% 100%;">
          <div class="section-bg-overlay absolute fill" style="background-color:  rgba(0, 18, 35, 0.69); "></div>
        </div>
        <div class="container section-content relative">
         
              <div class="page-header">
                <div class="title"> 
                <?php the_title(); ?>
                </div>

                <?php rank_math_the_breadcrumbs(); ?>
              </div>
        
        </div>
    
    </section>





<div id="content" role="main" class="content-area">

	<main id="primary" class="site-main">

        <div class="container" style="margin-bottom: 40px;">
            <div class="row">
                <div class="col large-8 info-service">

                    <div class="row">
                        <div class="col large-7">
                            <div class="box-image radius-15">
                                <?php the_post_thumbnail( 'full'); ?>
                            </div>
                        </div>

                        <div class="col large-5">
                            <h1><?php the_title(); ?></h1>
                            <div class="sv-price">
                                <?php the_field( 'price' ); ?>
                            </div>
                            <div class="desc" style="font-size: 16px;">
                                <?php the_field( 'desc' ); ?>
                            </div>
                        </div>
                    </div>




<div class="product-description">
    <h2>Mô tả chi tiết</h2>
    <?php the_field('product_description'); ?>
</div>









<div class="main-info">
    <?php while ( has_sub_field('service' ) ) { ?>
        <div id="text-834157744" class="text text-label">
            <p><?php the_sub_field( 'title' ); ?></p>
        </div>

        <div class="content">
            <?php the_sub_field( 'content' ); ?>
        </div>
    <?php } ?>

    <!-- Chèn phần mô tả chi tiết sản phẩm vào đây -->
    <div class="product-description">
        <h2>Mô tả chi tiết sản phẩm</h2>
        <?php
            // Hiển thị mô tả chi tiết sản phẩm
            the_content();
        ?>
    </div>
</div>











                    <div class="main-info">
                        <?php while ( has_sub_field('service' ) ) { ?>
                            <div id="text-834157744" class="text text-label">
                                <p><?php the_sub_field( 'title' ); ?></p>
                            </div>

                            <div class="content">
                                <?php the_sub_field( 'content' ); ?>
                            </div>
                        <?php } ?>
                    </div>  
                    
                </div>


                <div class="col large-4">

                    <?php flatsome_sticky_column_open( 'blog_sticky_sidebar' ); ?>
        
                    <div class="form-tuvan dark">
                        <?php echo do_shortcode('[contact-form-7 id="326" title="Đặt xe"]') ?>
                    </div>

                    <div class="sv-style widget">
                        <span class="widget-title "><span>Bản đồ</span></span>
                        <div class="is-divider small"></div>

                        <div class="radius-15 sv-map">
                            <?php the_field( 'google_map' ); ?>
                        </div>
                    </div>


                    <div class="sv-style widget">
                        <span class="widget-title "><span>Thời gian thuê xe - Điểm nhận - Trả xe</span></span>
                        <div class="is-divider small"></div>

                        <div class="radius-15 sv-map">
                            <?php the_field( 'time' ); ?>
                        </div>
                    </div>

                     <?php flatsome_sticky_column_close( 'blog_sticky_sidebar' ); ?>
                    
                </div>


            </div>
        </div>
       
    </main>

</div>

<?php do_action( 'flatsome_after_page' ); ?>

<?php get_footer(); ?>
