<?php

########################################################################
# Extension Manager/Repository config file for ext "memolist".
#
# Auto generated 06-10-2011 13:36
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Memolist: API Functions to attach data to a FE session or user record',
	'description' => 'Memolist is a simple helper extension for everybody who needs to store data (like a memolist, a wishlist) that is attached to a website user (can be located in the users\' session or a logged-in user). Helpful for programmers only, as it is just a better abstraction to existing functions.',
	'category' => 'fe',
	'author' => 'Benjamin Mack',
	'author_email' => 'typo3@b13.de',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.7-6.1.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:4:{s:16:"ext_autoload.php";s:4:"f2dc";s:12:"ext_icon.gif";s:4:"7e66";s:17:"ext_localconf.php";s:4:"ba28";s:20:"Classes/Memolist.php";s:4:"20a2";}',
	'suggests' => array(
	),
);

?>