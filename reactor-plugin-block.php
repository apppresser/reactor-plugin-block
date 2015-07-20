<?php
/*
Plugin Name: Reactor Plugin Block
Description: Blocks plugins from loading in Reactor app.
Author: Reactor Team
Version: 1.0
Author URI: http://reactorapps.io
*/

class ReactorPluginBlock {

	// A single instance of this class.
	public static $instance    = null;
	public static $this_plugin = null;
	const PLUGIN               = 'Reactor Plugin Block';
	const VERSION              = '1.0';
	public static $dir_path;
	public static $dir_url;

	/**
	* run function.
	*
	* @access public
	* @static
	* @return void
	*/
	public static function run() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	* __construct function.
	*
	* @access public
	*/
	public function __construct() {
	
		self::$dir_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		self::$dir_url = trailingslashit( plugins_url( null , __FILE__ ) );

		// is main plugin active? If not, throw a notice and deactivate
		if ( ! in_array( 'reactor-core/reactor-core.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action( 'all_admin_notices', array( $this, 'reactor_required' ) );
			return;
		}

		add_filter( 'option_active_plugins', array( $this, 'appp_filter_plugins' ), 5 );
		
	}
	
	
	// remove some plugins
	function appp_filter_plugins( $active = array() ) {
		
		if( 'woo' === $_GET['appp'] || 'gform' === $_GET['appp'] || 'login' === $_GET['appp'] ) {
	
			$exclude = apply_filters( 'appp_exclude_plugins', $active );
	
			foreach ( $exclude as $plugin ) {
				$key = array_search( $plugin, $active );
				if ( false !== $key ) {
					unset( $active[ $key ] );
				}
			}
		}
		
		return $active;
	}


	/**
	* apppresser_required function.
	*
	* @access public
	* @return void
	*/
	public function reactor_required() {
	echo '<div id="message" class="error"><p>'. sprintf( __( '%1$s requires the Reactor Core plugin to be installed/activated. %1$s has been deactivated.', 'appshare' ), self::PLUGIN ) .'</p></div>';
	deactivate_plugins( self::$this_plugin, true );
	}


}
ReactorPluginBlock::run();


function appp_filter_exclude_plugins( $exclude = array() ) {
	/* CONFIGURE PLUGINS TO REMOVE HERE!
	--------------------------------------------------------------------------------------------- */
	// Add the name of the main plugin php file that you want to exclude here, to the array, and return.
	// Below are some example ones. Feel free to delete these and add your own.
		
		$exclude = maybe_unserialize( get_option( 'reactor-plugin-block' ) );

	return $exclude;
}
add_filter( 'appp_exclude_plugins', 'appp_filter_exclude_plugins' );




function rpb_admin_menu() {
    add_options_page( 'Plugin Block', 'Plugin Block', 'manage_options', 'reactor-plugin-block', 'rpb_options_page' );
}
add_action( 'admin_menu', 'rpb_admin_menu' );


function rpb_admin_init() {
    register_setting( 'reactor-plugin-block-group', 'reactor-plugin-block', 'rpb_validate_input' );
    add_settings_section( 'section-one', 'Choose Plugins', 'section_one_callback', 'reactor-plugin-block' );
    add_settings_field( 'field-one', 'Plugins', 'field_one_callback', 'reactor-plugin-block', 'section-one' );
}
add_action( 'admin_init', 'rpb_admin_init' );


function section_one_callback() {
    echo 'Checked plugins will be deactived on mobile.';
}

function field_one_callback() {
    $setting = maybe_unserialize( get_option( 'reactor-plugin-block' ) );
            
    $plugins = get_plugins();
    
    $keep = array(
    	'apppresser-plugin-block/loader.php' => '',
    	'reactor-core/reactor-core.php' => '',
    	'reactor-plugin-block/reactor-plugin-block.php' => ''
    );
    
    $plugins = array_diff_key($plugins, $keep);        
    $array_keys = array_keys( $plugins );
    
	foreach( $array_keys as $key ){	
	
		if( !empty($setting) )
		$checked = ( in_array( $key, $setting ) ) ? $checked = 'checked="checked"' : $checked = '' ;

		echo "<input type='checkbox' $checked name='reactor-plugin-block[]' value='$key' />" . $plugins[$key]['Title'] . '</br>';
	}
        
}

function rpb_validate_input( $input ) {
	return maybe_serialize($input);
}



function rpb_options_page() {
    ?>
    <div class="wrap">
        <h2>Reactor Plugin Block</h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'reactor-plugin-block-group' ); ?>
            <?php do_settings_sections( 'reactor-plugin-block' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}