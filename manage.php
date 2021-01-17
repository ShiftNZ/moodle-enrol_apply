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
 * @package    enrol_applyhospice
 * @copyright  emeneo.com (http://emeneo.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     emeneo.com (http://emeneo.com/)
 * @author     Johannes Burk <johannes.burk@sudile.com>
 */

require_once '../../config.php';
require_once $CFG->dirroot . '/enrol/applyhospice/lib.php';
require_once $CFG->dirroot . '/enrol/applyhospice/manage_table.php';
require_once $CFG->dirroot . '/enrol/applyhospice/renderer.php';

$id = optional_param('id', null, PARAM_INT);
$formaction = optional_param('formaction', null, PARAM_TEXT);
$userenrolments = optional_param_array('userenrolments', null, PARAM_INT);

require_login();

$manageurlparams = array();
if ($id == null) {
    $context = context_system::instance();
    require_capability('enrol/applyhospice:manageapplications', $context);
    $pageheading = get_string('confirmusers', 'enrol_applyhospice');
    $instance = null;
} else {
    $instance = $DB->get_record('enrol', array('id' => $id, 'enrol' => 'applyhospice'), '*', MUST_EXIST);
    require_course_login($instance->courseid);
    $course = get_course($instance->courseid);
    $context = context_course::instance($course->id, MUST_EXIST);
    require_capability('enrol/applyhospice:manageapplications', $context);
    $manageurlparams['id'] = $instance->id;
    $pageheading = $course->fullname;
}

$manageurl = new moodle_url('/enrol/applyhospice/manage.php', $manageurlparams);

$PAGE->set_context($context);
$PAGE->set_url($manageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($pageheading);
$PAGE->navbar->add(get_string('confirmusers', 'enrol_applyhospice'));
$PAGE->set_title(get_string('confirmusers', 'enrol_applyhospice'));
$PAGE->requires->css('/enrol/applyhospice/style.css');

if ($formaction != null && $userenrolments != null) {
    $enrolapply = enrol_get_plugin('applyhospice');
    switch ($formaction) {
        case 'confirm':
            $enrolapply->confirm_enrolment($userenrolments);
            break;
        case 'wait':
            $enrolapply->wait_enrolment($userenrolments);
            break;
        case 'cancel':
            $enrolapply->cancel_enrolment($userenrolments);
            break;
    }
    redirect($manageurl);
}

$table = new enrol_applyhospice_manage_table($id);
$table->define_baseurl($manageurl);

$renderer = $PAGE->get_renderer('enrol_applyhospice');
$renderer->manage_page($table, $manageurl, $instance);
