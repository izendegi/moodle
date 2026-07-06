<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'SMOWL';

// P&P manage.

$string['typeconfigheading'] = 'Konfiguraatiotyyppi';
$string['typeconfigheadingdesc'] = 'Valitse konfiguraatiotyyppi <br> · Automaattinen <br> · Manuaalinen <br> · Määritä myöhemmin';
$string['typeconfigheadingdesc2'] = 'Voit sitten muuttaa konfiguraatiotyyppiäsi';

$string['typeconfig'] = 'Konfiguraatiotyyppi';
$string['typeconfigdesc'] = 'Valitse toiminto, jonka haluat suorittaa alla';
$string['noconfigmessage'] = 'Olet valinnut konfiguroida SMOWL jälkeenpäin, voit tehdä sen kampuksen hallinnasta.';

$string['changetypeconfigauto'] = 'Vaihda automaattiseen konfiguraatioon.';
$string['changetypeconfigrestartauto'] = 'Käynnistä automaattinen konfiguraatio uudelleen.';
$string['changetypeconfigmanual'] = 'Vaihda manuaaliseen konfiguraatioon.';
$string['changetypeconfigcancel'] = 'Peruuta konfiguraation muutos.';
$string['changetypeconfigafter'] = 'Määritä myöhemmin.';

$string['typeask'] = 'Valitse vaihtoehto';
$string['typenoconfig'] = 'Määritä myöhemmin';
$string['typeautoconfig'] = 'Automaattinen konfiguraatio';
$string['typemanualconfig'] = 'Manuaalinen konfiguraatio';

$string['dataautoconfigheading'] = 'Automaattiset konfiguraatiodata';
$string['dataautoconfigheadingdesc'] = 'Voit nyt tarkastella tai syöttää automaattiset konfiguraatiodata';

$string['autoconfigmessagejsondataclient'] = 'Asiakastiedot on esiasetettu tässä liitännäisessä.';
$string['autoconfigmessageconfigdone'] = 'SMOWL-liitännäinen on konfiguroitu automaattisesti';

$string['autoconfigmessageclientvoid'] = 'Asiakastiedot on määritettävä asianmukaisesti';
$string['autoconfigmessagecontenterror'] = 'Automaattinen konfiguraatio epäonnistui: paluu virheellisillä elementeillä';
$string['autoconfigmessageunauthorized'] = 'Automaattinen konfiguraatio epäonnistui: todennusongelmat';
$string['autoconfigmessagebadrequest'] = 'Automaattinen konfiguraatio epäonnistui: virheellinen pyyntö';
$string['autoconfigmessageentityerror'] = 'Automaattinen konfiguraatio epäonnistui: Entiteettiä ei voitu hakea';
$string['autoconfigmessageconflict'] = 'Automaattinen konfiguraatio epäonnistui: sisäiset ongelmat entiteetissä';

$string['clientid'] = 'Asiakastunnus';
$string['clientiddesc'] = 'Asiakastunnus kampuksen aktivointiin';

$string['clientkey'] = 'Aktivointiavain';
$string['clientkeydesc'] = 'Aktivointiavain kampuksen aktivointiin';

$string['nocapabilityconfigmessage'] = 'Sinulla ei ole oikeutta suorittaa tätä toimintoa';

// Connection smowl settings.
$string['connectionsconfig'] = 'Yhteysasetukset';
$string['connectionsconfigdesc'] = 'Seuraavat vaihtoehdot mahdollistavat yhteyden SMOWL:aan';
$string['connectionsconfigcontact'] = 'Lisätietoja nykyisestä lisenssistäsi löydät asiakastililtäsi osoitteesta <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Alusta';
$string['entitydesc'] = 'Smowltechin toimittama ID. ';
$string['noticeemptyentitysettings'] = 'Alusta on tyhjä, ota yhteyttä Smowltechiin saadaksesi syötettävän arvon. ';

$string['password'] = 'Lisenssiavain';
$string['passworddesc'] = 'Lisenssiavain alustalle, joka on toimitettu Smowltechiltä.';
$string['noticeemptypasswordsettings'] = 'Alusta on tyhjä, ota yhteyttä Smowltechiin saadaksesi syötettävän arvon.';

$string['apikey'] = 'API-avain';
$string['apikeydesc'] = 'Avain API:lle, jolla on pääsy API:in, joka on toimitettu Smowltechiltä.';
$string['noticeemptyapikeysettings'] = 'API-avain on tyhjä, ota yhteyttä Smowltechiin saadaksesi syötettävän arvon.';

// JWT Secret
$string['smowljwtsecret'] = 'JWT-salaisuus';
$string['smowljwtsecretdesc'] = 'JWT-salaisuus, joka on toimitettu Smowltechiltä.';
$string['noticeemptysmowljwtsecret'] = 'JWT-salaisuus on tyhjä, ota yhteyttä Smowltechiin saadaksesi syötettävän arvon.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Opettajien asetukset';
$string['instructorsconfigdesc'] = 'Seuraavat vaihtoehdot mukauttavat SMOWL:n käyttöä opettajille kursseissa, joissa lohko on aktiivinen.';

$string['continuousassessment'] = 'Jatkuva arviointi';
$string['onlyexams'] = 'Vain tenttien valvonta';

$string['tracking'] = 'Valvontatyyppi';
$string['trackingdesc'] = 'Valitse, onko SMOWL-valvontatyyppi jatkuva arviointi vai vain tentit.';

$string['attempttracking'] = 'Erota tenttien yritykset';
$string['attempttrackingdesc'] = 'Valitse, haluatko erottaa saman testin eri yritykset samalle opiskelijalle.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Opettajien lisäasetukset';
$string['viewsettingsadvancedinstructors'] = 'Näytä opettajien lisäasetukset';
$string['viewsettingsadvancedinstructorsdesc'] = 'Jos tämä vaihtoehto on valittuna, opettajien lisäasetukset näytetään.';

$string['accesscontrol'] = 'Pääsyvalvonta';
$string['accesscontroldesc'] = 'Salli käyttäjien pääsy tenttiin vain, kun SMOWL-työkalut ovat aktiivisia';

// View smowl settings.
$string['usersconfig'] = 'Käyttäjien asetukset';
$string['usersconfigdesc'] = 'Seuraavat vaihtoehdot mukauttavat SMOWL:n käyttöä käyttäjille aktiviteeteissa ja kursseissa, joissa valvontajärjestelmä on aktiivinen.';

$string['floatblock'] = 'Aktivoi kelluva valvontalohko';
$string['floatblockdesc'] = 'Aktivoi kelluva lohko näyttääksesi sen';
$string['activeinpopup'] = 'Valvonta on käytettävissä kelluvassa lohkossa';

$string['blockheight'] = 'Näytön koko';
$string['blockheightdesc'] = 'Iframe:n korkeus, jossa web-kamera näkyy valvonnan aikana.';
$string['noticeblockheight'] = 'Iframe:n korkeuden on oltava vähintään 280 px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Käyttäjien lisäasetukset';
$string['viewsettingsadvancedusers'] = 'Näytä käyttäjien lisäasetukset';
$string['viewsettingsadvancedusersdesc'] = 'Jos tämä vaihtoehto on valittuna, käyttäjien lisäasetukset näytetään.';

// Capabilities.
$string['smowl:addinstance'] = 'Lisää SMOWL-lohko';
$string['smowl:manageactivities'] = 'SMOWL-aktiviteettien hallinta';
$string['smowl:managegroups'] = 'Hallitse SMOWL-ryhmiä';
$string['smowl:viewstudentcontent'] = 'Näytä opiskelijoiden tulokset';
$string['smowl:enrolment'] = 'Pääsy SMOWL-rekisteröintiin';

// Notices.
$string['notinstancedblockincourse'] = 'Tämän toiminnon suorittamiseksi sinun on luotava SMOWL-lohko kurssille.';
$string['notmanagepermissions'] = 'Sinulla ei ole oikeutta hallita SMOWL-aktiviteetteja.';
$string['notteachersmanagementpermissions'] = 'Opettajilla ei ole oikeutta hallita SMOWL-aktiviteetteja.';
$string['notviewmanagepermissions'] = 'Sinulla ei ole oikeutta näyttää tai hallita SMOWL-aktiviteetteja.';
$string['cannotcreatefile'] = 'Tiedostoa ei voitu luoda';
$string['activitiesupdate'] = 'SMOWL-aktiviteetit päivitetty';
$string['activityupdate'] = 'SMOWL-aktiviteetti päivitetty';
$string['noticeemptysmowlconfig'] = 'Lohkokonfiguraatiota ei ole suoritettu loppuun. Ota yhteyttä alustan ylläpitäjään ongelman ratkaisemiseksi.';
$string['noticeemptyentitynavigation'] = 'Lohkokonfiguraatiota ei ole suoritettu loppuun. Ota yhteyttä alustan ylläpitäjään ongelman ratkaisemiseksi.';

// Privacy.
$string['privacy:metadata'] = 'SMOWL-järjestelmä näyttää vain Smowltechin palvelimilla tallennetut tiedot.';
$string['privacy:metadata:smowl:smowltech_net'] = 'SMOWL-järjestelmä näyttää vain Smowltechin palvelimilla tallennetut tiedot ja siirtää Moodle-käyttäjätiedot Smowltechin palvelimille.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Yksilöllinen käyttäjätunniste';

// Events.
$string['eventinstancecreated'] = 'Yksilöllinen käyttäjätunniste';
$string['createblockinstance'] = 'Lohko luotu ';
$string['eventinstancedeleted'] = 'Yksilöllinen käyttäjätunniste';
$string['deleteblockinstance'] = 'Lohko poistettu ';
$string['eventinstanceupdated'] = 'Yksilöllinen käyttäjätunniste';
$string['updateblockinstance'] = 'Lohko päivitetty ';
$string['fromoldblockname'] = ' vanhasta lohkosta ';
$string['apicalled'] = 'API SMOWL-kutsu suoritettu';
$string['apicalleddesc'] = 'API SMOWL-kutsu suoritettu parametreilla.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Sisäiset SMOWL-konfiguraatiot';
$string['internalconfigdesc'] = 'Seuraavat vaihtoehdot voivat vaikuttaa liitännäisen toimintaan, niitä tulisi muuttaa vain SMOWL:n pyynnöstä.';
$string['onlysmowlexpressrequest'] = 'Tätä vaihtoehtoa tulisi muuttaa vain SMOWL:n pyynnöstä.';
$string['viewsettingsinternal'] = 'Näytä sisäiset asetukset';
$string['viewsettingsinternaldesc'] = 'Aktivoi tarkastelu nähdäksesi sisäiset SMOWL-vaihtoehdot.';

$string['internalconfigurls'] = 'Sisäiset SMOWL-URL-osoitteet';
$string['urlstudentview'] = 'Opiskelijanäkymä';
$string['urlstudentviewdesc'] = 'Linkki opiskelijanäkymään.';

// API URLs.

$string['internalconfigapiurls'] = 'Sisäiset SMOWL-API-URL-osoitteet';
$string['urlsmowlapi'] = 'SMOWL-API-URL';
$string['urlsmowlapidesc'] = 'URL SMOWL-API:in käyttämiseen.';

$string['apilmssettings'] = 'LMS-konfiguraatio-URL';
$string['apilmssettingsdesc'] = 'URL LMS-konfiguraatioiden päivittämiseen automaattisissa asennuksissa.';

$string['apilmssettingscustomer'] = 'Asiakkaan LMS-konfiguraatio-URL';
$string['apilmssettingscustomerdesc'] = 'URL asiakkaan LMS-konfiguraatioiden päivittämiseen automaattisissa asennuksissa.';

$string['apiconfigclient'] = 'Integraation aktivointi-URL';
$string['apiconfigclientdesc'] = 'URL integraation aktivointiin automaattisissa asennuksissa.';

$string['apiaddactivity'] = 'Lisää toimintaa';
$string['apiaddactivitydesc'] = 'URL-osoite suojatun toiminnan lisäämiseen';

$string['apimodifyactivity'] = 'Muokkaa toimintaa';
$string['apimodifyactivitydesc'] = 'URL-osoite suojatun toiminnan muokkaamiseen';

// Accessrule smowlcheckcam Settings.

$string['accesrulesmowlcheckcamconfig'] = 'Web-kamera -testin aktivointi-URL';
$string['accesrulesmowlcheckcamconfigdesc'] = 'URL web-kamera -testin aktivointiin automaattisissa asennuksissa.';
$string['accesrulesmowlcheckcam'] = 'Web-kamera -testilinkki';
$string['accesrulesmowlcheckcamdesc'] = 'URL web-kamera -testiin automaattisissa asennuksissa.';

// View smowl settings.

$string['viewconfig'] = 'Näytä konfiguraatiot';
$string['viewconfigdesc'] = 'Seuraavat vaihtoehdot vaikuttavat SMOWL-lohkon näyttämiseen';
$string['floatsnap'] = 'Aktivoi kelluva lohko Snap-suunnittelussa';
$string['floatsnapdesc'] = 'Aktivoi kelluva lohko näyttääksesi sen. Se toimii vain Snap-suunnittelussa';

// Manage SMOWL Groups.

$string['managegroups'] = 'Ryhmien hallinta';
$string['groupsaccessrestrictions'] = 'Pääsyrajoitukset';
$string['managegroupsformintro'] = 'Seuraavassa lomakkeessa sinun on valittava käyttäjäryhmät tai ryhmät, joille SMOWL-lohko näytetään.';
$string['managegroupsupdate'] = 'SMOWL-ryhmät on päivitetty onnistuneesti.';
$string['availabilityconditionsjsonform'] = 'Pääsyrajoitukset';
$string['availabilityconditionsjsonform_help'] = 'Tässä valikossa voit lisätä tarvittavat pääsyrajoitukset.';

// Setting Manage Groups.

$string['notmanagegroupspermissions'] = 'Sinulla ei ole oikeutta hallita ryhmiä.';
$string['managegroupsnotconfigured'] = 'Ryhmien hallinta ei ole konfiguroitu.';

// Bulk actions.

$string['bulkactions'] = 'Massatoiminnot';
$string['bulkactionsdesc'] = 'Seuraavat vaihtoehdot mahdollistavat SMOWL:n aktivoinnin tai poistamisen massatoiminnoilla.';

$string['noticeactivebulkactions'] = 'SMOWL:n aktivointi tai poistaminen massatoiminnoilla voi kestää jonkin aikaa valittujen aktiviteettien tai ryhmien määrästä riippuen.';
$string['bulkactive'] = 'Aktivointi aktiviteeteille';
$string['bulkgroups'] = 'Ryhmien aktivointi';
$string['coursecategory'] = 'Kurssikategoria';
$string['coursecategory_help'] = 'Kurssikategoria, jossa SMOWL on aktiivinen.';
$string['activitytype'] = 'Aktiviteettityyppi';
$string['activitytype_help'] = 'Aktiviteettityyppi, jossa SMOWL on aktiivinen.';
$string['activityname'] = 'Aktiviteetin nimi';
$string['activityname_help'] = 'Aktiviteetin nimi, jossa SMOWL on aktiivinen.';
$string['searchactivities'] = 'Etsi aktiviteetteja';
$string['allcategories'] = 'Kaikki kategoriat';
$string['searchresults'] = 'Hakutulokset';
$string['notfound'] = 'Ei tuloksia';
$string['savechanges'] = 'Tallenna muutokset';
$string['bulkactiveupdate'] = 'Massa-SMOWL-konfiguraatiot aktiviteeteille on päivitetty onnistuneesti.';
$string['searchgroups'] = 'Etsi ryhmiä';
$string['groupname'] = 'Ryhmän nimi';
$string['groupname_help'] = 'Ryhmän nimi, jossa SMOWL on aktiivinen.';

$string['managebulkgroupsformintro'] = 'Seuraavassa lomakkeessa voit nähdä kaikki kurssin ryhmät, jotka näkyvät haun tuloksena.';
$string['managebulkgroupsforminfo'] = 'Käyttäjät, jotka kuuluvat johonkin valituista ryhmistä, voivat nähdä näkymän.';
$string['managebulkgroupsformmoreinfo'] = 'On tärkeää, että kaikki kurssin käyttäjät kuuluvat vähintään yhteen ryhmään.';
$string['notviewmanagebuklpermissions'] = 'Sinulla ei ole oikeutta näyttää tai hallita SMOWL-aktiviteetteja massatoiminnoilla.';

// Access Control Status.
$string['acwaiting'] = 'SMOWL:n tarkistus, odota';
$string['acaccess'] = 'SMOWL on aktivoitu, voit käyttää aktiviteettia';
$string['acnotaccess'] = 'SMOWL ei ole vahvistettu, lataa sivu uudelleen tarkistaaksesi uudelleen';

// LTI Integration.
$string['internalconfigltitool'] = 'LTI SMOWL-konfiguraatio-URL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'LTI-perus-URL';
$string['urlsmowlltitooldesc'] = 'LTI SMOWL-työkalun perus-URL';
$string['ltitoolinit'] = 'Alkuperäinen URL';
$string['ltitoolinitdesc'] = 'LTI SMOWL-työkalun alkuperäinen URL';
$string['ltitoolversion'] = 'LTI-versio';
$string['ltitoolversiondesc'] = 'LTI SMOWL-työkalun versio';
$string['ltitoolpublickeyset'] = 'LTI-avainsetin URL';
$string['ltitoolpublickeysetdesc'] = 'LTI SMOWL-työkalun julkisen avainsetin URL';

$string['ltitoolinitiatelogin'] = 'LTI-kirjautumisen alkuperäinen URL';
$string['ltitoolinitiatelogindesc'] = 'LTI SMOWL-työkalun alkuperäinen kirjautumisen URL';
$string['ltitoolredirection'] = 'Uudelleenohjaus-URI';
$string['ltitoolredirectiondesc'] = 'LTI SMOWL-työkalun uudelleenohjaus-URI';
$string['ltitoolconfigusage'] = 'Konfiguraation käyttö';
$string['ltitoolconfigusagedesc'] = 'LTI SMOWL-työkalun konfiguraation käyttö "Näytä ennalta määritetty työkalu, kun lisätään ulkoinen aktiviteetti"';
$string['ltitoollaunch'] = 'Oletuslaunch-kontti';
$string['ltitoollaunchdesc'] = 'LTI SMOWL-työkalun oletuslaunch-kontti "Upota ilman lohkoja"';
$string['ltitoolconfigmemberships'] = 'Oletusjäsenet';
$string['ltitoolconfigmembershipsdesc'] = 'Oletusjäsenet LTI SMOWL-työkalulle "Upota ilman lohkoja"';

$string['urlsmowlltiapi'] = 'LTI-API:n perus-URL';
$string['urlsmowlltiapidesc'] = 'LTI SMOWL-API:n perus-URL';
$string['ltiapiapplications'] = 'LTI-sovellukset';
$string['ltiapiapplicationsdesc'] = 'LTI-sovellukset LTI SMOWL-API:lle';
$string['ltiapideployments'] = 'LTI-julkaisut';
$string['ltiapideploymentsdesc'] = 'LTI-julkaisut LTI SMOWL-API:lle';

// LTI problems.
$string['lticreatetoolsuccess'] = 'LTI SMOWL-työkalu luotu onnistuneesti';
$string['ltiupdatetoolsuccess'] = 'LTI SMOWL-työkalu päivitetty onnistuneesti';
$string['lticreatetoolerror'] = 'Ongelmia LTI SMOWL-työkalun luomisessa';
$string['ltisendtoolerror'] = 'Ongelmia LTI-konfiguraation lähettämisessä SMOWL:iin';
$string['ltisendtoolneedactivation'] = 'Huomio! Vaikuttaa siltä, että entiteettiäsi ei ole vielä vahvistettu mySmowltech-sovelluksessa, mikä estää laajennuksen määritysten valmistumisen. <a href="https://my.smowltech.net/" target="_blank">Napsauta tätä</a> ja kirjaudu sisään mySmowltech-kirjautumistiedoillasi vahvistaaksesi entiteettisi. Tämä vahvistus vaaditaan integroinnin onnistuneeseen loppuun saattamiseksi.';
$string['lticreatewserror'] = 'Ongelmia WS SMOWL:n luomisessa';
$string['ltiactivewserror'] = 'Ongelmia WS SMOWL:n aktivoinnissa';
$string['lticreateusererror'] = 'Käyttäjän "Smowl Webservices User" luomisessa on ongelmia. Ylläpitäjän toimenpide on tarpeen sen aktivoimiseksi.';
$string['noticeltinotvisible'] = 'LTI-työkalut on lukittu kampuksen hallinnassa (Lisäosat / Hallitse toimintoja).';
$string['ltisendwserror'] = 'Ongelmia WS-tietojen lähettämisessä';
$string['ltiactivityerror'] = 'Ongelmia WS SMOWL:n aktiviteetin luomisessa';
$string['notlticourse'] = 'Do not have LTI in course.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Sisäiset LTI SMOWL-konfiguraatiot';
$string['ltientity'] = 'LTI-entiteetti';
$string['ltitypeid'] = 'LTI-tyyppi-ID';
$string['ltideploymentid'] = 'LTI-julkaisu';
$string['ltiappid'] = 'LTI-sovellus';
$string['ltirestid'] = 'LTI-REST';

$string['ltiactivityname'] = 'SMOWL-paneeli';

// Block links teacher.
$string['ltimanagesmowl'] = 'Proctoring kojelauta';
$string['teachercontent'] = 'Määritä seuranta ja tarkista tulokset';
$string['teacherbutton'] = 'Käytä SMOWL-palvelua';

// Block links Student.
$string['ltistudentsmowl'] = 'SMOWL-paneeli';
$string['studentcontent'] = 'Siirry SMOWL-rekisteröinti- ja latauspaneeliin';
$string['studentbutton'] = 'Käytä SMOWL-palvelua';

$string['notstudentaccesspermissions'] = 'Vain opiskelijat pääsevät tähän osioon';

// Corner
$string['drag_me'] = 'Raahaa';