=== Plugin Name ===
Contributors: Gabriel Dromard
Plugin URI: http://wordpress.org/extend/plugins/mandatory-authentication
Tags: login, plugin, behind, meta, form, register, authentication, mandatory
Requires at least: 2.6.3
Tested up to: 2.6.3
Stable tag: 1.0.0


Fill the (entire) page with a login screen if the user is not connected, else add a little widget that can replace the meta one.

== Description ==

Mandatory Authentication is a plugin that put a WordPress blog behind a login form. 
It can be usefull for a family blog whose members do not want to make their blog accessible from 
visitor but do not really need to have a hight security. This plugin does not sweat to those who host 
confidential informations, use a plugin like wp-sentry instead ! 


It lets users login, and then redirects them back to the blog, it also shows error messages.

NOTE: it is based on sidebar-login version 2.1.4 from jolley_small, thanks to his great job !

== Installation ==

= First time installation instructions =

   1. Unzip and upload the php file to your wordpress plugin directory
   2. Activate the plugin
   3. For a sidebar widget: Goto the design > widgets tab - Drag the widget into a sidebar and save!

== Screenshots ==

1. Login Form
2. After Login

== Warning ==

The login form still contains (as hidden) the original page content.
The RSS feed are readable !

This plugin does not makes your post private as some other plugins like wp-sentry can do ! 
It just force user to login !

== TODO ==

* Externalyze CSS of login form
* Make login form CSS modifiable from Admin GUI
