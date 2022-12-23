# Procurement Management Application


## Overview

This PROCUREMENT APPLICATION  mainly caters for the following

1. Purchase Request Management
2. Contract Management
3. Asset Management

## Purchase Request Management

This is primarily to keep records of all the generated Purchase Requests (PR) and compare the data. 

Purchase Request flow :

1. An employee can generate PR for the expenses made. PR Status: Pending
2. Once the PR is generated the department head of concerned employee gets notified 
3. The department head can approve or reject the request mentioning the reason for rejection. PR Status: Approved / Rejected by Department
4. In case the PR is approved by the department  the PR goes to Finance head for the final approval on acceptance the PR is processed. 
    
    PR Status: Processed / Rejected
## **Contract Management**

This is primarily to keep records of all the contracts signed in the company with records of signing users and files uploaded with the records. This makes it easier for users to manage contract details in one place.

## Asset Management

The Asset Management  name itself defines the use case and the working of this project, let go for a deeper explanation. In this project company assets are managed by creating a crud operation of different models.

So it becomes easy to manage assets and keep a record of the assets.

## Installation in local system

git clone https://github.com/trackier/procurement-app.git<br>
cd procurement-app<br>
composer install 

Update configuration in ini files in Application/configuration with credentials 

Install Mongo DB 

## Libraries Used

"mongodb/mongodb": "^1.2.0",
"symfony/dom-crawler": "^3.1",
"symfony/css-selector": "^3.1",
"php-http/curl-client": "^1.7",
"firebase/php-jwt": "^5.2",
"monolog/monolog": "^2.8",
"symfony/http-foundation": "^5.4",
"mailgun/mailgun-php": "^2.1"


