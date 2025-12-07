<?php
    $container = require dirname(__DIR__, 2) . '/app/bootstrap.php';
    $config    = $container['config'] ?? [];
    $logger    = $container['logger'] ?? null;
	
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
	$_POST['city'], $_POST['state'], $_POST['zipCode'],$_POST['vegan'], $_POST['vegetarian'], $_POST['applicantOrganiaztion'],  $_POST['currentTitle'], $_POST['sponsorName'], 
	$_POST['sponsorEmail'], $_POST['sponsorPhone'], $_POST['questionOne'], $_POST['questionTwo'], $_POST['questionThree'],  
	$_POST['questionFour'], $_POST['questionFive'], $_POST['scholarshipQuestion']))
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
	$organitaztion = filter_input(INPUT_POST, 'applicantOrganiaztion', FILTER_SANITIZE_SPECIAL_CHARS);
	$currentTitle = filter_input(INPUT_POST, 'currentTitle', FILTER_SANITIZE_SPECIAL_CHARS);
	$sponsorName = filter_input(INPUT_POST, 'sponsorName', FILTER_SANITIZE_SPECIAL_CHARS);
	$sponsorEmail = filter_input(INPUT_POST, 'sponsorEmail', FILTER_SANITIZE_SPECIAL_CHARS);
	$sponsorPhone = filter_input(INPUT_POST, 'sponsorPhone', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionOne = filter_input(INPUT_POST, 'questionOne', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionTwo = filter_input(INPUT_POST, 'questionTwo', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionThree = filter_input(INPUT_POST, 'questionThree', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionFour = filter_input(INPUT_POST, 'questionFour', FILTER_SANITIZE_SPECIAL_CHARS);
	$questionFive = filter_input(INPUT_POST, 'questionFive', FILTER_SANITIZE_SPECIAL_CHARS);
	$scholarshipQuestion = filter_input(INPUT_POST, 'scholarshipQuestion', FILTER_SANITIZE_SPECIAL_CHARS);
	
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
	try 
	{
                /**
                        * Mapping HTML field names → keys expected by handle_application_submission()
			*
			* forms.php expects fields:
			*   applicantFirstName, applicantLastName, applicantPreferredName, applicantPronouns,
			*   applicantEmail, applicantPhone, applicantPhoneType,
			*   addressOne, addressTwo, city, state, zip,
			*   vegan, vegetarian, diet, accommodations,
			*   org, title,
			*   supName, supEmail, supPhone,
			*   refferalQuestion, questionOne, questionTwo, questionThree, questionFour,
			*   partialScholarship, assistAmount
			*
			* apply.php HTML uses:
			*   applicantFirstName, applicantLastName, applicantPreferredName, applicantPronouns,
			*   applicantEmail, applicantPhone, applicantPhoneType,
			*   addressOne, addressTwo, city, state, zipCode,
			*   vegan, vegetarian, dietaryRestrictions, accessibilityNeeds,
			*   organiaztion, currentTitle,
			*   sponsorName, sponsorEmail, sponsorPhone,
			*   refferalQuestion, questionOne, questionTwo, questionThree, questionFour,
			*   partialScholarship, assistAmount
		*/
		
		$payload = [
        'applicantFirstName'     => trim((string)($_POST['applicantFirstName'] ?? '')),
        'applicantLastName'      => trim((string)($_POST['applicantLastName'] ?? '')),
        'applicantPreferredName' => trim((string)($_POST['applicantPreferredName'] ?? '')),
        'applicantPronouns'      => trim((string)($_POST['applicantPronouns'] ?? '')),
        'applicantEmail'         => trim((string)($_POST['applicantEmail'] ?? '')),
        'applicantPhone'         => trim((string)($_POST['applicantPhone'] ?? '')),
        'applicantPhoneType'     => trim((string)($_POST['applicantPhoneType'] ?? '')),
		
        'addressOne'             => trim((string)($_POST['addressOne'] ?? '')),
        'addressTwo'             => trim((string)($_POST['addressTwo'] ?? '')),
        'city'                   => trim((string)($_POST['city'] ?? '')),
        'state'                  => trim((string)($_POST['state'] ?? '')),
        // HTML "zipCode" → expected "zip"
        'zipCode'                    => trim((string)($_POST['zipCode'] ?? '')),
		
        'vegan'                  => trim((string)($_POST['vegan'] ?? '')),
        'vegetarian'             => trim((string)($_POST['vegetarian'] ?? '')),
        // HTML "dietaryRestrictions" → expected "diet"
        'dietaryRestrictions'                   => trim((string)($_POST['dietaryRestrictions'] ?? '')),
        // HTML "accessibilityNeeds" → expected "accommodations"
        'accessibilityNeeds'         => trim((string)($_POST['accessibilityNeeds'] ?? '')),
		
        // HTML "organiaztion" (sic) → expected "org"
        'applicantOrganiaztion'                    => trim((string)($_POST['applicantOrganiaztion'] ?? '')),
        // HTML "currentTitle" → expected "title"
        'currentTitle'                  => trim((string)($_POST['currentTitle'] ?? '')),
		
        // Sponsor / supervisor fields
        // HTML "sponsorName" → expected "supName"
        'sponsorName'                => trim((string)($_POST['sponsorName'] ?? '')),
        // HTML "sponsorEmail" → expected "supEmail"
        'sponsorEmail'               => trim((string)($_POST['sponsorEmail'] ?? '')),
        // HTML "sponsorPhone" → expected "supPhone"
        'sponsorPhone'               => trim((string)($_POST['sponsorPhone'] ?? '')),
		

        'questionOne'            => trim((string)($_POST['questionOne'] ?? '')),
        'questionTwo'            => trim((string)($_POST['questionTwo'] ?? '')),
        'questionThree'          => trim((string)($_POST['questionThree'] ?? '')),
        'questionFour'           => trim((string)($_POST['questionFour'] ?? '')),
		'questionFive'       => trim((string)($_POST['questionFive'] ?? '')),
        'scholarshipQuestion'     => trim((string)($_POST['scholarshipQuestion'] ?? '')),
        'scholarshipAmount'           => trim((string)($_POST['scholarshipAmount'] ?? '')),
		];
		
		// This will:
		//  - sanitize the payload
		//  - validate required fields + emails
		//  - append a row to the application Google Sheet
		//  - send an email notification with full application details
                handle_application_submission($config, $payload, $logger);
		
		// SUCCESS MESSAGE	
		$_SESSION['message'] = 'Application submitted successfully! Thank you.';
		$_SESSION['messageType'] = 'success';
	} 
	catch (InvalidArgumentException $e) 
	{
		// Validation error (missing required / invalid email, etc.)
		$_SESSION['message'] = $e->getMessage();
		$_SESSION['messageType'] = 'error';
	} 
        catch (Throwable $e)
        {
                if ($logger instanceof AppLogger) {
                        $logger->error('Application submission failed', [
                                'error' => $e->getMessage(),
                        ]);
                }

                $_SESSION['message'] = 'There was a problem submitting the application. Please try again later.';
                $_SESSION['messageType'] = 'error';
        }
	
	// Redirect back to contact.php with status anchor
	header($HEADERLOCATION);
	exit();	
?>