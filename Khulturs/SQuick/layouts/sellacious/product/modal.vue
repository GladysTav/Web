<div>
	<slot name="button"></slot>
	<div class="ctech-modal" :class="classes" role="dialog" :id="id" :data-backdrop="backdrop !== '' ? backdrop : true">
		<div class="ctech-modal-dialog" :class="size !== '' ? `ctech-modal-${size}` : ''">
			<div class="ctech-modal-content">
				<div class="ctech-modal-header" v-if="header !== false">
					<h5 class="ctech-modal-title">
						<slot name="header"></slot>
					</h5>
					<button type="button" class="ctech-close" data-dismiss="ctech-modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="ctech-modal-body">
					<iframe v-if="url !== ''" :src="url"></iframe>
					<slot v-else name="body"></slot>
				</div>
				<div class="ctech-modal-footer" v-if="footer !== false">
					<slot name="footer"></slot>
				</div>
			</div>
		</div>
	</div>
</div>
