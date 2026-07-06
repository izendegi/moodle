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

$string['typeconfigheading'] = 'Type de configuration';
$string['typeconfigheadingdesc'] = 'Sélectionnez le type de configuration<br> · Automatique <br> · Manuel <br> · Configurer plus tard';
$string['typeconfigheadingdesc2'] = 'Vous pouvez ensuite modifier votre type de configuration';

$string['typeconfig'] = 'Type de configuration';
$string['typeconfigdesc'] = 'Sélectionnez l\'action que vous souhaitez effectuer ci-dessous';
$string['noconfigmessage'] = 'Vous avez sélectionné configurer SMOWL plus tard, vous pourrez le faire depuis l\'administration du campus.';

$string['changetypeconfigauto'] = 'Passer à la configuration automatique.';
$string['changetypeconfigrestartauto'] = 'Redémarrer la configuration automatique.';
$string['changetypeconfigmanual'] = 'Passer à la configuration manuelle.';
$string['changetypeconfigcancel'] = 'Annuler le changement de configuration.';
$string['changetypeconfigafter'] = 'Configurer plus tard.';

$string['typeask'] = 'Sélectionnez une option';
$string['typenoconfig'] = 'Configurer plus tard';
$string['typeautoconfig'] = 'Configuration automatique';
$string['typemanualconfig'] = 'Configuration manuelle';

$string['dataautoconfigheading'] = 'Données de configuration automatique';
$string['dataautoconfigheadingdesc'] = 'Vous pouvez maintenant valider ou insérer les données de configuration automatique';

$string['autoconfigmessagejsondataclient'] = 'Les informations client sont préconfigurées dans ce plugin.';
$string['autoconfigmessageconfigdone'] = 'Le plugin SMOWL a été configuré automatiquement';

$string['autoconfigmessageclientvoid'] = 'Les informations client doivent être configurées correctement';
$string['autoconfigmessagecontenterror'] = 'Erreur de configuration automatique: Retour avec des éléments non valides';
$string['autoconfigmessageunauthorized'] = 'Erreur de configuration automatique: Problèmes d\'authentification';
$string['autoconfigmessagebadrequest'] = 'Erreur de configuration automatique: Bad Request';
$string['autoconfigmessageentityerror'] = 'Erreur de configuration automatique: Impossible d\'obtenir l\'entité';
$string['autoconfigmessageconflict'] = 'Erreur de configuration automatique: Problèmes internes dans l\'entité';

$string['clientid'] = 'Identifiant client';
$string['clientiddesc'] = 'Identifiant client pour activer le campus';

$string['clientkey'] = 'Clé d\'activation';
$string['clientkeydesc'] = 'Clé d\'activation pour activer le campus';

$string['nocapabilityconfigmessage'] = 'Vous n\'avez pas les autorisations pour effectuer cette action.';

// Connection smowl settings.
$string['connectionsconfig'] = 'Paramètres de connexion';
$string['connexionsconfigdesc'] = 'Les options suivantes vous permettent de vous connecter à SMOWL';
$string['connectionsconfigcontact'] = 'Pour plus d\'informations sur votre licence actuelle, accédez à votre espace client sur <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = "Plateforme";
$string['entitydesc'] = 'Identifiant de  fourni par <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>.';
$string['noticeemptyentitysettings'] = "La plateforme est vide, contactez Smowltech pour connaître la valeur à entrer. ";

$string['password'] = 'Clé de licence';
$string['passworddesc'] = 'Clé de licence pour la plateforme, fournie par Smowltech.';
$string['noticeemptypasswordsettings'] = 'La clé de licence est vide, contactez Smowltech pour connaître la valeur à entrer.';
$string['apikey'] = 'Clé API';
$string['apikeydesc'] = 'Clé pour accéder à l\'API, fournie par Smowltech.';
$string['noticeemptyapikeysettings'] = 'La clé API est vide, contactez Smowltech pour connaître la valeur à entrer.';

// JWT Secret
$string['smowljwtsecret'] = 'Clé secrète JWT';
$string['smowljwtsecretdesc'] = 'Clé secrète pour générer le token JWT.';
$string['noticeemptysmowljwtsecret'] = 'La clé secrète JWT est vide, contactez Smowltech pour connaître la valeur à entrer.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Paramètres pour les instructeurs';
$string['instructorsconfigdesc'] = 'Les options suivantes personnalisent l\'utilisation de SMOWL pour les instructeurs dans les cours où le bloc est activé.';

$string['continuousassessment'] = 'Évaluation continue';
$string['onlyexams'] = 'Examens surveillance';

$string['tracking'] = 'Type de surveillance';
$string['trackingdesc'] = 'Choisissez si le type de suivi SMOWL est une évaluation continue ou seulement des examens.';

$string['attempttracking'] = 'Distinction des tentatives d\'examen';
$string['attempttrackingdesc'] = 'Sélectionnez si vous souhaitez différencier les différentes tentatives d\'un même examen pour un étudiant.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Paramètres avancés pour les instructeurs';
$string['viewsettingsadvancedinstructors'] = 'Voir les paramètres avancés pour les instructeurs';
$string['viewsettingsadvancedinstructorsdesc'] = 'Si cette option est activée, les options avancées pour les instructeurs seront affichées.';

$string['accesscontrol'] = 'Contrôle d\'accès';
$string['accesscontroldesc'] = 'n\'autoriser les utilisateurs à accéder à l\'examen que si les outils SMOWL sont actifs';

// Settings advancer users.
$string['usersconfig'] = 'Paramètres pour les utilisateurs';
$string['usersconfigdesc'] = 'Les options suivantes personnalisent l\'utilisation de SMOWL pour les utilisateurs dans les activités et les cours où le système de surveillance est activé.';

$string['floatblock'] = 'Activer le bloc de surveillance flottant';
$string['floatblockdesc'] = 'Sélectionnez pour afficher le bloc de surveillance en mode flottant.';
$string['activeinpopup'] = 'La surveillance est disponible dans le bloc flottant';

$string['blockheight'] = 'Taille de l\'écran';
$string['blockheightdesc'] = 'Hauteur de l\'iframe où la webcam sera affichée lors de la surveillance.';
$string['noticeblockheight'] = 'La hauteur de l\'iframe ne peut être inférieure à 280px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Paramètres avancés pour les utilisateurs';
$string['viewsettingsadvancedusers'] = 'Voir les paramètres avancés pour les utilisateurs';
$string['viewsettingsadvancedusersdesc'] = 'Si cette option est activée, les options avancées pour les utilisateurs seront affichées.';

// Capabilities.
$string['smowl:addinstance'] = 'Ajouter un bloc SMOWL';
$string['smowl:manageactivities'] = 'Gérer les activités SMOWL';
$string['smowl:managegroups'] = 'Gérer les groupes SMOWL';
$string['smowl:viewstudentcontent'] = 'Afficher les liens des étudiants';
$string['smowl:enrolment'] = 'Accéder au lien d\'enregistrement SMOWL';

// Notices.
$string['notinstancedblockincourse'] = 'Pour effectuer cette action, vous devez créer le bloc SMOWL dans le cours.';
$string['notmanagepermissions'] = "Vous n\' avez pas l\'autorisation de gérer des activités SMOWL.";
$string['notviewmanagepermissions'] = "Vous n\' avez pas l\'autorisation de visualiser ou de gérer les activités SMOWL.";
$string['notteachersmanagementpermissions'] = 'Les enseignants ne sont pas autorisés à gérer les activités SMOWL.';
$string['notviewmanagepermissions'] = 'Vous n\' avez pas l\'autorisation de visualiser ou de gérer les activités SMOWL.';
$string['cannotcreatefile'] = 'Le fichier n\'a pas pu être créé';
$string['activitiesupdate'] = 'Activités SMOWL mises à jour';
$string['activityupdate'] = 'Activité SMOWL mise à jour';
$string['noticeemptysmowlconfig'] = 'La configuration du bloc n\'a pas été complétée. <br/> '.
    "Contactez l\'administrateur de la plateforme pour résoudre ce problème.";
$string['noticeemptyentitynavigation'] = 'La configuration du bloc SMOWL n\'a pas été complétée. <br/>'.
    "Contactez l\'administrateur de la plateforme pour résoudre ce problème.";

// Privacy.
$string['privacy:metadata'] = 'Le système SMOWL n\'affiche que les données stockées sur les serveurs de Smowltech.';
$string['privacy:metadata:smowl:smowltech_net'] = 'Le système SMOWL affiche uniquement les données stockées et transmet les données utilisateur de Moodle aux serveurs de Smowltech.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = "Identifiant unique de l\'utilisateur";

// Events.
$string['eventinstancecreated'] = 'Instance de bloc créée par un événement';
$string['createblockinstance'] = 'Instance de bloc créée:';
$string['eventinstancedeleted'] = 'Instance de bloc supprimée par un événement';
$string['deleteblockinstance'] = 'Instance de bloc supprimé:';
$string['eventinstanceupdated'] = 'Instance de bloc mise à jour par un événement';
$string['updateblockinstance'] = 'Instance de bloc mise à jour';
$string['fromoldblockname'] = 'du vieux bloc';
$string['apicalled'] = 'Appel à l\'API SMOWL effectué';
$string['apicalleddesc'] = 'Appel à l\'API SMOWL de paramètres effectué.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Paramètres internes SMOWL';
$string['internalconfigdesc'] = 'Les options suivantes peuvent affecter le fonctionnement du plugin, elles ne doivent être modifiées que sur demande expresse de SMOWL';
$string['onlysmowlexpressrequest'] = 'Cette option doit être modifiée uniquement sur demande expresse de SMOWL.';
$string['viewsettingsinternal'] = 'Voir les options internes';
$string['viewsettingsinternaldesc'] = 'Si cette option est activée, vous pourrez à nouveau voir les options.';

$string['internalconfigurls'] = 'URL internes SMOWL';
$string['urlstudentview'] = 'URL vision de l\'étudiant';
$string['urlstudentviewdesc'] = 'Lien vers la vision de l\'étudiant';

// API URLs.
$string['internalconfigapiurls'] = 'URL API internes SMOWL';
$string['urlsmowlapi'] = 'URL API SMOWL';
$string['urlsmowlapidesc'] = 'URL pour accéder à l\'API SMOWL.';

$string['apilmssettings'] = 'Mettre à jour les paramètres du LMS';
$string['apilmssettingsdesc'] = 'URL pour mettre à jour les paramètres du LMS.';

$string['apilmssettingscustomer'] = 'Mise à jour automatique des paramètres du LMS';
$string['apilmssettingscustomerdesc'] = 'URL pour mettre à jour les paramètres du LMS dans les installations automatiques.';

$string['apiconfigclient'] = 'Activation de l\'intégration';
$string['apiconfigclientdesc'] = 'URL pour obtenir les données d\'activation de l\'intégration.';

$string['apiaddactivity'] = 'Ajouter une activité';
$string['apiaddactivitydesc'] = 'URL pour ajouter une activité surveillée';

$string['apimodifyactivity'] = 'Modifier l\'activité';
$string['apimodifyactivitydesc'] = 'URL pour modifier l\'activité surveillée';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'Règles d\'accès SMOWL';
$string['accesrulesmowlcheckcamconfigdesc'] = 'Les options suivantes affectent les règles d\'accès aux questionnaires avec surveillance.';
$string['accesrulesmowlcheckcam'] = 'Activer la validation de la caméra pour l\'étudiant';
$string['accesrulesmowlcheckcamdesc'] = 'Si la validation de la caméra est activée, l\'étudiant sera obligé de valider que sa caméra fonctionne correctement avant d\'accéder aux questionnaires.';

// View smowl settings.
$string['viewconfig'] = 'Configuration d\'affichage';
$string['viewconfigdesc'] = 'Les options suivantes affectent l\'affichage du bloc';
$string['floatsnap'] = 'Bloc flottant dans le thème Snap';
$string['floatsnapdesc'] = 'Sélectionnez pour afficher le bloc flottant, fonctionne uniquement pour le thème SNAP.';

// Manage SMOWL Groups.
$string['managegroups'] = 'Gestion des groupes';
$string['groupsaccessrestrictions'] = 'Restriction d\'accès';
$string['managegroupsformintro'] = 'Dans le formulaire suivant, vous devez sélectionner les groupes ou les regroupements d\'utilisateurs auxquels le bloc SMOWL sera affiché.';
$string['managegroupsupdate'] = 'Groupes avec SMOWL actif, correctement mis à jour.';
$string['availabilityconditionsjsonform'] = 'Restrictions d\'accès';
$string['availabilityconditionsjsonform_help'] = 'Depuis ce menu, vous pouvez ajouter les restrictions d\'accès nécessaires.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'Vous n\'avez pas les autorisations pour afficher la gestion des groupes.';
$string['managegroupsnotconfigured'] = 'La gestion des groupes n\'est pas configurée.';

// Bulk actions.

$string['bulkactions'] = 'Actions en masse';
$string['bulkactionsdesc'] = 'Si activé, vous pourrez gérer la recherche et l\'activation de SMOWL dans les activités, depuis la page d\'accueil du campus.';
$string['noticeactivebulkactions'] = 'Pour activer cette fonctionnalité, vous devez aller dans \"Options internes\" de SMOWL';
$string['bulkactive'] = 'Activation des activités';
$string['bulkgroups'] = 'Activation des groupes';
$string['coursecategory'] = 'Catégorie de cours';
$string['coursecategory_help'] = 'Catégorie de cours où activer SMOWL.';
$string['activitytype'] = 'Types d\'activité';
$string['activitytype_help'] = 'Type d\'activité où activer SMOWL.';
$string['activityname'] = 'Nom de l\'activité';
$string['activityname_help'] = 'Nom de l\'activité où activer SMOWL.';
$string['searchactivities'] = 'Rechercher des activités';
$string['allcategories'] = 'Toutes les catégories';
$string['searchresults'] = 'Résultats de la recherche';
$string['notfound'] = 'Résultats non trouvés';
$string['savechanges'] = 'Enregistrer les modifications';
$string['bulkactiveupdate'] = 'Configuration des activités SMOWL en masse mise à jour correctement';
$string['searchgroups'] = 'Rechercher des groupes';
$string['groupname'] = 'Nom du groupe';
$string['groupname_help'] = 'Nom du groupe pour activer SMOWL.';
$string['managebulkgroupsformintro'] = 'Dans le formulaire suivant, vous pouvez voir tous les groupes résultant de la recherche.';
$string['managebulkgroupsforminfo'] = 'Les utilisateurs appartenant à l\'un des groupes sélectionnés seront surveillés par SMOWL.';
$string['managebulkgroupsformmoreinfo'] = 'IMPORTANT: l\'attribution de groupes de ce formulaire écrasera l\'attribution de groupes appliquée à chacun des cours concernés.';
$string['notviewmanagebuklpermissions'] = 'Vous n\'avez pas l\'autorisation d\'afficher ou d\'administrer les activités SMOWL en masse';

// Access Control Status.
$string['acwaiting'] = 'Vérification de SMOWL, veuillez patienter';
$string['acaccess'] = 'SMOWL activé, vous pouvez accéder à votre activité';
$string['acnotaccess'] = 'SMOWL n\'a pas pu être validé, rechargez le navigateur pour vérifier à nouveau';

// LTI Integration.
$string['internalconfigltitool'] = 'URL de configuration LTI SMOWL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'URL de base LTI';
$string['urlsmowlltitooldesc'] = 'URL de base de l\'outil LTI de SMOWL';
$string['ltitoolinit'] = 'URL initiale';
$string['ltitoolinitdesc'] = 'URL initiale de l\'outil LTI de SMOWL';
$string['ltitoolversion'] = 'Version LTI';
$string['ltitoolversiondesc'] = 'Version de l\'outil LTI de SMOWL';
$string['ltitoolpublickeyset'] = 'Public keyset';
$string['ltitoolpublickeysetdesc'] = 'URL du public keyset de l\'outil LTI de SMOWL';
$string['ltitoolinitiatelogin'] = 'URL de connexion initiale';
$string['ltitoolinitiatelogindesc'] = 'URL de connexion initiale de l\'outil LTI de SMOWL';
$string['ltitoolredirection'] = 'URI de redirection';
$string['ltitoolredirectiondesc'] = 'URI de redirection de l\'outil LTI de SMOWL';
$string['ltitoolconfigusage'] = 'Utilisation de la configuration';
$string['ltitoolconfigusagedesc'] = 'Utilisation de la configuration de l\'outil LTI de SMOWL comme \"Afficher comme outil préconfiguré lors de l\'ajout d\'un outil externe\"';
$string['ltitoollaunch'] = 'Conteneur de lancement par défaut';
$string['ltitoollaunchdesc'] = 'Conteneur de lancement par défaut de l\'outil LTI de SMOWL comme \"Incrustation, sans blocs\"';
$string['ltitoolconfigmemberships'] = 'Membres par défaut';
$string['ltitoolconfigmembershipsdesc'] = 'Membres par défaut de l\'outil LTI de SMOWL comme \"Incrustation, sans blocs\"';

$string['urlsmowlltiapi'] = 'URL de base API LTI';
$string['urlsmowlltiapidesc'] = 'URL de base de l\'API LTI de SMOWL';
$string['ltiapiapplications'] = 'Applications LTI';
$string['ltiapiapplicationsdesc'] = 'Applications LTI de l\'API LTI de SMOWL';
$string['ltiapideployments'] = 'Déploiements LTI';
$string['ltiapideploymentsdesc'] = 'Déploiements LTI de l\'API LTI de SMOWL';

// LTI problems.
$string['lticreatetoolsuccess'] = 'Outil LTI SMOWL créé avec succès';
$string['ltiupdatetoolsuccess'] = 'Outil LTI SMOWL mis à jour avec succès';
$string['lticreatetoolerror'] = 'Problèmes lors de la création de l\'outil LTI SMOWL';
$string['ltisendtoolerror'] = 'Problèmes lors de l\'envoi de la configuration LTI à SMOWL';
$string['ltisendtoolneedactivation'] = 'Attention! Il semble que votre entité n\'ait pas encore été validée dans l\'application mySmowltech, ce qui empêche la finalisation de la configuration du plugin. <a href="https://my.smowltech.net/" target="_blank">Cliquez ici</a> et connectez-vous avec vos identifiants d\'accès à mySmowltech pour valider votre entité. Cette validation est nécessaire pour terminer correctement l’intégration.';
$string['lticreatewserror'] = 'Problèmes lors de la création du WS SMOWL';
$string['ltiactivewserror'] = 'Problèmes lors de l\'activation du WS SMOWL';
$string['lticreateusererror'] = 'Il y a des problèmes pour créer l\'utilisateur "Smowl Webservices User". Une action de l\'administrateur est nécessaire pour l\'activer.';
$string['noticeltinotvisible'] = 'Les outils LTI sont verrouillés dans l\'administration du campus (Plugins / Gérer les activités).';
$string['ltisendwserror'] = 'Problèmes lors de l\'envoi des informations WS';
$string['ltiltiactivityerror'] = 'Problèmes lors de la création de l\'activité LTI SMOWL';
$string['notlticourse'] = 'Do not have LTI in course.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Configuration interne LTI SMOWL';
$string['ltientity'] = 'Entité LTI';
$string['ltitypeid'] = 'Type d\'ID LTI';
$string['ltideploymentid'] = 'Déploiement LTI';
$string['ltiappid'] = 'Application LTI';
$string['ltirestid'] = 'REST LTI';

$string['ltiactivityname'] = 'Panneau SMOWL';

// Block links teacher.
$string['ltimanagesmowl'] = 'Tableau de bord de surveillance';
$string['teachercontent'] = 'Set up monitoring and review results';
$string['teacherbutton'] = 'Accéder à SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'Panneau SMOWL';
$string['studentcontent'] = 'Accédez au panneau d\'inscription et de téléchargement de SMOWL';
$string['studentbutton'] = 'Accéder à SMOWL';

$string['notstudentaccesspermissions'] = 'Seuls les étudiants peuvent accéder à cette section';

// Corner
$string['drag_me'] = 'Glissez-moi';