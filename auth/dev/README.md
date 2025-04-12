Moodle Auth Development
===============

Information
-----------

This plugin allow a user not logout when this user has loginas.
It's useful to developers and main administrators.
You have to enable in authentication settings to work it.

This plugin must be enable in:
    Home  ▶ Site administration ▶ Plugins  ▶ Authentication  ▶ Manage authentication

And enable "Auth Development tools"  to use it.

Add Redirect to specific URL when user logout.

Version
-------
Moodle 4.5 and legacy releases.

TO INSTALL:

Git way
- To install it using git, type this command in the root of your Moodle install:
```
git clone git@github.com:cescobedo/moodle-auth_dev.git auth/dev
```


Download way
- Download the zip from <https://github.com/cescobedo/moodle-auth_dev/archive/master.zip>
- Unzip it into  auth/ folder in your Moodle,
- Rename the new folder "moodle-auth_dev-master" to "dev"
- Enjoy!!!

NEW FEATURE
- Privacy Subsystem implementation for auth_dev. <https://docs.moodle.org/dev/Privacy_API>

FUTURE SCOPE:
- ADD useful actions to developer like not create user in ldap_plugin when user not exist.

Author
------
Carlos Escobedo
- <http://www.twitter.com/carlosagile>
- <https://coderwall.com/carlosagile>
