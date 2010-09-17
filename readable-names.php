<?php
/*
Plugin Name: Readable Names
Plugin URI: http://wordpress.org/extend/plugins/readable-names/
Description: The plugin forces commenters to write their names in the language that your blog uses.
Version: 0.6.1
Author: Anatol Broder
Author URI: http://doktorbro.net/
License: GPL2
Text Domain: readable_names
*/

if ( ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Readable_Names {

	function Readable_Names() {
		// activation, deactivation and uninstall
		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );
		register_uninstall_hook( __FILE__, array( $this, 'plugin_uninstall' ) );
		
		// add actions
		add_action( 'init', array( $this, 'plugin_init' ) );
		add_action( 'pre_comment_on_post', array( $this, 'check_comment_author' ) );
		add_action( 'user_profile_update_errors', array( $this, 'check_user_profile' ), 1, 3 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'call_add_options_page' ) );
		add_filter( 'plugin_action_links', array( $this, 'init_action_links' ), 10, 2 );
	}
	
	function plugin_init() {
		load_plugin_textdomain( 'readable_names', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	
	function plugin_activation() {
		$options = get_option( 'readable_names' );
		if ( ! $options ) {
			$options = $this->options_default();
		}
		else {
			$options = $this->options_upgrade( $options );
		}
		delete_option( 'readable_names' );
		add_option( 'readable_names', $options, '', 'yes' );
	}
	
	function plugin_deactivation() {
		$options = get_option( 'readable_names' );
		delete_option( 'readable_names' );
		add_option( 'readable_names', $options, '', 'no' );
	}
	
	function plugin_uninstall() {
		delete_option( 'readable_names' );
	}
	
	function options_upgrade( $options_old ) {
		$options_default = $this->options_default();
		$options_old_usable = array_intersect_key( $options_old, $options_default );
		return array_merge( $options_default, $options_old_usable );
	}
	
	function options_field( $field ) {
		$options = get_option( 'readable_names' );
		if ( isset( $options[ $field ] ) )
			return @$options[ $field ];
		else
			return false;
	}
	
	function check_comment_author($comment_post_ID) {
		if ( ( ! $this->options_field( 'check_visitor' ) ) && ( ! is_user_logged_in() ) )
			return;
		
		$result = null;
		
		$comment_author = ( isset( $_POST[ 'author' ] ) ) ? trim( strip_tags( $_POST[ 'author' ] ) ) : null;
	
		if ( empty( $comment_author ) ) 
			return;
		
		$result = $this->check_full_name( $comment_author );
		if ( $result )
			wp_die( $result, __( 'Error: The name is not readable', 'readable_names' ) . ' | ' . get_bloginfo ( 'name' ), 
				array( 'response' => 500, 'back_link' => true ) );
	}

	function check_full_name( $full_name ) {
		if ( empty( $full_name ) )
			return;
		
		$result = null;
		
		$result = $this->check_allowed_characters( $full_name );
		if ( $result )
			return $result;
		
		// eliminate whitespace repetition
		$full_name = preg_replace( '/\s+/', ' ', $full_name );

		// break the full name in single names
		$name_array = explode( ' ', $full_name );
	
		foreach ($name_array as $name) {
			
			if ( ! $result )
				$result = $this->check_name_length( $name );

			if ( ! $result )
				$result = $this->check_first_letter_capital( $name );
			
			if ( ! $result )
				$result = $this->check_one_capital_letter_only( $name );
		}
		return $result;
	}
	
	function check_allowed_characters( $name ) {
		// eliminate whitespace
		$input = preg_replace( '/\s+/', '', $name );
		
		if ( empty ( $input ) )
			return;
		
		$result = null;

		$length = mb_strlen( $input, 'UTF-8' );
		$allowed_characters = 	$this->options_field( 'allowed_small_letters' ) . 
								$this->options_field( 'allowed_capital_letters' ) .
								$this->options_field( 'allowed_digits' );
								
		for ( $i = 0; ( $i < $length ) && ( ! $result ); $i++ ) {
			$letter = mb_substr ( $input, $i, 1, 'UTF-8' );
			
			$position = mb_strpos( $allowed_characters, $letter, 0, 'UTF-8' );
			
			if ( false === $position ) {
				$result = sprintf( __( '<strong>Error:</strong> The name “%1$s” contains an invalid character: “%2$s”. Please only use these characters: “%3$s”.', 'readable_names' ), $name, $letter, $allowed_characters );
			}
		}
								
		return $result;
	}
	
	function check_name_length( $name ) {
		if ( empty ( $name ) )
			return;
		
		// don't check the length, if the name is a number
		if ( ( $this->options_field( 'allowed_digits' ) ) && ( $this->name_is_number( $name ) ) )
			return;
		
		$result = null;
		$length = mb_strlen( $name, 'UTF-8' );

		if ( $length <  $this->options_field( 'minimum_name_length' ) ) {
			$result = sprintf( __( '<strong>Error:</strong> The name “%1$s” is too short. It has to be at least %2$d characters long.', 'readable_names' ), $name, $this->options_field( 'minimum_name_length' ) );
		}

		return $result;
	}
	
	function name_is_number( $name ) {
        if ( ( is_numeric( $name ) === true ) && ( (int)$name == $name ) && ( intval( $name ) >= 0 ) )
        	return true;
        else
        	return false;
	}
	
	function check_first_letter_capital( $name ) {
		if (
			( ! $name ) ||	
			( ! $this->options_field( 'first_letter_capital' ) ) ||
			( ! $this->options_field( 'allowed_capital_letters' ) ) )
		{
			return;
		}
		
		if ( $this->name_is_number( $name ) ) {
			return;
		}
		
		$result = null;

		$first_letter = mb_substr( $name, 0, 1, 'UTF-8' );
		if ( $this->strings_compare_count( $first_letter, $this->options_field( 'allowed_capital_letters' ) ) == 0 ) {
			$result = sprintf( __( '<strong>Error:</strong> The name “%s” does not begin with a capital letter.', 'readable_names' ), $name );
		}
		
		return $result;
	}
	
	function check_one_capital_letter_only( $name ) {
		if (
			( ! $name ) ||	
			( ! $this->options_field( 'allowed_capital_letters' ) ) ||
			( ! $this->options_field( 'one_capital_letter_only' ) ) ) 
		{ 
			return;
		}
		
		$result = null;
		
		if ( $this->strings_compare_count( $name, $this->options_field( 'allowed_capital_letters' ) ) > 1 ) {
			$result = sprintf( __( '<strong>Error:</strong> The name “%s” has to many capital letters.', 'readable_names' ), $name );
		}
		
		return $result;
	}
	
	function check_user_profile( $errors, $update, $user ) {
		if ( ! $this->options_field( 'check_user' ) )
			return;
		
		// don't check the user with 'edit_users' capability
		if ( current_user_can( 'edit_users' ) )
			return;
		
		// first name
		$result = ( $user->first_name ) ? $this->check_full_name( $user->first_name ) : null;
		if ( $result )
			$errors->add( 'first_name', $result, array( 'form-field' => 'first_name' ) );
		
		// last name
		$result = ( $user->last_name ) ? $this->check_full_name( $user->last_name ) : null;
		if ( $result )
			$errors->add( 'last_name', $result, array( 'form-field' => 'last_name' ) );	
		
		// nickname
		$result = ( $user->nickname ) ? $this->check_full_name( $user->nickname ) : null;
		if ( $result )
			$errors->add( 'nickname', $result, array( 'form-field' => 'nickname' ) );	

		// display name
		$result = ( $user->display_name ) ? $this->check_full_name( $user->display_name ) : null;
		if ( $result )
			$errors->add( 'display_name', $result, array( 'form-field' => 'display_name' ) );
	}
	
	function call_add_options_page() {
		add_options_page(
		__( 'Readable Names Settings', 'readable_names' ),
		__( 'Readable Names', 'readable_names' ),
		'manage_options',
		'readable_names',
		array( $this, 'show_options_page' ) );
	}
	
	function show_options_page() {?>
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Readable Names Settings', 'readable_names' ); ?></h2>			
		<form method="post" action="options.php">
			<?php settings_fields( 'readable_names_group' ); ?>
			<?php do_settings_sections( 'readable_names' ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" name="Submit" value="<?php _e( 'Save Changes' ) ?>" />
			</p>
		</form>
		</div>

	<?php }
	
	function register_settings() {
		register_setting( 'readable_names_group', 'readable_names', array( $this, 'options_validate' ) );
		
		// section "Allowed characters" with id="section_allowed_characters"
		add_settings_section( 'section_allowed_characters', __( 'Allowed characters', 'readable_names' ), array( $this, 'admin_section_characters_text' ), 'readable_names' );
		add_settings_field( 'allowed_small_letters',  __( 'Small letters', 'readable_names' ), array( $this, 'admin_allowed_small_letters' ), 'readable_names', 'section_allowed_characters' );
		add_settings_field( 'allowed_capital_letters',  __( 'Capital letters', 'readable_names' ), array( $this, 'admin_allowed_capital_letters' ), 'readable_names', 'section_allowed_characters' );
		add_settings_field( 'allowed_digits',  __( 'Digits', 'readable_names' ), array( $this, 'admin_allowed_digits' ), 'readable_names', 'section_allowed_characters' );
		
		// section "Grammar" with id="section_grammar"
		add_settings_section( 'section_grammar', __( 'Grammar', 'readable_names' ), array( $this, 'admin_section_grammar_text' ), 'readable_names' );
		add_settings_field( 'minimum_name_length',  __( 'Minimum name length', 'readable_names' ), array( $this, 'admin_minimum_name_length' ), 'readable_names', 'section_grammar' );
		add_settings_field( 'first_letter_capital',  __( 'First character must be a capital letter', 'readable_names' ), array( $this, 'admin_first_letter_capital' ), 'readable_names', 'section_grammar' );
		add_settings_field( 'one_capital_letter_only',  __( 'One capital letter only', 'readable_names' ), array( $this, 'admin_one_capital_letter_only' ), 'readable_names', 'section_grammar' );
		
		// section "Affected roles" with id="section_affected_roles"
		add_settings_section( 'section_affected_roles', __( 'Affected roles', 'readable_names' ), array( $this, 'admin_section_affected_roles_text' ), 'readable_names' );
		add_settings_field( 'check_visitor',  __( 'Visitor', 'readable_names' ), array( $this, 'admin_check_visitor' ), 'readable_names', 'section_affected_roles' );
		add_settings_field( 'check_user',  __( 'User', 'readable_names' ), array( $this, 'admin_check_user' ), 'readable_names', 'section_affected_roles' );
	}
	
	function admin_section_characters_text() {
		echo '<p class="description">' . __( 'Whitespace is always allowed.', 'readable_names' ) . '</p>';
	}
	
	function admin_allowed_small_letters() { ?>
		<input
			id="allowed_small_letters"
			name="<?php echo 'readable_names'; ?>[allowed_small_letters]"
			type="text"
			class="regular-text"
			value="<?php echo $this->options_field( 'allowed_small_letters' ) ?>"
		/>
		<span class="description">(<?php echo mb_strlen( $this->options_field( 'allowed_small_letters' ), 'UTF-8' ) ?>)</span>
	<?php }
	
	function admin_allowed_capital_letters() { ?>
		<input
			id="allowed_capital_letters"
			name="<?php echo 'readable_names'; ?>[allowed_capital_letters]"
			type="text"
			class="regular-text"
			value="<?php echo $this->options_field( 'allowed_capital_letters' ) ?>"
		/>
		<span class="description">(<?php echo mb_strlen( $this->options_field( 'allowed_capital_letters' ), 'UTF-8' ) ?>)</span>
	<?php }
	
	function admin_allowed_digits() { ?>
		<input
			id="allowed_digits"
			name="<?php echo 'readable_names'; ?>[allowed_digits]"
			type="text"
			class="regular-text"
			value="<?php echo $this->options_field( 'allowed_digits' ) ?>"
		/>
		<span class="description">(<?php echo mb_strlen( $this->options_field( 'allowed_digits' ), 'UTF-8' ) ?>)</span>
	<?php }

	function admin_section_grammar_text() {
		echo '<p class="description">' . __( 'Depending on allowed characters.', 'readable_names' ) . '</p>';
	}

	function admin_minimum_name_length() { ?>
		<input
			id="minimum_name_length"
			name="<?php echo 'readable_names'; ?>[minimum_name_length]"
			type="text"
			class="small-text"
			value="<?php echo $this->options_field( 'minimum_name_length' ); ?>"
		/>
		<span class="description"><?php _e( 'characters', 'readable_names' ) ?></span>
	<?php }

	function admin_first_letter_capital() { ?>
		<input
			id="first_letter_capital" 
			name="<?php echo 'readable_names'; ?>[first_letter_capital]" 
			type="checkbox"
			value="1" 
			<?php checked( '1', $this->options_field( 'first_letter_capital' ) ) ?>
		/>
	<?php }

	function admin_one_capital_letter_only() { ?>
		<input
			id="one_capital_letter_only" 
			name="<?php echo 'readable_names'; ?>[one_capital_letter_only]" 
			type="checkbox"
			value="1" 
			<?php checked( '1', $this->options_field( 'one_capital_letter_only' ) ) ?>
		/>
	<?php }
	
	function admin_section_affected_roles_text() {
		echo '<p class="description">' . sprintf( __( 'Depending on <a href="%s">discussion settings</a>.', 'readable_names' ), admin_url( 'options-discussion.php' ) ) . '</p>';
	}

	function admin_check_visitor() { ?>
		<input
			id="check_visitor" 
			name="<?php echo 'readable_names'; ?>[check_visitor]" 
			type="checkbox"
			value="1" 
			<?php checked( '1', $this->options_field( 'check_visitor' ) ) ?>
		/>
	<?php }
	
	function admin_check_user() { ?>
		<input
			id="check_user" 
			name="<?php echo 'readable_names'; ?>[check_user]" 
			type="checkbox"
			value="1" 
			<?php checked( '1', $this->options_field( 'check_user' ) ) ?>
		/>
	<?php }
	
	function options_validate($options) {
		$valid_options = $options;

		// validate allowed characters
		$valid_options[ 'allowed_small_letters' ] = $this->admin_validate_input_letters( $valid_options[ 'allowed_small_letters' ] );
		$valid_options[ 'allowed_capital_letters' ] = $this->admin_validate_input_letters( $valid_options[ 'allowed_capital_letters' ] );
		$valid_options[ 'allowed_digits' ] = $this->admin_validate_input_letters( $valid_options[ 'allowed_digits' ] );
		
		// minimum name length must be between 1 and 3
		$valid_options[ 'minimum_name_length' ] = absint( $valid_options[ 'minimum_name_length' ] );
		$valid_options[ 'minimum_name_length' ] = max( 1, min( 3, $valid_options[ 'minimum_name_length' ] ) );
		
		return $valid_options;
	}
	
	function admin_validate_input_letters( $input ) {
		// wordpress sanitize for names
		$input = sanitize_text_field( $input );
		
		// eliminate whitespace
		$input = preg_replace( '/\s+/', '', $input );
		
		// sort
		$length = mb_strlen( $input, 'UTF-8' );
		$letters = array();
		for ($i = 0; ( $i < $length ); $i++) {
			$letters[] = mb_substr ( $input, $i, 1, 'UTF-8' );
		}
		sort ( $letters );

		// eliminate repititions
		$letters = array_unique( $letters );

		$result = implode ( $letters );
	
		return $result;
	}
	
	function strings_compare_count( $s1, $s2 ) {
		$result = 0;
		
		$length = mb_strlen( $s1, 'UTF-8' );		
		
		for ( $i = 0; ( $i < $length ); $i++ ) {
			$letter = mb_substr ( $s1, $i, 1, 'UTF-8' );
			
			$count = mb_substr_count( $s2, $letter, 'UTF-8' );
			
			if ( $count > 0 ) {
				$result += $count;
			}
		}
		return $result;
	}
	
	function options_default() {
		// English
		$options = array(
			'allowed_small_letters' => 'abcdefghijklmnopqrstuvwxyz',
			'allowed_capital_letters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'allowed_digits' => '',
			'minimum_name_length' => 2,
			'first_letter_capital' => true,
			'one_capital_letter_only' => true,
			'check_visitor' => true,
			'check_user' => true
		);
		$locale = get_locale();
		// German
		if ( 'de_DE' == $locale ) {
			$options[ 'allowed_small_letters' ] = 'aäbcdefghijklmnoöpqrsßtuüvwxyz';
			$options[ 'allowed_capital_letters' ] = 'AÄBCDEFGHIJKLMNOÖPQRSTUÜVWXYZ';
		}
		// Hebrew
		elseif ( 'he_IL' == $locale ) {
			$options[ 'allowed_small_letters' ] = 'למנסעפצקרשתםןףץאבגדהוזחטיכך';
			$options[ 'allowed_capital_letters' ] = '';
			$options[ 'first_letter_capital' ] = false;
			$options[ 'one_capital_letter_only' ] = false;
		}
		// Icelandic
		elseif ( 'is_IS' == $locale ) {
			$options[ 'allowed_small_letters' ] = 'aábdðeéfghiíjklmnoóprstuúvxyýþæö';
			$options[ 'allowed_capital_letters' ] = 'AÁBDEÉFGHIÍJKLMNOÓPRSTUÚVXYÝÞÆÖ';
			$options[ 'minimum_name_length' ] = 3;
		}
		// Persian
		elseif ( 'fa_IR' == $locale ) {
			$options[ 'allowed_small_letters' ] = 'اآأأبپتثجچحخدذرزژسشصضطظعغفقکكگلمنوؤهةیيئ';
			$options[ 'allowed_capital_letters' ] = '';
			$options[ 'minimum_name_length' ] = 3;
			$options[ 'first_letter_capital' ] = false;
			$options[ 'one_capital_letter_only' ] = false;
		}
		// Russian
		elseif ( 'ru_RU' == $locale ) {
			$options[ 'allowed_small_letters' ] = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
			$options[ 'allowed_capital_letters' ] = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЫЭЮЯ';
		}
		return $options;
	}
	
	function init_action_links( $links, $file ) {
		if ( plugin_basename( __FILE__ ) == $file ) {
			return array_merge(
				array( sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=readable_names' ), __( 'Settings' ) ) ),
				$links
			);
		}
		return $links;
	}
	
}	
	
$GLOBALS[ 'Readable_Names' ] = new Readable_Names();

?>
