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
namespace Sellacious\Tree;

defined('_JEXEC') or die;

/**
 * Tree Class
 *
 * @since  2.0.0
 */
class AbstractTree
{
	/**
	 * Root node
	 *
	 * @var    AbstractNode
	 *
	 * @since  2.0.0
	 */
	protected $root = null;

	/**
	 * Current working node
	 *
	 * @var    AbstractNode
	 *
	 * @since  2.0.0
	 */
	protected $current = null;

	/**
	 * Constructor
	 *
	 * @param   AbstractNode  $root  The root node to initialise with
	 *
	 * @since   2.0.0
	 */
	public function __construct(AbstractNode $root)
	{
		$this->root    = $root;
		$this->current = $this->root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   AbstractNode  $node        The node to process
	 * @param   boolean       $setCurrent  True to set as current working node
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addChild(AbstractNode $node, $setCurrent = false)
	{
		$this->current->addChild($node);

		if ($setCurrent)
		{
			$this->current = $node;
		}
	}

	/**
	 * Method to get the parent
	 *
	 * @param   boolean  $setCurrent  True to set as current working node
	 *
	 * @return  AbstractNode
	 *
	 * @since   2.0.0
	 */
	public function getParent($setCurrent = false)
	{
		$parent = $this->current->getParent();

		if ($setCurrent)
		{
			$this->current = $parent;
		}

		return $parent;
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function reset()
	{
		$this->current = $this->root;
	}
}
