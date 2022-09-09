<?php

/**
 * Plugin Name:       WPW Melio User Access Manager
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Allow users access to Melio.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Daniel Jones
 * Author URI:        https://wpwhitesecurity.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       melio-access
 */

function wpw_melio_add_settings_page() {
    add_options_page( 'Melio access manager', 'Melio Access Manager', 'manage_options', 'wpw-melio-access', 'wpw_melio_render_plugin_settings_page' );
}

add_action( 'admin_menu', 'wpw_melio_add_settings_page' );

function wpw_melio_render_plugin_settings_page() {
    ?>
    <h2>WPW Melio access manager</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'wpw_melio_plugin_options' );
        do_settings_sections( 'wpw_melio_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function wpw_melio_register_settings() {
    register_setting( 'wpw_melio_plugin_options', 'wpw_melio_plugin_options', 'wpw_melio_plugin_options_validate' );
    add_settings_section( 'id_settings', 'IDs to allow', 'wpw_melio_section_text', 'wpw_melio_plugin' );
    add_settings_field( 'wpw_melio_settings_ids', 'IDs', 'wpw_melio_settings_ids', 'wpw_melio_plugin', 'id_settings' );
}
add_action( 'admin_init', 'wpw_melio_register_settings' );

function wpw_melio_plugin_options_validate( $input ) {
    $newinput['ids_to_allow'] = trim( $input['ids_to_allow'] );
    $ids = explode( ',', $newinput['ids_to_allow'] );
    $final_string = '';

    foreach ( $ids as $user_id ) {
        $user = get_userdata( $user_id );
        if ( $user !== false ) {
            $final_string .= $user_id . ',';
        }
    }

    $newinput['ids_to_allow'] = ( ! empty( $final_string ) ) ? substr( $final_string, 0, -1 ) : '';

    return $newinput;
}

function wpw_melio_section_text() {
    echo '<p>Here you can set all the user IDs</p>';
}

function wpw_melio_settings_ids() {
    $options = get_option( 'wpw_melio_plugin_options' );
    
    if ( ! isset( $options['ids_to_allow'] ) ) {
        $options['ids_to_allow'] = '';
    }
    echo "<input id='ids_to_allow' name='wpw_melio_plugin_options[ids_to_allow]' type='text' value='" . esc_attr( $options['ids_to_allow'] ) . "' />";
}

add_filter( 'nelio_content_can_user_manage_plugin', 'wpw_add_melio_users', 10, 2 );

function wpw_add_melio_users( $can_manage, $user_id ) {
    $options = get_option( 'wpw_melio_plugin_options' );
    if ( isset( $options['ids_to_allow'] ) ) {
        $ids = explode( ',', $options['ids_to_allow'] );
        if ( in_array( $user_id, $ids ) ) {
            return true;
        }
    }
    return $can_manage;
};