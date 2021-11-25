<?php
/**
 * @version     2.2.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Sellacious Category  Plugin
 *
 * @since  3.2
 */
class PlgSystemSellaciousCatTemplates extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	protected $app;

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
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() == 'com_sellacious.category')
		{
			$form->loadFile(__DIR__ . '/forms/cat_templates.xml', false);
		}
	}


	/**
	 * This method logs the user visits.
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		$option = $this->app->input->getCmd('option');
		$view   = $this->app->input->getCmd('view');
		$task   = $this->app->input->getCmd('task');

		if (($option == 'com_customcatalogue' || $option == 'com_sellacious') && $view == 'categories')
		{
			$helper   = SellaciousHelper::getInstance();
			$category = $this->app->input->getInt('parent_id');

			if (!empty($category))
			{
				$result = $helper->category->getItem($category);

				$params = json_decode($result->params, true);

				if ($params['cat_redirect'] == 1 && !empty($params['cat_template']))
				{
					$this->app->input->set('option', 'com_sppagebuilder');
					$this->app->input->set('view', 'page');
					$this->app->input->set('id', $params['cat_template']);

					$this->app->input->set('category', $category);

				}
			}
		}

		if (($option == 'com_customcatalogue' || $option == 'com_sellacious') && $view == 'product')
		{
			$helper = SellaciousHelper::getInstance();
			$p_code = $this->app->input->getString('p');
			$tmplLayout = $this->app->input->getString('tmpl');

			if (!empty($p_code) && !$tmplLayout)
			{
				$helper->product->parseCode($p_code, $product, $variant, $seller);
				$categories = $helper->product->getCategories($product);

				if ($product && !empty($categories[0]))
				{
					foreach ($categories as $categoryId)
					{
						$result = $helper->category->getItem($categoryId);
						$params = json_decode($result->params, true);

						if ($params['product_redirect'] != 1)
						{
							// Continue with next category
							continue;
						}
						else if ($params['product_redirect'] == 1 && !empty($params['product_template']))
						{
							if (!in_array($task, array('product.switchVariant', 'product.saveRating')))
							{
								$this->app->input->set('option', 'com_sppagebuilder');
								$this->app->input->set('view', 'page');
								$this->app->input->set('id', $params['product_template']);

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
}
