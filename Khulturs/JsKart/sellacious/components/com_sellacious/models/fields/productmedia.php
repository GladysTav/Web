<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('JPATH_BASE') or die;

/**
 * Form Field to provide an input field for files
 *
 * @since   2.0.0
 */
class JFormFieldProductMedia extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var    string
	 *
	 * @since   2.0.0
	 */
	public $type = 'ProductMedia';

	/**
	 * @var  int
	 *
	 * @since   2.0.0
	 */
	protected $productId;

	/**
	 * @var  int
	 *
	 * @since   2.0.0
	 */
	protected $variantId;

	/**
	 * @var  int
	 *
	 * @since   2.0.0
	 */
	protected $sellerUid;

	/**
	 * @var  int
	 *
	 * @since   2.0.0
	 */
	protected $code;

	/**
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $rename;

	/**
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $file_types;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $layout = 'com_sellacious.formfield.productmedia.input';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  bool  True on success
	 *
	 * @since   2.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->productId = (int) $this->element['product_id'];
			$this->sellerUid = (int) $this->element['seller_uid'];
			$this->variantId = strlen((string) $this->element['variant_id']) == 0 ? null : (int) $this->element['variant_id'];

			try
			{
				$helper     = SellaciousHelper::getInstance();
				$this->code = $helper->product->getCode($this->productId, $this->variantId, $this->sellerUid);
			}
			catch (Exception $e)
			{
			}
		}

		$this->hidden = !($this->productId > 0 && $this->sellerUid > 0 && $this->variantId !== null);

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 *
	 * @return  string  The field input markup
	 *
	 * @since   2.0.0
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<div class="alert alert-info" style="margin-left: -5px;">' . JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_ALERT_SAVE_PRODUCT_TO_UPLOAD') . '</div>';
		}

		JHtml::_('jquery.framework');

		JHtml::_('script', 'com_sellacious/plugin/jquery.iframe-drawer.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/plugin/clipboardjs/clipboard.min.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.productmedia.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('stylesheet', 'com_sellacious/field.productmedia.css', array('version' => S_VERSION_CORE, 'relative' => true));

		// We do not need values here, its loaded via ajax later
		$vars = (object) get_object_vars($this);

		return JLayoutHelper::render($this->layout, $vars, '', array('client' => 2, 'debug' => 0));
	}
}
