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

namespace Leap\Plugin\DB\Firebird\DataReader {

	/**
	 * This class is used to read data from a Firebird database using the standard
	 * driver.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\Firebird\DataReader
	 * @version 2015-08-31
	 */
	class Standard extends \Leap\Core\DB\SQL\DataReader\Standard {

		/**
		 * This variable stores the names of all blob fields.
		 *
		 * @access protected
		 * @var string
		 */
		protected $blobs;

		/**
		 * This variable is used to store the connection's resource.
		 *
		 * @access protected
		 * @var resource
		 */
		protected $resource;

		/**
		 * This method initializes the class.
		 *
		 * @access public
		 * @override
		 * @param \Leap\Core\DB\Connection\Driver $connection       the connection to be used
		 * @param \Leap\Core\DB\SQL\Command $command                the SQL command to be used
		 * @param integer $mode                                     the execution mode to be used
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the query failed
		 */
		public function __construct(\Leap\Core\DB\Connection\Driver $connection, \Leap\Core\DB\SQL\Command $command, $mode = NULL) {
			$this->resource = $connection->get_resource();
			$handle = @ibase_query($this->resource, $command->text);
			if ($handle === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to query SQL command. Reason: :reason', array(':reason' => @ibase_errmsg()));
			}
			$this->handle = $handle;
			$this->record = FALSE;
			$this->blobs = array();
			$count = (int) @ibase_num_fields($handle);
			for ($i = 0; $i < $count; $i++) {
				$field = ibase_field_info($handle, $i);
				if ($field['type'] == 'BLOB') {
					$this->blobs[] = $field['name'];
				}
			}
		}

		/**
		 * This method releases any internal references to an object.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->blobs);
			unset($this->resource);
		}

		/**
		 * This method assists with freeing, releasing, and resetting un-managed resources.
		 *
		 * @access public
		 * @param boolean $disposing                                whether managed resources can be disposed
		 *                                                          in addition to un-managed resources
		 */
		public function dispose($disposing = TRUE) {
			if ($this->handle !== NULL) {
				@ibase_free_result($this->handle);
				$this->handle = NULL;
				$this->record = FALSE;
				$this->blobs = array();
				$this->resource = NULL;
			}
		}

		/**
		 * This method advances the reader to the next record.
		 *
		 * @access public
		 * @override
		 * @return boolean                                          whether another record was fetched
		 *
		 * @see http://php.net/manual/en/function.ibase-blob-get.php
		 */
		public function read() {
			$this->record = @ibase_fetch_assoc($this->handle);
			if ($this->record !== FALSE) {
				foreach ($this->blobs as $field) {
					$info = @ibase_blob_info($this->resource, $this->record[$field]);
					if (is_array($info) AND ! $info['isnull']) {
						$buffer = '';
						$handle = @ibase_blob_open($this->resource, $this->record[$field]);
						if ($handle !== FALSE) {
							for ($i = 0; $i < $info[1]; $i++) {
								$size = ($i == ($info[1] - 1))
									? $info[0] - ($i * $info[2])
									: $info[2];
								$value = @ibase_blob_get($handle, $size);
								if ($value !== FALSE) {
									$buffer .= $value;
								}
							}
							@ibase_blob_close($handle);
						}
						$this->record[$field] = $buffer;
					}
					else {
						$this->record[$field] = NULL;
					}
				}
				return TRUE;
			}
			return FALSE;
		}

	}

}