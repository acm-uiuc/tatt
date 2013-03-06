# Setting up your dev system for TATT development

Our current development setup is on Ubuntu linux.  Please either develop on an
Ubuntu system or a virtualized one.  Search for info regarding virtualbox,
parallels, hyperv, or vmware if you are not familiar with virtualization.

Also, please fork the main repo on github if you haven't.  You will be pushing 
changes to your own repo and submitting pull requests to the main acm repo when
you have valid, working code.  Please keep your working code in your master
branch and use other branches for adding new features, testing other developer's
code and implementing bug fixes.  Check out [this guide for how to effectively
branch and merge code in git](http://git-scm.com/book/en/Git-Branching-Basic-Branching-and-Merging).

The following guide assumes you clone the project in your home directory and
that you will create a python virtual environment in your home directory.

# Software setup
To install the project you will need to do the following.

* sudo apt-get install python git python-pip ruby-compass
* sudo pip install virtualenv
* cd ~
* git clone git@github.com:{YOUR GITHUB ID HERE}/tatt.git
* virtualenv --no-site-packages tattenv
* source tattenv/bin/activate
* pip install -r tatt/dependencies.txt

## Regarding Virtual Environment
Whenever you work on the project you will first need to activate the virtual 
environment as we did above with the source command.  When you are done with the
environment, simply type:
* deactivate

## Additional setup and running the server
Now that you have all the software installed, now you need to do the following
the first time you run the project
* cd ~/tatt/server
* python manage.py syncdb
* python manage.py collectstatic
* python manage.py runserver

In another terminal do the following and then minimize it:
* cd ~/tatt/compass
* compass watch

While the server is running, you can go to 127.0.0.1:8000 in your browser to test
it.  To stop the server, just press Ctrl-C.

Whenever there are database schema changes you will need to re-run the
syncdb line.

Whenever new static files (images, css, javascript, etc) are added you will need
to re-run the collecstatic command.  We will look at changes in the future so
this is not needed on dev environments.

The command "compass watch" will check for when the files in ~/tatt/compass/sass
are modified.  If they are, then compass will regenerate the css files used by
the site.  As such, be sure to edit the files compass that is watching rather
than the files in the static css directory.
