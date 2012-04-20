Webedit
=======

Welcome to the Webedit application, a PHP project made for a class in college,
using Symfony2.

This document contains information on how to download and start using Webedit.
For a more detailed explanation on Symfony2 installation, see the
[Installation chapter](http://symfony.com/doc/current/book/installation.html)
of the Symfony Documentation.

1) Download Webedit
-------------------

Only downloads using git is supported at this time, so make sure it's installed.

### Clone the git Repository

Run the following commands:

    git clone git://github.com/helderco/webedit.git
    cd webedit
    mkdir -p app/logs app/cache web/uploads
    cp app/config/parameters.ini.dist app/config/parameters.ini

2) Installation
---------------

Once you've downloaded Webedit, installation is easy, and basically
involves making sure your system is ready for Symfony.

### a) Check your System Configuration

Before you begin, make sure that your local system is properly configured
for Symfony. To do this, execute the following:

    php app/check.php

If you get any warnings or recommendations, fix these now before moving on.

If the PHP version check failed, and you have a `php-5.3` and `php` installed
(where `php` is 5.2), you can create a symbolic link to `php-5.3` somewhere
and add it to your `$PATH`.

    ln -s `which php-5.3` ~/bin/php
    export PATH=~/bin/:$PATH

### b) Install the Vendor Libraries

You need to download all of the necessary vendor libraries. If you're not sure
if you need to do this, check to see if you have a ``vendor/`` directory. If
you don't, or if that directory is empty, run the following:

    php bin/vendors install

### c) Configure

Edit the file `app/config/parameters.ini` to configure your database and
email credentials, and also create a secret key. If you don't yet have a
database created for this application, create one now.

Create the database tables with the following:

    php app/console doctrine:schema:create

And add an administrator:

    php app/console fos:user:create admin webedit@siriux.org s3cr3t --super-admin

You now have your first user with access to the administration area, with
username `admin` and password `s3cr3t`.

### d) Access the Application via the Browser

Congratulations! You're now ready to use Webedit. If you've downloaded Webedit
in the web root of your computer, then you should be able to access the
application via:

    http://localhost/webedit/web/

It is recommended that you create a virtual host pointing to the `web` folder
of Webedit, though. If you know how to do this, you can have a URL like this:

    http://webedit.local/


In a development environment always add the `app_dev.php` front controller to
the start of your urls:

    http://webedit.local/app_dev.php/login

Enjoy!
