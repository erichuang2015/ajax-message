<?php
/*
Plugin Name: Ajax message
Plugin URI:  http://keksus.com/wordpress-plugins/ae.html
Description: Send message to email with ajax form
Version:     0.0.1
Author:      Keksus
Author URI:  http://keksus.com/
Text Domain: ae
Domain Path: /languages/
License:     GPLv3

Copyright 2017-2018 Keksus.com (email : alexbalance@gmail.com)
*/ 

// this is an include only WP file
if (!defined('ABSPATH')){
	die;
}

global $option_name,$options;
$option_name = 'ajax-message';
$options = get_option($option_name);

register_activation_hook(__FILE__, 'activate');
register_deactivation_hook(__FILE__, 'deactivate');

function activate(){
	global $option_name,$options;
	$data = array(
		'email'      =>  get_option('admin_email'),
		'name'       => 'Name:',
		'message'    => 'Message:',
		'submit'     => 'Send message',
		'width'      => '100%',
		'btn_color'  => '#000',
		'btn_text_color' => '#fff',
		'from'       =>  get_option('blogname'),
		'subject'    => 'Ajax Message',
		'success'    => 'Thank you, message sent',
		'error'      => 'Error: Message not sent',
		'textarea'   => '',
		'captcha_on' => '1',
		'captcha_digits' => '3'
	);
	
	update_option($option_name,  $data);

	$notices   = get_option('plugin_admin_notices', array());
    $notices = array( 
    	'content' => __('Thank you for installing this plugin! Plugin settings are available on the page', 'ae') .
    				  ' <a href="' . admin_url('options-general.php?page=ae_options_group'). '">Settings - Ajax Message</a>'
    );
    update_option('plugin_admin_notices', $notices);
}

function deactivate(){
	global $option_name;
	delete_option($option_name);
	delete_option('plugin_admin_notices');  
}

function plugin_admin_notices() {
	if ($notices = get_option('plugin_admin_notices')) {
		foreach ($notices as $notice) {
			echo "<div class='updated'><p>$notice</p></div>";
		}
		delete_option('plugin_admin_notices');
	}
}
add_action('admin_notices', 'plugin_admin_notices');

function scripts_frontend(){
	wp_enqueue_style('frontend-css',   plugins_url('css/frontend.css',__FILE__ ));
	wp_enqueue_style('admin-icons',    plugins_url('css/ionicons.min.css',__FILE__ ));
	wp_enqueue_script('frontend-ajax', plugins_url('js/frontend.js',__FILE__ ), array(jquery));
	wp_localize_script('frontend-ajax', 'ajax', 
		array( 
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('helloworld') 
		)
	);
}
add_action('wp_enqueue_scripts', 'scripts_frontend');

function scripts_admin(){
	$current_screen = get_current_screen();
    if($current_screen->id === "settings_page_ae_options_group" ) {
    	wp_enqueue_style('admin-css',   plugins_url('css/admin.css',__FILE__ ));
		wp_enqueue_style('admin-icons', plugins_url('css/ionicons.min.css',__FILE__ ));
		wp_enqueue_script('admin-js',   plugins_url('js/admin.js',__FILE__ ), array(jquery));
		wp_localize_script('admin-js', 'ajax', 
			array( 
				'url'   => admin_url('admin-ajax.php')
				//'nonce' => wp_create_nonce('helloadmin') 
			)
		);
    }
}
add_action('admin_enqueue_scripts', 'scripts_admin');

// show plugin footer text
function this_screen(){
    $current_screen = get_current_screen();
    if($current_screen->id === "settings_page_ae_options_group" ) {
    	add_filter('update_footer', 'right_admin_footer_text_output', 11); 
		function right_admin_footer_text_output($text){
		    $text = current_time('Y-m-d');
		    return $text;
		}
		add_filter('admin_footer_text', 'left_admin_footer_text_output'); 
		function left_admin_footer_text_output($text){
		    $text = __('Thank you for installing this plugin! Created by', 'ae').' <a class="created" href="http://keksus.com">Keksus</a>';
		    return $text;
		}
    }
}
add_action( 'current_screen', 'this_screen');

// add custom styles to header
function style_to_header(){
	global $option_name,$options;
	?><style type="text/css"><?php
		echo "\n". $options['textarea']. "\n";
	?></style><?php
}
add_action( 'wp_head', 'style_to_header' );

// frontend html form
function ae_form(){
	// send ajax script in plugin.js file
	global $option_name,$options;
	?>
	<div class='clear'>	
		<div class='q12' style='width:<?php echo _e($options['width'], 'ae');?>'>
			<form id='#ae' class='ajax-form' method='POST' action=''>
				<label for='text_field'><?php echo _e($options['name'], 'ae');?></label>
					<input id='name' type='text' class='txt' name='name'>

				<label for='text_area'><?php echo _e($options['message'], 'ae');?></label>
				<textarea id='message' rows='5' name='message'></textarea>

				<?php if( $options['captcha_on'] == '1' ): ?>
					<span class='captcha'><?php echo _e('Enter code:', 'ae'); ?></span>
					<?php echo ae_session(); ?>
					<input id='captcha' type='text' class='txt' name='captcha'>
				<?php endif; ?>
				<input id='from' type='hidden' name='<?php echo $option_name?>[from]' value='<?php echo $options['from']; ?>'>
				<input id='subject' type='hidden' name='<?php echo $option_name?>[subject]' value='<?php echo $options['subject']; ?>'>
				
				<div class="message-btn">
					<div>
						<input type='submit' name='submit' class='ajax-button' style='
						background:<?php echo _e($options['btn_color'], 'ae'); ?>;
						color: <?php echo _e($options['btn_text_color'], 'ae'); ?>'
						value='<?php echo _e($options['submit'], 'ae');?>'/> 
					</div>
					<div>
						<span id='load'><div id='loading'>LOADING!</div></span>
					</div>
				</div>
				<?php wp_nonce_field('helloworld'); ?>
			</form>
			<div id='response'></div>
		</div>
	</div>
	<?php
}

// validate and sent message
function ae_action_callback(){

	check_ajax_referer('helloworld');

	if( defined('DOING_AJAX') && DOING_AJAX ){
		global $option_name,$options;

		$name    = sanitize_text_field($_POST['name']);
		$message = nl2br( esc_textarea($_POST['message']));
		$from    = sanitize_text_field($_POST['from']);
		$subject = sanitize_text_field($_POST['subject']);

		// validate data
		if( '' == $name ){
			echo '<div class="alert-error"><span>'. __('Name required', 'ae') . '</span>';
			echo '<div class="alert-close"><i class="ion-android-close"></i></div>';
		}
		elseif( '' == $message ){
			echo '<div class="alert-error"><span>'. __('Message required', 'ae') . '</span>';
			echo '<div class="alert-close"><i class="ion-android-close"></i></div>';
		}
		elseif( isset($_SESSION['captcha']) ){
				if( $_POST['captcha'] == $_SESSION['captcha'] ){
					ae_mail($from,$subject,$name,$message);
				}
				else{
					echo '<div class="alert-error"><span>'. __('Wrong captcha code', 'ae') . '</span>';
					echo '<div class="alert-close"><i class="ion-android-close"></i></div>';
				}
		}
		else{
			ae_mail($from,$subject,$name,$message);
		}
		//echo wp_json_encode($_POST);
		wp_die();
	}
}
add_action('wp_ajax_ae_action', 'ae_action_callback');
add_action('wp_ajax_nopriv_ae_action', 'ae_action_callback');

// function used in callback
function ae_mail($from,$subject,$name,$message){
	global $option_name,$options;

	$to_email   = $options['email'];
	$body       = "<p><b>From:</b> $from</p>";
	$body	   .= "<p><b>Subject:</b> $subject</p>";
	$body      .= '===============================';
	$body      .= "<p><b>Name:</b> $name</p>";
	//$body      .= "<p><b>Email:</b> $from_email</p>";
	$body      .= "<p><b>Message:</b></p><p> $message</p>";  

	$headers   = 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers  .= "From: $name <wordpress@localhost>" . "\r\n";
	
	if( wp_mail($to_email, $subject, $body, $headers )){
		echo '<div class="alert-success"><span>'. __($options['success'], 'ae') .'</span>';
		echo '<div class="alert-close"><i class="ion-android-close"></i></div>';
	}
	else{
		echo '<div class="alert-error"><span>'. __($options['error'], 'ae') .'</span>';
		echo '<div class="alert-close"><i class="ion-android-close"></i></div>';
	}
}

// captcha session value
function ae_session(){
	global $option_name,$options;
	if( $options['captcha_on'] == '1' ){
		session_start();
		if(headers_sent()){
			$digits = $options['captcha_digits'];
			$_SESSION['captcha'] = rand(pow(10, $digits-1), pow(10, $digits)-1);
			$captcha  = $_SESSION['captcha'];
			return $captcha;
		}
	}
	elseif(isset($_SESSION)){
		session_destroy();
	}
}
add_action('init', 'ae_session');

// register plugin settings
function plugin_settings(){
	global $option_name,$options;
	register_setting('ae_options_group',  $option_name, 'sanitize_callback');
}
add_action('admin_init', 'plugin_settings');

// settings page for admin dashboard
function admin_page_settings(){
	add_options_page('Ajax Message settings', 'Ajax Message', 'manage_options', 'ae_options_group', 'options_output');
}
add_action('admin_menu', 'admin_page_settings');

function options_output(){
	global $option_name,$options;

	$checked = ( is_array($options) && $options['captcha_on'] == '1' ) ?  'checked="checked"' : '';
	?>
	<div class='options'>
	<?php //settings_errors(); ?>
		<h1><?php _e('Ajax Message', 'ae'); ?></h1>
		<form class='ajax-form-admin' method='POST' action="<?php echo admin_url('options.php'); ?>">
			<?php settings_fields('ae_options_group'); ?>    
			<div class='q12'>
				<div class='tabs'>
					<ul class='tab-links'>
						<li class='active'><a href='#tab1'><?php _e('Settings', 'ae'); ?></a></li>
						<li><a href='#tab2'><?php _e('Readme', 'ae'); ?></a></li>
					</ul>
				</div>
				<div class='tab-content active'>
					<div id='tab1' class='tab active' >
						<div class='clear'>
							<div class='q6'>
								<div class='post'>

									<h2><?php _e('Form field text', 'ae'); ?></h2>
									<?php //print_r($options) ?>
									<label for='text_field'><?php _e('Name field:', 'ae'); ?></label>
									<input type='text' class='txt name' name='<?php echo $option_name?>[name]' value='<?php echo $options['name']; ?>'>

									<label for='text_field'><?php _e('Message field:', 'ae'); ?></label>
									<input type='text' class='txt message' name='<?php echo $option_name?>[message]' value='<?php echo $options['message']; ?>'>

									<label for='text_field'><?php _e('Submit button (*):', 'ae'); ?></label>
									<input type='text' class='txt submit' name='<?php echo $option_name?>[submit]' value='<?php echo $options['submit']; ?>'>

									<h2><?php _e('Form styles', 'ae'); ?></h2>

									<label for='text_field'><?php _e('Form width (pixels or percents):', 'ae'); ?></label>
									<input type='text' class='txt' name='<?php echo $option_name?>[width]' value='<?php echo $options['width']; ?>'>

									<label for='text_field'><?php _e('Submit button color:', 'ae'); ?></label>
									<input type='text' class='txt' name='<?php echo $option_name?>[btn_color]' value='<?php echo $options['btn_color']; ?>'>

									<label for='text_field'><?php _e('Submit button text color:', 'ae'); ?></label>
									<input type='text' class='txt subtext' name='<?php echo $option_name?>[btn_text_color]' value='<?php echo $options['btn_text_color']; ?>'>

									<h2><?php _e('Form reply messages', 'ae'); ?></h2>

									<label for='text_field'><?php _e('Success message (*):', 'ae'); ?></label>
									<input type='text' class='txt success' name='<?php echo $option_name?>[success]' value='<?php echo $options['success']; ?>'>
									<label for='text_field'><?php _e('Error message (*):', 'ae'); ?></label>
									<input type='text' class='txt error' name='<?php echo $option_name?>[error]' value='<?php echo $options['error']; ?>'>

								</div><!-- end post -->
							</div>
							<div class='q6'>
								<div class='post'>

									<h2><?php _e('Send mail', 'ae'); ?></h2>
									<?php //print_r($options) ?>
									<label for='text_field'><?php _e('Destination Email address (*): ', 'ae'); ?></label>
									<input type='text' class='txt email' name='<?php echo $option_name?>[email]' value='<?php echo $options['email']; ?>'>

									<label for='text_field'><?php _e('From (*): ', 'ae'); ?></label>
									<input type='text' class='txt from' name='<?php echo $option_name?>[from]' value='<?php echo $options['from']; ?>'>

									<label for='text_field'><?php _e('Subject (*): ', 'ae'); ?></label>
									<input type='text' class='txt subj' name='<?php echo $option_name?>[subject]' value='<?php echo $options['subject']; ?>'>

									<h2><?php _e('Captcha', 'ae'); ?></h2>
									<label for='text_field'><?php _e('Captcha number of digits:', 'ae'); ?></label>
									<input type='text' class='txt captchadig' name='<?php echo $option_name?>[captcha_digits]' value='<?php echo $options['captcha_digits']; ?>'>
									<label for='text_field'><?php _e('Show/hide captcha:', 'ae'); ?></label>
									<input type="checkbox" name="<?php echo $option_name?>[captcha_on]" value="1"<?php echo $checked; ?> />

									<h2><?php _e('Custom CSS', 'ae'); ?></h2>
									<label for='text_field'><?php _e('CSS code: ', 'ae'); ?></label>
									<textarea spellcheck="false" id='textarea' class='txt textarea' name='<?php echo $option_name?>[textarea]' rows="5"><?php echo $options['textarea']; ?> </textarea>
									
								</div>	
							</div>
						</div>
					</div>
					<div id='tab2' class='tab'>
						<div class='clear'>
							<div class='q10'>
								<div class='post'>
									<h2><?php _e('How to:', 'ae'); ?></h2>
										<p>1. If you want to use Ajax message form on the page or post, add this shortcode inside the text editor:</p>
											<pre>[ae_message]</pre>
										<p>Also you can use the code written above in the widget "Ajax message".</p>
										<p>2. If you want to use Ajax message form in the theme code, add this code to your template:</p>
											<pre><?php echo "<>";
												   echo "?php>" ."do_shortcode('[ae_message]');" . "?>"; ?></pre>

									<h2><?php _e('License:', 'ae'); ?></h2>
										<p><?php _e( 'This plugin is licensed under the', 'ae' );?>
											<a target="_blank" href="https://www.gnu.org/licenses/gpl-3.0.html">GPL v3 license</a>
										<?php _e('This means you can use it for anything you like as long as it remains GPL v3.', 'ae' ); ?></p>

									<h2><?php _e('Links:', 'ae'); ?></h2>
										<p><?php _e( 'This plugin was created by', 'ae' );?>
											<a target="_blank" href="http://keksus.com/">Keksus.com</a>
										</p>
										<p><?php _e( 'A back-link to our website is very much appreciated or you can follow us via our social media!', 'ae' ); ?></p>
										<p class='links'>
											<a target="_blank" href="https://twitter.com/keks5588" class="button button-secondary">Twitter</a>
											<a target="_blank" href="https://www.facebook.com/keks5588" class="button button-secondary">Facebook</a>
											<a target="_blank" href="https://plus.google.com/110925729980114845157" class="button button-secondary">Google +</a>
											<a href="https://vk.com/keks5588" class="button button-secondary">VK</a>
										</p>

									<h2><?php _e('Donation:', 'ae'); ?></h2>
									<p><?php _e( 'If you would like this plugin, you can donate any amount for other plugins development.', 'colored' ); ?></p>
									<p class='links'><a href="http://keksus.com/donate.html" target="_blank" class="button button-secondary">
										<?php _e( 'Donate', 'colored' ); ?>
										</a>
									</p>
								</div><!-- end post -->
							</div>
					</div>		
				</div>
			</div>
			<div id='response' class='clear'></div>
			<div class='buttons clear'>
				<input type='submit' class='button-primary' value='<?php _e('Save Changes') ?>' />  
				<span id='load'><div id='loading'>LOADING!</div></span>
			</div>
		</form>
	</div>
	<?php
}

// clean options
function sanitize_callback($options){ 
	
	$type = 'error';
	if( empty($options['email'])){
		$message = __('Empty email field!', 'ae');
	}
	elseif( !is_email($options['email'])){
		$message = __('Not valid email!', 'ae');
	}
	elseif( empty($options['from'])){
		$message = __('Empty From field!', 'ae');
	}
	elseif( empty($options['subject'])){
		$message = __('Empty Subject field!', 'ae');
	}
	elseif( empty($options['submit'])){
		$message = __('Empty Submit button field!', 'ae');
	}
	elseif( empty($options['success'])){
		$message = __('Empty Success message field!', 'ae');
	}
	elseif( empty($options['error'])){
		$message = __('Empty Error message field!', 'ae');
	}
	else{
		$type = 'updated';
		$message = __('Settings Saved', 'ae');
	}
	add_settings_error($option_name, 'settings_updated', $message, $type );

	foreach( $options as $name => $val ){
		$val = sanitize_text_field($val);
	}
	return $options; //die(print_r($options )); 
}

// shortcode button [ae_message]
function ae_message_shortcode(){
	return ae_form();
}
add_shortcode('ae_message', 'ae_message_shortcode');

// Widget
function ajax_message_widget() {
	register_widget( 'Ajax_Message_Widget' );
}
add_action( 'widgets_init', 'ajax_message_widget' );

class Ajax_Message_Widget extends WP_Widget {

	public function __construct(){
		$widget_ops	 = array( 
			'classname' => 'ajax_message_widget', 
			'description' => __( 'Shortcode or HTML or Plain Text.', 'ae' )
		);
		parent::__construct( 'ae', __( 'Ajax Message', 'ae' ), $widget_ops );
	}

	public function widget($args, $instance ){
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base );
		
		echo $args['before_widget'];
		if ($title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<div class="textwidget">
			<?php 
				do_shortcode( apply_filters( 'widget_text', empty($instance['text']) ? '' : $instance['text'], $instance ));
			?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function update($new_instance, $old_instance ){
		$instance			 = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can( 'unfiltered_html' )) {
			$instance['text'] = $new_instance['text'];
		} else {
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text'])) ); // wp_filter_post_kses() expects slashed
		}
		$instance['filter'] = !empty($new_instance['filter']);
		return $instance;
	}

	public function form($instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ));
		?>
		<p><label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Title:', 'ae' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" /></p>

		<p><label for="<?php echo esc_attr($this->get_field_id( 'text' )); ?>"><?php esc_html_e( 'Content:', 'ae' ); ?></label>
			<textarea class="widefat" rows="16" cols="20" id="<?php echo esc_attr($this->get_field_id( 'text' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'text' )); ?>"><?php echo esc_textarea($instance['text']) ?></textarea></p>

		<p><input id="<?php echo esc_attr($this->get_field_id( 'filter' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'filter' )); ?>" type="checkbox" <?php checked( isset($instance['filter']) ? $instance['filter'] : 0  ); ?> />&nbsp;<label for="<?php echo esc_attr($this->get_field_id( 'filter' )); ?>"><?php esc_html_e( 'Automatically add paragraphs', 'ae' ); ?></label></p>
		<?php
	}
}


	


