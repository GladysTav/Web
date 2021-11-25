<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('radio');

/**
 * Form Field class for the Joomla Platform. Provides radio button inputs as  switch
 *
 * @since  2.0.0
 */
class JFormFieldRadioSwitch extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $type = 'radioswitch';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $layout = 'sellacious.form.field.radioswitch';

	protected function getInput()
	{
		static $loaded = false;

		if (!$loaded)
		{
			JFactory::getDocument()->addScriptDeclaration(<<<JS
				(jq => {
					jq(document).on('change', '.jff-radioswitch-ui', function() {
				        const value = jq(this).data(this.checked ? 'true' : 'false');
				        jq(this).closest('.jff-radioswitch').find('input[name]').val(value).trigger('change');
					}); 
				})(jQuery);
JS
			);

			$loaded = true;
		}

		return parent::getInput();
	}
}
