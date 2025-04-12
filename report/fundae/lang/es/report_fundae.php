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

$string['pluginname'] = 'Informe Fundae';
$string['fundae:manage'] = 'Administrar Informes Fundae';
$string['fundae:view'] = 'Ver Informes Fundae';

$string['usertime'] = 'Tiempos de usuario';
$string['timeoncourse'] = 'Tiempo en el curso';
$string['timeactivities'] = 'Tiempo en actividades';
$string['timescorms'] = 'Tiempo en SCORMs';
$string['timezoom'] = 'Tiempo en Zoom';
$string['timebbb'] = 'Tiempo en BBB';
$string['timescreen'] = 'Tiempo en pantalla';
$string['timeinteracting'] = 'Tiempo interactuando';
$string['numberofsessions'] = 'Número de sesiones';
$string['daysonline'] = 'Días conectado';
$string['ratioonline'] = 'Ratio conectado';
$string['messages'] = 'Mensajes';
$string['messagestostudents'] = 'Mensajes a estudiantes';
$string['messagestoteachers'] = 'Mensajes a profesores';
$string['studentscontacted'] = 'Estudiantes contactados';
$string['teacherscontacted'] = 'Profesores contactados';
$string['access'] = 'Accesos al curso';
$string['firstaccess'] = 'Primer acceso al curso';
$string['lastaccess'] = 'Último acceso al curso';
$string['activityfirstaccess'] = 'Primer acceso a la actividad';
$string['activitylastaccess'] = 'Último acceso a la actividad';
$string['actions'] = 'Acciones';
$string['details'] = 'Detalle de usuario';
$string['nodata'] = '-';
$string['activityname'] = 'Nombre de la actividad';
$string['screentime'] = 'Tiempo de pantalla';
$string['engagedtime'] = 'Tiempo de compromiso';
$string['hits'] = 'Recuento de eventos';
$string['unabletogeneratereports'] = 'Cron parece estar desactivado. Si cron no se está ejecutando los informes programados no se generarán. Póngase en contacto con su administrador de Moodle para obtener más información.';
$string['general'] = 'General';
$string['enableteachersseereports'] = 'Mostrar informes a los Profesores';
$string['enableteachersseereports_desc'] = 'Los profesores podrán visualizar y descargar los informes de sus cursos, donde visualizarán los datos de todos los estudiantes.';
$string['enablestudentsseereports'] = 'Mostrar informes a los Estudiantes';
$string['enablestudentsseereports_desc'] = 'Los estudiantes podrán visualizar y descargar sus propios informes, donde únicamente visualizarán sus propios datos.<br><b>Al activar o desactivar esta opción se habilitará/deshabilitará la capacidad "moodle/site:viewreports" a los estudiantes para que puedan ver la pestaña de informes en los cursos.</b>';
$string['enablebbbstatistics'] = 'Mostrar estadísticas de Big Blue Button';
$string['enablebbbstatistics_desc'] = '<b>Marque esta opción si tiene configurado Big Blue Button en su plataforma y lo utiliza en sus cursos.</b> Se mostrará una columna nueva en los informes con los tiempos de los alumnos en las reuniones de bbb, y se sumarán los tiempos al de las actividades y el del curso.';
$string['timetracking'] = 'Seguimiento del tiempo';
$string['sessionthreshold'] = 'Umbral de la sesión (minutos)';
$string['sessionthreshold_desc'] = 'Cada sesión se estima mediante un conjunto de dos o más eventos consecutivos (eventos relevantes registrados) en los que el tiempo transcurrido entre cualquier par de eventos consecutivos no supera esta cantidad de tiempo. Hay que tener en cuenta que si se establece un umbral irreal, lo más probable es que se produzca una sobreestimación de los tiempos de las sesiones.';
$string['reportgeneration'] = 'Generación de informes';
$string['enablescheduledreportgeneration'] = 'Habilitar la generación programada de informes';
$string['enablescheduledreportgeneration_desc'] = 'Activar o desactivar la generación de informes periódicos. La programación por defecto se puede cambiar desde Administración del sitio > Servidor > Tareas > Tareas programadas';
$string['bulkscheduledreportgenerationmode'] = 'Modo de informes programados en masa';
$string['bulkscheduledreportgenerationmode_desc'] = 'Esta configuración sólo se aplica si la configuración de los informes programados está activada. Si se selecciona "Todos los cursos", todos los archivos de informes de los cursos se generarán durante el proceso programado. Si se selecciona "Todos los cursos y actividades", todos los archivos de informes de cursos y actividades se generarán durante el proceso programado.';
$string['disabled'] = 'Deshabilitado';
$string['allcourses'] = 'Todos los cursos';
$string['allcoursesandactivities'] = 'Todos los cursos y actividades';
$string['scheduledreportmailing'] = 'Informes programados por correo electrónico';
$string['scheduledreportmailing_desc'] = 'Lista separada por comas de los correos electrónicos que recibirán los archivos de informes seleccionados o programados en bloque cada vez que se generen.';
$string['scheduledreportgenerationformat'] = 'Formato de los informes programados';
$string['scheduledreportgenerationformat_desc'] = 'Elija el formato de archivo para los informes generados periódicamente.';
$string['ftpnoextension'] = 'No se ha instalado o habilitado la extensión php "ftp" necesaria para subir informes al servidor con una configuración FTP';
$string['sftpnoextension'] = 'La extensión php "ssh2" necesaria para subir informes al servidor con una configuración SFTP no ha sido instalada o habilitada';
$string['enableextensions'] = 'Al habilitar cualquiera de las extensiones de PHP necesarias para la carga de informes, se mostrarán los campos de conexión con el servidor';
$string['anyextensionsupload'] = 'No hay ninguna extensión PHP instalada o habilitada para la carga de archivos';
$string['ftpconfig'] = 'Configuración SFTP/FTP';
$string['ftpinformation'] = 'La carga de archivos en SFTP/FTP sólo se aplica si la configuración de informes programados está activada. Si se establece una configuración válida de SFTP/FTP, la generación periódica de informes subirá el documento a la ruta especificada cada vez que se genere.';
$string['credentialsjson'] = 'Credenciales Json';
$string['credentialsjson_help'] = 'Añade el contenido del archivo credentials.json.<br>Credenciales es un archivo JSON que obtienes cuando configuras la API de conexión en Google, en el panel de credenciales, en https://console.developers.google.com/';
$string['typehost'] = 'Tipo de anfitrión';
$string['typehost_help'] = 'Se utilizará el tipo de conexión establecido';
$string['clientid'] = 'Identificación del cliente';
$string['clientid_help'] = 'Corresponde a "client_id" en el archivo json';
$string['clientsecret'] = 'Cliente secreto';
$string['clientsecret_help'] = 'Corresponde a "client_secret" en el archivo json';
$string['folderid'] = 'ID de la carpeta';
$string['folderid_help'] = 'Corresponde al id de la carpeta en google drive. Aparece en la url.';
$string['historicfolderid'] = 'ID de la carpeta del historial';
$string['historicfolderid_help'] = 'Corresponde al id de la carpeta donde se almacena el historial de archivos en Google drive. Aparece en la url.';
$string['ftphost'] = 'Host';
$string['ftpport'] = 'Puerto';
$string['ftpuser'] = 'Username';
$string['ftppassword'] = 'Password';
$string['ftpremotepath'] = 'Ruta del archivo remoto';
$string['ftphost_help'] = 'El host FTP sin protocolo ni puerto. Ejemplo: ftp.moodle.org';
$string['ftpport_help'] = 'Puerto FTP. Ejemplo: 21';
$string['ftpuser_help'] = 'Usuario FTP válido.';
$string['ftppassword_help'] = 'Contraseña FTP válida.';
$string['ftpremotepath_help'] = 'Ruta remota. Ejemplo: /home/user/uploads/';
$string['generateperiodicreports'] = 'Generar informes periódicos';
$string['scheduledreportssubject'] = 'Informe programado {$a}';
$string['scheduledreportsmessage'] = '<p>Informes programados adjuntos</p>';
$string['ftpsettingerror'] = 'No hay configuración SFTP/FTP o no es correcta';
$string['ftpuploaded'] = 'Se ha subido {$a}';
$string['ftpnotuploaded'] = 'No se ha podido subir {$a}';
$string['ftpconexion'] = 'Conexión establecida';
$string['ftpuploading'] = 'Subiendo archivos a {$a}';
$string['ftploginerror'] = 'Error de inicio de sesión. No se puede iniciar sesión con el usuario y la contraseña proporcionados al host SFTP/FTP.';
$string['ftpuploaderror'] = 'Error al Subir. Se produjo un error al intentar cargar el archivo de informe al servidor SFTP/FTP.';
$string['ftpconnectionerror'] = 'Error de conexión. No se puede conectar con el servidor SFTP/FTP.';
$string['missinggdrive'] = 'Falta configuración para subir archivos a Google Drive';
$string['conexiongdrive'] = 'Conexión establecida con Google Drive, buscando carpetas.';
$string['folderdfound'] = 'Carpeta encontrada: ';
$string['folderdnotfound'] = 'No se encontraron carpetas.';
$string['deleteoldfile'] = 'Borrando archivo anterior: {$a}';
$string['createfile'] = 'Nuevo fichero creado: {$a}';
$string['createhistoricfile'] = 'Fichero histórico creado: {$a}';
$string['ftpupload'] = 'Subida de archivos al servidor:';
$string['coursecompletion'] = '% Completado';
$string['coursecompletion_help'] = 'Porcentaje del curso que ha sido completado por este usuario.

Tenga en cuenta que algunos cursos pueden tener el seguimiento de finalización deshabilitado a pesar de tener actividades.
';
$string['totalactivitiestimes'] = 'Tiempo en actividades';
$string['totalactivitiestimes_help'] = 'Tiempo en plataforma atribuible sólo a las actividades del curso.';
$string['totalscormtime'] = 'Tiempo en SCORMs';
$string['totalzoomtime'] = 'Tiempo en Zoom';
$string['totalbbbtime'] = 'Tiempo en BBB';
$string['totalscormtime_help'] = 'Suma de todos los tiempos registrados para este usuario en los SCORMs del curso.';
$string['dedicationtime'] = 'Tiempo dedicado';
$string['dedicationtime_help'] = 'El tiempo dedicado se estima en función de los conceptos de sesión y la duración de la sesión aplicada a los hits / eventos relevantes del registro.

* **Hit:** Cada vez que un usuario accede a una página en Moodle o envía una actividad, se almacena un evento en el registro de eventos.
* **Sesión:** conjunto de dos o más hits consecutivos en los que el tiempo transcurrido entre cada par de hits consecutivos no supera un tiempo máximo establecido (establecido en {$a} minutos).
* **Duración de la sesión:** tiempo transcurrido entre el primer y el último hit de una sesión.
* **Tiempo dedicado:** la suma de todas las duraciones de sesión para un usuario.
';
$string['connectionratio'] = 'Ratio conectado';
$string['connectionratio_help'] = '**Días conectados / Duración del curso en días**

Un valor cercano a 1 significa que el usuario se conectó casi todos los días desde que comenzó el curso.

Un valor cercano a 0 significa que el usuario se conectó pocos días desde que comenzó el curso.
';
$string['activitiesreport'] = 'Informe de actividades';
$string['viewuser'] = 'Ver usuario';
$string['filteroptions'] = 'Filtros';
$string['resultsperpage'] = 'Resultados por página';
$string['applyfilters'] = 'Aplicar filtros';
$string['coursereport'] = 'Informe de curso';
$string['entitycoursecompletion'] = 'Finalización del curso';
$string['profiledepartment'] = 'Departamento de perfil';
$string['entitycourseenrolment'] = 'Matriculación al curso';
$string['lastcourseaccess'] = 'Acceso al último curso';
$string['course_enrolment_timestarted'] = 'Inicio de la matriculación';
$string['course_enrolment_timeended'] = 'Fin de la matriculación';
$string['course_completion_days_course'] = 'Días de curso';
$string['course_completion_days_enrolled'] = 'Días matriculado';
$string['course_completion_progress'] = 'Progreso';
$string['course_completion_progress_percent'] = 'Progreso (%)';
$string['course_completion_reaggregate'] = 'Tiempo reagrupado';
$string['course_completion_timecompleted'] = 'Tiempo completado';
$string['course_completion_timeenrolled'] = 'Tiempo inscrito';
$string['course_completion_timestarted'] = 'Hora de inicio';
$string['course_enrolment_status'] = 'Situación de la matrícula';
$string['lessthanaday'] = 'Menos de un día';
$string['urlgradebook'] = 'Libro de calificaciones';
$string['allusers'] = 'Todos los usuarios';
$string['selectuser'] = 'Seleciona un usuario';