# Magento 2 Migration SQL

Migration tool helps to migrate sql scripts created based on trigger changes.

## Features:

- Enable / disable triggers for table(s)
- Push changes to sql file.

## Installing the Extension

    composer require magekey/module-migration-sql
    
## Using the Extension

    php bin/magento migration:trigger:enable cms_block cms_page
    
Do insert/update/deleted changes with those tables

    php bin/magento migration:trigger:disable
    php bin/magento migration:trigger:push [migration_name] [--module Vendor_Module]
    
## Apply changes
    
    php bin/magento migration:setup:upgrade
or 

    php bin/magento setup:upgrade

## Deployment

    php bin/magento maintenance:enable                  #Enable maintenance mode
    php bin/magento setup:upgrade                       #Updates the Magento software
    php bin/magento setup:di:compile                    #Compile dependencies
    php bin/magento setup:static-content:deploy         #Deploys static view files
    php bin/magento cache:flush                         #Flush cache
    php bin/magento maintenance:disable                 #Disable maintenance mode

## Versions tested
> 2.2.6
