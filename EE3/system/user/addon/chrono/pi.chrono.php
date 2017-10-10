<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Chrono plugin
 *
 * @package chrono
 * @author TJ Draper <tj@buzzingpixel.com>
 * @link https://github.com/tjdraper/chrono
 * @copyright Copyright (c) 2017 BuzzingPixel, LLC
 */

class Chrono
{
	public function __construct()
	{
		ee()->load->model('chrono_model');

		// Get parameters
		$channelId = $this->getParam('channel_id');
		$this->channelId = $channelId ? explode('|', $channelId) : false;

		$authId = $this->getParam('author_id');
		$this->authId = $authId ? explode('|', $authId) : false;

		$catId = $this->getParam('category_id');
		$this->catId = $catId ? explode('|', $catId) : false;

		$this->limit = (int) $this->getParam('limit');

		$this->ySort = $this->getParam('year_sort') === 'asc' ? 'asc' : 'desc';
		$this->mSort = $this->getParam('month_sort') === 'asc' ? 'asc' : 'desc';

		$expired = $this->getParam('show_expired');
		$this->expired = $expired === 'true' || $expired === 'yes';

		$futureEntries = $this->getParam('show_future_entries');
		$this->futureEntries = $futureEntries === 'true' || $futureEntries === 'yes';

		$this->namespace = $this->getParam('namespace', 'chrono') . ':';

		$status = $this->getParam('status', 'open');
		$this->negateStatus = substr($status, 0, 4) === 'not ';

		if ($this->negateStatus === true) {
			$status = substr($status, 4);
		}

		$this->status = explode('|', $status);
	}

	/**
	 * Process the archive tag pair
	 *
	 * @return string
	 */
	public function archive()
	{
		// Get the years and months from the model
		$yearsMonths = ee()->chrono_model->getYearsMonths(array(
			'channelId' => $this->channelId,
			'authorId' => $this->authId,
			'catId' => $this->catId,
			'limit' => $this->limit,
			'ySort' => $this->ySort,
			'mSort' => $this->mSort,
			'expired' => $this->expired,
			'futureEntries' => $this->futureEntries,
			'status' => $this->status,
			'negateStatus' => $this->negateStatus
		));

		if (! $yearsMonths) {
			return false;
		}

		// Format the vars and return the parsed variables
		return ee()->TMPL->parse_variables(
			ee()->TMPL->tagdata,
			$this->formatVars($yearsMonths)
		);
	}

	/**
	 * Fetch tag parameter
	 *
	 * @access private
	 * @return string|bool String or param not set returns (bool) false
	 */
	private function getParam($param = false, $default = false)
	{
		if (! $param) {
			return false;
		}

		return ee()->TMPL->fetch_param($param, $default);
	}

	/**
	 * Format Variables
	 *
	 * @access private
	 * @return array
	 */
	private function formatVars($yearsMonths)
	{
		$returnData = array();
		$yearKey = 0;

		// Loop through the years
		foreach ($yearsMonths as $yKey => $yVal) {
			$returnData[$yearKey][$this->namespace . 'year'] = $yKey;
			$returnData[$yearKey][$this->namespace . 'year_count'] = $yearKey + 1;
			$returnData[$yearKey][$this->namespace . 'year_total_entries'] = $yVal['year_total_entries'];

			$monthKey = 0;

			// Loop through the months
			foreach ($yVal['months'] as $mKey => $mVal) {
				$month = array(
					$this->namespace . 'short_digit' => $mVal['short_digit'],
					$this->namespace . 'two_digit' => $mVal['two_digit'],
					$this->namespace . 'short' => $mVal['short'],
					$this->namespace . 'long' => $mVal['long'],
					$this->namespace . 'month_count' => $monthKey + 1,
					$this->namespace . 'month_total_entries' => $mVal['month_total_entries']
				);

				$returnData[$yearKey][$this->namespace . 'months'][$monthKey] = $month;

				$monthKey++;
			}

			$yearKey++;
		}

		// Get the year total
		$yearTotal = count($returnData);

		// Set the year and month totals
		foreach ($returnData as $key => $val) {
			$returnData[$key][$this->namespace . 'year_total'] = $yearTotal;

			$monthCount = count($val[$this->namespace . 'months']);
			$returnData[$key][$this->namespace . 'month_total'] = $monthCount;
		}

		return $returnData;
	}
}