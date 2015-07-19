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
require('../../system/library/bitcoin.php');
require('../../system/library/mail.php');

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

// Init Database
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

// Init BitCoin
try {
    $bitcoin = new BitCoin(
        BITCOIN_RPC_USERNAME,
        BITCOIN_RPC_PASSWORD,
        BITCOIN_RPC_HOST,
        BITCOIN_RPC_PORT
    );

} catch (Exception $e) {
    $error[] = $bitcoin->error . '/' . $e->getMessage();
    exit;
}

// Init variables
$total_count       = 0;
$processed_count   = 0;
$transaction_count = 0;

// Get pending orders
$statement = $db->prepare('SELECT `uvr`.`user_verification_id`,
                                  `uvr`.`user_id`,
                                  `uvr`.`amount`,
                                  `u`.`username`,
                                  `u`.`email`,
                                  `a`.`user_id` AS `affiliate_user_id`,
                                  `a`.`email` AS `affiliate_email`,
                                  `a`.`affiliate_address`

                                FROM `user_verification_request` AS `uvr`
                                LEFT JOIN `user` AS `u` ON (`u`.`user_id` = `uvr`.`user_id`)
                                LEFT JOIN `user` AS `a` ON (`a`.`user_id` = `u`.`referrer_user_id`)
                                WHERE `status` <> ?');

$statement->execute(array('approved'));

if ($statement->rowCount()) {

    foreach ($statement->fetchAll() as $request) {

        $total_count++;

        $address_id = BITCOIN_USER_VERIFICATION_PREFIX . $request->user_id;

        // When order has been purchased
        if ((float) $bitcoin->getreceivedbyaccount($address_id) >= (float) $request->amount &&    // Check amount
            $bitcoin->getreceivedbyaccount($address_id, BITCOIN_MIN_TRANSACTION_CONFIRMATIONS)) { // Transaction has minimum confirmations

            // Increase counter
            $processed_count++;

            // Set order as PROCESSED
            $statement = $db->prepare('UPDATE `user_verification_request` SET `status` = ? WHERE `user_verification_id` = ? LIMIT 1');
            $statement->execute(
                array(
                    'processed',
                    $request->user_verification_id
                )
            );

            $affiliate_profit = round($request->amount - ($request->amount * FEE_USER_VERIFICATION_AFFILIATE / 100), 8);
            $fund_profit      = round($request->amount - $affiliate_profit, 8);

            // Withdraw affiliate profit
            if ($transaction_id = $bitcoin->sendtoaddress(
                $request->affiliate_address,
                $affiliate_profit,
                sprintf("%s Payout to Affiliate ID %s - User Verification ID %s", PROJECT_NAME, $request->affiliate_user_id, $request->user_verification_id)
            )) {
                $transaction_count++;
            } else {
               $error[] = sprintf("[Affiliate Withdraw] %s", $bitcoin->error);
            }

            // Withdraw fund profit
            if (!$error && $fund_profit > 0) {

                if ($transaction_id = $bitcoin->sendtoaddress(
                    BITCOIN_FUND_ADDRESS,
                    $fund_profit,
                    sprintf("%s Profit - User Verification ID %s", PROJECT_NAME, $request->user_verification_id)
                )) {
                    $transaction_count++;
                } else {
                    $error[] = sprintf("[Fund Withdraw] %s", $bitcoin->error);
                }
            }


            // Add affiliate notification
            $notification = $db->prepare('INSERT INTO `user_notification` SET `user_id`     = :user_id,
                                                                              `language_id` = :language_id,
                                                                              `label`       = :label,
                                                                              `title`       = :title,
                                                                              `description` = :description,
                                                                              `read`        = 0,
                                                                              `date_added`  = NOW()');
            $notification->execute(
                array(
                    ':user_id'     => $request->affiliate_user_id,
                    ':language_id' => DEFAULT_LANGUAGE_ID,
                    ':label'       => 'activity',
                    ':title'       => 'New verification request from you affiliate',
                    ':description' => sprintf("@%s was send a new verification request. Good job!", $request->username)
                )
            );

            // Send cheers to email
            $mail_data['project_name'] = PROJECT_NAME;

            $mail_data['subject'] = sprintf('New verification request from you affiliate - %s', PROJECT_NAME);
            $mail_data['message'] = sprintf("@%s was send a new verification request. Good job!", $request->username);

            $mail_data['href_home']         = URL_BASE;
            $mail_data['href_contact']      = URL_BASE . 'contact';
            $mail_data['href_subscription'] = URL_BASE . 'subscriptions';

            $mail_data['href_facebook'] = URL_FACEBOOK;
            $mail_data['href_twitter']  = URL_TWITTER;
            $mail_data['href_tumblr']   = URL_TUMBLR;
            $mail_data['href_github']   = URL_GITHUB;

            $mail->setTo($request->affiliate_email);
            $mail->setSubject($mail_data['subject']);
            $mail->setHtml(helper_load_view('email/common.tpl', $mail_data));
            $mail->send();

            if ((float) $bitcoin->getreceivedbyaccount($address_id) != (float) $request->amount) {
                $error[] = sprintf("Amount is not match! Required amount: %s Received amount: %s", (float) $request->amount, (float) $bitcoin->getreceivedbyaccount($address_id));
            }
        }
    }
}


// Prepare output
$output  = sprintf("Total: %s\n", $total_count);
$output .= sprintf("Pending: %s\n", $processed_count);
$output .= sprintf("Transactions: %s\n\n", $transaction_count);

if ($error) {
    $output .= sprintf("WARNING!\n\n");
    $output .= sprintf("%s\n", implode("\n", $error));
}

// Send report
if ($processed_count || $transaction_count || $error) {
    $mail->setTo(MAIL_EMAIL_BILLING_ADDRESS);
    $mail->setSubject(sprintf('%s REPORT', PROJECT_NAME));
    $mail->setHtml(false);
    $mail->setText($output);
    $mail->send();
}

// Output
die($output);
