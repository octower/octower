# Basic usage

## Installation

To install Octower, you just need to download the `octower.phar` executable.

    $ curl -sS https://getoctower.org/installer | php
    
You need this executable both on your local dev folder and on your server

## Local configuration

To use Octower you need to create an `octower.json` file at the root of your project.

For more information please [check the local configuration page](/doc/03-local-configuration.md).

## Server configuration

Octower is needed on the server you want to deploy (follow the same process than above to install it).

Once installed execute the following command to initialize your server configuration :

    $ php octower.phar server:init
    
This will generate the directory tree and an octower.json used for server configuration.

For more information please [check the server side concepts page](/doc/04-server-site-concepts.md).

## Your first deployment

### Create an archive of your project 

First step is to generate an archive of your project :

    $ php octower.phar package:generate
    
This will create an .octopack file which is an archive of your local project. 

### Deploy on your remote server

Send the package (the .octopack file) to the server where you want to deploy your project.

Once connected to your server extract the package so that octower recognize it :

    $ php octower.phar server:package:extract <path of your package.octopack>
    
Your release should now be recognized by octower : 

    $ php octower.phar server:release:list
    
You can enable the release :

    $ php octower.phar server:release:enable <id of the release>
