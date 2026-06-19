<?php

/**
 * @link         https://metinsarac.net/
 * @since        1.1
 * @package     Popup_for_CF7_with_Sweet_Alert
 *
 * @wordpress-plugin
 *
 * Plugin Name:  Popup for CF7 with Sweet Alert
 * Plugin URI:   https://metinsarac.net/
 * Description:  Popup for CF7 with Sweet Alert
 * Version:      1.6.5
 * Author:       Metin Saraç
 * Author URI: https://metinsarac.net/
 * License:      GPL-3.0+
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:  cf7simplepopup
 */

// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Load plugin textdomain.

function cf7simplepopup_load_textdomain() {

    load_plugin_textdomain( 'cf7simplepopup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}

add_action( 'init', 'cf7simplepopup_load_textdomain' );

// Define Our Constants

define( 'cf7simplepopup_CORE_CSS', plugins_url( 'assets/css/', __FILE__ ) );
define( 'cf7simplepopup_CORE_JS', plugins_url( 'assets/js/', __FILE__ ) );
define( 'cf7simplepopup_ADMIN', plugins_url( 'admin/', __FILE__ ) );

// Register Css & JS

function cf7simplepopup_register() {

    $cf7spVersion = '1.6.4';

    wp_enqueue_style( 'cf7simplepopup-css', cf7simplepopup_CORE_CSS . 'cf7simplepopup-core.css', null, $cf7spVersion, 'all' );
    wp_enqueue_script( 'cf7simplepopup-js', cf7simplepopup_CORE_JS . 'cf7simplepopup-core.js', null, $cf7spVersion, 'all' );
    wp_enqueue_script( 'sweetalert', cf7simplepopup_CORE_JS . 'sweetalert2.all.min.js', null, $cf7spVersion, 'all' );
}


function cf7simplepopup_registerAdmin() {

    $cf7spVersion = '1.6.4';

    wp_enqueue_style( 'cf7simplepopup-admin', cf7simplepopup_ADMIN . 'admin.css', null, $cf7spVersion, 'all' );

}

add_action( 'wp_enqueue_scripts', 'cf7simplepopup_register' );
add_action( 'admin_enqueue_scripts', 'cf7simplepopup_registerAdmin' );

// Plugin Configuration

function cf7windowWidthHead() {

    $width     = get_option( 'cf7simplePopupWidth' ) == true ? get_option( 'cf7simplePopupWidth' ) : "500";
    $autoClose = get_option( 'cf7simplePopupAutoClose' ) == true ? get_option( 'cf7simplePopupAutoClose' ) : "7000";

    echo '<script>';
    echo 'var cf7windowWidth = ' . $width . ';';
    echo 'var cf7simplePopupAutoClose = ' . $autoClose . ';';
    echo '</script>';

}

add_action( 'wp_head', 'cf7windowWidthHead' );

// Go Premium Button

function goPremiumButton( $links, $file ) {

    static $this_plugin;

    if ( ! $this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }

    if ( false !== strpos( $file, 'cf7simplepopup.php' ) ) {
        $settings_link = '<a class="premiumButton" href="https://metinsarac.net/premium/popup-for-cf7-with-alert-premium/" target="_blank">' . __( 'Go Premium', 'cf7simplepopup' ) . '</a>';
        array_unshift( $links, $settings_link );
    }

    return $links;
}

add_filter( 'plugin_action_links', 'goPremiumButton', 10, 2 );


// Admin Setting(s)

add_action( 'admin_menu', 'cf7simplepopup_admin' );

function cf7simplepopup_admin() {
    add_submenu_page(
        'wpcf7',
        __( 'CF7 Sweet Alert Settings', 'cf7simplepopup' ),
        __( 'CF7 Swal Settings', 'cf7simplepopup' ),
        'manage_options',
        'cf7-simplepopup',
        'cf7simplepopup_page_callback' );
}

function cf7simplepopup_page_callback() {

    if ( isset($_POST['cf7simplePopupNonce']) && ! wp_verify_nonce( $_POST['cf7simplePopupNonce'], __FILE__ ) ) {

        update_option( 'cf7simplePopupWidth', $_POST['cf7simplePopupWidth'] );
        update_option( 'cf7simplePopupAutoClose', $_POST['cf7simplePopupAutoClose'] );

    }

    ?>

    <div class="wrap">

        <h1>
            <?php _e( 'CF7 Sweet Alert Settings', 'cf7simplepopup' ) ?>
        </h1>

        <p>
            <?php echo '<span class="dashicons dashicons-info" aria-hidden="true"></span> ' . __( 'You can leave the boxes blank for the default values.', 'cf7simplepopup' ) ?>
        </p>

        <form method="post">

            <?php wp_nonce_field( 'cf7simplePopup', 'cf7simplePopupNonce' ); ?>

            <table class="form-table">

                <tr>
                    <th><label for="cf7simplePopupWidth">
                            <?php _e( 'Popup Window Width', 'cf7simplepopup' ); ?>
                        </label></th>
                    <td>
                        <input type="text" name="cf7simplePopupWidth" id="cf7simplePopupWidth"
                            value="<?php echo get_option( 'cf7simplePopupWidth' ); ?>" class="regular-text">
                        <p class="description" id="tagline-description">
                            <?php _e( 'Please Enter <b>Pixel</b> Value.', 'cf7simplepopup' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th><label for="cf7simplePopupWidth">
                            <?php _e( 'Popup Window Auto Close Time', 'cf7simplepopup' ); ?>
                        </label></th>
                    <td>
                        <input type="text" name="cf7simplePopupAutoClose" id="cf7simplePopupAutoClose"
                            value="<?php echo get_option( 'cf7simplePopupAutoClose' ); ?>" class="regular-text">
                        <p class="description" id="tagline-description">
                            <?php _e( 'Please enter the value in miliseconds. <b>1 Second = 1000ms</b>', 'cf7simplepopup' ); ?>
                        </p>
                    </td>
                </tr>

            </table>

            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                    value="<?php _e( 'Save Settings', 'cf7simplepopup' ); ?>"></p>

        </form>

        <p>
        <div class="goPremiumText">
            <?php echo '<span class="dashicons dashicons-admin-network"></span> ' . __( '<a target="_blank" href="https://metinsarac.net/premium/popup-for-cf7-with-alert-premium/"> To Support Us; Switch to Premium Version to Enjoy All the Plugin\'s Features! Buy Now !</a>', 'cf7simplepopup' ) ?>
        </div>
        </p>

    </div>

    <?php

}