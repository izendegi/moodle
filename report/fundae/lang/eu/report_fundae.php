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
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

$string['pluginname'] = 'Fundae Txostena';
$string['fundae:manage'] = 'Kudeatu Fundae Txostenak';
$string['fundae:view'] = 'Ikusi Fundae Txostenak';

$string['usertime'] = 'Erabiltzaileen Denborak';
$string['timeoncourse'] = 'Ikastaroko denbora';
$string['timeactivities'] = 'Denbora jardueretan';
$string['timescorms'] = 'Denbora SCORMetan';
$string['timezoom'] = 'Denbora Zoom-en';
$string['timebbb'] = 'Denbora BBB-en';
$string['timescreen'] = 'Pantailako denbora';
$string['timeinteracting'] = 'Interakzioen denbora';
$string['numberofsessions'] = 'Saio kopurua';
$string['daysonline'] = 'Konektatutako egun kopurua';
$string['ratioonline'] = 'Konexio-ratioa';
$string['messages'] = 'Mezu kopurua';
$string['messagestostudents'] = 'Ikasleei bidalitako mezuak';
$string['messagestoteachers'] = 'Irakasleei bidalitako mezuak';
$string['studentscontacted'] = 'Kontaktatutako ikasle kopurua';
$string['teacherscontacted'] = 'Kontaktatutako irakasle kopurua';
$string['access'] = 'Sarrera ikastarora';
$string['firstaccess'] = 'Lehen sarrera';
$string['lastaccess'] = 'Azken sarrera';
$string['activityfirstaccess'] = 'Lehen sarrera jarduerara';
$string['activitylastaccess'] = 'Azken sarrera jarduerara';
$string['actions'] = 'Ekintzak';
$string['details'] = 'Erabiltzailearen xehetasunak';
$string['nodata'] = '-';
$string['activityname'] = 'Jardueraren izena';
$string['screentime'] = 'Pantailan emandako denbora';
$string['engagedtime'] = 'Okupatutako denbora';
$string['hits'] = 'Ekitaldi kopurua';
$string['unabletogeneratereports'] = 'Cron-a desgaituta dagoela ematen du. Cron-a ez bada exekutatzen programatutako txostenak ez dira sortuko. Informazio gehiagorako zure Moodleko kudeatzailearekin harremanetan jarri zaitez.';
$string['general'] = 'Orokorra';
$string['enableteachersseereports'] = 'Erakutsi txostenak irakasleei';
$string['enableteachersseereports_desc'] = 'Irakasleek euren ikastaroetako txostenak ikusi eta jaitsi ahalko dituzte, eta bertan ikasle guztietako datuak ikusi ahalko dituzte';
$string['enablebbbstatistics'] = 'Mostrar estadÃ­sticas de Big Blue Button';
$string['enablebbbstatistics_desc'] = '<b>Marque esta opciÃ³n si tiene configurado Big Blue Button en su plataforma y lo utiliza en sus cursos.</b> Se mostrarÃ¡ una columna nueva en los informes con los tiempos de los alumnos en las reuniones de bbb, y se sumarÃ¡n los tiempos al de las actividades y el del curso.';
$string['enablestudentsseereports'] = 'Ikasleek txostenak ikusi ditzakete';
$string['enablestudentsseereports_desc'] = 'Ikasleek euren txostenak ikusi eta deskargatru ahalko dituzte, eta bertan euren datuak baino ez dituzte ikusiko.<br><b>Aukera hau gaitu ala desgaitzeak "moodle/site:viewreports" baimena ikasleentzako gaitu/desgaituko du ikastaroetako Txostenak fitxan euren txostena ikusi dezaten.</b>';
$string['timetracking'] = 'Denboraren jarraipena';
$string['sessionthreshold'] = 'Saioaren atalasea (minututan)';
$string['sessionthreshold_desc'] = 'Saio bakoitza jarraian dauden bi agerraldiren artean (esanguratsua den erregistratutako ekitaldia) hemen zehaztutako gehieneko denbora-tartea gainditzen ez den bi agerraldi edo gehiagoren multzoaren bitartez kalkulatzen da. Kontuan izan hemen errealista ez den atalase handi bat zehazteak saioen iraupenaren gainestimazioa eragingo lukeela.';
$string['reportgeneration'] = 'Txosten-sorrera';
$string['enablescheduledreportgeneration'] = 'Gaitu programatutako txosten-sorrera';
$string['enablescheduledreportgeneration_desc'] = 'Gaitu ala desgaitu programatutako txosten-sorrera. Lehenetsitako programazioa Gunearen Kudeaketa > Zerbitzaria > Atazak > Programatutako atazak ataletik aldatu daiteke.';
$string['bulkscheduledreportgenerationmode'] = 'Programatutako txostenen masiboen formatua';
$string['bulkscheduledreportgenerationmode_desc'] = 'Ezarpen hau soilik aplikatzen da programatutako txostenak ezarpena gaituta badago. \'Ikastaro guztiak\' aukeratzen bada programatuta dagoen unean ikastaro guztien txostenen fitxategiak sortuko dira. \'Ikastaro eta jarduera guztiak\' aukeratzen bada programatuta dagoen unean ikastaro eta jarduera guztien txostenen fitxategiak sortuko dira.';
$string['disabled'] = 'Desgaituta';
$string['allcourses'] = 'Ikastaro guztiak';
$string['allcoursesandactivities'] = 'Ikastaro eta jarduera guztiak';
$string['scheduledreportmailing'] = 'Bidali posta elektronikoz programatutako txostenak';
$string['scheduledreportmailing_desc'] = 'Hemen zehaztutako komaz banatutako helbide elektronikoek aukeratutako edo masiboki programatutako txostenen fitxategiak sortzen diren aldiro jasoko dituzte.';
$string['scheduledreportgenerationformat'] = 'Programatutako txostenen formatua';
$string['scheduledreportgenerationformat_desc'] = 'Aukeratu ezazu programatutako txostenentzako fitxategi-formatua.';
$string['ftpnoextension'] = 'Txostenak zerbitzarira igotzeko PHPko beharrezko "ftp" luzapena eta FTP konfigurazioa ez da instalatu ala gaitu';
$string['sftpnoextension'] = 'Txostenak zerbitzarira igotzeko PHPko beharrezko "ssh2" luzapena eta SFTP konfigurazioa ez da instalatu ala gaitu';
$string['enableextensions'] = 'Txostenak zerbitzarira igotzeko beharrezkoak diren PHP luzapenak gaitzen direnean zerbitzarira konektatzeko eremuak erakutsiko dira';
$string['anyextensionsupload'] = 'Ez dago instalatuta edo gaituta fitxategiak igotzeko PHP luzapenik';
$string['ftpconfig'] = 'SFTP/FTP Ezarpenak';
$string['ftpinformation'] = 'Fitxategiak SFTP/FTP bidez igotzeak soilik aplikatzen dira programatutako txostenak gaituta daudenean. SFTP/FTP ezarpen baliagarriak zehazten direnean, programatutako txostenak sortzen den bakoitzean dokumentua zehaztutako bidera igoko da.';
$string['credentialsjson'] = 'JSON Kredentzialak';
$string['credentialsjson_help'] = 'Zehaztu credentials.json fitxategiaren edukia.<br>Credentials JSON fitxategia Google-ko konexio-API konfiguratzean lortzen den fitxategia da, kredentzialen panelean, https://console.developers.google.com/ helbidean';
$string['typehost'] = 'Hostalari mota';
$string['typehost_help'] = 'Erabiliko den konexio mota';
$string['clientid'] = 'Bezeroaren IDa';
$string['clientid_help'] = 'JSON fitxategiaren "client_id" eremua';
$string['clientsecret'] = 'Bezeroaren Sekretua';
$string['clientsecret_help'] = 'JSON fitxategiaren "client_secret" eremua';
$string['folderid'] = 'Karpetaren IDa';
$string['folderid_help'] = 'Google Drive-ko karpetaren IDa. URLan erakusten da.';
$string['historicfolderid'] = 'Historiaren Karpetaren IDa';
$string['historicfolderid_help'] = 'Google Drive-ko fitxategi-historia gordetzen den karpetaren IDa. URLan erakusten da.';
$string['ftphost'] = 'Ostalaria';
$string['ftpport'] = 'Ataka';
$string['ftpuser'] = 'Erabiltzaile-izena';
$string['ftppassword'] = 'Pasahitza';
$string['ftpremotepath'] = 'Urruneko fitxategi-bidea';
$string['ftphost_help'] = 'FTP ostalaria protokolo eta atakarik gabe. Adibidez: ftp.moodle.org';
$string['ftpport_help'] = 'FTP ataka. Adibidez: 21';
$string['ftpuser_help'] = 'Baliozko FTP erabiltzaile-izena.';
$string['ftppassword_help'] = 'Baliozko FTP pasahitza.';
$string['ftpremotepath_help'] = 'Urruneko bidea. Adibidez: /home/erabiltzailea/igoerak/';
$string['generateperiodicreports'] = 'Sortu Programatutako txostenak';
$string['scheduledreportssubject'] = 'Programatutako txostenak {$a}';
$string['scheduledreportsmessage'] = '<p>Programatutako txostenak erantsita aurkituko dituzu</p>';
$string['ftpsettingerror'] = 'SFTP/FTP ezarpenak zehaztu gabe daude ala baliogabeak dira';
$string['ftpuploaded'] = '{$a} igo da';
$string['ftpnotuploaded'] = 'Ezin izan da {$a} igo';
$string['ftpconexion'] = 'Konexioa egin da';
$string['ftpuploading'] = 'Fitxategiak {$a}(e)ra igotzen';
$string['ftploginerror'] = 'Errorea saio-hasieran. Ezin izan da saioa hasi SFTP/FTP hostalarian emandako erabiltzaile eta pasahitzarekin.';
$string['ftpuploaderror'] = 'Errorea fitxategia igotzerakoan. Errorea gertatu da txostenaren fitxategia SFTP/FTP zerbitzarira igotzerakoan.';
$string['ftpconnectionerror'] = 'Konexio-errorea. Ezin izan da konektatu SFTP/FTP zerbitzariarekin.';
$string['missinggdrive'] = 'Google Drive-ra fitxategiak igotzeko konfigurazioa falta da';
$string['conexiongdrive'] = 'Konexioa egin da Google Drive-rekin, karpetak bilatzen.';
$string['folderdfound'] = 'Karpeta aurkitu da: ';
$string['folderdnotfound'] = 'Ez da karpetarik aurkitu.';
$string['deleteoldfile'] = 'Lehenagoko fitxategia ezabatzen: {$a}';
$string['createfile'] = 'Fitxategi berria sortuta: {$a}';
$string['createhistoricfile'] = 'Fitxategi historikoa sortuta: {$a}';
$string['ftpupload'] = 'Fitxategiak zerbitzarira igotzea:';
$string['coursecompletion'] = '% osatuta';
$string['coursecompletion_help'] = 'Erabiltzaile honek osatutako ikastaroaren ehunekoa.

Kontuan izan ikastaro batzuk jarduerak izan arren osaketaren jarraipena desgaituta izan ahal dutela.
';
$string['totalactivitiestimes'] = 'Jardueretan emandako denbora';
$string['totalactivitiestimes_help'] = 'Ikastaroko jardueretara egotzi dakieken dedikazio-denbora, SCORM jarduerak alde batera utzita.';
$string['totalscormtime'] = 'SCORMetan emandako denbora';
$string['totalzoomtime'] = 'Denbora Zoom-en';
$string['totalbbbtime'] = 'Denbora BBB-en';
$string['totalscormtime_help'] = 'Erabiltzailea ikastaro barruko SCORM guztietan erregistratutako denboraren batura.';
$string['dedicationtime'] = 'Emandako denbora';
$string['dedicationtime_help'] = 'Emandako denbora erregistro-sarrera / agerraldi esanguratsuei Saioa eta Saioaren iraupena kontzeptuak aplikatzean oinarrituta dago, **SCORM jarduerak alde batera utzita**.

* **Agerraldia:** Erabiltzaile bat Moodleko orri batera sartzen den bakoitzean erregistro-sarrera bat gordetzen da.
* **Saioa:** jarraian dauden bi agerraldiren artean zehaztutako gehieneko denbora-tartea ({$a} minutu) gainditzen ez den bi agerraldi edo gehiagoren multzoa.
* **Saioaren iraupena:** saio bateko lehen eta azken agerraldien arteko denbora-tartea.
* **Emandako denbora:** erabiltzaile baten saio guztien iraupenaren batura.
';
$string['connectionratio'] = 'Konexio-ratioa';
$string['connectionratio_help'] = '**Konektatutako egunak / Ikastaroaren iraupena (egunetan)**

1etik gertuko balioek ikastaroa hasi zenetik erabiltzailea ia egunero konektatu dela esan nahi dute.

0tik gertuko balioek ikastaroa hasi zenetik erabiltzailea egun gutxitan konektatu dela esan nahi dute.
';
$string['activitiesreport'] = 'Jardueren Txostena';
$string['viewuser'] = 'Ikusi erabiltzailea';
$string['filteroptions'] = 'Iragazkiak';
$string['resultsperpage'] = 'Emaitzak orriko';
$string['applyfilters'] = 'Aplikatu iragazkiak';
$string['coursereport'] = 'Ikastaro-txostena';
$string['entitycoursecompletion'] = 'Ikastaro-osaketa';
$string['profiledepartment'] = 'Profileko departamentua';
$string['entitycourseenrolment'] = 'Ikastaroko matrikulazioa';
$string['lastcourseaccess'] = 'Azken sarrera ikastarora';
$string['course_enrolment_timestarted'] = 'Matrikulazioaren hasiera-data';
$string['course_enrolment_timeended'] = 'Matrikulazioaren amaiera-data';
$string['course_completion_days_course'] = 'Ikastaron emandako egun kopurua';
$string['course_completion_days_enrolled'] = 'Matrikulatutako egun kopurua';
$string['course_completion_progress'] = 'Aurrerapena';
$string['course_completion_progress_percent'] = 'Aurrerapena (%)';
$string['course_completion_reaggregate'] = 'Denbora bateratua';
$string['course_completion_timecompleted'] = 'Osatutako denbora';
$string['course_completion_timeenrolled'] = 'Matrikulatutako denbora';
$string['course_completion_timestarted'] = 'Hasiera unea';
$string['course_enrolment_status'] = 'Matrikulazioaren egoera';
$string['lessthanaday'] = 'Egun bat baino gutxiago';
$string['allusers'] = 'Erabiltzaile guztiak';
$string['selectuser'] = 'Aukeratu erabiltzaile bat';

