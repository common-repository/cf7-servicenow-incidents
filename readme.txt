=== Contact Form 7: ServiceNow Incidents integration ===
Contributors: jcornutt
Donate link: https://joscor.com/cf7-servicenow-incidents
Tags: cf7, contact form 7, servicenow, snow, incident, incidents, incident reporting, itsm, itil, service management, contact form, cf7 integration, wordpress, curl, xml
Requires at least: 3.2
Tested up to: 4.6.1
Stable tag: 4.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ServiceNow Incident reporting integration for Contact Form 7 forms.


== Description ==

The ServiceNow Incidents integration for Contact Form 7 plugin allows existing CF7 users to
automatically create ServiceNow incident (and user / caller) entries on form submission.
When a user enters their information into a CF7 form and hits submit, this plugin will
forward their information to ServiceNow as well as let CF7 continue its
form submission process. Note - This plugin is developed, maintained, and
supported by an independent developer. The developer has no affiliation with
Contact Form 7 or ServiceNow.

= ServiceNow integration =

This plugin interacts with the third-party ServiceNow API (Application Programming Interface)
in order to create new callers (users) and incident reports.
This is a public web service provided by ServiceNow and
additional information can be found [here](http://wiki.servicenow.com/index.php?title=REST_API).


== Features ==

* Easy CF7 to ServiceNow field mapping
* Supports creating users / callers if they don't exist yet
* Future compatibility with flexible field mapping
* Convenient, intuitive shortcode
* Seamless integration and no change to contact form flow

== Installation ==

1. Download the plugin and unzip to your wp-content/plugins directory
2. Alternatively, just upload the zip file via the WordPress plugins UI
3. Activate plugin via Wordpress admin
4. Include the following shortcode on your page or post

= Shortcode usage =

`
[cf7_servicenow_incident_form cf7_id="[CF7_Form_ID]"]
`

Replace *[CF7_Form_ID]* with the ID of your CF7 form. See the
admin settings page for additional shortcode information.

== Changelog ==

= 0.0.1 =
* Genesis
