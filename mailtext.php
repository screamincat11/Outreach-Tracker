<?php
require_once 'inc/swift/lib/swift_required.php';

// Mail Transport
$transport = Swift_SmtpTransport::newInstance('ssl://smtp.gmail.com', 465)
    ->setUsername('illuminatingtexts@gmail.com') // Your Gmail Username
    ->setPassword('ilovemyduck'); // Your Gmail Password
// Mailer
$mailer = Swift_Mailer::newInstance($transport);

// Create the message
$message = Swift_Message::newInstance()

  // Give the message a subject
  ->setSubject('Your subject')

  // Set the From address with an associative array
  ->setFrom(array('illuminatingtext@gmail.com', 'illuminatingtext@gmail.com' => 'Illuminating Texts'))

  // Set the To addresses with an associative array
  ->setTo(array('bossduck@gmail.com', 'bossduck@gmail.com' => 'Ryan Sullivan'))

  // Give it a body
  ->setBody('Here is the message itself')

  ;

// Send the message
if ($mailer->send($message)) {
    echo 'Mail sent successfully.';
} else {
    echo 'I am sure, your configuration are not correct. :(';
}


?>
