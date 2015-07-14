<?php

/**
 * LICENSE
 *
 * This source file is subject to the GNU General Public License, Version 3
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @package    BitsyBay Engine
 * @copyright  Copyright (c) 2015 The BitsyBay Project (http://bitsybay.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License, Version 3
 */

die('unsupported'); // todo

// Build the template
$subject = "The Licensing Policy has been updated - %s";
$body    =
"Hi, %s

Our Licensing Policy has been updated!

Please read this Licensing Policy carefully, and contact us if you have any questions:
%s

If you do not want to receive further messages, you can change your notification preferences here:
%s

Best Regards
%s";


// Load dependencies
require('../../config.php');
require('../../system/library/mail.php');

// Init the database
try {
    $db = new PDO(
        'mysql:dbname=' . DB_DATABASE . ';host=' . DB_HOSTNAME . ';charset=utf8',
        DB_USERNAME,
        DB_PASSWORD,
        array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        )
    );

    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $db->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_OBJ
    );

} catch(PDOException $e) {
    $error[] = $e->getMessage();
    exit;
}

$i_total = 0;
$i_sent  = 0;
$mail    = new Mail();

// Get registered users
$statement = $db->prepare('SELECT `user_id`, `username`, `email`, `notify_au` FROM `user`');
$statement->execute();

if ($statement->rowCount()) {

    foreach ($statement->fetchAll() as $user) {

        // If subscribed
        if ($user->notify_au == 1) { // todo

            // Send email
            $mail->setFrom(MAIL_EMAIL_SUPPORT_ADDRESS);
            $mail->setReplyTo(MAIL_EMAIL_SUPPORT_ADDRESS);
            $mail->setSender(sprintf('%s Notification Center', MAIL_EMAIL_SENDER_NAME));
            $mail->setTo($user->email);
            $mail->setSubject(sprintf($subject, PROJECT_NAME));
            $mail->setText(sprintf($body, $user->username, URL_BASE . 'licenses', URL_BASE . 'notification', PROJECT_NAME));
            $mail->send();

            $i_sent++;
        }



        // Add notification
        $notification = $db->prepare('INSERT INTO `user_notification` SET `user_id`     = :user_id,
                                                                          `language_id` = :language_id,
                                                                          `label`       = :label,
                                                                          `title`       = :title,
                                                                          `description` = :description,
                                                                          `sent`        = :sent,
                                                                          `read`        = 0,
                                                                          `date_added`  = NOW()');
        $notification->execute(
            array(
                ':user_id'     => $user->user_id,
                ':sent'        => $user->notify_au,
                ':language_id' => DEFAULT_LANGUAGE_ID,
                ':label'       => 'news',
                ':title'       => 'Licensing Policy has been updated',
                ':description' => 'Please read our Licensing Policy carefully, and contact us if you have any questions.'
            )
        );

        $i_total++;

    }
}

die(sprintf('total: %s sent: %s', $i_total, $i_sent));
