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
 * IVS filter post install hook
 *
 * @package    filter
 * @subpackage ivs
 * @copyright  2014 onwards Andrew Davis (andyjdavis)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_filter_ivs_install() {
    global $CFG;
    require_once("$CFG->libdir/filterlib.php");

    filter_set_global_state('ivs', TEXTFILTER_ON, -1);

    $states = filter_get_global_states();
    $displayivsppos = $states['ivs']->sortorder;
    $activitynamespos = 1;
    if (!empty($states['activitynames'])) {
        $activitynamespos = $states['activitynames']->sortorder;
    }
    $urltolinkpos = 1;
    if (!empty($states['urltolink'])) {
        $urltolinkpos = $states['urltolink']->sortorder;
    }
    $minpos = ($activitynamespos < $urltolinkpos) ? $activitynamespos : $urltolinkpos;
    while ($minpos < $displayivsppos) {
        filter_set_global_state('ivs', TEXTFILTER_ON, -1);
        $displayivsppos--;
    }
}
