<?php
    $container = require __DIR__ . '/../app/bootstrap.php';
    $config    = $container['config'] ?? [];
    $logger    = $container['logger'] ?? null;
	
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
	if(!isset($_POST['contactFirstName'], $_POST['contactLastName'], $_POST['contactEmail'],$_POST['phone'], $_POST['phoneType'], $_POST['yearAttended']))
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
	$contactPhone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
	$contactPhoneType = filter_input(INPUT_POST, 'phoneType', FILTER_SANITIZE_SPECIAL_CHARS);
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
	try 
	{
                // Map POST values to the keys expected by handle_contact_submission()
                // forms.php expects:
                //   contactFirstName, contactLastName, contactEmail, phone,
                //   phoneType, currentOrganization, yearAttended
		$payload = [
        'contactFirstName' => trim((string)($_POST['contactFirstName'] ?? '')),
        'contactLastName'  => trim((string)($_POST['contactLastName'] ?? '')),
        'contactEmail'     => trim((string)($_POST['contactEmail'] ?? '')),
        'phone'     => trim((string)($_POST['phone'] ?? '')),
        'phoneType' => trim((string)($_POST['phoneType'] ?? '')),
        // HTML name="currentOrganization" → expected key "currentOrganization"
        'currentOrganization'      => trim((string)($_POST['currentOrganization'] ?? '')),
        'yearAttended'     => trim((string)($_POST['yearAttended'] ?? '')),
		];
		
		// This will:
		//  - sanitize the payload
		//  - validate required fields + email
		//  - append a row to the contact Google Sheet
		//  - send an email notification
                handle_contact_submission($config, $payload, $logger);
		
		// SUCCESS MESSAGE	
		$_SESSION['message'] = 'Updated contact information submitted successfully! Thank you.';
		$_SESSION['messageType'] = 'success';
		
	} 
	catch (InvalidArgumentException $e) 
	{
		// Validation error (missing field, invalid email, etc.)
		$_SESSION['message'] = $e->getMessage();
		$_SESSION['messageType'] = 'error';
	} 
        catch (Throwable $e) // Any other unexpected error (Sheets / Gmail / config issues, etc.)
        {
                if ($logger instanceof AppLogger) {
                        $logger->error('Contact form submission failed', [
                                'error' => $e->getMessage(),
                        ]);
                }

                $_SESSION['message'] = 'There was a problem submitting the form. Please try again later.';
                $_SESSION['messageType'] = 'error';
        }
	
	// Redirect back to contact.php with status anchor
	header($HEADERLOCATION);
	exit();	
?>