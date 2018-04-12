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
        $mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'user@example.com';                 // SMTP username
        $mail->Password = 'secret';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        //Recipients
        $email = $_REQUEST["emailid"];
        $mail->setFrom('from@example.com', 'Mailer');
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
}

if (isset($_REQUEST["receivemails"]) && $_REQUEST["receivemails"] == "yes") {

    $server = new Server('imap.zoho.com');

// $connection is instance of \Ddeboer\Imap\Connection
    $connection = $server->authenticate('rakshit@3iology.com', ']b/4G83VfwN6"~+A');

    $server = new Server(
        'imap.zoho.com', // required
        993,     // defaults to '993'
        '/imap/ssl/validate-cert'    // defaults to '/imap/ssl/validate-cert'
    );

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

    foreach ($messages as $message) {
        // $message is instance of \Ddeboer\Imap\Message
        $message->getBodyText();
        var_dump($email_from = $message->getFrom()->getAddress());
        $user_id = find_userid_from_email($conn,$email_from);
        if ($user_id == null) {
            echo "Not a registered User";
            continue;
        }
        $eid = insert_email_content($conn, $message->getBodyText(), $message->getBodyHtml(), $user_id);    // userid to be inserted
        $attachments = $message->getAttachments();
        foreach ($attachments as $attachment) {
            $aid = insert_email_attachments($conn, $attachment->getFilename(), $eid);
//        echo $attachment->getFilename() . '<br>';
            // $attachment is instance of \Ddeboer\Imap\Message\Attachment
            file_put_contents(
                './download-attachment/' . $eid . $attachment->getFilename(),
                $attachment->getDecodedContent()
            );
        }
        $mailbox = $connection->getMailbox('Finished-Parsing');
        $message->move($mailbox);
        $connection->expunge();
    }
}