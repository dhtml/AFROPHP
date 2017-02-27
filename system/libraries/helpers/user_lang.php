<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * allow public user registration
 * 1 -  Only site administrators can create new user accounts.
 * 2 -  Visitors can create accounts and no administrator approval is required.
 */
$lang['public_user_registration'] = 2;  

/**
 * require email verification
 * 0 - do not require
 * 1 - require email verification
 */
$lang['registration_require_email'] = 1;  

/**
 * message to show above user registration
 */
$lang['user_registration_help'] = "By clicking Join now, you agree to %site_name% <a href=\"%site_url%/terms\">terms</a> and <a href=\"%site_url%/privacy\">privacy policy</a>.";

// Account verification
$lang['user_email_verification_subject'] = "Hi %first_name%, please verify your account on %site_name%";
$lang['user_email_verification_message'] = "Dear %first_name% %last_name%,<br/><br/>
Congratulations for registering on %site_name% - your favourite social network for developers.<br/><br/>

At %site_name%, we're always trying to make improve your user experience. <br/><br/>

In order to access your account, you need to verify your account.<br/><br/>

Your verification code is shown below:<br/>
<b>%verification%</b><br/><br/>

You can also click on (or copy and paste) the following link:<br/>
<a href=\"%verify_link%\">%verify_link%</a><br/><br/>
 
Warm Regards,<br/>
%site_name% Team 
";


//welcome message
$lang['user_email_welcome_subject'] = "Hi %first_name%, welcome to %site_name%";
$lang['user_email_welcome_message'] = "Dear %first_name% %last_name%,<br/><br/>
Congratulations for registering on %site_name% - your favourite social network for developers.<br/><br/>

At %site_name%, we're always trying to make improve your user experience. <br/><br/>

 
Warm Regards,<br/>
%site_name% Team 
";


$lang['user_email_verification_code'] = " You can also click on (or copy and paste) the following link\n\n";
$lang['user_email_verification_text'] = " You can also click on (or copy and paste) the following link\n\n";

// Password reset
$lang['user_email_reset_subject'] = 'Recover Your %site_name% Account';
$lang['user_email_reset_message'] = "Hi %first_name% %last_name%,<br/><br/>
We received a request to reset the password for your account.<br/>
If you requested a reset for @%username%, then follow the instructions below. If you didn’t make this request, please ignore this email.
<br/><br/>

You need to click on (or copy and paste) the following link:<br/>
<a href=\"%verify_link%\">%verify_link%</a><br/><br/>
 
Warm Regards,<br/>
%site_name% Team 
";


// Password reset success
$lang['user_email_reset_success_subject'] = 'Your %site_name% password has been changed';
$lang['user_email_reset_success_message'] = "
Hi %first_name% %last_name%,<br/><br/>
You recently changed the password associated with your account @%username%.<br/>
If you did not make this change and believe your account has been compromised, please contact <a href=\"%site_url%contact\">support</a>.
<br/><br/>
 
Warm Regards,<br/>
%site_name% Team 
";



// Account creation errors
$lang['user_error_first_required'] = 'Please enter your first name';
$lang['user_error_last_required'] = 'Please enter your last name';
$lang['user_error_email_required'] = 'Please enter your email';
$lang['user_error_phone_required'] = 'Please enter your phone';
$lang['user_error_sex_required'] = 'Please select your sex';

$lang['user_error_email_exists'] = 'Your email has already been taken';
$lang['user_error_username_exists'] = 'Your username has already been taken';
$lang['user_error_email_invalid'] = 'Your email address is invalid';
$lang['user_error_phone_invalid'] = 'Your phone is invalid';
$lang['user_error_password_invalid'] = 'Your password must be at least 6 characters';
$lang['user_error_username_invalid'] = 'Your username is not valid.';
$lang['user_error_username_required'] = 'Username required';
$lang['user_error_totp_code_required'] = 'Authentication Code required';
$lang['user_error_totp_code_invalid'] = 'Invalid Authentication Code';

$lang['user_error_birthday_invalid'] = 'Your birthday is not valid';

$lang['user_error_cannot_create'] = 'Issues with account creation, try again later';


// Account update errors
$lang['user_error_update_email_exists'] = 'Email address already exists on the system.  Please enter a different email address.';
$lang['user_error_update_username_exists'] = "Username already exists on the system.  Please enter a different username.";


// Access errors
$lang['user_error_no_access'] = 'Sorry, you do not have access to the resource you requested.';
$lang['user_error_login_failed_email'] = 'E-mail Address and Password do not match.';
$lang['user_error_login_failed_name'] = 'Username and Password do not match.';
$lang['user_error_login_failed_all'] = 'E-mail, Username or Password do not match.';
$lang['user_error_login_attempts_exceeded'] = 'You have exceeded your login attempts, your account has now been locked.';
$lang['user_error_recaptcha_not_correct'] = 'Sorry, the reCAPTCHA text entered was incorrect.';

// Misc. errors
$lang['user_error_no_user'] = 'Your credentials do not match, please double-check';
$lang['user_error_is_suspended'] = 'Your account has been suspended';
$lang['user_error_pend_activate'] = 'Your account requires activation';
$lang['user_error_is_active'] = 'This account is already active, please <a href="./login">login here</a>.';

$lang['user_error_cant_create'] = 'Your account cannot be created, please <a href="./signup">signup here</a>.';



$lang['user_error_missing'] = 'There is no such account here, please <a href="./signup">create account</a>.';

$lang['user_verification_expired'] = 'Your verification code has expired, please <a href="./begin_password_reset">click here</a>.';



//we want all login errors to show similar messages to confuse hackers
$lang['user_error_login_failed_email']=$lang['user_error_no_user'];
$lang['user_error_login_failed_name']=$lang['user_error_no_user'];
$lang['user_error_login_failed_all']=$lang['user_error_no_user'];

$lang['user_error_account_not_verified'] = 'Your account has not been verified.  please <a href="./resend_verification">click here</a>.';
$lang['user_error_no_group'] = 'Group does not exist';
$lang['user_error_no_subgroup'] = 'Subgroup does not exist';
$lang['user_error_self_pm'] = 'It is not possible to send a Message to yourself.';
$lang['user_error_no_pm'] = 'Private Message not found';

$lang['user_error_no_account'] = 'We could not find your account on %site_name%';


/* Info messages */
$lang['user_info_already_member'] = 'User is already member of group';
$lang['user_info_already_subgroup'] = 'Subgroup is already member of group';
$lang['user_info_group_exists'] = 'Group name already exists';
$lang['user_info_perm_exists'] = 'Permission name already exists';

/* misc messages */
$lang['signup_subheader'] = 'Hangout with core developers today!';


