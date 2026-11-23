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
 * Strings for component 'enrol_database', language 'eu'.
 *
 * @package     enrol_database
 * @copyright   1999 onwards Martin Dougiamas  {@link http://moodle.com}
 *              2024 onwards Iñigo Zendegi  {@link https://mondragon.edu}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['autocreatecategory'] = 'Sortu ikastaro-kategoriak';
$string['autocreatecategory_desc'] = 'Markatuz gero, sortu behar diren ikastaroak oraindik Moodlen existitzen ez diren kategorietan kokatu behar badira, kategoria horiek automatikoki sortuko dira.';
$string['categoryseparator'] = 'Kategorien karaktere-banatzailea';
$string['categoryseparator_desc'] = 'Eremu hau hutsik utzi ezazu zure kanpoko datu-basean azpikategoriak erabili nahi ez badituzu. Bestela, zehaztu kategoriak banatzeko erabiltzen duzun karakterea. Azpikategoriaren \'bidea\' zehaztu beharko duzu (\'Ikastaro berrien kategoria\' eremuan) karaktere-banatzaileaz banatutako kategorien identifikatzaileen zerrenda gisa. Esaterako, karaktere banatzailea \'/\' bada, kategoria1/kategoria2 moduko zerbait zehaztu beharko duzu (non kategoria1 maila goreneko kategoria eta kategoria2 kategoria1 barruko azpikategoria izango diren)';
$string['database:config'] = 'Konfiguratu datu-base bidezko matrikulazio instantziak';
$string['database:unenrol'] = 'Desmatrikulatu kontua etenda duten erabiltzaileak';
$string['dbencoding'] = 'Datu-basearen kodifikazioa';
$string['dbhost'] = 'Datu-basearen ostalaria';
$string['dbhost_desc'] = 'Idatzi datu-basearen zerbitzariaren IPa edo izena. Erabili sistemako DSN izen bat ODBC erabiliz gero. Erabili PDO DSN bat PDO erabiliz gero.';
$string['dbname'] = 'Datu-basearen izena';
$string['dbname_desc'] = 'Hutsik utzi datu-base ostalarian DSN izena erabiliz gero.';
$string['dbpass'] = 'Datu-basearen pasahitza';
$string['dbsetupsql'] = 'Datu-basea konfiguratzeko komandoa';
$string['dbsetupsql_desc'] = 'Datu-basearen konfigurazio berezirako SQL komandoa, maiz komunikazio kodifikazioa konfiguratzeko erabilia - MySQL eta PostgreSQLrako adibidea: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Erabili sybase kakotxak (quotes)';
$string['dbsybasequoting_desc'] = 'Sybase motako kakotx sinpleen ihesbidea - beharrezkoa Oracle, MS SQL eta beste datu-base batzuetan. Ez erabil MySQL-rentzat!';
$string['dbtype'] = 'Datu-basearen kontrolatzailea';
$string['dbtype_desc'] = 'ADOdb datu-basearen kontrolatzailearen izena, kanpoko datu-baseko motore mota.';
$string['dbuser'] = 'Datu-basearen erabiltzailea';
$string['debugdb'] = 'Araztu ADOdb';
$string['debugdb_desc'] = 'Araztu ADOdb konexioa kanpoko datu-basera - erabili orri hutsa jasotzen baduzu saioa hastean. Ez erabili lanean ari diren guneetan!';
$string['defaultcategory'] = 'Ikastaro berrientzako kategoria lehenetsia';
$string['defaultcategory_desc'] = 'Lehenetsitako kategoria automatikoi sortutako ikastaroentzako. Kategoria IDa zehazten edo aurkitzen ez denean erabiliko da.';
$string['defaultrole'] = 'Lehenetsitako rola';
$string['defaultrole_desc'] = 'Modu lehenetsian esleituko den rola, kanpoko datu-basean rolik zehazten ez denean.';
$string['groupenroltable'] = 'Urruneko talde-esleipenen taula';
$string['groupenroltable_desc'] = 'Zehaztu taldeetara gehitu beharreko erabiltzaileen zerrenda daukan taularen izena. Errenkada bakoitzaren ikastaroa \'Talde berrien ikastaroaren identifikatzailearen eremua\' ezarpenean zehaztutako eremutik irakurtzen da, beraz ezarpen hori ere beharrezkoa da. Hutsik utziz gero ez da talde-esleipenik egingo.';
$string['groupfield'] = 'Taldearen ID zenbakiaren eremua';
$string['groupfield_desc'] = 'Urruneko talde-esleipenen taulan taldearen ID zenbakia duen eremuaren izena.';
$string['groupingcreation'] = 'Gaitu talde-multzo berrien sorrera';
$string['groupingcreation_desc'] = 'Gaituz gero, kanpoko datu-basean zehaztutako talde-multzoa Moodlen existitzen ez bada automatikoki sortuko da.';
$string['groupmessaging'] = 'Gaitu talde mezularitza';
$string['groupmessaging_desc'] = 'Gaituz gero, taldeko kideak Moodleko mezularitzaren bitartean bere taldeko beste kideekin mezuak trukatu ahalko dituzte.';
$string['groupupgrading'] = 'Eguneratu talde osagaia';
$string['groupupgrading_desc'] = 'Markatuz gero, kanpoko datu-basean taldekide bat agertzean erabiltzaile hori dagoeneko taldean eskuz gehituta badago, kidea taldean gehitzeko metodoa kanpoko datu-basera eguneratuko da kide hori taldetik kentzea saihesteko.';
$string['ignorehiddencourses'] = 'Baztertu ezkutuko ikastaroak';
$string['ignorehiddencourses_desc'] = 'Gaituz gero erabiltzaileak ez dira ikastaroetan matrikulatuko ikastaroak ikasleentzako ezkutuan badaude.';
$string['localcategoryfield'] = 'Kategoriaren eremu lokala';
$string['localcoursefield'] = 'Ikastaroaren eremu lokala';
$string['localrolefield'] = 'Rolaren eremu lokala';
$string['localtemplatefield'] = 'Txantiloiaren eremu lokala';
$string['localuserfield'] = 'Erabiltzailearen eremu lokala';
$string['manualenrol_cleaning'] = 'Kendu bikoiztutako eskuzko matrikulazioak';
$string['manualenrol_cleaning_desc'] = 'Gaituz gero, kanpoko datu-baseko erabiltzaile bat matrikulatzerakoan erabiltzailea lehendik eskuz matrikulatuta badago eskuzko matrikulazioa kenduko zaio.';
$string['manualenrol_cleaning_mode'] = 'Bikoiztutako eskuzko matrikulazioak kentzeko modua';
$string['manualenrol_cleaning_mode_desc'] = 'Aukeratu zein bikoiztutako eskuzko matrikulazio kendu behar diren. "Matrikulazio berriak soilik" aukerarekin, eskuzko matrikulazioa erabiltzailea kanpoko datu-basetik matrikulatzen den unean soilik kentzen da. "Matrikulazio guztiak" aukerarekin, sinkronizazio bakoitzean kanpoko datu-basetik matrikulatutako erabiltzaile guztien eskuzko matrikulazioak egiaztatu eta kentzen dira.';
$string['manualenrol_cleaning_mode_full'] = 'Matrikulazio guztiak';
$string['manualenrol_cleaning_mode_new'] = 'Matrikulazio berriak soilik';
$string['newcoursecategory'] = 'Ikastaro-kategoria berriaren eremua';
$string['newcoursecategory_desc'] = 'Ikastaro berriaren kategoria duen eremuaren izena (\'Kategoriaren eremu lokala\' ezarpenean zehaztutako moduan). \'Ikastaro berrien kategoria-bidearen eremua\' zehaztuta ez badago edo ikastaroarentzat hutsik badago bakarrik erabiltzen da.';
$string['newcoursecategorypath'] = 'Ikastaro berrien kategoria-bidearen eremua';
$string['newcoursecategorypath_desc'] = 'Kategoriaren bidea, kategoria existitzen den jakiteko erabiliko dena, azpikategoriak kontuan hartuta kategorien karaktere-banatzailea zehaztuta badago.';
$string['newcourseenddate'] = 'Ikastaro berriko amaiera-data eremua';
$string['newcourseenddate_desc'] = 'Aukerazkoa. Ez bada zehazten, ikastaroko amaiera-data ikastaroaren iraupen lehenetsiaren arabera kalkulatuko da. Zehazten bada, txantiloiko balioa baliogabetuko da.';
$string['newcoursefullname'] = 'Ikastaro berriaren izen luzeraren eremua';
$string['newcourseidnumber'] = 'Ikastaro berriaren ID zenbakiaren eremua';
$string['newcourseshortname'] = 'Ikastaro berriaren izen laburraren eremua';
$string['newcoursestartdate'] = 'Ikastaro berriko hasiera-data eremua';
$string['newcoursestartdate_desc'] = 'Aukerazkoa. Ez bada zehazten, ikastaroko hasiera-data uneko data izango da. Zehazten bada, txantiloiko balioa baliogabetuko da.';
$string['newcoursesummary'] = 'Ikastaro berrien laburpenaren eremua';
$string['newcoursetable'] = 'Urrutiko ikastaro berrien taula';
$string['newcoursetable_desc'] = 'Zehaztu automatikoki sortu beharreko ikastaroen zerrenda duen taularen izena. Hutsik utziz gero ez da ikastarorik sortuko.';
$string['newcoursetemplate'] = 'Ikastaro berrien txantiloiaren eremua';
$string['newcoursetemplate_desc'] = 'Automatikoki sortutako ikastaroak txantiloi-ikastaro baten kopia oso gisa sor daitezke, haren jarduera, baliabide eta blokeak barne, baina erabiltzaile eta talderik gabe. Zehaztu txantiloi-ikastaroaren identifikatzailea gordeta dagoen eremuaren izena (\'Txantiloiaren eremu lokala\' ezarpenean zehaztutako moduan)';
$string['newgroupcourse'] = 'Talde berrien ikastaroaren identifikatzailearen eremua';
$string['newgroupcourse_desc'] = 'Talde sortu beharreko ikastaroaren identifikatzailea (\'Ikastaroaren eremu lokala\' ezarpenean zehaztutako moduan).';
$string['newgroupdesc'] = 'Talde berrien deskribapenaren eremua';
$string['newgroupgroupings'] = 'Talde berrien talde-multzoaren eremua';
$string['newgroupgroupings_desc'] = 'Urruneko datu-basean talde-multzoa identifikatzeko erabilitako eremuaren izena.';
$string['newgroupidnumber'] = 'Talde berrien ID zenbakiaren eremua';
$string['newgroupname'] = 'Talde berrien izenaren eremua';
$string['newgrouptable'] = 'Urruneko talde berrien taula';
$string['newgrouptable_desc'] = 'Zehaztu automatikoki sortuko diren taldeen zerrenda gordetzen duen taularen izena. Hutsik utziz gero ez da talde berririk sortuko.';
$string['newuserauth'] = 'Erabiltzaile berrien autentifikazio-metodo lehenetsia';
$string['newuserauth_desc'] = 'Urruneko erabiltzaileen taulatik sortutako erabiltzaileen autentifikazio-metodoa, autentifikazio-metodoaren eremuak baliorik ez badu. Erabiltzaileak pasahitzik gabe sortzen dira; beraz, autentifikazio-metodoak ez luke pasahitzaren mende egon behar (adibidez, OAuth 2, SAML2, CAS edo LDAP).';
$string['newuserauthfield'] = 'Erabiltzaile berrien autentifikazio-metodoaren eremua';
$string['newuserauthfield_desc'] = 'Aukerakoa: erabiltzaile bakoitzaren autentifikazio-metodoa (adibidez, manual, oauth2 edo ldap) gordetzen duen urruneko erabiltzaileen taulako eremuaren izena. Hutsik badago, autentifikazio-metodo lehenetsia erabiliko da.';
$string['newuseremail'] = 'Erabiltzaile berrien helbide elektronikoaren eremua';
$string['newuserfirstname'] = 'Erabiltzaile berrien izenaren eremua';
$string['newuseridnumber'] = 'Erabiltzaile berrien ID zenbakiaren eremua';
$string['newuseridnumber_desc'] = 'Aukerakoa: utzi hutsik urruneko erabiltzaileen taulak erabiltzaileen ID zenbakia ez badu.';
$string['newuserlastname'] = 'Erabiltzaile berrien abizenen eremua';
$string['newusertable'] = 'Urruneko erabiltzaile berrien taula';
$string['newusertable_desc'] = 'Zehaztu urruneko matrikulazio-taulan agertu baina Moodlen ez dauden erabiltzaileak automatikoki sortzeko erabiltzaileen zerrenda gordetzen duen taularen izena. Erabiltzaileak erabiltzailearen eremu lokalari esleitutako eremuaren bidez bilatzen dira; beraz, eremu hori id bada, ezin dira sortu. Hutsik utziz gero ez da erabiltzaile berririk sortuko.';
$string['newuserusername'] = 'Erabiltzaile berrien erabiltzaile-izenaren eremua';
$string['pluginname'] = 'Kanpoko datu-basea';
$string['pluginname_desc'] = 'Kanpoko datu-base bat erabili dezakezu (ia edozein motatakoa) zure matrikulazioak kudeatzeko. Suposatu egiten da zure kanpoko datu-baseak gutxienez ikastaroaren IDa duen eremua eta erabiltzailearen IDa duen eremua dituela. Eremu hauek datu-base lokaleko ikastaro eta erabiltzaileen tauletan zuk aukeratutako eremuekin konparatuko dira.';
$string['privacy:metadata'] = 'Kanpoko datu-basea matrikulazio-pluginak ez du datu pertsonalik biltzen.';
$string['remotecoursefield'] = 'Urrutiko ikastaroen eremua';
$string['remotecoursefield_desc'] = 'Urrutiko taulan ikastaroen erregistroak parekatzeko erabiltzen den eremuaren izena';
$string['remoteenroltable'] = 'Urrutiko erabiltzaileen matrikulaziorako taula';
$string['remoteenroltable_desc'] = 'Zehaztu erabiltzaileen matrikulazioen zerrenda duen taularen izena. Hutsik utziz gero ez da matrikulaziorik sinkronizatuko.';
$string['remoteotheruserfield'] = 'Urrutiko Beste Erabiltzaile bat eremua';
$string['remoteotheruserfield_desc'] = '"Beste Erabiltzaile bat" rol esleipena markatzeko urrutiko taulan erabiliko den eremuaren izena.';
$string['remoterolefield'] = 'Urrutiko rolaren eremua';
$string['remoterolefield_desc'] = 'Urrutiko taulan rolen erregistroak parekatzeko erabiltzen den eremuaren izena.';
$string['remoteuserfield'] = 'Urrutiko erabiltzailearen eremua';
$string['remoteuserfield_desc'] = 'Urrutiko taulan erabiltzaileen erregistroak parekatzeko erabiltzen den eremuaren izena.';
$string['settingsheaderdb'] = 'Kanpoko datu-basearen konexioa';
$string['settingsheadergroupenrol'] = 'Talde-esleipenen sinkronizazioa';
$string['settingsheaderlocal'] = 'Eremu lokalen lotura';
$string['settingsheadernewcourses'] = 'Ikastaro berriak sortzea';
$string['settingsheadernewgroups'] = 'Talde berrien sorrera';
$string['settingsheadernewusers'] = 'Erabiltzaile berrien sorrera';
$string['settingsheaderremote'] = 'Urrutiko rolen sinkronizazioa';
$string['syncenrolmentstask'] = 'Sinkronizatu kanpoko datu-baseko matrikulazioak ataza';
$string['templatecourse'] = 'Ikastaro berrien txantiloia';
$string['templatecourse_desc'] = 'Hautazkoa: automatikoki sortutako ikastaroak txantiloi-ikastaro baten kopia oso gisa sortzen dira, haren jarduera, baliabide eta blokeak barne, baina erabiltzaile eta talderik gabe. Idatzi hemen txantiloi-ikastaroaren izen laburra. Ikastaro berrien txantiloiaren eremurik zehazten ez denean edo adierazitako txantiloia aurkitzen ez denean erabiltzen da.';
$string['userfield'] = 'Talde-esleipenen erabiltzaile-eremua';
$string['userfield_desc'] = 'Urruneko talde-esleipenen taulan erabiltzailea aurkitzeko erabiltzen den eremuaren izena (\'Erabiltzailearen eremu lokala\' ezarpenean zehaztutako moduan).';

