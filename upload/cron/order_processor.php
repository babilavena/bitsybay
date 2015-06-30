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

// Recommend to update: 60 min

// Load dependencies
require_once('../config.php');
require_once('../system/library/bitcoin.php');
require_once('../system/library/mail.php');

// Debug mode
if (ORDER_PROCESSOR_DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Init variables
$approved_order_data = array();
$text = '';

// Init Database
try {
    $db = new PDO('mysql:dbname=' . DB_DATABASE . ';host=' . DB_HOSTNAME . ';charset=utf8', DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch(PDOException $e) {
    trigger_error($e->getMessage());
    exit;
}

// Init BitCoin
try {
    $bitcoin = new BitCoin(BITCOIN_RPC_USERNAME, BITCOIN_RPC_PASSWORD, BITCOIN_RPC_HOST, BITCOIN_RPC_PORT);

} catch (Exception $e) {
    trigger_error($bitcoin->error . '/' . $e->getMessage());
    exit;
}

// Get pending orders
$statement = $db->prepare('SELECT `order_id`, `user_id`, `amount` FROM `order` WHERE `order_status_id` <> ?');
$statement->execute(array(ORDER_APPROVED_STATUS_ID));

if ($statement->rowCount()) {

    foreach ($statement->fetchAll() as $order) {

        // Order has ben purchased
        if ($bitcoin->getreceivedbyaccount($order->order_id) > 0) {

            // If order amount is correct & already has minimum confirmations
            if ((float) $order->amount == (float) $bitcoin->getreceivedbyaccount($order->order_id)) {

                // Set order as PROCESSED
                $statement = $db->prepare('UPDATE `order` SET `order_status_id` = ? WHERE `order_status_id` <> ? AND `order_id` = ? LIMIT 1');
                $statement->execute(array(ORDER_PROCESSED_STATUS_ID, ORDER_PROCESSED_STATUS_ID, $order->order_id));

                // todo: Send notices to the customer & merchant

                // Set order as APPROVED
                if ($bitcoin->getreceivedbyaccount($order->order_id, ORDER_PROCESSOR_MIN_BTC_TRANSACTION_CONF_TO_ORDER_APPROVE)) {

                    $statement = $db->prepare('UPDATE `order` SET `order_status_id` = ? WHERE `order_id` = ? LIMIT 1');
                    $statement->execute(array(ORDER_APPROVED_STATUS_ID, $order->order_id));

                    // Collect the billing report
                    if ($statement->rowCount()) {

                        $ordered_product = $db->prepare('SELECT `p`.`product_id`, `p`.`currency_id`, `o`.`amount`, `p`.`user_id`, `p`.`withdraw_address` FROM `product` AS `p` JOIN `order` AS `o` ON (`o`.`product_id` = `p`.`product_id`) WHERE `o`.`order_id` = ? LIMIT 1');
                        $ordered_product->execute(array($order->order_id));

                        if ($ordered_product->rowCount()) {

                            $ordered_product_info = $ordered_product->fetch();

                            $approved_order_data[$order->order_id] = array(
                                'buyer_id' => $order->user_id,
                                'seller_id' => $ordered_product_info->user_id,
                                'product_id' => $ordered_product_info->product_id,
                                'currency_id' => $ordered_product_info->currency_id,
                                'withdraw_address' => $ordered_product_info->withdraw_address,
                                'amount' => $ordered_product_info->amount,
                                'date' => date('d.m.y H:i:s'),
                            );

                            // todo: Send congratulations to the customer & merchant

                        }
                    }
                }


            // If order total is wrong
            } else if ($bitcoin->getreceivedbyaccount($order->order_id, ORDER_PROCESSOR_MIN_BTC_TRANSACTION_CONF_TO_ORDER_APPROVE) > 0) {

                // Trow exception
                $response = sprintf("\n\nWARNING! Received (%s) and Order (%s) amount is not match in the Order #%s.\n\n", (float) $bitcoin->getreceivedbyaccount($order->order_id), (float) $order->amount, $order->order_id);
                $text .= $response;
            }

        }
    }
}

// Prepare billing info
if ($approved_order_data) {

    $text .= "\n\nAPPROVED ORDERS\n\n";
    foreach ($approved_order_data as $order_id => $approved_order) {
        $text .= sprintf("Order #%s\n\n", $order_id);
        $text .= sprintf("\tBuyer #%s\n", $approved_order['buyer_id']);
        $text .= sprintf("\tSeller #%s\n", $approved_order['seller_id']);
        $text .= sprintf("\tProduct #%s\n", $approved_order['product_id']);
        $text .= sprintf("\tAmount: %s / Currency #%s\n", $approved_order['amount'], $approved_order['currency_id']);
        $text .= sprintf("\tWithdraw address: %s\n", $approved_order['withdraw_address']);
        $text .= sprintf("\tApproved time: %s\n\n", $approved_order['date']);
    }
}

// Withdraw to the Bank Storage if Current Balance more than 0
$total_balance = $bitcoin->getbalance();
if (BITCOIN_STORAGE_WITHDRAW_ENABLED && 0 < $total_balance && $total_balance > BITCOIN_STORAGE_WITHDRAW_MINIMUM_AMOUNT) {
    if ($bitcoin->sendtoaddress(BITCOIN_STORAGE_WITHDRAW_ADDRESS, $total_balance, 'BITSYBAY BACKUP')) {
        $text .= "\n\nFund " . $total_balance . ' BTC has been send to the reserve address ' . BITCOIN_STORAGE_WITHDRAW_ADDRESS . ' at ' . date('d.m.y H:i:s');
    } else {
        $text .= "\n\nFund " . $total_balance . ' BTC has not been send to the reserve address ' . BITCOIN_STORAGE_WITHDRAW_ADDRESS . ' at ' . date('d.m.y H:i:s');
        $text .= sprintf("\nReason: %s", $bitcoin->error);
    }
}

// Send billing report
if (!empty($text)) {
    $mail = new Mail();
    $mail->setTo(MAIL_BILLING);
    $mail->setFrom(MAIL_FROM);
    $mail->setReplyTo(MAIL_INFO);
    $mail->setSender(MAIL_SENDER);
    $mail->setSubject('BitsyBay - ORDER PROCESSOR REPORT');
    $mail->setText($text);
    $mail->send();
}

// Output response
die('Done.');
