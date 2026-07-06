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

$string['typeconfigheading'] = 'Tipus de configuració';
$string['typeconfigheadingdesc'] = 'Selecciona el tipus de configuració <br> · Automàtica <br> · Manual <br> · Configurar més tard';
$string['typeconfigheadingdesc2'] = 'A continuació, si ho desitges, pots canviar el teu tipus de configuració';

$string['typeconfig'] = 'Tipus de Configuració';
$string['typeconfigdesc'] = 'Selecciona l\'acció que vols realitzar a continuació';
$string['noconfigmessage'] = 'Has seleccionat configurar SMOWL més tard, podràs fer-ho des de l\'administració del campus.';

$string['changetypeconfigauto'] = 'Canviar a configuració automàtica.';
$string['changetypeconfigrestartauto'] = 'Reiniciar configuració automàtica.';
$string['changetypeconfigmanual'] = 'Canviar a configuració manual.';
$string['changetypeconfigcancel'] = 'Cancel·lar canvi de configuració.';
$string['changetypeconfigafter'] = 'Configurar posteriorment.';

$string['typeask'] = 'Selecciona una opció';
$string['typenoconfig'] = 'Configurar més tard';
$string['typeautoconfig'] = 'Configuració automàtica';
$string['typemanualconfig'] = 'Configuració manual';

$string['dataautoconfigheading'] = 'Dades de configuració automàtica';
$string['dataautoconfigheadingdesc'] = 'A continuació podràs validar o inserir les dades d\'autoconfiguració';

$string['autoconfigmessagejsondataclient'] = 'La informació de client ve preconfigurada en aquest plugin.';
$string['autoconfigmessageconfigdone'] = 'El plugin SMOWL s\'ha configurat automàticament';

$string['autoconfigmessageclientvoid'] = 'La informació de client s\'ha de configurar correctament';
$string['autoconfigmessagecontenterror'] = 'Error d\'autoconfiguració: Retorn amb elements no vàlids';
$string['autoconfigmessageunauthorized'] = 'Error d\'autoconfiguració: Problemes d\'autenticació';
$string['autoconfigmessagebadrequest'] = 'Error d\'autoconfiguració: Bad Request';
$string['autoconfigmessageentityerror'] = 'Error d\'autoconfiguració: No s\'ha pogut obtenir entitat';
$string['autoconfigmessageconflict'] = 'Error d\'autoconfiguració: Problemes interns en l\'entitat';

$string['clientid'] = 'Identificador de client';
$string['clientiddesc'] = 'Identificador de client per activar el campus';

$string['clientkey'] = 'Clau d\'activació';
$string['clientkeydesc'] = 'Clau d\'activació per activar el campus';

$string['nocapabilityconfigmessage'] = 'No tens permisos per realitzar aquesta acció';

// Connection smowl settings.
$string['connectionsconfig'] = 'Configuració de connexió';
$string['connectionsconfigdesc'] = 'A continuació trobaràs els valors de connexió de la teva plataforma amb Smowltech.';
$string['connectionsconfigcontact'] = 'Per obtenir més informació sobre la teva llicència actual, accedeix a la teva àrea personal de client a <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Plataforma';
$string['entitydesc'] = 'Identificador únic per a aquesta plataforma, proporcionat per <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>.';
$string['noticeemptyentitysettings'] = 'El camp plataforma està buit, contacta amb Smowltech per conèixer el valor a introduir.';

$string['password'] = 'Clau de llicència';
$string['passworddesc'] = 'Clau de llicència per a la plataforma, proporcionada per Smowltech.';
$string['noticeemptypasswordsettings'] = 'La clau de llicència està buida, contacta amb Smowltech per conèixer el valor a introduir.';

$string['apikey'] = 'Clau API';
$string['apikeydesc'] = 'Clau per accedir a l\'API, proporcionada per Smowltech.';
$string['noticeemptyapikeysettings'] = 'La clau API està buida, contacta amb Smowltech per conèixer el valor a introduir.';

// JWT Secret
$string['smowljwtsecret'] = 'Clau secreta JWT';
$string['smowljwtsecretdesc'] = 'Clau secreta JWT, proporcionada per Smowltech.';
$string['noticeemptysmowljwtsecret'] = 'La clau secreta JWT està buida, contacta amb Smowltech per conèixer el valor a introduir.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Configuració per a instructors';
$string['instructorsconfigdesc'] = 'Les següents opcions personalitzen l\'ús de SMOWL per a instructors en els cursos on el bloc estigui activat.';

$string['continuousassessment'] = 'Avaluació contínua';
$string['onlyexams'] = 'Monitorització d\'exàmens';

$string['tracking'] = 'Tipus de monitorització';
$string['trackingdesc'] = 'Si el tipus marcat és “Monitorització d\'exàmens” SMOWL només podrà activar-se en qüestionaris.<br>Si el tipus marcat és “Avaluació contínua” SMOWL estarà disponible per a totes les activitats proporcionades en MOODLE.';

$string['attempttracking'] = 'Distinció d\'intents d\'examen';
$string['attempttrackingdesc'] = 'Selecciona si vols diferenciar entre diferents intents d\'un mateix examen per a un alumne.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Configuració avançada per a instructors';
$string['viewsettingsadvancedinstructors'] = 'Veure configuració avançada per a instructors';
$string['viewsettingsadvancedinstructorsdesc'] = 'Si s\'activa aquesta opció es mostraran les opcions avançades per a instructors.';

$string['accesscontrol'] = 'Control d\'accés';
$string['accesscontroldesc'] = 'Només permetre accedir als usuaris a l\'examen si consten actives les eines de SMOWL';

// View smowl settings.
$string['usersconfig'] = 'Configuració per a usuaris';
$string['usersconfigdesc'] = 'Les següents opcions personalitzen l\'ús de SMOWL per a usuaris en aquelles activitats i cursos on estigui activat el sistema de monitorització.';

$string['floatblock'] = 'Activar bloc de proctoring flotant';
$string['floatblockdesc'] = 'Selecciona per veure el bloc de proctoring en mode flotant.';
$string['activeinpopup'] = 'La supervisió està disponible al bloc flotant';

$string['blockheight'] = 'Mida visualització';
$string['blockheightdesc'] = 'Alçada de l\'iframe on es mostrarà la webcam durant la monitorització.';
$string['noticeblockheight'] = 'L\'alçada de l\'iframe no pot ser inferior a 280px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Configuració avançada per a usuaris';
$string['viewsettingsadvancedusers'] = 'Veure configuració avançada per a usuaris';
$string['viewsettingsadvancedusersdesc'] = 'Si s\'activa aquesta opció es mostraran les opcions avançades per a usuaris.';

// Capabilities.
$string['smowl:addinstance'] = 'Afegir bloc SMOWL';
$string['smowl:manageactivities'] = 'Gestionar activitats SMOWL';
$string['smowl:managegroups'] = 'Gestionar grups SMOWL';
$string['smowl:viewstudentcontent'] = 'Visualitzar enllaços d\'estudiant';
$string['smowl:enrolment'] = 'Accedir a l\'enllaç de registre de SMOWL';

// Notices.
$string['notinstancedblockincourse'] = 'Per realitzar aquesta acció has de crear el bloc SMOWL en el curs.';
$string['notmanagepermissions'] = 'No tens permisos per gestionar activitats SMOWL.';
$string['notteachersmanagementpermissions'] = 'Els professors no tenen permís per gestionar activitats SMOWL.';
$string['notviewmanagepermissions'] = 'No tens permisos per visualitzar o gestionar activitats SMOWL.';
$string['cannotcreatefile'] = 'No s\'ha pogut crear el fitxer';
$string['activitiesupdate'] = 'Activitats SMOWL actualitzades';
$string['activityupdate'] = 'Activitat SMOWL actualitzada';
$string['noticeemptysmowlconfig'] = 'La configuració del bloc no s\'ha completat.<br/>'.
    'Contacta amb l\'administrador de la plataforma per solucionar aquest problema.';
$string['noticeemptyentitynavigation'] = 'La configuració del bloc SMOWL no s\'ha completat.<br/>'.
    'Contacta amb l\'administrador de la plataforma per solucionar aquest problema.';

// Privacy.
$string['privacy:metadata'] = 'El sistema SMOWL només mostra dades emmagatzemades als servidors de Smowltech.';
$string['privacy:metadata:smowl:smowltech_net'] = 'El sistema SMOWL només mostra les dades emmagatzemades i transmet les dades de l\'usuari de Moodle als servidors de Smowltech. ';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Identificador únic d\'usuari';

// Events.
$string['eventinstancecreated'] = 'Esdeveniment instància de bloc creada';
$string['createblockinstance'] = 'Instància de bloc creada: ';
$string['eventinstancedeleted'] = 'Esdeveniment instància de bloc eliminada';
$string['deleteblockinstance'] = 'Instància de bloc eliminada: ';
$string['eventinstanceupdated'] = 'Esdeveniment instància de bloc actualitzada';
$string['updateblockinstance'] = 'Instància de bloc actualitzada ';
$string['fromoldblockname'] = ' del bloc antic ';
$string['apicalled'] = 'Trucada a SMOWL API realitzada';
$string['apicalleddesc'] = 'Trucada a SMOWL API de configuracions realitzada.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Configuracions internes SMOWL';
$string['internalconfigdesc'] = 'Les següents opcions poden afectar el funcionament del plugin,'.
    ' s\'han de modificar únicament sota petició expressa de SMOWL';
$string['onlysmowlexpressrequest'] = 'Aquesta opció s\'ha de modificar només a petició expressa de SMOWL.';

$string['viewsettingsinternal'] = 'Veure opcions internes';
$string['viewsettingsinternaldesc'] = 'Si s\'activa aquest check, podràs tornar a veure les opcions.';

$string['internalconfigurls'] = 'Configuracions internes de URL SMOWL';
$string['urlstudentview'] = 'URL visió d\'estudiant';
$string['urlstudentviewdesc'] = 'Enllaç a la visió de l\'estudiant';
// API URLs.
$string['internalconfigapiurls'] = 'Configuracions de URL API SMOWL';
$string['urlsmowlapi'] = 'URL API SMOWL';
$string['urlsmowlapidesc'] = 'URL per accedir a l\'API de SMOWL.';

$string['apilmssettings'] = 'Actualitzar les configuracions del LMS';
$string['apilmssettingsdesc'] = 'URL per actualitzar les configuracions del LMS.';

$string['apilmssettingscustomer'] = 'Actualització automàtica de les configuracions del LMS';
$string['apilmssettingscustomerdesc'] = 'URL per actualitzar les configuracions del LMS en instal·lacions automàtiques.';

$string['apiconfigclient'] = 'Activació d\'integració';
$string['apiconfigclientdesc'] = 'URL per obtenir les dades d\'activació d\'integració.';

$string['apiaddactivity'] = 'Afegir activitat';
$string['apiaddactivitydesc'] = 'URL per afegir activitat de proctoring';

$string['apimodifyactivity'] = 'Modificar activitat';
$string['apimodifyactivitydesc'] = 'URL per modificar l\'activitat de proctoring';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'Configuració de les regles d\'accés de SMOWL';
$string['accesrulesmowlcheckcamconfigdesc'] = 'Les següents opcions afecten les regles d\'accés per a qüestionaris amb proctoring.';
$string['accesrulesmowlcheckcam'] = 'Activar validació de càmera per a l\'alumne';
$string['accesrulesmowlcheckcamdesc'] = 'Si s\'activa la validació de càmera, l\'alumne es veurà obligat a validar que la seva càmera està funcionant correctament abans d\'accedir als qüestionaris.';

// View smowl settings.
$string['viewconfig'] = 'Configuració de visualització';
$string['viewconfigdesc'] = 'Les següents opcions afecten la visualització del bloc';
$string['floatsnap'] = 'Bloc flotant en tema Snap';
$string['floatsnapdesc'] = 'Seleccionar per veure el bloc flotant, només funciona per al tema SNAP.';

// Manage SMOWL Groups.
$string['managegroups'] = 'Gestió de grups';
$string['groupsaccessrestrictions'] = 'Restricció d\'accés';

$string['managegroupsformintro'] = 'En el següent formulari, s\'han de seleccionar els grups o agrupacions d\'usuaris als quals es mostrarà el bloc de SMOWL.';
$string['managegroupsupdate'] = 'Grups amb SMOWL actiu, correctament actualitzats.';

$string['availabilityconditionsjsonform'] = 'Restriccions d\'accés';
$string['availabilityconditionsjsonform_help'] = 'Des d\'aquest menú es poden afegir les restriccions d\'accés necessàries.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'No tens permisos per visualitzar la gestió de grups.';
$string['managegroupsnotconfigured'] = 'La gestió de grups no està configurada.';

// Bulk actions.
$string['bulkactions'] = 'Accions massives';
$string['bulkactionsdesc'] = 'Si s\'activa, podràs gestionar la cerca i activació de SMOWL en activitats, des de la portada del campus.';
$string['noticeactivebulkactions'] = 'Per activar aquesta funcionalitat, has d\'anar a "Opcions internes" de SMOWL';
$string['bulkactive'] = 'Activació d\'activitats';
$string['bulkgroups'] = 'Activació de grups';
$string['coursecategory'] = 'Categoria de cursos';
$string['coursecategory_help'] = 'Categoria de cursos on activar SMOWL.';
$string['activitytype'] = 'Tipus d\'activitat';
$string['activitytype_help'] = 'Tipus d\'activitat on activar SMOWL.';
$string['activityname'] = 'Nom de l\'activitat';
$string['activityname_help'] = 'Nom de l\'activitat on activar SMOWL.';
$string['searchactivities'] = 'Cercar activitats';
$string['allcategories'] = 'Totes les categories';
$string['searchresults'] = 'Resultats de la cerca';
$string['notfound'] = 'Resultats no trobats';
$string['savechanges'] = 'Desar canvis';
$string['bulkactiveupdate'] = 'Configuració d\'activitats massives SMOWL actualitzada correctament';
$string['searchgroups'] = 'Cercar grups';
$string['groupname'] = 'Nom del grup';
$string['groupname_help'] = 'Nom del grup per activar SMOWL.';
$string['managebulkgroupsformintro'] = 'En el següent formulari pots veure tots els grups resultants de la cerca.';
$string['managebulkgroupsforminfo'] = 'Els usuaris que pertanyin a qualsevol dels grups seleccionats, seran monitoritzats per SMOWL.';
$string['managebulkgroupsformmoreinfo'] = 'IMPORTANT:<BR/> L\'assignació de grups d\'aquest formulari sobreescriurà l\'assignació de grups aplicada en cadascun dels cursos implicats.';
$string['notviewmanagebuklpermissions'] = 'No tens permís per veure o administrar les activitats massives de SMOWL';

// Access Control Status.
$string['acwaiting'] = 'Revisant SMOWL, si us plau espera';
$string['acaccess'] = 'SMOWL activat, pots accedir a la teva activitat';
$string['acnotaccess'] = 'No s\'ha pogut validar SMOWL, recarrega el navegador per comprovar de nou';

// LTI Integration.
$string['internalconfigltitool'] = 'Configuració de URL LTI SMOWL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'URL Base LTI';
$string['urlsmowlltitooldesc'] = 'URL base de l\'eina LTI de SMOWL';
$string['ltitoolinit'] = 'URL inicial';
$string['ltitoolinitdesc'] = 'URL inicial de l\'eina LTI de SMOWL';
$string['ltitoolversion'] = 'Versió LTI';
$string['ltitoolversiondesc'] = 'Versió de l\'eina LTI de SMOWL';
$string['ltitoolpublickeyset'] = 'Public keyset';
$string['ltitoolpublickeysetdesc'] = 'URL del public keyset de l\'eina LTI de SMOWL';
$string['ltitoolinitiatelogin'] = 'URL d\'inici de sessió';
$string['ltitoolinitiatelogindesc'] = 'URL d\'inici de sessió de l\'eina LTI de SMOWL';
$string['ltitoolredirection'] = 'Redirection URI';
$string['ltitoolredirectiondesc'] = 'Redirection URI de l\'eina LTI de SMOWL';
$string['ltitoolconfigusage'] = 'Ús de configuració';
$string['ltitoolconfigusagedesc'] = 'Ús de configuració de l\'eina LTI de SMOWL com "Mostrar com a eina preconfigurada en afegir una eina externa"';
$string['ltitoollaunch'] = 'Contenidor de llançament predeterminat';
$string['ltitoollaunchdesc'] = 'Contenidor de llançament predeterminat de l\'eina LTI de SMOWL com "Incrustar, sense blocs"';
$string['ltitoolconfigmemberships'] = 'Membresies predeterminades';
$string['ltitoolconfigmembershipsdesc'] = 'Membresies predeterminades de l\'eina LTI de SMOWL com "Incrustar, sense blocs"';

$string['urlsmowlltiapi'] = 'URL Base API LTI';
$string['urlsmowlltiapidesc'] = 'URL base de l\'API LTI de SMOWL';
$string['ltiapiapplications'] = 'Aplicacions LTI';
$string['ltiapiapplicationsdesc'] = 'Aplicacions LTI de l\'API LTI de SMOWL';
$string['ltiapideployments'] = 'Desplegaments LTI';
$string['ltiapideploymentsdesc'] = 'Desplegaments LTI de l\'API LTI de SMOWL';

// LTI problems.
$string['lticreatetoolsuccess'] = 'Eina LTI SMOWL creada correctament';
$string['ltiupdatetoolsuccess'] = 'Eina LTI SMOWL actualitzada correctament';
$string['lticreatetoolerror'] = 'Problemes en crear l\'eina LTI SMOWL';
$string['ltisendtoolerror'] = 'Problemes en enviar la configuració LTI a SMOWL';
$string['ltisendtoolneedactivation'] = 'Atenció! Sembla que la teva entitat encara no ha estat validada a l\'aplicació mySmowltech, cosa que impedeix completar la configuració del complement. Fes <a href="https://my.smowltech.net/" target="_blank">clic aquí</a> i inicia sessió amb les teves credencials d\'accés a mySmowltech per validar la teva entitat. Aquesta validació és necessària per completar la integració correctament.';
$string['lticreatewserror'] = 'Problemes en crear el WS de SMOWL';
$string['ltiactivewserror'] = 'Problemes en activar el WS de SMOWL';
$string['lticreateusererror'] = 'Hi ha problemes en crear l\'usuari "Smowl Webservices User". Es requereix una acció de l\'administrador per activar-lo.';
$string['noticeltinotvisible'] = 'Les eines LTI estan bloquejades a l\'administració del campus (Plugins / Manage activities).';
$string['ltisendwserror'] = 'Problemes en enviar la informació del WS';
$string['ltiltiactivityerror'] = 'Problemes en crear l\'activitat LTI de SMOWL';
$string['notlticourse'] = 'No hi ha LTI en el curs.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Configuració interna LTI SMOWL';
$string['ltientity'] = 'Entitat LTI';
$string['ltitypeid'] = 'Tipus d\'ID LTI';
$string['ltideploymentid'] = 'Desplegament LTI';
$string['ltiappid'] = 'Aplicació LTI';
$string['ltirestid'] = 'REST LTI';

$string['ltiactivityname'] = 'Panell SMOWL';

// Block links teacher.
$string['ltimanagesmowl'] = 'Panell de supervisió';
$string['teachercontent'] = 'Configura la monitorització i revisa els resultats';
$string['teacherbutton'] = 'Accedeix a SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'Panell SMOWL';
$string['studentcontent'] = 'Accedeix al panell de registre i descàrrega de SMOWL';
$string['studentbutton'] = 'Accedeix a SMOWL';

$string['notstudentaccesspermissions'] = 'Només els estudiants poden accedir a aquesta secció';

// Corner
$string['drag_me'] = 'Arrossega\'m';