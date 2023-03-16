<?php
// This file is part of Moodle Course Rollover Plugin
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
 * @package local_plugin_message
 * @author Zylal <zminollari@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $DB;

require_login();

$PAGE->set_url(new moodle_url('/local/plugin_message/manage.php'));

$messages = $DB->get_records('local_plugin_message', null, 'id');

echo $OUTPUT->header();
$templatecontext = (object) [
    'messages' => array_values($messages),
    'editurl' => new moodle_url('/local/plugin_message/edit.php'),
    'bulkediturl' => new moodle_url('/local/plugin_message/bulkedit.php'),
];

echo $OUTPUT->render_from_template('local_plugin_message/manage', $templatecontext);

echo $OUTPUT->footer();