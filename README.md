Sign In System (v2)
===================

A web based sign in system for use in small offices.

Usage
-----

This is a jekyll compatible server that can be deployed easily to a server.

To enable the RESTful API to power the system you will need a LAMP (mysql and php) server that can be used to process requests.  

In order for data to pass throughout the system you will also need a "data" directory at the top level that is writable by the RESTful web server. 

In order for alerts (email and push) to work your server also needs to be globally visible and able to send email using sendmail or something equivalent. 

In order for OS X push notifications to work you will need a VALID NON SELF SIGNED HTTPS web server and all associated certifications from the apple developer programme. These are not included however the config file (server/push-config.php_example) should help you get set up. Following the apple procedure via the developer guides will fill in the gaps. One of the main gaps is that you will need to enable the DELETE verb in apache, as it is disabled in a lot of cases by default. 

Apple developer guide - https://developer.apple.com/library/mac/documentation/NetworkingInternet/Conceptual/NotificationProgrammingGuideForWebsites/PushNotifications/PushNotifications.html

License
-------

This code is open source under the MIT license. See the LICENSE.md file for 
full details.

Authors
-------

Dave Tarrant <davetaz@theodi.org>
James Smith <james.smith@theodi.org>
