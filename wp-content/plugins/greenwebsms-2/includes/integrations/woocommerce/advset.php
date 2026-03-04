<?php 
$parse_uri = explode( 'wp-content', __DIR__ );    
require_once( $parse_uri[0] . 'wp-load.php' );

//connect db
global $wpdb;
$wpdb->hide_errors();
if ( class_exists( 'WooCommerce' ) ) {
	
} else {
	echo "Please install WooCommerce First.";
	exit();
}


$statuse = wc_get_order_statuses();
$fs = "";

	foreach ($statuse as $key => $val) {
   $statuses = $val;
   $fs = "$statuses,$fs";
}  


	
function sqli($data) {
	$data = urlencode(htmlentities(strip_tags($data)));
	return $data;
}
function sqlid($data) {
$data =  preg_replace('#[^\w( )/,%\-&]#',
"",$data);
	return $data;
}



//create table if not exists

if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}greenwebsmsstatus'") != $wpdb->prefix . "greenwebsmsstatus") {

        // we need this to access the maybe_create_table function
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $charset_collate = $wpdb->get_charset_collate();
        $table_tasks = $wpdb->prefix . "greenwebsmsstatus";
        $columns_tasks = <<<COLUMNS
(
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(250) NOT NULL,
  `smsmessage` longtext NOT NULL,

  `cleanstatus` varchar(250) NOT NULL,
  PRIMARY KEY (`ID`)
)
COLUMNS;
 
        // no duplicate
        maybe_create_table( $wpdb->prefix . $table_name, "CREATE TABLE {$table_tasks} {$columns_tasks} {$charset_collate};" );
		
$charset_collate = $wpdb->get_charset_collate();
        $table_tasks = $wpdb->prefix . "gwdeny";
        $columns_tasks = <<<COLUMNS
(
  `denylist` longtext NOT NULL
)
COLUMNS;
 
        // no duplicate
        maybe_create_table( $wpdb->prefix . $table_name, "CREATE TABLE {$table_tasks} {$columns_tasks} {$charset_collate};" );		
}

if( current_user_can('editor') || current_user_can('administrator') ) {  
    
}  else {
    echo "Please log in as admin to view this page";
    exit();
}

echo "<center>";
if ((isset($_POST['disableit'])) AND (isset($_POST['submitdisable']))) {
$clean = sqlid($_POST['disableit']);

$result = $wpdb->get_results("SELECT denylist FROM {$wpdb->prefix}gwdeny",
 ARRAY_A);

if($wpdb->num_rows < 1) { 
$inserted = $wpdb->query("INSERT INTO {$wpdb->prefix}gwdeny (denylist) VALUES ('')");
 }
      foreach($result as $row)
    {
        $loadnew = $row['denylist'];
        
    }
	
	$loadnew = trim($loadnew,',');
	$cleans = "$loadnew,$clean";
	$cleans = trim($cleans,
',');
	if ($clean != "") {
$inserted = $wpdb->query("UPDATE {$wpdb->prefix}gwdeny SET denylist = '$cleans'");

 if ($inserted === false) {
     echo "Database permission problem, Contact with our support to get help";
 } else {
     echo "</br></br><b style='font-size:18px;color:white;background:green;padding:5px;'>Settings Updated Successfully !</b></br>";
 }
	} else {
		
		 echo "</br></br><b style='font-size:18px;color:white;background:red;padding:5px;'>Failed to Disable ! Close this page and try again after 30 seconds</b></br>";
	}

}

if ((isset($_POST['enableit'])) AND (isset($_POST['submitenable']))) {
$enableit = sqlid($_POST['enableit']);
$clean = "";
$result = $wpdb->get_results("SELECT denylist FROM {$wpdb->prefix}gwdeny", ARRAY_A);
      foreach($result as $row)
    {
        $loadnew = $row['denylist'];
        
    }
	$loadnew = trim($loadnew,
',');
	
	$loadne = explode(',', $loadnew);
			
			foreach ($loadne as $loadn) {
				if ($loadn != "$enableit") {
				$clean = "$clean,
$loadn";
			}
			}
	$clean = trim($clean,',');
$inserted = $wpdb->query("UPDATE {$wpdb->prefix}gwdeny SET denylist = '$clean'");

 if ($inserted === false) {
     echo "Setting File is not editable, Contact with our support to get help";
 } else {
     echo "</br></br><b style='font-size:18px;color:white;background:green;padding:5px;'>Settings Updated Successfully !</b></br>";
 }

}



 $loads = "";
//get loads data
$result = $wpdb->get_results("SELECT denylist FROM {$wpdb->prefix}gwdeny",
 ARRAY_A);
      foreach($result as $row)
    {
        $loads = $row['denylist'];
        
    }
    
//setup status specific message
if ((isset($_POST['statusname'])) AND (isset($_POST['addstatussms']))) {
$statusname = sqlid($_POST['statusname']);
$statusmessage = sqli($_POST['statusmessage']);
$cleanstatus =  strtolower(preg_replace('#[^\w()/,%\-&]#',"",$statusname));



$dublicate = $wpdb->get_results("select cleanstatus FROM {$wpdb->prefix}greenwebsmsstatus WHERE cleanstatus = '$cleanstatus'",
 ARRAY_A); 

if($wpdb->num_rows > 0) {
$inserted = $wpdb->query("UPDATE {$wpdb->prefix}greenwebsmsstatus SET status = '$statusname', smsmessage ='$statusmessage' WHERE cleanstatus = '$cleanstatus'");	
} else {
$inserted = $wpdb->query("INSERT INTO {$wpdb->prefix}greenwebsmsstatus (status,smsmessage,cleanstatus) VALUES ('$statusname',
'$statusmessage','$cleanstatus')");
}


 if ($inserted === false) {
     echo "<b style='font-size:18px;color:white;background:red;padding:5px;'>Setting File is not editable, Contact with our support to get help</b>";
 } else {
     echo "</br></br><b style='font-size:18px;color:white;background:green;padding:5px;'>Settings Updated Successfully !</b></br>";
     
 }

}
 
if (isset($_POST['deletestatus'])) {
$row = $_POST['deletestatus'];
$result = $wpdb->query("DELETE FROM {$wpdb->prefix}greenwebsmsstatus WHERE ID = '$row'");
if ($result === false) {
        echo "<b style='font-size:18px;color:white;background:red;padding:5px;'>Status Failed to remove</b>"; 
 } else {
    echo "<b style='font-size:18px;color:white;background:green;padding:5px;'>Status Removed Successfully</b>";
 } 
}
   


echo "<style>
td {
   border:1px solid white; 
   vertical-align:middle;
   color:white;
   
}
th {
	 border:1px solid white; 
   vertical-align:middle;
   color:white;
   background: #FF5722;
}
</style>

</br>

<div style='background:#6d0707;color:white;padding:5px;margin:10px'>
<hr/>Disabled Statuses: <hr/>
নিচের টেবিলে দেখানো স্ট্যাটাসগুলোতে SMS Status Update Notification যাবে না, আপনি Turn On Sending SMS বাটনে ক্লিক করে পূনরায় SMS নোটিফিকেশন চালু করতে পারবেন


</br>
<style>
.button {
  border-radius: 4px;
  background-color: #f4511e;
  border: none;
  color: #FFFFFF;
  text-align: center;
  font-size: 16px;
  padding: 5px;
 
  transition: all 0.5s;
  cursor: pointer;

}

.button span {
  cursor: pointer;
  display: inline-block;
  position: relative;
  transition: 0.5s;

}

.button span:after {
  content: '\00bb';
  position: absolute;
  opacity: 0;
  
  right: -20px;
  transition: 0.5s;
}

.button:hover span {
	padding:10px;
	 padding-right: 35px;
   	font-size:20px;
}

.button:hover span:after {
  opacity: 1;
  right: 0;
}

</style>
	
</br>
<table style='
    background: #607D8B;min-width:50%;
'>
<thead>
<th>Status Name</th>
<th>SMS Notification</th>
<th>Action</th>
 </thead>
 <tbody>
</tr>";
			$disabledstatuses = explode(',
', $loads);
			
			foreach ($disabledstatuses as $disabledstatus) {
					if (!empty($disabledstatuses)) {
				if ($disabledstatus != "") {
				echo "<tr> <td>$disabledstatus</td> <td>Currently OFF</td>
				
				<td><form method='post' action=''><input name='enableit' value='$disabledstatus' type='hidden'><button class='button' name='submitenable'><span class='circleon'>Turn On Sending SMS</span></button></form></td>
				
				";
				}
			} else {
			echo "<tr><td colspan='3'>You dont have any status disabled !</td></tr>";	
			}
			
			}
		
			
echo "
</b> 
</tbody>
</table>
</br>
</br>
</div>
<div style='background:#064509;color:white;padding:5px;margin:10px'>
<hr/>Enabled Statuses: <hr/>
নিচের টেবিলে দেখানো স্ট্যাটাসগুলোতে SMS Status Update Notification যাবে, আপনি Turn Off Sending SMS বাটনে ক্লিক করে SMS নোটিফিকেশন বন্ধ করতে পারবেন

<table style='
    background: #3f51b5;min-width:50%;
'>
<thead>
<th>Status Name</th>
<th>SMS Notification</th>
<th>Action</th>
 </thead>
 <tbody>
</tr>
";

$enabledstatuses = explode(',',
 $fs);

$enabledstatusess = array_diff($enabledstatuses, $disabledstatuses);
			
			
			foreach ($enabledstatusess as $enabledstatus) {
				if (!empty($enabledstatusess)) {
					if ($enabledstatus != "") {
				echo "<tr> <td>$enabledstatus</td> <td>Currently On</td>
				
				
				<td><form method='post' action=''><input name='disableit' value='$enabledstatus' type='hidden'><button class='button' name='submitdisable'><span class='circleon'>Turn Off Sending SMS</span></button></form></td>
				
				
				";
					}
					
			} else {
				echo "<tr><td colspan='3'>You dont have any status enabled!</td></tr>";
			}
			}
		
echo "			
</tbody>
</table>
</b></br>
</div>

</div>

<div style='background:#7a7473;color:white;padding:5px;margin:10px;font-size:16px;'>
<hr/>Set Specific Status Based SMS<hr/>

ভিন্ন ভিন্ন অর্ডার স্ট্যাটাসের জন্য ভিন্ন ভিন্ন SMS প্রেরন করতে নিচের সিলেক্ট অপশন থেকে কাঙ্খিত স্ট্যাটাসটি সিলেক্ট করুন, এরপর ম্যাসেজ বক্সে সর্টকোড সহ টেক্সট নোটিফিকেশনের লেখা দিন । যেমন: অর্ডার Complete এ এক রকম ম্যাসেজ আর অর্ডার Pending এ অন্যরকম ম্যাসেজ দিতে নিচের অপশনটি ব্যবহার করুন । 
	
</br><p></p>
<form method='post' action=''>
  <div class='form-group'>
  
 
            <label for=''>Select Status:</label> </br>
			
			<select style='min-width:50%' id='cars' name='statusname'>
  			";
			
			$finalstatus = explode(',',
 $fs);
			
			foreach ($finalstatus as $finalstat) {
				if ($finalstat != "") {
				echo "<option value='$finalstat'>$finalstat</option>";
				}
			}
			
echo "</select>
</br>
             <label for=''>Write Your Message here:</label> </br>
            <textarea rows='4' cols='50' class='form-control' name='statusmessage' value='<?php echo $loads; ?>'  style='width:80%' required></textarea>
            </br> Available Shortcodes: Order id:  <b style='color:white;background:#66747a'>%order_id%</b>, Order number:  <b style='color:white;background:#66747a'>%order_number%</b>, Order status:  <b style='color:white;background:#66747a'>%status%</b>, Total Price:  <b style='color:white;background:#66747a'>%product_price%</b>,
 Billing First name:  <b style='color:white;background:#66747a'>%billing_first_name%</b>, Billing Last Name:  <b style='color:white;background:#66747a'>%billing_last_name%</b>, Payment Method:  <b style='color:white;background:#66747a'>%gweb_payment%</b>, Purchased Items:  <b style='color:white;background:#66747a'>%gweb_items%</b>,
 Shipping Address:  <b style='color:white;background:#66747a'>%gweb_shipping%</b>, Customer Note:  <b style='color:white;background:#66747a'>%gweb_note%</b>, Order View URL:  <b style='color:white;background:#66747a'>%gweb_orderurl%</b>, Order Payment Link:  <b style='color:white;background:#66747a'>%gweb_payurl%</b>,
 Ordered Products Link:  <b style='color:white;background:#66747a'>%gweb_producturl%</b>,
</br></br><a style='color:white' href='<?php echo get_site_url(); ?>/wp-admin/admin.php?page=greenweb-sms-extra&tab=gwebreview' target='_blank'>Special Shortcode:Send Review Link to Customer(Woo Review Tab Must be Configured first and setup it only for completed status or delivered type status final so that your user left review after getting products): </a> <b style='color:white;background:#66747a'>%gweb_review%</b>,
          <input type='hidden' name='addstatussms'>
		  </div>
		  <button type='submit' value='submit' class='button'><span>Save Setting</span></button>
		  </form>
		  
		  </br></br>
		  Saved Messages:
		  </br>
		  <table style='background: darkkhaki;max-width: 80%;'>
		      <head>
		          <tr>
		              <th>Status Name</th>
		              <th>Status SMS</th>
		              <th>Action</th>
		          </tr>
		      </head>
		  <tbody>
		      <tr>";

$result = $wpdb->get_results("SELECT status,smsmessage,
ID FROM {$wpdb->prefix}greenwebsmsstatus", ARRAY_A);
      foreach($result as $row)
    {
       
         $id = $row['ID'];
         echo "<tr><td>";
         echo $status = $row['status'];
         echo "</td><td><textarea  rows='4' cols='50' >";
         echo  $smsmessage = stripslashes(urldecode(stripslashes($row['smsmessage'])));
         echo "</textarea></td><td>";
         echo "<form style='margin: 0 auto;' action='' method='post'>
         <input name='deletestatus' type='hidden' value='$id'>
         <input type='submit' name='deletest' value='Delete'>
         </form>"
         ;
    } 

echo "</tbody>
	</table>	  
</div>
</center>";
