
# Group filter XLS file
The '**Group filter XLS file**' plugin allow you to export the grades of a course by filtering by groups, without the need to have set the course in 'Groups' mode. In addition, it allows you to indicate the user profile fields that you want to add in the export from the export page itself.

The group name will also appear on the export sheet, whether it has been previously filtered or not.



## How does 'Group filter XLS file' work?
To export grades using either of the two plugins you must:

1. Access the 'Grades' section within the course.
2. In the drop-down menu, select 'Group filter XLS file'.
3. If desired, filter the group from which you want to export the notes.
4. In the 'User profile fields' section, select which fields you want to add in the export.
5. Click 'Download' to proceed with the export.
## Prerequisites
To allow teachers, or users with the appropriate permissions to export grades, to select the Profile Fields they want to add to the resulting file, the system administrator must make these fields available to them. To do this you must:

1. Access 'Site administration > Grades > General settings' (/admin/settings.php?section=gradessettings).
2. In the fields 'User profile fields when exporting grades' (grade_export_userprofilefields) and 'Custom profile fields when exporting grades' (grade_export_customprofilefields), indicate the short names of the fields susceptible to being selected for export.
3. Click 'Save changes' to confirm.

## Installation
You can download the admin tool plugin from: https://github.com/UNIMOODLE/groupfilter_xls

The grade report should be located and named as:

`[yourmoodledir]/grade/export/groupfilter_xls`
    
## Uninstall

1. Remove the `gradeexport_groupfilter_xls` plugin from the Moodle folder: `[yourmoodledir]/grade/export/groupfilter_xls`
2. Access the plugin uninstall page: `Site Administration > Plugins > Plugins overview`
3. Look for the removed plugin and click on uninstall.

## Authors

Project implemented by the "Recovery, Transformation and Resilience Plan.
Funded by the European Union - Next GenerationEU".

Produced by the UNIMOODLE University Group: Universities of
Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

Display information about all the gradereport_gradeconfigwizard modules in the requested course.

* @package gradeconfigwizard
* @copyright 2023 Proyecto UNIMOODLE {@link https://unimoodle.github.io}
* @author UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author Joan Carbassa (IThinkUPC) <joan.carbassa@ithinkupc.com>
 * @author Yerai Rodríguez (IThinkUPC) <yerai.rodriguez@ithinkupc.com>
 * @author Marc Geremias (IThinkUPC) <marc.geremias@ithinkupc.com>
 * @author Miguel Gutiérrez (UPCnet) <miguel.gutierrez.jariod@upcnet.es>
## License

 This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.

