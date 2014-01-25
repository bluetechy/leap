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

/**
 * This class builds a PostgreSQL select statement.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-02-27
 *
 * @see http://www.postgresql.org/docs/9.0/static/sql-select.html
 *
 * @abstract
 */
abstract class Base\DB\PostgreSQL\Select\Builder extends DB\SQL\Select\Builder {

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @override
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
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
				if ($append AND ($where[1] != DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
					$sql .= " {$where[0]} ";
				}
				$sql .= $where[1];
				$append = ($where[1] != DB\SQL\Builder::_OPENING_PARENTHESIS_);
			}
		}

		if ( ! empty($this->data['group_by'])) {
			$sql .= ' GROUP BY ' . implode(', ', $this->data['group_by']);
		}

		if ( ! empty($this->data['having'])) {
			$append = FALSE;
			$sql .= ' HAVING ';
			foreach ($this->data['having'] as $having) {
				if ($append AND ($having[1] != DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
					$sql .= " {$having[0]} ";
				}
				$sql .= $having[1];
				$append = ($having[1] != DB\SQL\Builder::_OPENING_PARENTHESIS_);
			}
		}

		if ( ! empty($this->data['order_by'])) {
			$sql .= ' ORDER BY ' . implode(', ', $this->data['order_by']);
		}

		if ($this->data['limit'] > 0) {
			$sql .= " LIMIT {$this->data['limit']}";
		}

		if ($this->data['offset'] > 0) {
			$sql .= " OFFSET {$this->data['offset']}";
		}

		foreach ($this->data['combine'] as $combine) {
			$sql .= " {$combine}";
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
