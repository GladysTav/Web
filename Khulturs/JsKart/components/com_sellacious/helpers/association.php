<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Sellacious\Product;

/**
 * Content associations helper.
 *
 * @since  1.6.0
 */
class SellaciousHelperAssociation extends AssociationExtensionHelper
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		$helper = SellaciousHelper::getInstance();
		$app    = JFactory::getApplication();

		$code = $app->input->getString('p');
		$view = $view ?: $app->input->get('view');
		$id   = $id ?: $app->input->getInt('id');

		$helper->product->parseCode($code, $productId, $variantId, $sellerId);

		if ($view === 'product')
		{
			if ($id && $code && $productId && $id == $productId)
			{
				return self::getProductAssociation($id, $code);
			}
		}
		elseif ($view == 'categories')
		{
			return self::getCategoryAssociation();
		}
		else
		{
			$id        = $app->input->getInt('id');
			$layout    = $app->input->getString('layout');
			$return    = array();
			$languages = JLanguageHelper::getInstalledLanguages(0);

			foreach ($languages as $lang => $language)
			{
				$link = 'index.php?option=com_sellacious&view=' . $view;

				if ($id)
				{
					$link .= '&id=' . $id;
				}

				if ($layout)
				{
					$link .= '&layout=' . $layout;
				}

				$link .= '&lang=' . $lang;

				$return[$lang] = $link;
			}

			return $return;
		}

		return array();
	}

	/**
	 * Get the associations for given category
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function getCategoryAssociation()
	{
		$app       = JFactory::getApplication();
		$languages = JLanguageHelper::getInstalledLanguages(0);
		$parentId  = $app->input->getInt('parent_id');
		$return    = array();

		foreach ($languages as $code => $language)
		{
			$return[$code] = 'index.php?option=com_sellacious&view=categories&parent_id=' . $parentId;
		}

		return $return;
	}

	/**
	 * Get associations for given product
	 *
	 * @param   int  $id
	 * @param   int  $code
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected static function getProductAssociation($id, $code)
	{
		$helper    = SellaciousHelper::getInstance();
		$languages = JLanguageHelper::getInstalledLanguages(0);
		$isEnabled = JLanguageMultilang::isEnabled();

		$helper->product->parseCode($code, $productId, $variantId, $sellerUid);

		// Get the associations
		$associations = $helper->product->getAssociations('com_sellacious', '#__sellacious_products', 'com_sellacious.product', $id, 'id', 'alias');

		$return = array();

		if (empty($associations) && $isEnabled)
		{
			$product     = new Product($productId, $variantId, $sellerUid);
			$productLang = $product->get('language');

			if (empty($productLang) || $productLang == '*')
			{
				foreach ($languages as $tag => $language)
				{
					$return[$tag] = "index.php?option=com_sellacious&view=product&p={$code}&lang={$tag}";
				}
			}

			return $return;
		}

		foreach ($associations as $tag => $item)
		{
			$idSegments = explode(':', $item->id);
			$pid        = $idSegments[0];

			if ($pid != $id)
			{
				$variants = $helper->product->getVariants($pid);
				$sellers  = $helper->product->getSellers($pid);

				if (!empty($sellers))
				{
					$seller = array_values(array_filter($sellers, function ($item) use ($sellerUid) {
						return ($item->seller_uid == $sellerUid);
					}));

					if (empty($seller))
					{
						$seller = $sellers;
					}

					$sellerUid = $seller[0]->seller_uid;
				}

				if (!empty($variants))
				{
					$variantId = $variants[0]->id;
				}

				$productId = $pid;
				$code      = $helper->product->getCode($pid, $variantId, $sellerUid);
			}

			$pe = $helper->product->loadResult(array('list.select' => 'a.state', 'id' => $productId));
			$ve = $variantId ? $helper->variant->loadResult(array('list.select' => 'a.state', 'id' => $variantId)) : 0;
			$sd = $helper->user->loadResult(array('list.select' => 'a.block', 'list.from' => '#__users', 'id' => $sellerUid));

			if ($pe && $sd && ($variantId && !$ve))
			{
				$link = 'index.php?option=com_sellacious&view=product&p=' . $code;
			}
			else
			{
				$link = 'index.php?option=com_sellacious&view=products';
			}

			if ($item->language && $item->language !== '*' && $isEnabled)
			{
				$link .= '&lang=' . $item->language;
			}

			$return[$tag] = $link;
		}

		return $return;
	}
}
