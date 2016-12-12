<?php

// this function looks at the emails.csv file and tries to find
// the csv entry for the email address.  it returns the array
// of the user (user name, email, location, terrority)
function getEmail($email) {
	// open this file

	$f_pointer = fopen("emails.csv","r"); // file pointer

	$emailElement = "";
	$ar = [];

	// compare all the entries in the file until email addresss found

	while ((! feof($f_pointer)) && (strcmp ($emailElement,$email) != 0)) {
		$ar=fgetcsv($f_pointer);
	
		$emailElement = $ar[0];
	}

	// close the pointer and return the array

	fclose ($f_pointer);

	return ($ar);
}

// main part of the script

if( $_GET["emailName"] )
{	
	// retrieve the email from the form
	$email = $_GET['emailName'];

	// find the user info from the email address passed in
	$userInfoArray = getEmail ($email);

	// need to format the time to use as the file name
	$timestamp = getdate();  // WeekYYMMDD where DD is 12 am Sunday PDT/PST

	// get the week number
	$week = date ("W", mktime(0, 0, 0, $timestamp[mon], $timestamp[mday], $timestamp[year]));

	// get all the arrays from the form
	$accountName = count($_GET['accountName']) ? $_GET['accountName'] : array();
	$competitionName = count($_GET['competitionName']) ? $_GET['competitionName'] : array();
	$typeName = count($_GET['typeName']) ? $_GET['typeName'] : array();
	$productName = count($_GET['productName']) ? $_GET['productName'] : array();
	$note = count($_GET['note']) ? $_GET['note'] : array();

	$activityName = count($_GET['activityName']) ? $_GET['activityName'] : array();
	$hoursName = count($_GET['hoursName']) ? $_GET['hoursName'] : array();
	$productAName = count($_GET['productAName']) ? $_GET['productAName'] : array();

	if (sizeof($accountName) >= 5) {

		// need to format the arrays retrieved into JSON data
		$learningArray = "";
		$activityArray = "";
		$typeArray = "";
	
		for ($i = 0; $i < sizeof($accountName); $i++) {
 			$learningArray[] = array ('account'=>$accountName[$i], 'competitor'=>$competitionName[$i], 'type'=>$typeName[$i], 'product'=>$productName[$i], 'note'=>$note[$i]);
		}

		for ($i = 0; $i < sizeof($activityName); $i++) {
 			$activityArray[] = array ('activity'=>$activityName[$i], 'hours'=>$hoursName[$i], 'product'=>$productAName[$i],);
		}

		$jsonArray = array('name'=>$userInfoArray[1], 'email'=>$email, 'learning'=>$learningArray, 'activity'=>$activityArray);
	
		// send email
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: ". $email. "\r\n";
		$headers .= "Reply-To: ". $email. "\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();
		$headers .= "X-Priority: 1" . "\r\n";  
 
		$body = 'User: ' . $userInfoArray[1] . "\r\n<br />";
		$body .= 'Email: ' . $userInfoArray[0] . "\r\n<br />";
		$body .= 'Territory: ' . $userInfoArray[2] . "\r\n<br />";
		$body .= 'Location: ' . $userInfoArray[3] . "\r\n<br />";
    	$body .= 'Date: ' . $timestamp[mon] . '/' . $timestamp[mday] . '/' . $timestamp[year] . "\r\n<br />\r\n<br />";
		$body .= 'Learnings' . "\r\n<br />";

		for ($i = 0; $i < sizeof($accountName); $i++) {
 			$body .= 'Account: ' . $accountName[$i] . "\r\n<br />";
 			$body .= 'Competitor: ' . $competitionName[$i] . "\r\n<br />";
 			$body .= 'Type: ' . $typeName[$i] . "\r\n<br />"; 	
 			$body .= 'Product: ' . "\n" . $productName[$i] . "\r\n<br />";
 			$body .= 'Note: ' . "\r\n<br />" . $note[$i] . "\r\n<br />";
 			$body .= "\r\n<br />";
		}

		$body .= 'Activity' . "\r\n<br />";

		for ($i = 0; $i < sizeof($activityName); $i++) {
 			$body .= 'Activity: ' . $activityName[$i] . "\r\n<br />";
 			$body .= 'Hours: ' . $hoursName[$i] . "\r\n<br />";
 			$body .= 'Product: ' . "\n" . $productAName[$i] . "\r\n<br />";
 			$body .= "\r\n<br />";
		}

		if (mail ('sig-top5@synopsys.com', 'Top 5 Weekly Summary', $body, $headers)) {
			echo "<br />Your status was successfully submitted<br />";
		} else {
			echo "<br />Failure submitting your status<br />";
		}

		$file_name = "/tmp/"; 		// hard coded: todo remove
		$file_name .= $timestamp[year];
		$file_name .= "_week";
		$file_name .= $week;
		$file_name .= ".json";

		// store JSON data into a file

		$handle = fopen($file_name, 'a');
		fwrite ($handle,  json_encode($jsonArray));
		fclose ($handle);
	} else {
		echo "Must input at least 5 learning fields";
	}

} else {
	echo "Must input email to be able to submit";
}
?>