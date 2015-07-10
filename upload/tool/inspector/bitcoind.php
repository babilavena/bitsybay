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

// Init BitCoin
$bitcoin = new BitCoin(
    BITCOIN_RPC_USERNAME,
    BITCOIN_RPC_PASSWORD,
    BITCOIN_RPC_HOST,
    BITCOIN_RPC_PORT
);

// Check daemon status, sometimes it going down
if (false === $bitcoin->getinfo()) echo system(BITCOIN_DAEMON_PATH);
echo sprintf("status: %s\n", $bitcoin->status);
