<div>
	<div class="preverify-input-group">
		<input ref="input" :type="type" :id="id" v-model="value" v-show="!disabled"
			   :class="classname" class="preverify-input validate-preverify"
			   :placeholder="placeholder" :required="required"
			   :unique="unique" :userid="userid"
			   autocomplete="off"/>
		<input v-show="disabled" :type="type" disabled :value="value"/>
		<input type="hidden" :name="name" :value="token"/>
		<a v-if="disabled" class="preverify-send-otp" href="#" @click="change">
			<slot name="change">Change</slot>
		</a>
		<a v-else class="preverify-send-otp" href="#" @click="send">
			<slot name="send">Send</slot>
		</a>
	</div>
	<div class="preverify-otp-form" v-if="askOtp">
		<div class="preverify-otp-container">
			<input type="text" v-model="otp"/>
			<button type="button" class="ctech-btn ctech-btn-info ctech-btn-sm" @click="send">
				<slot name="resend">Resend</slot>
			</button>
			<button type="button" class="ctech-btn ctech-btn-success ctech-btn-sm" @click="verify">
				<slot name="verify">Verify</slot>
			</button>
		</div>
	</div>
	<div class="preverify-message-box" v-show="message.text">
		<span :class="`ctech-text-${message.type}`">{{ message.text }}</span>
	</div>
</div>

