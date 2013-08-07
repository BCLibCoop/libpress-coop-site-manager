<?php defined('ABSPATH') || die(-1);

/**
 * @package Coop Site Manager
 * @copyright BC Libraries Coop 2013
 *
 **/
/**
 * Plugin Name: Coop Site Manager
 * Description: NETWORK ACTIVATE. This is the common location for the other Coop Plugins to reside.
 * Author: Erik Stainsby, Roaring Sky Software
 * Author URI: http://roaringsky.ca/plugins/coop_site_manager/
 * Version: 0.2.1
 **/
 
if ( ! class_exists( 'CoopSiteManager' )) :
	
class CoopSiteManager {

	var $slug = 'coop-site-manager';

	public function __construct() {
		add_action( 'init', array( &$this, '_init' ));
	}

	public function _init() {
				
		wp_register_sidebar_widget( $this->slug.'-widget','Contact Information',array(&$this, 'coop_site_manager_ci_widget'));		
				
		if( is_admin()) {	
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_styles_scripts' ));
			add_action( 'admin_menu', array( &$this,'add_site_manager_menu' ));
			add_action( 'wp_ajax_coop-save-ci-change', array( &$this, 'ci_admin_save_changes'));
		}
	}
		
	public function admin_enqueue_styles_scripts($hook) {
		
		wp_register_script( 'coop-site-manager-admin-js', plugins_url( '/js/coop-site-manager.js',__FILE__), array('jquery'));
		wp_register_style( 'coop-site-manager-admin', plugins_url( '/css/coop-site-manager.css', __FILE__ ), false );
		
		wp_enqueue_style( 'coop-site-manager-admin' );
		wp_enqueue_script( 'coop-site-manager-admin-js' );	
	}
	
	public function frontside_enqueue_styles_scripts() {
		wp_enqueue_style( 'coop-ci' );
	}
	
	
	public function add_site_manager_menu() 
	{
		//									page			menu			cap				handle/slug		
		$this->suffix = add_menu_page( 'Contact Information', 'Site Manager', 'manage_local_site', 'site-manager', array(&$this,'admin_contact_info_page'), '', 29 );
	
		add_submenu_page( 'site-manager', 'Contact Information','Contact Information', 'manage_local_site', 'site-manager', array(&$this,'admin_contact_info_page'));
	
	//	error_log('suffix: ' . $this->suffix );
	}

	
	public function admin_contact_info_page () {
		
		if( ! current_user_can('manage_local_site') ) die('You do not have required permissions to view this page');
		
		$info = get_option('coop-ci-info');
		
		$out = array();
		
		$out[] = '<div class="wrap">';
		
		$out[] = '<div id="icon-options-general" class="icon32">';
		$out[] = '<br>';
		$out[] = '</div>';
		
		$out[] = '<h2>Contact Information parameters</h2>';
		
		$out[] = '<p>Contact info used on the front page of the site</p>';
		
		$out[] = '<table class="form-table">';
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Heading:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-heading" name="coop-ci-heading" class="coop-ci input-wide" value="'.$info['heading'].'">';
		$out[] = '</td>';
		
		$out[] = '</tr>';
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Email:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-email" name="coop-ci-email" class="coop-ci input-wide" value="'.$info['email'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';
		
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Phone:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-phone" name="coop-ci-phone" class="coop-ci input-wide" value="'.$info['phone'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';

		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Fax:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-fax" name="coop-ci-fax" class="coop-ci input-wide" value="'.$info['fax'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Address 1:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-address" name="coop-ci-address" class="coop-ci input-wide" value="'.$info['address'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';


		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Address 2:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-address-2" name="coop-ci-address-2" class="coop-ci input-wide" value="'.$info['address2'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';


		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">City/Town:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-city" name="coop-ci-form" class="coop-ci input-wide" value="'.$info['city'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Province:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-prov" name="coop-ci-prov" class="coop-ci input-wide" value="'.$info['prov'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';


		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Postal Code:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-pcode" name="coop-ci-pcode" class="coop-ci input-wide" value="'.$info['pcode'].'">';
		$out[] = '</td>';
		$out[] = '</tr>';


		$out[] = '</table>';
		
		$out[] = '<p class="submit">';
		$out[] = '<input type="submit" value="Save Changes" class="button button-primary" id="coop-ci-submit" name="submit">';
		$out[] = '</p>';
		
		echo implode("\n",$out);
		
	}
	
	
	public function ci_admin_save_changes() {
				
		error_log( __FUNCTION__ );		
		
		$info = array(
			'heading'	=> sanitize_text_field($_POST['heading']),
			'email' 	=> sanitize_text_field($_POST['email']),
			'phone' 	=> sanitize_text_field($_POST['phone']),
			'fax' 		=> sanitize_text_field($_POST['fax']),
		//	'enable_form' 		=> $_POST['enable_form'],
			'address' 	=> sanitize_text_field($_POST['address']),
			'address2' 	=> sanitize_text_field($_POST['address2']),
			'city' 		=> sanitize_text_field($_POST['city']),
			'prov' 		=> sanitize_text_field($_POST['prov']),
			'pcode' 	=> sanitize_text_field($_POST['pcode'])
		);
		
		
		
		if( update_option( 'coop-ci-info', $info ) ) {
			echo '{"result":"success","feedback":"Saved changes" }';
		}
		else {
			echo '{"result":"failed","feedback":"Failed to save changes" }';
		}
		die();
	}


	public function coop_site_manager_ci_widget($args) {

		extract($args);

		$out = array();	
		$out[] = $before_widget;
		
		$info = maybe_unserialize(get_option('coop-ci-info'));
		
		if (!empty($info)) {
			$out[] = $before_title . $info['heading'] . $after_title;
			$out[] = '<div class="coop-contact-info">';
			if( !empty( $info['email'] )) {
				$out[] = '<a href="mailto:'.$info['email'].'">Email Us</a><br/>';
			}
			if( !empty( $info['phone'] )) {
				$out[] = '<strong>Phone</strong> '.$info['phone'].'<br/>';
			}
			if( !empty( $info['fax'] )) {
				$out[] = '<strong>Fax</strong> '.$info['fax'].'<br/>';
			}
			if( !empty( $info['address'] )) {
				$out[] = $info['address'].'<br/>';
				if( !empty($info['address2'])) {
					$out[] = $info['address2'].'<br/>';
				}
				$out[] = $info['city'].' '.$info['prov'].' ' .$info['pcode'].'<br/>';
			}
			$out[] = '</div><!-- .coop-contact-info -->';
		}
		else {
			return '<!-- no results from ContactInfo plugin -->';
		}
		$out[] = $after_widget;
		
		echo implode("\n",$out);
	}

	
}

if( ! isset($coopsitemanager) ) {
	global $coopsitemanager;
	$coopsitemanager = new CoopSiteManager();
}

endif; /* ! class_exists */