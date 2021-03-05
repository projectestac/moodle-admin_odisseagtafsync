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
 * Version details.
 *
 * @package    tool
 * @subpackage odisseagtafsync
 * @copyright  2013 Departament d'Ensenyament de la Generalitat de Catalunya
 * @author     Sara Arjona Téllez <sarjona@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class odissea_uu_progress_tracker {

    private $_row;
    public $columns = ['status', 'line', 'id', 'username', 'firstname', 'lastname', 'email', 'password', 'auth', 'enrolments', 'suspended', 'deleted'];
    protected $return = [];
    protected $print = '';

    /**
     * Print table header.
     *
     * @return void
     * @throws coding_exception
     */
    public function start() {
        $ci = 0;
        $this->print .= '<table id="uuresults" class="generaltable boxaligncenter flexible-wrap" summary="'.get_string('uploadusersresult', 'tool_uploaduser').'">';
        $this->print .= '<tr class="heading r0">';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('status').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('uucsvline', 'tool_uploaduser').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">ID</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('username').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('firstname').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('lastname').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('email').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('password').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('authentication').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('enrolments', 'enrol').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('suspended', 'auth').'</th>';
        $this->print .= '<th class="header c'.$ci++.'" scope="col">'.get_string('delete').'</th>';
        $this->print .= '</tr>';
        $this->return[0] = $this->columns;
        $this->_row = null;
    }

    /**
     * Flush previous line and start a new one.
     * @param int $linenum
     */
    public function flush($linenum = -1) {
        if (empty($this->_row) or empty($this->_row['line']['normal'])) {
            // Nothing to print - each line has to have at least number
            $this->_row = array();
            foreach ($this->columns as $col) {
                $this->_row[$col] = array('normal'=>'', 'info'=>'', 'warning'=>'', 'error'=>'');
            }
            return;
        }

        if ($linenum < 0){
            $linenum = count($this->return);
        }
        $ci = 0;
        $ri = $linenum;
        $this->print .= '<tr class="r'.$ri.'">';
        foreach ($this->_row as $key=>$field) {
            $str = ' ';
            foreach ($field as $type=>$content) {
                if ($field[$type] !== '') {
                    $str = $field[$type];
                    $field[$type] = '<span class="uu'.$type.'">'.$field[$type].'</span>';
                } else {
                    unset($field[$type]);
                }
            }
            $this->print .= '<td class="cell c'.$ci++.'">';
            if (!empty($str)) {
                $this->return[$linenum][] = str_replace(';', ',', str_replace('&quot;', '"', $str));
                unset($str);
            }
            if (!empty($field)) {
                $this->print .= implode('<br />', $field);
            } else {
                $this->print .= '&nbsp;';
            }
            $this->print .= '</td>';
        }
        $this->print .= '</tr>';
        foreach ($this->columns as $col) {
            $this->_row[$col] = array('normal'=>'', 'info'=>'', 'warning'=>'', 'error'=>'');
        }
    }

    /**
     * Add tracking info
     * @param string $col name of column
     * @param string $msg message
     * @param string $level 'normal', 'warning' or 'error'
     * @param bool $merge true means add as new line, false means override all previous text of the same type
     * @return void
     */
    public function track($col, $msg, $level = 'normal', $merge = true) {
        if (empty($this->_row)) {
            $this->flush(); //init arrays
        }
        if (!in_array($col, $this->columns)) {
            debugging('Incorrect column:'.$col);
            return;
        }
        if ($merge) {
            if ($this->_row[$col][$level] != '') {
                $this->_row[$col][$level] .='<br />';
            }
            $this->_row[$col][$level] .= $msg;
        } else {
            $this->_row[$col][$level] = $msg;
        }
    }

    /**
     * Print the table end
     * @return void
     */
    public function close() {
        $this->flush();
        $this->print .= '</table>';
        return [$this->return, $this->print];
    }
}
