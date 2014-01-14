# Basic usage

## Installation

To install Octower, you just need to download the `octower.phar` executable.

    $ curl -sS https://getoctower.org/installer | php
    
You need this executable both on your local dev folder and on your server

## Local configuration

To use Octower you need to create an `octower.json` file at the root of your project. 

For mode information about local configuration please check this page (make a link to the configuration page).

## Server configuration

Octower is needed on the server you want to deplay (follow the same process than above to install it).

Once installed execute the following command to initialize your server configuration :

    $ php octower.phar server:init
    
This will generate the directory tree and an octower.json used for server configuration.

## Your first deployment

@TODO
