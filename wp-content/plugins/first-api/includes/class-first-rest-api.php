<?php
add_action( 'rest_api_init', function () {
  register_rest_route( 'componentwatch/v1', '/getInstalledComponents/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'my_awesome_func',
  ) );
} );


add_action( 'rest_api_init', function () {
  register_rest_route( 'componentwatch/v1', '/getDomainHistory/(?P<date>\w+)', array(
    'methods' => 'GET',
    'callback' => 'get_domain_history',
  ) );
} );

    function get_domain_history( WP_REST_Request $request ) {
        $param = $request['date'];
        echo "<pre>";
        print_r($param);
        
    }
    /**
    * Grab latest post title by an author!
    *
    * @param array $data Options for the function.
    * @return string|null Post title for the latest,  * or null if none.
    */
    function my_awesome_func( $data ) {
        $posts = get_posts( array( 
        'getInstalledComponents' => $data['id'],
        ) );

        if ( empty( $posts ) ) {
        return null;
        }
        global $wpdb;

        $show_versions = $wpdb->get_row('SHOW VARIABLES LIKE "version"');
        $arr = array();
        $arr['domain'] = 'virat.com';
        $arr['registered'] = date("Y-m-d h:m:s");
        $arr['last_updated'] = date("Y-m-d h:m:s");
        
        
        $arr['softwares_installed']['system_info']['mysql_version']['current_version'] = $show_versions->Value;
        $arr['softwares_installed']['system_info']['mysql_version']['status'] = 'Active';
        $arr['softwares_installed']['system_info']['mysql_version']['is_update'] = '--';
        $arr['softwares_installed']['system_info']['mysql_version']['new_version'] = '--';
        
        $arr['softwares_installed']['system_info']['wp_version']['current_version'] = get_bloginfo( 'version' );
        $arr['softwares_installed']['system_info']['wp_version']['status'] = 'Active';
        $arr['softwares_installed']['system_info']['wp_version']['is_update'] = '--';
        $arr['softwares_installed']['system_info']['wp_version']['new_version'] = '--';
        
        $arr['softwares_installed']['system_info']['php_version']['current_version'] = PHP_VERSION;
        $arr['softwares_installed']['system_info']['php_version']['status'] = 'Active';
        $arr['softwares_installed']['system_info']['php_version']['is_update'] = '--';
        $arr['softwares_installed']['system_info']['php_version']['new_version'] = '--';
        
        $arr['softwares_installed']['system_info']['domain']['current_version'] = $_SERVER['SERVER_NAME'];
        $arr['softwares_installed']['system_info']['domain']['status'] = 'Active';
        $arr['softwares_installed']['system_info']['domain']['is_update'] = '--';
        $arr['softwares_installed']['system_info']['domain']['new_version'] = '--';
        $lResult = $_SERVER['SERVER_SOFTWARE'];
        $arr['softwares_installed']['system_info']['apache_version']['current_version'] = $lResult;
        $arr['softwares_installed']['system_info']['apache_version']['status'] = 'Active';
        $arr['softwares_installed']['system_info']['apache_version']['is_update'] = '--';
        $arr['softwares_installed']['system_info']['apache_version']['new_version'] = '--';

        require_once ABSPATH.'/wp-admin/includes/plugin.php';
        $all_plugins = get_plugins();
        
        
        $active_plugins = get_option( 'active_plugins' );
        $current         = get_site_transient( 'update_plugins' );
        foreach ( $all_plugins as $key => $value ) {
            $is_active = ( in_array( $key, $active_plugins ) ) ? 'Active' : 'In Active';
          
            $all_plugins[ $key ]['Status'] = $is_active;
            $all_plugins[ $key ]['is_update'] = 'Not Available';
            $all_plugins[ $key ]['new_version'] = '-';
        }
        
        foreach ( (array) $all_plugins as $plugin_file => $plugin_data ) {
		if ( isset( $current->response[ $plugin_file ] ) ) {
			$plugin_updates[ $plugin_file ]         = (object) $plugin_data;
			$plugin_updates[ $plugin_file ]->update = $current->response[ $plugin_file ];
		}
	}
         foreach ( $plugin_updates as $key => $value ) {
            if($all_plugins[ $key ])
            {
                //$var =get_object_vars($value);
                $all_plugins[ $key ]['is_update'] = 'Available';
                $all_plugins[ $key ]['new_version'] = $value->update->new_version;
            }
        } 

        //echo "<pre>";
        //print_r($all_plugins);

        foreach ( $all_plugins as $key => $value ) {
            $version_arr = array( 
                'current_version' => $value['Version'],
                'status' => $value['Status'],
                'is_update' => $value['is_update'],
                'new_version' => $value['new_version'] );
                
            if($value['is_update'] == 'Available'){
                  $version_arr['new_version'] = $value['new_version'];
                
            }
            $arr['softwares_installed']['Plugin'][$value['TextDomain']] = $version_arr;
        } 
        return $arr['softwares_installed'];
    }