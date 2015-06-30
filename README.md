BitsyBay Engine
===============

Simple and minimalistic SaaS platform to help you buy or sell digital creative with cryptocurrency like BitCoin. This is a 100% free and Open Source project of the BitsyBay Store http://bitsybay.com

By donating to the foundation, you are helping us fund and manage this project: 

    BTC 13t5kVqpFgKzPBLYtRNShSU2dMSTP4wQYx

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

* Create the database from the dump **/database/bitsybay.sql**
* Set your server settings in the **config.php** file
* Set write-access to the following directories:


    /storage 
    /public/image/cache 
    /system/log 

* Setup crontab: **/cron/order_processor.php** - hourly
* Do not forget:


    upload_max_filesize 
    post_max_size 
    memory_limit 
    allow_url_fopen 

**Enjoy!**
