# Link Depot

A minimalist way to store, manage, and work with your bookmarks and random URLs
from the internet.

## Installation

In order to get this project up and running you'll have to perform a couple of
very simple tasks.

Clone this project into your web server's `htdocs` directory either in the root
if this is the only web application running on your server or in a
sub-directory. Remember to set the `SITE_PATH` configuration parameter to match
your setup.

Start by editing the `config.php` file to suit your environment needs. You can
also set environment variables which will take precedence over what's defined in
the configuration file.

Initialize the application's database using the initialization script:

```bash
sqlite3 depot.db < sql/initialize.sql
```

Ensure that the web server user can read and write to the folder where the
database is located as well as the database file itself otherwise you'll run
into [permission issues](https://stackoverflow.com/a/3330616/126353).

Finally ensure that your web server is
[set to allow overrides from `.htaccess` files](https://www.linode.com/docs/guides/how-to-set-up-htaccess-on-apache/)
and that [mod_rewrite is enabled](https://www.digitalocean.com/community/tutorials/how-to-rewrite-urls-with-mod_rewrite-for-apache-on-ubuntu-22-04).

## License

This project is licensed under the
[Mozilla Public License Version 2.0](https://www.mozilla.org/en-US/MPL/2.0/).

