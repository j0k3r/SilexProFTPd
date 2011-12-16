# SilexProFTPd

This application allows to manage and monitor [ProFTPd](http://www.proftpd.org/) accounts.
I used a custom MyFTP Admin (v0.6.1) for a while, since I lost everything from this custom version, I decided to quickly rebuild one using [Silex](http://silex.sensiolabs.org/).

# Server configuration
Apache users, you don't have to care about this part. It's done by the .htaccess file.
Lighttpd users, here are the config for your simple-vhost:

  server.document-root = "/path/to/SilexProFTPd"

  url.rewrite-once = (
    "^/assets/.+" => "$0", # directories with static files
    "^/favicon\.ico$" => "$0", # static file example
    "^(/[^\?]*)(\?.*)?" => "/index.php$1$2" # default application
  )
