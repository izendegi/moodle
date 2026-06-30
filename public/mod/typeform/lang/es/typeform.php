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
 * Cadenas de idioma para mod_typeform
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activititmodaltitle'] = 'Typeform activity';
$string['activitymodaltitle'] = 'Actividad Typeform';
$string['loadingforms'] = 'Cargando formularios...';
$string['modulename'] = 'Encuesta Typeform';
$string['modulename_help'] = 'El módulo de actividad Typeform permite incrustar encuestas de Typeform en tu curso de Moodle. Los estudiantes pueden completar las encuestas directamente dentro de Moodle, y la finalización se registra automáticamente.';
$string['modulenameplural'] = 'Encuestas Typeform';
$string['name'] = 'Nombre';
$string['name_help'] = 'Pon nombre a la actividad.';
$string['pluginadministration'] = 'Administración de Typeform';
$string['pluginname'] = 'Typeform';

// Configuración
$string['typeformsettings'] = 'Configuración de Typeform';
$string['selecttypeform'] = 'Seleccionar encuesta Typeform';
$string['selecttypeform_help'] = 'Selecciona una encuesta de Typeform desde tu cuenta de Typeform. Solo se mostrarán formularios de workspaces autorizados.';
$string['includestudentcode'] = 'Incluir Student Code anónimo';
$string['includestudentcode_help'] = 'El parámetro student_code se anonimiza siempre (se cifra mediante hash) antes de enviarse a Typeform. Si está marcado, ese student_code anónimo se incluye en la URL del formulario; si no está marcado, no se envía. Desmarcado por defecto.';
$string['apitoken'] = 'Token de API de Typeform';
$string['apitoken_desc'] = 'Introduce tu token de API de Typeform. Puedes obtenerlo desde la configuración de tu cuenta de Typeform.';
$string['allowedworkspaces'] = 'Workspaces permitidos';
$string['allowedworkspaces_desc'] = 'Lista separada por comas de IDs de workspaces que están permitidos. Deja vacío para permitir todos los workspaces.';
$string['testconnection'] = 'Probar conexión';
$string['notokenconfigured'] = 'El token de API de Typeform no está configurado. Por favor, configúralo en la configuración del plugin.';

// Errores
$string['errortypeformrequired'] = 'Debes seleccionar una encuesta de Typeform.';
$string['errorloadingforms'] = 'Error al cargar las encuestas de Typeform. Por favor, verifica tu token de API e inténtalo de nuevo.';
$string['notypeforms'] = 'No se encontraron encuestas de Typeform.';

// Finalización
$string['completiondetail:submit'] = 'El estudiante debe enviar el formulario';
$string['formcompleted'] = '¡Gracias! Tu respuesta ha sido registrada.';
$string['formstarted'] = 'formstarted';
$string['alreadycompleted'] = 'Ya has completado esta encuesta.';

// Privacidad
$string['privacy:metadata'] = 'El módulo Typeform no almacena ningún dato personal. Las respuestas de las encuestas se almacenan en Typeform, no en Moodle. Moodle solo almacena el estado de finalización (si el usuario completó la encuesta).';
$string['privacy:metadata:course_modules_completion'] = 'Información sobre la finalización de actividades Typeform';
$string['privacy:metadata:course_modules_completion:userid'] = 'El ID del usuario que completó la actividad Typeform';
$string['privacy:metadata:course_modules_completion:completionstate'] = 'Si la actividad Typeform ha sido completada';
$string['privacy:metadata:course_modules_completion:timemodified'] = 'El momento en que se completó la actividad Typeform';
$string['privacy:metadata:typeform'] = 'Para integrarse con Typeform, los datos del usuario deben intercambiarse con ese servicio.';
$string['privacy:metadata:typeform:userid'] = 'El ID de usuario se envía desde Moodle para permitirte acceder a tus datos en Typeform';

// Capacidades
$string['typeform:view'] = 'Ver Typeform';
$string['typeform:addinstance'] = 'Añadir una nueva actividad Typeform';

// Probar conexión
$string['configuration'] = 'Configuración';
$string['testresults'] = 'Resultados de la prueba';
$string['testingconnection'] = 'Probando conexión con la API';
$string['testingforms'] = 'Probando recuperación de formularios';
$string['testingworkspaces'] = 'Probando workspaces';
$string['connectionsuccessful'] = '¡Conexión exitosa!';
$string['connectionfailed'] = 'Conexión fallida. Por favor, verifica tu token de API.';
$string['formsfound'] = 'Se encontraron {$a} formulario(s)';
$string['noformsfound'] = 'No se encontraron formularios.';
$string['andmoreforms'] = '... y {$a} formulario(s) más';
$string['allworkspacesvalid'] = 'Todos los {$a} workspace(s) son válidos';
$string['someworkspacesinvalid'] = 'Algunos workspaces son inválidos: {$a}';
$string['alltestspassed'] = '¡Todas las {$a} prueba(s) pasaron exitosamente!';
$string['sometestsfailed'] = 'Algunas pruebas fallaron: {$a->passed} pasaron, {$a->failed} fallaron';
$string['allworkspaces'] = 'Todos los workspaces (ninguno configurado)';
$string['summary'] = 'Resumen';
$string['back'] = 'Volver';
$string['completionsubmit'] = 'Hacer una entrega';
$string['attemptfinished'] = 'Intento finalizado';
$string['attemptstarted'] = 'Intento iniciado';
$string['eventnotexists'] = 'El evento no existe';
$string['cmnotexists'] = 'cm not exists';
$string['alreadyexist'] = 'Ya existe';
$string['privacy:metadata:typeform_submission'] = 'Información de envíos del usuario en la actividad Typeform.';
$string['privacy:metadata:typeform_submission:typeform'] = 'El ID de la instancia de Typeform (actividad) a la que pertenece el envío.';
$string['privacy:metadata:typeform_submission:userid'] = 'El ID del usuario que realizó el envío.';
$string['privacy:metadata:typeform_submission:submitted'] = 'Si el usuario ha marcado/completado el envío (estado de envío).';
$string['privacy:metadata:typeform_submission:timecreated'] = 'Fecha/hora de creación del registro de envío.';
$string['privacy:metadata:typeform_submission:timemodified'] = 'Fecha/hora de la última modificación del registro de envío.';
$string['privacy:metadata:typeform_submission:usermodified'] = 'Usuario que modificó por última vez el registro (si aplica).';
$string['apiurl'] = 'Api url';
$string['apiurl_desc'] = 'La url debe de terminar en /';
$string['typeformjslink'] = 'Enlace JS';
$string['typeformjslink_desc'] = 'Enlace del javascript de Typeform';
$string['typeformdomain'] = 'Dominio';
$string['typeformdomain_desc'] = 'Dominio de Typeform';
$string['fworkspace'] = 'Seleccionar Workspace de Typeform';
