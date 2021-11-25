jQuery(document).ready($ => {
	$('.product-blocks-container').each((i, blocksContainer) => {
		let boxH = 0;
		const listBlocks = $(blocksContainer).find('.product-list-block')

		listBlocks.each((i, block) => {
			const eleHeight = $(block).outerHeight();
			boxH = eleHeight > boxH ? eleHeight : boxH;
		})

		listBlocks.css({height: boxH})
	})
})
