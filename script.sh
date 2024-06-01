#!/bin/bash
(crontab -l | grep -v "/usr/bin/php /opt/lampp/htdocs/6amMart-Backend/artisan dm:disbursement") | crontab -
(crontab -l ; echo "57 13 * * * /usr/bin/php /opt/lampp/htdocs/6amMart-Backend/artisan dm:disbursement") | crontab -
(crontab -l | grep -v "/usr/bin/php /opt/lampp/htdocs/6amMart-Backend/artisan store:disbursement") | crontab -
(crontab -l ; echo "57 13 * * * /usr/bin/php /opt/lampp/htdocs/6amMart-Backend/artisan store:disbursement") | crontab -
