<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Benjamin Mack (benni@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * call it wishlist, lookbook, 
 * but this class helps you deal with all the simple functions to store the items
 *
 */
class Tx_Memolist {

	protected $type = 'ses';	// can be "user" or "ses"
	protected $namespace = 'tx_memolist';	// namespace in session key
	protected $userObj = NULL;

	public function __construct($type = NULL, $userObj = NULL) {
		if ($userObj !== NULL) {
			$this->userObj = $userObj;
		} else {
			$this->userObj = $GLOBALS['TSFE']->fe_user;
		}

		if ($type) {
			$this->type = $type;
		} else {
			// check if the user is logged in, if so, use the
			// user's record, otherwise the session cookie
			if ($this->userObj->user['uid'] > 0) {
				$this->type = 'user';
			} else {
				$this->type = 'ses';
			}
		}
	}
	
	public function getMemolist() {
		$t3Basket = $this->userObj->getKey($this->type, 'recs');
		if (!is_array($t3Basket)) {
			$t3Basket = array($this->namespace => array());
		}
		return $t3Basket[$this->namespace];
		
	}
	
	public function updateMemoList($items) {
		foreach($items as $item => $counter) {
			$newList[$item] = $counter;
		}
		$this->storeMemoList($newList);
	}
	
	protected function storeMemolist($memos) {
		$t3Basket = $this->userObj->getKey($this->type, 'recs');
		$t3Basket[$this->namespace] = $memos;
		$GLOBALS['TSFE']->fe_user->setKey($this->type, 'recs', $t3Basket);
	}
	
	// clears the memo list for the current session
	public function clearMemolist($memos) {
		$t3Basket[$this->namespace] = array();
		$GLOBALS['TSFE']->fe_user->setKey($this->type, 'recs', $t3Basket);
	}

	/**
	 * check if it's in the users' memo list
	 *
	 * @return boolean
	 * @api
	 */
	public function isInMemoList($item) {
		$memoList = $this->getMemoList();
		if (count($memoList)) {
			return (in_array($item, $memoList));
		} else {
			return FALSE;
		}
	}

	
	public function bulkAddAndRemoveItems($addedItems, $removedItems) {
		if (is_array($addedItems) || is_array($removedItems)) {
			$memoList = $this->getMemoList();

			if (is_array($addedItems)) {
				foreach ($addedItems as $item) {
					if ($item && !in_array($item, $memoList)) {
						$memoList[] = $item;
					}
				}
			}

			if (is_array($removedItems)) {
				foreach ($removedItems as $item) {
					if ($item && in_array($item, $memoList)) {
						$key = array_search($item, $memoList);
						unset($memoList[$key]);
					}
				}
			}

			$this->storeMemolist($memoList);
		}
	}


	/**
	 * adds an item to the memo list
	 * 
	 * @param	mixed	$item, the item to store in the memo list
	 * @return	integer	the number of items in the users' memo list
	 */
	public function addItemToMemoList($item) {
		$memoList = $this->getMemoList();
		if ($item && !isset($memoList[$item])) {
			$memoList[$item] = '1';
			$this->storeMemolist($memoList);
		}
		return count($memoList);
	}


	/**
	 * removes an item from the memo list
	 *
	 * @param	mixed	$item, the item to remove from the memo list
	 * @return	string	the number of items in the memo list
	 */
	public function removeItemFromMemoList($item) {
		$memoList = $this->getMemoList();
		if ($item && isset($memoList[$item])) {
			unset($memoList[$item]);
			$this->storeMemolist($memoList);
		}
		return count($memoList);
	}

	/**
	 * returns the number of items in the users' memo list
	 *
	 * @return	integer	the number of items in the memo list
	 */
	public function getNumberOfItemsInMemoList() {
		$memoList = $this->getMemoList();
		return count($memoList);
	}
	
	
	
	
	/*** PUBLIC FUNCTIONS FOR AJAX CALLS ***/

	public function getAjaxMemoListItems() {
		$t3Basket = $this->userObj->getKey($this->type, 'recs');
		if (!is_array($t3Basket)) {
			$t3Basket = array($this->namespace => array());
		}
		foreach($t3Basket as $memoList) {
		 $list = implode(',', $memoList);
		}
		echo('['.$list.']');
	}
	
	
	public function addAjaxItemToMemoList() {
		$item = t3lib_div::_GP('memo');
		$item = intval($item);
		if ($item > 0) {
			$this->addItemToMemoList($item);
			return '1';
		}
		return '0';
	}

	public function removeAjaxItemToMemoList() {
		$item = t3lib_div::_GP('memo');
		$item = intval($item);
		if ($item > 0) {
			$this->removeItemFromMemoList($item);
			return '1';
		}
		return '0';
	}
	

}

?>