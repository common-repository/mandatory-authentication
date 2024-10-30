<?php
/*
Plugin Name: Mandatory Authentication
Plugin URI: http://wordpress.org/extend/plugins/mandatory-authentication/
Description: Fill the (entire) page with a login screen if the user is not connected, else add a little widget that can replace the meta one.
Version: 1.0.0
Author: Gabriel Dromard
Author URI: http://dromard.blogspot.com
*/


function mandatoryauthentication() {
	$args["before_widget"]="<div class='mandatoryauthentication'>";
	$args["after_widget"]="</div>";
	$args["before_title"]="<h2>";
	$args["after_title"]="</h2>";
	widget_mandatoryauthentication($args);
}

function widget_mandatoryauthentication($args) {
		extract($args);
		
		global $user_ID;

		if (isset($user_ID)) {
			// User is logged in
			$user_info = get_userdata($user_ID);
			echo $before_widget . $before_title . __("Welcome "). $user_info->display_name . $after_title;
			echo '<ul class="pagenav">
					<li class="page_item"><a href="'.get_bloginfo('wpurl').'/wp-admin/">'.__('Dashboard').'</a></li>
					<li class="page_item"><a href="'.get_bloginfo('wpurl').'/wp-admin/profile.php">'.__('Profile').'</a></li>
					<li class="page_item"><a href="'.current_url('logout').'">'.__('Logout').'</a></li>
				</ul>';
		} else {
			echo '<div id="mandatoryauthentication">';
			// User is NOT logged in!!!
			echo $before_widget . $before_title . __("Login") . $after_title;
			// Show any errors
			global $myerrors;
			$wp_error = new WP_Error();
			if ( !empty($myerrors) ) {
				$wp_error = $myerrors;
			}
			if ( $wp_error->get_error_code() ) {
				$errors = '';
				$messages = '';
				foreach ( $wp_error->get_error_codes() as $code ) {
					$severity = $wp_error->get_error_data($code);
					foreach ( $wp_error->get_error_messages($code) as $error ) {
						if ( 'message' == $severity )
							$messages .= '	' . $error . "<br />\n";
						else
							$errors .= '	' . $error . "<br />\n";
					}
				}
				if ( !empty($errors) )
					echo '<div id="login_error">' . apply_filters('login_errors', $errors) . "</div>\n";
				if ( !empty($messages) )
					echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
			}
			// login form
			echo '<form action="'.current_url().'" method="post">';
			?>
			<p><label for="user_login"><?php _e('Username:') ?><br/><input name="log" value="<?php echo attribute_escape(stripslashes($_POST['log'])); ?>" class="mid" id="user_login" type="text" tabindex="1"/></label></p>
			<p><label for="user_pass"><?php _e('Password:') ?><br/><input name="pwd" class="mid" id="user_pass" type="password"  tabindex="2"/></label></p>
			<p><label for="rememberme"><input name="rememberme" class="checkbox" id="rememberme" value="forever" type="checkbox"  tabindex="3"/> <?php _e('Remember me'); ?></label></p>
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Login'); ?> &raquo;"  tabindex="4"/>
			<input type="hidden" name="mandatoryauthentication_posted" value="1" />
			<input type="hidden" name="testcookie" value="1" /></p>
			</form>
			<?php 			
			// Output other links
			echo '<ul class="mandatoryauthentication_otherlinks">';		
			if (get_option('users_can_register')) { 
				// MU FIX
				global $wpmu_version;
				if (empty($wpmu_version)) {
					?>
						<li><a href="<?php bloginfo('wpurl'); ?>/wp-login.php?action=register"><?php _e('Register') ?></a></li>
					<?php 
				} else {
					?>
						<li><a href="<?php bloginfo('wpurl'); ?>/wp-signup.php"><?php _e('Register') ?></a></li>
					<?php 
				}
			}
			?>
			<li><a href="<?php bloginfo('wpurl'); ?>/wp-login.php?action=lostpassword" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a></li>
			</ul>
			</div>
			<script type="text/javascript">
					var loginFormNode = document.getElementById("mandatoryauthentication");
					window.document.body.appendChild(loginFormNode);
			</script>
			<?php	
		}
		// echo widget closing tag
		echo $after_widget;
}

function widget_mandatoryauthentication_init() {
	if (!function_exists('register_sidebar_widget')) return;
	// Register widget for use
	register_sidebar_widget('Mandatory Authentication', 'mandatoryauthentication');
}

function widget_mandatoryauthentication_check() {
	if ($_POST['mandatoryauthentication_posted'] || $_GET['logout']) {
		// Includes
		global $myerrors;
		$myerrors = new WP_Error();
		//Set a cookie now to see if they are supported by the browser.
		setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
		if ( SITECOOKIEPATH != COOKIEPATH )
			setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
		// Logout
		if ($_GET['logout']==true) {
			nocache_headers();
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			wp_logout();
			wp_redirect(current_url('nologout'));
			exit();
		}
		// Are we doing a mandatory authentication login action?
		if ($_POST['mandatoryauthentication_posted']) {
		
			if ( is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
				$secure_cookie = false;
			else
				$secure_cookie = '';
		
			$user = wp_signon('', $secure_cookie);
			
			// Error Handling
			if ( is_wp_error($user) ) {
			
				$errors = $user;
	
				// If cookies are disabled we can't log in even with a valid user+pass
				if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
					$errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
					
				if ( empty($_POST['log']) && empty($_POST['pwd']) ) {
					$errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.'));
					$errors->add('empty_password', __('<strong>ERROR</strong>: Please enter your password.'));
				}
					
				$myerrors = $errors;
						
			} else {
				nocache_headers();
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Pragma: no-cache");
				wp_redirect(current_url('nologout'));
				exit;
			}
		}
	}
}
if ( !function_exists('current_url') ) :
function current_url($url = '') {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") $pageURL .= "s";
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	if ($url == "logout" && strstr($pageURL,'logout')==false) {
		if (strstr($pageURL,'?')) {
			$pageURL .='&logout=true';
		} else {
			$pageURL .='?logout=true';
		}
	} elseif ($url != "nologout") {
		$pageURL .='#login';
	}
	if ($url == "nologout" && strstr($pageURL,'logout')==true) {
		$pageURL = str_replace('?logout=true','',$pageURL);
		$pageURL = str_replace('&logout=true','',$pageURL);
	}
	//————–added by mick 
	if (!strstr(get_bloginfo('wpurl'),'www.')) $pageURL = str_replace('www.','', $pageURL );
	//——————–
	return $pageURL;
}
endif;





if (!function_exists('mandatoryauthentication_head')) :
	function mandatoryauthentication_head() {
		// Do not acivate plugin if it is not registered as widget !
	if ( !function_exists('register_sidebar_widget') ) return;
/**/
		global $user_ID;

		if (!isset($user_ID)) {
			echo '
	<style type="text/css"><!--
		body #page * { display: none; } 
		body #credits * { display: none; } 
		body #mandatoryauthentication {
			border: 1px solid black; 
			padding: 20px;
			text-align: center;
			width: 300px;
			margin-left: 40%;
			margin-top: 15%;
			background: rgb(225, 225, 225);
		}
		body #mandatoryauthentication form {
			text-align: left;
		}
		body #mandatoryauthentication form input {
			width: 295px;
			font-size: 14pt;
		}
		body #mandatoryauthentication p.submit {
			/*text-align: center;*/
		}
		body #mandatoryauthentication ul li {
			display: inline;
			padding: 20px;
		}
	// --></style>
			';
		}
		/**/
	}
endif;

// Run code and init
add_action('init', 'widget_mandatoryauthentication_check',1);
add_action('widgets_init', 'widget_mandatoryauthentication_init');
add_action('wp_head', 'mandatoryauthentication_head');
?>