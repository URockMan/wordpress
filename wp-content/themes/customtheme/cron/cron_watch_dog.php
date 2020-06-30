<?php

class CustomPageTheme {
  
    // Properties
    public $dbObj;
    public $domainList;
    public $database;
    // Methods

    function __construct() {
        
        $servername = "localhost";
        $username = "root";
        $password = "";
        $this->database = 'testsite';

        $servername = "wpaasdb.cydwunev2mr4.us-west-2.rds.amazonaws.com";
        $username = "wpadmin";
        $password = "wpadmin1234";
        $this->database = 'wpaascmdb'; 

        // Create connection
        $this->dbObj = mysqli_connect($servername, $username, $password);

        // Check connection
        if (!$this->dbObj) {
          die("Connection failed: " . mysqli_connect_error());
        }
        
        /* $this->domainList['a994f071d7ed3401cb152b1df46fc9d3-1471600098'] = "http://a994f071d7ed3401cb152b1df46fc9d3-1471600098.us-west-2.elb.amazonaws.com//index.php/wp-json/componentwatch/v1/getInstalledComponents/1";
        
        $this->domainList['a23a9268ca049478c8bd5eca799ccd26-1829171198'] = "http://a23a9268ca049478c8bd5eca799ccd26-1829171198.us-west-2.elb.amazonaws.com//index.php/wp-json/componentwatch/v1/getInstalledComponents/1";
        
        $this->domainList['a11417d24790447be8109179cb9f739d-1862852182'] = "http://a11417d24790447be8109179cb9f739d-1862852182.us-west-2.elb.amazonaws.com//index.php/wp-json/componentwatch/v1/getInstalledComponents/1"; */ 
        
        /*$this->domainList['as-cv-pub-vip-vol-wl-p-001'] = "https://as-cv-pub-vip-vol-wl-p-001.azurewebsites.net/index.php/wp-json/componentwatch/v1/getInstalledComponents/1"; */
        
        $this->domainList['localhost'] = "http://localhost/wordpress/index.php/wp-json/componentwatch/v1/getInstalledComponents/1";
        
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
        return $site_arr;
    }
    
    function getHistoryOfSoftwaresInstalled($domain,$site_arr,$history_arr=null)
    {
        $final_arr = $site_arr;
        $default_row = null;
        if($history_arr != null)
        {
            $default_row = $history_arr;
            
        }
        else {
            $sql = "SELECT softwares_installed FROM ".$this->database.".wp_track_softwares_installed where domain = '".$domain."' and last_updated = '".date("Y-m-d")."' ORDER BY site_id DESC LIMIT 1";
            
            if ($result = mysqli_query($this->dbObj,$sql)) {
               $default_row = mysqli_fetch_assoc($result);
            }
        }
        if ($default_row != null ) {
            $history_softwares = $default_row['softwares_installed'];
            
            $history_softwares_arr = json_decode($history_softwares,true);
            
            $diff = $this->array_diff_assoc_recursive($site_arr,$history_softwares_arr);
           
            if($diff || is_array($diff))
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
    
    function setHistoryKey($final_arr)
    {
        foreach ($final_arr as $type => $software) {
            foreach ($software as $sw => $info){
                $final_arr[$type][$sw]['history'] = 'No Changes';
            }
        }
        return $final_arr;
    }
    
   
    function insertRowInDatable($domain,$site_arr)
    {
        $sql = "SELECT site_id FROM ".$this->database.".wp_track_softwares_installed ORDER BY site_id DESC LIMIT 1";
        $default_row = null;
        
        if ($result = mysqli_query($this->dbObj,$sql)) {
            $default_row = mysqli_fetch_assoc($result);
        }


        if ( $default_row != null ) {
            $id = $default_row['site_id'] + 1;
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
        date_default_timezone_set('UTC');
        $arr['site_id'] = $id;
        $arr['domain'] = $domain;
        $arr['registered'] = date("Y-m-d H:i:s");
        $arr['last_updated'] = date("Y-m-d H:i:s");
        $arr['archived'] = 0;
        $arr['softwares_installed'] = json_encode($site_arr);
        //$item = shortcode_atts($default, $arr);
        
        if($arr['softwares_installed'] != null)
        {
            $sql = "INSERT INTO ".$this->database.".wp_track_softwares_installed(site_id,domain,registered,last_updated,archived,softwares_installed)VALUES ('".$arr['site_id']."','".$arr['domain']."','".$arr['registered']."','".$arr['last_updated']."','".$arr['archived']."','".$arr['softwares_installed']."')";
            
            if (mysqli_query($this->dbObj, $sql)) {
                   echo "New record created successfully\n";
            } else {
               echo "Error: " . $sql . "" . mysqli_error($conn);
            }
        }
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
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
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
$custom = new CustomPageTheme();
$domain_output = $custom->getInstalledComponents();
?>