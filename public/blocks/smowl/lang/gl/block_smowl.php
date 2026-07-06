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

$string['typeconfigheading'] = 'Tipo de configuración';
$string['typeconfigheadingdesc'] = 'Selecciona el tipo de configuración <br> · Automática <br> · Manual <br> · Configurar más tarde';
$string['typeconfigheadingdesc2'] = 'A continuación, si lo deseas, puedes cambiar tu tipo de configuración';

$string['typeconfig'] = 'Type Config';
$string['typeconfigdesc'] = 'Selecciona la acción que quieres realizar a continuación';
$string['noconfigmessage'] = 'Has seleccionado configurar SMOWL más tarde, podrás hacerlo desde la administración del campus.';

$string['changetypeconfigauto'] = 'Cambiar a configuración automática.';
$string['changetypeconfigrestartauto'] = 'Reiniciar configuración automática.';
$string['changetypeconfigmanual'] = 'Cambiar a configuración manual.';
$string['changetypeconfigcancel'] = 'Cancelar cambio de configuración.';
$string['changetypeconfigafter'] = 'Configurar posteriormente.';

$string['typeask'] = 'Selecciona una opción';
$string['typenoconfig'] = 'Configurar más tarde';
$string['typeautoconfig'] = 'Configuración automática';
$string['typemanualconfig'] = 'Configuración manual';

$string['dataautoconfigheading'] = 'Datos de configuración automática';
$string['dataautoconfigheadingdesc'] = 'A continuación podrás validar o insertar los datos de autoconfiguración';

$string['autoconfigmessagejsondataclient'] = 'La información de cliente viene preconfigurada en este plugin.';
$string['autoconfigmessageconfigdone'] = 'El plugin SMOWL se ha configurado automáticamente';

$string['autoconfigmessageclientvoid'] = 'La información de cliente debe configurarse correctamente';
$string['autoconfigmessagecontenterror'] = 'Error de autoconfiguración: Retorno con elementos no válidos';
$string['autoconfigmessageunauthorized'] = 'Error de autoconfiguración: Problemas de autenticación';
$string['autoconfigmessagebadrequest'] = 'Error de autoconfiguración: Bad Request';
$string['autoconfigmessageentityerror'] = 'Error de autoconfiguración: No se ha podido obtener entidad';
$string['autoconfigmessageconflict'] = 'Error de autoconfiguración: Problemas internos en la entidad';

$string['clientid'] = 'Identificador de cliente';
$string['clientiddesc'] = 'Identificador de cliente para activar el campus';

$string['clientkey'] = 'Clave de activación';
$string['clientkeydesc'] = 'Clave de activación para activar el campus';

$string['nocapabilityconfigmessage'] = 'No tienes parmisos para realizar esta acción';

// Connection smowl settings.
$string['connectionsconfig'] = 'Configuración de conexión';
$string['connectionsconfigdesc'] = 'A continuación encontrará los valores de conexión de su plataforma con Smowltech.';
$string['connectionsconfigcontact'] = 'Para obtener más información sobre su licencia actual, acceda a su área personal de cliente en <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Plataforma';
$string['entitydesc'] = 'Identificador único para esta plataforma, proporcionado por <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>.';
$string['noticeemptyentitysettings'] = 'El campo plataforma está vacío, contacte con Smowltech para conocer el valor a introducir.';

$string['password'] = 'Clave de licencia';
$string['passworddesc'] = 'Clave de licencia para la plataforma, proporcionado por Smowltech.';
$string['noticeemptypasswordsettings'] = 'La clave de licencia está vacía, contacte con Smowltech para conocer el valor a introducir.';

$string['apikey'] = 'Clave API';
$string['apikeydesc'] = 'Clave para acceso a API, proporcionado por Smowltech.';
$string['noticeemptyapikeysettings'] = 'La clave API está vacía, contacte con Smowltech para conocer el valor a introducir.';

// JWT Secret
$string['smowljwtsecret'] = 'Clave secreta JWT';
$string['smowljwtsecretdesc'] = 'Clave secreta para JWT, proporcionado por Smowltech.';
$string['noticeemptysmowljwtsecret'] = 'La clave secreta JWT está vacía, contacte con Smowltech para conocer el valor a introducir.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Configuración para instructores';
$string['instructorsconfigdesc'] = 'Las siguientes opciones personalizan el empleo de SMOWL para instructores en los cursos donde el bloque esté activado.';

$string['continuousassessment'] = 'Evaluación continua';
$string['onlyexams'] = 'Monitorización de exámenes';

$string['tracking'] = 'Tipo de monitorización';
$string['trackingdesc'] = 'Si el tipo marcado es “Monitorización de exámenes” SMOWL solo podrá activarse en cuestionarios.<br>Si el tipo marcado es “Evaluación continua” SMOWL estará disponible para todas las actividades proporcionadas en MOODLE.';

$string['attempttracking'] = 'Distinción de intentos de examen';
$string['attempttrackingdesc'] = 'Seleccionar si se quiere diferenciar entre distintos intentos de un mismo examen para un alumno.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Configuración avanzada para instructores';
$string['viewsettingsadvancedinstructors'] = 'Ver configuración avanzada para instructores';
$string['viewsettingsadvancedinstructorsdesc'] = 'Si se activa esta opción se mostrarán las opciones avanzada para instructores.';

$string['accesscontrol'] = 'Control de acceso';
$string['accesscontroldesc'] = 'Sólo permitir acceder a los usuarios al examen si constan activas las herramientas de SMOWL';

// View smowl settings.
$string['usersconfig'] = 'Configuración para usuarios';
$string['usersconfigdesc'] = 'Las siguientes opciones personalizan el empleo de SMOWL para usuarios en aquellas actividades y cursos donde esté activado el sistema de monitorización.';

$string['floatblock'] = 'Activar bloque de proctoring flotante';
$string['floatblockdesc'] = 'Seleccionar para ver el bloque de proctoring en modo flotante.';
$string['activeinpopup'] = 'La supervisión está disponible en el bloque flotante';

$string['blockheight'] = 'Tamaño visualización';
$string['blockheightdesc'] = 'Altura del iframe donde se mostrará la webcam durante la monitorización.';
$string['noticeblockheight'] = 'Altura del iframe no puede ser inferior a 280px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Configuración avanzada para usuarios';
$string['viewsettingsadvancedusers'] = 'Ver configuración avanzada para usuarios';
$string['viewsettingsadvancedusersdesc'] = 'Si se activa esta opción se mostrarán las opciones avanzada para usuarios.';

// Capabilities.
$string['smowl:addinstance'] = 'Añadir bloque SMOWL';
$string['smowl:manageactivities'] = 'Gestionar actividades SMOWL';
$string['smowl:managegroups'] = 'Manage SMOWL groups';
$string['smowl:viewstudentcontent'] = 'Visualizar enlaces de estudiante';
$string['smowl:enrolment'] = 'Acceder al enlace de registro de SMOWL';

// Notices.
$string['notinstancedblockincourse'] = 'Para realizar esta acción debes crear el bloque SMOWL en el curso.';
$string['notmanagepermissions'] = 'No tienes permisos para gestionar actividades SMOWL.';
$string['notteachersmanagementpermissions'] = 'Los profesores no tienen permitida la gestión de actividades SMOWL.';
$string['notviewmanagepermissions'] = 'No tienes permisos para visualizar o gestionar actividades SMOWL.';
$string['cannotcreatefile'] = 'No se ha podido crear el fichero';
$string['activitiesupdate'] = 'Actividades SMOWL actualizadas';
$string['activityupdate'] = 'Actividad SMOWL actualizada';
$string['noticeemptysmowlconfig'] = 'La configuración del bloque no se ha completado.<br/>'.
    'Contacta con el administrador de la plataforma para solucionar este problema.';
$string['noticeemptyentitynavigation'] = 'La configuración del bloque SMOWL no se ha completado.<br/>'.
    'Contacta con el administrador de la plataforma para solucionar este problema.';

// Privacy.
$string['privacy:metadata'] = 'El sistema SMOWL solamente muestra datos almacenados en los servidores de Smowltech.';
$string['privacy:metadata:smowl:smowltech_net'] = 'El sistema SMOWL solo muestra los datos almacenados y transmite los datos del usuario de Moodle a los servidores de Smowltech. ';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Identificador único de usuario';

// Events.
$string['eventinstancecreated'] = 'Evento instancia de bloque creada';
$string['createblockinstance'] = 'Instancia de bloque creada: ';
$string['eventinstancedeleted'] = 'Evento instancia de bloque eliminada';
$string['deleteblockinstance'] = 'Instancia de bloque eliminada: ';
$string['eventinstanceupdated'] = 'Evento instancia de bloque actualizada';
$string['updateblockinstance'] = 'Instancia de bloque actualizada ';
$string['fromoldblockname'] = ' del bloque antiguo ';
$string['apicalled'] = 'Llamada a SMOWL API realizada';
$string['apicalleddesc'] = 'Llamada a SMOWL API de settings realizada.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Configuraciones internas SMOWL';
$string['internalconfigdesc'] = 'Las siguientes opciones pueden afectar al funcionamiento del plugin,'.
    ' se deben modificar únicamente bajo petición expresa de SMOWL';
$string['onlysmowlexpressrequest'] = 'Esta opción debe modificarse solo a petición expresa de SMOWL.';

$string['viewsettingsinternal'] = 'Ver opciones internas';
$string['viewsettingsinternaldesc'] = 'Si se activa este check, podrás volver a ver las opciones.';

$string['internalconfigurls'] = 'Configuraciones internas de URL SMOWL';
$string['urlstudentview'] = 'URL visión de estudiante';
$string['urlstudentviewdesc'] = 'Enlace a la visión del estudiante';

// API URLs.
$string['internalconfigapiurls'] = 'Configuraciones de URL API SMOWL';
$string['urlsmowlapi'] = 'URL API SMOWL';
$string['urlsmowlapidesc'] = 'URL para acceder a la API de SMOWL.';

$string['apilmssettings'] = 'Actualizar las configuraciones del LMS';
$string['apilmssettingsdesc'] = 'URL para actualizar las configuraciones del LMS.';

$string['apilmssettingscustomer'] = 'Actualización automática de las configuraciones del LMS';
$string['apilmssettingscustomerdesc'] = 'URL para actualizar las configuraciones del LMS en instalaciones automáticas.';

$string['apiconfigclient'] = 'Activación de integración';
$string['apiconfigclientdesc'] = 'URL para obtenet los datos de activación de integración.';

$string['apiaddactivity'] = 'Agregar actividad';
$string['apiaddactivitydesc'] = 'URL para agregar actividad de proctoring';

$string['apimodifyactivity'] = 'Modificar actividad';
$string['apimodifyactivitydesc'] = 'URL para modificar la actividad de proctoring';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'Configuración de las reglas de acceso de SMOWL';
$string['accesrulesmowlcheckcamconfigdesc'] = 'Las siguientes opciones afectan a las reglas de acceso para cuestionarios con proctoring.';
$string['accesrulesmowlcheckcam'] = 'Activar validación de cámara para el alumno';
$string['accesrulesmowlcheckcamdesc'] = 'Si se activa la validación de cámara, el alumno se verá obligado a validar que su cámara está funcionando correctamente antes de acceder a los cuestionarios.';

// View smowl settings.
$string['viewconfig'] = 'Configuración de visualización';
$string['viewconfigdesc'] = 'Las siguientes opciones afectan a la visualización del bloque';
$string['floatsnap'] = 'Bloque flotante en tema Snap';
$string['floatsnapdesc'] = 'Seleccionar para ver el bloque flotante, solo funciona para el tema SNAP.';

// Manage SMOWL Groups.
$string['managegroups'] = 'Gestión de grupos';
$string['groupsaccessrestrictions'] = 'Restricción de acceso';

$string['managegroupsformintro'] = 'En el siguiente formulario, se deben seleccionar los grupos o agrupaciones de usuarios a los que se mostrará el bloque de SMOWL.';
$string['managegroupsupdate'] = 'Grupos con SMOWL activo, correctamente actualizados.';

$string['availabilityconditionsjsonform'] = 'Restricciones de acceso';
$string['availabilityconditionsjsonform_help'] = 'Desde este menú se pueden añadir las restricciones de acceso necesarias.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'No tienes permisos para visualizar la gestión de grupos.';
$string['managegroupsnotconfigured'] = 'La gestión de grupos no está configurada.';

// Bulk actions.
$string['bulkactions'] = 'Acciones masivas';
$string['bulkactionsdesc'] = 'Si se activa, podrás gestionar la búsqueda y activación de SMOWL en actividades, desde la portada del campus.';
$string['noticeactivebulkactions'] = 'Para activar esta funcionalidad, debes ir a "Opciones internas" de SMOWL';
$string['bulkactive'] = 'Activación de actividades';
$string['bulkgroups'] = 'Activación de grupos';
$string['coursecategory'] = 'Categoría de cursos';
$string['coursecategory_help'] = 'Categoría de cursos donde activar SMOWL.';
$string['activitytype'] = 'Tipos de actividad';
$string['activitytype_help'] = 'Tipo de actividad donde activar SMOWL.';
$string['activityname'] = 'Nombre de la actividad';
$string['activityname_help'] = 'Nombre de la actividad donde activar SMOWL.';
$string['searchactivities'] = 'Buscar actividades';
$string['allcategories'] = 'Todas las categorías';
$string['searchresults'] = 'Resultados de la búsqueda';
$string['notfound'] = 'Resultados no encontrados';
$string['savechanges'] = 'Guardar cambios';
$string['bulkactiveupdate'] = 'Configuración de actividades masivas SMOWL actualizada correctamente';
$string['searchgroups'] = 'Buscar grupos';
$string['groupname'] = 'Nombre del grupo';
$string['groupname_help'] = 'Nombre del grupo para activar SMOWL.';
$string['managebulkgroupsformintro'] = 'En el siguiente formulario puedes ver todos los grupos resultantes de la búsqueda.';
$string['managebulkgroupsforminfo'] = 'Los usuarios que pertenezcan cualquiera de los grupos seleccionados, serán monitorizados por SMOWL.';
$string['managebulkgroupsformmoreinfo'] = 'IMPORTANTE:<BR/> La asignación de grupos de este formulario soreescribirá la asignación de grupos aplicada en cada uno de los cursos implicados.';
$string['notviewmanagebuklpermissions'] = 'No tiene permiso para ver o administrar las actividades masivas de SMOWL';

// Access Control Status.
$string['acwaiting'] = 'Revisando SMOWL, por favor espera';
$string['acaccess'] = 'SMOWL activado, puedes acceder a tu actividad';
$string['acnotaccess'] = 'No se ha podido validar SMOWL, recarga el navegador para comprobar de nuevo';

// LTI Integration.
$string['internalconfigltitool'] = 'Configuración de URL LTI SMOWL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'URL Base LTI';
$string['urlsmowlltitooldesc'] = 'URL base de la herramienta LTI de SMOWL';
$string['ltitoolinit'] = 'URL inicial';
$string['ltitoolinitdesc'] = 'URL inicial de la herramienta LTI de SMOWL';
$string['ltitoolversion'] = 'Versión LTI';
$string['ltitoolversiondesc'] = 'Versión de la herramienta LTI de SMOWL';
$string['ltitoolpublickeyset'] = 'Public keyset';
$string['ltitoolpublickeysetdesc'] = 'URL del public keyset de la herramienta LTI de SMOWL';
$string['ltitoolinitiatelogin'] = 'URL de inicio de sesión';
$string['ltitoolinitiatelogindesc'] = 'URL de inicio de sesión de la herramienta LTI de SMOWL';
$string['ltitoolredirection'] = 'Redirection URI';
$string['ltitoolredirectiondesc'] = 'Redirection URI de la herramienta LTI de SMOWL';
$string['ltitoolconfigusage'] = 'Uso de configuración';
$string['ltitoolconfigusagedesc'] = 'Uso de configuración de la herramienta LTI de SMOWL como "Mostrar como herramienta preconfigurada al agregar una herramienta externa"';
$string['ltitoollaunch'] = 'Contenedor de lanzamiento predeterminado';
$string['ltitoollaunchdesc'] = 'Contenedor de lanzamiento predeterminado de la herramienta LTI de SMOWL como "Incrustar, sin bloques"';
$string['ltitoolconfigmemberships'] = 'Membresías predeterminadas';
$string['ltitoolconfigmembershipsdesc'] = 'Membresías predeterminadas de la herramienta LTI de SMOWL como "Incrustar, sin bloques"';

$string['urlsmowlltiapi'] = 'URL Base API LTI';
$string['urlsmowlltiapidesc'] = 'URL base de la API LTI de SMOWL';
$string['ltiapiapplications'] = 'Aplicaciones LTI';
$string['ltiapiapplicationsdesc'] = 'Aplicaciones LTI de la API LTI de SMOWL';
$string['ltiapideployments'] = 'Despliegues LTI';
$string['ltiapideploymentsdesc'] = 'Despliegues LTI de la API LTI de SMOWL';

// LTI problems.
$string['lticreatetoolsuccess'] = 'Herramienta LTI SMOWL creada correctamente';
$string['ltiupdatetoolsuccess'] = 'Herramienta LTI SMOWL actualizada correctamente';
$string['lticreatetoolerror'] = 'Problemas al crear la herramienta LTI SMOWL';
$string['ltisendtoolerror'] = 'Problemas al enviar la configuración LTI a SMOWL';
$string['ltisendtoolneedactivation'] = '¡Atención! Parece que su entidad aún no ha sido validada en la aplicación mySmowltech, lo que impide completar la configuración del complemento. Haz <a href="https://my.smowltech.net/" target="_blank">clic aquí</a> e inicia sesión con tus credenciales de acceso a mySmowltech para validar tu entidad. Esta validación es necesaria para completar la integración correctamente.';
$string['lticreatewserror'] = 'Problemas al crear el WS de SMOWL';
$string['ltiactivewserror'] = 'Problemas al activar el WS de SMOWL';
$string['lticreateusererror'] = 'Existen problemas al crear el usuario "Smowl Webservices User". Se requiere una acción del administrador para activarlo.';
$string['noticeltinotvisible'] = 'Las herramientas LTI estan bloqueadasen la administración del campus (Plugins / Manage activities).';
$string['ltisendwserror'] = 'Problemas al enviar la información del WS';
$string['ltiltiactivityerror'] = 'Problemas al crear la actividad LTI de SMOWL';
$string['notlticourse'] = 'No hay LTI en el curso.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Configuración interna LTI SMOWL';
$string['ltientity'] = 'Entidad LTI';
$string['ltitypeid'] = 'Tipo de ID LTI';
$string['ltideploymentid'] = 'Despliegue LTI';
$string['ltiappid'] = 'Aplicación LTI';
$string['ltirestid'] = 'REST LTI';

$string['ltiactivityname'] = 'Panel SMOWL';

// Block links teacher.
$string['ltimanagesmowl'] = 'Panel de supervisión';
$string['teachercontent'] = 'Configura la monitorización y revisa los resultados';
$string['teacherbutton'] = 'Accede a SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'Panel SMOWL';
$string['studentcontent'] = 'Accede al panel de registro y descarga de SMOWL';
$string['studentbutton'] = 'Accede a SMOWL';

$string['notstudentaccesspermissions'] = 'Sólo los estudiantes pueden acceder a esta sección';

// Corner
$string['drag_me'] = 'Arrástrame';