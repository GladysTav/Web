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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Tree\AbstractNode;
use Sellacious\Tree\AbstractTree;

/**
 * Tree based class to render the admin menu
 *
 * @since   1.1.0
 */
class JAdminCssSmartyMenu extends AbstractTree
{
	/**
	 * Root node
	 *
	 * @var    JSmartyMenuNode
	 *
	 * @since  2.0.0
	 */
	protected $root = null;

	/**
	 * Current working node
	 *
	 * @var    JSmartyMenuNode
	 *
	 * @since  2.0.0
	 */
	protected $current = null;

	/**
	 * CSS string to add to document head
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $css = null;

	/**
	 * Constructor
	 *
	 * @since   1.1.0
	 */
	public function __construct()
	{
		$node = new JSmartyMenuNode('ROOT');

		parent::__construct($node);
	}

	/**
	 * Method to add a separator node
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	public function addSeparator()
	{
		$this->addChild(new JSmartyMenuNode(null, null, 'separator', false));
	}

	/**
	 * Method to render the menu
	 *
	 * @param   string $id    The id of the menu to be rendered
	 * @param   string $class The class of the menu to be rendered
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	public function renderMenu($id = 'menu', $class = '')
	{
		$depth = 1;

		echo "<nav id='$id' class='$class'>\n";

		// Recurse through children if they exist
		while ($this->current->hasChildren())
		{
			echo "<ul>\n";

			foreach ($this->current->getChildren() as $child)
			{
				$this->current = &$child;
				$this->renderLevel($depth++);
			}

			echo "</ul>\n";
		}

		echo "</nav>\n";

		if ($this->css)
		{
			// Add style to document head
			$doc = JFactory::getDocument();
			$doc->addStyleDeclaration($this->css);
		}
	}

	/**
	 * Method to render a given level of a menu
	 *
	 * @param   int  $depth  The level of the menu to be rendered
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	public function renderLevel($depth)
	{
		if ($this->current->class == 'separator')
		{
			// We do not handle separators, plus they do not have children ever
			return;
		}

		// Build the CSS class suffix
		$class = '';

		if ($this->current->active)
		{
			$class .= 'active';
		}

		// Print the item
		echo "<li class=\"" . $class . "\">";

		$attr = '';
		$icon = '';
		$attr .= $this->current->link != null ? ' href="' . $this->current->link . '"' : ' href="#"';
		$attr .= $this->current->target != null ? ' target="' . $this->current->target . '"' : '';
		$title = $this->current->title != null ? $this->current->title : '';

		// $title = $this->current->hasChildren() ? '<span class="menu-item-parent">' . $title . '</span>' : $title;
		$title = '<span class="menu-item-parent">' . $title . '</span>';

		$iconClass = $this->getIconClass($this->current->class);

		if (!empty($iconClass))
		{
			$icon = '<i class="fa fa-lg fa-fw fa-' . $iconClass . '"></i> ';
		}

		echo '<a' . $attr . '>' . $icon . $title . '</a>';

		// Recurse through children if they exist
		while ($this->current->hasChildren())
		{
			echo "<ul>\n";

			foreach ($this->current->getChildren() as $child)
			{
				$this->current = &$child;
				$this->renderLevel($depth++);
			}

			echo "</ul>\n";
		}

		echo "</li>\n";
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 *
	 * @param   string  $identifier Icon identification string
	 *
	 * @return  string  CSS class name
	 *
	 * @since   1.1.0
	 */
	public function getIconClass($identifier)
	{
		static $classes;

		// Initialise the known classes array if it does not exist
		if (!is_array($classes))
		{
			// List all classes names used for icon
			$classes = array();
		}

		$class = explode(":", $identifier);

		return ArrayHelper::getValue($classes, $identifier, $class[1]);
	}
}

/**
 * A Node for JAdminCssMenu
 *
 * @see    JAdminCssMenu
 *
 * @since   1.1.0
 */
class JSmartyMenuNode extends AbstractNode
{
	/**
	 * Node Title
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	public $title = null;

	/**
	 * Node Id
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	public $id = null;

	/**
	 * Node Link
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	public $link = null;

	/**
	 * Link Target
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	public $target = null;

	/**
	 * CSS Class for node
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	public $class = null;

	/**
	 * Active Node?
	 *
	 * @var  boolean
	 *
	 * @since   1.1.0
	 */
	public $active = false;

	/**
	 * Parent node
	 *
	 * @var  JSmartyMenuNode
	 *
	 * @since   1.1.0
	 */
	protected $parent = null;

	/**
	 * Array of Children
	 *
	 * @var  JSmartyMenuNode[]
	 *
	 * @since   1.1.0
	 */
	protected $children = array();

	/**
	 * Constructor for the class.
	 *
	 * @param   string   $title      The title of the node
	 * @param   string   $link       The node link
	 * @param   string   $class      The CSS class for the node
	 * @param   boolean  $active     True if node is active, false otherwise
	 * @param   string   $target     The link target
	 * @param   string   $titleicon  The title icon for the node
	 *
	 * @since   1.1.0
	 */
	public function __construct($title, $link = null, $class = null, $active = false, $target = null, $titleicon = null)
	{
		$this->title  = $titleicon ? $title . $titleicon : $title;
		$this->link   = JFilterOutput::ampReplace(JRoute::_($link));
		$this->class  = $class;
		$this->active = $active;

		$this->id = null;

		if (!empty($link) && $link !== '#')
		{
			$uri   = new JUri($link);
			$query = $uri->getQuery();

			$this->id = preg_replace('/[^A-Z0-9-]+/i', '-', $query);
		}

		$this->target = $target;
	}
}
