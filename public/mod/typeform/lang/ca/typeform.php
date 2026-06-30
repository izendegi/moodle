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
 * Cadenes d'idioma per a mod_typeform
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activitymodaltitle'] = 'Activitat de Typeform';
$string['loadingforms'] = 'Carregant formularis...';
$string['modulename'] = 'Enquesta Typeform';
$string['modulename_help'] = 'El mòdul d\'activitat Typeform permet incrustar enquestes de Typeform al teu curs de Moodle. Els estudiants poden completar les enquestes directament dins de Moodle, i la finalització es registra automàticament.';
$string['modulenameplural'] = 'Enquestes Typeform';
$string['name'] = 'Nom';
$string['name_help'] = 'Poseu nom a l\'activitat.';
$string['pluginadministration'] = 'Administració de Typeform';
$string['pluginname'] = 'Typeform';

// Configuració
$string['typeformsettings'] = 'Configuració de Typeform';
$string['selecttypeform'] = 'Seleccionar enquesta Typeform';
$string['selecttypeform_help'] = 'Selecciona una enquesta de Typeform des del teu compte de Typeform. Només es mostraran formularis de workspaces autoritzats.';
$string['includestudentcode'] = 'Incloure Student Code anònim';
$string['includestudentcode_help'] = 'El paràmetre student_code sempre s\'anonimitza (es xifra mitjançant hash) abans d\'enviar-se a Typeform. Si està marcat, aquest student_code anònim s\'inclou a l\'URL del formulari; si no està marcat, no s\'envia. Desmarcat per defecte.';
$string['apitoken'] = 'Token d\'API de Typeform';
$string['apitoken_desc'] = 'Introdueix el teu token d\'API de Typeform. Pots obtenir-lo des de la configuració del teu compte de Typeform.';
$string['allowedworkspaces'] = 'Workspaces permesos';
$string['allowedworkspaces_desc'] = 'Llista separada per comes d\'IDs de workspaces que estan permesos. Deixa buit per permetre tots els workspaces.';
$string['testconnection'] = 'Provar connexió';
$string['notokenconfigured'] = 'El token d\'API de Typeform no està configurat. Si us plau, configura\'l a la configuració del connector.';

// Errors
$string['errortypeformrequired'] = 'Has de seleccionar una enquesta de Typeform.';
$string['errorloadingforms'] = 'Error en carregar les enquestes de Typeform. Si us plau, verifica el teu token d\'API i torna-ho a intentar.';
$string['notypeforms'] = 'No s\'han trobat enquestes de Typeform.';

// Finalització
$string['completiondetail:submit'] = 'L\'estudiant ha d\'enviar el formulari';
$string['formcompleted'] = 'Gràcies! La teva resposta ha estat registrada.';
$string['formstarted'] = 'formstarted';
$string['alreadycompleted'] = 'Ja has completat aquesta enquesta.';

// Privacitat
$string['privacy:metadata'] = 'El mòdul Typeform no emmagatzema cap dada personal. Les respostes de les enquestes s\'emmagatzemen a Typeform, no a Moodle. Moodle només emmagatzema l\'estat de finalització (si l\'usuari ha completat l\'enquesta).';
$string['privacy:metadata:course_modules_completion'] = 'Informació sobre la finalització d\'activitats Typeform';
$string['privacy:metadata:course_modules_completion:userid'] = 'L\'ID de l\'usuari que ha completat l\'activitat Typeform';
$string['privacy:metadata:course_modules_completion:completionstate'] = 'Si l\'activitat Typeform ha estat completada';
$string['privacy:metadata:course_modules_completion:timemodified'] = 'El moment en què es va completar l\'activitat Typeform';
$string['privacy:metadata:typeform'] = 'Per integrar-se amb Typeform, les dades de l\'usuari s\'han d\'intercanviar amb aquest servei.';
$string['privacy:metadata:typeform:userid'] = 'L\'ID d\'usuari s\'envia des de Moodle per permetre\'t accedir a les teves dades a Typeform';

// Capacitats
$string['typeform:view'] = 'Veure Typeform';
$string['typeform:addinstance'] = 'Afegir una nova activitat Typeform';

// Provar connexió
$string['configuration'] = 'Configuració';
$string['testresults'] = 'Resultats de la prova';
$string['testingconnection'] = 'Provant connexió amb l\'API';
$string['testingforms'] = 'Provant recuperació de formularis';
$string['testingworkspaces'] = 'Provant workspaces';
$string['connectionsuccessful'] = 'Connexió exitosa!';
$string['connectionfailed'] = 'Connexió fallida. Si us plau, verifica el teu token d\'API.';
$string['formsfound'] = 'S\'han trobat {$a} formulari(s)';
$string['noformsfound'] = 'No s\'han trobat formularis.';
$string['andmoreforms'] = '... i {$a} formulari(s) més';
$string['allworkspacesvalid'] = 'Tots els {$a} workspace(s) són vàlids';
$string['someworkspacesinvalid'] = 'Alguns workspaces són invàlids: {$a}';
$string['alltestspassed'] = 'Totes les {$a} prova(s) han passat exitosament!';
$string['sometestsfailed'] = 'Algunes proves han fallat: {$a->passed} han passat, {$a->failed} han fallat';
$string['allworkspaces'] = 'Tots els workspaces (cap configurat)';
$string['summary'] = 'Resum';
$string['back'] = 'Tornar';
$string['completionsubmit'] = 'Fer una entrega';
$string['attemptfinished'] = 'Intent finalitzat';
$string['attemptstarted'] = 'Intent iniciat';
$string['eventnotexists'] = 'L’esdeveniment no existeix';
$string['cmnotexists'] = 'cm no existeix';
$string['alreadyexist'] = 'Ja existeix';
$string['privacy:metadata:typeform_submission'] = 'Informació sobre els enviaments dels usuaris a l\'activitat Typeform.';
$string['privacy:metadata:typeform_submission:typeform'] = 'L\'ID de la instància de l\'activitat Typeform a la qual pertany l\'enviament.';
$string['privacy:metadata:typeform_submission:userid'] = 'L\'ID de l\'usuari que ha fet l\'enviament.';
$string['privacy:metadata:typeform_submission:submitted'] = 'Si l\'enviament s\'ha marcat com a enviat/completat.';
$string['privacy:metadata:typeform_submission:timecreated'] = 'La data i hora de creació del registre d\'enviament.';
$string['privacy:metadata:typeform_submission:timemodified'] = 'La data i hora de la darrera modificació del registre d\'enviament.';
$string['privacy:metadata:typeform_submission:usermodified'] = 'L\'usuari que va modificar per darrera vegada el registre d\'enviament (si escau).';
$string['apiurl'] = 'URL de l\'API';
$string['apiurl_desc'] = 'L\'adreça URL ha d\'acabar en /';
$string['typeformjslink'] = 'JS link';
$string['typeformjslink_desc'] = 'Typefrom javascript link';
$string['typeformdomain'] = 'Domain';
$string['typeformdomain_desc'] = 'Typefrom domain';
$string['fworkspace'] = 'Seleccionar Workspace de Typeform';
