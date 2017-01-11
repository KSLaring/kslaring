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
 * Contains class \local_tag\output\output_base
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag\output;

use templatable;
use coding_exception;

/**
 * Class to display the tags in a tag meta group.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class output_base implements templatable {
    /**
     * PHP overloading magic to make the __isset syntax work by redirecting
     * it to the corresponding isset_name method if there is one, and
     * throwing an exception if not.
     *
     * @param string $name property name
     *
     * @return mixed
     * @throws coding_exception
     */
    public function __isset($name) {
        $issetmethod = 'isset_' . $name;
        if (method_exists($this, $issetmethod)) {
            return $this->$issetmethod();
        } else {
            throw new coding_exception('Unknown isset_ method for property ' . $name);
        }
    }

    /**
     * PHP overloading magic to make the __get syntax work by redirecting
     * it to the corresponding get_name method if there is one, and
     * throwing an exception if not.
     *
     * @param string $name property name
     *
     * @return mixed
     * @throws coding_exception
     */
    public function __get($name) {
        $getmethod = 'get_' . $name;
        if (method_exists($this, $getmethod)) {
            return $this->$getmethod();
        } else {
            throw new coding_exception('Unknown get_ method for property ' . $name);
        }
    }

    /**
     * PHP overloading magic to make the __set syntax work by redirecting
     * it to the corresponding set_name method if there is one, and
     * throwing an exception if not.
     *
     * @param string $name property name
     *
     * @throws coding_exception
     */
    public function __set($name, $value) {
        $setmethod = 'set_' . $name;
        if (method_exists($this, $setmethod)) {
            $this->$setmethod($value);
        } else {
            throw new coding_exception('Unknown set_ method for property' . $name);
        }
    }
}
