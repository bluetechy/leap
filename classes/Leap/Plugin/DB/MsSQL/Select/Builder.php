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

namespace Leap\Plugin\DB\MsSQL\Select {

	/**
	 * This class builds a MS SQL select statement.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\MsSQL\Select
	 * @version 2015-08-23
	 *
	 * @see https://msdn.microsoft.com/en-us/library/aa259187%28v=sql.80%29.aspx
	 * @see http://msdn.microsoft.com/en-us/library/aa260662%28v=sql.80%29.aspx
	 */
	class Builder extends \Leap\Core\DB\SQL\Select\Builder {

		/**
		 * This method returns the SQL command.
		 *
		 * @access public
		 * @override
		 * @param boolean $terminated           whether to add a semi-colon to the end
		 *                                      of the statement
		 * @return string                       the SQL command
		 *
		 * @see http://www.leghumped.com/blog/2007/12/09/limit-and-offset-clauses-in-mssql/
		 */
		public function command($terminated = TRUE) {
			$text = 'SELECT ';

			if ($this->data['distinct']) {
				$text .= 'DISTINCT ';
			}

			if ($this->data['limit'] > 0) {
				$text .= "TOP {$this->data['limit']} ";
			}

			$columns_sql = ( ! empty($this->data['column']))
				? implode(', ', $this->data['column'])
				: $this->data['wildcard'];

			$text .= $columns_sql;

			if ($this->data['from'] !== NULL) {
				$text .= " FROM {$this->data['from']}";
			}

			foreach ($this->data['join'] as $join) {
				$text .= " {$join[0]}";
				if ( ! empty($join[1])) {
					$text .= ' ON (' . implode(' AND ', $join[1]) . ')';
				}
				else if ( ! empty($join[2])) {
					$text .= ' USING (' . implode(', ', $join[2]) . ')';
				}
			}

			$where_sql = '';

			if ( ! empty($this->data['where'])) {
				$append = FALSE;
				$where_sql = ' WHERE ';
				foreach ($this->data['where'] as $where) {
					if ($append AND ($where[1] != \Leap\Core\DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
						$where_sql .= " {$where[0]} ";
					}
					$where_sql .= $where[1];
					$append = ($where[1] != \Leap\Core\DB\SQL\Builder::_OPENING_PARENTHESIS_);
				}

				$text .= $where_sql;
			}

			if ( ! empty($this->data['group_by'])) {
				$text .= ' GROUP BY ' . implode(', ', $this->data['group_by']);
			}

			if ( ! empty($this->data['having'])) {
				$append = FALSE;
				$text .= ' HAVING ';
				foreach ($this->data['having'] as $having) {
					if ($append AND ($having[1] != \Leap\Core\DB\SQL\Builder::_CLOSING_PARENTHESIS_)) {
						$text .= " {$having[0]} ";
					}
					$text .= $having[1];
					$append = ($having[1] != \Leap\Core\DB\SQL\Builder::_OPENING_PARENTHESIS_);
				}
			}

			if ( ! empty($this->data['order_by'])) {
				$text .= ' ORDER BY ' . implode(', ', $this->data['order_by']);
			}

			if (($this->data['offset'] >= 0) AND ($this->data['limit'] > 0) AND ! empty($this->data['order_by'])) {
				$text = 'SELECT [outer].* FROM (';
				$text .= 'SELECT ROW_NUMBER() OVER(ORDER BY ' . implode(', ', $this->data['order_by']) . ') as ROW_NUMBER, ' . $columns_sql . ' FROM ' . $this->data['from'] . ' ' . $where_sql;
				$text .= ') AS [outer] ';
				$text .= 'WHERE [outer].[ROW_NUMBER] BETWEEN ' . ($this->data['offset'] + 1) . ' AND ' . ($this->data['offset'] + $this->data['limit']);
				$text .= ' ORDER BY [outer].[ROW_NUMBER]';
			}

			foreach ($this->data['combine'] as $combine) {
				$text .= " {$combine}";
			}

			if ($terminated) {
				$text .= ';';
			}

			$command = new \Leap\Core\DB\SQL\Command($text);
			return $command;
		}

	}

}