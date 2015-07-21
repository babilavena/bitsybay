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

/*
 * Configuration: BEGIN
 */

$subject = "";
$body    = "";

$module_link_enabled = true;
$module_link_title   = "";
$module_link_href    = "";

/*
 * Configuration: END
 */

// Init helpers
function helper_load_view($template, array $data = array()) {

    $file = DIR_BASE . 'application' . DIR_SEPARATOR . 'view' . DIR_SEPARATOR . $template;

    if (file_exists($file)) {
        extract($data);
        ob_start();
        require($file);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    } else {

        trigger_error('Error: Could not load template ' . $file . '!');
        return false;
    }
}

function tt($string) {
    return htmlentities($string);
}

// Init mail
$mail = new Mail();
$mail->setFrom(MAIL_EMAIL_SUPPORT_ADDRESS);
$mail->setReplyTo(MAIL_EMAIL_SUPPORT_ADDRESS);
$mail->setSender(MAIL_EMAIL_SENDER_NAME);

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

// Get subscribers users
$statement = $db->prepare('SELECT `u`.`user_id`,
                                  `u`.`username`,
                                  `u`.`email`,
                                  (SELECT COUNT(*)
                                      FROM `user_subscription` AS `us`
                                      WHERE `us`.`user_id` = `u`.`user_id` AND `subscription_id` = ?) as `subscribed`
                                FROM `user` AS `u`');

$statement->execute(array(PROJECT_NEWS_SUBSCRIPTION_ID));

if ($statement->rowCount()) {

    foreach ($statement->fetchAll() as $user) {

        // Check subscription
        if ($user->subscribed == 1) {

            // Send email
            $mail_data['project_name'] = PROJECT_NAME;

            $mail_data['subject'] = $subject;
            $mail_data['message'] = $body;

            $mail_data['href_home']         = URL_BASE;
            $mail_data['href_contact']      = URL_BASE . 'contact';
            $mail_data['href_subscription'] = URL_BASE . 'subscriptions';

            $mail_data['href_facebook'] = URL_FACEBOOK;
            $mail_data['href_twitter']  = URL_TWITTER;
            $mail_data['href_tumblr']   = URL_TUMBLR;
            $mail_data['href_github']   = URL_GITHUB;

            if (!empty($module_link_enabled)) {
                $mail_data['module_link_title'] = $module_link_title;
                $mail_data['module_link_href']  = $module_link_href;
                $mail_data['module'] = helper_load_view('email/module/link.tpl', $mail_data);
            }

            $mail->setTo($user->email);
            $mail->setSubject($subject);
            $mail->setHtml(helper_load_view('email/common.tpl', $mail_data));
            $mail->send();

            $i_sent++;
        }

        // Add notification
        $notification = $db->prepare('INSERT INTO `user_notification` SET `user_id`     = :user_id,
                                                                          `language_id` = :language_id,
                                                                          `label`       = :label,
                                                                          `title`       = :title,
                                                                          `description` = :description,
                                                                          `read`        = 0,
                                                                          `date_added`  = NOW()');
        $notification->execute(
            array(
                ':user_id'     => $user->user_id,
                ':language_id' => DEFAULT_LANGUAGE_ID,
                ':label'       => 'news',
                ':title'       => $subject,
                ':description' => $body
            )
        );

        $i_total++;
    }
}

die(sprintf('total: %s sent: %s', $i_total, $i_sent));
