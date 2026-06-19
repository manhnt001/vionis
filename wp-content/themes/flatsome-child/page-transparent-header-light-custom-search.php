<?php
/**
 * Template name: Tìm kiếm tour

 */

get_header(); ?>

<?php do_action( 'flatsome_before_page' ); ?>


<div id="content" role="main" class="content-area">

	<main id="primary" class="site-main">


        <div class="realestate">
            <div class="container">
                <h1 class="section-title page-title-has-line text-playfair font-medium">

                    <?php $search_key = $_GET['tu-khoa'] ?? '';
                    if($search_key) {  ?>
                    <?php
                    /* translators: %s: search query. */
                    printf(esc_html__('Tìm kiếm cho từ khóa:'.$search_key.'', 'hazo'), '<span>' . get_search_query() . '</span>');
                    } else {
                        _e('Kết quả tìm kiếm tour','hazo');
                    } ?>


                </h1>


                <?php 

                $keyword = $_GET['tu-khoa'] ?? '';
                $loaitour = $_GET['loai-tour'] ?? '';
                $khoihanh = $_GET['khoi-hanh'] ?? '';
                $noiden = $_GET['noi-den'] ?? '';
                $dongtour = $_GET['dong-tour'] ?? '';
                $ngaykhoihanh = $_GET['ngay-khoi-hanh'] ?? '';
                $minprice = intval($_GET['min-price'] ?? 0);
                $maxprice = intval($_GET['max-price'] ?? 0);
                $songay = $_GET['so-luong-ngay'] ?? '';
                function wbc_get_term($term_slug){
                    $taxonomies = get_taxonomies();
                    foreach ( $taxonomies as $tax_type_key => $taxonomy ) {
                        if ( $term_object = get_term_by( 'slug', $term_slug , $taxonomy ) ) {
                            break;
                        }
                    }
                    return $term_object->name;
                } ?>


                <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $arrgs = array(
                    'post_type'=> 'product',
                    'posts_per_page' => 12,
                    'paged' =>$paged,
                    // 's' => $keyword,
                );


                $arrgs['tax_query']['relation'] = 'AND';

                // Search project
                if($loaitour) {
                    $arrgs['tax_query'][] = 
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => $loaitour,

                    );
                }

                if($songay) {
                    $arrgs['tax_query'][] = 
                    array(
                        'taxonomy' => 'tour_time',
                        'field'    => 'slug',
                        'terms'    => $songay,

                    );
                }
                if($khoihanh) {
                    $arrgs['tax_query'][] = 
                    array(
                        'taxonomy' => 'product_start',
                        'field'    => 'slug',
                        'terms'    => $khoihanh,

                    );
                }
                //
                
                if($noiden) {
                    $arrgs['tax_query'][] = 
                    array(
                        'taxonomy' => 'product_end',
                        'field'    => 'slug',
                        'terms'    => $noiden,

                    );
                }

                if ($dongtour) {
                    $arrgs['tax_query'][] = 
                    array(
                        'taxonomy' => 'product_tour',
                        'field'    => 'slug',
                        'terms'    => $dongtour,

                    );
                }
                

                if ($ngaykhoihanh) {
                    $today = date('Ymd');
                    $arrgs['orderby']  = 'meta_value_num';
                    $arrgs['meta_key'] = 'tour_time';
                    $arrgs['meta_query'][] = 
                        array(
                            'key' => 'tour_time', 
                            'value' => $today, 
                            'compare' => '=', // >=
                            'type' => 'DATE'
                    );
                    
                }
                if ($minprice || $maxprice) {
                    $arrgs['orderby']  = 'meta_value_num';
                    $arrgs['meta_key'] = '_price';
                    $arrgs['meta_query'][] = 
                        array(
                        'key'     => '_price',
                        'value'   => array( $minprice, $maxprice ),  // Replace min_price and max_price with your desired range
                        'type'    => 'numeric',
                        'compare' => 'BETWEEN',
                    );
                }


                $wp_query = new WP_Query( $arrgs); ?>
                
                <div class="row">
                    <div class="col large-8">
                        <?php
                        if ( $wp_query->have_posts() ) : 
                            woocommerce_product_loop_start();

                                while ( $wp_query->have_posts() ) {
                                    $wp_query->the_post();

                                    /**
                                     * Hook: woocommerce_shop_loop.
                                     *
                                     * @hooked WC_Structured_Data::generate_product_data() - 10
                                     */
                                    do_action( 'woocommerce_shop_loop' );

                                    wc_get_template_part( 'content', 'product' );
                                }
                          

                            woocommerce_product_loop_end();
                        
                        else :
                            ?>
                            <div class="no-tours-found" style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 16px; margin-top: 20px; border: 1px dashed #cbd5e1;">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <h3 style="color: #334155; margin-bottom: 10px; font-weight: 600; font-size: 22px;">Rất tiếc, không tìm thấy tour nào phù hợp!</h3>
                                <p style="color: #64748b; font-size: 16px;">Vui lòng thử tìm kiếm với địa điểm hoặc thời gian khác.</p>
                            </div>
                            <?php
                        endif;
                        ?>
                    </div>

                    <div class="col large-4">
                        <?php get_sidebar( 'shop' ) ?>
                    </div>
                </div>

                
                
            </div>
        </div>
    </main>

</div>

<?php do_action( 'flatsome_after_page' ); ?>

<?php get_footer(); ?>
