<?php
/**
 * @version     1.0.1
 * @package     mod_sellacious_offeredproducts
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the mod_sellacious_offeredproducts ProductList.
 *
 * @since   1.6
 */
class JFormFieldProductList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'ProductList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// This may be called from outer context so load helpers explicitly.
		jimport('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			JFactory::getApplication()->enqueueMessage('Sellacious Library was not found.', 'error');

			return parent::getOptions();
		}

		$helper = SellaciousHelper::getInstance();

		$filters                  = array();
		$filters['list.select'][] = ' a.id, a.title';

		$filters['list.join'] = array(
			array('inner', '#__sellacious_product_prices AS p ON p.product_id = a.id'),
		);

        $filters['list.where'][] = 'a.state = 1';
        $filters['list.where'][] = 'p.edate >= NOW()';
		$filters['list.where'][] = 'p.qty_min <= 1';

		$filters['list.group'][] = 'p.product_id';

		$items   = $helper->product->loadObjectList($filters);
		$options = array();

		$this->loadJs();

		foreach ($items as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, $item->title, 'value', 'text');
		}

		return array_merge(parent::getOptions(), $options);
	}

	protected function loadJs()
	{
		ob_start(); ?>

		jQuery(document).ready(function($)
		{
            var mp = $('#jform_params_mainproduct').val();
			if(mp != ""){
                $('#jform_params_products option[value='+mp+']').prop('disabled', true).trigger('liszt:updated');
            }

            $('#jform_params_usecatsorprods').on('change', function(e) {
                if($( this ).val() == 0){
                    $('#jform_params_mainproduct option').prop('selected', false).trigger('liszt:updated');
                    $('#jform_params_products option').prop('disabled', false).prop('selected', false).trigger('liszt:updated');
                }else{
					$('#jform_params_categories option').prop('selected', false).trigger('liszt:updated');
				}
            });

			$('#jform_params_layout_style').on('change', function(e) {
				if($( this ).val() == 3){
					$('#jform_params_mainproduct option[value='+mp+']').prop('selected', false).trigger('liszt:updated');
					$('#jform_params_products option').prop('disabled', false).trigger('liszt:updated');
				}
				$('#jform_params_products option').prop('selected', false);
				$('#jform_params_products').trigger('liszt:updated');
			});

            $('#jform_params_mainproduct').on( "change", function(e) {
                var op = $( this ).val();
                if(op != ""){
                    $('#jform_params_products option').prop('disabled', false);
                    $('#jform_params_products option[value='+op+']').prop('disabled', true).trigger('liszt:updated');
                }else{
                    $('#jform_params_products option').prop('disabled', false).trigger('liszt:updated');
                }


            });
            $('#jform_params_products_chzn').click(function() {
                var style = $('#jform_params_layout_style').val();
                var selectable = 0;

                if (style == 1){
                    selectable = 4;
                }
                if (style == 2){
                    selectable = 6;
                }
                if (style == 3){
                    selectable = 16;
                }

                var count = $('#jform_params_products :selected').length;
                if (count >= selectable) {
                    $('#jform_params_products_chzn .chzn-drop').css('display', 'none');
                    alert('You can select ' + selectable + ' products only. Deselect anyone to change selection.');
                }
                if (count < selectable) {
                    $('#jform_params_products_chzn .chzn-drop').css('display', 'block');
                }
            });
		});
		<?php
		$script = ob_get_clean();

		JFactory::getDocument()->addScriptDeclaration($script);
	}
}
