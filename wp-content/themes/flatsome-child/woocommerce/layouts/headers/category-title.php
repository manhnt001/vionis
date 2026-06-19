<?php
/**
 * Category title.
 *
 * @package          Flatsome/WooCommerce/Templates
 * @flatsome-version 3.16.0
 */

?>


<section class="section header_page dark" style="background-color: #101d3b;">
<?php
        // Get current category
        $category = get_queried_object();
        $bg_image_url = get_site_url().'/wp-content/uploads/2023/06/Tour-du-lich-chau-au-mua-he-2023-top-ten-travel-1.jpeg'; // Default fallback image

        if ( $category && isset( $category->term_id ) ) {
            $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
            if ( $thumbnail_id ) {
                $image = wp_get_attachment_image_url( $thumbnail_id, 'hazo-banner' );
                if ( $image ) {
                    $bg_image_url = $image;
                }
            }
        }
        ?>
        <div class="bg section-bg fill bg-fill bg-loaded" style="background-image:url('<?php echo esc_url($bg_image_url); ?>'); background-position: 50% 100%;">
          <div class="section-bg-overlay absolute fill" style="background-color:  rgba(0, 18, 35, 0.69); "></div>
        </div>
        <div class="container section-content relative">
         
              <div class="page-header">
                <h1 class="title"> 
                <?php echo single_cat_title(); ?>
                </h1>

                <?php rank_math_the_breadcrumbs(); ?>
              </div>
        
        </div>
    
    </section>