/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($, factory) {
	if (typeof window === "object") {

		// add to window
		window['SortOptions'] = factory();

		// add jquery plugin, if available
		if (typeof $ === "object") {
			$.fn.SortOptions = function (options) {
				return this.each(function () {
					new window['SortOptions'](this, options);
				});
			};
		}
	}
})(jQuery, function () {

	let _d = document.documentElement;

	// get position of mouse/touch in relation to viewport
	let getPoint = function (e) {
		let _w = window;
		let _b = document.body;
		let scrollX = Math.max(0, _w.pageXOffset || _d.scrollLeft || _b.scrollLeft || 0) - (_d.clientLeft || 0),
			scrollY = Math.max(0, _w.pageYOffset || _d.scrollTop || _b.scrollTop || 0) - (_d.clientTop || 0),
			pointX = e ? (Math.max(0, e.pageX || e.clientX || 0) - scrollX) : 0,
			pointY = e ? (Math.max(0, e.pageY || e.clientY || 0) - scrollY) : 0;

		return {x: pointX, y: pointY};
	};

	// class constructor
	let Factory = function (container, options) {
		if (container && container instanceof Element) {
			this._container = container;
			this._options = options || {}; /* nothing atm */
			this._clickItem = null;
			this._dragItem = null;
			this._hovItem = null;
			this._sortLists = [];
			this._click = {};
			this._dragging = false;

			this._container.setAttribute("data-is-sortable", '1');
			this._container.style["position"] = "static";

			window.addEventListener("mousedown", this._onPress.bind(this), true);
			window.addEventListener("touchstart", this._onPress.bind(this), true);
			window.addEventListener("mouseup", this._onRelease.bind(this), true);
			window.addEventListener("touchend", this._onRelease.bind(this), true);
			window.addEventListener("mousemove", this._onMove.bind(this), true);
			window.addEventListener("touchmove", this._onMove.bind(this), true);
		}
	};

	// class prototype
	Factory.prototype = {
		constructor: Factory,

		// serialize order into array list
		toArray: function (attr) {
			attr = attr || "id";

			let data = [],
				item = null,
				uniq = "";

			for (let i = 0; i < this._container.children.length; ++i) {
				item = this._container.children[i];
				uniq = item.getAttribute(attr) || "";
				uniq = uniq.replace(/[^a-z0-9_]+/gi, "");
				data.push(uniq);
			}
			return data;
		},

		// serialize order array into a string
		toString: function (attr, delimiter) {
			delimiter = delimiter || ":";
			return this.toArray(attr).join(delimiter);
		},

		// checks if mouse x/y is on top of an item
		_isOnTop: function (item, x, y) {
			let box = item.getBoundingClientRect(),
				isx = (x > box.left && x < (box.left + box.width)),
				isy = (y > box.top && y < (box.top + box.height));
			return (isx && isy);
		},

		// manipulate the className of an item (for browsers that lack classList support)
		_itemClass: function (item, task, cls) {
			let list = item.className.split(/\s+/),
				index = list.indexOf(cls);

			if (task === "add" && index === -1) {
				list.push(cls);
				item.className = list.join(" ");
			} else if (task === "remove" && index !== -1) {
				list.splice(index, 1);
				item.className = list.join(" ");
			}
		},

		// swap position of two item in sortable list container
		_swapItems: function (item1, item2) {
			let parent1 = item1.parentNode,
				parent2 = item2.parentNode;

			if (parent1 !== parent2) {
				// move to new list if has same group
				if (parent1.dataset.sortOptionsGroup === parent2.dataset.sortOptionsGroup) {
					parent2.insertBefore(item1, item2);
					if (typeof this._options.update === "function") {
						this._options.update(parent1);
						this._options.update(parent2);
					}
				}
			} else {
				// sort is same list
				let temp = document.createElement("div");
				parent1.insertBefore(temp, item1);
				parent1.insertBefore(item1, item2);
				parent1.insertBefore(item2, temp);
				parent1.removeChild(temp);
				if (typeof this._options.update === "function") {
					typeof this._options.update(parent1);
				}
			}
		},

		// update item position
		_moveItem: function (item, x, y) {
			item.style["-webkit-transform"] = "translateX( " + x + "px ) translateY( " + y + "px )";
			item.style["-moz-transform"] = "translateX( " + x + "px ) translateY( " + y + "px )";
			item.style["-ms-transform"] = "translateX( " + x + "px ) translateY( " + y + "px )";
			item.style["transform"] = "translateX( " + x + "px ) translateY( " + y + "px )";
		},

		// make a temp fake item for dragging and add to container
		_makeDragItem: function (item) {
			this._trashDragItem();
			this._sortLists = document.querySelectorAll("[data-is-sortable]");

			this._clickItem = item;
			this._itemClass(this._clickItem, "add", "active");

			this._dragItem = document.createElement(item.tagName);
			this._dragItem.className = "dragging";
			this._dragItem.innerHTML = item.innerHTML;
			this._dragItem.style["position"] = "absolute";
			this._dragItem.style["z-index"] = "999";
			this._dragItem.style["left"] = (item.offsetLeft || 0) + "px";
			this._dragItem.style["top"] = (item.offsetTop || 0) + "px";
			this._dragItem.style["width"] = (item.offsetWidth || 0) + "px";

			this._container.appendChild(this._dragItem);
		},

		// remove drag item that was added to container
		_trashDragItem: function () {
			if (this._dragItem && this._clickItem) {
				this._itemClass(this._clickItem, "remove", "active");
				this._clickItem = null;

				this._container.removeChild(this._dragItem);
				this._dragItem = null;
			}
		},

		// on item press/drag
		_onPress: function (e) {
			if (e && e.target && e.target.parentNode === this._container) {
				e.preventDefault();

				this._dragging = true;
				this._click = getPoint(e);
				this._makeDragItem(e.target);
				this._onMove(e);
			}
		},

		// on item release/drop
		_onRelease: function (e) {
			this._dragging = false;
			this._trashDragItem();
		},

		// on item drag/move
		_onMove: function (e) {
			if (this._dragItem && this._dragging) {
				e.preventDefault();

				let point = getPoint(e);
				let container = this._container;

				// drag fake item
				this._moveItem(this._dragItem, (point.x - this._click.x), (point.y - this._click.y));

				// keep an eye for other sortable lists and switch over to it on hover
				for (let a = 0; a < this._sortLists.length; ++a) {
					let subContainer = this._sortLists[a];

					if (this._isOnTop(subContainer, point.x, point.y)) {
						container = subContainer;
					}
				}

				// container is empty, move clicked item over to it on hover
				if (this._isOnTop(container, point.x, point.y) && container.children.length === 0) {
					const parent1 = this._clickItem.parentNode;
					if (parent1.dataset.sortOptionsGroup === container.dataset.sortOptionsGroup) {
						container.appendChild(this._clickItem);
						if (typeof this._options.update === "function") {
							this._options.update(container);
							this._options.update(parent1);
						}
					}
					return;
				}

				// check if current drag item is over another item and swap places
				for (let b = 0; b < container.children.length; ++b) {
					let subItem = container.children[b];

					if (subItem === this._clickItem || subItem === this._dragItem) {
						continue;
					}
					if (this._isOnTop(subItem, point.x, point.y)) {
						this._hovItem = subItem;
						this._swapItems(this._clickItem, subItem);
					}
				}
			}
		},

	};

	// export
	return Factory;
});

jQuery($ => {
	$(document).ready(() => {
		$('.sff-sort-options-sortable').each(function () {
			const update = function(el) {
				const $el = $(el);
				const sortable = $el.data('sff-sortable');
				const value = sortable.toString('rel', ':');
				$el.closest('div').find('.sff-sort-options-input').val(value);
			};
			const sortable = new SortOptions(this, {update});
			$(this).data('sff-sortable', sortable);
			update(this);
		});
	});
});
