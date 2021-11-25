<component :is="'stores-block-' + blockStyle" v-for="store in stores" :key="store.user_id" :store="store" :params="params" :mod_params="params.mod_params" :user="user" :context="context"></component>
