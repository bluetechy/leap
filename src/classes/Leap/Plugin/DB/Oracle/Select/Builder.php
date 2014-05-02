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

namespace Leap\Plugin\DB\Oracle\Select {

	/**
	 * This class builds an Oracle select statement.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\Oracle\Select
	 * @version 2014-05-01
	 *
	 * @see http://download.oracle.com/docs/cd/B14117_01/server.101/b10759/statements_10002.htm
	 */
	class Builder extends \Leap\Core\DB\SQL\Select\Builder {

		/**
		 * This method combines another SQL statement using the specified operator.
		 *
		 * @access public
		 * @override
		 * @param string $operator                                  the operator to be used to append
		 *                                                          the specified SQL statement
		 * @param string $statement                                 the SQL statement to be appended
		 * @return \Leap\Core\DB\SQL\Select\Builder                 a reference to the current instance
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates an invalid SQL build instruction
		 */
		public function combine($operator, $statement) {
			if ($statement instanceof \Leap\Core\DB\SQL\Select\Builder) {
				$statement = $statement->statement(FALSE)->text;
			}
			else if ($statement instanceof \Leap\Core\DB\SQL\Command) {
				$statement = \Leap\Core\DB\SQL\Command::trim($statement->text);
			}
			else {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Invalid SQL build instruction. Reason: May only combine a SELECT statement.', array(':operator' => $operator, ':statement' => $statement));
			}
			$operator = $this->precompiler->prepare_operator($operator, 'SET');
			$this->data['combine'][] = "{$operator} ({$statement})";
			return $this;
		}

		/**
		 * This method returns the SQL statement.
		 *
		 * @access public
		 * @override
		 * @param boolean $terminated                               whether to add a semi-colon to the end
		 *                                                          of the statement
		 * @return \Leap\Core\DB\SQL\Command                        the SQL statement
		 *
		 * @see http://www.oracle.com/technetwork/issue-archive/2006/06-sep/o56asktom-086197.html
		 * @see http://stackoverflow.com/questions/470542/how-do-i-limit-the-number-of-rows-returned-by-an-oracle-query
		 */
		public function statement($terminated = TRUE) {
			$sql = 'SELECT ';

			if ($this->data['distinct']) {
				$sql .= 'DISTINCT ';
			}

			$sql .= ( ! empty($this->data['column']))
				? implode(', ', $this->data['column'])
				: $this->data['wildcard'];

			if ($this->data['from'] !== NULL) {
				$sql .= " FROM {$this->data['from']}";
			}

			foreach ($this->data['join'] as $join) {
				$sql .= " {$join[0]}";
				if ( ! empty($join[1])) {
					$sql .= ' ON (' . implode(' AND ', $join[1]) . ')';
				}
				else if ( ! empty($join[2])) {
					$sql .= ' USING (' . implode(', ', $join[2]) . ')';
				}
			}

			if ( ! empty($this->data['where'])) {
				$append = FALSE;
				$sql .= ' WHERE ';
				foreach ($this->data['where'] as $where) {
					if ($append AND ($where[1] != \Leap\Core\DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
						$sql .= " {$where[0]} ";
					}
					$sql .= $where[1];
					$append = ($where[1] != \Leap\Core\DB\SQL\Builder::_OPENING_PARENTHESIS_);
				}
			}

			if ( ! empty($this->data['group_by'])) {
				$sql .= ' GROUP BY ' . implode(', ', $this->data['group_by']);
			}

			if ( ! empty($this->data['having'])) {
				$append = FALSE;
				$sql .= ' HAVING ';
				foreach ($this->data['having'] as $having) {
					if ($append AND ($having[1] != \Leap\Core\DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
						$sql .= " {$having[0]} ";
					}
					$sql .= $having[1];
					$append = ($having[1] != \Leap\Core\DB\SQL\Builder::_OPENING_PARENTHESIS_);
				}
			}

			foreach ($this->data['combine'] as $combine) {
				$sql .= " {$combine}";
			}

			if ( ! empty($this->data['order_by'])) {
				$sql .= ' ORDER BY ' . implode(', ', $this->data['order_by']);
			}

			if (($this->data['limit'] > 0) AND ($this->data['offset'] > 0)) {
				$max_row_to_fetch = $this->data['offset'] + ($this->data['limit'] - 1);
				$min_row_to_fetch = $this->data['offset'];
				$sql = "SELECT * FROM (SELECT \"t0\".*, ROWNUM AS \"rn\" FROM ({$sql}) \"t0\" WHERE ROWNUM <= {$max_row_to_fetch}) WHERE \"rn\" >= {$min_row_to_fetch}";
			}
			else if ($this->data['limit'] > 0) {
				$sql = "SELECT * FROM ({$sql}) WHERE ROWNUM <= {$this->data['limit']}";
			}
			else if ($this->data['offset'] > 0) {
				$sql = "SELECT * FROM ({$sql}) WHERE ROWNUM >= {$this->data['offset']}";
			}

			if ($terminated) {
				$sql .= ';';
			}

			$command = new \Leap\Core\DB\SQL\Command($sql);
			return $command;
		}

	}

}