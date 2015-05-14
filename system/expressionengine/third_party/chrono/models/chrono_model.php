<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Chrono model
 *
 * @package chrono
 * @author TJ Draper <tj@caddis.co>
 * @link https://github.com/caddis/chrono
 * @copyright Copyright (c) 2015, Caddis Interactive, LLC
 */

class Chrono_model extends CI_Model
{
	/**
	 * Get years and months
	 *
	 * @param array $conf{
	 *     @var false|int|array $channelId
	 *     @var false|int|array $authorId
	 *     @var false|int|array $catId
	 *     @var false|int $limit
	 *     @var string $ySort Sort year "asc" or "desc"
	 *     @var string $mSort Sort month(s) "asc" or "desc"
	 *     @var bool $expired
	 *     @var bool $futureEntries
	 *     @var false|string|array $status
	 *     @var bool $negateStatus Where not in
	 * }
	 * @return array
	 */
	public function getYearsMonths($conf = array())
	{
		$defaultConf = array(
			'channelId' => false,
			'authorId' => false,
			'catId' => false,
			'limit' => false,
			'ySort' => 'desc',
			'mSort' => 'asc',
			'expired' => false,
			'futureEntries' => false,
			'status' => false,
			'negateStatus' => false
		);

		$conf = array_merge($defaultConf, $conf);

		// Make sure sorting parameters are correct
		$sorting = array(
			'asc',
			'desc'
		);

		if (! in_array($conf['ySort'], $sorting)) {
			$conf['ySort'] = 'desc';
		}

		if (! in_array($conf['mSort'], $sorting)) {
			$conf['mSort'] = 'asc';
		}

		$select = array(
			'FROM_UNIXTIME(entry_date, "%Y") AS year',
			'FROM_UNIXTIME(entry_date, "%c") AS short_digit',
			'FROM_UNIXTIME(entry_date, "%m") AS two_digit',
			'FROM_UNIXTIME(entry_date, "%b") AS short',
			'FROM_UNIXTIME(entry_date, "%M") AS "long"',
			'COUNT(*) AS month_total_entries'
		);

		$groupBy = array(
			'year',
			'two_digit'
		);

		ee()->db->select($select)
			->from('channel_titles CT')
			->group_by($groupBy)
			->order_by(
				'year ' . $conf['ySort'] . ', two_digit ' . $conf['mSort']
			);

		if ($conf['channelId']) {
			ee()->db->where_in('CT.channel_id', $conf['channelId']);
		}

		if ($conf['authorId']) {
			ee()->db->where_in('CT.author_id', $conf['authorId']);
		}

		if ($conf['catId']) {
			ee()->db->join('category_posts CP', 'CT.entry_id = CP.entry_id')
				->where_in('CP.cat_id', $conf['catId']);
		}

		if ($conf['expired']) {
			ee()->db->where(
				'(CT.expiration_date > ' . time() .
				' OR CT.expiration_date = 0)'
			);
		}

		if ($conf['futureEntries']) {
			ee()->db->where('CT.entry_date <' . time());
		}

		if ($conf['status']) {
			if ($conf['negateStatus'] === true) {
				ee()->db->where_not_in('status', $conf['status']);
			} else {
				ee()->db->where_in('status', $conf['status']);
			}
		} else {
			ee()->db->where('status', 'open');
		}

		// Run the query
		$query = ee()->db->get()->result_array();

		return $this->formatResults($query, $conf['limit']);
	}

	/**
	 * Build years and months
	 *
	 * @access private
	 * @param array $rawResults
	 * @param false|int $limit
	 * @return array
	 */
	private function formatResults($rawResults, $limit)
	{
		$return = array();
		$yearCount = 0;

		// Loop through the results
		foreach ($rawResults as $val) {
			$entryCount = (int) $val['month_total_entries'];
			$year = $val['year'];
			$yearIsSet = isset($return[$year]);

			// Remove the year from the value
			unset($val['year']);

			// If this is a new year, increment the year count
			if (! $yearIsSet) {
				$yearCount++;
			}

			// If the year count is greater than the limit, break loop
			if ($limit && $yearCount > $limit) {
				break;
			}

			// Add up the entry count of months to get total year count
			if (isset($return[$year])) {
				$prevCount = $return[$year]['year_total_entries'];
				$return[$year]['year_total_entries'] = $prevCount + $entryCount;
			} else {
				$return[$year]['year_total_entries'] = $entryCount;
			}

			// Set to return value
			$return[$year]['months'][$val['short_digit']] = $val;
		}

		return $return;
	}
}