<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This class represents a "double" field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2011-12-02
 *
 * @abstract
 */
abstract class Base_DB_ORM_Field_Double extends DB_ORM_Field {

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param DB_ORM_Model $model                   a reference to the implementing model
     * @param array $metadata                       the field's metadata
     */
    public function __construct(DB_ORM_Model $model, Array $metadata = array()) {
        parent::__construct($model, 'double');

        $this->metadata['max_digits'] = (integer)$metadata['max_digits']; // the total number of digits that are stored
        
        $this->metadata['max_decimals'] = (integer)$metadata['max_decimals']; // the number of digits that may be after the decimal point

        $this->metadata['unsigned'] = (isset($metadata['unsigned'])) ? (boolean)$metadata['unsigned'] : FALSE;

        $default = 0.0;
		if (isset($metadata['range'])) { // http://firebirdsql.org/manual/migration-mssql-data-types.html
		    $this->metadata['range']['lower_bound'] = (double)$metadata['range'][0]; // float: -1.79E + 308 double: -3.40E + 38
		    $default = max($default, $this->metadata['range']['lower_bound']);
		    $this->metadata['range']['upper_bound'] = (double)$metadata['range'][1]; // float: 1.79E + 308 double: 3.40E + 38
		}

		if (isset($metadata['savable'])) {
            $this->metadata['savable'] = (boolean)$metadata['savable'];
        }

        if (isset($metadata['nullable'])) {
            $this->metadata['nullable'] = (boolean)$metadata['nullable'];
        }

        if (isset($metadata['filter'])) {
            $this->metadata['filter'] = (string)$metadata['filter'];
        }

        if (isset($metadata['callback'])) {
            $this->metadata['callback'] = (string)$metadata['callback'];
        }

        if (isset($metadata['enum'])) {
            $this->metadata['enum'] = (array)$metadata['enum'];
        }

        if (isset($metadata['default'])) {
            $default = $metadata['default'];
            if (!is_null($default)) {
                settype($default, $this->metadata['type']);
                $this->validate($default);
            }
            $this->metadata['default'] = $default;
            $this->value = $default;
        }
        else if (!$this->metadata['nullable']) {
            $this->metadata['default'] = $default;
            $this->value = $default;
        }
    }

    /**
     * This function validates the specified value against any constraints.
     *
     * @access protected
     * @param mixed $value                          the value to be validated
     * @return boolean                              whether the specified value validates
     */
    protected function validate($value) {
        if (!is_null($value)) {
            if ($this->metadata['unsigned'] && ($value < 0.0)) {
                return FALSE;
            }
            else if (isset($this->metadata['range'])) {
                if (($value < $this->metadata['range']['lower_bound']) || ($value > $this->metadata['range']['upper_bound'])) {
				    return FALSE;
			    }
		    }
            $parts = preg_split('/\./', "{$value}");
            $digits = strlen("{$parts[0]}");
            if (count($parts) > 1) {
                $decimals = strlen("{$parts[1]}");
                if ($decimals > $this->metadata['max_decimals']) {
                    return FALSE;
                }
                $digits += $decimals;
            }
            if ($digits > $this->metadata['max_digits']) {
                return FALSE;
            }
        }
        return parent::validate($value);
    }

}
?>