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

$string['typeconfigheading'] = 'Tipe konfigurasie';
$string['typeconfigheadingdesc'] = 'Kies die tipe konfigurasie <br> · Outomaties <br> · Handmatig <br> · Stel later op';
$string['typeconfigheadingdesc2'] = 'Hieronder kan jy, indien jy wil, jou tipe konfigurasie verander';

$string['typeconfig'] = 'Tipe Konfigurasie';
$string['typeconfigdesc'] = 'Kies die aksie wat jy volgende wil uitvoer';
$string['noconfigmessage'] = 'Jy het gekies om SMOWL later op te stel, jy kan dit doen vanaf die kampusadministrasie.';

$string['changetypeconfigauto'] = 'Verander na outomatiese konfigurasie.';
$string['changetypeconfigrestartauto'] = 'Herbegin outomatiese konfigurasie.';
$string['changetypeconfigmanual'] = 'Verander na handmatige konfigurasie.';
$string['changetypeconfigcancel'] = 'Kanselleer konfigurasieverandering.';
$string['changetypeconfigafter'] = 'Stel later op.';

$string['typeask'] = 'Kies \'n opsie';
$string['typenoconfig'] = 'Stel later op';
$string['typeautoconfig'] = 'Outomatiese konfigurasie';
$string['typemanualconfig'] = 'Handmatige konfigurasie';

$string['dataautoconfigheading'] = 'Outomatiese konfigurasie data';
$string['dataautoconfigheadingdesc'] = 'Hieronder kan jy die outomatiese konfigurasie data valideer of invoer';

$string['autoconfigmessagejsondataclient'] = 'Die kliëntinligting is vooraf gekonfigureer in hierdie plugin.';
$string['autoconfigmessageconfigdone'] = 'Die SMOWL plugin is outomaties gekonfigureer';

$string['autoconfigmessageclientvoid'] = 'Die kliëntinligting moet korrek gekonfigureer word';
$string['autoconfigmessagecontenterror'] = 'Outomatiese konfigurasie fout: Terugkeer met ongeldige elemente';
$string['autoconfigmessageunauthorized'] = 'Outomatiese konfigurasie fout: Verifikasie probleme';
$string['autoconfigmessagebadrequest'] = 'Outomatiese konfigurasie fout: Slegte Versoek';
$string['autoconfigmessageentityerror'] = 'Outomatiese konfigurasie fout: Kon nie entiteit kry nie';
$string['autoconfigmessageconflict'] = 'Outomatiese konfigurasie fout: Interne probleme in die entiteit';

$string['clientid'] = 'Kliënt Identifiseerder';
$string['clientiddesc'] = 'Kliënt identifiseerder om die kampus te aktiveer';

$string['clientkey'] = 'Aktiveringsleutel';
$string['clientkeydesc'] = 'Aktiveringsleutel om die kampus te aktiveer';

$string['nocapabilityconfigmessage'] = 'Jy het nie toestemming om hierdie aksie uit te voer nie';

// Connection smowl settings.
$string['connectionsconfig'] = 'Verbindingskonfigurasie';
$string['connectionsconfigdesc'] = 'Hieronder vind jy die verbindingswaardes van jou platform met Smowltech.';
$string['connectionsconfigcontact'] = 'Vir meer inligting oor jou huidige lisensie, besoek jou persoonlike kliëntarea by <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Platform';
$string['entitydesc'] = 'Unieke identifiseerder vir hierdie platform, verskaf deur <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>.';
$string['noticeemptyentitysettings'] = 'Die platform veld is leeg, kontak Smowltech om die waarde in te voer.';

$string['password'] = 'Lisensiesleutel';
$string['passworddesc'] = 'Lisensiesleutel vir die platform, verskaf deur Smowltech.';
$string['noticeemptypasswordsettings'] = 'Die lisensiesleutel is leeg, kontak Smowltech om die waarde in te voer.';

$string['apikey'] = 'API Sleutel';
$string['apikeydesc'] = 'Sleutel vir toegang tot API, verskaf deur Smowltech.';
$string['noticeemptyapikeysettings'] = 'Die API sleutel is leeg, kontak Smowltech om die waarde in te voer.';

// JWT Secret
$string['smowljwtsecret'] = 'JWT Geheim';
$string['smowljwtsecretdesc'] = 'JWT geheim om die platform te aktiveer';
$string['noticeemptysmowljwtsecret'] = 'Die JWT geheim is leeg, kontak Smowltech om die waarde in te voer.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Konfigurasie vir instrukteurs';
$string['instructorsconfigdesc'] = 'Die volgende opsies pas die gebruik van SMOWL vir instrukteurs aan in die kursusse waar die blok geaktiveer is.';

$string['continuousassessment'] = 'Deurlopende assessering';
$string['onlyexams'] = 'Eksamenmonitering';

$string['tracking'] = 'Tipe monitering';
$string['trackingdesc'] = 'As die gemerkte tipe “Eksamenmonitering” is, kan SMOWL slegs in vraelyste geaktiveer word.<br>As die gemerkte tipe “Deurlopende assessering” is, sal SMOWL beskikbaar wees vir alle aktiwiteite wat in MOODLE verskaf word.';

$string['attempttracking'] = 'Onderskeid van eksamenpogings';
$string['attempttrackingdesc'] = 'Kies of jy verskillende pogings van dieselfde eksamen vir \'n student wil onderskei.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Gevorderde konfigurasie vir instrukteurs';
$string['viewsettingsadvancedinstructors'] = 'Sien gevorderde konfigurasie vir instrukteurs';
$string['viewsettingsadvancedinstructorsdesc'] = 'As hierdie opsie geaktiveer is, sal die gevorderde opsies vir instrukteurs vertoon word.';

$string['accesscontrol'] = 'Toegangbeheer';
$string['accesscontroldesc'] = 'Laat slegs gebruikers toe om toegang tot die eksamen te kry as die SMOWL gereedskap aktief is';

// View smowl settings.
$string['usersconfig'] = 'Konfigurasie vir gebruikers';
$string['usersconfigdesc'] = 'Die volgende opsies pas die gebruik van SMOWL vir gebruikers aan in die aktiwiteite en kursusse waar die moniteringstelsel geaktiveer is.';

$string['floatblock'] = 'Aktiveer drywende proctoring blok';
$string['floatblockdesc'] = 'Kies om die proctoring blok in drywende modus te sien.';
$string['activeinpopup'] = 'Die monitering is beskikbaar in die drywende blok';

$string['blockheight'] = 'Visualisasie grootte';
$string['blockheightdesc'] = 'Hoogte van die iframe waar die webcam tydens die monitering vertoon sal word.';
$string['noticeblockheight'] = 'Hoogte van die iframe kan nie minder as 280px wees nie.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Gevorderde konfigurasie vir gebruikers';
$string['viewsettingsadvancedusers'] = 'Sien gevorderde konfigurasie vir gebruikers';
$string['viewsettingsadvancedusersdesc'] = 'As hierdie opsie geaktiveer is, sal die gevorderde opsies vir gebruikers vertoon word.';

// Capabilities.
$string['smowl:addinstance'] = 'Voeg SMOWL blok by';
$string['smowl:manageactivities'] = 'Bestuur SMOWL aktiwiteite';
$string['smowl:managegroups'] = 'Bestuur SMOWL groepe';
$string['smowl:viewstudentcontent'] = 'Sien student skakels';
$string['smowl:enrolment'] = 'Toegang tot SMOWL registrasie skakel';

// Notices.
$string['notinstancedblockincourse'] = 'Om hierdie aksie uit te voer moet jy die SMOWL blok in die kursus skep.';
$string['notmanagepermissions'] = 'Jy het nie toestemming om SMOWL aktiwiteite te bestuur nie.';
$string['notteachersmanagementpermissions'] = 'Instrukteurs het nie toestemming om SMOWL aktiwiteite te bestuur nie.';
$string['notviewmanagepermissions'] = 'Jy het nie toestemming om SMOWL aktiwiteite te sien of te bestuur nie.';
$string['cannotcreatefile'] = 'Kon nie die lêer skep nie';
$string['activitiesupdate'] = 'SMOWL aktiwiteite opgedateer';
$string['activityupdate'] = 'SMOWL aktiwiteit opgedateer';
$string['noticeemptysmowlconfig'] = 'Die konfigurasie van die blok is nie voltooi nie.<br/>'.
    'Kontak die platform administrateur om hierdie probleem op te los.';
$string['noticeemptyentitynavigation'] = 'Die konfigurasie van die SMOWL blok is nie voltooi nie.<br/>'.
    'Kontak die platform administrateur om hierdie probleem op te los.';

// Privacy.
$string['privacy:metadata'] = 'Die SMOWL stelsel wys slegs data wat op die Smowltech bedieners gestoor is.';
$string['privacy:metadata:smowl:smowltech_net'] = 'Die SMOWL stelsel wys slegs die gestoor data en stuur die Moodle gebruiker se data na die Smowltech bedieners.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Unieke gebruiker identifiseerder';

// Events.
$string['eventinstancecreated'] = 'Gebeurtenis blok instansie geskep';
$string['createblockinstance'] = 'Blok instansie geskep: ';
$string['eventinstancedeleted'] = 'Gebeurtenis blok instansie verwyder';
$string['deleteblockinstance'] = 'Blok instansie verwyder: ';
$string['eventinstanceupdated'] = 'Gebeurtenis blok instansie opgedateer';
$string['updateblockinstance'] = 'Blok instansie opgedateer ';
$string['fromoldblockname'] = ' van die ou blok ';
$string['apicalled'] = 'SMOWL API oproep gemaak';
$string['apicalleddesc'] = 'SMOWL API oproep van instellings gemaak.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Interne SMOWL konfigurasies';
$string['internalconfigdesc'] = 'Die volgende opsies kan die werking van die plugin beïnvloed,'.
    ' dit moet slegs verander word op uitdruklike versoek van SMOWL';
$string['onlysmowlexpressrequest'] = 'Hierdie opsie moet slegs verander word op uitdruklike versoek van SMOWL.';

$string['viewsettingsinternal'] = 'Sien interne opsies';
$string['viewsettingsinternaldesc'] = 'As hierdie merkblokkie geaktiveer is, sal jy weer die opsies kan sien.';

$string['internalconfigurls'] = 'Interne URL SMOWL konfigurasies';
$string['urlstudentview'] = 'URL student siening';
$string['urlstudentviewdesc'] = 'Skakel na die student siening';

// API URLs.
$string['internalconfigapiurls'] = 'API SMOWL URL-konfigurasies';
$string['urlsmowlapi'] = 'API SMOWL URL';
$string['urlsmowlapidesc'] = 'URL om toegang tot die SMOWL API te verkry.';

$string['apilmssettings'] = 'Werk die LMS-konfigurasies op';
$string['apilmssettingsdesc'] = 'URL om die LMS-konfigurasies op te dateer.';

$string['apilmssettingscustomer'] = 'Outomatiese opdatering van LMS-konfigurasies';
$string['apilmssettingscustomerdesc'] = 'URL om die LMS-konfigurasies in outomatiese installasies op te dateer.';

$string['apiconfigclient'] = 'Integrasie aktivering';
$string['apiconfigclientdesc'] = 'URL om die integrasie aktiveringsdata te verkry.';

$string['apiaddactivity'] = 'Voeg aktiwiteit by';
$string['apiaddactivitydesc'] = 'URL om proctoring aktiwiteit by te voeg';

$string['apimodifyactivity'] = 'Wysig aktiwiteit';
$string['apimodifyactivitydesc'] = 'URL om die proctoring aktiwiteit te wysig';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'SMOWL toegangreëls konfigurasie';
$string['accesrulesmowlcheckcamconfigdesc'] = 'Die volgende opsies beïnvloed die toegangreëls vir vraelyste met proctoring.';
$string['accesrulesmowlcheckcam'] = 'Aktiveer kamera validering vir die student';
$string['accesrulesmowlcheckcamdesc'] = 'As kamera validering geaktiveer is, sal die student verplig wees om te valideer dat hul kamera korrek werk voordat hulle toegang tot die vraelyste kry.';

// View smowl settings.
$string['viewconfig'] = 'Vertoon konfigurasie';
$string['viewconfigdesc'] = 'Die volgende opsies beïnvloed die vertoon van die blok';
$string['floatsnap'] = 'Drywende blok in Snap tema';
$string['floatsnapdesc'] = 'Kies om die drywende blok te sien, werk slegs vir die SNAP tema.';

// Manage SMOWL Groups.
$string['managegroups'] = 'Groepbestuur';
$string['groupsaccessrestrictions'] = 'Toegangbeperkings';

$string['managegroupsformintro'] = 'In die volgende vorm moet die groepe of gebruikersgroeperings gekies word waaraan die SMOWL-blok gewys sal word.';
$string['managegroupsupdate'] = 'Groepe met SMOWL aktief, suksesvol opgedateer.';

$string['availabilityconditionsjsonform'] = 'Toegangbeperkings';
$string['availabilityconditionsjsonform_help'] = 'Vanuit hierdie menu kan die nodige toegangbeperkings bygevoeg word.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'Jy het nie toestemming om die groepbestuur te sien nie.';
$string['managegroupsnotconfigured'] = 'Die groepbestuur is nie gekonfigureer nie.';

// Bulk actions.
$string['bulkactions'] = 'Massa aksies';
$string['bulkactionsdesc'] = 'As geaktiveer, kan jy die soektog en aktivering van SMOWL in aktiwiteite vanaf die kampus se voorblad bestuur.';
$string['noticeactivebulkactions'] = 'Om hierdie funksionaliteit te aktiveer, moet jy na "Interne opsies" van SMOWL gaan';
$string['bulkactive'] = 'Aktivering van aktiwiteite';
$string['bulkgroups'] = 'Aktivering van groepe';
$string['coursecategory'] = 'Kursus kategorie';
$string['coursecategory_help'] = 'Kursus kategorie waar SMOWL geaktiveer moet word.';
$string['activitytype'] = 'Aktiwiteit tipe';
$string['activitytype_help'] = 'Tipe aktiwiteit waar SMOWL geaktiveer moet word.';
$string['activityname'] = 'Naam van die aktiwiteit';
$string['activityname_help'] = 'Naam van die aktiwiteit waar SMOWL geaktiveer moet word.';
$string['searchactivities'] = 'Soek aktiwiteite';
$string['allcategories'] = 'Alle kategorieë';
$string['searchresults'] = 'Soekresultate';
$string['notfound'] = 'Geen resultate gevind nie';
$string['savechanges'] = 'Stoor veranderinge';
$string['bulkactiveupdate'] = 'Massa SMOWL aktiwiteit konfigurasie suksesvol opgedateer';
$string['searchgroups'] = 'Soek groepe';
$string['groupname'] = 'Naam van die groep';
$string['groupname_help'] = 'Naam van die groep om SMOWL te aktiveer.';
$string['managebulkgroupsformintro'] = 'In die volgende vorm kan jy al die groepe sien wat uit die soektog voortspruit.';
$string['managebulkgroupsforminfo'] = 'Gebruikers wat aan enige van die geselekteerde groepe behoort, sal deur SMOWL gemonitor word.';
$string['managebulkgroupsformmoreinfo'] = 'BELANGRIK:<BR/> Die toewysing van groepe in hierdie vorm sal die toewysing van groepe wat in elk van die betrokke kursusse toegepas is, oorskryf.';
$string['notviewmanagebuklpermissions'] = 'Jy het nie toestemming om die massa SMOWL aktiwiteite te sien of te bestuur nie';

// Access Control Status.
$string['acwaiting'] = 'SMOWL hersiening, asseblief wag';
$string['acaccess'] = 'SMOWL geaktiveer, jy kan toegang tot jou aktiwiteit kry';
$string['acnotaccess'] = 'Kon nie SMOWL valideer nie, herlaai die blaaier om weer te probeer';

// LTI Integration.
$string['internalconfigltitool'] = 'SMOWL LTI URL-konfigurasie';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'LTI Basis URL';
$string['urlsmowlltitooldesc'] = 'Basis URL van die SMOWL LTI hulpmiddel';
$string['ltitoolinit'] = 'Aanvangs URL';
$string['ltitoolinitdesc'] = 'Aanvangs URL van die SMOWL LTI hulpmiddel';
$string['ltitoolversion'] = 'LTI Weergawe';
$string['ltitoolversiondesc'] = 'Weergawe van die SMOWL LTI hulpmiddel';
$string['ltitoolpublickeyset'] = 'Publieke sleutelstel';
$string['ltitoolpublickeysetdesc'] = 'URL van die publieke sleutelstel van die SMOWL LTI hulpmiddel';
$string['ltitoolinitiatelogin'] = 'Aanmeld URL';
$string['ltitoolinitiatelogindesc'] = 'Aanmeld URL van die SMOWL LTI hulpmiddel';
$string['ltitoolredirection'] = 'Herleiding URI';
$string['ltitoolredirectiondesc'] = 'Herleiding URI van die SMOWL LTI hulpmiddel';
$string['ltitoolconfigusage'] = 'Konfigurasie gebruik';
$string['ltitoolconfigusagedesc'] = 'Gebruik van die SMOWL LTI hulpmiddel konfigurasie as "Wys as vooraf gekonfigureerde hulpmiddel wanneer \'n eksterne hulpmiddel bygevoeg word"';
$string['ltitoollaunch'] = 'Standaard bekendstellingshouer';
$string['ltitoollaunchdesc'] = 'Standaard bekendstellingshouer van die SMOWL LTI hulpmiddel as "Inbed, sonder blokke"';
$string['ltitoolconfigmemberships'] = 'Standaard lidmaatskappe';
$string['ltitoolconfigmembershipsdesc'] = 'Standaard lidmaatskappe van die SMOWL LTI hulpmiddel as "Inbed, sonder blokke"';

$string['urlsmowlltiapi'] = 'LTI Basis API URL';
$string['urlsmowlltiapidesc'] = 'Basis URL van die SMOWL LTI API';
$string['ltiapiapplications'] = 'LTI Toepassings';
$string['ltiapiapplicationsdesc'] = 'LTI Toepassings van die SMOWL LTI API';
$string['ltiapideployments'] = 'LTI Ontplooiings';
$string['ltiapideploymentsdesc'] = 'LTI Ontplooiings van die SMOWL LTI API';

// LTI problems.
$string['lticreatetoolsuccess'] = 'SMOWL LTI hulpmiddel suksesvol geskep';
$string['ltiupdatetoolsuccess'] = 'SMOWL LTI hulpmiddel suksesvol opgedateer';
$string['lticreatetoolerror'] = 'Probleme om die SMOWL LTI hulpmiddel te skep';
$string['ltisendtoolerror'] = 'Probleme om die LTI konfigurasie na SMOWL te stuur';
$string['ltisendtoolneedactivation'] = 'Let wel! Dit lyk asof jou entiteit nog nie in die mySmowltech toepassing gevalideer is nie, wat verhoed dat die konfigurasie voltooi word. Klik <a href="https://my.smowltech.net/" target="_blank">hier</a> en meld aan met jou mySmowltech toegangbewyse om jou entiteit te valideer. Hierdie validering is nodig om die integrasie suksesvol te voltooi.';
$string['lticreatewserror'] = 'Probleme om die SMOWL WS te skep';
$string['ltiactivewserror'] = 'Probleme om die SMOWL WS te aktiveer';
$string['lticreateusererror'] = 'Daar is probleme om die "Smowl Webservices User" te skep. Administrateur aksie is nodig om dit te aktiveer.';
$string['noticeltinotvisible'] = 'Die LTI hulpmiddels is geblokkeer in die kampus administrasie (Plugins / Bestuur aktiwiteite).';
$string['ltisendwserror'] = 'Probleme om die WS inligting te stuur';
$string['ltiltiactivityerror'] = 'Probleme om die SMOWL LTI aktiwiteit te skep';
$string['notlticourse'] = 'Geen LTI in die kursus nie.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'SMOWL LTI interne konfigurasie';
$string['ltientity'] = 'LTI Entiteit';
$string['ltitypeid'] = 'LTI ID Tipe';
$string['ltideploymentid'] = 'LTI Ontplooiing';
$string['ltiappid'] = 'LTI Toepassing';
$string['ltirestid'] = 'LTI REST';

$string['ltiactivityname'] = 'SMOWL Paneel';

// Block links teacher.
$string['ltimanagesmowl'] = 'Toesig paneel';
$string['teachercontent'] = 'Konfigureer die monitering en hersien die resultate';
$string['teacherbutton'] = 'Gaan na SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'SMOWL Paneel';
$string['studentcontent'] = 'Gaan na die registrasie paneel en laai SMOWL af';
$string['studentbutton'] = 'Gaan na SMOWL';

$string['notstudentaccesspermissions'] = 'Slegs studente kan toegang tot hierdie afdeling kry';

// Corner
$string['drag_me'] = 'Sleep my';