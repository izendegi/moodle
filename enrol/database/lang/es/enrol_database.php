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
 * Strings for component 'enrol_database', language 'es'.
 *
 * @package     enrol_database
 * @copyright   1999 onwards Martin Dougiamas  {@link http://moodle.com}
 *              2024 onwards Iñigo Zendegi  {@link https://mondragon.edu}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['autocreatecategory'] = 'Creación de categorias de cursos';
$string['autocreatecategory_desc'] = 'Si se activa, cuando se creen cursos que pertenecen a categorias que todavía no existen en Moodle, dichas categorías se crearán automáticamente.';
$string['categoryseparator'] = 'Carácter de separación de categorías';
$string['categoryseparator_desc'] = 'Deja vacío este campo si en tu base de datos externa no vas a utilizar subcategorías. En caso contrario, define el carácter que vayas a utilizar como separador de categorías. Tienes que definir la \'ruta\' de la subcategoría (en el campo \'Categoría de los nuevos cursos\') como los identificadores de las categorías separados por el carácter de separación. Por ejemplo, si usamos \'/\' como carácter de separación, deberíamos tener algo similar a categoria1/categoria2 (donde categoria1 es una categoría de primer nivel y categoria2 es una subcategoría dentro de categoria1)';
$string['database:config'] = 'Configurar instancias de matriculación en la base de datos';
$string['database:unenrol'] = 'Dar de baja usuarios suspendidos';
$string['dbencoding'] = 'Codificación de base de datos';
$string['dbhost'] = 'Host de la base de datos';
$string['dbhost_desc'] = 'Escriba la dirección IP del servidor de la base de datos o el nombre del host. Utilice un nombre de sistema DSN si está usando ODBC. Utilice un PDO DSN si está usando PDO.';
$string['dbname'] = 'Nombre de la base de datos';
$string['dbname_desc'] = 'Dejar en blanco si se utiliza un nombre DSN en la base de datos.';
$string['dbpass'] = 'Contraseña de la base de datos';
$string['dbsetupsql'] = 'Comando de configuración de la base de datos';
$string['dbsetupsql_desc'] = 'Comando SQL para la instalación de bases de datos especiales, a menudo utilizado para establecer la codificación de comunicación - ejemplo para MySQL y PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Usar cuotas de Sybase';
$string['dbsybasequoting_desc'] = 'Estilo de comilla simple Sybase  - necesario para Oracle, MS SQL y otras bases de datos. !No utilizar para MySQL!';
$string['dbtype'] = 'Driver de la base de datos';
$string['dbtype_desc'] = 'Nombre del driver de la base de datos ADOdb, tipo del motor de base de datos externo.';
$string['dbuser'] = 'Usuario de la base de datos';
$string['debugdb'] = 'Depurar ADOdb';
$string['debugdb_desc'] = 'Depurar conexión ADOdb a base de datos externa - se usa cuando se obtiene una página en blanco en la identificación. !No es adecuado para sitios en producción!';
$string['defaultcategory'] = 'Categoría por defecto del nuevo curso';
$string['defaultcategory_desc'] = 'Categoría por defecto para cursos de creación automática. Usada cuando no se ha especificado o no se ha encontrado el ID de una nueva categoría.';
$string['defaultrole'] = 'Rol por defecto';
$string['defaultrole_desc'] = 'Rol que se asigna por defecto si ningún otro rol se especifica en una tabla externa.';
$string['groupenroltable'] = 'Tabla remota de asignaciones a grupos';
$string['groupenroltable_desc'] = 'Indica el nombre de la tabla que contiene la lista de usuarios a añadir en grupos. Si se deja este campo vacío no se sincronizará la asignación a grupos.';
$string['groupfield'] = 'Campo del IDnumber del grupo';
$string['groupfield_desc'] = 'Nombre del campo de la tabla remota que recoge la información de identificación de grupos.';
$string['groupingcreation'] = 'Habilitar la creación de nuevas agrupaciones';
$string['groupingcreation_desc'] = 'Si está habilitada, las agrupaciones definidas en la base de datos externa que no existan en Moodle serán creadas automaticamente.';
$string['groupmessaging'] = 'Habilitar mensajería de grupo';
$string['groupmessaging_desc'] = 'Si está habilitada, los miembros de grupos podrán mandar mensajes al resto de miembros de sus grupos mediante la mensajería de Moodle.';
$string['groupupgrading'] = 'Actualizar el componente de grupo';
$string['groupupgrading_desc'] = 'Si está habilitada, cuando se vaya a añadir un miembro a un grupo desde la base de datos externa y ese miembro ya esté añadido previamente de forma manual a ese grupo, se actualizará el método de añadido al grupo cambiando el componente a base de datos externa, evitando así que se quite dicho usuario del grupo.';
$string['ignorehiddencourses'] = 'Pasar por alto cursos ocultos';
$string['ignorehiddencourses_desc'] = 'Si se activa esta opción, los usuarios no serán matriculados en cursos configurados como no disponibles para los estudiantes.';
$string['localcategoryfield'] = 'Campo de categoría local';
$string['localcoursefield'] = 'Campo de curso local';
$string['localrolefield'] = 'Campo de rol local';
$string['localtemplatefield'] = 'Campo de plantilla local';
$string['localuserfield'] = 'Campo de usuario local';
$string['newcoursecategory'] = 'Campo de categoría del nuevo curso';
$string['newcoursecategory_desc'] = 'NOTA: Este campo no se utiliza en el parche de matriculacion basada en plantillas.';
$string['newcoursecategorypath'] = 'Ruta de la categoría de los nuevos cursos';
$string['newcoursecategorypath_desc'] = 'La ruta de la categoría, que se usará para comprobar si la categoría existe teniendo en cuenta las subcategorías si se define un caracter de separación de categorías.';
$string['newcourseenddate'] = 'Campo de fecha de finalización de los nuevos cursos';
$string['newcourseenddate_desc'] = 'Opcional. Si no se define, fecha de finalización de los nuevos cursos se definirán teniendo en cuenta la duración predefinida de los cursos. Si está definida, utilizará ese valor.';
$string['newcoursefullname'] = 'Campo de nombre completo del nuevo curso';
$string['newcourseidnumber'] = 'Campo de número ID del nuevo curso';
$string['newcourseshortname'] = 'Campo de nombre corto del nuevo curso';
$string['newcoursestartdate'] = 'Campo de fecha de inicio de los nuevos cursos';
$string['newcoursestartdate_desc'] = 'Opcional. Si no se define, la fecha de inicio de los nuevos cursos será la fecha del momento en que se creen. Si está definida, utilizará ese valor.';
$string['newcoursetable'] = 'Tabla de nuevos cursos remotos';
$string['newcoursetable_desc'] = 'Se especifica el nombre de la tabla que contiene la lista de cursos que deberían crearse automáticamente. Si está vacía, significa que no está creado ningún curso.';
$string['newcoursetemplate'] = 'Campo de plantilla de nuevos cursos';
$string['newcoursetemplate_desc'] = 'Los cursos creados automáticamente pueden crearse basándose en un curso plantilla. Define el nombre del campo que guarda el identificador de la plantilla de cursos (según lo definido en el ajuste \'Campo de curso local\')';
$string['newcoursesummary'] = 'Campo de resumen de nuevos cursos';
$string['newgroupcourse'] = 'Campo de nombre de nuevos grupos';
$string['newgroupcourse_desc'] = 'El identificador del curso al que pertenece el nuevo grupo (según lo definido en el ajuste \'Campo de curso local\')';
$string['newgroupdesc'] = 'Campo de descripción de nuevos grupos';
$string['newgroupidnumber'] = 'Campo de IDnumber de nuevos grupos';
$string['newgroupgroupings'] = 'Campo de agrupación de nuevos grupos';
$string['newgroupgroupings_desc'] = 'El nombre del campo de la base de datos externa que guarda la información de identificación de agrupaciones.';
$string['newgroupname'] = 'Campo de nombre de nuevos grupos';
$string['newgrouptable'] = 'Tabla remota de nuevos grupos';
$string['newgrouptable_desc'] = 'Especifica el nombre de la tabla que contiene la lista de grupos que deben crearse automáticamente. Si se deja el campo vacío no se crearán nuevos grupos.';
$string['pluginname'] = 'Base de datos externa';
$string['pluginname_desc'] = 'Puede utilizar una base de datos externa (casi de cualquier tipo) para controlar sus matriculaciones. Se asume que su base de datos externa contiene al menos un campo que contiene un ID de curso, y un campo que contiene un ID de usuario. Estos se comparan con los campos que usted elija en el curso local y las tablas de usuario.';
$string['privacy:metadata'] = 'El complemento de matrícula a través de base de datos externa no almacena ningún dato personal.';
$string['remotecoursefield'] = 'Campo curso remoto';
$string['remotecoursefield_desc'] = 'El nombre del campo en la tabla remota que usamos para casar entradas en la tabla del curso.';
$string['remoteenroltable'] = 'Tabla de matriculación remota de usuarios';
$string['remoteenroltable_desc'] = 'Indique el nombre de la tabla que contiene la lista de matrículas de usuario. Si queda vacío significa que no hay sincronización en la matriculación de usuarios.';
$string['remoteotheruserfield'] = 'Campo de otro usuario remoto';
$string['remoteotheruserfield_desc'] = 'El nombre del campo en la tabla remota que estamos utilizando para marcar las asignaciones del rol "Otro usuario".';
$string['remoterolefield'] = 'Campo rol remoto';
$string['remoterolefield_desc'] = 'El nombre del campo en la tabla remota que usamos para casar entradas en la tabla del curso.';
$string['remoteuserfield'] = 'Campo usuario remoto';
$string['remoteuserfield_desc'] = 'El nombre del campo en la tabla remota que usamos para casar entradas en la tabla del curso.';
$string['settingsheaderdb'] = 'Conexión con la base de datos externa';
$string['settingsheadergroupenrol'] = 'Sincronización de asignaciones a grupos';
$string['settingsheaderlocal'] = 'Asignación de campos locales';
$string['settingsheadernewcourses'] = 'Creación de nuevos cursos';
$string['settingsheadernewgroups'] = 'Creación de nuevos grupos';
$string['settingsheaderremote'] = 'Sincronización de matriculación remota';
$string['syncenrolmentstask'] = 'Tarea de sincronización de las  inscripciones por base de datos externa';
$string['templatecourse'] = 'Plantilla para nuevo curso';
$string['templatecourse_desc'] = 'Opcional: Auto-crear cursos puede copiar su configuración de un curso plantilla. Escriba aquí el nombre corto del curso  plantilla.';
$string['userfield'] = 'Campo de nombre de usuario';
$string['userfield_desc'] = 'El nombre del campo de la tabla remota utilizado para guardar el identificador de usuario.';

