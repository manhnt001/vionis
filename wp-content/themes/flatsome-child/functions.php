<?php 
// Display "Contact Us" if price is empty
add_filter( 'woocommerce_empty_price_html', 'custom_call_for_price' );
function custom_call_for_price() {
    return '<span class="contact-for-price">' . esc_html__( 'Contact Us', 'hazo' ) . '</span>';
}

add_action( 'wp_head', 'contact_for_price_styles' );
function contact_for_price_styles() {
    ?>
    <style>
        /* CSS cho chữ "Liên hệ" ở danh sách sản phẩm */
        .tour-price .contact-for-price,
        .related-item .contact-for-price,
        .product-small .contact-for-price {
            color: #0071bb !important;
            font-size: 18px !important;
            font-weight: bold !important;
        }
        
        /* CSS cho chữ "Liên hệ" ở chi tiết sản phẩm */
        .box-price .contact-for-price,
        .product-info .contact-for-price {
            font-size: 24px !important;
            color: #FF3D00 !important;
            font-weight: 800 !important;
        }
    </style>
    <?php
}

add_action('admin_head', 'bc_disable_notice'); function bc_disable_notice() { ?> <style> .notice-warning, .notice-error { display: none;} </style> <?php }

// Classic edior
add_filter('use_block_editor_for_post', '__return_false');
add_filter( 'use_widgets_block_editor', '__return_false' );


add_action( 'wp_enqueue_scripts', 'wpshare247_datepicker' );
function wpshare247_datepicker() {
   wp_enqueue_script( 'jquery-ui-datepicker' );
}


//Ẩn các panel không cần thiết
function camap_load_js() {
  // wp_enqueue_script( 'myjs' , get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), false,true );

  // wp_enqueue_script( 'ticker' , get_stylesheet_directory_uri() . '/assets/js/ticker.js', array('jquery'), false,true );
}
add_action( 'wp_enqueue_scripts', 'camap_load_js' );

/**
 * Remove Crop Image Wordpress Size: Large + Medium_large + Medium
 */
add_filter('intermediate_image_sizes', function ($sizes) {
    return array_diff($sizes, ['medium_large']);  // Medium Large (768 x 0)
});
//
add_action('init', 'remove_extra_image_sizes');
function remove_extra_image_sizes()
{
    $sizes = array();
    foreach (get_intermediate_image_sizes() as $size) {
        if (!in_array($size, $sizes)) {
            remove_image_size($size);
        }
    }
}


if ( ! function_exists( 'camap_mce_text_sizes' ) ) {
    function camap_mce_text_sizes( $initArray ){
        $initArray['fontsize_formats'] = "9px 10px 12px 13px 14px 16px 17px 18px 19px 20px 21px 24px 28px 32px 36px 40px";
        return $initArray;
    }
    add_filter( 'tiny_mce_before_init', 'camap_mce_text_sizes', 99 );
}



// the archive title
add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_tax()) { //for custom post types
        $title = sprintf(__('%1$s'), single_term_title('', false));
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    }
    return $title;
});

function single_tour_title() { ?>

  <div class="page-header">
    <div class="title">
    <?php echo wp_get_post_terms(get_the_ID(), 'product_cat')[0]->name; ?>
    </div>
  </div>
  <?php
}
add_shortcode('head_title','single_tour_title' );


function customJS() { ?>

  <script>
    (function($){
        $(".car_title").val("<?php the_title(); ?>");
    })(jQuery);
    </script>   

<?php }
add_action('wp_head','customJS');

add_action('wp_footer','camap_backtotop');
function camap_backtotop(){
    ?>

    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
          <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>
        </svg>
    </div>
    <style>
        .progress-wrap {
          position: fixed;
          right: 30px;
          bottom: 100px;
          height: 46px;
          width: 46px;
          cursor: pointer;
          display: block;
          border-radius: 50px;
          z-index: 10000;
          opacity: 0;
          visibility: hidden;
          transform: translateY(15px);
          -webkit-transition: all 200ms linear;
            transition: all 200ms linear;
        }
        .progress-wrap.active-progress {
          opacity: 1;
          visibility: visible;
          transform: translateY(0);
        }
        .progress-wrap::after {
          position: absolute;
          font-family: "fl-icons" !important;
          content: "";
          text-align: center;
            font-size: 24px;
            color: #fff;
            left: 0;
            right: 0;
            margin: auto;
            background-color: var(--primary-color);
            border-radius: 99px;
            top: 50%;
            transform: translateY(-50%);
            height: 38px;
            width: 38px;
            line-height: 35px;
            cursor: pointer;
            display: block;
            z-index: 1;
          -webkit-transition: all 200ms linear;
            transition: all 200ms linear;
        }
        .progress-wrap:hover::after {
          background-color: #333;
        }
        .progress-wrap::before {
          position: absolute;
          font-family: "fl-icons" !important;
          content: "";
          text-align: center;
          line-height: 46px;
          font-size: 24px;
          opacity: 0;
          background: var(--primary-color); /* --- Pijl hover kleur --- */
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          left: 0;
          top: 0;
          height: 46px;
          width: 46px;
          cursor: pointer;
          display: block;
          z-index: 2;
          -webkit-transition: all 200ms linear;
            transition: all 200ms linear;
        }
        .progress-wrap:hover::before {
          opacity: 1;
        }
        .progress-wrap svg path { 
          fill: none; 
        }
        .progress-wrap svg.progress-circle path {
          stroke: var(--primary-color); /* --- Lijn progres kleur --- */
          stroke-width: 4;
          box-sizing:border-box;
          -webkit-transition: all 200ms linear;
            transition: all 200ms linear;
        }
    </style>
    <script>
        (function($) { "use strict";

          $(document).ready(function(){"use strict";
            
            var progressPath = document.querySelector('.progress-wrap path');
            var pathLength = progressPath.getTotalLength();
            progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
            progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
            progressPath.style.strokeDashoffset = pathLength;
            progressPath.getBoundingClientRect();
            progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';    
            var updateProgress = function () {
              var scroll = $(window).scrollTop();
              var height = $(document).height() - $(window).height();
              var progress = pathLength - (scroll * pathLength / height);
              progressPath.style.strokeDashoffset = progress;
            }
            updateProgress();
            $(window).scroll(updateProgress); 
            var offset = 50;
            var duration = 550;
            jQuery(window).on('scroll', function() {
              if (jQuery(this).scrollTop() > offset) {
                jQuery('.progress-wrap').addClass('active-progress');
              } else {
                jQuery('.progress-wrap').removeClass('active-progress');
              }
            });       
            jQuery('.progress-wrap').on('click', function(event) {
              event.preventDefault();
              jQuery('html, body').animate({scrollTop: 0}, duration);
              return false;
            })
            
            
          });
          
        })(jQuery); 
    </script>
    <?php
}
function show_logo_mobile() {

  echo '<div class="logo-custom">';
  get_template_part('template-parts/header/partials/element','logo');
  echo '</div>'; ?>
  <style>
    .mobile-sidebar .logo-custom img {
      width: 250px;
    }
    .mobile-sidebar .logo-custom {
      display: flex;
      justify-content: center;
      padding-left: 0 !important;
      padding-top: 0;
      margin: 20px 0;
    }
  </style>

<?php
}
add_action('flatsome_before_sidebar_menu_elements','show_logo_mobile');



// Kích thước chuẩn cho Banner Header (tỷ lệ giống ảnh Châu Âu cũ 1929x613)
add_image_size('hazo-banner', 1920, 610, true);

function header_page() { ?>

  <?php if(!is_front_page()) { ?>

    <?php 
      $image = get_site_url().'/wp-content/uploads/2023/06/Tour-du-lich-chau-au-mua-he-2023-top-ten-travel-1.jpeg';
      $overlay = 'rgb(0 18 35 / 69%)';

      // 1. Lấy ảnh đại diện của bài viết/trang (nếu có)
      if( (is_page() || is_single()) && has_post_thumbnail() ) {
          $image = get_the_post_thumbnail_url(get_the_ID(), 'hazo-banner');
      } 
      // 2. Lấy ảnh đại diện của danh mục (nếu có)
      elseif( is_category() || is_archive() || is_tax() ) {
          $category = get_queried_object();
          if ( $category && isset( $category->term_id ) ) {
              $banner_id = get_term_meta( $category->term_id, 'category_banner_id', true );
              if ( ! $banner_id ) {
                  $banner_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
              }
              if ( $banner_id ) {
                  $img_url = wp_get_attachment_image_url( $banner_id, 'hazo-banner' );
                  if ( $img_url ) {
                      $image = $img_url;
                  }
              }
          }
      }
    ?>
    <section class="section header_page dark" style="background-color: #101d3b;">
        <div class="bg section-bg fill bg-fill bg-loaded" style="background-image:url(<?php echo $image ?>); background-position: 50% 100%;">
          <div class="section-bg-overlay absolute fill" style="background-color: <?php echo $overlay; ?> ;"></div>
        </div>
        <div class="container section-content relative">
         
              <div class="page-header">
                <h1 class="title"> 
                <?php 
                if(is_category() /*|| is_product_category() */) {
                    echo single_cat_title();
                }
                elseif(is_archive()) {
                    echo post_type_archive_title();
                } elseif(is_page()) {
                    the_title();
                } elseif(is_single()) {
                    echo get_the_category(get_the_ID())[0]->name;
                } elseif(is_singular('service')) {
                  echo _e('Dịch vụ','cm');
                }  ?>
                </h1>

                <?php rank_math_the_breadcrumbs(); ?>
              </div>
        
        </div>
    
    </section>
    
<?php } }
add_action('flatsome_before_page','header_page');
add_action('flatsome_before_blog','header_page');



function cptui_register_my_taxes(){

    /**
     * Taxonomy: product_start
     */

    $labels = [
        "name" => __("Nơi khởi hành", "cm"),
        "singular_name" => __("Nơi khởi hành", "cm"),
    ];

    $args = [
        "label" => __("Nơi khởi hành", "cm"),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "hierarchical" => true,
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "query_var" => true,
        "rewrite" => ['slug' => 'noi-khoi-hanh', 'with_front' => true,],
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rest_base" => "product_start",
        "rest_controller_class" => "WP_REST_Terms_Controller",
        "show_in_quick_edit" => true,
        "show_in_graphql" => false,
    ];
    register_taxonomy("product_start", ["product"], $args);


     /**
     * Taxonomy: Nơi đến
     */

    $labels = [
        "name" => __("Nơi đến", "cm"),
        "singular_name" => __("Nơi đến", "cm"),
    ];

    $args = [
        "label" => __("Nơi đến", "cm"),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "hierarchical" => true,
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "query_var" => true,
        "rewrite" => ['slug' => 'noi-den', 'with_front' => true,],
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rest_base" => "product_end",
        "rest_controller_class" => "WP_REST_Terms_Controller",
        "show_in_quick_edit" => true,
        "show_in_graphql" => false,
    ];
    register_taxonomy("product_end", ["product"], $args);

    /**
     * Taxonomy: Dòng tour
     */

    $labels = [
        "name" => __("Dòng tour", "cm"),
        "singular_name" => __("Dòng tour", "cm"),
    ];

    $args = [
        "label" => __("Dòng tour", "cm"),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "hierarchical" => true,
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "query_var" => true,
        "rewrite" => ['slug' => 'dong-tour', 'with_front' => true,],
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rest_base" => "product_tour",
        "rest_controller_class" => "WP_REST_Terms_Controller",
        "show_in_quick_edit" => true,
        "show_in_graphql" => false,
    ];
    register_taxonomy("product_tour", ["product"], $args);


    /**
     * Taxonomy: Dòng tour
     */

    $labels = [
        "name" => __("Thời gian tour", "cm"),
        "singular_name" => __("Thời gian tour", "cm"),
    ];

    $args = [
        "label" => __("Thời gian tour", "cm"),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "hierarchical" => true,
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "query_var" => true,
        "rewrite" => ['slug' => 'thoi-gian-tour', 'with_front' => true,],
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rest_base" => "tour_time",
        "rest_controller_class" => "WP_REST_Terms_Controller",
        "show_in_quick_edit" => true,
        "show_in_graphql" => false,
    ];
    register_taxonomy("tour_time", ["product"], $args);


}
add_action('init', 'cptui_register_my_taxes');


add_action( 'woocommerce_after_subcategory_title', 'my_add_cat_description', 12);
function my_add_cat_description ($category) {
  $cat_id=$category->term_id;
  $prod_term=get_term($cat_id,'product_cat');
  $description=$prod_term->description;
  echo '<div class="shop_cat_desc">'.$description.'</div>';
}

remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

add_action('woocommerce_shop_loop_item_title', 'custom_tour');
function custom_tour() { ?>

  <div class="row row-xsmall">
    <div class="col large-6 small-6">
      <div class="icon-box">
        <img src="<?php echo get_stylesheet_directory_uri() ?>/assets/images/map.svg" alt="">
        <div class="ct">
          <span>
            <?php _e('Departure Location','cm'); ?>
          </span>
            <p>
            <?php the_field( 'place' ); ?>
            </p>
          
        </div>
      </div>
    </div>

    <div class="col large-6 small-6">
      <div class="icon-box">
        <img src="<?php echo get_stylesheet_directory_uri() ?>/assets/images/schelude.svg" alt="">
        <div class="ct">
          <span>
            <?php _e('Tour Duration','cm'); ?>
            </span>
            <p>
<?php
$tour_times = wp_get_post_terms(get_the_ID(), 'tour_time');

if (!empty($tour_times) && !is_wp_error($tour_times)) {
    echo $tour_times[0]->name;
}
?>
</p>
          
        </div>
      </div>
    </div>

  </div>

  <div class="is-divider" style="height: 1px;background-color: #D9D9D9;min-width: 100%;"></div>
  <div class="row align-middle row-xsmall">
    <div class="col large-6 medium-6 small-6 tour-price">
      <?php global $product;
      if ( $product && $product->get_price() !== '' ) : ?>
      <div class="price-wrapper" style="display: flex; align-items: baseline; gap: 4px; white-space: nowrap; flex-wrap: nowrap;">
        <?php echo '<span class="price-label" style="display: inline-block;">From: </span><span class="price" style="display: inline-block; margin: 0;">'. $product->get_price_html() .'</span><span class="price-unit" style="display: inline-block;"> / person</span>'; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="col large-6 medium-6 small-6 text-right tour-button">
      <a class="btn-tour" href="<?php the_permalink(); ?>">View Tour</a>
    </div>
  </div>
  

<?php }


function custom_single_price() {
  ob_start();
  global $product; ?>

  <div class="box-price">
    <div class="place">
      <strong><?php _e('Location','cm') ?>:</strong> <?php the_field( 'place' ); ?>
    </div>
    <?php if ( $product && $product->get_price() !== '' ) : ?>
    <div class="price">
      <?php _e('Price From','cm') ?>: <?php echo $product->get_price_html(); ?>
    </div>
    <?php endif; ?>
  </div>

  <?php
  $list_post = ob_get_contents(); //Lấy toàn bộ nội dung phía trên bỏ vào biến $list_post để return
  ob_end_clean();
  return $list_post;
}
add_shortcode('custom_single_price','custom_single_price');



function booktour() {
  ob_start();
  global $product; ?>


    <div class="tour-info related-item">
        <div class="title primary-color">
            <?php the_title(); ?>
        </div>

        <?php if ($product && $product->get_price() !== '') : ?>
            <p class="price">Starting From:&nbsp; <?php echo $product->get_price_html(); ?></p>
        <?php endif; ?>

        <p>Location:&nbsp; <?php the_field('place'); ?></p>

        <a href="#regtour"
        target="_self"
        class="button primary custom-btn"
        style="margin-top:20px;border-radius:99px;">
            <span>View Tour</span>
        </a>

        <a href="tel:0835219687"
        class="button alert btn-custom"
        style="margin-top:10px;border-radius:99px;">
            <i class="icon-phone"></i>
            <span>Hotline: 0835 219 687</span>
        </a>

    </div>

    <?php  $tour_title   =   $product->post->post_title; ?>
    <?php  $number   =  $product->get_price();
    $tour_price = is_numeric($number) ? number_format((float)$number, 0, ',', '.') : '0';
    ?>
    <script>
    (function($){
        $(".product_name").val("<?php echo $tour_title; ?>");
         $(".product_price").val('Giá <?php echo $tour_price; ?>đ');
    })(jQuery);
    </script>   
    <?php echo do_shortcode('[lightbox id="regtour" width="600px" padding="30px"][contact-form-7 id="173" title="Đặt tour"][/lightbox]'); ?>

  <?php
  $list_post = ob_get_contents(); //Lấy toàn bộ nội dung phía trên bỏ vào biến $list_post để return
  ob_end_clean();
  return $list_post;
}
add_shortcode('booktour','booktour');




function tour_info() {
  ob_start();  ?>

  <div class="box-info mgb-40 ">
    <div class="tour-place mgb-40">
        <h3 class="tour-heading">
          <?php _e('Location Information','cm'); ?>
        </h3>

        <?php if(get_field( 'place' )) { ?>
          <p class="primary-color">
          <?php _e('Location','cm'); ?>: <strong><?php the_field( 'place' ); ?></strong>
          </p>
        <?php } ?>

        <?php if(get_field( 'google_map' )) { ?>
        <div class="map">
          <?php the_field( 'google_map' ); ?>
        </div>
        <?php } ?>
    </div>

    <?php if(get_field( 'schedule' )) { ?>
    <div class="touring mgb-40">
        <h3 class="tour-heading">
          <?php _e('Tour Itinerary','cm'); ?>
        </h3>
        
        <div class="accordion">

          <?php $i=1; while ( has_sub_field('schedule' ) ) : ?>
          <div id="accordion-<?php echo $i; ?>" class="accordion-item"> 
            <a id="accordion-<?php echo $i; ?>-label" class="accordion-title plain" href="#accordion-item-accordion_title" aria-expanded="false" aria-controls="accordion-<?php echo $i; ?>-content"> 
              <button class="toggle" aria-label="Toggle">
                <i class="icon-angle-down"></i>
              </button> 
              <span>
                <?php the_sub_field( 'title' ); ?>
              </span> 
            </a>
            <div id="accordion-<?php echo $i; ?>-content" class="accordion-inner" aria-labelledby="accordion-<?php echo $i; ?>-label">
              <?php the_sub_field( 'content' ); ?>
            </div>
          </div>
          <?php $i++; endwhile; ?>
        </div>
        
    </div>
    <?php } ?>
  </div>


  <div class="box-info">
    <h3 class="tour-heading">
          <?php _e('Why Choose Us','cm'); ?>
    </h3>

    <?php if(get_field( 're_why','option' )) { ?>
    <div class="touring why-choose">
      <div class="accordion">

        <?php $i=1; while ( has_sub_field('re_why','option' ) ) : ?>
          <div id="accordion-<?php echo $i; ?>" class="accordion-item"> 
            <a id="accordion-<?php echo $i; ?>-label" class="accordion-title plain" href="#accordion-item-accordion_title" aria-expanded="false" aria-controls="accordion-<?php echo $i; ?>-content"> 
              <button class="toggle" aria-label="Toggle">
                <i class="icon-angle-down"></i>
              </button> 
              <span>
                <?php the_sub_field( 'title' ); ?>
              </span> 
            </a>
            <div id="accordion-<?php echo $i; ?>-content" class="accordion-inner" aria-labelledby="accordion-<?php echo $i; ?>-label">
              <?php the_sub_field( 'content' ); ?>
            </div>
          </div>
          <?php $i++; endwhile; ?>
        
      </div>
    </div>
    <?php } ?>
  </div>

  <?php
  $list_post = ob_get_contents(); //Lấy toàn bộ nội dung phía trên bỏ vào biến $list_post để return
  ob_end_clean();
  return $list_post;
}
add_shortcode('tour_info','tour_info');



if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Cài đặt themes',
        'menu_title'    => 'Cài đặt themes',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false,
        'position'         => 3
    ));
}

class ws247_widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'ws247_widget',
            'Sản phẩm liên quan',
            array('description'  =>  'Widget hiển thị Widget')
        );
    }

    public function form($instance)
    {
        $default = array();
        $instance = wp_parse_args((array) $instance, $default);
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        return $instance;
    }


    public function widget($args, $instance)
    {
        extract($args);
        $title = get_field('title', 'widget_' . $args['widget_id']);
        $number = get_field('pos', 'widget_' . $args['widget_id']);
        if ($title) : ?>

            <aside class="widget" id="fixed-widget-custom">
                <span class="widget-title"><?php echo $title ?></span>
                <div class="is-divider small"></div>
                <?php
                global $post;

                //get the taxonomy terms of custom post type
                $customTaxonomyTerms = wp_get_object_terms( $post->ID, 'product_cat', array('fields' => 'ids') );

                //query arguments
                $args = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => $number,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'id',
                            'terms' => $customTaxonomyTerms
                        )
                    ),
                    'post__not_in' => array ($post->ID),
                );

                //the query
                $relatedPosts = new WP_Query( $args );

                //loop through query
                if($relatedPosts->have_posts()){ ?>

                <div class="related-tour">
                  <?php while($relatedPosts->have_posts()){ 
                  $relatedPosts->the_post();
                  global $product; ?>

                  <a href="<?php the_permalink(); ?>">
                    <div class="related-item">
						<div class="box-image">
							<?php the_post_thumbnail('full') ?>
						</div>
						<div class="content">
						  <div class="title">
							<?php the_title(); ?>
						  </div>
						  <div class="icon">
							<?php
echo file_get_contents(
    get_stylesheet_directory() . '/assets/images/map.svg'
);
?>
							<span>
							  <?php the_field( 'place' ); ?>
							</span>
						  </div>

						  <div class="icon price">
							<?php echo file_get_contents(get_stylesheet_directory().'/assets/images/money.svg'); ?>
							<span>
<!-- 							  Giá &nbsp; -->
							  <?php echo $product->get_price_html(); ?>
							</span>
						  </div>
						</div>
                    </div>
                  </a>
                  <?php } ?>
                </div>

                <?php } wp_reset_postdata(); ?>

            </div>
            <?php
            wp_reset_postdata();
        endif;
    }
}
add_action('widgets_init', 'create_ws247_widget');
function create_ws247_widget()
{
    register_widget('ws247_widget');
}

// search form

function search_form() { 
  ob_start()
  ?>

  <form action="<?php echo get_site_url() ?>/search-tour" id="form-tour">
    <?php
      if (is_search()) {
          $search = get_search_query();
      } else {
          $search = '';
      }
      ?>

    <div class="row g-4">
      <div class="col large-3 medium-6 small-12">
        <label>Từ khóa</label>
        <input type="text" class="form-control" value="<?php echo $search ?>" name="tu-khoa" placeholder="<?php echo _e('Nhập từ khóa ....','hazo'); ?>">
      </div>

      <div class="col large-3 medium-6 small-12">
        <?php
        $taxonomies = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true
        ));
        if (!empty($taxonomies)) : ?>

          <label>Loại tour</label>
          <select name="loai-tour" id="" class="form-control">
              <option value=""><?php _e('Chọn loại tour', 'hazo') ?></option>
              <?php foreach ($taxonomies as $subcategory) {
                      if ($_GET['loai-tour'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                          $select = 'selected';
                      } else {
                          $select = '';
                      } ?>
                      <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>><?php echo $subcategory->name ?></option>
                  <?php } ?>
          </select>
        <?php endif; ?>

      </div>
      <!-- item --->


      <div class="col large-3 medium-6 small-12">
        <?php
        $taxonomies = get_terms(array(
            'taxonomy' => 'product_start',
            'hide_empty' => true
        ));
        if (!empty($taxonomies)) : ?>

          <label>Nơi khởi hành</label>
          <select name="khoi-hanh" id="" class="form-control">
              <option value=""><?php _e('Chọn nơi khởi hành', 'hazo') ?></option>
              <?php foreach ($taxonomies as $subcategory) {
                      if ($_GET['khoi-hanh'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                          $select = 'selected';
                      } else {
                          $select = '';
                      } ?>
                      <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>><?php echo $subcategory->name ?></option>
                  <?php } ?>
          </select>
        <?php endif; ?>

      </div>
      <!-- item --->

      <div class="col large-3 medium-6 small-12">
        <?php
        $taxonomies = get_terms(array(
            'taxonomy' => 'product_end',
            'hide_empty' => true
        ));
        if (!empty($taxonomies)) : ?>

          <label>Nơi đến</label>
          <select name="noi-den" id="" class="form-control">
              <option value=""><?php _e('Chọn nơi đến', 'hazo') ?></option>
              <?php foreach ($taxonomies as $subcategory) {
                      if ($_GET['noi-den'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                          $select = 'selected';
                      } else {
                          $select = '';
                      } ?>
                      <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>><?php echo $subcategory->name ?></option>
                  <?php } ?>
          </select>
        <?php endif; ?>

      </div>
      <!-- item --->

      <div class="col large-3 medium-6 small-12">
        <?php
        $taxonomies = get_terms(array(
            'taxonomy' => 'product_tour',
            'hide_empty' => true
        ));
        if (!empty($taxonomies)) : ?>

          <label>Dòng tour</label>
          <select name="dong-tour" id="" class="form-control">
              <option value=""><?php _e('Chọn dòng tour', 'hazo') ?></option>
              <?php foreach ($taxonomies as $subcategory) {
                      if ($_GET['dong-tour'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                          $select = 'selected';
                      } else {
                          $select = '';
                      } ?>
                      <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>><?php echo $subcategory->name ?></option>
                  <?php } ?>
          </select>
        <?php endif; ?>

      </div>
      <!-- item --->


      <div class="col large-3 medium-6 small-12">
       <label>Departure Date</label>
       <div id="date-time">
         <input type="text" class="date-time" name="ngay-khoi-hanh" placeholder="<?php if ($_GET['ngay-khoi-hanh']) { echo $_GET['ngay-khoi-hanh'];  } else { echo 'Chọn ngày khởi hành'; } ?>">
       </div>
       
      </div>
      <!-- item --->

      <div class="col large-3 medium-6 small-12">
        <button type="submit"
                class="bg-primary btn d-flex align-items-center text-white">
                
                <?php _e('Tìm kiếm ', 'hazo') ?><i style="margin-left: 10px;" class="icon-search"></i>
            </button>
      </div>
      <!-- item --->
  
    </div>
  </form>

  <script>
    jQuery(document).ready(function(){
            jQuery('#date-time .date-time').datepicker({ dateFormat: 'dd/mm/yy' });
        });
  </script>

  <?php 
  $list_post = ob_get_contents(); //Lấy toàn bộ nội dung phía trên bỏ vào biến $list_post để return
  ob_end_clean();
  return $list_post;
}
add_shortcode('search_form','search_form');


// ==========================================
// TÍNH NĂNG NHẬP BẢN DỊCH MENU GTRANSLATE
// ==========================================

// 1. Thêm field "Bản dịch Tiếng Việt" vào giao diện Admin
add_action('wp_nav_menu_item_custom_fields', 'hazo_add_menu_vi_translation_field', 10, 2);
function hazo_add_menu_vi_translation_field($item_id, $item) {
    $vi_translation = get_post_meta($item_id, '_menu_item_vi_translation', true);
    ?>
    <p class="field-vi_translation description description-wide">
        <label for="edit-menu-item-vi_translation-<?php echo esc_attr($item_id); ?>">
            <strong>Bản dịch Tiếng Việt (GTranslate)</strong><br>
            <input type="text" id="edit-menu-item-vi_translation-<?php echo esc_attr($item_id); ?>" class="widefat edit-menu-item-vi_translation" name="menu_item_vi_translation[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($vi_translation); ?>" />
            <span class="description">Nhập bản dịch tiếng Việt. Website sẽ tự động đổi ngôn ngữ mà không bị lỗi dịch máy.</span>
        </label>
    </p>
    <?php
}

// 2. Lưu dữ liệu khi ấn Save Menu
add_action('wp_update_nav_menu_item', 'hazo_save_menu_vi_translation_field', 10, 3);
function hazo_save_menu_vi_translation_field($menu_id, $menu_item_db_id, $args) {
    if (isset($_POST['menu_item_vi_translation'][$menu_item_db_id])) {
        $vi_value = sanitize_text_field($_POST['menu_item_vi_translation'][$menu_item_db_id]);
        update_post_meta($menu_item_db_id, '_menu_item_vi_translation', $vi_value);
    } else {
        delete_post_meta($menu_item_db_id, '_menu_item_vi_translation');
    }
}

global $hazo_menu_dict;
$hazo_menu_dict = array();

// 3. Tự động render HTML ghép 2 ngôn ngữ ra ngoài Front-end
add_filter('nav_menu_item_title', 'hazo_display_bilingual_menu_title', 10, 2);
function hazo_display_bilingual_menu_title($title, $item) {
    // Không áp dụng trong Admin
    if (is_admin()) return $title;
    
    $vi_translation = get_post_meta($item->ID, '_menu_item_vi_translation', true);
    
    if (!empty($vi_translation)) {
        global $hazo_menu_dict;
        // Gộp chuỗi để JS có thể tìm ra đoạn text bị Flatsome gộp (trường hợp Flatsome dùng .text())
        $mushed = wp_strip_all_tags($title) . wp_strip_all_tags($vi_translation);
        
        $html = '<span class="notranslate menu-bilingual"><span class="menu-lang-en">' . $title . '</span><span class="menu-lang-vi">' . esc_html($vi_translation) . '</span></span>';
        
        // Lưu vào từ điển
        $hazo_menu_dict[$mushed] = $html;
        
        return $html;
    }
    
    return $title;
}

// Khôi phục HTML bị Flatsome JS xóa thẻ (Sử dụng TreeWalker và Regex không phân biệt hoa thường)
add_action('wp_footer', 'hazo_fix_mushed_menu_js', 999);
function hazo_fix_mushed_menu_js() {
    global $hazo_menu_dict;
    if (empty($hazo_menu_dict)) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        var dict = <?php echo json_encode($hazo_menu_dict); ?>;
        
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function fixMushedMenus() {
            var walker = document.createTreeWalker(
                document.body,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function(node) {
                        if (node.parentNode.nodeName === 'SCRIPT' || node.parentNode.nodeName === 'STYLE') {
                            return NodeFilter.FILTER_REJECT;
                        }
                        var text = node.nodeValue;
                        var match = false;
                        for (var mushed in dict) {
                            var regex = new RegExp(escapeRegExp(mushed), 'i');
                            if (regex.test(text)) {
                                match = true;
                                break;
                            }
                        }
                        return match ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
                    }
                },
                false
            );

            var nodesToReplace = [];
            var node;
            while (node = walker.nextNode()) {
                nodesToReplace.push(node);
            }

            nodesToReplace.forEach(function(node) {
                var parent = node.parentNode;
                var text = node.nodeValue;
                var replaced = false;
                
                for (var mushed in dict) {
                    var regex = new RegExp(escapeRegExp(mushed), 'i');
                    if (regex.test(text)) {
                        text = text.replace(regex, dict[mushed]);
                        replaced = true;
                    }
                }
                
                if (replaced) {
                    var span = document.createElement('span');
                    span.innerHTML = text;
                    parent.insertBefore(span, node);
                    parent.removeChild(node);
                }
            });
        }
        
        fixMushedMenus();
        
        var observer = new MutationObserver(function(mutations) {
            var shouldFix = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) shouldFix = true;
            });
            if (shouldFix) fixMushedMenus();
        });
        
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        }
    });
    </script>
    <?php
}

// 4. Chèn CSS thẳng vào Header để tránh lỗi lưu Cache của trình duyệt
add_action('wp_head', 'hazo_bilingual_menu_css');
function hazo_bilingual_menu_css() {
    ?>
    <style>
        /* Ẩn mặc định cả 2 ngôn ngữ để dùng logic hiển thị bên dưới */
        .notranslate .menu-lang-vi,
        .notranslate .menu-lang-en { 
            display: none !important; 
        }

        /* Hiện tiếng Việt khi html có lang chứa "vi" */
        html[lang*="vi"] .notranslate .menu-lang-vi { 
            display: inline-block !important; 
        }

        /* Hiện tiếng Anh khi html có lang chứa "en", hoặc khi KHÔNG có lang "vi" */
        html[lang*="en"] .notranslate .menu-lang-en,
        html:not([lang*="vi"]) .notranslate .menu-lang-en { 
            display: inline-block !important; 
        }
    </style>
    <?php
}


// Search form sidebar

function search_form_side() { 
  ob_start()
  ?>

  <form action="<?php echo get_site_url() ?>/search-tour" id="form-tour">
    <?php
      if (is_search()) {
          $search = get_search_query();
      } else {
          $search = '';
      }
      ?>

<div class="row g-4">

  <div class="col large-3 medium-6 small-12">
    <?php
    $taxonomies = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true
    ));
    if (!empty($taxonomies)) : ?>

      <label>Tour Type</label>
      <select name="loai-tour" class="form-control">
          <option value=""><?php _e('Select Tour Type', 'hazo') ?></option>
          <?php foreach ($taxonomies as $subcategory) {
                  if ($_GET['loai-tour'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                      $select = 'selected';
                  } else {
                      $select = '';
                  } ?>
                  <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>>
                      <?php echo $subcategory->name ?>
                  </option>
              <?php } ?>
      </select>
    <?php endif; ?>
  </div>

  <div class="col large-3 medium-6 small-12">
    <?php
    $taxonomies = get_terms(array(
        'taxonomy' => 'tour_time',
        'hide_empty' => true
    ));
    if (!empty($taxonomies)) : ?>

      <label>Duration</label>
      <select name="so-luong-ngay" class="form-control">
          <option value=""><?php _e('Select Duration', 'hazo') ?></option>
          <?php foreach ($taxonomies as $subcategory) {
                  if ($_GET['so-luong-ngay'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                      $select = 'selected';
                  } else {
                      $select = '';
                  } ?>
                  <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>>
                      <?php echo $subcategory->name ?>
                  </option>
              <?php } ?>
      </select>
    <?php endif; ?>
  </div>

  <!-- Price Filter -->
  <div class="col large-3 medium-6 small-12">
    <label>Price Range</label>

    <?php
    $products = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'orderby' => 'meta_value_num',
        'meta_key'=> '_price',
        'posts_per_page' => -1,
    ));

    $highest = $products[array_key_first($products)];
    $lowest = $products[array_key_last($products)];

    $max_price = get_post_meta($highest->ID, '_price', true);
    $min_price = get_post_meta($lowest->ID, '_price', true);
    ?>

    <div class="price-content">
      <div>
        <label>From</label>
        <p id="min-value">
          <?php echo number_format((float)($_GET['min-price'] ?: $min_price), 0, ',', '.'); ?> VND
        </p>
      </div>

      <div>
        <label>To</label>
        <p id="max-value">
          <?php echo number_format((float)($_GET['max-price'] ?: $max_price), 0, ',', '.'); ?> VND
        </p>
      </div>
    </div>

    <div class="range-slider">
      <input type="range"
             name="min-price"
             class="min-price"
             value="0"
             min="0"
             max="<?php echo $_GET['max-price'] ?? $max_price; ?>"
             step="100000">

      <input type="range"
             name="max-price"
             class="max-price"
             value="<?php echo $_GET['max-price'] ?? $max_price; ?>"
             min="<?php echo $_GET['min-price'] ?? $min_price; ?>"
             max="<?php echo $_GET['max-price'] ?? $max_price; ?>"
             step="0">
    </div>
  </div>

  <div class="col large-3 medium-6 small-12">
    <?php
    $taxonomies = get_terms(array(
        'taxonomy' => 'product_end',
        'hide_empty' => true
    ));
    if (!empty($taxonomies)) : ?>

      <label>Destination</label>
      <select name="noi-den" class="form-control">
          <option value=""><?php _e('Select Destination', 'hazo') ?></option>
          <?php foreach ($taxonomies as $subcategory) {
                  if ($_GET['noi-den'] == $subcategory->slug || get_queried_object_id() == $subcategory->term_id) {
                      $select = 'selected';
                  } else {
                      $select = '';
                  } ?>
                  <option value="<?php echo $subcategory->slug ?>" <?php echo $select ?>>
                      <?php echo $subcategory->name ?>
                  </option>
              <?php } ?>
      </select>
    <?php endif; ?>
  </div>

  <div class="col large-3 medium-6 small-12">
    <button type="submit"
            class="bg-primary btn d-flex align-items-center text-white">
        <?php _e('Search', 'hazo') ?>
        <i style="margin-left:10px;" class="icon-search"></i>
    </button>
  </div>

</div>
  </form>


  <style>
   .card {
  width: 400px;
  background-color: #fff;
  border-radius: 5px;
  padding: 20px;
}

h4 {
  color: #000;
  margin-bottom: 20px;
  text-align: center;
  font-weight: 600;
}

.price-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.price-content p, .price-content label {
  font-size: 12px !important;
  font-weight: 600;
}
label {
  font-size: 12px;
  font-weight: 500;
}

p {
  font-size: 16px;
  font-weight: 600;
}

.range-slider {
  position: relative;
  margin: 15px 0 30px 0;
}

input[type=range] {
  -webkit-appearance: none;
  width: 100%;
  background: transparent; 
  position: absolute;
  left: 0;
}

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 15px;
  width: 15px;
  border-radius: 50%;
  background: var(--primary-color);
  cursor: pointer;
  margin-top: -5px;
  position: relative;
  z-index: 1;
}

input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 5px;
  background: #e8e8e8;
  border-radius: 3px;
  border: none;
}
  </style>
  <script>
  jQuery(document).ready(function(){
    jQuery('#date-time .date-time').datepicker({ dateFormat: 'dd/mm/yy' });


    let minValue = document.getElementById("min-value");
    let maxValue = document.getElementById("max-value");

    function formatNumberWithDotSeparator(number) {
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function validateRange(minPrice, maxPrice) {
      if (minPrice > maxPrice) {
        // Swap the Values
        let tempValue = maxPrice;
        maxPrice = minPrice;
        minPrice = tempValue;
      }

      minValue.innerHTML = formatNumberWithDotSeparator(minPrice) + " vnd";
      maxValue.innerHTML = formatNumberWithDotSeparator(maxPrice) + " vnd";
    }

    const inputElements = document.querySelectorAll(".range-slider input");

    inputElements.forEach((element) => {
      element.addEventListener("change", (e) => {
        let minPrice = parseInt(inputElements[0].value);
        let maxPrice = parseInt(inputElements[1].value);

        validateRange(minPrice, maxPrice);
      });
    });

    validateRange(inputElements[0].value, inputElements[1].value);
      
  });
</script>


  <?php 
  $list_post = ob_get_contents(); //Lấy toàn bộ nội dung phía trên bỏ vào biến $list_post để return
  ob_end_clean();
  return $list_post;
}
add_shortcode('search_form_side','search_form_side');

//



add_action('admin_init', function () {
    // Redirect any user trying to access comments page
    global $pagenow;
    
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});



function cptui_register_my_cpts() {
  /**
   * Post Type: Sản phẩm.
   */
  $labels = [
    "name" => __("Dịch vụ", "khitam"),
    "singular_name" => __("Dịch vụ", "khitam"),
  ]; 

  $args = [
    "label" => __("Dịch vụ", "khitam"),
    "labels" => $labels,
    "description" => "",
    "public" => true,
    "publicly_queryable" => true,
    "show_ui" => true,
    "show_in_rest" => true,
    "rest_base" => "",
    "rest_controller_class" => "WP_REST_Posts_Controller",
    "has_archive" => true,
    "show_in_menu" => true,
    "show_in_nav_menus" => true,
    "delete_with_user" => false,
    "exclude_from_search" => false,
    "capability_type" => "post",
    "map_meta_cap" => true,
    "hierarchical" => false,
    "rewrite" => ["slug" => "dich-vu", "with_front" => true],
    "query_var" => true,
    "menu_icon" => "dashicons-screenoptions",
    "supports" => ["title", "editor", "thumbnail"],
    "show_in_graphql" => false,
  ];

  register_post_type("service", $args);
  }
add_action('init', 'cptui_register_my_cpts');



add_action( 'woocommerce_single_product_summary', 'display_product_description', 20 );

function display_product_description() {
    echo '<div class="product-description">';
    the_content();  // Hiển thị mô tả chi tiết sản phẩm
    echo '</div>';
}

add_action('wp_footer', function(){
	?>
		<!-- Brevo Conversations {literal} -->
		<script>
			(function(d, w, c) {
				w.BrevoConversationsID = '6a5d82f794a1960693071d12';
				w[c] = w[c] || function() {
					(w[c].q = w[c].q || []).push(arguments);
				};
				var s = d.createElement('script');
				s.async = true;
				s.src = 'https://conversations-widget.brevo.com/brevo-conversations.js';
				if (d.head) d.head.appendChild(s);
			})(document, window, 'BrevoConversations');
		</script>
		<!-- /Brevo Conversations {/literal} -->
	<?php
});

/*load icon từ CDN*/
function vionis_enqueue_fontawesome() {
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        array(),
        '6.7.2'
    );
}
add_action('wp_enqueue_scripts', 'vionis_enqueue_fontawesome');

// Thêm tuỳ chọn Loại trừ danh mục vào block Products (ux_products) của Flatsome UX Builder
add_filter( 'ux_builder_shortcode_data_ux_products', 'vionis_add_exclude_cat_to_ux_products' );
function vionis_add_exclude_cat_to_ux_products( $data ) {
    $data['options']['filter_posts']['options']['cat_exclude'] = array(
        'type'       => 'select',
        'heading'    => __( 'Danh mục ẩn', 'flatsome' ),
        'config'     => array(
            'multiple'    => true,
            'placeholder' => __( 'Chọn danh mục để ẩn...', 'flatsome' ),
            'termSelect'  => array(
                'post_type'  => 'product',
                'taxonomies' => 'product_cat',
            ),
        ),
    );
    return $data;
}

// Bắt lấy tuỳ chọn cat_exclude trong shortcode ux_products
add_filter( 'pre_do_shortcode_tag', 'vionis_ux_products_cat_exclude_atts', 10, 4 );
function vionis_ux_products_cat_exclude_atts( $return, $tag, $attr, $m ) {
    if ( $tag === 'ux_products' || $tag === 'ux_bestseller_products' || $tag === 'ux_featured_products' || $tag === 'ux_sale_products' || $tag === 'ux_latest_products' || $tag === 'ux_custom_products' ) {
        if ( is_array( $attr ) && isset( $attr['cat_exclude'] ) ) {
            $GLOBALS['vionis_ux_products_cat_exclude'] = $attr['cat_exclude'];
        } else {
            $GLOBALS['vionis_ux_products_cat_exclude'] = '';
        }
    }
    return $return;
}

// Áp dụng bộ lọc cho query của Flatsome
add_action( 'pre_get_posts', 'vionis_apply_cat_exclude_to_ux_products', 999 );
function vionis_apply_cat_exclude_to_ux_products( $q ) {
    if ( is_admin() && ! wp_doing_ajax() ) return; 

    if ( ! empty( $GLOBALS['vionis_ux_products_cat_exclude'] ) ) {
        $post_type = $q->get('post_type');
        if ( $post_type === 'product' || (is_array($post_type) && in_array('product', $post_type)) ) {
            $hidden_categories = explode( ',', $GLOBALS['vionis_ux_products_cat_exclude'] );
            
            $tax_query = (array) $q->get( 'tax_query' );
            $tax_query[] = array(
                   'taxonomy' => 'product_cat',
                   'field'    => 'term_id',
                   'terms'    => $hidden_categories, 
                   'operator' => 'NOT IN'
            );
            $q->set( 'tax_query', $tax_query );
        }
    }
}

// Vionis Tour Categories Shortcode
add_shortcode( 'vionis_tour_categories', 'vionis_tour_categories_shortcode' );
function vionis_tour_categories_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'ids'     => '',
        'type'    => 'slider', // 'slider' hoặc 'row'
    ), $atts );

    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    );

    if ( ! empty( $atts['ids'] ) ) {
        $args['include'] = array_map( 'intval', explode( ',', $atts['ids'] ) );
        $args['orderby'] = 'include';
    } else {
        $args['parent'] = 0;
    }

    $categories = get_terms( $args );

    if ( empty( $categories ) || is_wp_error( $categories ) ) {
        return '';
    }

    $is_slider = $atts['type'] === 'slider';
    $item_count = count( $categories );
    $wrapper_classes = $is_slider 
        ? 'vionis-tour-wrapper row row-small slider slider-nav-circle slider-nav-push slider-style-normal vionis-items-' . $item_count 
        : 'vionis-tour-wrapper row row-small vionis-items-' . $item_count;
    
    // Flickity options: cuộn 1 ô mỗi lần, không lặp, không chấm tròn
    $flickity_attr = $is_slider 
        ? ' data-flickity-options=\'{"cellAlign": "left", "wrapAround": false, "pageDots": false, "autoPlay": false, "prevNextButtons": true, "groupCells": 1, "contain": true}\'' 
        : '';

    ob_start();
    ?>
    <style>
        /*==============================
            VIONIS TOUR CARD
        ==============================*/
        .vionis-tour-wrapper * {
            box-sizing: border-box;
        }
        .vionis-tour-card {
            position: relative;
            display: block;
            overflow: hidden;
            border-radius: 24px;
            height: 420px;
            text-decoration: none;
            background: #ddd;
        }
        .vionis-tour-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .8s ease;
        }
        .vionis-tour-card .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,.08), rgba(0,0,0,.22), rgba(0,0,0,.28));
            transition: .35s;
        }
        .vionis-tour-card .content {
            position: absolute;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
            z-index: 2;
        }
        .vionis-tour-card h3 {
            margin: 0;
            padding: 0;
            color: #fff;
            font-family: "Montserrat", sans-serif;
            font-size: 40px;
            font-weight: 800;
            line-height: 1.05;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0;
            -webkit-text-stroke: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,.45), 0 6px 16px rgba(0,0,0,.35);
            transition: all .35s ease;
        }
        .vionis-tour-card:hover img {
            transform: scale(1.08);
        }
        .vionis-tour-card:hover .overlay {
            background: linear-gradient(to bottom, rgba(0,0,0,.18), rgba(0,0,0,.32), rgba(0,0,0,.42));
        }
        .vionis-tour-card:hover h3 {
            transform: scale(1.03);
        }
        
        /* Tablet Mini Override (2 columns / 50% width) */
        @media(max-width: 768px) and (min-width: 550px) {
            .vionis-tour-wrapper .vionis-tour-cell {
                width: 50% !important;
                max-width: 50% !important;
                flex-basis: 50% !important;
            }
        }

        /* Responsive typography & heights */
        @media(max-width: 768px) {
            .vionis-tour-card { height: 360px; }
            .vionis-tour-card h3 { font-size: 34px; }
        }
        @media(max-width: 549px) {
            .vionis-tour-card { height: 280px; }
            .vionis-tour-card h3 { font-size: 24px; line-height: 1.15; }
        }

        /* CSS kiểm soát nút bấm prev/next của Slider */
        
        /* 1. Hiển thị nút bấm ở trạng thái disabled (mờ đi chứ không ẩn hẳn) */
        .vionis-tour-wrapper .flickity-button:disabled {
            opacity: 0.3 !important;
            pointer-events: none;
            cursor: not-allowed;
        }

        /* 2. Ẩn nút bấm hoàn toàn nếu số lượng item nhỏ hơn hoặc bằng số cột hiển thị trên thiết bị */
        
        /* Trên Desktop (>= 1025px) - Hiện 3 cột: Ẩn nếu <= 3 items */
        @media (min-width: 1025px) {
            .vionis-tour-wrapper.vionis-items-1 .flickity-button,
            .vionis-tour-wrapper.vionis-items-2 .flickity-button,
            .vionis-tour-wrapper.vionis-items-3 .flickity-button {
                display: none !important;
            }
        }
        
        /* Trên Tablet (769px - 1024px) - Hiện 3 cột: Ẩn nếu <= 3 items */
        @media (max-width: 1024px) and (min-width: 769px) {
            .vionis-tour-wrapper.vionis-items-1 .flickity-button,
            .vionis-tour-wrapper.vionis-items-2 .flickity-button,
            .vionis-tour-wrapper.vionis-items-3 .flickity-button {
                display: none !important;
            }
        }
        
        /* Trên Tablet Mini (550px - 768px) - Hiện 2 cột: Ẩn nếu <= 2 items */
        @media (max-width: 768px) and (min-width: 550px) {
            .vionis-tour-wrapper.vionis-items-1 .flickity-button,
            .vionis-tour-wrapper.vionis-items-2 .flickity-button {
                display: none !important;
            }
        }
        
        /* Trên Mobile (< 550px) - Hiện 1 cột: Ẩn nếu <= 1 item */
        @media (max-width: 549px) {
            .vionis-tour-wrapper.vionis-items-1 .flickity-button {
                display: none !important;
            }
        }
    </style>
    
    <div class="<?php echo esc_attr( $wrapper_classes ); ?>" <?php echo $flickity_attr; ?>>
        <?php foreach ( $categories as $category ) : 
            $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
            $image_url = wc_placeholder_img_src();
            if ( $thumbnail_id ) {
                $image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
                if ( $image ) {
                    $image_url = $image[0];
                }
            }
            $cat_link = get_term_link( $category );
            ?>
            <div class="col large-4 medium-4 small-12 vionis-tour-cell">
                <div class="col-inner">
                    <a class="vionis-tour-card" href="<?php echo esc_url( $cat_link ); ?>">
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $category->name ); ?>">
                        <div class="overlay"></div>
                        <div class="content">
                            <h3><?php echo esc_html( $category->name ); ?></h3>
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Tích hợp shortcode vionis_tour_categories vào Flatsome UX Builder
add_action( 'ux_builder_setup', 'vionis_ux_builder_tour_categories' );
function vionis_ux_builder_tour_categories() {
    $thumbnail = function_exists( 'flatsome_ux_builder_thumbnail' ) ? flatsome_ux_builder_thumbnail( 'gallery' ) : '';

    add_ux_builder_shortcode( 'vionis_tour_categories', array(
        'name'      => __( 'Vionis Danh Mục Tour' ),
        'category'  => __( 'Content' ),
        'thumbnail' => $thumbnail,
        'options' => array(
            'type' => array(
                'type'    => 'select',
                'heading' => 'Kiểu hiển thị',
                'default' => 'slider',
                'options' => array(
                    'slider' => 'Carousel (Slider)',
                    'row'    => 'Grid (Lưới)'
                )
            ),
            'ids' => array(
                'type' => 'select',
                'heading' => 'Chọn danh mục',
                'config' => array(
                    'multiple' => true,
                    'termSelect' => array(
                        'post_type' => 'product',
                        'taxonomies' => array('product_cat'),
                    )
                )
            )
        )
    ) );
}

// Thêm CDN Font chữ Montserrat
add_action( 'wp_enqueue_scripts', 'vionis_enqueue_google_fonts' );
function vionis_enqueue_google_fonts() {
    wp_enqueue_style( 'google-font-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap', array(), null );
}

/* ẩn yacht-tour với danh mục chung sản phẩm */
// add_action('pre_get_posts', function ($query) {

//     if (is_admin() || !$query->is_main_query()) {
//         return;
//     }

//     if (!is_product_category()) {
//         return;
//     }

//     $term = get_queried_object();

//     // Nếu đang xem Yacht Tour thì không lọc
//     if ($term->slug === 'yacht-tour') {
//         return;
//     }

//     $tax_query = $query->get('tax_query');

//     if (!is_array($tax_query)) {
//         $tax_query = [];
//     }

//     $tax_query[] = [
//         'taxonomy' => 'product_cat',
//         'field'    => 'slug',
//         'terms'    => ['yacht-tour'],
//         'operator' => 'NOT IN',
//     ];

//     $query->set('tax_query', $tax_query);

// });


/* ẩn yacht-tour với danh mục north-vietnam và danh mục con */
// add_action('pre_get_posts', function ($query) {

//     if (is_admin() || !$query->is_main_query()) {
//         return;
//     }

//     if (!is_product_category()) {
//         return;
//     }

//     $term = get_queried_object();

//     // Kiểm tra nếu đang ở North Vietnam hoặc bất kỳ danh mục con nào của nó
//     $is_north_vietnam = (
//         $term->slug === 'north-vietnam'
//         || term_is_ancestor_of(
//             get_term_by('slug', 'north-vietnam', 'product_cat'),
//             $term,
//             'product_cat'
//         )
//     );

//     if ($is_north_vietnam) {

//         $tax_query = (array) $query->get('tax_query');

//         $tax_query[] = [
//             'taxonomy' => 'product_cat',
//             'field'    => 'slug',
//             'terms'    => ['yacht-tour'],
//             'operator' => 'NOT IN',
//         ];

//         $query->set('tax_query', $tax_query);
//     }

// });

/* ẩn yacht-tour với danh mục north-vietnam */
add_action('pre_get_posts', function ($query) {

    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!is_tax('product_cat')) {
        return;
    }

    $term = get_queried_object();

    // Chỉ áp dụng cho North Vietnam
    if ($term->slug === 'north-vietnam') {

        $tax_query = (array) $query->get('tax_query');

        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => array('yacht-tour'),
            'operator' => 'NOT IN',
        );

        $query->set('tax_query', $tax_query);
    }

});

/**
 * Get category banner image URL based on product ID
 */
function get_product_category_banner_url( $product_id ) {
    $terms = wp_get_post_terms( $product_id, 'product_cat' );
    $term = null;
    
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        // 1. Check if any assigned term is yacht-tour or under yacht-tour
        foreach ( $terms as $t ) {
            $current = $t;
            while ( $current && ! is_wp_error( $current ) ) {
                if ( $current->slug === 'yacht-tour' ) {
                    $term = $t;
                    break 2; // Found yacht-tour associated term, break both loops
                }
                if ( $current->parent ) {
                    $current = get_term( $current->parent, 'product_cat' );
                } else {
                    break;
                }
            }
        }
        
        // 2. If no yacht-tour category, fall back to Rank Math primary category
        if ( ! $term ) {
            $primary_cat_id = get_post_meta( $product_id, 'rank_math_primary_product_cat', true );
            if ( $primary_cat_id ) {
                $term = get_term( $primary_cat_id, 'product_cat' );
            }
        }
        
        // 3. Fall back to the first category if still not set
        if ( ! $term || is_wp_error( $term ) ) {
            $term = $terms[0];
        }
    }
    
    // 4. Traverse up terms to find a category_banner_id, with fallback to thumbnail_id
    if ( $term && ! is_wp_error( $term ) ) {
        $current_term = $term;
        while ( $current_term && ! is_wp_error( $current_term ) ) {
            // First check the custom banner field
            $banner_id = get_term_meta( $current_term->term_id, 'category_banner_id', true );
            if ( $banner_id ) {
                $img_url = wp_get_attachment_image_url( $banner_id, 'full' );
                if ( $img_url ) {
                    return $img_url;
                }
            }
            // If no custom banner, check standard WooCommerce category thumbnail
            $thumbnail_id = get_term_meta( $current_term->term_id, 'thumbnail_id', true );
            if ( $thumbnail_id ) {
                $img_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
                if ( $img_url ) {
                    return $img_url;
                }
            }
            if ( $current_term->parent ) {
                $current_term = get_term( $current_term->parent, 'product_cat' );
            } else {
                break;
            }
        }
    }
    return '';
}

/**
 * Dynamically set background image for .banner-categories based on category image on single product page
 */
add_action( 'wp_head', 'custom_product_category_banner_css', 100 );
function custom_product_category_banner_css() {
    if ( is_singular( 'product' ) ) {
        $product_id = get_the_ID();
        $banner_url = get_product_category_banner_url( $product_id );
        
        // Output debug comment to HTML source to verify image retrieval
        echo "\n<!-- VIONIS DEBUG: Product ID = " . esc_html($product_id) . " | Banner URL = " . esc_url($banner_url) . " -->\n";
        
        if ( $banner_url ) {
            ?>
            <style>
                .banner-categories .section-bg {
                    background-image: url('<?php echo esc_url( $banner_url ); ?>') !important;
                    background-size: cover !important;
                    background-position: 50% 50% !important;
                }
                .banner-categories .section-bg img {
                    display: none !important;
                }
            </style>
            <?php
        }
    }
}

/**
 * Add custom banner image field to Product Category (product_cat)
 */
// Enqueue WordPress Media Uploader scripts for taxonomy page
add_action( 'admin_enqueue_scripts', 'custom_category_banner_media_scripts' );
function custom_category_banner_media_scripts( $hook ) {
    if ( 'edit-tags.php' === $hook && isset( $_GET['taxonomy'] ) && 'product_cat' === $_GET['taxonomy'] ) {
        wp_enqueue_media();
    }
}

// Add field to "Add New Category" screen
add_action( 'product_cat_add_form_fields', 'add_category_banner_field', 10, 2 );
function add_category_banner_field( $taxonomy ) {
    ?>
    <div class="form-field term-group">
        <label for="category_banner_id"><?php _e( 'Banner Image', 'woocommerce' ); ?></label>
        <input type="hidden" id="category_banner_id" name="category_banner_id" class="custom-img-id" value="" />
        <div id="category_banner_wrapper" style="margin-bottom: 10px;"></div>
        <p>
            <button type="button" class="upload_banner_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
            <button type="button" class="remove_banner_button button" style="display:none;"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
        </p>
        <?php custom_category_banner_js(); ?>
    </div>
    <?php
}

// Add field to "Edit Category" screen
add_action( 'product_cat_edit_form_fields', 'edit_category_banner_field', 10, 2 );
function edit_category_banner_field( $term, $taxonomy ) {
    $banner_id = get_term_meta( $term->term_id, 'category_banner_id', true );
    $image_url = '';
    if ( $banner_id ) {
        $image_url = wp_get_attachment_image_url( $banner_id, 'thumbnail' );
    }
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="category_banner_id"><?php _e( 'Banner Image', 'woocommerce' ); ?></label></th>
        <td>
            <input type="hidden" id="category_banner_id" name="category_banner_id" value="<?php echo esc_attr( $banner_id ); ?>" />
            <div id="category_banner_wrapper" style="margin-bottom: 10px;">
                <?php if ( $image_url ) : ?>
                    <img src="<?php echo esc_url( $image_url ); ?>" style="max-width: 150px; height: auto;" />
                <?php endif; ?>
            </div>
            <p>
                <button type="button" class="upload_banner_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
                <button type="button" class="remove_banner_button button" style="<?php echo $banner_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
            </p>
            <?php custom_category_banner_js(); ?>
        </td>
    </tr>
    <?php
}

// JavaScript for Media Uploader
function custom_category_banner_js() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            var file_frame;
            $(document).on('click', '.upload_banner_button', function(e) {
                e.preventDefault();
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: '<?php _e( "Select or Upload Category Banner", "woocommerce" ); ?>',
                    button: {
                        text: '<?php _e( "Use this image", "woocommerce" ); ?>'
                    },
                    multiple: false
                });
                file_frame.on('select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $('#category_banner_id').val(attachment.id);
                    var img_url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    $('#category_banner_wrapper').html('<img src="' + img_url + '" style="max-width:150px;height:auto;" />');
                    $('.remove_banner_button').show();
                });
                file_frame.open();
            });

            $(document).on('click', '.remove_banner_button', function(e) {
                e.preventDefault();
                $('#category_banner_id').val('');
                $('#category_banner_wrapper').empty();
                $(this).hide();
            });
        });
    </script>
    <?php
}

// Save fields
add_action( 'created_product_cat', 'save_category_banner_field', 10, 2 );
add_action( 'edited_product_cat', 'save_category_banner_field', 10, 2 );
function save_category_banner_field( $term_id, $tt_id ) {
    if ( isset( $_POST['category_banner_id'] ) ) {
        update_term_meta( $term_id, 'category_banner_id', sanitize_text_field( $_POST['category_banner_id'] ) );
    }
}