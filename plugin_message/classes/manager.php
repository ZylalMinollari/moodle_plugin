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
 * @package     local_plugin_message
 * @author      Zylal <zminollari@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_plugin_message;

use dml_exception;
use stdClass;

class manager
{

    public function create_message(string $message_text, string $message_type): bool
    {
        global $DB;
        $record_to_insert = new stdClass();
        $record_to_insert->messagetext = $message_text;
        $record_to_insert->messagetype = $message_type;
        try {
            return $DB->insert_record('local_plugin_message', $record_to_insert, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    public function get_messages(int $userid): array
    {
        global $DB;
        $sql = "SELECT lm.id, lm.messagetext, lm.messagetype 
            FROM {local_plugin_message} lm 
            LEFT OUTER JOIN {local_plugin_message_read} lmr ON lm.id = lmr.messageid AND lmr.userid = :userid 
            WHERE lmr.userid IS NULL";
        $params = [
            'userid' => $userid,
        ];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (dml_exception $e) {
            return [];
        }
    }

    public function get_all_messages(): array
    {
        global $DB;
        return $DB->get_records('local_plugin_message');
    }

    public function mark_message_read(int $message_id, int $userid): bool
    {
        global $DB;
        $read_record = new stdClass();
        $read_record->messageid = $message_id;
        $read_record->userid = $userid;
        $read_record->timeread = time();
        try {
            return $DB->insert_record('local_plugin_message_read', $read_record, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    public function get_message(int $messageid)
    {
        global $DB;
        return $DB->get_record('local_plugin_message', ['id' => $messageid]);
    }

    public function update_message(int $messageid, string $message_text, string $message_type): bool
    {
        global $DB;
        $object = new stdClass();
        $object->id = $messageid;
        $object->messagetext = $message_text;
        $object->messagetype = $message_type;
        return $DB->update_record('local_plugin_message', $object);
    }

    public function update_messages(array $messageids, $type): bool
    {
        global $DB;
        list($ids, $params) = $DB->get_in_or_equal($messageids);
        return $DB->set_field_select('local_plugin_message', 'messagetype', $type, "id $ids", $params);
    }

    public function delete_message($messageid)
    {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $deletedMessage = $DB->delete_records('local_plugin_message', ['id' => $messageid]);
        $deletedRead = $DB->delete_records('local_plugin_message_read', ['messageid' => $messageid]);
        if ($deletedMessage && $deletedRead) {
            $DB->commit_delegated_transaction($transaction);
        }
        return true;
    }

    public function delete_messages($messageids)
    {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        list($ids, $params) = $DB->get_in_or_equal($messageids);
        $deletedMessages = $DB->delete_records_select('local_plugin_message', "id $ids", $params);
        $deletedReads = $DB->delete_records_select('local_plugin_message_read', "messageid $ids", $params);
        if ($deletedMessages && $deletedReads) {
            $DB->commit_delegated_transaction($transaction);
        }
        return true;
    }
}