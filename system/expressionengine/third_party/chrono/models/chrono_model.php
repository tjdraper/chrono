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
	 *     @var false|string $ySort Sort year "asc" or "desc"
	 *     @var false|string $mSort Sort month(s) "asc" or "desc"
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
			'ySort' => false,
			'mSort' => false,
			'expired' => false,
			'futureEntries' => false,
			'status' => false,
			'negateStatus' => false
		);

		$conf = array_merge($defaultConf, $conf);

		ee()->db->select('CT.entry_date')
			->from('channel_titles CT');

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

		$query = ee()->db
			->order_by('CT.entry_date', 'desc')
			->get()
			->result_array();

		return $this->formatResults(
			$query,
			$conf['ySort'],
			$conf['mSort']
		);
	}

	/**
	 * Build years and months
	 *
	 * @access private
	 * @param array $rawResults
	 * @param false|string $ySort "asc" or "desc"
	 * @param false|string $mSort "asc" or "desc"
	 * @return array
	 */
	private function formatResults($rawResults, $ySort, $mSort)
	{
		$returnData = array();

		// Loop over the raw results
		foreach ($rawResults as $key => $val) {
			// Format the dates and explode into something usable
			$date = ee()->localize->format_date(
				'%Y|%F|%M|%m|%n',
				$val['entry_date']
			);
			$date = explode('|', $date);

			// Set total entries for each year
			if (! isset($returnData[$date[0]]['year_total_entries'])) {
				$returnData[$date[0]]['year_total_entries'] = 1;
			} else {
				$returnData[$date[0]]['year_total_entries']++;
			}

			// Populate the month if it doesn't exist
			if (! isset($returnData[$date[0]]['months'][$date[4]])) {
				$thisMonth = array(
					'short_digit' => $date[4],
					'two_digit' => $date[3],
					'short' => $date[2],
					'long' => $date[1]
				);

				$returnData[$date[0]]['months'][$date[4]] = $thisMonth;
			}

			// Add up the total entries for each month
			if (! isset($returnData[$date[0]]['months'][$date[4]]['month_total_entries'])) {
				$returnData[$date[0]]['months'][$date[4]]['month_total_entries'] = 1;
			} else {
				$returnData[$date[0]]['months'][$date[4]]['month_total_entries']++;
			}
		}

		// Sort the variables
		if ($ySort === 'asc') {
			$returnData = array_reverse($returnData, true);
		}

		if ($mSort === 'asc') {
			foreach ($returnData as $key => $val) {
				$returnData[$key] = array_reverse($val, true);
			}
		}

		return $returnData;
	}
}