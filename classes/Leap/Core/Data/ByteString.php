<?php

/**
 * Copyright © 2011–2015 Spadefoot Team.
 *
 * Unless otherwise noted, Leap is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Leap\Core\Data {

	/**
	 * This class represents a data buffer.
	 *
	 * @access public
	 * @class
	 * @package Leap\Core\Data
	 * @version 2015-08-23
	 *
	 * @see https://developer.apple.com/library/mac/documentation/Cocoa/Reference/Foundation/Classes/NSData_Class/Reference/Reference.html
	 */
	class ByteString extends \Leap\Core\Object implements \Countable {

		/**
		 * This constant represents binary data.
		 *
		 * @access public
		 * @const integer
		 */
		const BINARY_DATA = 0;

		/**
		 * This constant represents hexadecimal data.
		 *
		 * @access public
		 * @const integer
		 */
		const HEXADECIMAL_DATA = 1;

		/**
		 * This constant represents string data.
		 *
		 * @access public
		 * @const integer
		 */
		const STRING_DATA = 2;

		/**
		 * This variable stores the data as a hexadecimal.
		 *
		 * @access protected
		 * @var string
		 */
		protected $hexcode;

		/**
		 * This variable stores the length of the data as a byte string.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $length;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param string $data                                      the data
		 * @param integer $type                                     the current type of data
		 */
		public function __construct($data, $type = 1) {
			$this->hexcode = static::unpack($data, $type);
			$this->length = -1;
		}

		/**
		 * This method releases any internal references to an object.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->hexcode);
			unset($this->length);
		}

		/**
		 * This method returns the data as a hexadecimal.
		 *
		 * @access public
		 * @override
		 * @return string                                           the data as a hexadecimal
		 */
		public function __toString() {
			return $this->hexcode;
		}

		/**
		 * This method returns the data as a binary string.
		 *
		 * @access public
		 * @param string $format                                    the string formatting to be used
		 * @return string                                           the data as a binary string
		 */
		public function as_binary($format = '%s') {
			$binary = base_convert($this->hexcode, 16, 2);
			if ($format != '%s') { // this is done for efficiency
				return sprintf($format, $binary);
			}
			return $binary;
		}

		/**
		 * This method returns the data as a hexadecimal.
		 *
		 * @access public
		 * @param string $format                                    the string formatting to be used
		 * @return string                                           the data as a hexadecimal
		 */
		public function as_hexcode($format = '%s') {
			if ($format != '%s') {
				return sprintf($format, $this->hexcode); // this is done for efficiency
			}
			return $this->hexcode;
		}

		/**
		 * This method returns the data as a string.
		 *
		 * @access public
		 * @param string $format                                    the string formatting to be used
		 * @param boolean $pack                                     whether to pack the hexcode as a string
		 * @return string                                           the data as a string
		 */
		public function as_string($format = '%s', $pack = TRUE) {
			$string = ($pack) ? static::pack($this->hexcode) : $this->hexcode;
			if ($format != '%s') {
				return sprintf($format, $string); // this is done for efficiency
			}
			return $string;
		}

		/**
		 * This method return the length of the data as a byte string.
		 *
		 * @access public
		 * @override
		 * @return integer                                          the length of the data as a byte
		 *                                                          string
		 */
		public function count() {
			if ($this->length < 0) {
				$this->length = strlen($this->hexcode) * 2;
			}
			return $this->length;
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This method converts a hexadecimal to a string.
		 *
		 * @access protected
		 * @static
		 * @param string $hexcode                                   the data to be converted
		 * @return string                                           a string
		 */
		protected static function pack($hexcode) {
			if (is_string($hexcode)) {
				return pack('H*', $hexcode);
			}
			return '';
		}

		/**
		 * This method converts string to a hexadecimal.
		 *
		 * @access protected
		 * @static
		 * @param string $data                                      the data to be converted
		 * @param integer $type                                     the type of data to be converted
		 * @return string                                           a hexadecimal string
		 */
		protected static function unpack($data, $type) {
			if (is_string($data)) {
				switch ($type) {
					case static::BINARY_DATA:
						$binary = (preg_match("/^b'.*'$/i", $data))
							? substr($data, 2, strlen($data) - 3)
							: $data;
						return base_convert($binary, 2, 16);
					break;
					case static::STRING_DATA:
						$hexcode = unpack('H*hex', $data);
						return $hexcode['hex'];
					break;
					case static::HEXADECIMAL_DATA:
						return $data;
					break;
				}
			}
			else if (is_object($data) AND ($data instanceof \Leap\Core\Data\ByteString)) {
				return $data->as_hexcode();
			}
			return '';
		}

	}

}