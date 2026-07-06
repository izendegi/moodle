# SMOWL Proctoring for Moodle

* Author:   Manu Fraile Yarza   <manu.fraile@smowltech.com>
            Daniel Ureña Zafra  <dani.urena@smowltech.com>
* Copyright: Smiley Owl Tech S.L. https://smowl.net

SMOWL is a remote proctoring solution that enable institutions to monitorize online activities without interfering with the user’s experience. Afterwards, a report is automatically generated for an evaluator to work in a more objective way.
This is how Smowltech supports organizations to improve quality assurance.

Smowltech will provide you all the information related to the installation, including a unique platform identifier and a license key, among other useful information in order to start monitoring online activities in Moodle.

## QUICK INSTALL
1. Place these files in a new folder in your Moodle install under /blocks/smowl
2. Visit the Notifications page in Moodle to trigger the upgrade scripts
3. Configure the SMOWL plugin under admin > plugins > blocks > SMOWL

## SMOWL LICENSE
For more information you can reach Smowltech' team. They will guide you through all your enquiries. Once you install the plugin in your MOODLE platform, you will need to fill the plugin configuration with the ID and license information provided by Smowltech during the onboarding process. You can always reach them through:
  Services
    https://smowl.net/en/proctoring-products/
  Support
    https://smowl.net/en/support-for-exam-takers/

## ADDITIONAL INFORMATION AFTER SMOWL LICENSE SETUP
The SMOWL extension uses Moodle's LTI tools and activities, as well as secure WS connections to improve LTI integration and linking with the SMOWL LTI server.
* New Web Services, named "Smowl Services".
* New user, named "Smowl Webservices User (service.desk@smowltech.com)":
  * Added with authentication only by "Web services autentication".
  * Added an specific TOKEN to use only the "Smowl Services" Web service.

## DEVELOPMENT ENVIROMENT
### Requirements
* The plugin needs to be in the `blocks` directory of a Moodle. The recommended version is the 403.
* Requires the zip command
* Requires Node >=20.11.0 <21.0.0-0

### Setup (only first time)
```bash
git clone -b MOODLE_403_STABLE git://git.moodle.org/moodle.git
cd moodle
npm install
git clone git@github.com:Smiley-Owl-Tech/moodle_block_smowl.git
```

## BUILD
```bash
cd blocks/smowl
sh build.sh
```

Or, if you want to set another timestamp on the zip name, you can use the following:

```bash
sh build.sh 1984122905 # create block_smowl_1984122905.zip
```
