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
 * Settings used by the muonline course format
 *
 * @package format_muonline
 * @copyright  2019 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings = null; // We add our own settings pages and do not want the standard settings link.

    $settingscategory = new \format_muonline\local\admin_settingspage_tabs(
        'formatsettingmuonline',
        get_string('pluginname', 'format_muonline')
    );

    // Colour settings.
    $page = new admin_settingpage('format_muonline/tab-colours', get_string('colours', 'format_muonline'));

    $page->add(
        new admin_setting_heading('other', get_string('other', 'format_muonline'), '')
    );

    $name = 'format_muonline/followthemecolour';
    $title = get_string('followthemecolour', 'format_muonline');
    $default = 0;
    $description = get_string('followthemecolour_desc', 'format_muonline');
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/subtileiconcolourbackground';
    $title = get_string('subtileiconcolourbackground', 'format_muonline');
    $description = get_string('subtileiconcolourbackground_desc', 'format_muonline');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $brandcolourdefaults = [
        '#1670CC' => get_string('colourblue', 'format_muonline'),
        '#00A9CE' => get_string('colourlightblue', 'format_muonline'),
        '#7A9A01' => get_string('colourgreen', 'format_muonline'),
        '#009681' => get_string('colourdarkgreen', 'format_muonline'),
        '#D13C3C' => get_string('colourred', 'format_muonline'),
        '#772583' => get_string('colourpurple', 'format_muonline'),
    ];
    $colournumber = 1;
    foreach ($brandcolourdefaults as $hex => $displayname) {
        $title = get_string('brandcolour', 'format_muonline') . ' ' . $colournumber;
        if ($colournumber === 1) {
            $title .= " - " . get_string('defaulttilecolour', 'format_muonline');
        }
        $page->add(
            new admin_setting_heading(
                'brand' . $colournumber,
                $title,
                ''
            )
        );
        // Colour picker for this brand.

        if ($colournumber === 1) {
            $visiblename = get_string('defaulttilecolour', 'format_muonline');
        } else {
            $visiblename = get_string('tilecolourgeneral', 'format_muonline') . ' ' . $colournumber;
        }
        $setting = new admin_setting_configcolourpicker(
            'format_muonline/tilecolour' . $colournumber,
            $visiblename,
            '',
            $hex
        );
        $page->add($setting);

        // Display name for this brand.
        $setting = new admin_setting_configtext(
            'format_muonline/colourname' . $colournumber,
            get_string('colournamegeneral', 'format_muonline') . ' ' . $colournumber,
            get_string('colourname_descr', 'format_muonline'),
            $displayname,
            PARAM_RAW,
            30
        );
        $page->add($setting);
        $colournumber++;
    }

    $settingscategory->add($page);

    // Modal activities / resources.
    $page = new admin_settingpage('format_muonline/tab-modalwindows', get_string('modalwindows', 'format_muonline'));
    $cachecallback = function () {
        \cache_helper::purge_by_event('format_muonline/modaladminsettingchanged');
    };

    // Modal windows for course modules.
    $allowedmodtypes = ['page' => 1]; // Number is default to on or off.
    $allmodtypes = get_module_types_names();
    $options = [];
    foreach (array_keys($allowedmodtypes) as $modtype) {
        if (isset($allmodtypes[$modtype])) {
            $options[$modtype] = $allmodtypes[$modtype];
        }
    }
    $name = 'format_muonline/modalmodules';
    $title = get_string('modalmodules', 'format_muonline');
    $description = get_string('modalmodules_desc', 'format_muonline');
    $setting = new admin_setting_configmulticheckbox(
        $name,
        $title,
        $description,
        $allowedmodtypes,
        $options
    );
    $setting->set_updatedcallback($cachecallback);
    $page->add($setting);

    // Modal windows for resources.
    $displayembed = get_string('display', 'form') . ': ' . get_string('resourcedisplayembed');
    $link = html_writer::link(
        "https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options",
        "https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options"
    );
    $allowedresourcetypes = [
        'pdf' => get_string('displaytitle_mod_pdf', 'format_muonline') . " (pdf)",
        'url' => get_string('url') . ' (' . $displayembed . ')',
        'html' => get_string('displaytitle_mod_html', 'format_muonline') . " (HTML " . get_string('file') . ")",
    ];
    $name = 'format_muonline/modalresources';
    $title = get_string('modalresources', 'format_muonline');
    $description = get_string('modalresources_desc', 'format_muonline', ['displayembed' => $displayembed, 'link' => $link]);
    $setting = new admin_setting_configmulticheckbox(
        $name,
        $title,
        $description,
        ['pdf' => 1, 'url' => 1, 'html' => 1],
        $allowedresourcetypes
    );
    $page->add($setting);
    $setting->set_updatedcallback($cachecallback);
    $settingscategory->add($page);

    // Photo tile settings.
    $page = new admin_settingpage('format_muonline/tab-photomuonlineettings', get_string('phototilesettings', 'format_muonline'));

    $name = 'format_muonline/allowphotomuonline';
    $title = get_string('allowphototiles', 'format_muonline');
    $description = get_string('allowphototiles_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $choices = [];
    $stylestr = get_string('style', 'format_muonline');
    for ($i = 1; $i <= 2; $i++) {
        $choices[(string)$i] = $stylestr . ' ' . $i;
    }

    $setting = new admin_setting_configselect(
        'format_muonline/muonlinetyle',
        get_string('tilestyle', 'format_muonline'),
        get_string('tilestyle_desc', 'format_muonline'),
        "1",
        $choices
    );
    $page->add($setting);

    $name = 'format_muonline/showprogresssphotomuonline';
    $title = get_string('courseshowtileprogress', 'format_muonline');
    $description = get_string('showprogresssphototiles_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    // Tile title CSS adjustments.
    $page->add(
        new admin_setting_heading(
            'transparenttitleadjustments',
            get_string('transparenttitleadjustments', 'format_muonline'),
            get_string('transparenttitleadjustments_desc', 'format_muonline')
        )
    );

    $opacities = [0.3, 0.2, 0.1, 0];
    $choices = [];
    foreach ($opacities as $op) {
        $choices[(string)$op] = (string)($op * 100) . "%";
    }
    $setting = new admin_setting_configselect(
        'format_muonline/phototiletitletransarency',
        get_string('phototiletitletransarency', 'format_muonline'),
        get_string('phototiletitletransarency_desc', 'format_muonline'),
        "0",
        $choices
    );
    $page->add($setting);

    // Tile title line height.
    $choices = [];
    for ($x = 30.0; $x <= 33.0; $x += 0.1) {
        $choices[(int)($x * 10)] = $x;
    }
    $setting = new admin_setting_configselect(
        'format_muonline/phototitletitlelineheight',
        get_string('phototitletitlelineheight', 'format_muonline'),
        '',
        305,
        $choices
    );
    $page->add($setting);

    // Tile title line line padding.
    $choices = [];
    for ($x = 0.0; $x <= 6.0; $x += 0.5) {
        $choices[(int)($x * 10)] = $x;
    }
    $setting = new admin_setting_configselect(
        'format_muonline/phototitletitlepadding',
        get_string('phototitletitlepadding', 'format_muonline'),
        '',
        40,
        $choices
    );
    $page->add($setting);
    $settingscategory->add($page);

    // Javascript navigation settings.
    $page = new admin_settingpage('format_muonline/tab-jsnav', get_string('jsactivate', 'format_muonline'));

    $name = 'format_muonline/usejavascriptnav';
    $title = get_string('usejavascriptnav', 'format_muonline');
    $description = get_string('usejavascriptnav_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/reopenlastsection';
    $title = get_string('reopenlastsection', 'format_muonline');
    $description = get_string('reopenlastsection_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/usejsnavforsinglesection';
    $title = get_string('usejsnavforsinglesection', 'format_muonline');
    $description = get_string('usejsnavforsinglesection_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/fitmuonlinetowidth';
    $title = get_string('fittilestowidth', 'format_muonline');
    $description = get_string('fittilestowidth_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $settingscategory->add($page);

    // Other settings.
    $page = new admin_settingpage('format_muonline/tab-other', get_string('other', 'format_muonline'));

    $page->add(
        new admin_setting_heading(
            'problemcourses',
            get_string('problemcourses', 'format_muonline'),
            html_writer::link(
                \format_muonline\local\course_section_manager::get_list_problem_courses_url(),
                get_string('checkforproblemcourses', 'format_muonline'),
                ['class' => 'btn btn-primary', 'target' => '_blank']
            )
        )
    );

    $page->add(
        new admin_setting_heading(
            'other',
            get_string('other', 'format_muonline'),
            ''
        )
    );


    $name = 'format_muonline/allowsubmuonlineview';
    $title = get_string('allowsubtilesview', 'format_muonline');
    $description = get_string('allowsubtilesiew_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/showoverallprogress';
    $title = get_string('showoverallprogress', 'format_muonline');
    $description = get_string('showoverallprogress_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/progressincludesubsections';
    $title = get_string('progressincludesubsections', 'format_muonline');
    $description = get_string('progressincludesubsections_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/showseczerocoursewide';
    $title = get_string('showseczerocoursewide', 'format_muonline');
    $description = get_string('showseczerocoursewide_desc', 'format_muonline');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/seczerocollapsible';
    $title = get_string('seczerocollapsible', 'format_muonline');
    $description = get_string('seczerocollapsible_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    // Custom css.
    $name = 'format_muonline/customcss';
    $title = get_string('customcss', 'format_muonline');
    $description = get_string('customcssdesc', 'format_muonline');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $page->add($setting);

    $name = 'format_muonline/enablelinebreakfilter';
    $title = get_string('enablelinebreakfilter', 'format_muonline');
    $description = get_string('enablelinebreakfilter_desc', 'format_muonline', '<code>&amp;#8288;</code>');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/assumedatastoreconsent';
    $title = get_string('assumedatastoreconsent', 'format_muonline');
    $description = get_string('assumedatastoreconsent_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $name = 'format_muonline/usecourseindex';
    $title = get_string('usecourseindex', 'format_muonline');
    $description = get_string('usecourseindex_desc', 'format_muonline');
    $default = 1;
    $page->add(new admin_setting_configcheckbox($name, $title, $description, $default));

    $page->add(new admin_setting_heading(
        'experimentalfeatures',
        get_string('experimentalfeatures', 'format_muonline'),
        ''
    ));
    $name = 'format_muonline/highcontrastmodeallow';
    $title = get_string('highcontrastmodeallow', 'format_muonline');
    $default = 0;
    $page->add(new admin_setting_configcheckbox($name, $title, get_string('highcontrastmodeallow_desc', 'format_muonline'), $default));

    $settingscategory->add($page);

    $ADMIN->add('formatsettings', $settingscategory);
}
