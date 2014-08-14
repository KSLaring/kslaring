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
 * This is built using the bootstrapbase template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 * @package     theme_essential
 * @copyright   2013 Julian Ridden
 * @copyright   2014 Gareth J Barnard, David Bezemer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
$settings = null;

defined('MOODLE_INTERNAL') || die;

    $ADMIN->add('themes', new admin_category('theme_essential', 'Essential'));

    /* Generic Settings */
    $temp = new admin_settingpage('theme_essential_generic',  get_string('genericsettings', 'theme_essential'));
    
    // Default Site icon setting.
    $name = 'theme_essential/siteicon';
    $title = get_string('siteicon', 'theme_essential');
    $description = get_string('siteicondesc', 'theme_essential');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);

    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_essential/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_essential');
    $description = get_string('bootstrapcdndesc', 'theme_essential');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Logo file setting.
    $name = 'theme_essential/logo';
    $title = get_string('logo', 'theme_essential');
    $description = get_string('logodesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Font Selector.
    $name = 'theme_essential/fontselect';
    $title = get_string('fontselect' , 'theme_essential');
    $description = get_string('fontselectdesc', 'theme_essential');
    $default = '1';
    $choices = array(
        '1'=>'Open Sans', 
        '2'=>'Oswald & PT Sans', 
        '3'=>'Roboto', 
        '4'=>'PT Sans', 
        '5'=>'Ubuntu',
        '6'=>'Arimo',
        '7'=>'Lobster & Raleway',
        '8'=>'Arial',
        '9'=>'Georgia',
        '10'=>'Verdana',
        '11'=>'Times New Roman',
        '12'=>'Consolas', 
        );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // User picture in header setting.
    $name = 'theme_essential/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_essential');
    $description = get_string('headerprofilepicdesc', 'theme_essential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Fixed or Variable Width.
    $name = 'theme_essential/pagewidth';
    $title = get_string('pagewidth', 'theme_essential');
    $description = get_string('pagewidthdesc', 'theme_essential');
    $default = 1200;
    $choices = array(1200=>get_string('fixedwidthnarrow','theme_essential'),1600=>get_string('fixedwidthwide','theme_essential'), 100=>get_string('variablewidth','theme_essential'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_essential/layout';
    $title = get_string('layout', 'theme_essential');
    $description = get_string('layoutdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // New or old navbar.
    $name = 'theme_essential/oldnavbar';
    $title = get_string('oldnavbar', 'theme_essential');
    $description = get_string('oldnavbardesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Use max width for side regions.
    $name = 'theme_essential/sideregionsmaxwidth';
    $title = get_string('sideregionsmaxwidth', 'theme_essential');
    $description = get_string('sideregionsmaxwidthdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_essential/perfinfo';
    $title = get_string('perfinfo' , 'theme_essential');
    $description = get_string('perfinfodesc', 'theme_essential');
    $perf_max = get_string('perf_max', 'theme_essential');
    $perf_min = get_string('perf_min', 'theme_essential');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Choose breadcrumbstyle
    $name = 'theme_essential/breadcrumbstyle';
    $title = get_string('breadcrumbstyle' , 'theme_essential');
    $description = get_string('breadcrumbstyledesc', 'theme_essential');
    $default = '1';
    $choices = array(1 => get_string('breadcrumbstyled', 'theme_essential'),
                     2 => get_string('breadcrumbsimple', 'theme_essential'),
                     3 => get_string('breadcrumbthin', 'theme_essential'),
                     0 => get_string('nobreadcrumb', 'theme_essential')
                    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_essential/copyright';
    $title = get_string('copyright', 'theme_essential');
    $description = get_string('copyrightdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_essential/footnote';
    $title = get_string('footnote', 'theme_essential');
    $description = get_string('footnotedesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_essential/customcss';
    $title = get_string('customcss', 'theme_essential');
    $description = get_string('customcssdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $readme = new moodle_url('/theme/essential/Readme.txt');
    $readme = html_writer::link($readme, 'Readme.txt', array('target' => '_blank'));

    $temp->add(new admin_setting_heading('theme_essential_generalreadme', get_string('readme_title', 'theme_essential'),
            get_string('readme_desc', 'theme_essential', array('url' => $readme))));

    $ADMIN->add('theme_essential', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_essential_custommenu', get_string('custommenuheading', 'theme_essential'));
                
    // This is the descriptor for the following Moodle color settings
    $name = 'theme_essential/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_essential');
    $information = get_string('mydashboardinfodesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_essential/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_essential');
    $description = get_string('displaymydashboarddesc', 'theme_essential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // This is the descriptor for the following Moodle color settings
    $name = 'theme_essential/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_essential');
    $information = get_string('mycoursesinfodesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_essential/displaymycourses';
    $title = get_string('displaymycourses', 'theme_essential');
    $description = get_string('displaymycoursesdesc', 'theme_essential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
    $name = 'theme_essential/mycoursetitle';
    $title = get_string('mycoursetitle','theme_essential');
    $description = get_string('mycoursetitledesc', 'theme_essential');
    $default = 'course';
    $choices = array(
        'course' => get_string('mycourses', 'theme_essential'),
        'unit' => get_string('myunits', 'theme_essential'),
        'class' => get_string('myclasses', 'theme_essential'),
        'module' => get_string('mymodules', 'theme_essential')
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_essential', $temp);
    
    /* Colour Settings */
    $temp = new admin_settingpage('theme_essential_color', get_string('colorheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_color', get_string('colorheadingsub', 'theme_essential'),
            format_text(get_string('colordesc' , 'theme_essential'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_essential/pagebackground';
    $title = get_string('pagebackground', 'theme_essential');
    $description = get_string('pagebackgrounddesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_essential/themecolor';
    $title = get_string('themecolor', 'theme_essential');
    $description = get_string('themecolordesc', 'theme_essential');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme text colour setting.
    $name = 'theme_essential/themetextcolor';
    $title = get_string('themetextcolor', 'theme_essential');
    $description = get_string('themetextcolordesc', 'theme_essential');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme link colour setting.
    $name = 'theme_essential/themeurlcolor';
    $title = get_string('themeurlcolor', 'theme_essential');
    $description = get_string('themeurlcolordesc', 'theme_essential');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_essential/themehovercolor';
    $title = get_string('themehovercolor', 'theme_essential');
    $description = get_string('themehovercolordesc', 'theme_essential');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Navigation colour setting.
    $name = 'theme_essential/themenavcolor';
    $title = get_string('themenavcolor', 'theme_essential');
    $description = get_string('themenavcolordesc', 'theme_essential');
    $default = '#ffffff';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // This is the descriptor for the Footer
    $name = 'theme_essential/footercolorinfo';
    $heading = get_string('footercolors', 'theme_essential');
    $information = get_string('footercolorsdesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_essential/footercolor';
    $title = get_string('footercolor', 'theme_essential');
    $description = get_string('footercolordesc', 'theme_essential');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_essential/footertextcolor';
    $title = get_string('footertextcolor', 'theme_essential');
    $description = get_string('footertextcolordesc', 'theme_essential');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_essential/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_essential');
    $description = get_string('footerheadingcolordesc', 'theme_essential');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_essential/footersepcolor';
    $title = get_string('footersepcolor', 'theme_essential');
    $description = get_string('footersepcolordesc', 'theme_essential');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_essential/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_essential');
    $description = get_string('footerurlcolordesc', 'theme_essential');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_essential/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_essential');
    $description = get_string('footerhovercolordesc', 'theme_essential');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // This is the descriptor for the user theme colors.
    $name = 'theme_essential/alternativethemecolorsinfo';
    $heading = get_string('alternativethemecolors', 'theme_essential');
    $information = get_string('alternativethemecolorsdesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    $defaultalternativethemecolors = array('#a430d1', '#d15430', '#5dd130');
    $defaultalternativethemehovercolors = array('#9929c4', '#c44c29', '#53c429');

    foreach (range(1, 3) as $alternativethemenumber) {

        // Enables the user to select an alternative colours choice.
        $name = 'theme_essential/enablealternativethemecolors' . $alternativethemenumber;
        $title = get_string('enablealternativethemecolors', 'theme_essential', $alternativethemenumber);
        $description = get_string('enablealternativethemecolorsdesc', 'theme_essential', $alternativethemenumber);
        $default = false;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
        
        // User theme colour name.
        $name = 'theme_essential/alternativethemename' . $alternativethemenumber;
        $title = get_string('alternativethemename', 'theme_essential', $alternativethemenumber);
        $description = get_string('alternativethemenamedesc', 'theme_essential', $alternativethemenumber);
        $default = get_string('alternativecolors', 'theme_essential', $alternativethemenumber);
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
  
        // User theme colour setting.
        $name = 'theme_essential/alternativethemecolor' . $alternativethemenumber;
        $title = get_string('alternativethemecolor', 'theme_essential', $alternativethemenumber);
        $description = get_string('alternativethemecolordesc', 'theme_essential', $alternativethemenumber);
        $default = $defaultalternativethemecolors[$alternativethemenumber - 1];
        $previewconfig = null;
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Alternative theme text colour setting.
        $name = 'theme_essential/alternativethemetextcolor' . $alternativethemenumber;
        $title = get_string('alternativethemetextcolor', 'theme_essential', $alternativethemenumber);
        $description = get_string('alternativethemetextcolordesc', 'theme_essential', $alternativethemenumber);
        $default = $defaultalternativethemecolors[$alternativethemenumber - 1];
        $previewconfig = null;
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Alternative theme link colour setting.
        $name = 'theme_essential/alternativethemeurlcolor' . $alternativethemenumber;
        $title = get_string('alternativethemehovercolor', 'theme_essential', $alternativethemenumber);
        $description = get_string('alternativethemehovercolordesc', 'theme_essential', $alternativethemenumber);
        $default = $defaultalternativethemecolors[$alternativethemenumber - 1];
        $previewconfig = null;
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // User theme hover colour setting.
        $name = 'theme_essential/alternativethemehovercolor' . $alternativethemenumber;
        $title = get_string('alternativethemehovercolor', 'theme_essential', $alternativethemenumber);
        $description = get_string('alternativethemehovercolordesc', 'theme_essential', $alternativethemenumber);
        $default = $defaultalternativethemehovercolors[$alternativethemenumber - 1];
        $previewconfig = null;
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
    }

    $ADMIN->add('theme_essential', $temp);
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_essential_slideshow', get_string('slideshowheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_slideshow', get_string('slideshowheadingsub', 'theme_essential'),
            format_text(get_string('slideshowdesc' , 'theme_essential'), FORMAT_MARKDOWN)));

    // Toggle Slideshow.
    $name = 'theme_essential/toggleslideshow';
    $title = get_string('toggleslideshow' , 'theme_essential');
    $description = get_string('toggleslideshowdesc', 'theme_essential');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_essential');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_essential');
    $displayafterlogin = get_string('displayafterlogin', 'theme_essential');
    $dontdisplay = get_string('dontdisplay', 'theme_essential');
    $default = '1';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Number of slides.
    $name = 'theme_essential/numberofslides';
    $title = get_string('numberofslides', 'theme_essential');
    $description = get_string('numberofslides_desc', 'theme_essential');
    $default = 4;
    $choices = array(
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
        6 => '6',
        7 => '7',
        8 => '8',
        9 => '9',
        10 => '10',
        11 => '11',
        12 => '12',
        13 => '13',
        14 => '14',
        15 => '15',
        16 => '16'
    );
    $temp->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Hide slideshow on phones.
    $name = 'theme_essential/hideontablet';
    $title = get_string('hideontablet' , 'theme_essential');
    $description = get_string('hideontabletdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on tablet.
    $name = 'theme_essential/hideonphone';
    $title = get_string('hideonphone' , 'theme_essential');
    $description = get_string('hideonphonedesc', 'theme_essential');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Slide interval.
    $name = 'theme_essential/slideinterval';
    $title = get_string('slideinterval', 'theme_essential');
    $description = get_string('slideintervaldesc', 'theme_essential');
    $default = '5000';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Slide Text colour setting.
    $name = 'theme_essential/slidecolor';
    $title = get_string('slidecolor', 'theme_essential');
    $description = get_string('slidecolordesc', 'theme_essential');
    $default = '#ffffff';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Show caption below the image.
    $name = 'theme_essential/slidecaptionbelow';
    $title = get_string('slidecaptionbelow' , 'theme_essential');
    $description = get_string('slidecaptionbelowdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Slide button colour setting.
    $name = 'theme_essential/slidebuttoncolor';
    $title = get_string('slidebuttoncolor', 'theme_essential');
    $description = get_string('slidebuttoncolordesc', 'theme_essential');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Slide button hover colour setting.
    $name = 'theme_essential/slidebuttonhovercolor';
    $title = get_string('slidebuttonhovercolor', 'theme_essential');
    $description = get_string('slidebuttonhovercolordesc', 'theme_essential');
    $default = '#45b5d6';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $numberofslides = get_config('theme_essential', 'numberofslides');
    for ($i = 1; $i <= $numberofslides; $i++) {
        // This is the descriptor for Slide One
        $name = 'theme_essential/slide'.$i.'info';
        $heading = get_string('slideno', 'theme_essential', array('slide' => $i));
        $information = get_string('slidenodesc', 'theme_essential', array('slide' => $i));
        $setting = new admin_setting_heading($name, $heading, $information);
        $temp->add($setting);

        // Title.
        $name = 'theme_essential/slide'.$i;
        $title = get_string('slidetitle', 'theme_essential');
        $description = get_string('slidetitledesc', 'theme_essential');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Image.
        $name = 'theme_essential/slide'.$i.'image';
        $title = get_string('slideimage', 'theme_essential');
        $description = get_string('slideimagedesc', 'theme_essential');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide'.$i.'image');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // Caption text.
        $name = 'theme_essential/slide'.$i.'caption';
        $title = get_string('slidecaption', 'theme_essential');
        $description = get_string('slidecaptiondesc', 'theme_essential');
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_TEXT);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // URL.
        $name = 'theme_essential/slide'.$i.'url';
        $title = get_string('slideurl', 'theme_essential');
        $description = get_string('slideurldesc', 'theme_essential');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);

        // URL target.
        $name = 'theme_essential/slide'.$i.'target';
        $title = get_string('slideurltarget' , 'theme_essential');
        $description = get_string('slideurltargetdesc', 'theme_essential');
        $target1 = get_string('slideurltargetself', 'theme_essential');
        $target2 = get_string('slideurltargetnew', 'theme_essential');
        $target3 = get_string('slideurltargetparent', 'theme_essential');
        $default = 'target1';
        $choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
    }
    $ADMIN->add('theme_essential', $temp);
    
    $temp = new admin_settingpage('theme_essential_frontcontent', get_string('frontcontentheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_frontcontent', get_string('frontcontentheadingsub', 'theme_essential'),
            format_text(get_string('frontcontentdesc' , 'theme_essential'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_essential/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_essential');
    $description = get_string('usefrontcontentdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_essential/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_essential');
    $description = get_string('frontcontentareadesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Block alignment.
    $name = 'theme_essential/frontpageblocks';
    $title = get_string('frontpageblocks' , 'theme_essential');
    $description = get_string('frontpageblocksdesc', 'theme_essential');
    $left = get_string('left', 'theme_essential');
    $right = get_string('right', 'theme_essential');
    $default = 'left';
    $choices = array('1'=>$left, '0'=>$right);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Toggle Frontpage Middle Blocks
    $name = 'theme_essential/frontpagemiddleblocks';
    $title = get_string('frontpagemiddleblocks' , 'theme_essential');
    $description = get_string('frontpagemiddleblocksdesc', 'theme_essential');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_essential');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_essential');
    $displayafterlogin = get_string('displayafterlogin', 'theme_essential');
    $dontdisplay = get_string('dontdisplay', 'theme_essential');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_essential', $temp);
    

    /* Marketing Spot Settings */
    $temp = new admin_settingpage('theme_essential_marketing', get_string('marketingheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_marketing', get_string('marketingheadingsub', 'theme_essential'),
            format_text(get_string('marketingdesc' , 'theme_essential'), FORMAT_MARKDOWN)));
    
    // Toggle Marketing Spots.
    $name = 'theme_essential/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_essential');
    $description = get_string('togglemarketingdesc', 'theme_essential');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_essential');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_essential');
    $displayafterlogin = get_string('displayafterlogin', 'theme_essential');
    $dontdisplay = get_string('dontdisplay', 'theme_essential');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
    $name = 'theme_essential/marketingheight';
    $title = get_string('marketingheight','theme_essential');
    $description = get_string('marketingheightdesc', 'theme_essential');
    $default = 100;
    $choices = array(50, 100, 150, 200, 250, 300);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $temp->add($setting);
    
    // This is the descriptor for Marketing Spot One
    $name = 'theme_essential/marketing1info';
    $heading = get_string('marketing1', 'theme_essential');
    $information = get_string('marketinginfodesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Marketing Spot One
    $name = 'theme_essential/marketing1';
    $title = get_string('marketingtitle', 'theme_essential');
    $description = get_string('marketingtitledesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing1icon';
    $title = get_string('marketingicon', 'theme_essential');
    $description = get_string('marketingicondesc', 'theme_essential');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing1image';
    $title = get_string('marketingimage', 'theme_essential');
    $description = get_string('marketingimagedesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing1content';
    $title = get_string('marketingcontent', 'theme_essential');
    $description = get_string('marketingcontentdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_essential');
    $description = get_string('marketingbuttontextdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_essential');
    $description = get_string('marketingbuttonurldesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing1target';
    $title = get_string('marketingurltarget' , 'theme_essential');
    $description = get_string('marketingurltargetdesc', 'theme_essential');
    $target1 = get_string('marketingurltargetself', 'theme_essential');
    $target2 = get_string('marketingurltargetnew', 'theme_essential');
    $target3 = get_string('marketingurltargetparent', 'theme_essential');
    $default = 'target1';
    $choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // This is the descriptor for Marketing Spot Two
    $name = 'theme_essential/marketing2info';
    $heading = get_string('marketing2', 'theme_essential');
    $information = get_string('marketinginfodesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Marketing Spot Two.
    $name = 'theme_essential/marketing2';
    $title = get_string('marketingtitle', 'theme_essential');
    $description = get_string('marketingtitledesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing2icon';
    $title = get_string('marketingicon', 'theme_essential');
    $description = get_string('marketingicondesc', 'theme_essential');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing2image';
    $title = get_string('marketingimage', 'theme_essential');
    $description = get_string('marketingimagedesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing2content';
    $title = get_string('marketingcontent', 'theme_essential');
    $description = get_string('marketingcontentdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_essential');
    $description = get_string('marketingbuttontextdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_essential');
    $description = get_string('marketingbuttonurldesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing2target';
    $title = get_string('marketingurltarget' , 'theme_essential');
    $description = get_string('marketingurltargetdesc', 'theme_essential');
    $target1 = get_string('marketingurltargetself', 'theme_essential');
    $target2 = get_string('marketingurltargetnew', 'theme_essential');
    $target3 = get_string('marketingurltargetparent', 'theme_essential');
    $default = 'target1';
    $choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // This is the descriptor for Marketing Spot Three
    $name = 'theme_essential/marketing3info';
    $heading = get_string('marketing3', 'theme_essential');
    $information = get_string('marketinginfodesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Marketing Spot Three.
    $name = 'theme_essential/marketing3';
    $title = get_string('marketingtitle', 'theme_essential');
    $description = get_string('marketingtitledesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing3icon';
    $title = get_string('marketingicon', 'theme_essential');
    $description = get_string('marketingicondesc', 'theme_essential');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing3image';
    $title = get_string('marketingimage', 'theme_essential');
    $description = get_string('marketingimagedesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing3content';
    $title = get_string('marketingcontent', 'theme_essential');
    $description = get_string('marketingcontentdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_essential');
    $description = get_string('marketingbuttontextdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_essential');
    $description = get_string('marketingbuttonurldesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_essential/marketing3target';
    $title = get_string('marketingurltarget' , 'theme_essential');
    $description = get_string('marketingurltargetdesc', 'theme_essential');
    $target1 = get_string('marketingurltargetself', 'theme_essential');
    $target2 = get_string('marketingurltargetnew', 'theme_essential');
    $target3 = get_string('marketingurltargetparent', 'theme_essential');
    $default = 'target1';
    $choices = array('_self'=>$target1, '_blank'=>$target2, '_parent'=>$target3);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_essential', $temp);

    
    /* Social Network Settings */
    $temp = new admin_settingpage('theme_essential_social', get_string('socialheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_social', get_string('socialheadingsub', 'theme_essential'),
            format_text(get_string('socialdesc' , 'theme_essential'), FORMAT_MARKDOWN)));
    
    // Website url setting.
    $name = 'theme_essential/website';
    $title = get_string('website', 'theme_essential');
    $description = get_string('websitedesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_essential/facebook';
    $title = get_string(        'facebook', 'theme_essential');
    $description = get_string(      'facebookdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_essential/flickr';
    $title = get_string('flickr', 'theme_essential');
    $description = get_string('flickrdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_essential/twitter';
    $title = get_string('twitter', 'theme_essential');
    $description = get_string('twitterdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_essential/googleplus';
    $title = get_string('googleplus', 'theme_essential');
    $description = get_string('googleplusdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_essential/linkedin';
    $title = get_string('linkedin', 'theme_essential');
    $description = get_string('linkedindesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_essential/pinterest';
    $title = get_string('pinterest', 'theme_essential');
    $description = get_string('pinterestdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_essential/instagram';
    $title = get_string('instagram', 'theme_essential');
    $description = get_string('instagramdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_essential/youtube';
    $title = get_string('youtube', 'theme_essential');
    $description = get_string('youtubedesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_essential/skype';
    $title = get_string('skype', 'theme_essential');
    $description = get_string('skypedesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_essential/vk';
    $title = get_string('vk', 'theme_essential');
    $description = get_string('vkdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_essential', $temp);
    
     /* Category Settings */
    $temp = new admin_settingpage('theme_essential_categoryicon', get_string('categoryiconheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_categoryicon', get_string('categoryiconheadingsub', 'theme_essential'),
            format_text(get_string('categoryicondesc' , 'theme_essential'), FORMAT_MARKDOWN)));
    
    // Category Icons.
    $name = 'theme_essential/enablecategoryicon';
    $title = get_string('enablecategoryicon', 'theme_essential');
    $description = get_string('enablecategoryicondesc', 'theme_essential');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // We only want to output category icon options if the parent setting is enabled
    if(get_config('theme_essential', 'enablecategoryicon')) {
    
        // Default Icon Selector.
        $name = 'theme_essential/defaultcategoryicon';
        $title = get_string('defaultcategoryicon', 'theme_essential');
        $description = get_string('defaultcategoryicondesc', 'theme_essential');
        $default = 'folder-open';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
    
        // Category Icons.
        $name = 'theme_essential/enablecustomcategoryicon';
        $title = get_string('enablecustomcategoryicon', 'theme_essential');
        $description = get_string('enablecustomcategoryicondesc', 'theme_essential');
        $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
        
        if(get_config('theme_essential', 'enablecustomcategoryicon')) {
        
            // This is the descriptor for Custom Category Icons
            $name = 'theme_essential/categoryiconinfo';
            $heading = get_string('categoryiconinfo', 'theme_essential');
            $information = get_string('categoryiconinfodesc', 'theme_essential');
            $setting = new admin_setting_heading($name, $heading, $information);
            $temp->add($setting);
            
            // Get the default category icon.
            $defaultcategoryicon = get_config('theme_essential', 'defaultcategoryicon');
            if(empty($defaultcategoryicon)) {
                $defaultcategoryicon = 'folder-open';
            }
        
            // Get all category IDs and their pretty names
            require_once($CFG->libdir. '/coursecatlib.php');
            $coursecats = coursecat::make_categories_list();
            
            // Go through all categories and create the necessary settings
            foreach($coursecats as $key => $value) {
            
                // Category Icons for each category.
                $name = 'theme_essential/categoryicon';
                $title = $value;
                $description = get_string('categoryiconcategory', 'theme_essential', array('category' => $value));
                $default = $defaultcategoryicon;
                $setting = new admin_setting_configtext($name.$key, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $temp->add($setting);
            }
            unset($coursecats);
        }
    }

    $ADMIN->add('theme_essential', $temp);

    /* Apps Settings */
    $temp = new admin_settingpage('theme_essential_mobileapps', get_string('mobileappsheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_mobileapps', get_string('mobileappsheadingsub', 'theme_essential'),
            format_text(get_string('mobileappsdesc' , 'theme_essential'), FORMAT_MARKDOWN)));

    // Android App url setting.
    $name = 'theme_essential/android';
    $title = get_string('android', 'theme_essential');
    $description = get_string('androiddesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_essential/ios';
    $title = get_string('ios', 'theme_essential');
    $description = get_string('iosdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // This is the descriptor for iOS Icons
    $name = 'theme_essential/iosiconinfo';
    $heading = get_string('iosicon', 'theme_essential');
    $information = get_string('iosicondesc', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_essential/iphoneicon';
    $title = get_string('iphoneicon', 'theme_essential');
    $description = get_string('iphoneicondesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_essential/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_essential');
    $description = get_string('iphoneretinaicondesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_essential/ipadicon';
    $title = get_string('ipadicon', 'theme_essential');
    $description = get_string('ipadicondesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_essential/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_essential');
    $description = get_string('ipadretinaicondesc', 'theme_essential');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_essential', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_essential_alerts', get_string('alertsheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_alerts', get_string('alertsheadingsub', 'theme_essential'),
            format_text(get_string('alertsdesc' , 'theme_essential'), FORMAT_MARKDOWN)));
    
    // This is the descriptor for Alert One
    $name = 'theme_essential/alert1info';
    $heading = get_string('alert1', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_essential/enable1alert';
    $title = get_string('enablealert', 'theme_essential');
    $description = get_string('enablealertdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_essential/alert1type';
    $title = get_string('alerttype' , 'theme_essential');
    $description = get_string('alerttypedesc', 'theme_essential');
    $alert_info = get_string('alert_info', 'theme_essential');
    $alert_warning = get_string('alert_warning', 'theme_essential');
    $alert_general = get_string('alert_general', 'theme_essential');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_essential/alert1title';
    $title = get_string('alerttitle', 'theme_essential');
    $description = get_string('alerttitledesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_essential/alert1text';
    $title = get_string('alerttext', 'theme_essential');
    $description = get_string('alerttextdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // This is the descriptor for Alert Two
    $name = 'theme_essential/alert2info';
    $heading = get_string('alert2', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_essential/enable2alert';
    $title = get_string('enablealert', 'theme_essential');
    $description = get_string('enablealertdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_essential/alert2type';
    $title = get_string('alerttype' , 'theme_essential');
    $description = get_string('alerttypedesc', 'theme_essential');
    $alert_info = get_string('alert_info', 'theme_essential');
    $alert_warning = get_string('alert_warning', 'theme_essential');
    $alert_general = get_string('alert_general', 'theme_essential');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_essential/alert2title';
    $title = get_string('alerttitle', 'theme_essential');
    $description = get_string('alerttitledesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_essential/alert2text';
    $title = get_string('alerttext', 'theme_essential');
    $description = get_string('alerttextdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // This is the descriptor for Alert Three
    $name = 'theme_essential/alert3info';
    $heading = get_string('alert3', 'theme_essential');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_essential/enable3alert';
    $title = get_string('enablealert', 'theme_essential');
    $description = get_string('enablealertdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_essential/alert3type';
    $title = get_string('alerttype' , 'theme_essential');
    $description = get_string('alerttypedesc', 'theme_essential');
    $alert_info = get_string('alert_info', 'theme_essential');
    $alert_warning = get_string('alert_warning', 'theme_essential');
    $alert_general = get_string('alert_general', 'theme_essential');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_essential/alert3title';
    $title = get_string('alerttitle', 'theme_essential');
    $description = get_string('alerttitledesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_essential/alert3text';
    $title = get_string('alerttext', 'theme_essential');
    $description = get_string('alerttextdesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_essential', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_essential_analytics', get_string('analyticsheading', 'theme_essential'));
    $temp->add(new admin_setting_heading('theme_essential_analytics', get_string('analyticsheadingsub', 'theme_essential'),
            format_text(get_string('analyticsdesc' , 'theme_essential'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_essential/useanalytics';
    $title = get_string('useanalytics', 'theme_essential');
    $description = get_string('useanalyticsdesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_essential/analyticsid';
    $title = get_string('analyticsid', 'theme_essential');
    $description = get_string('analyticsiddesc', 'theme_essential');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_essential/analyticsclean';
    $title = get_string('analyticsclean', 'theme_essential');
    $description = get_string('analyticscleandesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Track Admins
    $name = 'theme_essential/analyticsadmin';
    $title = get_string('analyticsadmin', 'theme_essential');
    $description = get_string('analyticsadmindesc', 'theme_essential');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_essential', $temp);