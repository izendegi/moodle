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

$string['"typeconfigheading'] = 'Konfigurationstyp';
$string['typeconfigheadingdesc'] = 'Wählen Sie den Konfigurationstyp aus <br> ·  Automatisch <br> ·  Manuell <br> ·  Konfigurieren Sie später';
$string['typeconfigheadingdesc2'] = 'Sie können dann Ihren Konfigurationstyp ändern';

$string['typeconfig'] = 'Konfigurationstyp';
$string['typeconfigdesc'] = 'Wählen Sie die Aktion aus, die Sie unten ausführen möchten';
$string['noconfigmessage'] = 'Sie haben SMOWL konfigurieren in der Folge ausgewählt, Sie können dies von der Campus-Administration aus tun.';

$string['changetypeconfigauto'] = 'Wechseln Sie zur automatischen Konfiguration.';
$string['changetypeconfigrestartauto'] = 'Starten Sie die automatische Konfiguration neu.';
$string['changetypeconfigmanual'] = 'Wechseln Sie zur manuellen Konfiguration.';
$string['changetypeconfigcancel'] = 'Konfigurationsänderung abbrechen.';
$string['changetypeconfigafter'] = 'Konfigurieren Sie es später.';

$string['typeask'] = 'Wählen Sie eine Option aus';
$string['typenoconfig'] = 'Konfigurieren Sie es später';
$string['typeautoconfig'] = 'Automatische Konfiguration';
$string['typemanualconfig'] = 'Manuelle Konfiguration';

$string['dataautoconfigheading'] = 'Automatische Konfigurationsdaten';
$string['dataautoconfigheadingdesc'] = 'Sie können nun die automatischen Konfigurationsdaten überprüfen oder eingeben';

$string['autoconfigmessagejsondataclient'] = 'Die Clientinformationen sind in diesem Plugin vorab konfiguriert.';
$string['autoconfigmessageconfigdone'] = 'Das SMOWL-Plugin wurde automatisch konfiguriert';

$string['autoconfigmessageclientvoid'] = 'Die Clientinformationen müssen ordnungsgemäß konfiguriert werden';
$string['autoconfigmessagecontenterror'] = 'Automatische Konfiguration fehlgeschlagen: Rückkehr mit ungültigen Elementen';
$string['autoconfigmessageunauthorized'] = 'Automatische Konfiguration fehlgeschlagen: Authentifizierungsprobleme';
$string['autoconfigmessagebadrequest'] = 'Automatische Konfiguration fehlgeschlagen: Ungültige Anfrage';
$string['autoconfigmessageentityerror'] = 'Automatische Konfiguration fehlgeschlagen: Entität konnte nicht abgerufen werden';
$string['autoconfigmessageconflict'] = 'Automatische Konfiguration fehlgeschlagen: Interne Probleme in der Entität';

$string['clientid'] = 'Client-ID';
$string['clientiddesc'] = 'Client-ID zum Aktivieren des Campus';

$string['clientkey'] = 'Aktivierungsschlüssel';
$string['clientkeydesc'] = 'Aktivierungsschlüssel zum Aktivieren des Campus';

$string['nocapabilityconfigmessage'] = 'Sie haben keine Berechtigung, diese Aktion auszuführen';

// Connection smowl settings.
$string['connectionsconfig'] = 'Verbindungseinstellungen';
$string['connectionsconfigdesc'] = 'Die folgenden Optionen ermöglichen Ihnen die Verbindung zu SMOWL';
$string['connectionsconfigcontact'] = 'Weitere Informationen zu Ihrer aktuellen Lizenz finden Sie in Ihrem Kundenkonto unter <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Plattform';
$string['entitydesc'] = 'ID von Smowltech bereitgestellt. ';
$string['noticeemptyentitysettings'] = 'Die Plattform ist leer, kontaktieren Sie <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>, um den einzugebenden Wert zu erfahren. ';

$string['password'] = 'Lizenzschlüssel';
$string['passworddesc'] = 'Lizenzschlüssel für die Plattform, bereitgestellt von Smowltech.';
$string['noticeemptypasswordsettings'] = 'Der Lizenzschlüssel ist leer, kontaktieren Sie Smowltech, um den einzugebenden Wert zu erfahren.';

$string['apikey'] = 'API-Schlüssel';
$string['apikeydesc'] = 'Schlüssel zum Zugriff auf die API, bereitgestellt von Smowltech.';
$string['noticeemptyapikeysettings'] = 'Der API-Schlüssel ist leer, kontaktieren Sie Smowltech, um den einzugebenden Wert zu erfahren.';

// JWT Secret
$string['smowljwtsecret'] = 'JWT-Schlüssel';
$string['smowljwtsecretdesc'] = 'JWT-Schlüssel für die Plattform, bereitgestellt von Smowltech.';
$string['noticeemptysmowljwtsecret'] = 'Der JWT-Schlüssel ist leer, kontaktieren Sie Smowltech, um den einzugebenden Wert zu erfahren.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Einstellungen für Instruktoren';
$string['instructorsconfigdesc'] = 'Die folgenden Optionen passen die Verwendung von SMOWL für Instruktoren in den Kursen an, in denen der Block aktiv ist.';

$string['continuousassessment'] = 'Kontinuierliche Bewertung';
$string['onlyexams'] = 'Prüfungsüberwachung';

$string['tracking'] = 'Überwachungstyp';
$string['trackingdesc'] = 'Wählen Sie aus, ob der SMOWL-Überwachungstyp eine kontinuierliche Bewertung oder nur Prüfungen ist.';

$string['attempttracking'] = 'Unterscheidung der Prüfungsversuche';
$string['attempttrackingdesc'] = 'Wählen Sie aus, ob Sie zwischen verschiedenen Versuchen desselben Tests für einen Schüler unterscheiden möchten.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Erweiterte Einstellungen für Instruktoren';
$string['viewsettingsadvancedinstructors'] = 'Erweiterte Einstellungen für Instruktoren anzeigen';
$string['viewsettingsadvancedinstructorsdesc'] = 'Wenn diese Option aktiviert ist, werden die erweiterten Einstellungen für Instruktoren angezeigt.';

$string['accesscontrol'] = 'Zugangskontrolle';
$string['accesscontroldesc'] = 'Erlauben Sie Benutzern den Zugriff auf die Prüfung nur, wenn die SMOWL-Tools aktiv sind';

// Settings advancer users.
$string['usersconfig'] = 'Benutzereinstellungen';
$string['usersconfigdesc'] = 'Die folgenden Optionen passen die Verwendung von SMOWL für Benutzer in den Aktivitäten und Kursen an, in denen das Überwachungssystem aktiv ist.';

$string['floatblock'] = 'Aktivieren Sie den schwebenden Überwachungsblock';
$string['floatblockdesc'] = 'Aktivieren Sie den schwebenden Block, um ihn anzuzeigen';
$string['activeinpopup'] = 'Die Überwachung ist im schwebenden Block verfügbar';

$string['blockheight'] = 'Bildschirmgröße';
$string['blockheightdesc'] = 'Höhe des Iframe, in dem die Webcam während der Überwachung angezeigt wird.';
$string['noticeblockheight'] = 'Die Iframe-Höhe darf nicht kleiner als 280 px sein.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Erweiterte Einstellungen für Benutzer';
$string['viewsettingsadvancedusers'] = 'Erweiterte Einstellungen für Benutzer anzeigen';
$string['viewsettingsadvancedusersdesc'] = 'Wenn diese Option aktiviert ist, werden die erweiterten Einstellungen für Benutzer angezeigt.';

// Capabilities.
$string['smowl:addinstance'] = 'SMOWL-Block hinzufügen';
$string['smowl:manageactivities'] = 'SMOWL-Aktivitätenverwaltung';
$string['smowl:managermanageacti'] = 'Der Manager kann SMOWL-Aktivitäten verwalten';
$string['smowl:managegroups'] = 'SMOWL-Gruppen verwalten';
$string['smowl:viewstudentcontent'] = 'Schülfergebnisse anzeigen';
$string['smowl:enrolment'] = 'Zugriff auf SMOWL-Registrierung';

// Notices.
$string['notinstancedblockincourse'] = 'Um diese Aktion auszuführen, müssen Sie den SMOWL-Block im Kurs erstellen.';
$string['notmanagepermissions'] = 'Sie haben keine Berechtigung, SMOWL-Aktivitäten zu verwalten.';
$string['notteachersmanagementpermissions'] = 'Lehrer haben keine Berechtigung, SMOWL-Aktivitäten zu verwalten.';
$string['notviewmanagepermissions'] = 'Sie haben keine Berechtigung, SMOWL-Aktivitäten anzuzeigen oder zu verwalten.';
$string['notviewmanagegroupspermissions'] = 'Sie haben keine Berechtigung, SMOWL-Gruppen anzuzeigen oder zu verwalten.';
$string['notviewmanageuserspermissions'] = 'Sie haben keine Berechtigung, SMOWL-Benutzer anzuzeigen oder zu verwalten.';
$string['notviewmanageinstructorspermissions'] = 'Sie haben keine Berechtigung, SMOWL-Instruktoren anzuzeigen oder zu verwalten.';
$string['cannotcreatefile'] = 'Die Datei konnte nicht erstellt werden';
$string['activitiesupdate'] = 'SMOWL-Aktivitäten aktualisiert';
$string['activityupdate'] = 'SMOWL-Aktivität aktualisiert';
$string['noticeemptysmowlconfig'] = 'Die Blockkonfiguration wurde nicht abgeschlossen. Wenden Sie sich an den Plattformadministrator, um das Problem zu lösen.';
$string['noticeemptyentitynavigation'] = 'Die Blockkonfiguration wurde nicht abgeschlossen. Wenden Sie sich an den Plattformadministrator, um das Problem zu lösen.';

// Privacy.
$string['privacy:metadata'] = 'Das SMOWL-System zeigt nur die auf den Smowltech-Servern gespeicherten Daten an.';
$string['privacy:metadata:smowl:smowltech_net'] = 'Das SMOWL-System zeigt nur die auf den Smowltech-Servern gespeicherten Daten an und überträgt die Moodle-Benutzerdaten an die Smowltech-Server.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Eindeutiger Benutzeridentifikator';

// Events.
$string['eventinstancecreated'] = 'Ereignis erstellte Blockinstanz';
$string['createblockinstance'] = 'Blockinstanz erstellt ';
$string['eventinstancedeleted'] = 'Ereignis gelöschte Blockinstanz';
$string['deleteblockinstance'] = 'Blockinstanz gelöscht ';
$string['eventinstanceupdated'] = 'Ereignis aktualisierte Blockinstanz';
$string['updateblockinstance'] = 'Blockinstanz aktualisiert ';
$string['fromoldblockname'] = ' von alter Block ';
$string['apicalled'] = 'API SMOWL-Aufruf durchgeführt';
$string['apicalleddesc'] = 'API SMOWL-Aufruf mit Parametern durchgeführt.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Interne SMOWL-Konfigurationen';
$string['internalconfigdesc'] = 'Die folgenden Optionen können die Funktionsweise des Plugins beeinflussen, sie sollten nur auf ausdrücklichen Wunsch von SMOWL geändert werden.';
$string['onlysmowlexpressrequest'] = 'Diese Option sollte nur auf ausdrücklichen Wunsch von SMOWL geändert werden.';
$string['viewsettingsinternal'] = 'Interne Einstellungen anzeigen';
$string['viewsettingsinternaldesc'] = 'Aktivieren Sie diese Überprüfung, um die internen SMOWL-Optionen anzuzeigen.';

$string['internalconfigurls'] = 'Interne SMOWL-URLs';
$string['urlstudentview'] = 'Schüleransicht';
$string['urlstudentviewdesc'] = 'Link zur Schüleransicht.';

// API URLs.
$string['internalconfigapiurls'] = 'SMOWL API URL settings';
$string['urlsmowlapi'] = 'SMOWL-API-URL';
$string['urlsmowlapidesc'] = 'URL zum Zugriff auf die SMOWL-API.';

$string['apilmssettings'] = 'LMS-Konfigurations-URL';
$string['apilmssettingsdesc'] = 'URL zum Aktualisieren der LMS-Konfigurationen in automatischen Installationen.';

$string['apilmssettingscustomer'] = 'Kunden-LMS-Konfigurations-URL';
$string['apilmssettingscustomerdesc'] = 'URL zum Aktualisieren der Kunden-LMS-Konfigurationen in automatischen Installationen.';

$string['apiconfigclient'] = 'Aktivierungs-URL der Integration';
$string['apiconfigclientdesc'] = 'URL zum Aktivieren der Integration in automatischen Installationen.';

$string['apiaddactivity'] = '';
$string['apiaddactivitydesc'] = '';

$string['apimodifyactivity'] = '';
$string['apimodifyactivitydesc'] = '';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'Aktivierungs-URL der Webcam-Prüfung';
$string['accesrulesmowlcheckcamconfigdesc'] = 'URL zum Aktivieren der Webcam-Prüfung in automatischen Installationen.';
$string['accesrulesmowlcheckcam'] = 'Webcam-Prüflink';
$string['accesrulesmowlcheckcamdesc'] = 'URL zum Zugriff auf die Webcam-Prüfung in automatischen Installationen.';

// View smowl settings.
$string['viewconfig'] = 'Konfigurationen anzeigen';
$string['viewconfigdesc'] = 'Die folgenden Optionen beeinflussen die Anzeige des Blocks ';
$string['floatsnap'] = 'Aktivieren Sie den schwebenden Block im Snap-Design';
$string['floatsnapdesc'] = 'Aktivieren Sie, um den schwebenden Block anzuzeigen. Es funktioniert nur für das SNAP-Design';

// Manage SMOWL Groups.
$string['managegroups'] = 'Gruppenverwaltung';
$string['groupsaccessrestrictions'] = 'Zugangsbeschränkungen';

$string['managegroupsformintro'] = 'Im folgenden Formular müssen Sie die Benutzergruppen oder Gruppierungen auswählen, für die der SMOWL-Block angezeigt wird.';
$string['managegroupsupdate'] = 'Gruppen mit aktivem SMOWL wurden erfolgreich aktualisiert.';

$string['availabilityconditionsjsonform'] = 'Zugangsbeschränkungen';
$string['availabilityconditionsjsonform_help'] = 'In diesem Menü können Sie die erforderlichen Zugangsbeschränkungen hinzufügen.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'Sie haben keine Berechtigung, Gruppen zu verwalten.';
$string['managegroupsnotconfigured'] = 'Die Gruppenverwaltung ist nicht konfiguriert.';

// Bulk actions.
$string['bulkactions'] = 'Massenaktionen';
$string['bulkactionsdesc'] = 'Die folgenden Optionen ermöglichen es Ihnen, SMOWL in Massen zu aktivieren oder zu deaktivieren.';
$string['noticeactivebulkactions'] = 'Das Aktivieren oder Deaktivieren von SMOWL in Massen kann je nach Anzahl der ausgewählten Aktivitäten oder Gruppen einige Zeit in Anspruch nehmen.';
$string['bulkactive'] = 'Aktivierung von Aktivitäten';
$string['bulkgroups'] = 'Gruppenaktivierung';
$string['coursecategory'] = 'Kurskategorie';
$string['coursecategory_help'] = 'Kategorie des Kurses, in dem SMOWL aktiv ist.';
$string['activitytype'] = 'Aktivitätstyp';
$string['activitytype_help'] = 'Aktivitätstyp, in dem SMOWL aktiv ist.';
$string['activityname'] = 'Aktivitätsname';
$string['activityname_help'] = 'Aktivitätsname, in dem SMOWL aktiv ist.';
$string['searchactivities'] = 'Aktivitäten suchen';
$string['allcategories'] = 'Alle Kategorien';
$string['searchresults'] = 'Suchergebnisse';
$string['notfound'] = 'Keine Ergebnisse gefunden';
$string['savechanges'] = 'Änderungen speichern';
$string['bulkactiveupdate'] = 'Massen-SMOWL-Konfigurationen für Aktivitäten wurden erfolgreich aktualisiert.';
$string['searchgroups'] = 'Gruppen suchen';
$string['groupname'] = 'Gruppenname';
$string['groupname_help'] = 'Gruppenname, in dem SMOWL aktiv ist.';
$string['managebulkgroupsformintro'] = 'Im folgenden Formular können Sie alle Gruppen von Kursen sehen, die aus der Suche resultieren.';
$string['managebulkgroupsforminfo'] = 'Benutzer, die einer der ausgewählten Gruppen angehören, können auf die Ansicht zugreifen.';
$string['managebulkgroupsformmoreinfo'] = 'Es ist wichtig, dass alle Benutzer des Kurses mindestens einer Gruppe angehören.';
$string['notviewmanagebuklpermissions'] = 'Sie haben keine Berechtigung, SMOWL-Aktivitäten in Massen anzuzeigen oder zu verwalten.';

// Access Control Status.
$string['acwaiting'] = 'SMOWL Überprüfung, bitte warten';
$string['acaccess'] = 'SMOWL wurde aktiviert, Sie können Ihre Aktivität starten';
$string['acnotaccess'] = 'SMOWL wurde nicht validiert, laden Sie den Browser neu, um erneut zu überprüfen';

// LTI Integration.
$string['internalconfigltitool'] = 'LTI SMOWL-Konfigurations-URL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'Basis-URL LTI';
$string['urlsmowlltitooldesc'] = 'Basis-URL des LTI SMOWL-Tools';
$string['ltitoolinit'] = 'Initiale URL';
$string['ltitoolinitdesc'] = 'Initiale URL des LTI SMOWL-Tools';
$string['ltitoolversion'] = 'LTI-Version';
$string['ltitoolversiondesc'] = 'Version des LTI SMOWL-Tools';
$string['ltitoolpublickeyset'] = 'Public Keyset-URL';
$string['ltitoolpublickeysetdesc'] = 'URL des öffentlichen Keysets des LTI SMOWL-Tools';
$string['ltitoolinitiatelogin'] = 'Initiale Login-URL';
$string['ltitoolinitiatelogindesc'] = 'Initiale Login-URL des LTI SMOWL-Tools';
$string['ltitoolredirection'] = 'Redirection-URI';
$string['ltitoolredirectiondesc'] = 'Redirection-URI des LTI SMOWL-Tools';
$string['ltitoolconfigusage'] = 'Verwendung der Konfiguration';
$string['ltitoolconfigusagedesc'] = 'Verwendung der Konfiguration des LTI SMOWL-Tools als "Als vorab konfiguriertes Tool anzeigen, wenn eine externe Aktivität hinzugefügt wird"';
$string['ltitoollaunch'] = 'Standard-Launch-Container';
$string['ltitoollaunchdesc'] = 'Standard-Launch-Container des LTI SMOWL-Tools als "Einbetten, ohne Blöcke"';
$string['ltitoolconfigmemberships'] = 'Standardmitglieder';
$string['ltitoolconfigmembershipsdesc'] = 'Standardmitglieder des LTI SMOWL-Tools als "Einbetten, ohne Blöcke"';

$string['urlsmowlltiapi'] = 'Basis-URL der LTI-API';
$string['urlsmowlltiapidesc'] = 'Basis-URL der LTI SMOWL-API';
$string['ltiapiapplications'] = 'LTI-Anwendungen';
$string['ltiapiapplicationsdesc'] = 'LTI-Anwendungen der LTI SMOWL-API';
$string['ltiapideployments'] = 'LTI-Deployments';
$string['ltiapideploymentsdesc'] = 'LTI-Deployments der LTI SMOWL-API';

// LTI problems.
$string['lticreatetoolsuccess'] = 'LTI SMOWL-Tool erfolgreich erstellt';
$string['ltiupdatetoolsuccess'] = 'LTI SMOWL-Tool erfolgreich aktualisiert';
$string['lticreatetoolerror'] = 'Probleme beim Erstellen des LTI SMOWL-Tools';
$string['ltisendtoolerror'] = 'Probleme beim Senden der LTI-Konfiguration an SMOWL';
$string['ltisendtoolneedactivation'] = 'Aufmerksamkeit! Es scheint, dass Ihre Entität in der mySmowltech-App noch nicht validiert wurde, was den Abschluss der Plugin-Konfiguration verhindert. Klicken <a href="https://my.smowltech.net/" target="_blank">Sie hier</a> und melden Sie sich mit Ihren Zugangsdaten bei mySmowltech an, um Ihre Entität zu validieren. Diese Validierung ist notwendig, um die Integration korrekt abzuschließen.';
$string['lticreatewserror'] = 'Probleme beim Erstellen des WS SMOWL';
$string['ltiactivewserror'] = 'Probleme beim Aktivieren des WS SMOWL';
$string['lticreateusererror'] = 'Es gibt Probleme beim Erstellen des Benutzers "Smowl Webservices User". Eine Aktion des Administrators ist erforderlich, um ihn zu aktivieren.';
$string['noticeltinotvisible'] = 'LTI-Tools sind in der Campus-Verwaltung gesperrt (Plugins / Aktivitäten verwalten).';
$string['ltisendwserror'] = 'Probleme beim Senden der WS-Informationen';
$string['ltiltiactivityerror'] = 'Probleme beim Erstellen der LTI SMOWL-Aktivität';
$string['notlticourse'] = 'Do not have LTI in course.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Interne LTI SMOWL-Konfigurationen';
$string['ltientity'] = 'LTI-Plattform';
$string['ltitypeid'] = 'LTI-Typ-ID';
$string['ltideploymentid'] = 'LTI-Deployment';
$string['ltiappid'] = 'LTI-Anwendung';
$string['ltirestid'] = 'LTI-REST';

$string['ltiactivityname'] = 'SMOWL-panel';

// Block links teacher.
$string['ltimanagesmowl'] = 'Überwachungs-Dashboard';
$string['teachercontent'] = 'Richten Sie die Nachverfolgung ein und überprüfen Sie die Ergebnisse';
$string['teacherbutton'] = 'Zugriff auf SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'SMOWL-panel';
$string['studentcontent'] = 'Access the SMOWL registration and download panel';
$string['studentbutton'] = 'Zugriff auf SMOWL';

$string['notstudentaccesspermissions'] = 'Auf diesen Bereich haben nur Studierende Zugriff';

// Corner
$string['drag_me'] = 'Zieh mich';