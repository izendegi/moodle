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
 * Hizkuntza kateak mod_typeform modulurako
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activitymodaltitle'] = 'Typeform jarduera';
$string['loadingforms'] = 'Formularioak kargatzen...';
$string['modulename'] = 'Typeform Inkesta';
$string['modulename_help'] = 'Typeform jarduera moduluak Typeform inkestak Moodle ikastaroan txertatzea ahalbidetzen du. Ikasleek inkestak zuzenean Moodlen osatu ditzakete, eta osaketa automatikoki erregistratzen da.';
$string['modulenameplural'] = 'Typeform Inkestak';
$string['name'] = 'Izena';
$string['name_help'] = 'Idatzi jarduera-moduluaren izena';
$string['pluginadministration'] = 'Typeform kudeaketa';
$string['pluginname'] = 'Typeform';

// Konfigurazioa
$string['typeformsettings'] = 'Typeform Ezarpenak';
$string['selecttypeform'] = 'Aukeratu Typeform inkesta';
$string['selecttypeform_help'] = 'Aukeratu Typeform inkesta bat zure Typeform kontutik. Soilik baimendutako lan-eremuetako formularioak erakutsiko dira.';
$string['includestudentcode'] = 'Sartu Student Code anonimoa';
$string['includestudentcode_help'] = 'student_code parametroa beti anonimizatzen da (hash bidez zifratzen da) Typeform-era bidali aurretik. Markatuta badago, anonimizatutako student_code hori formularioaren URLan sartzen da; markatuta ez badago, ez da bidaltzen. Modu lehenetsian markatu gabe dago.';
$string['apitoken'] = 'Typeform APIaren Tokena';
$string['apitoken_desc'] = 'Idatzi zure Typeform APIaren tokena. Informazio hau zure Typeform kontuaren ezarpenetatik lortu dezakezu.';
$string['allowedworkspaces'] = 'Baimendutako lan-eremuak';
$string['allowedworkspaces_desc'] = 'Zehaztu itzazu erabili daitezkeen lan-eremuen IDak komaz banatutako zerrenda batean. Utzi hutsik ezazu lan-eremu guztiak baimendu nahi badituzu.';
$string['testconnection'] = 'Probatu konexioa';
$string['notokenconfigured'] = 'Typeform APIaren tokena konfiguratu gabe dago. Mesedez, konfiguratu pluginaren ezarpenetan.';

// Erroreak
$string['errortypeformrequired'] = 'Typeform inkesta bat aukeratu behar duzu.';
$string['errorloadingforms'] = 'Errorea Typeform inkestak kargatzean. Mesedez, egiaztatu zure API token-a eta saiatu berriro.';
$string['notypeforms'] = 'Ez da Typeform inkestarik aurkitu.';

// Osaketa
$string['completiondetail:submit'] = 'Ikasleak formularioa bidali behar du';
$string['formcompleted'] = 'Eskerrik asko! Zure erantzuna erregistratu da.';
$string['formstarted'] = 'formstarted';
$string['alreadycompleted'] = 'Dagoeneko inkesta hau osatu duzu.';

// Pribatutasuna
$string['privacy:metadata'] = 'Typeform moduluak ez du datu pertsonalik gordetzen. Inkesten erantzunak Typeform-en gordetzen dira, ez Moodlen. Moodlek osaketa egoera baino ez du gordetzen (erabiltzaileak inkesta osatu duen ala ez).';
$string['privacy:metadata:course_modules_completion'] = 'Typeform jardueren osaketari buruzko informazioa';
$string['privacy:metadata:course_modules_completion:userid'] = 'Typeform jarduera osatu duen erabiltzailearen IDa';
$string['privacy:metadata:course_modules_completion:completionstate'] = 'Typeform jarduera osatu den ala ez';
$string['privacy:metadata:course_modules_completion:timemodified'] = 'Typeform jarduera osatu zeneko unea';
$string['privacy:metadata:typeform'] = 'Typeform-ekin integratzeko, erabiltzaile datuak zerbitzu horrekin trukatu behar dira.';
$string['privacy:metadata:typeform:userid'] = 'Erabiltzaile IDa Moodletik bidaltzen da zure datuetara Typeform-en sartzeko aukera emateko';

// Gaitasunak
$string['typeform:view'] = 'Ikusi Typeform';
$string['typeform:addinstance'] = 'Gehitu Typeform jarduera berria';

// Konexioa probatu
$string['configuration'] = 'Konfigurazioa';
$string['testresults'] = 'Probaren emaitzak';
$string['testingconnection'] = 'API konexioa probatzen';
$string['testingforms'] = 'Formularioen eskuratzea probatzen';
$string['testingworkspaces'] = 'Lan-eremuak probatzen';
$string['connectionsuccessful'] = 'Konexioa ondo egin da!';
$string['connectionfailed'] = 'Konexioak huts egin du. Egiaztatu zure APIaren tokena.';
$string['formsfound'] = '{$a} formulario aurkitu dira';
$string['noformsfound'] = 'Ez da formulariorik aurkitu.';
$string['andmoreforms'] = '... eta {$a} formulario gehiago';
$string['allworkspacesvalid'] = '{$a} lan-eremuak baliozkoak dira';
$string['someworkspacesinvalid'] = 'Lan-eremu batzuk baliogabeak dira: {$a}';
$string['alltestspassed'] = '{$a} proba guztiak arrakastaz osatu dira!';
$string['sometestsfailed'] = 'Proba batzuk huts egin dute: {$a->passed} proba osatu dira, {$a->failed} probak huts egin dute';
$string['allworkspaces'] = 'Lan-eremu guztiak (bat ere ez dago konfiguratuta)';
$string['summary'] = 'Laburpena';
$string['back'] = 'Atzera';
$string['completionsubmit'] = 'Egin bidalketa';
$string['attemptfinished'] = 'Saiakera amaituta';
$string['attemptstarted'] = 'Saiakera hasita';
$string['eventnotexists'] = 'Gertaera ez da existitzen';
$string['cmnotexists'] = 'ikastaro-modulua ez da existitzen';
$string['alreadyexist'] = 'Dagoeneko existitzen da';
$string['privacy:metadata:typeform_submission'] = 'Typeform jarduerako erabiltzaileen bidalketei buruzko informazioa.';
$string['privacy:metadata:typeform_submission:typeform'] = 'Bidalketa dagokion Typeform jardueraren instantziaren IDa.';
$string['privacy:metadata:typeform_submission:userid'] = 'Bidalketa egin duen erabiltzailearen IDa.';
$string['privacy:metadata:typeform_submission:submitted'] = 'Bidalketa bidalita/osatuta gisa markatu den ala ez.';
$string['privacy:metadata:typeform_submission:timecreated'] = 'Bidalketaren erregistroa sortu zen data/ordua.';
$string['privacy:metadata:typeform_submission:timemodified'] = 'Bidalketaren erregistroa azken aldiz aldatu zen data/ordua.';
$string['privacy:metadata:typeform_submission:usermodified'] = 'Bidalketaren erregistroa azken aldiz aldatu zuen erabiltzailea (hala badagokio).';
$string['apiurl'] = 'APIaren URLa';
$string['apiurl_desc'] = 'URLak / ikurrarekin amaitu behar du';
$string['typeformjslink'] = 'JS esteka';
$string['typeformjslink_desc'] = 'Typeform-en javascript esteka';
$string['typeformdomain'] = 'Domeinua';
$string['typeformdomain_desc'] = 'Typeform domeinua';
$string['fworkspace'] = 'Hautatu Typeform-eko lan-eremua';
