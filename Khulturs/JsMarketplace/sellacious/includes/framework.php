<?php
/**
 * @package    Sellacious.Application
 *
 * @copyright  Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Joomla system checks.
@ini_set('magic_quotes_runtime', 0);

// System includes
require_once JPATH_LIBRARIES . '/import.legacy.php';

require_once JPATH_PLATFORM . '/loader.php';

JLoader::registerNamespace('Sellacious', JPATH_LIBRARIES . '/sellacious/objects');
JLoader::registerAlias('JToolbarHelper', 'Sellacious\Toolbar\ToolbarHelper');

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Set system error handling
JError::setErrorHandling(E_NOTICE, 'message');
JError::setErrorHandling(E_WARNING, 'message');
JError::setErrorHandling(E_ERROR, 'message', array('JError', 'customErrorPage'));

$version = new JVersion;

// Installation check, and check on removal of the install directory.
if (!file_exists(JPATH_CONFIGURATION . '/configuration.php')
	|| (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
	|| (file_exists(JPATH_INSTALLATION . '/index.php') && (false === $version->isInDevelopmentState())))
{
	if (file_exists(JPATH_INSTALLATION . '/index.php'))
	{
		header('Location: ../installation/index.php');

		exit();
	}
	else
	{
		echo 'No configuration file found and no installation code available. Exiting...';

		exit;
	}
}

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// System configuration.
$config = new JConfig;

// Set the error_reporting
switch ($config->error_reporting)
{
	case 'default':
	case '-1':
		break;

	case 'none':
	case '0':
		error_reporting(0);

		break;

	case 'simple':
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set('display_errors', 1);

		break;

	case 'maximum':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		break;

	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
		ini_set('log_error', 1);
		ini_set('error_log', JPATH_BASE . '/error.log');

		break;

	default:
		error_reporting($config->error_reporting);
		ini_set('display_errors', 1);

		break;
}

define('JDEBUG', $config->debug);

unset($config);
