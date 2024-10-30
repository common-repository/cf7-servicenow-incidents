<?php
/*
Plugin Name: Contact Form 7: ServiceNow Incidents
Plugin URI: https://developer.wordpress.org/plugins/cf7-servicenow-incidents
Description: ServiceNow incident report integration for Contact Form 7 forms
Version: 0.0.1
Author: Joshua Cornutt
Author URI: https://joscor.com
License: MIT
*/

const CF7SNOWI_PLUGIN_NAME = 'Contact Form 7: ServiceNow Incidents integration';
const CF7SNOWI_POST_HIDDEN_SUBMIT = 'cf7snowi_submit';
const CF7SNOWI_POST_HIDDEN_FIELDS = 'cf7snowi_user_fields';
const CF7SNOWI_OPT_AUTH_INSTANCE = 'cf7snowi_auth_instance';
const CF7SNOWI_OPT_AUTH_USERNAME = 'cf7snowi_auth_username';
const CF7SNOWI_OPT_AUTH_PASSWORD = 'cf7snowi_auth_password';

include_once( plugin_dir_path(__FILE__) . 'admin-menu.php');
include_once( plugin_dir_path(__FILE__) . 'libs/snow-api.php');

function cf7snowi_init_scripts() {
  wp_enqueue_script('jquery');
}

/**
 * Callback for the wpcf7_before_send_mail action.
 *
 * Captures submitted data from a specific Contact Form 7
 * form and uses the information to insert an incident
 * into ServiceNow
 *
 * @param WPCF7_ContactForm $contact_form
 *
 * @return void
 */
function cf7snowi_cf7_email_hook($contact_form) {
  $snow_instance = get_option(CF7SNOWI_OPT_AUTH_INSTANCE, null);
  $snow_username = get_option(CF7SNOWI_OPT_AUTH_USERNAME, null);
  $snow_password = get_option(CF7SNOWI_OPT_AUTH_PASSWORD, null);
  // Bail if there's missing authentication pieces
  if( empty($snow_instance) || empty($snow_username) || empty($snow_password) )
    return;
  // Get the CF7 posted data and title
  $cf7_data = WPCF7_Submission::get_instance()->get_posted_data();
  $caller = array();
  $incident = array();
  // Get the user-defined overrides
  $cf7snowi_fields = $cf7_data[CF7SNOWI_POST_HIDDEN_FIELDS];
  // Build the default map
  $map = array(
    "caller:first_name" => "first-name",
    "caller:last_name" => "last-name",
    "caller:email" => "email",
    "short_description" => "subject",
    "description" => "message"
  );
  // Build the user-defined map
  $overrides = array();
  foreach (explode('|', $cf7snowi_fields) as $chunk) {
      list($key, $val) = explode('=', $chunk);
      if( empty($key) ) continue;
      if( empty($val) ) $val = null;
      $overrides[$key] = $val;
  }
  // Map default CF7 fields to ServiceNow incident fields
  foreach( $map as $k => $v )
    if( $cf7_data[$v] )
      $incident[$k] = $cf7_data[$v];
  // Map user-defined CF7 fields to ServiceNow incident fields
  foreach( $overrides as $k => $v )
    if( !is_null($v) && $cf7_data[$v] )
      $incident[$k] = $cf7_data[$v];
  // Separate the incident fields from the caller fields
  foreach( $incident as $k => $v ) {
    if( substr($k, 0, strlen('caller:')) === 'caller:' ) {
      $caller[str_replace('caller:', '', $k)] = $v;
      unset($incident[$k]);
    }
  }
  // Get an interface to the SNOW API
  $sn = new ServiceNow($snow_instance, $snow_username, $snow_password);
  // Try and find the caller (system user)
  $snow_user = NULL;
  if( count($caller) ) {
    $snow_user = $sn->get_user($caller);
    // If the user doesn't exist, create an entry
    if( is_null($snow_user) )
      $snow_user = $sn->create_user($caller);
  }
  // Use sys_id of the caller if it exists
  if( !is_null($snow_user) && array_key_exists('sys_id', $snow_user) )
    $incident['caller_id'] = $snow_user['sys_id'];
  // Create the incident
  $sn->create_incident($incident);
}

/**
 * Callback for the wpcf7_form_hidden_fields filter.
 *
 * Injects custom hidden fields into the Contact Form 7
 * form. In this case, the "fields" attribute from the
 * shortcode (string) is saved for recall
 * by cf7_email_hook().
 *
 * @param Array $array Key/value pairs of hidden field names and values
 *
 * @return Array
 */
function cf7snowi_filter_wpcf7_form_hidden_fields($array) {
  global $cf7snowi_fields;
  $array[CF7SNOWI_POST_HIDDEN_FIELDS] = $cf7snowi_fields;
  return $array;
}

/**
 * Callback for using the "cf7snowi" shortcode.
 *
 * Collects shortcode attributes, prepares the user-defined
 * "fields" string (mapping overrides) to be consumed by
 * filter_wpcf7_form_hidden_fields(), and displays the
 * Contact Form 7 form.
 *
 * @param Array $atts Key/value pairs of shortcode attributes
 *
 * @return String Contact Form 7 HTML form
 */
function cf7snowi_build_shortcode($atts) {
  $scopts = shortcode_atts(array(
    'cf7_id' => '',
    'debug' => false,
    'fields' => ''), $atts);
  // Check for the API authentication options (admin settings)
  if( empty(get_option(CF7SNOWI_OPT_AUTH_INSTANCE, null)) ||
      empty(get_option(CF7SNOWI_OPT_AUTH_USERNAME, null)) ||
      empty(get_option(CF7SNOWI_OPT_AUTH_PASSWORD, null)) )
    return ($scopts['debug'] ? 'All ServiceNow authentication options must be set before use' : '');
  // Save the shortcode fields for later
  global $cf7snowi_fields;
  $cf7snowi_fields = $scopts['fields'];
  // Send the user-defined fields as a hidden input
  add_filter('wpcf7_form_hidden_fields', 'cf7snowi_filter_wpcf7_form_hidden_fields', 10, 1);
  // Check that the CF7 form exists (by ID)
  global $wpcf7_contact_form;
  if( !($wpcf7_contact_form = wpcf7_contact_form($scopts['cf7_id'])) )
    return ($scopts['debug'] ? "Could not find Contact Form 7 by ID" : '');
  return $wpcf7_contact_form->form_html();
}

add_action('wp_enqueue_scripts', 'cf7snowi_init_scripts');
add_shortcode('cf7_servicenow_incident_form', 'cf7snowi_build_shortcode');
add_action('wpcf7_before_send_mail', 'cf7snowi_cf7_email_hook', 10, 1);
