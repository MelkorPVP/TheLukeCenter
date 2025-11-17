<?php
	require_once __DIR__ . '/config.php';
	
	//Header Location
	$HEADERLOCATION = 'Location: contact.php#contactStatus';
	
	// VALIDATE HTTP REQUEST METHOD
	if($_SERVER['REQUEST_METHOD'] !== 'POST')
	{
		$_SESSION['message'] = 'Invalid request method. Please use the form to submit data.';
		$_SESSION['messageType'] = 'error';
		header($HEADERLOCATION);
		exit();
	}
	
	// VALIDATE HTTP REQUEST PARAMETERS
	if(!isset($_POST['contactFirstName'], $_POST['contactLastName'], $_POST['contactEmail'],$_POST['contactPhone'], $_POST['contactPhoneType'], $_POST['yearAttended']))
	{
		$_SESSION['message'] = 'Invalid request parameters. One or more required form parameters is missing.';
		$_SESSION['messageType'] = 'error';
		header($HEADERLOCATION);
		exit();		
	}
	
	// SANITIZE HTTP REQUEST PARAMETERS	
	$contactFirstName = filter_input(INPUT_POST, 'contactFirstName', FILTER_SANITIZE_SPECIAL_CHARS);
	$contactLastName = filter_input(INPUT_POST, 'contactLastName', FILTER_SANITIZE_SPECIAL_CHARS);
	$contactEmail = filter_input(INPUT_POST, 'contactEmail', FILTER_SANITIZE_SPECIAL_CHARS);
	$contactPhone = filter_input(INPUT_POST, 'contactPhone', FILTER_SANITIZE_SPECIAL_CHARS);
	$contactPhoneType = filter_input(INPUT_POST, 'contactPhoneType', FILTER_SANITIZE_SPECIAL_CHARS);
	$currentWork = filter_input(INPUT_POST, 'currentOrganization', FILTER_SANITIZE_SPECIAL_CHARS);
	$yearAttended = filter_input(INPUT_POST, 'yearAttended', FILTER_SANITIZE_SPECIAL_CHARS);
	
	// ERRORS ARRAY
	$errors = [];
	
	// VALIDATE EMAIL
	if(!filter_var($contactEmail, FILTER_VALIDATE_EMAIL))
	{
		$errors[] = 'Invalid email format.';
	}
	
	// CHECK FOR ERRORS
	if(!empty($errors))
	{
		$_SESSION['message'] = implode('<br>', $errors);
		$_SESSION['messageType'] = 'error';
		header($HEADERLOCATION);
		exit();	
	}
	
	// PROCESS FORM 
	//echo "<p>First Name: $contactFirstName</p><p>Last Name: $contactLastName</p><p>Email: $contactEmail</p><p>Phone: $contactPhone</p>" .
	//"<p>Phone Type: $contactPhoneType</p><p>Organization: $currentOrganization</p><p>Program Year: $yearAttended</p>"; 
	
	// SUCCESS MESSAGE	
	$_SESSION['message'] = 'Form submitted successfully! Thank You.';
	$_SESSION['messageType'] = 'success';
	header($HEADERLOCATION);
	exit();	
?>