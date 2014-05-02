<?php

/**
 * Copyright © 2011–2014 Spadefoot Team.
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

namespace Leap\Plugin\DB\MsSQL\DataReader {

	/**
	 * This class is used to read data from a MS SQL database using the improved
	 * (i.e. sqlsrv) driver.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\MsSQL\DataReader
	 * @version 2014-04-30
	 *
	 * @see http://php.net/manual/en/ref.sqlsrv.php
	 */
	class Improved extends \Leap\Core\DB\SQL\DataReader\Standard {

		/**
		 * This method initializes the class.
		 *
		 * @access public
		 * @override
		 * @param \Leap\Core\DB\Connection\Driver $connection       the connection to be used
		 * @param \Leap\Core\DB\SQL\Command $sql                    the SQL statement to be queried
		 * @param integer $mode                                     the execution mode to be used
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the query failed
		 *
		 * @see http://php.net/manual/en/function.sqlsrv-query.php
		 */
		public function __construct(\Leap\Core\DB\Connection\Driver $connection, \Leap\Core\DB\SQL\Command $sql, $mode = NULL) {
			$resource = $connection->get_resource();
			$command = @sqlsrv_query($resource, $sql->text);
			if ($command === FALSE) {
				$errors = @sqlsrv_errors(SQLSRV_ERR_ALL);
				$reason = (is_array($errors) AND isset($errors[0]['message']))
					? $errors[0]['message']
					: 'Unable to perform command.';
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to query SQL statement. Reason: :reason', array(':reason' => $reason));
			}
			$this->command = $command;
			$this->record = FALSE;
		}

		/**
		 * This method frees the command reference.
		 *
		 * @access public
		 * @override
		 *
		 * @see http://php.net/manual/en/function.sqlsrv-free-stmt.php
		 */
		public function free() {
			if ($this->command !== NULL) {
				@sqlsrv_free_stmt($this->command);
				$this->command = NULL;
				$this->record = FALSE;
			}
		}

		/**
		 * This method advances the reader to the next record.
		 *
		 * @access public
		 * @override
		 * @return boolean                                          whether another record was fetched
		 *
		 * @see http://php.net/manual/en/function.sqlsrv-fetch-array.php
		 */
		public function read() {
			$this->record = @sqlsrv_fetch_array($this->command, SQLSRV_FETCH_ASSOC);
			return ($this->record !== FALSE);
		}

	}

}