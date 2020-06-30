<?php /* Template Name: CustomPageT1 */
error_reporting(0);
class CustomPageTheme {
  
    // Properties
    public $dbObj;
    public $domainList;
    // Methods

    function __construct() {
        global $wpdb;
        if(!isset($wpdb))
        {
            //the '../' is the number of folders to go up from the current file to the root-map.
            require_once('../../../wp-config.php');
            require_once('../../../wp-includes/wp-db.php');
        }
        global $wpdb;
        $this->dbObj = $wpdb;
        //$this->dbObj = new wpdb('root', '', 'testsite', 'localhost'); 
        $this->dbObj->select('testsite');
        
        $this->domainList['a994f071d7ed3401cb152b1df46fc9d3-1471600098'] = "http://a994f071d7ed3401cb152b1df46fc9d3-1471600098.us-west-2.elb.amazonaws.com//index.php/wp-json/componentwatch/v1/getInstalledComponents/1";
        
        $this->domainList['a23a9268ca049478c8bd5eca799ccd26-1829171198'] = "http://a23a9268ca049478c8bd5eca799ccd26-1829171198.us-west-2.elb.amazonaws.com//index.php/wp-json/componentwatch/v1/getInstalledComponents/1";
        
        $this->domainList['a11417d24790447be8109179cb9f739d-1862852182'] = "http://a11417d24790447be8109179cb9f739d-1862852182.us-west-2.elb.amazonaws.com//index.php/wp-json/componentwatch/v1/getInstalledComponents/1"; 
        
        $this->domainList['as-cv-pub-vip-vol-wl-p-001'] = "https://as-cv-pub-vip-vol-wl-p-001.azurewebsites.net/index.php/wp-json/componentwatch/v1/getInstalledComponents/1"; 
        
        $this->domainList['localhost'] = "http://localhost/wordpress/index.php/wp-json/componentwatch/v1/getInstalledComponents/1"; 
        
        
        //$this->dbObj = new wpdb('wpadmin', 'wpadmin1234', 'wpaascmdb', 'wpaasdb.cydwunev2mr4.us-west-2.rds.amazonaws.com'); 
        //$this->dbObj->select('wpaascmdb');
        
       /*
          CREATE TABLE `wp_track_softwares_installed` (
          `site_id` bigint(20) NOT NULL DEFAULT 0,
          `domain` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
          `registered` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `last_updated` date NOT NULL,
          `archived` tinyint(2) NOT NULL DEFAULT 0,
          `softwares_installed` varchar(5000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci */
        
        if (!wp_next_scheduled('watch_dog_task_hook')) {
            wp_schedule_event( time(), 'hourly', 'watch_dog_task_hook' );
        }
        add_action ( 'watch_dog_task_hook', 'triggeredByCronJob' );
       //exit;
    }

    function wh_log($log_msg)
    {
        $log_file_data = 'debug.log';
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    } 

    
    function getInstalledComponents() {
        $this->wh_log("Triggered by Page Refersh");
        $array = array_values($this->domainList);
        $site_arr = array();
        foreach ($array as $url) {
            $ch = curl_init();
            curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => 1,CURLOPT_URL => $url]);
            $site_output = curl_exec($ch);
            $charge = explode('/', $url);
            $site_arr[$charge[2]] = json_decode($site_output,true);
            
            $final[$charge[2]] = $this->getHistoryOfSoftwaresInstalled($charge[2],$site_arr[$charge[2]]);
            
        }
        return $final;
    }
    
    function triggeredByCronJob()
    {
        // call to function
        $this->wh_log("Triggered by corn job");
        $this->getInstalledComponents();
    }
    
    function getInstalledSoftwaresByDomain($url) {
        $site_arr = array();
        $ch = curl_init();
        curl_setopt_array($ch, [ CURLOPT_RETURNTRANSFER => 1,CURLOPT_URL => $url]);
        $site_output = curl_exec($ch);
        return $site_output;
    }
    
    
    function getHistoryOfSoftwaresInstalled($domain,$site_arr,$history_arr=null)
    {
        $final_arr = $site_arr;
        if($history_arr != null)
        {
            $default_row = $history_arr;
            
        }
        else {
            $default_row = $this->dbObj->get_row( "SELECT softwares_installed FROM wp_track_softwares_installed where domain = '".$domain."' ORDER BY site_id DESC LIMIT 1");
        }
        if ($default_row != null ) {
            $history_softwares = $default_row->softwares_installed;
            $history_softwares_arr = json_decode($history_softwares,true);
           
            if(base64_encode($history_softwares) != base64_encode(json_encode($site_arr)))
            {
                foreach ($site_arr as $type => $software) {
                    foreach ($software as $sw => $info){
                        if(isset($history_softwares_arr[$type][$sw]))
                        {
                            $final_arr[$type][$sw]['history'] = '';
                            if($history_softwares_arr[$type][$sw]['current_version'] != $final_arr[$type][$sw]['current_version'])
                            {
                                $final_arr[$type][$sw]['history'] = 'Change in version<br/>';
                                
                            }
                            if($history_softwares_arr[$type][$sw]['status'] != $final_arr[$type][$sw]['status'])
                            {
                                $final_arr[$type][$sw]['history'] .= 'Change in status';
                            }
                            
                            if($final_arr[$type][$sw]['history'] == '')
                            {
                                $final_arr[$type][$sw]['history'] = 'No Changes';
                            }
                        }
                        else {
                           $final_arr[$type][$sw]['history'] = 'Added';
                        }
                    }
                }
                $this->insertRowInDatable($domain,$site_arr);
                
                #Logic to check if any plugin is deleted from yesterday.
                foreach ($history_softwares_arr as $type => $software) {
                    foreach ($software as $sw => $info){
                        if(!isset($site_arr[$type][$sw]))
                        {
                           $final_arr[$type][$sw] = $history_softwares_arr[$type][$sw];
                           $final_arr[$type][$sw]['history'] = 'Removed';
                        }
                    }
                }
            }
            else {
                
                $final_arr = $this->setHistoryKey($final_arr);
            }
        }
        else {
            $this->insertRowInDatable($domain,$site_arr);
            $final_arr = $this->setHistoryKey($final_arr);
            
        }
        return $final_arr;
    }
    
    function getHistoryOfSoftwaresInstalledByDate($domain,$date)
    {
        //$final_arr = $site_arr;
        $final_arr = array();
        $sql = "SELECT softwares_installed FROM wp_track_softwares_installed where domain like '".$domain."%' and last_updated = '".$date."' ORDER BY site_id DESC LIMIT 1";
        $default_row = $this->dbObj->get_row($sql);

        if ($default_row != null ) {
            $site_arr = $this->getInstalledSoftwaresByDomain($this->domainList[$domain]);
             $final_arr[$domain] = $this->getHistoryOfSoftwaresInstalled($domain,json_decode($site_arr,true),$default_row);
        }
        return $final_arr;
    }
    
    function getHistoryOfSoftwaresInstalledByDateTime($domain,$datetime)
    {
        //$final_arr = $site_arr;
        $final_arr = array();

        $sql = "SELECT softwares_installed FROM wp_track_softwares_installed where domain like '".$domain."%' and ABS(TIMESTAMPDIFF(MINUTE,'$datetime',registered)) = '0' ORDER BY site_id DESC LIMIT 1";
        $default_row = $this->dbObj->get_row($sql);
      
        if($default_row == null)
        {
            $sql = "SELECT softwares_installed FROM wp_track_softwares_installed where domain like '".$domain."%' and registered BETWEEN DATE_SUB('$datetime',INTERVAL 30 MINUTE) AND '$datetime' ORDER BY site_id DESC LIMIT 1";
            $default_row = $this->dbObj->get_row($sql);
        }

        if ($default_row != null ) {
            $site_arr = $this->getInstalledSoftwaresByDomain($this->domainList[$domain]);
            $final_arr[$domain] = $this->getHistoryOfSoftwaresInstalled($domain,json_decode($site_arr,true),$default_row);
        }
        return $final_arr;
    }
    
    function setHistoryKey($final_arr)
    {
        if(is_array($final_arr))
        {
            foreach ($final_arr as $type => $software) {
                foreach ($software as $sw => $info){
                    $final_arr[$type][$sw]['history'] = 'No Changes';
                }
            }
        }
        return $final_arr;
    }
    function insertRowInDatable($domain,$site_arr)
    {
        $default_row = $this->dbObj->get_row( "SELECT site_id FROM wp_track_softwares_installed ORDER BY site_id DESC LIMIT 1" );

        if ( $default_row != null ) {
            $id = $default_row->site_id + 1;
        } else {
            $id = 1;
        } 
        $default = array(
            'site_id' => $id,
            'domain' => '',
            'registered' => '',
            'last_updated' => '',
            'softwares_installed' => ''
        ); 
        //date_default_timezone_set('UTC');
        $arr['site_id'] = $id;
        $arr['domain'] = $domain;
        $arr['registered'] = date("Y-m-d H:i:s");
        $arr['last_updated'] = date("Y-m-d H:i:s");
        $arr['archived'] = 0;
        $arr['softwares_installed'] = json_encode($site_arr);
        $item = shortcode_atts($default, $arr);
        $this->dbObj->insert( 'wp_track_softwares_installed', $item );
        
    }
    
    function array_diff_assoc_recursive($array1, $array2)
    {
        foreach($array1 as $key => $value)
        {
            if(is_array($value))
            {
                if(!isset($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                elseif(!is_array($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                else
                {
                    $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                    if($new_diff != FALSE)
                    {
                        $difference[$key] = $new_diff;
                    }
                }
            }
            elseif(!isset($array2[$key]) || $array2[$key] != $value)
            {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }
}
?>