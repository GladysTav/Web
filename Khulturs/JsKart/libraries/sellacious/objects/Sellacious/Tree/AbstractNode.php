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
 * Tree Node Class
 *
 * @since   2.0.0
 */
class AbstractNode
{
	/**
	 * Parent node
	 *
	 * @var    AbstractNode
	 *
	 * @since  2.0.0
	 */
	protected $parent = null;

	/**
	 * Array of Children
	 *
	 * @var    AbstractNode[]
	 *
	 * @since  2.0.0
	 */
	protected $children = array();

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   AbstractNode  $child  The child to be added
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addChild(AbstractNode $child)
	{
		$child->setParent($this);
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   AbstractNode  $parent  The AbstractNode for parent to be set or null
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function setParent(AbstractNode $parent = null)
	{
		$hash = spl_object_hash($this);
		$node = $this->getParent();

		if ($node !== null)
		{
			unset($node->children[$hash]);
		}

		if ($parent !== null)
		{
			$parent->children[$hash] = $this;
		}

		$this->parent = $parent;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  AbstractNode[]  The children
	 *
	 * @since   2.0.0
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  AbstractNode  AbstractNode object with the parent or null for no parent
	 *
	 * @since   2.0.0
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  bool  True if there are children
	 *
	 * @since   2.0.0
	 */
	public function hasChildren()
	{
		return (bool) count($this->children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  bool  True if there is a parent
	 *
	 * @since   2.0.0
	 */
	public function hasParent()
	{
		return $this->getParent() !== null;
	}
}
