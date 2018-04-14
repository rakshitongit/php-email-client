<?php
/**
 * Created by PhpStorm.
 * User: rsb
 * Date: 12/4/18
 * Time: 6:54 PM
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Ddeboer\Imap\Server;

//Load Composer's autoloader
require 'vendor/autoload.php';
if (isset($_REQUEST["sendmail"]) && $_REQUEST["sendmail"] == "yes") {
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        //Server settings
        //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;// Enable SMTP authentication
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->Username = '';                 // SMTP username
        $mail->Password = '';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        //Recipients
        $email = $_REQUEST["emailid"];
        $mail->setFrom('Your Email Id', 'Your Name');
        $mail->addAddress($email);     // Add a recipient
        /*$mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('info@example.com', 'Information');
        $mail->addCC('cc@example.com');
        $mail->addBCC('bcc@example.com');*/

        //Attachments
//    $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $_REQUEST["subject"];
        $mail->Body = $_REQUEST["message"];
//    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'success';
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
    exit;
}


$server = new Server('Your mailer imap url');   // Eg for Gmail imap.gmail.com

// $connection is instance of \Ddeboer\Imap\Connection
$connection = $server->authenticate('youremail@mail.com', 'your email-password');

//$mailboxs = $connection->getMailboxes();
//foreach ($mailboxs as $mailbox) {
//    echo $mailbox->getName().'<br>';
//}

$mailbox = $connection->getMailbox('INBOX');

//var_dump($mailbox);

$messages = $mailbox->getMessages();
//$today = new DateTimeImmutable();
//$lastMonth = $today->sub(new DateInterval('P2D'));
//
//$messages = $mailbox->getMessages(
//    new Ddeboer\Imap\Search\Date\Since($lastMonth),
//    \SORTDATE, // Sort criteria
//    true // Descending order
//);
//var_dump($messages);
$data = array();
foreach ($messages as $message) {
    // $message is instance of \Ddeboer\Imap\Message
    $temp["body"] = $message->getBodyText();
    $temp["email"] = $message->getFrom()->getAddress();
    $temp["subject"] = $message->getSubject();
    $data[] = $temp;
}
//print_r($data);