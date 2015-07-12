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

// Get registered users
$statement = $db->prepare('SELECT `username`, `email` FROM `user` WHERE `notify_au` = 1');

$statement->execute();

if ($statement->rowCount()) {

    foreach ($statement->fetchAll() as $user) {

        // Send email
        $mail = new Mail();
        $mail->setFrom(MAIL_INFO);
        $mail->setReplyTo(MAIL_INFO);
        $mail->setSender(sprintf('%s Notification Center', MAIL_SENDER));
        $mail->setTo($user->email);
        $mail->setSubject(sprintf($subject, PROJECT_NAME));
        $mail->setText(sprintf($body, $user->username, URL_BASE . 'licenses', URL_BASE . 'notification', PROJECT_NAME));
        $mail->send();
    }
}



