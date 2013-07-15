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
 * Version: 0.1.0
 **/
 
if ( ! class_exists( 'CoopSiteManager' )) :
	
class CoopSiteManager {

	var $suffix;

	public function __construct() {
		add_action( 'init', array( &$this, '_init' ));
	}

	public function _init() {
				
		wp_register_sidebar_widget('ci-widget','Contact Information',array(&$this,'ci_widget'));		
				
		if( is_admin()) {	
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_styles_scripts' ));
			add_action( 'admin_menu', array( &$this,'add_site_manager_menu' ));
			add_action( 'wp_ajax_coop-save-ci-change', array( &$this, 'ci_admin_save_changes'));
		}
	}
		
	public function admin_enqueue_styles_scripts($hook) {
	
	//	error_log('hook: ' . $hook);
	
		wp_register_script( 'coop-site-manager-admin-js', plugins_url( '/js/coop-site-manager.js',__FILE__), array('jquery'));
		wp_register_style( 'coop-site-manager-admin', plugins_url( '/css/coop-site-manager.css', __FILE__ ), false );
		
		wp_enqueue_style( 'coop-site-manager-admin' );
		wp_enqueue_script( 'coop-site-manager-admin-js' );
		
/*
		wp_register_script( 'coop-ci-admin-js', plugins_url( '/js/coop-ci-admin.js',__FILE__), array('jquery'));
		wp_register_style( 'coop-ci-admin', plugins_url( '/css/coop-ci-admin.css', __FILE__ ), false );
		
		wp_enqueue_style( 'coop-ci-admin' );
		wp_enqueue_script( 'coop-ci-admin-js' );
*/
		
	}
	
	public function frontside_enqueue_styles_scripts() {
		error_log(__FUNCTION__);
		wp_enqueue_style( 'coop-ci' );
	//	wp_enqueue_script( 'coop-ci-js' );
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
		
		$info = json_decode(get_option('coop-ci-info'));
		
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
		$out[] = '<input type="text" id="coop-ci-heading" name="coop-ci-heading" class="coop-ci" value="'.(!empty($info->heading)?$info->heading:'').'">';
		$out[] = '</td>';
		
		$out[] = '</tr>';
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Email:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-email" name="coop-ci-email" class="coop-ci" value="'.(!empty($info->email)?$info->email:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';
		
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Phone:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-phone" name="coop-ci-phone" class="coop-ci" value="'.(!empty($info->phone)?$info->phone:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';

		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Fax:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-fax" name="coop-ci-fax" class="coop-ci" value="'.(!empty($info->fax)?$info->fax:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';
		
	/*	
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Enable form?:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="checkbox" id="coop-ci-enable-form" name="coop-ci-enable-form" class="coop-ci" value="'.(!empty($info->enable_form)?$info->enable_form:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';
	*/	
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Street Address:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-address" name="coop-ci-address" class="coop-ci" value="'.(!empty($info->address)?$info->address:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';


		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">City/Town:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-city" name="coop-ci-form" class="coop-ci" value="'.(!empty($info->city)?$info->city:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';
		
		
		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Province:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-prov" name="coop-ci-prov" class="coop-ci" value="'.(!empty($info->prov)?$info->prov:'').'">';
		$out[] = '</td>';
		$out[] = '</tr>';


		$out[] = '<tr valign="top">';
		$out[] = '<th scope="row">';
		$out[] = '<label for="tag">Postal Code:</label>';
		$out[] = '</th>';
		$out[] = '<td>';
		$out[] = '<input type="text" id="coop-ci-pcode" name="coop-ci-pcode" class="coop-ci" value="'.(!empty($info->pcode)?$info->pcode:'').'">';
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
			'city' 		=> sanitize_text_field($_POST['city']),
			'prov' 		=> sanitize_text_field($_POST['prov']),
			'pcode' 	=> sanitize_text_field($_POST['pcode'])
		);
		
		$json = json_encode($info);
		
		if( update_option( 'coop-ci-info', $json ) ) {
			echo '{"result":"success","feedback":"Saved changes" }';
		}
		else {
			echo '{"result":"failed","feedback":"Failed to save changes" }';
		}
		die();
	}


	public function ci_widget($args) {

		extract($args);

		/**
		*	writes out the javascript 
		*	necessary to load this library's map
		*
		***/
		$out = array();
		
		$out[] = $before_widget;
		
		$info = json_decode(get_option('coop-ci-info'));
		
		if (!empty($info)) {
		
			$out[] = $before_title . $info->heading . $after_title;
	
			$out[] = '<div class="coop-contact-info">';
			$out[] = '<a href="mailto:'.$info->email.'">Email Us</a><br/>';
			$out[] = '<strong>Phone</strong>'.$info->phone.'<br/>';
			$out[] = '<strong>Fax</strong>'.$info->fax.'<br/>';
			$out[] = $info->address.'<br/>';
			$out[] = $info->city.' '.$info->prov.' ' .$info->pcode.'<br/>';
			$out[] = '</div><!-- .coop-contact-info -->';
		}
			
		if( empty($info)) {
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