The BitsyBay Project
====================

BitsyBay is an experimental minimalistic service to help you buy or sell digital creative with cryptocurrency like BitCoin. It includes marketplace for legal CMS extensions, illustrations, photos, themes and other creative assets from them authors.  
It was made by and for creative peoples who love a freedom and looking to easy trading around the world.  

BitsyBay Engine
===============

We believe that liberty service should be accessible to its users: it's 100% open source licensed under the GNU GPL version 3.  
If you know how to make this world better, join to the party or use this engine as your own BitCoin service.  

DONATE
------

By donating to the foundation, you are helping us fund and manage this project: 

    BTC 13t5kVqpFgKzPBLYtRNShSU2dMSTP4wQYx

STATUS
------

Beta, only for developers


FEATURES
--------

* Unlimited Categories
* Unlimited Products
* Unlimited Users
* Multi-currency
* Multi-language

**Account**

* Email approving
* File quota for each seller
* Basic brute force protection
* IP / access logging
* Profile page and account settings

**Catalog**

* SEF support
* Auto redirects 301 from old to new url
* Product Reviews
* Product Demos
* Product Videos
* Product Specials
* Product Licenses
* Product Favorites
* Product Tags
* Basic Search
* Search requests logging
* Comment form
* AJAX file uploading
* Automatic image generation based on IdentIcon algorithm
* Automatic image resizing
* Optional watermarking
* Abuse reporting

**Analytics**

* Search log
* 404 log
* Withdraw log

**Payment**

* Standalone BitCoin payment processor
* Royalty-Free and Exclusive offers
* Simple email notifications

COMING SOON
-----------

* Multi-currency implementation
* Multi-language implementation

REQUIREMENTS
------------


    apache2 
    php5 
    mysql-server 
    bitcoind 
    php-gd 
    php-imagick 
    php-curl

INSTALL
-------

* Copy all content from **/upload** directory to your host root directory
* Change **/pulic** directory as public root directory
* Enable rewrite module
* Create the database from the dump **/database/structure.sql**
* Import custom database content from the dump **/database/data/*.sql**
* Set your server settings in the **config.php** file
* Set write-access to the following directories:


    /storage 
    /public/image/cache 
    /system/log 

* Setup crontab: **/tool/order/manager.php** - hourly
* Do not forget:


    upload_max_filesize  
    post_max_size  
    memory_limit  
    allow_url_fopen  

**Enjoy!**
