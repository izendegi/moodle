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

$string['typeconfigheading'] = 'Tipo di configurazione';
$string['typeconfigheadingdesc'] = 'Seleziona il tipo di configurazione <br> · Automatico <br> · Manuale <br> · Configura in seguito';
$string['typeconfigheadingdesc2'] = 'Puoi quindi modificare il tuo tipo di configurazione';

$string['typeconfig'] = 'Tipo di configurazione';
$string['typeconfigdesc'] = 'Seleziona l\'azione che desideri eseguire di seguito';
$string['noconfigmessage'] = 'Hai selezionato configura SMOWL in seguito, potrai farlo dall\'amministrazione del campus.';

$string['changetypeconfigauto'] = 'Passa alla configurazione automatica.';
$string['changetypeconfigrestartauto'] = 'Riavvia la configurazione automatica.';
$string['changetypeconfigmanual'] = 'Passa alla configurazione manuale.';
$string['changetypeconfigcancel'] = 'Annulla il cambio di configurazione.';
$string['changetypeconfigafter'] = 'Configura in seguito.';

$string['typeask'] = 'Seleziona un\'opzione';
$string['typenoconfig'] = 'Configura in seguito';
$string['typeautoconfig'] = 'Configurazione automatica';
$string['typemanualconfig'] = 'Configurazione manuale';

$string['dataautoconfigheading'] = 'Dati di configurazione automatica';
$string['dataautoconfigheadingdesc'] = 'Puoi ora convalidare o inserire i dati di configurazione automatica';

$string['autoconfigmessagejsondataclient'] = 'Le informazioni sul client sono preconfigurate in questo plugin.';
$string['autoconfigmessageconfigdone'] = 'Il plugin SMOWL è stato configurato automaticamente';

$string['autoconfigmessageclientvoid'] = 'Le informazioni sul client devono essere configurate correttamente';
$string['autoconfigmessagecontenterror'] = 'Errore di configurazione automatica: Ritorno con elementi non validi';
$string['autoconfigmessageunauthorized'] = 'Errore di configurazione automatica: Problemi di autenticazione';
$string['autoconfigmessagebadrequest'] = 'Errore di configurazione automatica: Richiesta non valida';
$string['autoconfigmessageentityerror'] = 'Errore di configurazione automatica: Impossibile ottenere l\'entità';
$string['autoconfigmessageconflict'] = 'Errore di configurazione automatica: Problemi interni nell\'entità';

$string['clientid'] = 'ID client';
$string['clientiddesc'] = 'ID client per attivare il campus';

$string['clientkey'] = 'Chiave di attivazione';
$string['clientkeydesc'] = 'Chiave di attivazione per attivare il campus';

$string['nocapabilityconfigmessage'] = 'Non hai le autorizzazioni per eseguire questa azione';

// Connection smowl settings.
$string['connectionsconfig'] = 'Impostazioni di connessione';
$string['connectionsconfigdesc'] = 'Le seguenti opzioni consentono di connettersi a SMOWL';
$string['connectionsconfigcontact'] = 'Per ulteriori informazioni sulla tua licenza attuale, accedi al tuo account client su <a href="https://my.smowltech.net/" target="_blank">mySmowltech</a>.';

$string['entity'] = 'Piattaforma';
$string['entitydesc'] = 'Identificatore della piattaforma fornito da <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>.';
$string['noticeemptyentitysettings'] = 'La piattaforma è vuota, contatta Smowltech per conoscere il valore da inserire.';

$string['password'] = 'Chiave di licenza';
$string['passworddesc'] = 'Chiave di licenza per la piattaforma, fornita da Smowltech.';
$string['noticeemptypasswordsettings'] = 'La chiave di licenza è vuota, contatta Smowltech per conoscere il valore da inserire.';

$string['apikey'] = 'Chiave API';
$string['apikeydesc'] = 'Chiave per accedere all\'API, fornita da Smowltech.';
$string['noticeemptyapikeysettings'] = 'La chiave API è vuota, contatta Smowltech per conoscere il valore da inserire.';

// JWT Secret
$string['smowljwtsecret'] = 'Chiave segreta JWT';
$string['smowljwtsecretdesc'] = 'Chiave segreta per la firma JWT, fornita da Smowltech.';
$string['noticeemptysmowljwtsecret'] = 'La chiave segreta JWT è vuota, contatta Smowltech per conoscere il valore da inserire.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Impostazioni per gli istruttori';
$string['instructorsconfigdesc'] = 'Le seguenti opzioni personalizzano l\'utilizzo di SMOWL per gli istruttori nei corsi in cui il blocco è attivo.';

$string['continuousassessment'] = 'Valutazione continua';
$string['onlyexams'] = 'Esami\' monitoraggio';

$string['tracking'] = 'Tipo monitoraggio';
$string['trackingdesc'] = 'Selezionare se il tipo di tracciamento SMOWL è una valutazione continua o solo esami.';

$string['attempttracking'] = 'Distinzione dei tentativi di esame';
$string['attempttrackingdesc'] = 'Selezionare se si desidera distinguere tra diversi tentativi dello stesso esame per uno studente.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Impostazioni avanzate per gli istruttori';
$string['viewsettingsadvancedinstructors'] = 'Visualizza le impostazioni avanzate per gli istruttori';
$string['viewsettingsadvancedinstructorsdesc'] = 'L\'attivazione di questa opzione visualizzerà le opzioni avanzate per gli istruttori.';

$string['accesscontrol'] = 'Controllo di accesso';
$string['accesscontroldesc'] = 'Consenti agli utenti di accedere all\'esame solo se gli strumenti SMOWL sono attivi';

// Settings advancer users.
$string['usersconfig'] = 'Impostazioni per gli utenti';
$string['usersconfigdesc'] = 'Le seguenti opzioni personalizzano l\'utilizzo di SMOWL per gli utenti nelle attività e nei corsi in cui il sistema di monitoraggio è attivo.';

$string['floatblock'] = 'Attiva il blocco di monitoraggio galleggiante';
$string['floatblockdesc'] = 'Seleziona per visualizzare il blocco di monitoraggio in modalità galleggiante.';
$string['activeinpopup'] = 'La supervisione è disponibile nel blocco galleggiante';

$string['blockheight'] = 'Dimensione di visualizzazione';
$string['blockheightdesc'] = 'Altezza dell\'iframe in cui verrà visualizzata la webcam durante il monitoraggio.';
$string['noticeblockheight'] = 'L\'altezza dell\'iframe non può essere inferiore a 280px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Impostazioni avanzate per gli utenti';
$string['viewsettingsadvancedusers'] = 'Visualizza le impostazioni avanzate per gli utenti';
$string['viewsettingsadvancedusersdesc'] = 'Se questa opzione è attivata, verranno visualizzate le opzioni avanzate per gli utenti.';

// Capabilities.
$string['smowl:addinstance'] = 'Aggiungi blocco SMOWL';
$string['smowl:manageactivities'] = 'Gestisci le attività SMOWL';
$string['smowl:managegroups'] = 'Gestisci grouppi SMOWL';
$string['smowl:viewstudentcontent'] = 'Visualizza i collegamenti degli studenti';
$string['smowl:enrolment'] = 'Accedi al collegamento di registrazione SMOWL';

// Notices.
$string['notinstancedblockincourse'] = 'Per eseguire questa azione è necessario creare il blocco SMOWL nel corso.';
$string['notmanagepermissions'] = 'Non hai i permessi per gestire le attività SMOWL.';
$string['notviewmanagepermissions'] = 'Non hai i permessi per visualizzare o gestire le attività SMOWL';
$string['notteachersmanagementpermissions'] = 'Non hai le autorizzazioni per gli insegnanti per gestire le attività SMOWL.';
$string['cannotcreatefile'] = 'Le fichier n\'a pas pu être créé';
$string['activitiesupdate'] = 'Attività SMOWL aggiornate';
$string['activityupdate'] = 'Attività SMOWL aggiornata';
$string['noticeemptysmowlconfig'] = 'La configurazione del blocco non è stata completata. <br/>'.
     'Contatta l\'amministratore della piattaforma per risolvere questo problema.';
$string['noticeemptyentitynavigation'] = 'La configurazione del blocco SMOWL non è stata completata. <br/>'.
     'Contatta l\'amministratore della piattaforma per risolvere questo problema.';

// Privacy.
$string['privacy:metadata'] = 'Il sistema SMOWL visualizza solo i dati memorizzati sui server di Smowltech.';
$string['privacy:metadata:smowl:smowltech_net'] = 'Il sistema SMOWL visualizza solo i dati memorizzati e trasmette i dati degli utenti da Moodle ai server di Smowltech.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Identificatore univoco dell\'utente';

// Events.
$string['eventinstancecreated'] = 'Istanza di blocco creata dall\'evento';
$string['createblockinstance'] = 'Istanza di blocco creata:';
$string['eventinstancedeleted'] = 'Istanza di blocco eliminata evento';
$string['deleteblockinstance'] = 'Istanza di blocco eliminata:';
$string['eventinstanceupdated'] = 'Istanza di blocco aggiornata evento';
$string['updateblockinstance'] = 'Istanza di blocco aggiornata';
$string['fromoldblockname'] = 'dal vecchio blocco';
$string['apicalled'] = 'Chiamata API SMOWL effettuata';
$string['apicalleddesc'] = 'Chiamata API SMOWL di parametri effettuata.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Impostazioni SMOWL interne';
$string['internalconfigdesc'] = 'Le seguenti opzioni possono influenzare il funzionamento del plugin,'.
     'devono essere modificati solo su espressa richiesta di SMOWL';
$string['onlysmowlexpressrequest'] = 'Questa opzione deve essere modificata solo su richiesta espressa di SMOWL.';
$string['viewsettingsinternal'] = 'Visualizza le opzioni interne';
$string['viewsettingsinternaldesc'] = 'Se questa opzione è attivata, potrai nuovamente visualizzare le opzioni.';

$string['internalconfigurls'] = 'URL interni SMOWL';
$string['urlstudentview'] = 'Link vista studente';
$string['urlstudentviewdesc'] = 'Link alla vista studente.';

// API URLs.
$string['internalconfigapiurls'] = 'URL API interni SMOWL';
$string['urlsmowlapi'] = 'URL API SMOWL';
$string['urlsmowlapidesc'] = 'URL per accedere all\'API SMOWL.';

$string['apilmssettings'] = 'Aggiorna le impostazioni del LMS';
$string['apilmssettingsdesc'] = 'URL per aggiornare le impostazioni del LMS.';

$string['apilmssettingscustomer'] = 'Aggiornamento automatico delle impostazioni del LMS';
$string['apilmssettingscustomerdesc'] = 'URL per aggiornare le impostazioni del LMS nelle installazioni automatiche.';

$string['apiconfigclient'] = 'Attivazione dell\'integrazione';
$string['apiconfigclientdesc'] = 'URL per ottenere i dati di attivazione dell\'integrazione.';

$string['apiaddactivity'] = 'Aggiungi attività';
$string['apiaddactivitydesc'] = 'URL per aggiungere l\'attività protetta';

$string['apimodifyactivity'] = 'Modifica attività';
$string['apimodifyactivitydesc'] = 'URL per modificare l\'attività protetta';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'Regole di accesso SMOWL';
$string['accesrulesmowlcheckcamconfigdesc'] = 'Le seguenti opzioni influenzano le regole di accesso ai questionari con monitoraggio.';
$string['accesrulesmowlcheckcam'] = 'Attiva la verifica della webcam per lo studente';
$string['accesrulesmowlcheckcamdesc'] = 'Se la verifica della webcam è attivata, lo studente sarà obbligato a verificare che la sua webcam funzioni correttamente prima di accedere ai questionari.';

// View smowl settings.
$string['viewconfig'] = 'Visualizza impostazioni';
$string['viewconfigdesc'] = 'Le seguenti opzioni influenzano la visualizzazione del blocco';
$string['floatsnap'] = 'Abilita blocco mobile nel tema Snap';
$string['floatsnapdesc'] = 'Seleziona per visualizzare il blocco mobile, funziona solo per il tema SNAP';

// Manage SMOWL Groups.
$string['managegroups'] = 'Gruppi \' gestione ';
$string['groupsaccessrest restrizioni'] = 'Restrizioni di accesso';

$string['managegroupsformintro'] = 'Nel seguente modulo, è necessario selezionare i gruppi di utenti o i gruppi in cui verrà visualizzato il blocco SMOWL.';
$string['managegroupsupdate'] = 'Gruppi con SMOWL attivi aggiornati correttamente.';

$string['availabilityconditionsjsonform'] = 'Restrizioni di accesso';
$string['availabilityconditionsjsonform_help'] = 'Da questo menu puoi aggiungere le restrizioni di accesso necessarie.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'Non hai i permessi per gestire i gruppi.';
$string['managegroupsnotconfigured'] = 'I gruppi di gestione non sono configurati.';


// Bulk actions.

$string['bulkactions'] = 'Azioni di massa';
$string['bulkactionsdesc'] = 'Se attivato, potrai gestire la ricerca e l\'attivazione di SMOWL nelle attività, dalla home page del campus.';
$string['noticeactivebulkactions'] = 'Per attivare questa funzionalità, devi andare in \"Opzioni interne\" di SMOWL';
$string['bulkactive'] = 'Attivazione delle attività';
$string['bulkgroups'] = 'Attivazione dei gruppi';
$string['coursecategory'] = 'Categoria del corso';
$string['coursecategory_help'] = 'Categoria del corso in cui attivare SMOWL.';
$string['activitytype'] = 'Tipi di attività';
$string['activitytype_help'] = 'Tipo di attività in cui attivare SMOWL.';
$string['activityname'] = 'Nome dell\'attività';
$string['activityname_help'] = 'Nome dell\'attività in cui attivare SMOWL.';
$string['searchactivities'] = 'Cerca attività';
$string['allcategories'] = 'Tutte le categorie';
$string['searchresults'] = 'Risultati della ricerca';
$string['notfound'] = 'Risultati non trovati';
$string['savechanges'] = 'Salva le modifiche';
$string['bulkactiveupdate'] = 'Configurazione delle attività SMOWL in massa aggiornata correttamente';
$string['searchgroups'] = 'Cerca gruppi';
$string['groupname'] = 'Nome del gruppo';
$string['groupname_help'] = 'Nome del gruppo per attivare SMOWL.';
$string['managebulkgroupsformintro'] = 'Nel modulo seguente puoi vedere tutti i gruppi risultanti dalla ricerca.';
$string['managebulkgroupsforminfo'] = 'Gli utenti che appartengono a uno dei gruppi selezionati saranno monitorati da SMOWL.';
$string['managebulkgroupsformmoreinfo'] = 'IMPORTANTE: l\'assegnazione di gruppi da questo modulo sovrascriverà l\'assegnazione di gruppi applicata a ciascuno dei corsi interessati.';
$string['notviewmanagebuklpermissions'] = 'Non hai le autorizzazioni per visualizzare o amministrare le attività SMOWL in massa';

// Access Control Status.
$string['acwaiting'] = 'Verifica di SMOWL, attendere prego';
$string['acaccess'] = 'SMOWL attivato, puoi accedere alla tua attività';
$string['acnotaccess'] = 'SMOWL non è stato convalidato, ricarica il browser per verificare nuovamente';

// LTI Integration.
$string['internalconfigltitool'] = 'URL di configurazione LTI SMOWL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'URL LTI di base';
$string['urlsmowlltitooldesc'] = 'URL di base dello strumento LTI di SMOWL';
$string['ltitoolinit'] = 'URL iniziale';
$string['ltitoolinitdesc'] = 'URL iniziale dello strumento LTI di SMOWL';
$string['ltitoolversion'] = 'Versione LTI';
$string['ltitoolversiondesc'] = 'Versione dello strumento LTI di SMOWL';
$string['ltitoolpublickeyset'] = 'URL keyset';
$string['ltitoolpublickeysetdesc'] = 'URL del public keyset dello strumento LTI di SMOWL';
$string['ltitoolinitiatelogin'] = 'URL di accesso iniziale';
$string['ltitoolinitiatelogindesc'] = 'URL di accesso iniziale dello strumento LTI di SMOWL';
$string['ltitoolredirection'] = 'URI di reindirizzamento';
$string['ltitoolredirectiondesc'] = 'URI di reindirizzamento dello strumento LTI di SMOWL';
$string['ltitoolconfigusage'] = 'Utilizzo della configurazione';
$string['ltitoolconfigusagedesc'] = 'Utilizzo della configurazione dello strumento LTI di SMOWL come \"Mostra come strumento preconfigurato durante l\'aggiunta di un\'attività esterna\"';
$string['ltitoollaunch'] = 'Contenitore di lancio predefinito';
$string['ltitoollaunchdesc'] = 'Contenitore di lancio predefinito dello strumento LTI di SMOWL come \"Incorpora, senza blocchi\"';
$string['ltitoolconfigmemberships'] = 'Membri predefiniti';
$string['ltitoolconfigmembershipsdesc'] = 'Membri predefiniti dello strumento LTI di SMOWL come \"Incorpora, senza blocchi\"';

$string['urlsmowlltiapi'] = 'URL API LTI di base';
$string['urlsmowlltiapidesc'] = 'URL di base dell\'API LTI di SMOWL';
$string['ltiapiapplications'] = 'Applicazioni LTI';
$string['ltiapiapplicationsdesc'] = 'Applicazioni LTI dell\'API LTI di SMOWL';
$string['ltiapideployments'] = 'Deployments LTI';
$string['ltiapideploymentsdesc'] = 'Deployments LTI dell\'API LTI di SMOWL';

// LTI problems.
$string['lticreatetoolsuccess'] = 'Strumento LTI SMOWL creato correttamente';
$string['ltiupdatetoolsuccess'] = 'Strumento LTI SMOWL aggiornato correttamente';
$string['lticreatetoolerror'] = 'Problemi durante la creazione dello strumento LTI SMOWL';
$string['ltisendtoolerror'] = 'Problemi durante l\'invio della configurazione LTI a SMOWL';
$string['ltisendtoolneedactivation'] = 'Attenzione! Sembra che la tua entità non sia stata ancora convalidata nell\'app mySmowltech, il che impedisce il completamento della configurazione del plugin. <a href="https://my.smowltech.net/" target="_blank">Clicca qui</a> e accedi con le tue credenziali di accesso a mySmowltech per convalidare la tua entità. Questa convalida è necessaria per completare correttamente l\'integrazione.';
$string['lticreatewserror'] = 'Problemi durante la creazione del WS SMOWL';
$string['ltiactivewserror'] = 'Problemi durante l\'attivazione del WS SMOWL';
$string['lticreateusererror'] = 'Ci sono problemi nella creazione dell\'utente "Smowl Webservices User". È richiesta un\'azione dell\'amministratore per attivarlo.';
$string['noticeltinotvisible'] = 'Gli strumenti LTI sono bloccati nell\'amministrazione del campus (Plugin / Gestisci attività).';
$string['ltisendwserror'] = 'Problemi durante l\'invio delle informazioni WS';
$string['ltiltiactivityerror'] = 'Problemi durante la creazione dell\'attività LTI SMOWL';
$string['notlticourse'] = 'Do not have LTI in course.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Configurazione interna LTI SMOWL';
$string['ltientity'] = 'Entità LTI';
$string['ltitypeid'] = 'Tipo di ID LTI';
$string['ltideploymentid'] = 'Deployment LTI';
$string['ltiappid'] = 'Applicazione LTI';
$string['ltirestid'] = 'REST LTI';

$string['ltiactivityname'] = 'Pannello SMOWL';

// Block links teacher.
$string['ltimanagesmowl'] = 'Cruscotto di monitoraggio';
$string['teachercontent'] = 'Imposta il monitoraggio e rivedi i risultati.';
$string['teacherbutton'] = 'Accedi a SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'Pannello SMOWL';
$string['studentcontent'] = 'Accedi al pannello di registrazione e download di SMOWL';
$string['studentbutton'] = 'Accedi a SMOWL';

$string['notstudentaccesspermissions'] = 'A questa sezione possono accedere solo gli studenti';

// Corner
$string['drag_me'] = 'Trascinami';