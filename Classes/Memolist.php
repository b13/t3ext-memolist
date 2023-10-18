<?php

declare(strict_types=1);

namespace B13\Memolist;

/*
 * This file is part of b13 Memolist extension.
 * (c) 2011-2018 Benjamin Mack (benni@typo3.org)
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Memolist
{
    protected string $type = 'user';
    protected string $namespace = 'tx_memolist';
    protected ?FrontendUserAuthentication $userObj = null;

    public function __construct(string $type = null, FrontendUserAuthentication $userObj = null)
    {
        // check if there is a user object
        $this->setUserObj($userObj ?: $GLOBALS['TSFE']->fe_user);

        if ($type) {
            $this->type = $type;
        } else {
            // check if the user is logged in, if so, use the
            // user's record, otherwise the session cookie
            if (isset($this->userObj->user['uid']) && $this->userObj->user['uid'] > 0) {
                $this->type = 'user';
            }
        }
    }

    public function setUserObj(FrontendUserAuthentication $userObj)
    {
        $this->userObj = $userObj;
    }

    public function getMemolist(): array
    {
        $memolist = $this->userObj->getKey($this->type, $this->namespace);
        if (!is_array($memolist)) {
            $memolist = [];
        }
        return $memolist;
    }

    protected function persist(array $memos)
    {
        // store the new memos
        $this->userObj->setKey($this->type, $this->namespace, $memos);
        // persist in DB, done via TYPO3 API
        $this->userObj->storeSessionData();
    }

    /**
     * @param mixed $item the item to check
     * @api
     */
    public function isInMemoList($item): bool
    {
        $memoList = $this->getMemoList();
        if (count($memoList) > 0) {
            return in_array($item, $memoList);
        }
        return false;
    }

    /**
     * @param mixed $item the item to store in the memo list
     */
    public function addItemToMemoList($item, string $key = ''): int
    {
        $memoList = $this->getMemoList();
        if ($item) {
            if ($key === '') {
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
     * @param mixed $item the item to remove from the memo list
     */
    public function removeItemFromMemoList($item, string $key = ''): int
    {
        $memoList = $this->getMemoList();
        if ($item) {
            if ($key === '') {
                if (in_array($item, $memoList)) {
                    $key = array_search($item, $memoList);
                }
            }

            unset($memoList[$key]);
            $this->persist($memoList);
        }
        return count($memoList);
    }

    public function bulkAddAndRemoveItems(array $addedItems = [], array $removedItems = []): void
    {
        $memoList = $this->getMemoList();

        foreach ($addedItems as $item) {
            if ($item && !in_array($item, $memoList)) {
                $memoList[] = $item;
            }
        }

        foreach ($removedItems as $item) {
            if ($item && in_array($item, $memoList)) {
                $key = array_search($item, $memoList);
                unset($memoList[$key]);
            }
        }

        $this->persist($memoList);
    }

    public function clearMemolist(): void
    {
        $this->persist([]);
    }

    public function getNumberOfItemsInMemoList(): int
    {
        $memoList = $this->getMemoList();
        return count($memoList);
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * PUBLIC FUNCTIONS FOR AJAX CALLS
     * need some major overhaul
     */
    public function getAjaxMemoListItems(): void
    {
        $t3Basket = $this->userObj->getKey($this->type, $this->namespace);
        if (!is_array($t3Basket)) {
            $t3Basket = [];
        }
        $list = [];
        foreach ($t3Basket as $memoList) {
            $list = implode(',', $memoList);
        }
        echo '[' . $list . ']';
    }

    public function addAjaxItemToMemoList(): string
    {
        $namespace = GeneralUtility::_GP('namespace');
        if ($namespace) {
            $this->setNamespace($namespace);
        }

        $item = GeneralUtility::_GP('memo');
        $item = (int)$item;

        if ($item > 0) {
            $this->addItemToMemoList($item);
            return '1';
        }
        return '0';
    }

    public function removeAjaxItemToMemoList(): string
    {
        $namespace = GeneralUtility::_GP('namespace');
        if ($namespace) {
            $this->setNamespace($namespace);
        }

        $item = GeneralUtility::_GP('memo');
        $item = (int)$item;

        if ($item > 0) {
            $this->removeItemFromMemoList($item);
            return '1';
        }

        $key = GeneralUtility::_GP('key');
        $key = (string)$key;
        $this->removeItemFromMemoList($item, $key);
        return '1';
    }
}
