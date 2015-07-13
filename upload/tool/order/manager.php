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

// Init variables
$transaction_count = 0;
$approved_count    = 0;
$pending_count     = 0;
$total_count       = 0;
$error             = array();

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

// Get pending orders
$statement = $db->prepare('SELECT `o`.`order_id`,
                                  `o`.`amount`,
                                  `o`.`user_id` AS `buyer_user_id`,
                                  `p`.`user_id` AS `seller_user_id`,
                                  `p`.`product_id`,
                                  `p`.`currency_id`,
                                  `p`.`withdraw_address`,
                                  `us`.`email` AS `seller_email`,
                                  `ub`.`email` AS `buyer_email`,
                                  `ub`.`username` AS `buyer_username`,
                                  (SELECT `title` FROM `product_description` AS `pd` WHERE `pd`.`product_id` = `p`.`product_id` AND `pd`.`language_id` = ? LIMIT 1) AS `product_title`
                                  FROM `product` AS `p`
                                  JOIN `order` AS `o` ON (`o`.`product_id` = `p`.`product_id`)
                                  JOIN `user` AS `us` ON (`us`.`user_id` = `p`.`user_id`)
                                  JOIN `user` AS `ub` ON (`ub`.`user_id` = `o`.`user_id`)
                                  WHERE `order_status_id` <> ?
                                  AND `o`.`product_id` IS NOT NULL
                                  GROUP BY `order_id`');

$statement->execute(array(DEFAULT_LANGUAGE_ID, ORDER_APPROVED_STATUS_ID));

if ($statement->rowCount()) {

    foreach ($statement->fetchAll() as $order) {

        $total_count++;

        $address_id = BITCOIN_ORDER_PREFIX . $order->order_id;

        // When order has been purchased
        if ($bitcoin->getreceivedbyaccount($address_id) > 0) {

            // If order amount is correct
            if ((float) $order->amount == (float) $bitcoin->getreceivedbyaccount($address_id)) {

                // Set order as PROCESSED
                $statement = $db->prepare('UPDATE `order` SET `order_status_id` = ? WHERE `order_status_id` <> ? AND `order_id` = ? LIMIT 1');
                $statement->execute(
                    array(
                        ORDER_PROCESSED_STATUS_ID,
                        ORDER_PROCESSED_STATUS_ID,
                        $order->order_id
                    )
                );

                if ($statement->rowCount()) {
                    $pending_count++;
                }

                // When transaction has a minimum confirmations
                if ($bitcoin->getreceivedbyaccount($address_id, BITCOIN_MIN_TRANSACTION_CONFIRMATIONS)) {

                    // New approved statuses
                    $approved_count++;

                    // Set order as APPROVED
                    $statement = $db->prepare('UPDATE `order` SET `order_status_id` = ? WHERE `order_id` = ? LIMIT 1');
                    $statement->execute(array(ORDER_APPROVED_STATUS_ID, $order->order_id));

                    // Add file quota bonus
                    $statement = $this->db->prepare('UPDATE `user` SET `file_quota` = `file_quota` + ? WHERE `user_id` = ? LIMIT 1');
                    $statement->execute(array(QUOTA_BONUS_SIZE_PER_ORDER, $order->seller_user_id));

                    // Generating a billing report
                    if ($statement->rowCount()) {

                        $fund_profit   = (float) $order->amount * FEE_PER_ORDER / 100;
                        $seller_profit = (float) $order->amount - $fund_profit;

                        // Withdraw seller profit
                        if ($transaction_id = $bitcoin->sendtoaddress(
                            $order->withdraw_address,
                            $seller_profit,
                            sprintf("[%s] Payout - Order ID %s", PROJECT_NAME, $order->order_id)
                        )) {
                            // Save transaction to the log
                            $statement = $db->prepare('INSERT INTO `log_withdraw`
                                                       SET `target` = ?,
                                                           `order_id` = ?,
                                                           `user_id` = ?,
                                                           `currency_id` = ?,
                                                           `transaction_id` = ?,
                                                           `description` = ?');

                            $statement->execute(
                                array(
                                    'seller',
                                    $order->order_id,
                                    $order->seller_user_id,
                                    $order->currency_id,
                                    $transaction_id,
                                    sprintf("[%s] Payout - Order ID %s", PROJECT_NAME, $order->order_id)
                                )
                            );

                            if ($statement->rowCount()) {
                                $transaction_count++;
                            }
                        } else {
                            $error[] = sprintf("[Seller Withdraw] %s", $bitcoin->error);
                        }

                        // Withdraw fund profit
                        if (!$error && $fund_profit > 0) {

                            if ($transaction_id = $bitcoin->sendtoaddress(
                                BITCOIN_FUND_ADDRESS,
                                $fund_profit,
                                sprintf("[%s] Profit - Order ID %s", PROJECT_NAME, $order->order_id)
                            )) {
                                // Save transaction to the log
                                $statement = $db->prepare('INSERT INTO `log_withdraw`
                                                           SET `target` = ?,
                                                               `order_id` = ?,
                                                               `user_id` = ?,
                                                               `currency_id` = ?,
                                                               `transaction_id` = ?,
                                                               `description` = ?');

                                $statement->execute(
                                    array(
                                        'fund',
                                        $order->order_id,
                                        $order->seller_user_id,
                                        $order->currency_id,
                                        $transaction_id,
                                        sprintf("[%s] Profit - Order ID %s", PROJECT_NAME, $order->order_id)
                                    )
                                );

                                if ($statement->rowCount()) {
                                    $transaction_count++;
                                }
                            } else {
                                $error[] = sprintf("[Fund Withdraw] %s", $bitcoin->error);
                            }
                        }


                        // Alert to seller
                        $output  = sprintf("Hi,\n\n");
                        $output .= sprintf("Someone has purchased your product - awesome! \n\n");
                        $output .= sprintf("%s\n", $order->product_title);
                        $output .= sprintf("Order: %s\n\n", $order->order_id);
                        $output .= sprintf("Keep it going!\n%s\n", PROJECT_NAME);

                        $mail = new Mail();
                        $mail->setTo($order->seller_email);
                        $mail->setFrom(MAIL_EMAIL_SUPPORT_ADDRESS);
                        $mail->setReplyTo(MAIL_EMAIL_SUPPORT_ADDRESS);
                        $mail->setSender(MAIL_EMAIL_SENDER_NAME);
                        $mail->setSubject(sprintf('Your product has been purchased - %s', PROJECT_NAME));
                        $mail->setText($output);
                        $mail->send();

                        // Alert to buyer
                        $output  = sprintf("Hi,\n\n");
                        $output .= sprintf("Your order ID %s has been successfully confirmed!\n\n", $order->order_id);
                        $output .= sprintf("Get it now:\n%s\n\n", URL_BASE . "search?purchased=1");
                        $output .= sprintf("Best Regards\n%s\n", PROJECT_NAME);

                        $mail = new Mail();
                        $mail->setTo($order->buyer_email);
                        $mail->setFrom(MAIL_EMAIL_SUPPORT_ADDRESS);
                        $mail->setReplyTo(MAIL_EMAIL_SUPPORT_ADDRESS);
                        $mail->setSender(MAIL_EMAIL_SENDER_NAME);
                        $mail->setSubject(sprintf('%s is ready to download - %s', $order->product_title, PROJECT_NAME));
                        $mail->setText($output);
                        $mail->send();

                        // Add notification
                        $notification = $db->prepare('INSERT INTO `user_notification` SET `user_id`     = :user_id,
                                                                                          `language_id` = :language_id,
                                                                                          `type`        = :type,
                                                                                          `title`       = :title,
                                                                                          `description` = :description,
                                                                                          `sent`        = 0,
                                                                                          `read`        = 0,
                                                                                          `date_added`  = NOW()');
                        $notification->execute(
                            array(
                                ':user_id'     => $order->seller_user_id,
                                ':language_id' => DEFAULT_LANGUAGE_ID,
                                ':type'        => 'pp', // Product purchase
                                ':title'       => 'Your product has been purchased',
                                ':description' => sprintf("@%s has purchased your product %s.\n", $order->buyer_username, $order->product_title)
                            )
                        );
                    }
                }
            } else {
                $error[] = sprintf("An amount is not match! Order amount: %s Received amount: %s", $order->amount, $bitcoin->getreceivedbyaccount($address_id));
            }
        }
    }
}


// Prepare output
$output  = sprintf("Total: %s\n", $total_count);
$output .= sprintf("Pending: %s\n", $pending_count);
$output .= sprintf("Approved: %s\n", $approved_count);
$output .= sprintf("Transactions: %s\n\n", $transaction_count);

if ($error) {
    $output .= sprintf("WARNING!\n\n");
    $output .= sprintf("%s\n", implode("\n", $error));
}

// Send a report
if ($pending_count || $approved_count || $transaction_count || $error) {

    $mail = new Mail();
    $mail->setTo(MAIL_EMAIL_BILLING_ADDRESS);
    $mail->setFrom(MAIL_EMAIL_SENDER_ADDRESS);
    $mail->setReplyTo(MAIL_EMAIL_SUPPORT_ADDRESS);
    $mail->setSender(MAIL_EMAIL_SENDER_NAME);
    $mail->setSubject(sprintf('%s REPORT', PROJECT_NAME));
    $mail->setText($output);
    $mail->send();
}

// Output
die($output);
