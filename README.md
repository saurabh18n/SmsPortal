# Install HOW TO
1. ```cd /usr/src/```
2. ```git clone https://github.com/saurabh18n/SmsPortal```
3. ```cd fusionpbx-apps/; cp -R sms /var/www/fusionpbx/app/```
4. ```cd /var/www/fusionpbx/app/scripts/resources/scripts/app```
5. ```ln -s /var/www/fusionpbx/app/sms/resources/install/scripts/app/sms```
6. Go to GUI
7. Upgrades -> SCHEMA; APP DEFAULTS; MENU DEFAULTS; PERMISSION DEFAULTS
8. Log out and back in
9. Advanced -> Default Settings -> SMS Portal
10. Set api_from_number, api_url,api_user_name and api_password.
11. Go to Apps -> SMS portal and view and send sms as well see incomming sms's
12. Add your callback URL on flowroute for incomming message, it would be: https://YOURDOMAIN/app/smsportal/hook/receive.php

Send and receive!

NOTE: It is not recommended to use this app with versions of Freeswitch prior to 1.8 if you are installing in a clustered environment.  
There is a bug in earlier versions of Freeswitch that can cause it to crash in certain situation when using SMS.
