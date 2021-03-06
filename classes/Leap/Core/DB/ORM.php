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

namespace Leap\Core\DB {

	/**
	 * This class provides a shortcut way to get the appropriate ORM builder class.
	 *
	 * @access public
	 * @class
	 * @package Leap\Core\DB
	 * @version 2014-07-03
	 */
	class ORM extends \Leap\Core\Object {

		/**
		 * This method returns an instance of the DB\ORM\Delete\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                                     the model's name
		 * @return \Leap\Core\DB\ORM\Delete\Proxy                   an instance of the class
		 */
		public static function delete($model) {
			$object = new \Leap\Core\DB\ORM\Delete\Proxy($model);
			return $object;
		}

		/**
		 * This method will wrap a string so that it can be processed by a query
		 * builder.
		 *
		 * @access public
		 * @static
		 * @param string $expr                                      the raw SQL expression
		 * @param array $params                                     an associated array of parameter
		 *                                                          key/values pairs
		 * @return \Leap\Core\DB\SQL\Expression                     the wrapped expression
		 */
		public static function expr($expr, Array $params = array()) {
			$object = new \Leap\Core\DB\SQL\Expression($expr, $params);
			return $object;
		}

		/**
		 * This method returns an instance of the DB\ORM\Insert\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                                     the model's name
		 * @return \Leap\Core\DB\ORM\Insert\Proxy                   an instance of the class
		 */
		public static function insert($model) {
			$object = new \Leap\Core\DB\ORM\Insert\Proxy($model);
			return $object;
		}

		/**
		 * This method returns an instance of the specified model.
		 *
		 * @access public
		 * @static
		 * @param string $model                                     the model's name
		 * @param array $primary_key                                the column values of the primary key
		 *                                                          that will be used to load the model
		 * @return mixed                                            an instance of the specified model
		 */
		public static function model($model, $primary_key = array()) {
			$model = \Leap\Core\DB\ORM\Model::factory($model);
			if ( ! empty($primary_key)) {
				if ( ! is_array($primary_key)) {
					$primary_key = array($primary_key);
				}
				$model_key = $model::primary_key();
				$count = count($model_key);
				for ($i = 0; $i < $count; $i++) {
					$column = $model_key[$i];
					$model->{$column} = $primary_key[$i];
				}
				$model->load();
			}
			return $model;
		}

		/**
		 * This method returns an instance of the appropriate pre-compiler for the
		 * specified model.
		 *
		 * @access public
		 * @static
		 * @param string $model                                     the model's name
		 * @return \Leap\Core\DB\SQL\Precompiler                    an instance of the pre-compiler
		 */
		public static function precompiler($model) {
			$model = \Leap\Core\DB\ORM\Model::model_name($model);
			$data_source = $model::data_source(\Leap\Core\DB\DataSource::MASTER_INSTANCE);
			$data_type = '\\Leap\\Plugin\\DB\\' . $data_source->dialect . '\\Precompiler';
			$object = new $data_type($data_source);
			return $object;
		}

		/**
		 * This method returns an instance of the DB\ORM\Select\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                                     the model's name
		 * @param array $columns                                    the columns to be selected
		 * @return \Leap\Core\DB\ORM\Select\Proxy                   an instance of the class
		 */
		public static function select($model, Array $columns = array()) {
			$object = new \Leap\Core\DB\ORM\Select\Proxy($model, $columns);
			return $object;
		}

		/**
		 * This method returns an instance of the DB\ORM\Update\Proxy.
		 *
		 * @access public
		 * @static
		 * @param string $model                                     the model's name
		 * @return \Leap\Core\DB\ORM\Update\Proxy                   an instance of the class
		 */
		public static function update($model) {
			$object = new \Leap\Core\DB\ORM\Update\Proxy($model);
			return $object;
		}

	}

}