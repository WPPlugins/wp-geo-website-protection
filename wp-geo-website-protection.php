<?php
/*
Plugin Name: WP GEO Website Protection (by SiteGuarding.com)
Plugin URI: http://www.siteguarding.com/en/website-extensions
Description: Adds more security for your WordPress website. Blocks unwanted traffic, protects backend page. Blocks specific countries and IP addresses.
Version: 1.4
Author: SiteGuarding.com (SafetyBis Ltd.)
Author URI: http://www.siteguarding.com
License: GPLv2
TextDomain: plgsggeo
*/
define('GEO_PLUGIN_VERSION', '1.4');

if (!defined('DIRSEP'))
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
    else define('DIRSEP', '/');
}

//error_reporting(E_ERROR | E_WARNING);
//error_reporting(E_ERROR);


if( !is_admin() ) 
{
    if (defined('WP_DEBUG') && WP_DEBUG === true)
    {
        
    }
    else {
        function plgsggeo_frontend_user_check() 
        {
            if (strpos($_SERVER['SCRIPT_NAME'], '/wp-login.php') !== false)
            {
                // Check backend
                $params = SEO_SG_Protection::Get_Params(array('protection_backend', 'backend_ip_list', 'backend_country_list'));
                if (intval($params['protection_backend']) == 1)
                {
                    $myIP = SEO_SG_Protection::GetMyIP();
                    $myCountryCode = SEO_SG_Protection::GetCountryCode($myIP);
                    
                    if ( !SEO_SG_Protection::Check_if_User_IP_allowed($myIP, $params['backend_ip_list']) )
                    {
                        // Log action
                        $alert_data = array(
                            'time' => time(),
                            'ip' => $myIP,
                            'country_code' => $myCountryCode,
                            'url' => $_SERVER['REQUEST_URI']
                        );
                        SEO_SG_Protection::Save_Block_alert($alert_data);
                        SEO_SG_Protection_HTML::BlockPage($myIP, $myCountryCode);
                    }
                    
                    if ( !SEO_SG_Protection::Check_if_User_allowed($myCountryCode, json_decode($params['backend_country_list'], true)) )
                    {
                        // Log action
                        $alert_data = array(
                            'time' => time(),
                            'ip' => $myIP,
                            'country_code' => $myCountryCode,
                            'url' => $_SERVER['REQUEST_URI']
                        );
                        SEO_SG_Protection::Save_Block_alert($alert_data); 
                        SEO_SG_Protection_HTML::BlockPage($myIP, $myCountryCode);
                    }
                }
            }
            else {
                // Check frontend
                $params = SEO_SG_Protection::Get_Params(array('protection_frontend', 'frontend_ip_list', 'frontend_country_list'));
                if (intval($params['protection_frontend']) == 1)
                {
                    $myIP = SEO_SG_Protection::GetMyIP();
                    $myCountryCode = SEO_SG_Protection::GetCountryCode($myIP);
                    
                    if ( !SEO_SG_Protection::Check_if_User_IP_allowed($myIP, $params['frontend_ip_list']) )
                    {
                        // Log action
                        $alert_data = array(
                            'time' => time(),
                            'ip' => $myIP,
                            'country_code' => $myCountryCode,
                            'url' => $_SERVER['REQUEST_URI']
                        );
                        SEO_SG_Protection::Save_Block_alert($alert_data);
                        SEO_SG_Protection_HTML::BlockPage($myIP, $myCountryCode);
                    }
                    
                    if ( !SEO_SG_Protection::Check_if_User_allowed($myCountryCode, json_decode($params['frontend_country_list'], true)) )
                    {
                        // Log action
                        $alert_data = array(
                            'time' => time(),
                            'ip' => $myIP,
                            'country_code' => $myCountryCode,
                            'url' => $_SERVER['REQUEST_URI']
                        );
                        SEO_SG_Protection::Save_Block_alert($alert_data);
                        SEO_SG_Protection_HTML::BlockPage($myIP, $myCountryCode);
                    }
                }
            }
        }
        add_action( 'init', 'plgsggeo_frontend_user_check' );
    }
    
	// Show Protected by
	function plgsggeo_footer_protectedby() 
	{
        if (strlen($_SERVER['REQUEST_URI']) < 5)
        {
            $avp_path = dirname( str_replace('wp-geo-website-protection', 'wp-antivirus-site-protection', dirname(__FILE__)) );
            $avp_membership_file = $avp_path.DIRSEP.'tmp'.DIRSEP.'membership.log';
            if (!file_exists($avp_membership_file))
            {
                $params = SEO_SG_Protection::Get_Params(array('protection_by', 'installation_date'));
                if (!SEO_SG_Protection::CheckIfPRO()) $params['protection_by'] = 1;
                
                $new_date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")));
        		if ( !isset($params['protection_by']) || intval($params['protection_by']) == 1 && $new_date >= $params['installation_date'] )
        		{
        		      $links = array(
                        'https://www.siteguarding.com/en/',
                        'https://www.siteguarding.com/en/website-antivirus',
                        'https://www.siteguarding.com/en/protect-your-website',
                        'https://www.siteguarding.com/en/services/malware-removal-service'
                      );
                      $link = $links[ mt_rand(0, count($links)-1) ];
        			?>
        				<div style="font-size:10px; padding:0 2px;position: fixed;bottom:0;right:0;z-index:1000;text-align:center;background-color:#F1F1F1;color:#222;opacity:0.8;">Protected with <a style="color:#4B9307" href="<?php echo $link; ?>" target="_blank" title="Website Security services. Website Malware removal. Website Antivirus protection.">GEO protection plugin</a></div>
        			<?php
        		}
            }
        }	
	}
	add_action('wp_footer', 'plgsggeo_footer_protectedby', 100);
    
    

}




if( is_admin() ) {
	
	//error_reporting(0);
    
	function geoprotection_admin_notice() 
	{
        if (defined('WP_DEBUG') && WP_DEBUG === true)
        {
        	$class = 'notice notice-error';
        	$message = 'DEBUG mode is enabled. GEO Protection is disabled. To enable GEO Protection please edit wp-config.php and set WP_DEBUG = false. If you still need help, please contact with <a href="https://www.siteguarding.com/en/contacts" target="_black">SiteGuarding.com support</a>';
        
        	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
        }
	}
	add_action( 'admin_notices', 'geoprotection_admin_notice' );
    
    
	function register_plgsggeo_page() 
	{
		add_menu_page('plgsggeo_protection', 'GEO Protection', 'activate_plugins', 'plgsggeo_protection', 'register_plgsggeo_page_callback', plugins_url('images/', __FILE__).'geo-protection-logo.png');
	}
    add_action('admin_menu', 'register_plgsggeo_page');
    

	function register_plgsggeo_page_callback() 
	{
	    $action = '';
        if (isset($_REQUEST['action'])) $action = sanitize_text_field(trim($_REQUEST['action']));
        
        // Actions
        if ($action != '')
        {
            $action_message = '';
            switch ($action)
            {
                case 'Load_GEO_to_SQL':
                    SEO_SG_Protection::Add_IP_adresses(true);
                    break;
                
                // Front-end    
                case 'EnableDisable_frontend_protection':
                    if (check_admin_referer( 'name_2Jjf73gds8d' ))
                    {
                        $params = SEO_SG_Protection::Get_Params(array('protection_frontend'));
                        SEO_SG_Protection::Set_Params(array('protection_frontend' => round(1 - $params['protection_frontend']) ));
                    }
                    break;
                    
                case 'Save_frontend_params':
                    if (check_admin_referer( 'name_3dfUejeked' ))
                    {
                        $data = array();
                        if (isset($_POST['frontend_ip_list'])) $data['frontend_ip_list'] = sanitize_text_field($_POST['frontend_ip_list']);
                        if (isset($_POST['country_list'])) $data['frontend_country_list'] = $_POST['country_list'];
                        else $data['frontend_country_list'] = array();
                        
                        if (!SEO_SG_Protection::CheckIfPRO() && count($data['frontend_country_list']) > 15)
                        {
                            $data['frontend_country_list'] = array_slice($data['frontend_country_list'], 0, 15);
                            
                            $message_data = array(
                                'type' => 'info',
                                'header' => 'Free version limits',
                                'message' => 'Limit is 15 countries. Please upgrade.<br><b>For all websites with our <a href="https://www.siteguarding.com/en/antivirus-site-protection" target="_blank">PRO Antivirus plugin</a>, we provide with free license.</b>',
                                'button_text' => 'Upgrade',
                                'button_url' => 'https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection?domain='.urlencode( get_site_url() ),
                                'help_text' => ''
                            );
                            echo '<div style="max-width:800px;margin-top: 10px;">';
                            SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                            echo '</div>';
                        }
                        
                        $data['frontend_country_list'] = json_encode($data['frontend_country_list']);
                        
                        $action_message = 'Front-end settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);
                    }
                    break;
                
                // Backend    
                case 'EnableDisable_backend_protection':
                    if (check_admin_referer( 'name_2Jjf73gds8d' ))
                    {
                        $params = SEO_SG_Protection::Get_Params(array('protection_backend'));
                        SEO_SG_Protection::Set_Params(array('protection_backend' => round(1 - $params['protection_backend']) ));
                    }
                    break;
                    
                case 'Save_backend_params':
                    if (check_admin_referer( 'name_3dfUejeked' ))
                    {
                        $data = array();
                        if (isset($_POST['backend_ip_list'])) $data['backend_ip_list'] = sanitize_text_field($_POST['backend_ip_list']);
                        if (isset($_POST['country_list'])) $data['backend_country_list'] = $_POST['country_list'];
                        else $data['backend_country_list'] = array();
                        
                        if (!SEO_SG_Protection::CheckIfPRO() && count($data['backend_country_list']) > 15)
                        {
                            $data['backend_country_list'] = array_slice($data['backend_country_list'], 0, 15);
                            
                            $message_data = array(
                                'type' => 'info',
                                'header' => 'Free version limits',
                                'message' => 'Limit is 15 countries. Please upgrade.<br><b>For all websites with our <a href="https://www.siteguarding.com/en/antivirus-site-protection" target="_blank">PRO Antivirus plugin</a>, we provide with free license.</b>',
                                'button_text' => 'Upgrade',
                                'button_url' => 'https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection?domain='.urlencode( get_site_url() ),
                                'help_text' => ''
                            );
                            echo '<div style="max-width:800px;margin-top: 10px;">';
                            SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                            echo '</div>';
                        }
                        
                        $data['backend_country_list'] = json_encode($data['backend_country_list']);
                        
                        $action_message = 'Backend settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);
                    }
                    break;
                    
                case 'Save_Settings':
                    if (check_admin_referer( 'name_xZU32INTzZM1GFNz' ))
                    {
                        $data = array();
                        if (isset($_POST['registration_code'])) $data['registration_code'] = sanitize_text_field($_POST['registration_code']);
                        if (isset($_POST['protection_by'])) $data['protection_by'] = intval($_POST['protection_by']);
                        else $data['protection_by'] = 0;
                        if (!SEO_SG_Protection::CheckIfPRO()) $data['protection_by'] = 1;
                        
                        $action_message = 'Settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);
                    }
                    break;
            }
            
            if ($action_message != '')
            {
                $message_data = array(
                    'type' => 'info',
                    'header' => '',
                    'message' => $action_message,
                    'button_text' => '',
                    'button_url' => '',
                    'help_text' => ''
                );
                echo '<div style="max-width:800px;margin-top: 10px;">';
                SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                echo '</div>';
            }
        }
        
        
        
        
        wp_enqueue_style( 'plgsggeo_LoadStyle' );
        
        $geo_db_array = array();
        foreach (glob(dirname(__FILE__).DIRSEP."geo_base_*.db") as $filename) 
        {
            $geo_db_array[] = $filename;
        }
        
        if (count($geo_db_array) > 0)
        {
            SEO_SG_Protection_HTML::Load_GEO_to_SQL();
        }
        else SEO_SG_Protection_HTML::PluginPage();
    }
	
    
	function plgsggeo_activation()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgsggeo_config';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `var_name` char(255) CHARACTER SET utf8 NOT NULL,
                `var_value` LONGTEXT CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
            
            SEO_SG_Protection::Set_Params( array('installation_date' => date("Y-m-d")) );
		}
        
		$table_name = $wpdb->prefix . 'plgsggeo_ip';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `ip_from` bigint(11) NOT NULL,
              `ip_till` bigint(11) NOT NULL,
              `country_code` char(2) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `ip_from` (`ip_from`,`ip_till`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
		}
        
		$table_name = $wpdb->prefix . 'plgsggeo_stats';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `time` int(11) NOT NULL,
              `ip` varchar(15) NOT NULL,
              `country_code` varchar(2) NOT NULL,
              `url` varchar(128) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
		}
        


	}
	register_activation_hook( __FILE__, 'plgsggeo_activation' );
    
    
	function plgsggeo_uninstall()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgsggeo_config';
		$wpdb->query( 'DROP TABLE ' . $table_name );
        
		$table_name = $wpdb->prefix . 'plgsggeo_ip';
		$wpdb->query( 'DROP TABLE ' . $table_name );
        
		$table_name = $wpdb->prefix . 'plgsggeo_stats';
		$wpdb->query( 'DROP TABLE ' . $table_name );
		
	}
	register_uninstall_hook( __FILE__, 'plgsggeo_uninstall' );
	
	
    
	add_action( 'admin_init', 'plgsggeo_admin_init' );
	function plgsggeo_admin_init()
	{
		wp_register_style( 'plgsggeo_LoadStyle', plugins_url('css/wp-geo-website-protection.css', __FILE__) );	
        wp_register_script( 'plgsggeo_LoadCharts', plugins_url('js/highcharts.js', __FILE__) , '', '', true );
	}




}






/**
 * Functions
 */


class SEO_SG_Protection_HTML
{
	public static function Load_GEO_to_SQL()
    {
        $params = SEO_SG_Protection::Get_Params( array('geo_update_progress') );
        ?>
        <div class="ui grid max-box">
        <div class="row">
            <script type="text/javascript">
            window.setTimeout(function(){ document.location.reload(true); }, 60000);
            </script>
            <p style="text-align: center; width: 100%;">
                <img width="120" height="120" src="<?php echo plugins_url('images/ajax_loader.svg', __FILE__); ?>" />
                <br /><br />
                We are updating GEO database.<br>
                Please wait, it will take approximately 2-3 minutes.
            </p>
            <?php 
            if (intval($params['geo_update_progress']) == 0) {
            ?>
                <iframe src="admin.php?page=plgsggeo_protection&action=Load_GEO_to_SQL" style="height:1px;width:1px;"></iframe>
            <?php 
            } 
            ?>
        </div>
        </div>
        <?php
    }
    

    
    
    
    public static function PluginPage()
    {
        $params = SEO_SG_Protection::Get_Params();
        $params['frontend_country_list'] = json_decode($params['frontend_country_list'], true);
        $params['backend_country_list'] = json_decode($params['backend_country_list'], true);
        //print_r($params);
        
        $myIP = SEO_SG_Protection::GetMyIP();
        $myCountryCode = SEO_SG_Protection::GetCountryCode($myIP);
        $myCountry = SEO_SG_Protection::$country_list[$myCountryCode];
        
        
        $tab_id = intval($_GET['tab']);
        $tab_array = array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '' );
        $tab_array[$tab_id] = 'active ';
           ?>
    <script>
    function InfoBlock(id)
    {
        jQuery("#"+id).toggle();
    }
    function SelectCountries(select, uncheck)
    {
        if (select != '') jQuery(select).prop( "checked", true );
        
        if (uncheck != '') jQuery(uncheck).prop( "checked", false );
    }
    </script>
    
    <h3 class="ui header title_product"><i class="world icon"></i>GEO Website Protection (<a href="https://www.siteguarding.com/en/wordpress-geo-website-protection" target="_blank">ver. <?php echo GEO_PLUGIN_VERSION; ?></a>)</h3>
    
    <div class="ui grid max-box">
    <div class="row">
    
    <?php
    
    if (!SEO_SG_Protection::CheckAntivirusInstallation()) 
    {
        $action = 'install-plugin';
        $slug = 'wp-antivirus-site-protection';
        $install_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => $action,
                    'plugin' => $slug
                ),
                admin_url( 'update.php' )
            ),
            $action.'_'.$slug
        );
    ?>
        <a class="ui yellow label" style="text-decoration: none;" href="<?php echo $install_url; ?>">Antivirus is not installed. Try our antivirus to keep your website secured. Click here to open the details.</a>
    <?php
    }
    ?>
    
    <div class="ui top attached tabular menu" style="margin-top:0;">
            <a href="admin.php?page=plgsggeo_protection&tab=0" class="<?php echo $tab_array[0]; ?> item"><i class="desktop icon"></i> Front-end Protection</a>
            <a href="admin.php?page=plgsggeo_protection&tab=1" class="<?php echo $tab_array[1]; ?> item"><i class="lock icon"></i> Backend Protection</a>
            <a href="admin.php?page=plgsggeo_protection&tab=2" class="<?php echo $tab_array[2]; ?> item"><i class="pie chart icon"></i> Logs</a>
            <a href="admin.php?page=plgsggeo_protection&tab=3" class="<?php echo $tab_array[3]; ?> item"><i class="settings icon"></i> Settings & Support</a>
    </div>
    <div class="ui bottom attached segment">
    <?php
    if ($tab_id == 0)
    {
        ?>
        <h4 class="ui header">Front-end protection</h4>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=0">
        
        <p>
        <?php
        if (intval($params['protection_frontend']) == 1) { $block_class = ''; $protection_txt = '<span class="ui green horizontal label">Enabled</span>'; $protection_bttn_txt = 'Disable Protection'; }
        else { $block_class = 'class="hide"'; $protection_txt = '<span class="ui red horizontal label">Disabled</span>'; $protection_bttn_txt = 'Enable Protection'; }
        ?>
        GEO Protection for front-end is <?php echo $protection_txt; ?> Visitors from selected countried and selected IP addresses will not be able to visit your website.
        </p>
        <input type="submit" name="submit" id="submit" class="mini ui green button" value="<?php echo $protection_bttn_txt; ?>">
        <p>&nbsp;</p>
		<?php
		wp_nonce_field( 'name_2Jjf73gds8d' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="EnableDisable_frontend_protection"/>
		</form>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=0">
        <div <?php echo $block_class; ?>>
        
            <h4 class="ui header">Block by IP or range (your IP is <?php echo $myIP; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or or 200.150.*.*
            </div>
            
            <div class="ui input" style="width: 100%;margin-bottom:10px">
                <textarea name="frontend_ip_list" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to block, one by line"><?php echo $params['frontend_ip_list']; ?></textarea>
            </div>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            <h4 class="ui header">Block by country (your country is <?php echo $myCountry; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>Quick buttons: <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_<?php echo $myCountryCode; ?>');">Select All (exclude <?php echo $myCountryCode; ?>)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('', '.all');">Uncheck All</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_US,.country_CA');">Select All (exclude USA, Canada)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.europe');">Select All (exclude EU countries)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.3rdcountry', '');">Select All 3rd party countried</a>
            </div>
            
            <?php echo self::CountryList_checkboxes($params['frontend_country_list']); ?>
            
            
            <p>&nbsp;</p>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
        </div>
        
		<?php
		wp_nonce_field( 'name_3dfUejeked' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="Save_frontend_params"/>
		</form>
        <?php
    }
    
    
    
    
    if ($tab_id == 1)
    {
        ?>
        <h4 class="ui header">Backend protection</h4>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=1">
        
        <p>
        <?php
        if (intval($params['protection_backend']) == 1) { $block_class = ''; $protection_txt = '<span class="ui green horizontal label">Enabled</span>'; $protection_bttn_txt = 'Disable Protection'; }
        else { $block_class = 'class="hide"'; $protection_txt = '<span class="ui red horizontal label">Disabled</span>'; $protection_bttn_txt = 'Enable Protection'; }
        ?>
        GEO Protection for backend is <?php echo $protection_txt; ?> Visitors from selected countried and selected IP addresses will not be able to login to backend of your website.
        </p>
        <input type="submit" name="submit" id="submit" class="mini ui green button" value="<?php echo $protection_bttn_txt; ?>">
        <p>&nbsp;</p>
		<?php
		wp_nonce_field( 'name_2Jjf73gds8d' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="EnableDisable_backend_protection"/>
		</form>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=1">
        <div <?php echo $block_class; ?>>
        
            <h4 class="ui header">Block by IP or range (your IP is <?php echo $myIP; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or or 200.150.*.*
            </div>
            
            <div class="ui input" style="width: 100%;margin-bottom:10px">
                <textarea name="backend_ip_list" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to block, one by line"><?php echo $params['backend_ip_list']; ?></textarea>
            </div>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            <h4 class="ui header">Block by country (your country is <?php echo $myCountry; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>Quick buttons: <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_<?php echo $myCountryCode; ?>');">Select All (exclude <?php echo $myCountryCode; ?>)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('', '.all');">Uncheck All</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_US,.country_CA');">Select All (exclude USA, Canada)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.europe');">Select All (exclude EU countries)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.3rdcountry', '');">Select All 3rd party countried</a>
            </div>
            
            <?php echo self::CountryList_checkboxes($params['backend_country_list']); ?>
            
            
            <p>&nbsp;</p>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
        </div>
        
		<?php
		wp_nonce_field( 'name_3dfUejeked' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="Save_backend_params"/>
		</form>
        <?php
    }
    
    
    

    if ($tab_id == 2)
    {
        wp_enqueue_script( 'plgsggeo_LoadCharts' );
        
        ?>
        <h4 class="ui header">Charts</h4>
        
       
        <?php
        $pie_array = SEO_SG_Protection::GeneratePieData(1);
        $pie_data = SEO_SG_Protection::PreparePieData($pie_array);
        ?>
		<script type="text/javascript">
        jQuery(function () {
            jQuery('#pie_container_1').highcharts({
                credits: false,
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Blocked activity for the last 24 hours'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [<?php echo implode(", ", $pie_data); ?>]
                }]
            });
        });
        		</script>
        	</head>
        	<body>
        
        <div id="pie_container_1" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
        
        <hr />

        <?php
        $pie_array = SEO_SG_Protection::GeneratePieData(7);
        $pie_data = SEO_SG_Protection::PreparePieData($pie_array);
        ?>
		<script type="text/javascript">
        jQuery(function () {
            jQuery('#pie_container_2').highcharts({
                credits: false,
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Blocked activity for the last 7 days'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [<?php echo implode(", ", $pie_data); ?>]
                }]
            });
        });
        		</script>
        	</head>
        	<body>
        
        <div id="pie_container_2" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
        
        <hr />
        
        <?php
        $pie_array = SEO_SG_Protection::GeneratePieData(30);
        $pie_data = SEO_SG_Protection::PreparePieData($pie_array);
        ?>
		<script type="text/javascript">
        jQuery(function () {
            jQuery('#pie_container_3').highcharts({
                credits: false,
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Blocked activity for the last 30 days'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [<?php echo implode(", ", $pie_data); ?>]
                }]
            });
        });
        		</script>
        	</head>
        	<body>
        
        <div id="pie_container_3" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
        
        <hr />
        
        <?php
        $amount_records = 50;
        $latest_records_array = SEO_SG_Protection::GetLatestRecords($amount_records);
        ?>
        <h4 class="ui header">Latest Logs (latest <?php echo $amount_records; ?> records)</h4>
        <?php
        if (count($latest_records_array) == 0) echo '<p>No records</p>';
        else {
            ?>
            <table class="ui celled table small">
              <thead>
                <tr><th>Date</th>
                <th>Country</th>
                <th>IP address</th>
                <th>URL</th>
              </tr></thead>
              <tbody>
                <?php
                foreach ($latest_records_array as $v) {
                ?>
                <tr>
                  <td><?php echo date("Y-m-d H:i:s", $v->time); ?></td>
                  <td><?php echo SEO_SG_Protection::$country_list[ $v->country_code ].' ['.$v->country_code.']'; ?></td>
                  <td><?php echo $v->ip; ?></td>
                  <td class="tbl_urlrow"><a target="_blank" href="<?php echo $v->url; ?>"><?php echo $v->url; ?></span></td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
            
            <?php
        }
    }
    
    
    if ($tab_id == 3)
    {  
        $isPRO = SEO_SG_Protection::CheckIfPRO();
        if (!$isPRO) $params['protection_by'] = 1;
        
        if ($isPRO)
        {
            $box_text = 'You have <b>PRO version</b>';
        }
        else {
            $box_text = 'You have <b>Free version</b>. Please note free version has some limits. Please <a href="https://www.siteguarding.com/en/wordpress-geo-website-protection" target="_blank">Upgrade</a><br><i class="thumbs up icon"></i>Try our <a href="https://wordpress.org/plugins/wp-antivirus-site-protection/" target="_blank">WordPress Antivirus scanner</a> PRO version and get your registration code for GEO protection plugin for free.';
        }
        ?>
        <h4 class="ui header">Settings</h4>
        
        <div class="ui ignored info mini message"><?php echo $box_text; ?></div>
        
        <form method="post" class="ui form" action="admin.php?page=plgsggeo_protection&tab=3">
        
        <div class="ui fluid form">
        
            <div class="ui input ui-form-row">
              <input class="ui input" placeholder="Enter your registration code" type="text" name="registration_code" value="<?php echo $params['registration_code']; ?>">
            </div>
            
          <div class="ui checkbox ui-form-row">
            <input type="checkbox" name="protection_by" value="1" <?php if (!$isPRO) echo 'disabled="disabled"'; ?> <?php if ($params['protection_by'] == 1) echo 'checked="checked"'; ?>>
            <label>Enable 'Protected by' sign</label>
          </div>
        </div>
                
        <input type="submit" name="submit" id="submit" class="mini ui green button" value="Save Settings">
        <p>&nbsp;</p>
		<?php
		wp_nonce_field( 'name_xZU32INTzZM1GFNz' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="Save_Settings"/>
		</form>



      
        
        
        <hr />

        <h4 class="ui header">Support</h4>
        
		<p>
		For more information and details about GEO Website Protection please <a target="_blank" href="https://www.siteguarding.com/en/wordpress-geo-website-protection">click here</a>.<br /><br />
		<a href="http://www.siteguarding.com/livechat/index.html" target="_blank">
			<img src="<?php echo plugins_url('images/livechat.png', __FILE__); ?>"/>
		</a><br />
		For any questions and support please use LiveChat or this <a href="https://www.siteguarding.com/en/contacts" rel="nofollow" target="_blank" title="SiteGuarding.com - Website Security. Professional security services against hacker activity. Daily website file scanning and file changes monitoring. Malware detecting and removal.">contact form</a>.<br>
		<br>
		<a href="https://www.siteguarding.com/" target="_blank">SiteGuarding.com</a> - Website Security. Professional security services against hacker activity.<br />
		</p>
		<?php
        
        
    }
    


    ?>
    
    </div>
           
        
    </div>
    </div>	
    
    		<?php

    }
    
    
    public static function CountryList_checkboxes($selected_array = array())
    {
        $selected = array();
        if (count($selected_array))
        {
            foreach ($selected_array as $v)
            {
                $selected[$v] = $v;
            }
            
        }
        $a = '<div class="ui five column grid country_list">'."\n";

        foreach (SEO_SG_Protection::$country_list as $country_code => $country_name)
        {
            if (isset($selected[$country_code])) $checked = 'checked="checked"';
            else $checked = '';
            $a .= '<div class="column"><label><input class="country_'.$country_code.' '.SEO_SG_Protection::$country_type_list[$country_code].'" '.$checked.' type="checkbox" name="country_list[]" value="'.$country_code.'">'.$country_name.'</label></div>'."\n";
        }

        $a .= '</div>';
        
        return $a;
    }
    
    
    
    
    public static function PrintIconMessage($data)
    {
        $rand_id = "id_".rand(1,10000).'_'.rand(1,10000);
        if ($data['type'] == '' || $data['type'] == 'alert') {$type_message = 'negative'; $icon = 'warning sign';}
        if ($data['type'] == 'ok') {$type_message = 'green'; $icon = 'checkmark box';}
        if ($data['type'] == 'info') {$type_message = 'yellow'; $icon = 'info';}
        ?>
        <div class="ui icon <?php echo $type_message; ?> message">
            <i class="<?php echo $icon; ?> icon"></i>
            <div class="msg_block_row">
                <?php
                if ($data['button_text'] != '' || $data['help_text'] != '') {
                ?>
                <div class="msg_block_txt">
                    <?php
                    if ($data['header'] != '') {
                    ?>
                    <div class="header"><?php echo $data['header']; ?></div>
                    <?php
                    }
                    ?>
                    <?php
                    if ($data['message'] != '') {
                    ?>
                    <p><?php echo $data['message']; ?></p>
                    <?php
                    }
                    ?>
                </div>
                <div class="msg_block_btn">
                    <?php
                    if ($data['help_text'] != '') {
                    ?>
                    <a class="link_info" href="javascript:;" onclick="InfoBlock('<?php echo $rand_id; ?>');"><i class="help circle icon"></i></a>
                    <?php
                    }
                    ?>
                    <?php
                    if ($data['button_text'] != '') {
                        if (!isset($data['button_url_target']) || $data['button_url_target'] == true) $new_window = 'target="_blank"';
                        else $new_window = '';
                    ?>
                    <a class="mini ui green button" <?php echo $new_window; ?> href="<?php echo $data['button_url']; ?>"><?php echo $data['button_text']; ?></a>
                    <?php
                    }
                    ?>
                </div>
                    <?php
                    if ($data['help_text'] != '') {
                    ?>
                        <div style="clear: both;"></div>
                        <div id="<?php echo $rand_id; ?>" style="display: none;">
                            <div class="ui divider"></div>
                            <p><?php echo $data['help_text']; ?></p>
                        </div>
                    <?php
                    }
                    ?>
                <?php
                } else {
                ?>
                    <?php
                    if ($data['header'] != '') {
                    ?>
                    <div class="header"><?php echo $data['header']; ?></div>
                    <?php
                    }
                    ?>
                    <?php
                    if ($data['message'] != '') {
                    ?>
                    <p><?php echo $data['message']; ?></p>
                    <?php
                    }
                    ?>
                <?php
                }
                ?>
            </div> 
        </div>
        <?php
    }
    
    
    public static function BlockPage($myIP, $myCountryCode = '')
    {
        ?><html><head>
        <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('images/logo_siteguarding.svg', __FILE__); ?>">
        </head>
        <body>
        <div style="margin:100px auto; max-width: 500px;text-align: center;">
            <p><img src="<?php echo plugins_url('images/logo_siteguarding.svg', __FILE__); ?>"/></p>
            <p>&nbsp;</p>
            <h3 style="color: #de0027; text-align: center;">Access is not allowed from your IP or your country.</h3>
            <p>If you think it's a mistake, please contact with the websmater of the website.</p>
            <p>If you the owner of the website. Please enable DEBUG mode in your WordPress (use FTP) to disable GEO Protection.<br>
            Read more about it on <a target="_blank" href="https://codex.wordpress.org/Debugging_in_WordPress">Debugging in WordPress</a> or contact with <a target="_blank" href="https://www.siteguarding.com/en/contacts">SiteGuarding.com support</a></p>
            <h4>Session details:</h4>
            <p>IP: <?php echo $myIP; ?></p>
            <?php
            if ($myCountryCode != '') echo '<p>Country: '.SEO_SG_Protection::$country_list[$myCountryCode].'</p>';
            ?>
            <p>&nbsp;</p>
            <p>&nbsp;</p>

            <p style="font-size: 70%;">Powered by <a target="_blank" href="https://www.siteguarding.com/">SiteGuarding.com</a></p>


        </div>
        </body></html>
        <?php

        die();
    }
    
}


class SEO_SG_Protection
{
    public static $country_list = array(
        "AF" => "Afghanistan",   // Afghanistan
        "AL" => "Albania",   // Albania
        "DZ" => "Algeria",   // Algeria
        "AS" => "American Samoa",   // American Samoa
        "AD" => "Andorra",   // Andorra 
        "AO" => "Angola",   // Angola
        "AI" => "Anguilla",   // Anguilla
        "AQ" => "Antarctica",   // Antarctica
        "AG" => "Antigua and Barbuda",   // Antigua and Barbuda
        "AR" => "Argentina",   // Argentina
        "AM" => "Armenia",   // Armenia
        "AW" => "Aruba",   // Aruba 
        "AU" => "Australia",   // Australia 
        "AT" => "Austria",   // Austria
        "AZ" => "Azerbaijan",   // Azerbaijan
        "BS" => "Bahamas",   // Bahamas
        "BH" => "Bahrain",   // Bahrain 
        "BD" => "Bangladesh",   // Bangladesh
        "BB" => "Barbados",   // Barbados 
        "BY" => "Belarus",   // Belarus 
        "BE" => "Belgium",   // Belgium
        "BZ" => "Belize",   // Belize
        "BJ" => "Benin",   // Benin
        "BM" => "Bermuda",   // Bermuda
        "BT" => "Bhutan",   // Bhutan
        "BO" => "Bolivia",   // Bolivia
        "BA" => "Bosnia and Herzegovina",   // Bosnia and Herzegovina
        "BW" => "Botswana",   // Botswana
        "BV" => "Bouvet Island",   // Bouvet Island
        "BR" => "Brazil",   // Brazil
        "IO" => "British Indian Ocean Territory",   // British Indian Ocean Territory
        "VG" => "British Virgin Islands",   // British Virgin Islands,
        "BN" => "Brunei Darussalam",   // Brunei Darussalam
        "BG" => "Bulgaria",   // Bulgaria
        "BF" => "Burkina Faso",   // Burkina Faso
        "BI" => "Burundi",   // Burundi
        "KH" => "Cambodia",   // Cambodia 
        "CM" => "Cameroon",   // Cameroon
        "CA" => "Canada",   // Canada 
        "CV" => "Cape Verde",   // Cape Verde
        "KY" => "Cayman Islands",   // Cayman Islands
        "CF" => "Central African Republic",   // Central African Republic
        "TD" => "Chad",   // Chad
        "CL" => "Chile",   // Chile
        "CN" => "China",   // China
        "CX" => "Christmas Island",   // Christmas Island
        "CC" => "Cocos (Keeling Islands)",   // Cocos (Keeling Islands)
        "CO" => "Colombia",   // Colombia
        "KM" => "Comoros",   // Comoros
        "CG" => "Congo",   // Congo 
        "CK" => "Cook Islands",   // Cook Islands
        "CR" => "Costa Rica",   // Costa Rica 
        "HR" => "Croatia (Hrvatska)",   // Croatia (Hrvatska
        "CY" => "Cyprus",   // Cyprus
        "CZ" => "Czech Republic",   // Czech Republic
        "CG" => "Democratic Republic of Congo",   // Democratic Republic of Congo,
        "DK" => "Denmark",   // Denmark
        "DJ" => "Djibouti",   // Djibouti
        "DM" => "Dominica",   // Dominica
        "DO" => "Dominican Republic",   // Dominican Republic
        "TP" => "East Timor",   // East Timor
        "EC" => "Ecuador",   // Ecuador
        "EG" => "Egypt",   // Egypt 
        "SV" => "El Salvador",   // El Salvador 
        "GQ" => "Equatorial Guinea",   // Equatorial Guinea
        "ER" => "Eritrea",   // Eritrea 
        "EE" => "Estonia",   // Estonia 
        "ET" => "Ethiopia",   // Ethiopia
        "FK" => "Falkland Islands (Malvinas)",   // Falkland Islands (Malvinas)
        "FO" => "Faroe Islands",   // Faroe Islands 
        "FM" => "Federated States of Micronesia",   // Federated States of Micronesia,
        "FJ" => "Fiji",   // Fiji
        "FI" => "Finland",   // Finland
        "FR" => "France",   // France
        "GF" => "French Guiana",   // French Guiana
        "PF" => "French Polynesia",   // French Polynesia
        "TF" => "French Southern Territories",   // French Southern Territories
        "GA" => "Gabon",   // Gabon
        "GM" => "Gambia",   // Gambia
        "GE" => "Georgia",   // Georgia
        "DE" => "Germany",   // Germany
        "GH" => "Ghana",   // Ghana
        "GI" => "Gibraltar",   // Gibraltar
        "GR" => "Greece",   // Greece
        "GL" => "Greenland",   // Greenland
        "GD" => "Grenada",   // Grenada 
        "GP" => "Guadeloupe",   // Guadeloupe
        "GU" => "Guam",   // Guam 
        "GT" => "Guatemala",   // Guatemala
        "GN" => "Guinea",   // Guinea
        "GW" => "Guinea-Bissau",   // Guinea-Bissau
        "GY" => "Guyana",   // Guyana
        "HT" => "Haiti",   // Haiti
        "HM" => "Heard and McDonald Islands",   // Heard and McDonald Islands
        "HN" => "Honduras",   // Honduras
        "HK" => "Hong Kong",   // Hong Kong
        "HU" => "Hungary",   // Hungary
        "IS" => "Iceland",   // Iceland
        "IN" => "India",   // India
        "ID" => "Indonesia",   // Indonesia
        "IR" => "Iran",   // Iran
        "IQ" => "Iraq",   // Iraq
        "IE" => "Ireland",   // Ireland
        "IL" => "Israel",   // Israel
        "IT" => "Italy",   // Italy
        "CI" => "Ivory Coast",   // Ivory Coast,
        "JM" => "Jamaica",   // Jamaica
        "JP" => "Japan",   // Japan 
        "JO" => "Jordan",   // Jordan 
        "KZ" => "Kazakhstan",   // Kazakhstan
        "KE" => "Kenya",   // Kenya 
        "KI" => "Kiribati",   // Kiribati 
        "KW" => "Kuwait",   // Kuwait
        "KG" => "Kuwait",   // Kyrgyzstan
        "LA" => "Laos",   // Laos
        "LV" => "Latvia",   // Latvia
        "LB" => "Lebanon",   // Lebanon
        "LS" => "Lesotho",   // Lesotho
        "LR" => "Liberia",   // Liberia 
        "LY" => "Libya",   // Libya
        "LI" => "Liechtenstein",   // Liechtenstein
        "LT" => "Lithuania",   // Lithuania
        "LU" => "Luxembourg",   // Luxembourg 
        "MO" => "Macau",   // Macau
        "MK" => "Macedonia",   // Macedonia
        "MG" => "Madagascar",   // Madagascar
        "MW" => "Malawi",   // Malawi
        "MY" => "Malaysia",   // Malaysia
        "MV" => "Maldives",   // Maldives
        "ML" => "Mali",   // Mali
        "MT" => "Malta",   // Malta
        "MH" => "Marshall Islands",   // Marshall Islands
        "MQ" => "Martinique",   // Martinique
        "MR" => "Mauritania",   // Mauritania
        "MU" => "Mauritius",   // Mauritius
        "YT" => "Mayotte",   // Mayotte
        "MX" => "Mexico",   // Mexico
        "MD" => "Moldova",   // Moldova
        "MC" => "Monaco",   // Monaco
        "MN" => "Mongolia",   // Mongolia
        "MS" => "Montserrat",   // Montserrat
        "MA" => "Morocco",   // Morocco
        "MZ" => "Mozambique",   // Mozambique
        "MM" => "Myanmar",   // Myanmar
        "NA" => "Namibia",   // Namibia
        "NR" => "Nauru",   // Nauru
        "NP" => "Nepal",   // Nepal
        "NL" => "Netherlands",   // Netherlands
        "AN" => "Netherlands Antilles",   // Netherlands Antilles
        "NC" => "New Caledonia",   // New Caledonia
        "NZ" => "New Zealand",   // New Zealand
        "NI" => "Nicaragua",   // Nicaragua
        "NE" => "Nicaragua",   // Niger
        "NG" => "Nigeria",   // Nigeria
        "NU" => "Niue",   // Niue
        "NF" => "Norfolk Island",   // Norfolk Island
        "KP" => "Korea (North)",   // Korea (North)
        "MP" => "Northern Mariana Islands",   // Northern Mariana Islands
        "NO" => "Norway",   // Norway
        "OM" => "Oman",   // Oman
        "PK" => "Pakistan",   // Pakistan
        "PW" => "Palau",   // Palau
        "PA" => "Panama",   // Panama
        "PG" => "Papua New Guinea",   // Papua New Guinea
        "PY" => "Paraguay",   // Paraguay
        "PE" => "Peru",   // Peru
        "PH" => "Philippines",   // Philippines
        "PN" => "Pitcairn",   // Pitcairn
        "PL" => "Poland",   // Poland
        "PT" => "Portugal",   // Portugal
        "PR" => "Puerto Rico",   // Puerto Rico
        "QA" => "Qatar",   // Qatar
        "RE" => "Reunion",   // Reunion
        "RO" => "Romania",   // Romania
        "RU" => "Russian Federation",   // Russian Federation
        "RW" => "Rwanda",   // Rwanda
        "SH" => "Saint Helena and Dependencies",   // Saint Helena and Dependencies,
        "KN" => "Saint Kitts and Nevis",   // Saint Kitts and Nevis
        "LC" => "Saint Lucia",   // Saint Lucia
        "VC" => "Saint Vincent and The Grenadines",   // Saint Vincent and The Grenadines
        "VC" => "Saint Vincent and the Grenadines",   // Saint Vincent and the Grenadines,
        "WS" => "Samoa",   // Samoa
        "SM" => "San Marino",   // San Marino
        "ST" => "Sao Tome and Principe",   // Sao Tome and Principe 
        "SA" => "Saudi Arabia",   // Saudi Arabia
        "SN" => "Senegal",   // Senegal
        "SC" => "Seychelles",   // Seychelles
        "SL" => "Sierra Leone",   // Sierra Leone
        "SG" => "Singapore",   // Singapore
        "SK" => "Slovak Republic",   // Slovak Republic
        "SI" => "Slovenia",   // Slovenia
        "SB" => "Solomon Islands",   // Solomon Islands
        "SO" => "Somalia",   // Somalia
        "ZA" => "South Africa",   // South Africa
        "GS" => "S. Georgia and S. Sandwich Isls.",   // S. Georgia and S. Sandwich Isls.
        "KR" => "South Korea",   // South Korea,
        "ES" => "Spain",   // Spain
        "LK" => "Sri Lanka",   // Sri Lanka
        "SR" => "Suriname",   // Suriname
        "SJ" => "Svalbard and Jan Mayen Islands",   // Svalbard and Jan Mayen Islands
        "SZ" => "Swaziland",   // Swaziland
        "SE" => "Sweden",   // Sweden
        "CH" => "Switzerland",   // Switzerland
        "SY" => "Syria",   // Syria
        "TW" => "Taiwan",   // Taiwan
        "TJ" => "Tajikistan",   // Tajikistan
        "TZ" => "Tanzania",   // Tanzania
        "TH" => "Thailand",   // Thailand
        "TG" => "Togo",   // Togo
        "TK" => "Tokelau",   // Tokelau
        "TO" => "Tonga",   // Tonga
        "TT" => "Trinidad and Tobago",   // Trinidad and Tobago
        "TN" => "Tunisia",   // Tunisia
        "TR" => "Turkey",   // Turkey
        "TM" => "Turkmenistan",   // Turkmenistan
        "TC" => "Turks and Caicos Islands",   // Turks and Caicos Islands
        "TV" => "Tuvalu",   // Tuvalu
        "UG" => "Uganda",   // Uganda
        "UA" => "Ukraine",   // Ukraine
        "AE" => "United Arab Emirates",   // United Arab Emirates
        "UK" => "United Kingdom",   // United Kingdom
        "US" => "United States",   // United States
        "UM" => "US Minor Outlying Islands",   // US Minor Outlying Islands
        "UY" => "Uruguay",   // Uruguay
        "VI" => "US Virgin Islands",   // US Virgin Islands,
        "UZ" => "Uzbekistan",   // Uzbekistan
        "VU" => "Vanuatu",   // Vanuatu
        "VA" => "Vatican City State (Holy See)",   // Vatican City State (Holy See)
        "VE" => "Venezuela",   // Venezuela
        "VN" => "Viet Nam",   // Viet Nam
        "WF" => "Wallis and Futuna Islands",   // Wallis and Futuna Islands
        "EH" => "Western Sahara",   // Western Sahara
        "YE" => "Yemen",   // Yemen
        "ZM" => "Zambia",   // Zambia
        "ZW" => "Zimbabwe",   // Zimbabwe
        "CU" => "Cuba",   // Cuba,
        "IR" => "Iran",   // Iran,
    );
    
    public static $country_type_list = array(
        "AF" => "all 3rdcountry",   // Afghanistan
        "AL" => "all",   // Albania
        "DZ" => "all",   // Algeria
        "AS" => "all",   // American Samoa
        "AD" => "all",   // Andorra 
        "AO" => "all",   // Angola
        "AI" => "all",   // Anguilla
        "AQ" => "all",   // Antarctica
        "AG" => "all",   // Antigua and Barbuda
        "AR" => "all",   // Argentina
        "AM" => "all",   // Armenia
        "AW" => "all",   // Aruba 
        "AU" => "all",   // Australia 
        "AT" => "all europe",   // Austria
        "AZ" => "all",   // Azerbaijan
        "BS" => "all",   // Bahamas
        "BH" => "all",   // Bahrain 
        "BD" => "all",   // Bangladesh
        "BB" => "all",   // Barbados 
        "BY" => "all",   // Belarus 
        "BE" => "all europe",   // Belgium
        "BZ" => "all",   // Belize
        "BJ" => "all",   // Benin
        "BM" => "all",   // Bermuda
        "BT" => "all",   // Bhutan
        "BO" => "all",   // Bolivia
        "BA" => "all",   // Bosnia and Herzegovina
        "BW" => "all",   // Botswana
        "BV" => "all",   // Bouvet Island
        "BR" => "all",   // Brazil
        "IO" => "all",   // British Indian Ocean Territory
        "VG" => "all",   // British Virgin Islands,
        "BN" => "all",   // Brunei Darussalam
        "BG" => "all europe",   // Bulgaria
        "BF" => "all",   // Burkina Faso
        "BI" => "all 3rdcountry",   // Burundi
        "KH" => "all",   // Cambodia 
        "CM" => "all",   // Cameroon
        "CA" => "all",   // Canada 
        "CV" => "all",   // Cape Verde
        "KY" => "all",   // Cayman Islands
        "CF" => "all",   // Central African Republic
        "TD" => "all",   // Chad
        "CL" => "all",   // Chile
        "CN" => "all",   // China
        "CX" => "all",   // Christmas Island
        "CC" => "all",   // Cocos (Keeling Islands)
        "CO" => "all",   // Colombia
        "KM" => "all",   // Comoros
        "CG" => "all 3rdcountry",   // Congo 
        "CK" => "all",   // Cook Islands
        "CR" => "all",   // Costa Rica 
        "HR" => "all europe",   // Croatia (Hrvatska
        "CY" => "all europe",   // Cyprus
        "CZ" => "all europe",   // Czech Republic
        "CG" => "all",   // Democratic Republic of Congo,
        "DK" => "all europe",   // Denmark
        "DJ" => "all",   // Djibouti
        "DM" => "all",   // Dominica
        "DO" => "all",   // Dominican Republic
        "TP" => "all",   // East Timor
        "EC" => "all",   // Ecuador
        "EG" => "all",   // Egypt 
        "SV" => "all",   // El Salvador 
        "GQ" => "all",   // Equatorial Guinea
        "ER" => "all 3rdcountry",   // Eritrea 
        "EE" => "all europe",   // Estonia 
        "ET" => "all 3rdcountry",   // Ethiopia
        "FK" => "all",   // Falkland Islands (Malvinas)
        "FO" => "all",   // Faroe Islands 
        "FM" => "all",   // Federated States of Micronesia,
        "FJ" => "all",   // Fiji
        "FI" => "all europe",   // Finland
        "FR" => "all europe",   // France
        "GF" => "all",   // French Guiana
        "PF" => "all",   // French Polynesia
        "TF" => "all",   // French Southern Territories
        "GA" => "all",   // Gabon
        "GM" => "all",   // Gambia
        "GE" => "all",   // Georgia
        "DE" => "all europe",   // Germany
        "GH" => "all",   // Ghana
        "GI" => "all",   // Gibraltar
        "GR" => "all europe",   // Greece
        "GL" => "all",   // Greenland
        "GD" => "all",   // Grenada 
        "GP" => "all",   // Guadeloupe
        "GU" => "all",   // Guam 
        "GT" => "all",   // Guatemala
        "GN" => "all",   // Guinea
        "GW" => "all 3rdcountry",   // Guinea-Bissau
        "GY" => "all",   // Guyana
        "HT" => "all",   // Haiti
        "HM" => "all",   // Heard and McDonald Islands
        "HN" => "all",   // Honduras
        "HK" => "all",   // Hong Kong
        "HU" => "all europe",   // Hungary
        "IS" => "all",   // Iceland
        "IN" => "all",   // India
        "ID" => "all",   // Indonesia
        "IR" => "all",   // Iran
        "IQ" => "all",   // Iraq
        "IE" => "all europe",   // Ireland
        "IL" => "all",   // Israel
        "IT" => "all europe",   // Italy
        "CI" => "all",   // Ivory Coast,
        "JM" => "all",   // Jamaica
        "JP" => "all",   // Japan 
        "JO" => "all",   // Jordan 
        "KZ" => "all",   // Kazakhstan
        "KE" => "all",   // Kenya 
        "KI" => "all",   // Kiribati 
        "KW" => "all",   // Kuwait
        "KG" => "all",   // Kyrgyzstan
        "LA" => "all",   // Laos
        "LV" => "all europe",   // Latvia
        "LB" => "all",   // Lebanon
        "LS" => "all",   // Lesotho
        "LR" => "all 3rdcountry",   // Liberia 
        "LY" => "all",   // Libya
        "LI" => "all",   // Liechtenstein
        "LT" => "all europe",   // Lithuania
        "LU" => "all europe",   // Luxembourg 
        "MO" => "all",   // Macau
        "MK" => "all",   // Macedonia
        "MG" => "all 3rdcountry",   // Madagascar
        "MW" => "all 3rdcountry",   // Malawi
        "MY" => "all",   // Malaysia
        "MV" => "all",   // Maldives
        "ML" => "all",   // Mali
        "MT" => "all europe",   // Malta
        "MH" => "all",   // Marshall Islands
        "MQ" => "all",   // Martinique
        "MR" => "all",   // Mauritania
        "MU" => "all",   // Mauritius
        "YT" => "all",   // Mayotte
        "MX" => "all",   // Mexico
        "MD" => "all",   // Moldova
        "MC" => "all",   // Monaco
        "MN" => "all",   // Mongolia
        "MS" => "all",   // Montserrat
        "MA" => "all",   // Morocco
        "MZ" => "all",   // Mozambique
        "MM" => "all",   // Myanmar
        "NA" => "all",   // Namibia
        "NR" => "all",   // Nauru
        "NP" => "all",   // Nepal
        "NL" => "all europe",   // Netherlands
        "AN" => "all",   // Netherlands Antilles
        "NC" => "all",   // New Caledonia
        "NZ" => "all",   // New Zealand
        "NI" => "all",   // Nicaragua
        "NE" => "all 3rdcountry",   // Niger
        "NG" => "all",   // Nigeria
        "NU" => "all",   // Niue
        "NF" => "all",   // Norfolk Island
        "KP" => "all",   // Korea (North)
        "MP" => "all",   // Northern Mariana Islands
        "NO" => "all",   // Norway
        "OM" => "all",   // Oman
        "PK" => "all",   // Pakistan
        "PW" => "all",   // Palau
        "PA" => "all",   // Panama
        "PG" => "all",   // Papua New Guinea
        "PY" => "all",   // Paraguay
        "PE" => "all",   // Peru
        "PH" => "all",   // Philippines
        "PN" => "all",   // Pitcairn
        "PL" => "all europe",   // Poland
        "PT" => "all europe",   // Portugal
        "PR" => "all",   // Puerto Rico
        "QA" => "all",   // Qatar
        "RE" => "all",   // Reunion
        "RO" => "all europe",   // Romania
        "RU" => "all",   // Russian Federation
        "RW" => "all",   // Rwanda
        "SH" => "all",   // Saint Helena and Dependencies,
        "KN" => "all",   // Saint Kitts and Nevis
        "LC" => "all",   // Saint Lucia
        "VC" => "all",   // Saint Vincent and The Grenadines
        "VC" => "all",   // Saint Vincent and the Grenadines,
        "WS" => "all",   // Samoa
        "SM" => "all",   // San Marino
        "ST" => "all",   // Sao Tome and Principe 
        "SA" => "all",   // Saudi Arabia
        "SN" => "all",   // Senegal
        "SC" => "all",   // Seychelles
        "SL" => "all 3rdcountry",   // Sierra Leone
        "SG" => "all",   // Singapore
        "SK" => "all europe",   // Slovak Republic
        "SI" => "all europe",   // Slovenia
        "SB" => "all",   // Solomon Islands
        "SO" => "all",   // Somalia
        "ZA" => "all",   // South Africa
        "GS" => "all",   // S. Georgia and S. Sandwich Isls.
        "KR" => "all",   // South Korea,
        "ES" => "all europe",   // Spain
        "LK" => "all",   // Sri Lanka
        "SR" => "all",   // Suriname
        "SJ" => "all",   // Svalbard and Jan Mayen Islands
        "SZ" => "all",   // Swaziland
        "SE" => "all europe",   // Sweden
        "CH" => "all",   // Switzerland
        "SY" => "all",   // Syria
        "TW" => "all",   // Taiwan
        "TJ" => "all",   // Tajikistan
        "TZ" => "all 3rdcountry",   // Tanzania
        "TH" => "all",   // Thailand
        "TG" => "all",   // Togo
        "TK" => "all",   // Tokelau
        "TO" => "all",   // Tonga
        "TT" => "all",   // Trinidad and Tobago
        "TN" => "all",   // Tunisia
        "TR" => "all",   // Turkey
        "TM" => "all",   // Turkmenistan
        "TC" => "all",   // Turks and Caicos Islands
        "TV" => "all",   // Tuvalu
        "UG" => "all",   // Uganda
        "UA" => "all",   // Ukraine
        "AE" => "all",   // United Arab Emirates
        "UK" => "all europe",   // United Kingdom
        "US" => "all",   // United States
        "UM" => "all",   // US Minor Outlying Islands
        "UY" => "all",   // Uruguay
        "VI" => "all",   // US Virgin Islands,
        "UZ" => "all",   // Uzbekistan
        "VU" => "all",   // Vanuatu
        "VA" => "all",   // Vatican City State (Holy See)
        "VE" => "all",   // Venezuela
        "VN" => "all",   // Viet Nam
        "WF" => "all",   // Wallis and Futuna Islands
        "EH" => "all",   // Western Sahara
        "YE" => "all 3rdcountry",   // Yemen
        "ZM" => "all 3rdcountry",   // Zambia
        "ZW" => "all",   // Zimbabwe
        "CU" => "all",   // Cuba,
        "IR" => "all",   // Iran,
    );
    
    public static function Add_IP_adresses_shutdown_function()
    {
	    $reason = error_get_last();
		$fp = fopen(dirname(__FILE__).DIRSEP.'debug_geo.txt', 'a');
		$a = date("Y-m-d H:i:s")." Reason: ".$reason['message'].' File: '.$reason['file'].' Line: '.$reason['line'];	
		fwrite($fp, $a);
		fclose($fp);
    }
    
    public static function Add_IP_adresses($remove_file = true)
    {
        error_reporting(0);
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        
        register_shutdown_function('self::Add_IP_adresses_shutdown_function');
        
        // Find GEO DB files
        $geo_db_array = array();
        foreach (glob(dirname(__FILE__).DIRSEP."geo_base_*.db") as $filename) 
        {
            $geo_db_array[] = $filename;
        }

		global $wpdb;
        
		$table_name = $wpdb->prefix . 'plgsggeo_ip';
        
        self::Set_Params(array('geo_update_progress' => 1));
        
        // Save data to sql
        
        
        // Trunc database with IP
        if (count($geo_db_array) > 0 && file_exists(dirname(__FILE__).DIRSEP."geo_base_0.db"))
        {
            $query = "TRUNCATE ".$table_name.";";
    		$wpdb->query( $query );
        }
        
        
        foreach ($geo_db_array as $file)
        {
            $lines = file($file);
            
            foreach ($lines as $line)
            {
                $i++;
                if (trim($line) == '') continue;
                
                $a = explode(",", $line);
                
                $ip_from = trim(str_replace('"', '', $a[0]));
                $ip_till = trim(str_replace('"', '', $a[1]));
                $country_code = trim(strtoupper(str_replace('"', '', $a[2])));
                
                if (strlen($country_code) != 2) continue;
                if (strpos($ip_from, ":") !== false || strpos($ip_till, ":") !== false) continue;
                
                if (strpos($ip_from, ".") !== false)
                {
                    // Convert to number
                    $tmp_ip = explode(".", $ip_from);
                    $ip_from = $tmp_ip[0]*256*256*256 + $tmp_ip[1]*256*256 + $tmp_ip[2]*256 + $tmp_ip[3];
                }
                if (strpos($ip_till, ".") !== false)
                {
                    // Convert to number
                    $tmp_ip = explode(".", $ip_till);
                    $ip_till = $tmp_ip[0]*256*256*256 + $tmp_ip[1]*256*256 + $tmp_ip[2]*256 + $tmp_ip[3];
                }
                
        		$sql_array = array(
        			'ip_from' => $ip_from,
        			'ip_till' => $ip_till,
                    'country_code' => $country_code
        		);
                
                $wpdb->insert( $table_name, $sql_array ); 
            }
            
            if ($remove_file) unlink($file);
        }
        
        self::Set_Params(array('geo_update_progress' => 0));
    }
    
    
    
    public static function Get_Params($vars = array())
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_config';
        
        $ppbv_table = $wpdb->get_results("SHOW TABLES LIKE '".$table_name."'" , ARRAY_N);
        if(!isset($ppbv_table[0])) return false;
        
        if (count($vars) == 0)
        {
            $rows = $wpdb->get_results( 
            	"
            	SELECT *
            	FROM ".$table_name."
            	"
            );
        }
        else {
            foreach ($vars as $k => $v) $vars[$k] = "'".$v."'";
            
            $rows = $wpdb->get_results( 
            	"
            	SELECT * 
            	FROM ".$table_name."
                WHERE var_name IN (".implode(',',$vars).")
            	"
            );
        }
        
        $a = array();
        if (count($rows))
        {
            foreach ( $rows as $row ) 
            {
            	$a[trim($row->var_name)] = trim($row->var_value);
            }
        }
    
        return $a;
    }
    
    
    public static function Set_Params($data = array())
    {
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgsggeo_config';
    
        if (count($data) == 0) return;   
        
        foreach ($data as $k => $v)
        {
            $tmp = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $table_name . ' WHERE var_name = %s LIMIT 1;', $k ) );
            
            if ($tmp == 0)
            {
                // Insert    
                $wpdb->insert( $table_name, array( 'var_name' => $k, 'var_value' => $v ) ); 
            }
            else {
                // Update
                $data = array('var_value'=>$v);
                $where = array('var_name' => $k);
                $wpdb->update( $table_name, $data, $where );
            }
        } 
    }
    
    public static function GetMyIP()
    {
        return $_SERVER["REMOTE_ADDR"];
    }
    
    public static function GetCountryCode($ip)
    {
        if (isset($_COOKIE["GEO_country_code"]) && isset($_COOKIE["GEO_country_code_hash"]))
        {
            $cookie_GEO_country_code = trim($_COOKIE["GEO_country_code"]);
            $cookie_GEO_country_code_hash = trim($_COOKIE["GEO_country_code_hash"]);
            
            $hash = md5($ip.'-'.$cookie_GEO_country_code);
            if ($cookie_GEO_country_code_hash == $hash) return $cookie_GEO_country_code;
        }
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_ip';
        
    	$real_ip = $ip;
        $tmp = explode(".", $ip);
        $ip = $tmp[0]*256*256*256 + $tmp[1]*256*256 + $tmp[2]*256 + $tmp[3];
        
        $query = "SELECT country_code
            FROM ".$table_name."
            WHERE ".$ip." BETWEEN ip_from AND ip_till
            LIMIT 1;";

        $rows = $wpdb->get_results($query);

        
        $a = array();
        if (count($rows))
        {
            foreach ( $rows as $row ) 
            {
                // Set cookie
                $hash = md5($ip.'-'.$row->country_code);
                setcookie("GEO_country_code", $row->country_code, time()+3600*24);
                setcookie("GEO_country_code_hash", $hash, time()+3600*24);
            	return trim($row->country_code);
            }
        }
        
        return '';
    }
    
    
    public static function Check_if_User_allowed($myCountryCode, $blocked_country_list = array())
    {
        if (in_array($myCountryCode, $blocked_country_list)) return false;
        return true;
    }
    
    
    public static function Check_if_User_IP_allowed($ip, $ip_list = '')
    {
        if ($ip_list == '') return true;
        
        $ip_list = str_replace(array(".*.*.*", ".*.*", ".*"), ".", trim($ip_list));
        $ip_list = explode("\n", $ip_list);
        if (count($ip_list))
        {
            foreach ($ip_list as $rule_ip)
            {
                if (strpos($ip, $rule_ip) === 0) 
                {
                    // match
                    return false;
                }
            }
        }
        
        return true;
    }
    
    

    public static function Save_Block_alert($alert_data)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $sql_array = array(
            'time' => intval($alert_data['time']),
            'ip' => $alert_data['ip'],
            'country_code' => $alert_data['country_code'],
            'url' => addslashes($alert_data['url']),
        );
        
        $wpdb->insert( $table_name, $sql_array ); 
    }
    
    
    public static function Delete_old_logs($days)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $old_time = time() - $days*24*60*60;
        
        $sql = 'DELETE FROM '.$table_name.' WHERE time < '.$old_time;
        $wpdb->query($sql); 
    }


	public static function PrepareDomain($domain)
	{
	    $host_info = parse_url($domain);
	    if ($host_info == NULL) return false;
	    $domain = $host_info['host'];
	    if ($domain[0] == "w" && $domain[1] == "w" && $domain[2] == "w" && $domain[3] == ".") $domain = str_replace("www.", "", $domain);
	    //$domain = str_replace("www.", "", $domain);
	    
	    return $domain;
	}
    
    public static function CheckIfPRO()
    {
        $domain = self::PrepareDomain(get_site_url());
        
        $params = self::Get_Params(array('registration_code'));
        if (!empty($params)) $registration_code = strtoupper( $params['registration_code'] );
		else return false;
        
        $check_code = strtoupper( md5( md5( md5($domain)."Version 1MI3WNNjkME4TUZj" )."5OJjDFMjjYZk2MZT" ) );
        
        if ($check_code == $registration_code) return true;
        else return false;
    }
    
    public static function CheckAntivirusInstallation()
    {
        $avp_path = dirname(__FILE__);
		$avp_path = str_replace('wp-geo-website-protection', 'wp-antivirus-site-protection', $avp_path);
        return file_exists($avp_path);
    }
    
    public static function GeneratePieData($days = 1)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $new_time = time() - $days * 24 * 60 * 60;
        
        $query = "SELECT country_code, count(*) AS country_num
            FROM ".$table_name."
            WHERE time > '".$new_time."' 
            GROUP BY country_code
            ORDER BY count(*) desc";

        $rows = $wpdb->get_results($query);
        
        //print_r($rows);

        
        $data = array();
        if (count($rows))
        {
            $total = 0;
            $i_limit = 10;
            foreach ( $rows as $row ) 
            {
                $total = $total + $row->country_num;
                if ($i_limit > 0) $data[ $row->country_code ] = $row->country_num;
                else $data[ 'Other' ] += $row->country_num;
                
                $i_limit--;
            }
            
            //print_r($data);
            
            foreach ($data as $k => $v)
            {
                $data[$k] = round( 100 * $v / $total, 2);
            }
            
            //print_r($data);
        }
        
        return $data;
    }


    public static function PreparePieData($pie_array, $slice_flag = true)
    {
        $a = array();
        if (count($pie_array))
        {
            foreach ($pie_array as $country_code => $country_proc)
            {
                if ($country_code == "Other") $country_name_txt = "Other";
                else $country_name_txt = self::$country_list[ $country_code ];
                if ($country_name_txt == "") $country_name_txt = $country_code;
                
                if ($slice_flag) $txt = "{name: '".addslashes($country_name_txt)."', y: ".$country_proc.", sliced: true, selected: true}";
                else $txt = "{name: '".addslashes($country_name_txt)."', y: ".$country_proc."}";
                $a[] = $txt;
                
                $slice_flag = false;
            }
        }
        
        return $a;
    }
    
    public static function GetLatestRecords($amount)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $new_time = time() - $days * 24 * 60 * 60;
        
        $query = "SELECT *
            FROM ".$table_name."
            ORDER BY id DESC
            LIMIT ".$amount;

        $rows = $wpdb->get_results($query);
        
        return $rows;
    }

}

/* Dont remove this code: SiteGuarding_Block_AE74F51A6762 */
?>