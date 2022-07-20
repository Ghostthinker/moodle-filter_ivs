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
 * This filter provides automatic support for ivs
 *
 * @package    filter_ivs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_ivs\annotation;
use mod_ivs\IvsHelper;


/**
 * ivs filtering
 */
class filter_ivs extends moodle_text_filter {
    /*
     * Add the javascript to enable ivs processing on this page.
     *
     * @param moodle_page $page The current page.
     * @param context $context The current context.
     */

    public function setup($page, $context) {
        global $CFG, $PAGE;

        if ($page->requires->should_create_one_time_item_now('filter_ivs-scripts')) {
            $PAGE->requires->jquery();
            $PAGE->requires->css(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.css'));
            $PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/mod/ivs/templates/annotation_view.js'));

        }
    }

    /*
     * This function wraps the filtered text in a span, that ivs is configured to process.
     *
     * @param string $text The text to filter.
     * @param array $options The filter options.
     */
    public function filter($text, array $options = array()) {

        // find domain.tld/mod/ivs/view.php?id=15&cid=82
        $re = '@(("|\')?[[http[s]?]?(://)?([a-zA-Z][-\w]+[\.|:]+[^\s\.]+[^\s]*[/mod/ivs/view\.php\?id=][\d]+[&|&amp;]+[cid=]+)(\d+)([&|&amp;]+nofilter)?(</a>)?)@';

        $result = preg_replace_callback($re, function($m) {
            global $PAGE, $DB, $USER;
            $quotesfound = $m[2] ?? null;
            $annotationid = $m[5];
            $nofilter = $m[6] ?? null;
            $endtaglinkfound = $m[7] ?? null;

            $annotation = annotation::retrieve_from_db($annotationid);

            if ($quotesfound || $endtaglinkfound || empty($annotation)) {
                return $m[0];
            }

            if ($nofilter) {
                $outputlink = $annotation->get_annotation_player_url();
                $outputlink = html_writer::link(new moodle_url($outputlink), $outputlink, array('class' => 'ivsannotation'));
                return $outputlink;
            }

            if ($annotation->access("view")) {
                $cm = get_coursemodule_from_instance('ivs', $annotation->get_videoid(), 0, false, MUST_EXIST);
                $video = $DB->get_record('ivs', array('id' => $cm->instance), '*', MUST_EXIST);
                $renderable = new \mod_ivs\output\annotation_view($annotation, $video, $cm);
                $renderer = $PAGE->get_renderer('course');
                $renderedannotation = $renderer->render($renderable);
                $renderedannotation = '<div class="ivs-annotations ivs-annotation-embedded">' . $renderedannotation . '</div>';
            } else {
                $renderedannotation = $m[0] . " (" . get_string("accessdenied", 'admin') . ")";
            }

            return $renderedannotation;
        }, $text);

        return $result;
    }

}
