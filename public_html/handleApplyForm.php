<?php
	require_once __DIR__ . '/config.php';
	
	//Header Location
	$HEADERLOCATION = 'Location: apply.php#applyStatus';
	
	// VALIDATE HTTP REQUEST METHOD
	if($_SERVER['REQUEST_METHOD'] !== 'POST')
	{
		$_SESSION['message'] = 'Invalid request method. Please use the form to submit data.';
		$_SESSION['messageType'] = 'error';
		header($HEADERLOCATION);
		exit();
	}
	
	// VALIDATE HTTP REQUEST PARAMETERS
	if(!isset($_POST['applicantFirstName'], $_POST['applicantLastName'], $_POST['applicantEmail'],$_POST['applicantPhone'], $_POST['applicantPhoneType'], $_POST['addressOne'],
		$_POST['city'], $_POST['state'], $_POST['zipCode'],$_POST['vegan'], $_POST['vegetarian'], $_POST['organitaztion'],  $_POST['currentTitle'], $_POST['sponsorName'], 
		$_POST['sponsorEmail'], $_POST['sponsorPhone'], $_POST['refferalQuestion'],$_POST['questionOne'], $_POST['questionTwo'], $_POST['questionThree'],  
		$_POST['questionFour'], $_POST['partialScholarship']))
	{
		$_SESSION['message'] = 'Invalid request parameters. One or more required form parameters is missing.';
		$_SESSION['messageType'] = 'error';
		header($HEADERLOCATION);
		exit();		
	}
		
	// SANITIZE HTTP REQUEST PARAMETERS	
	
	$applicantFirstName = filter_input(INPUT_POST, 'applicantFirstName', FILTER_SANITIZE_SPECIAL_CHARS);
	$applicantLastName = filter_input(INPUT_POST, 'applicantLastName', FILTER_SANITIZE_SPECIAL_CHARS);
	$applicantEmail = filter_input(INPUT_POST, 'applicantEmail', FILTER_SANITIZE_SPECIAL_CHARS);
	$applicantPhone = filter_input(INPUT_POST, 'applicantPhone', FILTER_SANITIZE_SPECIAL_CHARS);
	$applicantPhoneType = filter_input(INPUT_POST, 'applicantPhoneType', FILTER_SANITIZE_SPECIAL_CHARS);
	$addressOne = filter_input(INPUT_POST, 'addressOne', FILTER_SANITIZE_SPECIAL_CHARS);
	$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
	$state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_SPECIAL_CHARS);
	$zipCode = filter_input(INPUT_POST, 'zipCode', FILTER_SANITIZE_SPECIAL_CHARS);
	$vegan = filter_input(INPUT_POST, 'vegan', FILTER_SANITIZE_SPECIAL_CHARS);
	$vegetarian = filter_input(INPUT_POST, 'vegetarian', FILTER_SANITIZE_SPECIAL_CHARS);
	$organitaztion = filter_input(INPUT_POST, 'organitaztion', FILTER_SANITIZE_SPECIAL_CHARS);
	$currentTitle = filter_input(INPUT_POST, 'currentTitle', FILTER_SANITIZE_SPECIAL_CHARS);
	$sponsorName = filter_input(INPUT_POST, 'sponsorName', FILTER_SANITIZE_SPECIAL_CHARS);
	$sponsorEmail = filter_input(INPUT_POST, 'sponsorEmail', FILTER_SANITIZE_SPECIAL_CHARS);
	$sponsorPhone = filter_input(INPUT_POST, 'sponsorPhone', FILTER_SANITIZE_SPECIAL_CHARS);
	$refferalQuestion = filter_input(INPUT_POST, 'refferalQuestion', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionOne = filter_input(INPUT_POST, 'questionOne', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionTwo = filter_input(INPUT_POST, 'questionTwo', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionThree = filter_input(INPUT_POST, 'questionThree', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionFour = filter_input(INPUT_POST, 'questionFour', FILTER_SANITIZE_SPECIAL_CHARS);
	$partialScholarship = filter_input(INPUT_POST, 'partialScholarship', FILTER_SANITIZE_SPECIAL_CHARS);
	
	// ERRORS ARRAY
	$errors = [];
	
	// VALIDATE EMAIL
	if(!filter_var($applicantEmail, FILTER_VALIDATE_EMAIL) || !filter_var($sponsorEmail, FILTER_VALIDATE_EMAIL))
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