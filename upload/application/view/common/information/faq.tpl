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
 ?>

<?php echo $header ?>
  <div class="row">
    <div class="col-lg-12">
      <div class="page-header">
        <h1 id="forms"><?php echo tt('General F.A.Q.') ?></h1>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <?php echo $module_breadcrumbs ?>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <section>
        <article>
          <h2 class="text-right"><a class="anchor" name="purchases"></a><a href="faq#purchases"><i class="glyphicon glyphicon-link" style="font-size:0.8em;color:#CCC"></i></a> Purchases</h2>
          <h4>How soon after my payment will I receive the purchased product?</h4>
          <div class="well">
            <p>You will be able to download the purchased product as soon as your transaction has been confirmed by the network.</p>
          </div>
          <h4>How many times can I download the purchased product?</h4>
          <div class="well">
            <p>You are guaranteed at least one product download after confirmation of your payment.</p>
<p>There is however no limit on the number of times you may download the purchased product as long as it's not removed by the merchant.</p>
           </div>
          <h4>What rights to the product do I receive after purchase?</h4>
          <div class="well">
            <p>You will receive the rights as specified by the author in the product license.</p>
          </div>

          <h2 class="text-right"><a class="anchor" name="payouts"></a><a href="faq#payouts"><i class="glyphicon glyphicon-link" style="font-size:0.8em;color:#CCC"></i></a> Payouts</h2>
          <h4>How soon after a sale do I receive my money?</h4>
          <div class="well">
            <p>We estimate to make payouts once a month as all payments are currently being processed manually.</p>
            <p>Sellers will however be able to withdraw their profits instantly once the Seller Verification System has been implemented.</p>
          </div>
          <h4>Can a different withdrawal addresses be used for each product?</h4>
          <div class="well">
            <p>Yes, the withdrawal address can be changed in the Withdraw Address field in the product form. Giving each product a different address allows you to identify the source of each payment.</p>
            <p>You can use the same withdrawal address for each product if you do not need to see the source of each payment.</p>
          </div>

          <h2 class="text-right"><a class="anchor" name="interface"></a><a href="faq#interface"><i class="glyphicon glyphicon-link" style="font-size:0.8em;color:#CCC"></i></a> Interface</h2>
          <h4>I can&rsquo;t remove my product, Help</h4>
          <div class="well">
            <p>We do not block the removal of products as the content belongs to you. If you do get an error while trying to remove a product, it may be due to a pending order which is awaiting payment. Please try removing the product again in 10 minutes time when the transaction has completed.</p>
            <p>Please note that every client is guaranteed at least one download of the product after purchase. Any product which was purchased and paid for but not yet downloaded cannot be removed until the client has downloaded the product.</p>
          </div>

          <h2 class="text-right"><a class="anchor" name="quota"></a><a href="faq#quota"><i class="glyphicon glyphicon-link" style="font-size:0.8em;color:#CCC"></i></a> Disk quota</h2>
          <h4>What is the initial amount of disk space available to each seller?</h4>
          <div class="well">
            <p>Every seller receives 100 MB of disk space to publish their products after registration.</p>
          </div>
          <h4>How to obtain additional disk space?</h4>
          <div class="well">
            <p>An additional 1 MB of disk space is provided for each completed sale. If you for example make a 100 sales you will receive an additional 100 MB of disk space.</p>
          </div>
          <h4>Is it possible to purchase additional disk space?</h4>
          <div class="well">
            <p>We do not sell disk space under the different tariff plans.</p>
            <p>However, if your product has justified the need for additional space, we will consider such a request. Please send your request via our <a href="contact">Contact Us</a> page.</p>
          </div>

          <h2 class="text-right"><a class="anchor" name="other"></a><a href="faq#other"><i class="glyphicon glyphicon-link" style="font-size:0.8em;color:#CCC"></i></a> Other</h2>
          <h4>Do you plan on supporting additional currencies like Litecoin, Dogecoin, Paycoin, etc?</h4>
          <div class="well">
            <p>Yes we will be focussing on including additional currencies in the short term.</p>
          </div>
          <h4>How about additional language implementations?</h4>
          <div class="well">
            <p>This is already provided for but not yet fully implemented. Priority of implementation will depend on the audience as per the site analytics.</p>
          </div>
          <h4>I found a bug on the site</h4>
          <div class="well">
            <p>Please send the details via the <a href="contact">contact form</a>. If you're a developer and would like to contribute, fork us on <a href="http://github.com/bitsybay" target="_blank" rel="nofollow">GitHub</a>. </p>
          </div>
        </article>
      </section>
    </div>
  </div>
<?php echo $footer ?>
