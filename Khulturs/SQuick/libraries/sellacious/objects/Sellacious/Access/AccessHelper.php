<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Access;

use Exception;
use JFactory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;
use JPath;
use Sellacious\User\User;
use Sellacious\User\UserGroupHelper;
use SimpleXMLElement;

defined('_JEXEC') or die;

/**
 * AccessHelper class
 *
 * @since   2.0.0
 */
class AccessHelper
{
	/**
	 * Get the asset id for the concern asset object for which permissions are to be set.
	 *
	 * @param   string  $component  The component name
	 *
	 * @return  int
	 *
	 * @since   2.0.0
	 */
	public static function getAssetId($component)
	{
		if (!$component || $component === 'root.1')
		{
			return 1;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('id'))
			->from($db->qn('#__sellacious_permissions'))
			->where($db->qn('name') . ' = ' . $db->q($component));

		return (int) $db->setQuery($query)->loadResult();
	}

	/**
	 * Method to return a list of actions from the component's access.xml file for which permissions can be set
	 *
	 * @param   string  $component  The component name to load access from
	 * @param   string  $section    The access section name
	 *
	 * @return  \stdClass[]  The list of actions available
	 *
	 * @since   2.0.0
	 */
	public static function getActions($component, $section = 'component')
	{
		$groups  = static::getActionGroups($component, $section);
		$actions = ArrayHelper::getColumn($groups, 'actions');

		return array_reduce($actions, 'array_merge', array());
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set
	 *
	 * @param   string  $component  The component name to load access from
	 * @param   string  $section    The access section name
	 *
	 * @return  \stdClass[]  The list of actions available
	 *
	 * @since   2.0.0
	 */
	public static function getActionGroups($component, $section = 'component')
	{
		// Get the actions for the asset.
		$groups = array();

		if (!$component)
		{
			// Global configurations
			$file = JPath::clean(JPATH_SELLACIOUS . '/access.xml');
		}
		else
		{
			// Component configurations
			$file = JPath::clean(JPATH_SELLACIOUS . '/components/' . $component . '/access.xml');
		}

		if (is_file($file) && is_readable($file))
		{
			$xml = simplexml_load_file($file);
		}

		if (!isset($xml) || !($xml instanceof SimpleXMLElement))
		{
			return array();
		}

		$xpath  = $section ? "/access/section[@name='$section']" : '/access/section';

		// Fetch flat actions
		$xml_actions_general = $xml->xpath("$xpath/action[@name][@title][@description]");

		if (!empty($xml_actions_general))
		{
			$groups['0'] = (object) array(
				'name'    => '',
				'title'   => '',
				'actions' => array(),
			);

			foreach ($xml_actions_general as $xml_action)
			{
				$a_name = (string) $xml_action['name'];

				$groups['0']->actions[$a_name] = (object) array(
					'name'        => $a_name,
					'title'       => (string) $xml_action['title'],
					'description' => (string) $xml_action['description'],
					'roles'       => array_filter(explode(',', (string) $xml_action['roles']), 'strlen') ,
				);
			}
		}

		// Fetch grouped actions
		$xml_actions_groups = $xml->xpath("$xpath/actions[@name]");

		if (!empty($xml_actions_groups))
		{
			foreach ($xml_actions_groups as $xml_actions_group)
			{
				$name  = (string) $xml_actions_group['name'];
				$title = (string) $xml_actions_group['title'];

				if (!isset($groups[$name]))
				{
					$groups[$name] = (object) array(
						'name'    => $name,
						'title'   => $title,
						'actions' => array(),
					);
				}
				elseif ($title)
				{
					$groups[$name]['title'] = $title;
				}

				$xml_actions = $xml_actions_group->xpath('action[@name][@title][@description]');

				if (!empty($xml_actions))
				{
					foreach ($xml_actions as $xml_action)
					{
						$a_name = (string) $xml_action['name'];

						$groups[$name]->actions[$a_name] = (object) array(
							'name'        => $a_name,
							'title'       => (string) $xml_action['title'],
							'description' => (string) $xml_action['description'],
							'roles'       => array_filter(explode(',', (string) $xml_action['roles']), 'strlen') ,
						);
					}
				}
			}
		}

		// Finally return the actions array
		return $groups;
	}

	/**
	 * Check access of current user
	 *
	 * @param   string  $action     Action to be checked for
	 * @param   string  $assetName  Asset name for this the access is required
	 * @param   int     $userId     Id of the user for which the access needs to be checked
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public static function allow($action, $assetName = null, $userId = null)
	{
		return User::getInstance($userId)->authorise($action, $assetName);
	}

	/**
	 * Check access of current user
	 *
	 * @param   string[]  $actions    Action to be checked for
	 * @param   string    $prefix     Actions/asset to be accessed may be grouped if they have same prefix asset name ('product.' + 'edit')
	 * @param   string    $assetName  Asset name for this the access is required
	 * @param   int       $userId     Id of the user for which the access needs to be checked
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public static function allowSome(array $actions, $prefix = null, $assetName = null, $userId = null)
	{
		if (count($actions))
		{
			foreach ($actions as $action)
			{
				if (static::allow($prefix . $action, $assetName, $userId))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check access of current user
	 *
	 * @param   string[]  $actions    Action to be checked for
	 * @param   string    $prefix     Actions/asset to be accessed may be grouped if they have same prefix asset name ('product.' + 'edit')
	 * @param   string    $assetName  Asset name for this the access is required
	 * @param   int       $userId     Id of the user for which the access needs to be checked
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public static function allowAll(array $actions, $prefix = null, $assetName = null, $userId = null)
	{
		if (count($actions))
		{
			foreach ($actions as $action)
			{
				if (!static::allow($prefix . $action, $assetName, $userId))
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Set user group access to given access mode
	 *
	 * @param   int       $groupId     Target group id
	 * @param   mixed     $value       Target group id
	 * @param   string[]  $components  Components names array
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function setAccess($groupId, $value = null, array $components = null)
	{
		if ($components === null)
		{
			$components = ComponentHelper::getComponents();
			$components = ArrayHelper::getColumn($components, 'option');

			array_unshift($components, '');
		}

		foreach ($components as $component)
		{
			$actions = AccessHelper::getActions($component, 'component');
			$rules   = new Rules;

			foreach ($actions as $action)
			{
				$rules->replaceAction($action->name, array($groupId => $value));
			}

			static::saveRules($rules, $component);
		}
	}

	/**
	 * Set user group access from default or given copy from source group access
	 *
	 * @param   int       $groupId     Target group id
	 * @param   int       $sourceId    Source group id
	 * @param   string[]  $components  Components names array
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function copyAccess($groupId, $sourceId, array $components = null)
	{
		if ($components === null)
		{
			$components = ComponentHelper::getComponents();
			$components = ArrayHelper::getColumn($components, 'option');

			array_unshift($components, '');
		}

		foreach ($components as $component)
		{
			$rules   = Access::getAssetRules($component, false, false);
			$actions = static::getActions($component);

			foreach ($actions as $action)
			{
				$allowed = $rules->allow($action->name, $sourceId);

				$rules->replaceAction($action->name, array($groupId => $allowed));
			}

			static::saveRules($rules, $component);
		}
	}

	/**
	 * Set user group access from default access
	 *
	 * @param   int       $groupId     Target group id
	 * @param   string[]  $components  Components names array
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function setDefaultAccess($groupId, array $components = null)
	{
		if ($components === null)
		{
			$components = ComponentHelper::getComponents();
			$components = ArrayHelper::getColumn($components, 'option');

			array_unshift($components, '');
		}

		foreach ($components as $component)
		{
			$rules   = Access::getAssetOwnRules($component);
			$actions = static::getDefaultRules($component, $groupId);

			$rules->replace($actions);

			static::saveRules($rules, $component);
		}
	}

	/**
	 * Get default access from the manifests
	 *
	 * @param   string  $component  Component name
	 * @param   int     $groupId    Target group id
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function getDefaultRules($component, $groupId)
	{
		$rules = array();
		$group = UserGroupHelper::get($groupId);

		if ($group)
		{
			$actions = static::getActions($component);

			foreach ($actions as $action)
			{
				$rules[$action->name][$group->id] = in_array($group->type, $action->roles) ? true : null;
			}
		}

		return $rules;
	}

	/**
	 * Save the asset rules to the permissions table
	 *
	 * @param   Rules   $rules       The rules
	 * @param   string  $assetName   The asset name
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function saveRules(Rules $rules, $assetName)
	{
		/** @var  \SellaciousTablePermission  $asset */
		$asset = \SellaciousTable::getInstance('Permission');

		if (!$asset->getRootId())
		{
			static::fixRootAsset();
		}

		$asset->loadByName($assetName ?: 'root.1');

		if ($asset->get('id') == 0)
		{
			$asset->set('parent_id', 1);
			$asset->set('name', $assetName);
			$asset->set('title', $assetName);
			$asset->setLocation(1, 'last-child');
		}

		$asset->set('rules', (string) $rules);

		if (!$asset->check() || !$asset->store())
		{
			throw new Exception($asset->getError());
		}
	}

	/**
	 * Fix the root asset if it is missing
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function fixRootAsset()
	{
		$root = (object) array(
			'id'        => 1,
			'parent_id' => 0,
			'lft'       => 0,
			'rgt'       => 1,
			'level'     => 0,
			'name'      => 'root.1',
			'title'     => 'Root Asset',
			'rules'     => '{}',
		);

		JFactory::getDbo()->insertObject('#__sellacious_permissions', $root, 'id');
	}
}
