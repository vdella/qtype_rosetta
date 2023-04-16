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
 * Question type class for the rosetta question type.
 *
 * @package    qtype
 * @subpackage rosetta
 * @copyright  2023 vdella (vitor.origamer@gmail.com)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /*https://docs.moodle.org/dev/Question_types#Question_type_and_question_definition_classes*/


defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/rosetta/question.php');
require_once($CFG->dirroot . '/question/type/rosetta/classes/fl_machine_test.php');


/**
 * The rosetta question type.
 *
 * @copyright  2023 vdella (vitor.origamer@gmail.com)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_rosetta extends question_type {

      /* ties additional table fields to the database */
    public function extra_question_fields() {
        return array('question_rosetta', 'somefieldname','anotherfieldname');
    }
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
            $newcontextid, 'qtype_essay', 'graderinfo', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_essay', 'graderinfo', $questionid);
    }
     /**
     * @param stdClass $question
     * @param array $form
     * @return object
     */
    public function save_question($question, $form) {
        return parent::save_question($question, $form);
    }
    public function save_question_options($question) {
        global $DB;
        $options = $DB->get_record('question_rosetta', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            /* add any more non combined feedback fields here */
            $options->id = $DB->insert_record('question_imageselect', $options);
        }
        $options = $this->save_combined_feedback_helper($options, $question, $question->context, true);
        $DB->update_record('question_rosetta', $options);
        $this->save_hints($question);
    }

 /* 
 * populates fields such as combined feedback 
 * also make $DB calls to get data from other tables
 */
   public function get_question_options($question) {
       global $CFG, $DB, $OUTPUT;
       if (parent::get_question_options($question)) {

           // Fetch and Parse Tests from DB (indexed by id)
           $tests = array_map(
               function ($db_test) {
                   return fl_machine_test::from_db_entry_to_array($db_test);
               },
               $DB->get_records('qtype_rosetta_tests', array('question_id' => $question->id))
           );

           // Insert tests into question object
           $question->machine_tests = $tests ? [...$tests] : array();

           return true;
       }
       return false;
    }

 /**
 * executed at runtime (e.g. in a quiz or preview 
 **/
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        // Load parent data
        parent::initialise_question_instance($question, $questiondata);
        // Load tests
        error_log("[initialise_question_instance]\n".print_r($questiondata->machine_tests, true));
        $question->machine_tests = $questiondata->machine_tests;
    }
    
   public function initialise_question_answers(question_definition $question, $questiondata,$forceplaintextanswers = true){ 
     //TODO
    }
    
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'question_rosetta') {
            return false;
        }
        $question = parent::import_from_xml($data, $question, $format, null);
        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, false, $format->get_format($question->questiontextformat));
        return $question;
    }
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        global $CFG;
        $pluginmanager = core_plugin_manager::instance();
        $gapfillinfo = $pluginmanager->get_plugin_info('question_rosetta');
        $output = parent::export_to_xml($question, $format);
        //TODO
        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);
        return $output;
    }


    public function get_random_guess_score($questiondata) {
        // TODO.
        return 0;
    }

    public function get_possible_responses($questiondata) {
        // TODO.
        return array();
    }
}
