# Server side concepts

## Directory structure

Once `$ php octower.phar server:init` is executed on the server it creates the following directory structure :

.
+-- octower.phar
+-- octower.json
+-- releases
|   +-- v1
|   +-- v2
+-- current
+-- shared

octower.phar is the executable that you downloaded
---
octower.json is a file describing the current server
---
releases is a directory that contains all the release that you installed on the server (by using the server:package:extract command or by using a remote).
---
shared is a directory that contains the folders that needs to be shared among the releases. [Check the local configuration page](/doc/03-local-configuration.md) to have more information about how to set up shared folders.
