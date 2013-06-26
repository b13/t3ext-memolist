<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011-2013 Benjamin Mack (benni@typo3.org)
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
 * but this class helps you deal with all the simple 
 * functions to store the items
 * if no "type" 
 */
class Tx_Memolist {


	/**
	 * can be "user" or "ses", where it should be saved
	 */
	protected $type = 'user';


	/**
	 * namespace in session key, "tx_memolist" by default
	 */
	protected $namespace = 'tx_memolist';


	/**
	 * the FE user where the whole memolist should be attached
	 */
	protected $userObj = NULL;


	/**
	 * constructor for a memolist, with the two configuration
	 * options
	 *
	 * @param string $type ("user" or "ses")
	 * @param string $userObj (tslib_tsfeuserAuth)
	 */
	public function __construct($type = NULL, $userObj = NULL) {

			// check if there is a user object
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
			}
		}
	}
	
	/**
	 * returns the whole memolist as an array
	 *
	 * @return array
	 * @api
	 */
	public function getMemolist() {
		$memolist = $this->userObj->getKey($this->type, $this->namespace);
		if (!is_array($memolist)) {
			$memolist = array();
		}
		return $memolist;
		
	}

	/**
	 * internal function to store a new memolist in the DB / session
	 * in the userfunc
	 *
	 * @param $memos
	 */
	protected function persist($memos) {
		$GLOBALS['TSFE']->fe_user->setKey($this->type, $this->namespace, $memos);
	}

	/**
	 * check if it's in the users' memo list
	 *
	 * @param mixed $item the item to check
	 * @return boolean
	 * @api
	 */
	public function isInMemoList($item) {
		$memoList = $this->getMemoList();
		if (count($memoList) > 0) {
			return (in_array($item, $memoList));
		} else {
			return FALSE;
		}
	}

	/**
	 * adds an item to the memo list
	 * 
	 * @param	mixed	$item, the item to store in the memo list
	 * @return	integer	the number of items in the users' memo list
	 */
	public function addItemToMemoList($item, $key = FALSE) {
		$memoList = $this->getMemoList();
		if ($item) {
			if ($key === FALSE) {
				if (!in_array($item, $memoList)) {
					$memoList[] = $item;
				}
			} else {
				$memoList[$key] = $item;
			}
			$this->persist($memoList);
		}
		return count($memoList);
	}


	/**
	 * removes an item from the memo list
	 *
	 * @param	mixed	$item, the item to remove from the memo list
	 * @return	string	the number of items in the memo list
	 */
	public function removeItemFromMemoList($item, $key = FALSE) {
		$memoList = $this->getMemoList();
		if ($item) {
			if ($key === FALSE) {
				if (in_array($item, $memoList)) {
					$key = array_search($item, $memoList);
					unset($memoList[$key]);
				}
			} else {
				if (isset($memoList[$key])) {
					unset($memoList[$key]);
				}
			}
			$this->persist($memoList);
		}
		return count($memoList);
	}


	
	/**
	 * one function to add and remove items to the current
	 * memolist
	 * 
	 * @param array $addedItems the items to add
	 * @param array $removedItems the items that should be removed
	 * @return void
	 */
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

			$this->persist($memoList);
		}
	}

	/**
	 * removes all items from the memo list list
	 *
	 * @return	void
	 */
	public function clearMemolist() {
		$this->persist(array());
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
	
	/**
	 * sets the namespace where the memos should be saved
	 * 
	 * @param string $namespace the string that acts as a namespace
	 * @return void
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}
	
	/**
	 * returns the namespace currently in use
	 *
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}


	
	/**
	 *
	 * PUBLIC FUNCTIONS FOR AJAX CALLS
	 * need some major overhaul
	 *
	 */
	public function getAjaxMemoListItems() {
		$t3Basket = $this->userObj->getKey($this->type, $this->namespace);		
		if (!is_array($t3Basket)) {
			$t3Basket = array();
		}
		foreach($t3Basket as $memoList) {
		 $list = implode(',', $memoList);
		}
		echo('['.$list.']');
	}
	
	
	public function addAjaxItemToMemoList() {

		$namespace = t3lib_div::_GP('namespace');
		if ($namespace) {
			$this->setNamespace($namespace);
		}

		$item = t3lib_div::_GP('memo');
		$item = intval($item);

		if ($item > 0) {
			$this->addItemToMemoList($item);
			return '1';
		}
		return '0';
	}

	public function removeAjaxItemToMemoList() {

		$namespace = t3lib_div::_GP('namespace');
		if ($namespace) {
			$this->setNamespace($namespace);
		}

		$item = t3lib_div::_GP('memo');
		$item = intval($item);
	
		if ($item > 0) {
			$this->removeItemFromMemoList($item);
			return '1';
		} else {

			$key = t3lib_div::_GP('key');
			$key = intval($key);
			$this->removeItemFromMemoList($item, $key);
			return '1';
		}
		return '0';
	}
}
