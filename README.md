# PitchPrint for Magento

This PichPrint plugin for Magento provides an interface between PitchPrint and Magento.
It retrieves all your designs from your PitchPrint account for you to select and assign to a product. When opening the product on the frontend, it will have the selected design ready for customization.
You can link your PitchPrint account to Magento, by providing your domain API key and Secret Key in the PitchPrint Plugin for Magento's settings page.
The plugin also emits events when certain actions take place. These events send information to an endpoint that you can specify on the Webhooks page of PitchPrint https://admin.pitchprint.io/webhooks

The plugin allows you to do the following on Magento:

* Assign a PitchPrint design to a product
* Choose the display mode of PitchPrint on a per product basis. ( Fullscreen, Inline, Mini )
* Indicate whether it is compulsory for a product to be customized, before add to cart is possible.
* Send information about a project / order when certain actions take place, these are the list of available events:
  * When a file is uploaded
  * When a project get's saved
  * When an order is being processed
  * When an order is completed

How to install PitchPrint on Magento: https://docs.pitchprint.com/article/113-magento-installation

Demo of PitchPrint on Magento: https://mg.demo.pitchprint.io/


Steps for Installation

1. Extract extension package and upload the folder named PitchPrintInc into your_magento_root_dir/app/code/ directory.
  
  2.  In shell run: php bin/magento setup:upgrade
  3. in Mysql run the following: 
  
      CREATE TABLE `magento`.`pitch_print_config` (
      ->  `id` INT NOT NULL AUTO_INCREMENT,
      ->  `api_key` TEXT NULL,
      ->  `secret_key` TEXT NULL,
      ->  PRIMARY KEY (`id`));
   
     CREATE TABLE `magento`.`pitch_print_product_design` (
      ->  `product_id` INT NOT NULL AUTO_INCREMENT,
      ->  `design_id` TEXT NULL,
      ->  PRIMARY KEY (`product_id`));
    
     CREATE TABLE `magento`.`pitch_print_quote_item` (
      ->  `item_id` INT NOT NULL AUTO_INCREMENT,
      ->  `project_data` TEXT NULL,
      ->  PRIMARY KEY (`item_id`));
      
 4.  Then run: php bin/magento cache:clean
 5.  Followed by: php bin/magento setup:static-content:deploy
 6.  Then navigate to your Store's Backend / Admin, on the left-hand sidebar you will see the PitchPrint module.
 7.  Next, let’s generate the PitchPrint keys. Navigate in a new window tab to https://admin.pitchprint.io/register and create an account or login here if you already have an account
 8.  Then navigate to the domains page and add a new domain. Don’t worry you can still use it for localhost testing if you are not ready to go live yet. Provide your intended domain url, not a localhost. You should now have a pair of API and Secret Key.
   Back to your Magento Admin, you will copy and paste the API and Secret keys generated above into the PitchPrint configuration boxes and save.
   Now navigate to Catalog – > Products page and click “edit” on the right under action against any product in your admin. On the product details page, you will find PitchPrint in the menus at the bottom. Click it to assign one of your designs to the product. To create more designs, you need to go to the PitchPrint Designs page. Save.
