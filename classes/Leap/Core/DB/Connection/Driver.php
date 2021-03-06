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

namespace Leap\Core\DB\Connection {

	/**
	 * This class sets forth the functions for a database connection.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Leap\Core\DB\Connection
	 * @version 2014-07-04
	 */
	abstract class Driver extends \Leap\Core\Object {

		/**
		 * This variable stores the connection configurations.
		 *
		 * @access protected
		 * @var string
		 */
		protected $cache_key;

		/**
		 * This variable stores the last SQL command executed.
		 *
		 * @access protected
		 * @var \Leap\Core\DB\SQL\Command
		 */
		protected $command;

		/**
		 * This variable stores a reference to the data source.
		 *
		 * @access protected
		 * @var \Leap\Core\DB\DataSource
		 */
		protected $data_source;

		/**
		 * This variable stores a reference to the lock builder.
		 *
		 * @access protected
		 * @var \Leap\Core\DB\SQL\Lock\Builder
		 */
		protected $lock;

		/**
		 * This variable is used to store the connection's resource.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $resource;

		/**
		 * This method initializes the class with the specified data source.
		 *
		 * @access public
		 * @param \Leap\Core\DB\DataSource $data_source             the data source to be used
		 */
		public function __construct(\Leap\Core\DB\DataSource $data_source) {
			$this->cache_key = NULL;
			$this->command = new \Leap\Core\DB\SQL\Command();
			$this->data_source = $data_source;
			$this->lock = \Leap\Core\DB\SQL\Lock\Builder::factory($this);
			$this->resource = NULL;
		}

		/**
		 * This destructor ensures that the connection is closed.
		 *
		 * @access public
		 * @abstract
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->cache_key);
			unset($this->command);
			unset($this->data_source);
			unset($this->lock);
			unset($this->resource);
		}

		/**
		 * This method returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $key                                       the name of the property
		 * @return mixed                                            the value of the property
		 * @throws \Leap\Core\Throwable\InvalidProperty\Exception   indicates that the specified property is
		 *                                                          either inaccessible or undefined
		 */
		public function __get($key) {
			switch ($key) {
				case 'command':
					return $this->command;
				case 'data_source':
					return $this->data_source;
				case 'lock':
					return $this->lock;
				default:
					throw new \Leap\Core\Throwable\InvalidProperty\Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key));
			}
		}

		/**
		 * This method begins a transaction.
		 *
		 * @access public
		 * @abstract
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 */
		public abstract function begin_transaction();

		/**
		 * This method manages query caching.
		 *
		 * @access protected
		 * @param \Leap\Core\DB\SQL\Command $command                the SQL command being queried
		 * @param string $type                                      the return type that is being used
		 * @param \Leap\Core\DB\ResultSet $results                  the result set
		 * @return \Leap\Core\DB\ResultSet                          the result set for the specified
		 */
		protected function cache(\Leap\Core\DB\SQL\Command $command, $type, $results = NULL) {
			/*
			if ($this->data_source->cache->enabled) {
				if ($results !== NULL) {
					if ($this->data_source->cache->lifetime > 0) {
						\Kohana::cache($this->cache_key, $results, $this->data_source->cache->lifetime);
					}
					return $results;
				}
				else if ($this->data_source->cache->lifetime !== NULL) {
					$this->cache_key = '\\Leap\\Core\\DB\\Connection\\Driver::query("' . $this->data_source->id . '", "' . $type . '", "' . $command->text . '")';
					$results = \Kohana::cache($this->cache_key, NULL, $this->data_source->cache->lifetime);
					if (($results !== NULL) AND ! $this->data_source->cache->force) {
						return $results;
					}
				}
			}
			*/
			return $results;
		}

		/**
		 * This method closes an open connection.
		 *
		 * @access public
		 * @abstract
		 * @return boolean                                          whether an open connection was closed
		 */
		public abstract function close();

		/**
		 * This method commits a transaction.
		 *
		 * @access public
		 * @abstract
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 */
		public abstract function commit();

		/**
		 * This method processes an SQL command that will NOT return data.
		 *
		 * @access public
		 * @abstract
		 * @param \Leap\Core\DB\SQL\Command $command                the SQL command to be queried
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 */
		public abstract function execute(\Leap\Core\DB\SQL\Command $command);

		/**
		 * This method returns the last insert id.
		 *
		 * @access public
		 * @abstract
		 * @param string $table                                     the table to be queried
		 * @param string $column                                    the column representing the table's id
		 * @return integer                                          the last insert id
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the query failed
		 */
		public abstract function get_last_insert_id($table = NULL, $column = 'id');

		/**
		 * This method returns the connection's resource.
		 *
		 * @access public
		 * @return mixed                                            the resource being used
		 * @throws \Leap\Core\Throwable\Database\Exception          indicates that no connection has been
		 *                                                          established
		 */
		public function get_resource() {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\Database\Exception('Message: Unable to fetch resource. Reason: No connection has been established.');
			}
			return $this->resource;
		}

		/**
		 * This method is for determining whether a connection is established.
		 *
		 * @access public
		 * @abstract
		 * @return boolean                                          whether a connection is established
		 */
		public abstract function is_connected();

		/**
		 * This method opens a connection using the data source provided.
		 *
		 * @access public
		 * @throws \Leap\Core\Throwable\Database\Exception          indicates that there is problem with
		 *                                                          opening the connection
		 */
		public abstract function open();

		/**
		 * This method processes an SQL command that will return data.
		 *
		 * @access public
		 * @param \Leap\Core\DB\SQL\Command $command                the SQL command to be queried
		 * @param string $type                                      the return type to be used
		 * @return \Leap\Core\DB\ResultSet                          the result set
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the query failed
		 */
		public function query(\Leap\Core\DB\SQL\Command $command, $type = 'array') {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to query SQL command. Reason: Unable to find connection.');
			}
			$result_set = $this->cache($command, $type);
			if ($result_set !== NULL) {
				$this->command = $command;
				return $result_set;
			}
			$reader = \Leap\Core\DB\SQL\DataReader::factory($this, $command);
			$result_set = $this->cache($command, $type, new \Leap\Core\DB\ResultSet($reader, $type));
			$this->command = $command;
			return $result_set;
		}

		/**
		 * This method escapes a string to be used in an SQL command.
		 *
		 * @access public
		 * @param string $string                                    the string to be escaped
		 * @param char $escape                                      the escape character
		 * @return string                                           the quoted string
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that no connection could
		 *                                                          be found
		 *
		 * @license http://codeigniter.com/user_guide/license.html
		 *
		 * @see http://codeigniter.com/forums/viewthread/179202/
		 */
		public function quote($string, $escape = NULL) {
			static $removables = array(
				'/%0[0-8bcef]/',
				'/%1[0-9a-f]/',
				'/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S',
			);

			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
			}

			do {
				$string = preg_replace($removables, '', $string, -1, $count);
			}
			while ($count);

			$string = "'" . str_replace("'", "''", $string) . "'";

			if (is_string($escape) OR ! empty($escape)) {
				$string .= " ESCAPE '{$escape}'";
			}

			return $string;
		}

		/**
		 * This method creates a data reader for query the specified SQL command.
		 *
		 * @access public
		 * @param \Leap\Core\DB\SQL\Command $command                the SQL command to be queried
		 * @return \Leap\Core\DB\SQL\DataReader                     the SQL data reader
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the query failed
		 */
		public function reader(\Leap\Core\DB\SQL\Command $command) {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to create SQL data reader. Reason: Unable to find connection.');
			}
			$reader = \Leap\Core\DB\SQL\DataReader::factory($this, $command);
			$this->command = $command;
			return $reader;
		}

		/**
		 * This method rollbacks a transaction.
		 *
		 * @access public
		 * @abstract
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 */
		public abstract function rollback();

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This method returns a connection to the appropriate database based
		 * on the specified configurations.
		 *
		 * @access public
		 * @static
		 * @param \Leap\Core\DB\DataSource $data_source             the data source to be used
		 * @return \Leap\Core\DB\Connection\Driver                  the database connection
		 */
		public static function factory(\Leap\Core\DB\DataSource $data_source) {
			$data_type = '\\Leap\\Plugin\\DB\\' . $data_source->dialect . '\\Connection\\' . $data_source->driver;
			$connection = new $data_type($data_source);
			return $connection;
		}

	}

}
