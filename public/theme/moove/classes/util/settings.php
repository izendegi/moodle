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
 * Theme helper to load a theme configuration.
 *
 * @package    theme_moove
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_moove\util;

use theme_config;

/**
 * Helper to load a theme configuration.
 *
 * @package    theme_moove
 * @copyright  2017 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings {
    /**
     * @var \stdClass $theme The theme object.
     */
    protected $theme;
    /**
     * @var array $files Theme file settings.
     */
    protected $files = [
        'logo', 'logodark', 'loginbg',
        'sliderimage1', 'sliderimage2', 'sliderimage3', 'sliderimage4', 'sliderimage5', 'sliderimage6',
        'sliderimage7', 'sliderimage8', 'sliderimage9', 'sliderimage10', 'sliderimage11', 'sliderimage12',
        'marketing1icon', 'marketing2icon', 'marketing3icon', 'marketing4icon',
        'numbersbackgroundimage',
        'logo1image', 'logo2image', 'logo3image', 'logo4image', 'logo5image', 'logo6image',
        'logo7image', 'logo8image', 'logo9image', 'logo10image', 'logo11image', 'logo12image',
    ];

    /**
     * Class constructor
     */
    public function __construct() {
        $this->theme = theme_config::load('moove');
    }

    /**
     * Magic method to get theme settings
     *
     * @param string $name
     *
     * @return false|string|null
     */
    public function __get(string $name) {
        if (in_array($name, $this->files)) {
            return $this->theme->setting_file_url($name, $name);
        }

        if (empty($this->theme->settings->$name)) {
            return false;
        }

        return $this->theme->settings->$name;
    }

    /**
     * Get footer settings
     *
     * @return array
     */
    public function footer() {
        global $CFG, $SITE;

        $templatecontext = [];

        $templatecontext['footerlayout'] = $this->footerlayout ?: 'default';
        $templatecontext['footerlayoutsimple'] = $templatecontext['footerlayout'] === 'simple';

        $templatecontext['footernavlinks'] = [];

        $navlinks = $this->footernavlinks;

        if (!empty($navlinks)) {
            $lines = explode("\n", $navlinks);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                [$label, $url] = array_pad(explode('|', $line, 2), 2, '');

                $label = trim($label);
                $url = trim($url);

                if ($label === '' || $url === '') {
                    continue;
                }

                $templatecontext['footernavlinks'][] = [
                    'label' => $label,
                    'url' => $url,
                ];
            }
        }

        $templatecontext['hasfooternavlinks'] = !empty($templatecontext['footernavlinks']);

        $templatecontext['footercopyright'] = get_string('footercopyright', 'theme_moove', (object) [
            'year' => userdate(time(), '%Y'),
            'sitename' => format_string($SITE->fullname, true, ['context' => \core\context\system::instance()]),
        ]);

        $settings = [
            'facebook', 'twitter', 'linkedin', 'youtube', 'instagram', 'whatsapp', 'telegram', 'tiktok', 'pinterest',
            'website', 'mobile', 'mail', 'disableorangefooter',
        ];

        $templatecontext['hasfootercontact'] = false;
        $templatecontext['hasfootersocial'] = false;
        foreach ($settings as $setting) {
            $templatecontext[$setting] = $this->$setting;

            if (in_array($setting, ['website', 'mobile', 'mail']) && !empty($templatecontext[$setting])) {
                $templatecontext['hasfootercontact'] = true;
            }

            $socialsettings = [
                'facebook', 'twitter', 'linkedin', 'youtube', 'instagram', 'whatsapp', 'telegram', 'tiktok', 'pinterest'
            ];

            if (in_array($setting, $socialsettings) && !empty($templatecontext[$setting])) {
                $templatecontext['hasfootersocial'] = true;
            }
        }

        $templatecontext['showwhatsappbutton'] = false;
        if ($this->enablewhatsappbutton && !empty($templatecontext['whatsapp'])) {
            $whatsappurl = new \moodle_url('https://api.whatsapp.com/send', ['phone' => $templatecontext['whatsapp']]);

            $whatsappbuttonmessage = $this->whatsappbuttonmessage;
            if (!empty($whatsappbuttonmessage)) {
                $whatsappurl->param('text', $whatsappbuttonmessage);
            }

            $templatecontext['showwhatsappbutton'] = true;
            $templatecontext['whatsappbuttonurl'] = $whatsappurl->out(false);
        }

        $templatecontext['enablemobilewebservice'] = $CFG->enablemobilewebservice;

        if ($CFG->enablemobilewebservice) {
            $iosappid = get_config('tool_mobile', 'iosappid');
            if (!empty($iosappid)) {
                $templatecontext['iosappid'] = $iosappid;
            }

            $androidappid = get_config('tool_mobile', 'androidappid');
            if (!empty($androidappid)) {
                $templatecontext['androidappid'] = $androidappid;
            }

            $setuplink = get_config('tool_mobile', 'setuplink');
            if (!empty($setuplink)) {
                $templatecontext['mobilesetuplink'] = $setuplink;
            }
        }

        $license = new license();

        if (!$license->is_active()) {
            $templatecontext['disableorangefooter'] = false;
        }

        return $templatecontext;
    }

    /**
     * Get frontpage settings
     *
     * @return array
     */
    public function frontpage() {
        return array_merge(
            $this->frontpage_slideshow(),
            $this->logos(),
            $this->frontpage_marketingboxes(),
            $this->frontpage_numbers(),
            $this->faq()
        );
    }

    /**
     * Get config theme slideshow
     *
     * @return array
     */
    public function frontpage_slideshow() {
        $templatecontext['slidercount'] = $this->slidercount;
        $templatecontext['slideshowlayout'] = $this->slideshowlayout ?: 'classic';
        $templatecontext['slideshowcolorblockcolor1'] = $this->slideshowcolorblockcolor1 ?: '#0f47ad';
        $templatecontext['slideshowcolorblockcolor2'] = $this->slideshowcolorblockcolor2 ?: '#17a2b8';

        $defaultimage = new \moodle_url('/theme/moove/pix/default_slide.jpg');
        for ($i = 1, $j = 0; $i <= $templatecontext['slidercount']; $i++, $j++) {
            $sliderimage = "sliderimage{$i}";
            $slidertitle = "slidertitle{$i}";
            $slidercap = "slidercap{$i}";
            $slidercapcontent = $this->$slidercap ?: null;

            $slidetitle = format_string($this->$slidertitle) ?: null;
            $slidecontent = format_text($slidercapcontent, FORMAT_MOODLE, ['noclean' => false]) ?: null;
            $image = $this->$sliderimage;

            $hascaption = isset($slidetitle) || isset($slidecontent);

            $templatecontext['slides'][$j]['key'] = $j;
            $templatecontext['slides'][$j]['active'] = $i === 1;
            $templatecontext['slides'][$j]['image'] = $image ?: $defaultimage->out();
            $templatecontext['slides'][$j]['title'] = $slidetitle;
            $templatecontext['slides'][$j]['caption'] = $slidecontent;
            $templatecontext['slides'][$j]['hascaption'] = $hascaption;
        }

        $templatecontext['slidersingleslide'] = $this->slidercount == 1;

        return $templatecontext;
    }

    /**
     * Get frontpage logos (clients/supporters) section settings
     *
     * @return array
     */
    public function logos() {
        $templatecontext['logosenabled'] = false;
        $templatecontext['logoslayout'] = $this->logoslayout ?: 'simple';

        if ($this->logoscount) {
            $templatecontext['logostitle'] = $this->logostitle ?: '';
            $templatecontext['logossubtitle'] = $this->logossubtitle ?: '';

            for ($i = 1; $i <= $this->logoscount; $i++) {
                $logoimage = 'logo' . $i . 'image';
                $logourl = 'logo' . $i . 'url';

                if (!$this->$logoimage) {
                    continue;
                }

                $templatecontext['logos'][] = [
                    'image' => $this->$logoimage,
                    'url' => $this->$logourl ?: '',
                    'hasurl' => (bool) $this->$logourl,
                ];
            }

            if (!empty($templatecontext['logos'])) {
                $templatecontext['logosenabled'] = true;
            }
        }

        return $templatecontext;
    }

    /**
     * Get config theme slideshow
     *
     * @return array
     */
    public function frontpage_marketingboxes() {
        if ($templatecontext['displaymarketingbox'] = $this->displaymarketingbox) {
            $templatecontext['featuresectionlayout'] = $this->featuresectionlayout ?: 'default';
            $templatecontext['marketingheading'] = format_text($this->marketingheading, FORMAT_HTML);
            $templatecontext['marketingcontent'] = format_text($this->marketingcontent, FORMAT_HTML);

            $defaulticons = [
                new \moodle_url('/theme/moove/pix/feature-target.svg'),
                new \moodle_url('/theme/moove/pix/feature-bulb.svg'),
                new \moodle_url('/theme/moove/pix/feature-growth.svg'),
                new \moodle_url('/theme/moove/pix/feature-shield.svg'),
            ];

            for ($i = 1, $j = 0; $i < 5; $i++, $j++) {
                $marketingicon = 'marketing' . $i . 'icon';
                $marketingheading = 'marketing' . $i . 'heading';
                $marketingcontent = 'marketing' . $i . 'content';

                $defaulticon = $defaulticons[$j % count($defaulticons)];

                $templatecontext['marketingboxes'][$j]['icon'] = $this->$marketingicon ?: $defaulticon->out();
                $templatecontext['marketingboxes'][$j]['heading'] = $this->$marketingheading ?
                    format_text($this->$marketingheading, FORMAT_HTML) : 'Lorem';
                $templatecontext['marketingboxes'][$j]['content'] = $this->$marketingcontent ?
                    format_text($this->$marketingcontent, FORMAT_HTML) :
                    'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.';
                $templatecontext['marketingboxes'][$j]['offset'] = (bool)($j % 2);
            }
        }

        return $templatecontext;
    }

    /**
     * Get config theme slideshow
     *
     * @return array
     */
    public function frontpage_numbers() {
        global $DB;

        if ($templatecontext['numbersfrontpage'] = $this->numbersfrontpage) {
            $templatecontext['numberslayout'] = $this->numberslayout ?: 'default';
            $templatecontext['numberscontent'] = $this->numbersfrontpagecontent ? format_text($this->numbersfrontpagecontent) : '';
            $templatecontext['numbersusers'] = $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]) - 1;
            $templatecontext['numberscourses'] = $DB->count_records('course', ['visible' => 1]) - 1;

            $defaultbackgroundimage = new \moodle_url('/theme/moove/pix/default_numbers_bg.svg');

            $templatecontext['numbersbackgroundimage'] = $this->numbersbackgroundimage ?: $defaultbackgroundimage->out();
        }

        return $templatecontext;
    }

    /**
     * Get config theme slideshow
     *
     * @return array
     */
    public function faq() {
        $templatecontext['faqenabled'] = false;
        $templatecontext['faqlayout'] = $this->faqlayout ?: 'default';

        if ($this->faqcount) {
            for ($i = 1; $i <= $this->faqcount; $i++) {
                $faqquestion = 'faqquestion' . $i;
                $faqanswer = 'faqanswer' . $i;

                if (!$this->$faqquestion || !$this->$faqanswer) {
                    continue;
                }

                $templatecontext['faq'][] = [
                    'id' => $i,
                    'question' => format_text($this->$faqquestion),
                    'answer' => format_text($this->$faqanswer),
                    'active' => $i === 1,
                ];
            }

            if (!empty($templatecontext['faq'])) {
                $templatecontext['faqenabled'] = true;

                $half = (int) ceil(count($templatecontext['faq']) / 2);
                $templatecontext['faqcolumn1'] = array_slice($templatecontext['faq'], 0, $half);
                $templatecontext['faqcolumn2'] = array_slice($templatecontext['faq'], $half);
            }
        }

        return $templatecontext;
    }
}
