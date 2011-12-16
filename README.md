# SilexProFTPd

This application allows to manage and monitor [ProFTPd](http://www.proftpd.org/) accounts.
I used a custom MyFTP Admin (v0.6.1) for a while, since I lost everything from this custom version, I decided to quickly rebuild one using [Silex](http://silex.sensiolabs.org/).

## Informations

First of all, I built this app to fit my needs about managing ftp's users with MySQL and ProFTPd. I'll be very happy if you want to add some functionnalities and I'm waiting your PRs :-)

This app doesn't (yet?) handle :

* quota for users
* groups management

This app runs perfectly :

* users management
* transfers (informations about downloaded/uploaded file)
* errors browsing

Of course, this app runs with PHP 5.3 (check [Silex configuration](http://silex.sensiolabs.org/doc/usage.html#pitfalls)) and MySQL.

## Installation

```
# clone the repository
git clone --recursive git://github.com/j0k3r/SilexProFTPd.git

# create and customize your config.php
cd SilexProFTPd/
cp src/config.php.dist src/config.php
```

## ProFTPd configuration

You will find a part of my [proftpd.conf](https://github.com/j0k3r/SilexProFTPd/blob/master/doc/proftpd.conf) about the server configuration.

If you don't have a proftpd database right now, you can use [database.sql](https://github.com/j0k3r/SilexProFTPd/blob/master/doc/database.sql) file to create empty table.

## Webserver configuration

* _Apache users_: you don't have to care about this part. It's done by the [.htaccess file](https://github.com/j0k3r/SilexProFTPd/blob/master/web/.htaccess). You might have to uncomment the second line if you got some troubles.
* _Lighttpd users_: here is the config for your simple-vhost:

```
  server.document-root = "/path/to/SilexProFTPd"

  url.rewrite-once = (
    "^/assets/.+" => "$0", # directories with static files
    "^/favicon\.ico$" => "$0",
    "^(/[^\?]*)(\?.*)?" => "/index.php$1$2" # default application
  )
```