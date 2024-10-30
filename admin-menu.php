<?php

/*
 * Administration menu for Contact Form 7: ServiceNow Incidents
 */

function cf7snowi_admin_notices() {
  echo "
    <div id='notice' class='updated fade'>
      <p>" . CF7SNOWI_PLUGIN_NAME . " is not fully configured yet</p>
    </div>";
}

function cf7snowi_remove_admin_notices() {
  remove_action('admin_notices', 'cf7snowi_admin_notices');
}

function cf7snowi_options_page() {
  if( !current_user_can('manage_options') )
    wp_die(__('You do not have sufficient permissions to access this page.'));
  // Check if the form has been POSTed
  if( isset($_POST[CF7SNOWI_POST_HIDDEN_SUBMIT]) &&
      $_POST[CF7SNOWI_POST_HIDDEN_SUBMIT] == 'Y' ) {
    check_admin_referer('cf7snowi-admin-options');
    update_option(CF7SNOWI_OPT_AUTH_INSTANCE,
                  sanitize_text_field($_POST[CF7SNOWI_OPT_AUTH_INSTANCE]));
    update_option(CF7SNOWI_OPT_AUTH_USERNAME,
                  sanitize_text_field($_POST[CF7SNOWI_OPT_AUTH_USERNAME]));
    update_option(CF7SNOWI_OPT_AUTH_PASSWORD, $_POST[CF7SNOWI_OPT_AUTH_PASSWORD]);
    echo "<div class='updated'><p><strong>Settings updated</strong></p></div>";
  }
  // Display the settings page form
  ?>
  <h1><?php echo CF7SNOWI_PLUGIN_NAME; ?></h1>
  <h3>ServiceNow Authentication</h3>
  <form name="form1" method="post" action="">
    <?php wp_nonce_field('cf7snowi-admin-options'); ?>
    <input type="hidden"
           name="<?php echo CF7SNOWI_POST_HIDDEN_SUBMIT; ?>"
           value="Y" />

    <p>
      <strong>Instance ID</strong><br />
      <input type="text"
             name="<?php echo CF7SNOWI_OPT_AUTH_INSTANCE; ?>"
             placeholder="Instance ID"
             value="<?php esc_attr_e(get_option(CF7SNOWI_OPT_AUTH_INSTANCE, '')) ?>"
             size="32" />
    </p>
    <p>
      <strong>User ID</strong><br />
      <input type="text"
             name="<?php echo CF7SNOWI_OPT_AUTH_USERNAME; ?>"
             placeholder="User ID"
             value="<?php esc_attr_e(get_option(CF7SNOWI_OPT_AUTH_USERNAME, '')) ?>"
             size="32" />
    </p>
    <p>
      <strong>Password</strong><br />
      <input type="password"
             name="<?php echo CF7SNOWI_OPT_AUTH_PASSWORD; ?>"
             placeholder="Password"
             value="<?php esc_attr_e(get_option(CF7SNOWI_OPT_AUTH_PASSWORD, '')) ?>"
             size="32" />
    </p>
    <p class="submit">
      <input type="submit"
             name="Submit"
             class="button-primary"
             value="<?php esc_attr_e('Update') ?>" />
    </p>
  </form>
  <br />
  <div>
    <h2>Shortcode Guide</h2>

    <h3>Quick start</h3>
    <p>
      <pre>[cf7_servicenow_incident_form cf7_id='1234' debug='true']</pre>
    </p>

    <h3>Options</h3>
    <p>
      <strong>cf7_id</strong>: The ID of the Contact Form 7 form.
    </p>
    <p>
      <strong>debug</strong>: Set to 'true' to enable debug messages on posts.
    </p>
    <p>
      <strong>fields</strong>: Field mappings from CF7 to ServiceNow.
      This is a '|' (pipe) separated list of '=' (equal) separated strings.
      For instance, 'caller:first_name=fname|short_description=subject' is a valid
      fields string to use. <br /><br />
      In the above example field string, the user is telling the plugin that
      they want to change which CF7 field to use for the ServiceNow caller (user)
      first name. The CF7 field it will now use is 'fname'. Additionally, the
      ServiceNow incident's 'short_description' field will be mapped to the
      CF7 field 'subject'.<br /><br />
      Any ServiceNow field that starts with 'caller:' will be used when
      searching for, and creating, ServiceNow callers (users).
    </p>
    <h3>Default field mapping</h3>
    <table>
      <tr>
        <td style="padding-right: 20px;"><strong>Contact Form 7 field</strong></td>
        <td><strong>ServiceNow field</strong></td>
      </tr>
      <tr>
        <td>first-name</td>
        <td>caller:first_name</td>
      </tr>
      <tr>
        <td>last-name</td>
        <td>caller:last_name</td>
      </tr>
      <tr>
        <td>email</td>
        <td>caller:email</td>
      </tr>
      <tr>
        <td>subject</td>
        <td>short_description</td>
      </tr>
      <tr>
        <td>message</td>
        <td>description</td>
      </tr>
    </table>
  </div>
  <?php
}

function cf7snowi_admin_menu() {
  $hook_suffix = add_options_page(
    'Contact Form 7: ServiceNow Incidents options',
    'Contact Form 7: ServiceNow Incidents',
    'manage_options',
    'cf7_servicenow_incidents_options',
    'cf7snowi_options_page'
  );

  if( empty(get_option(CF7SNOWI_OPT_AUTH_INSTANCE, null)) ||
      empty(get_option(CF7SNOWI_OPT_AUTH_USERNAME, null)) ||
      empty(get_option(CF7SNOWI_OPT_AUTH_PASSWORD, null)) )
     add_action('admin_notices', 'cf7snowi_admin_notices');
  add_action('load-' . $hook_suffix, 'cf7snowi_remove_admin_notices');
}

add_action('admin_menu', 'cf7snowi_admin_menu');
