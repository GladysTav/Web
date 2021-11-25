<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Sellacious\Config\Config;

defined('_JEXEC') or die;

// Include dependencies
jimport('sellacious.loader');

/**
 * Sellacious Category  Plugin
 *
 * @since  3.2
 */
class PlgSystemSellaciousCatTemplates extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds category templates fields to the sellacious category form
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   array $data The associated data for the form.
	 *
	 * @return  boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		if ($form instanceof JForm && JComponentHelper::isEnabled('com_sppagebuilder'))
		{
			$name = $form->getName();

			if ($name == 'com_sellacious.category')
			{
				$form->loadFile(__DIR__ . '/forms/cat_templates.xml', false);
			}
			elseif ($name == 'com_sellacious.config')
			{
				$formPath = $this->pluginPath . '/' . $this->_name . '.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false, '//config');
			}
		}
	}


	/**
	 * This method logs the user visits.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function onAfterRoute()
	{
		$option = $this->app->input->getCmd('option');
		$view   = $this->app->input->getCmd('view');
		$task   = $this->app->input->getCmd('task');

		if (($option == 'com_customcatalogue' || $option == 'com_sellacious') && $view == 'categories' && $this->app->isClient('site'))
		{
			$category = $this->app->input->getInt('parent_id', $this->app->input->getInt('category_id'));

			if (!empty($category))
			{
				$cat_redirect = $this->getTemplateSetting('cat_redirect', $category, 0);
				$cat_template = $this->getTemplateSetting('cat_template', $category, null);

				if ($cat_redirect == 1 && !empty($cat_template))
				{
					$this->app->input->set('option', 'com_sppagebuilder');
					$this->app->input->set('view', 'page');
					$this->app->input->set('id', $cat_template);

					$this->app->input->set('category', $category);

				}
			}
		}

		if (($option == 'com_customcatalogue' || $option == 'com_sellacious') && $view == 'product' && $this->app->isClient('site'))
		{
			$p_code     = $this->app->input->getString('p');
			$tmplLayout = $this->app->input->getString('tmpl');

			if (!empty($p_code) && !$tmplLayout)
			{
				$this->helper->product->parseCode($p_code, $product, $variant, $seller);
				$categories = $this->helper->product->getCategories($product);

				if ($product && !empty($categories[0]))
				{
					foreach ($categories as $categoryId)
					{
						$product_redirect = $this->getTemplateSetting('product_redirect', $categoryId, 0);
						$product_template = $this->getTemplateSetting('product_template', $categoryId, null);

						if ($product_redirect != 1)
						{
							// Continue with next category
							continue;
						}
						else if ($product_redirect == 1 && !empty($product_template))
						{
							if (!in_array($task, array('product.switchVariant', 'product.saveRating')))
							{
								$this->app->input->set('option', 'com_sppagebuilder');
								$this->app->input->set('view', 'page');
								$this->app->input->set('id', $product_template);

								$this->app->input->set('product', $product);
								$this->app->input->set('v', $variant);
								$this->app->input->set('s', $seller);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Method to get SPPB Template Setting
	 *
	 * @param   string  $param     The Parameter key name
	 * @param   int     $category  Category Id
	 * @param   int     $default   The default value
	 *
	 * @return  int
	 *
	 * @throws  \Exception
	 *
	 * @since   2.1.7
	 */
	public function getTemplateSetting($param, $category, $default = null)
	{
		$config  = new Config($this->pluginName);
		$setting = $config->get($param, $default);

		if (!empty($category))
		{
			$config_category = $this->helper->category->getCategoryParam($category, $param, $default, true);

			if ($config_category != '')
			{
				$setting = $this->helper->category->getCategoryParam($category, $param, $default, true);
			}
		}

		return $setting;
	}
}
