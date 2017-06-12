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
 * Class to manage tags, extends core_tag_tag
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag;

use core_tag_tag;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to manage tags, extends core_tag_tag
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag extends core_tag_tag {
    /** @var string Defines the prefix for all meta tags */
    const COURSE_TAGS_META_PREFIX = 'meta_';

    /** @var string Defines the prefix for the group meta tags */
    const COURSE_TAGS_META_GROUP_PREFIX = 'meta_group_';

    /** @var string Defines the prefix for the option meta tags */
    const COURSE_TAGS_META_OPTION_PREFIX = 'meta_option_';

    /** @var string Defines the singleselect option meta tag */
    const COURSE_TAGS_META_OPTION_SINGLESELECT = 'meta_option_singleselect';

    /** @var string Defines the provider option meta tag */
    const COURSE_TAGS_META_OPTION_PROVIDER = 'meta_option_provider';

    /** @var object $settings The tag settings object */
    protected static $settings = null;

    /**
     * Get the stripped name without the meta tag prefixes.
     *
     * @param string|object $metatag The meta tag object
     * @param string        $type    The meta tag type to be stripped off
     *
     * @return string
     */
    public static function get_meta_tag_stripped_name($metatag, $type) {
        $len = strlen($type);
        $metatagname = (is_string($metatag)) ? $metatag : (object)$metatag->rawname;
        $strippedname = substr($metatagname, $len);

        return ucfirst($strippedname);
    }

    /**
     * Get the meta prefix.
     * Return either the value saved in the config or the predefined constant.
     *
     * @return string
     */
    public static function get_meta_prefix() {
        $prefix = get_config('', 'tag_metaprefix');
        if ($prefix) {
            return strtolower($prefix);
        } else {
            return self::COURSE_TAGS_META_PREFIX;
        }
    }

    /**
     * Get the group meta prefix.
     * Return either the value saved in the config or the predefined constant.
     *
     * @return string
     */
    public static function get_meta_group_prefix() {
        $prefix = get_config('', 'tag_metagroupprefix');
        if ($prefix) {
            return strtolower($prefix);
        } else {
            return self::COURSE_TAGS_META_GROUP_PREFIX;
        }
    }

    /**
     * Get the option meta prefix.
     * Return either the value saved in the config or the predefined constant.
     *
     * @return string
     */
    public static function get_meta_option_prefix() {
        $prefix = get_config('', 'tag_metaoptionprefix');
        if ($prefix) {
            return strtolower($prefix);
        } else {
            return self::COURSE_TAGS_META_OPTION_PREFIX;
        }
    }

    /**
     * Get the singleselect option meta tag.
     * Return either the value saved in the config or the predefined constant.
     *
     * @return string
     */
    public static function get_meta_option_singleselect() {
        $prefix = get_config('', 'tag_metaoptionsingleselect');
        if ($prefix) {
            return strtolower($prefix);
        } else {
            return self::COURSE_TAGS_META_OPTION_SINGLESELECT;
        }
    }

    /**
     * Get the provider option meta tag.
     * Return either the value saved in the config or the predefined constant.
     *
     * @return string
     */
    public static function get_meta_option_provider() {
        $prefix = get_config('', 'tag_metaoptionprovider');
        if ($prefix) {
            return strtolower($prefix);
        } else {
            return self::COURSE_TAGS_META_OPTION_PROVIDER;
        }
    }
}
