<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * This warning is displayed when the API KEY isn't already set or not valid
 *
 * @since 1.0
 */
function rocket_need_api_key()
{ ?>

	<div class="updated">
		<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b> : <?php echo sprintf ( __('To finish the install and take advantage of high performance provided by our plugin, thank you to <a href="%s">Enter you API key</a>.', 'rocket' ), admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) ) ;?></p>
	</div>

<?php
}

/**
 * Add Rocket informations into USER_AGENT
 *
 * @since 1.1.0
 */
function rocket_user_agent( $user_agent )
{
	$consumer_key = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_key'] ) ) {
		$consumer_key = $_POST[ WP_ROCKET_SLUG ]['consumer_key'];
	} elseif ( '' != (string) get_rocket_option( 'consumer_key' ) ) {
		$consumer_key = (string) get_rocket_option( 'consumer_key' );
	}

	$consumer_email = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_email'] ) ) {
		$consumer_email = $_POST[ WP_ROCKET_SLUG ]['consumer_email'];
	} elseif ( '' != (string) get_rocket_option( 'consumer_email' ) ) {
		$consumer_email = (string) get_rocket_option( 'consumer_email' );
	}

	$bonus = ! rocket_is_white_label() ? '' : '*';
	$bonus .= ! get_rocket_option( 'do_beta' ) ? '' : '+';
	$new_ua = sprintf( ';WP-Rocket|%s%s|%s|%s|%s|;', WP_ROCKET_VERSION, $bonus, $consumer_key, $consumer_email, esc_url( home_url() ) );

    return $new_ua;
}

/**
 * Renew all boxes for everyone if $uid is missing
 *
 * @since 1.1.10
 * @modified 2.1 :
 *	- Better usage of delete_user_meta into delete_metadata
 *
 * @param (int|null)$uid : a User id, can be null, null = all users
 * @param (string|array)$keep_this : which box have to be kept
 * @return void
 */
function rocket_renew_all_boxes( $uid = null, $keep_this = array() )
{
	// Delete a user meta for 1 user or all at a time
	delete_metadata( 'user', $uid, 'rocket_boxes', null == $uid );

	// $keep_this works only for the current user
	if ( ! empty( $keep_this ) && null != $uid ) {
		if ( is_array( $keep_this ) ) {
			foreach ( $keep_this as $kt ) {
				rocket_dismiss_box( $kt );
			}
		} else {
			rocket_dismiss_box( $keep_this );
		}
	}
}

/**
 * Renew a dismissed error box admin side
 *
 * @since 1.1.10
 *
 * @return void
 */
function rocket_renew_box( $function, $uid = 0 )
{
	global $current_user;
	$uid = $uid==0 ? $current_user->ID : $uid;
	$actual = get_user_meta( $uid, 'rocket_boxes', true );

	if( $actual && false !== array_search( $function, $actual ) ) {
		unset( $actual[array_search( $function, $actual )] );
		update_user_meta( $uid, 'rocket_boxes', $actual );
	}
}

/**
 * Dismissed 1 box, wrapper of rocket_dismiss_boxes()
 *
 * @since 1.3.0
 *
 * @return void
 */
function rocket_dismiss_box( $function )
{
	rocket_dismiss_boxes(
		array(
			'box'      => $function,
			'_wpnonce' => wp_create_nonce( 'rocket_ignore_' . $function ),
			'action'   => 'rocket_ignore'
		)
	);
}

/**
 * Is this version White Labeled?
 *
 * @since 2.1
 */
function rocket_is_white_label()
{
	$names = array( 'wl_plugin_name', 'wl_plugin_URI', 'wl_description', 'wl_author', 'wl_author_URI' );
	$options = '';
	foreach( $names as $value )
	{
		$options .= !is_array( get_rocket_option( $value ) ) ? get_rocket_option( $value ) : reset( ( get_rocket_option( $value ) ) );
	}
	return 'a509cac94e0cd8238b250074fe802b90' != md5( $options );
}

/**
 * Reset white label options
 *
 * @since 2.1
 *
 * @return void
 */
function rocket_reset_white_label_values( $hack_post )
{
	// White Label default values - !!! DO NOT TRANSLATE !!!
	$options = get_option( WP_ROCKET_SLUG );
	$options['wl_plugin_name']	= 'WP Rocket';
	$options['wl_plugin_slug']	= 'wprocket';
	$options['wl_plugin_URI']	= 'http://www.wp-rocket.me';
	$options['wl_description']	= array( 'The best WordPress performance plugin.' );
	$options['wl_author']		= 'WP Rocket';
	$options['wl_author_URI']	= 'http://www.wp-rocket.me';
	if ( $hack_post ) {
		// hack $_POST to force refresh of files, sorry
		$_POST['page'] = 'wprocket';
	}
	update_option( WP_ROCKET_SLUG, $options );
}


/**
 * Create a unique id for some Rocket options and functions
 *
 * @since 2.1
 */
function create_rocket_uniqid()
{
	return str_replace( '.', '', uniqid( '', true ) );
}

/**
 * This warning is displayed to inform the user that the plugin can not be tested in connected mode
 *
 * @since 2.2 Only returns a string on demand, no more hook
 * @since 1.1.10
 */
function rocket_warning_logged_users()
{
	return	'</strong><b>' . WP_ROCKET_PLUGIN_NAME . '</b>: ' .
			__( 'Connected users don\'t have the cached version of the website. We recommend you, to browse your website disconnected.', 'rocket' );
}

/**
 * This thankful message is displayed when the site has been added
 *
 * @since 2.2 On demand display, no hook
 */
function rocket_thank_you_license()
{
	return 	'</strong><b>' . WP_ROCKET_PLUGIN_NAME . '</b>:
			' . __( 'Thank you. Your license has been validated by our servers for you.', 'rocket' ) . '
			<br>
			' . sprintf( __( 'Key: <code>%s</code><br>Email: <i>%s</i>', 'rocket' ), get_rocket_option( 'consumer_key' ), get_rocket_option( 'consumer_email' ) );
}