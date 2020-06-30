<?php 
include_once 'CustomPageTheme.php';
$custom = new CustomPageTheme();
$domain_output = '';
if(isset($_REQUEST['date']))
{
    $domain_output = $custom->getHistoryOfSoftwaresInstalledByDate($_REQUEST['domain'],$_REQUEST['date']);
}
else if(isset($_REQUEST['datetime'])) {
    $datetime = strtotime($_REQUEST['datetime']);
    $datetime = date("Y-m-d H:i:s", $datetime);
    $domain_output = $custom->getHistoryOfSoftwaresInstalledByDateTime($_REQUEST['domain'],$datetime);
}
else {
    $domain_output = $custom->getInstalledComponents();
}
echo json_encode($domain_output,true);

?>