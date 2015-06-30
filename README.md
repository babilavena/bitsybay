BitsyBay Engine is an OpenSource, simple and minimalistic SaaS platform to help you build your indie digital online store with BitCoin.

By donating to the foundation, you are helping us fund and manage this project:  
BTC 13t5kVqpFgKzPBLYtRNShSU2dMSTP4wQYx

== FEATURES ==

* Unlimited Categories
* Unlimited Products
* Unlimited Users

Account

* File quota for each seller
* Basic brute force protection
* Profile page and account settings

Catalog

* Product Reviews
* Product Demos
* Product Videos
* Product Specials
* Product Licenses
* Basic Search
* Automatic image resizing
* Optional watermarking
* Abuse reporting

Payment

* Standalone BitCoin payment processor
* Royalty-Free and Exclusive offers
* Simple email notifications

== COMING SOON ==

* Multi Currency
* Multi Language

== REQUIREMENTS ==

* apache2
* php5
* mysql-server
* bitcoind
* php-gd
* php-imagick

== INSTALL ==

1. Create the database from the dump /database/bitsybay.sql 
2. Set your server settings in the config.php file
3. Set write-access to the following directories:

* /storage
* /public/image/cache
* /system/log

4. Setup crontab:

* /cron/order_processor.php - hourly

5. Do not forget:

* upload_max_filesize
* post_max_size
* memory_limit
* allow_url_fopen

Enjoy!
