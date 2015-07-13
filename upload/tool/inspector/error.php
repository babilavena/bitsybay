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

// Load dependencies
require('../../config.php');
require('../../system/library/mail.php');

// Set log files
$filenames = array(
    '',
);

// Check logs
$errors = array();
foreach ($filenames as $filename) {
    if (file_exists($filename) && 0 < filesize($filename)) {
        $errors[] = $filename;
    }
}

// Prepare result
$result = sprintf("errors found: %s\n\n%s\n", count($errors), implode("\n", $errors));

// Send email
if ($errors) {

    $mail = new Mail();

    $mail->setTo(MAIL_EMAIL_SUPPORT_ADDRESS);
    $mail->setFrom(MAIL_EMAIL_SUPPORT_ADDRESS);
    $mail->setReplyTo(MAIL_EMAIL_SUPPORT_ADDRESS);
    $mail->setSender(sprintf('%s Notification Center', MAIL_EMAIL_SENDER_NAME));
    $mail->setSubject(sprintf('An error has occurred - %s', MAIL_EMAIL_SENDER_NAME));
    $mail->setText($result);

    $mail->send();
}

die($result);
