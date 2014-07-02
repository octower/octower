# Server side concepts

## Directory structure

Once `$ php octower.phar server:init` is executed on the server it creates the following directory structure :

```
.
+-- octower.phar
+-- octower.json
+-- releases
|   +-- v1
|   +-- v2
+-- current
+-- shared
```

- octower.phar is the executable that you downloaded

- octower.json is a file describing the current server

- releases is a directory that contains all the release that you installed on the server (by using the `server:package:extract` command or by using a remote).

- shared is a directory that contains the folders that needs to be shared among the releases. [Check the local configuration page](/doc/03-local-configuration.md) to have more information about how to set up shared folders.

- current is a symbolic link to a release in the release folder : this is your active release

## Working with release

You can see all the installed releases with the `$ php octower.phar server:release:list` command. 

Then you can **enable** one release with the `$ php octower.phar server:release:enable <release_version>` command. This will create a symbolic link between the release you gave in parameter and the current folder. 
The current folder needs to be the folder that is exposed in your webserver.


