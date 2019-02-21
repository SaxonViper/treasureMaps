<template>
    <transition name="modal-fade">
        <div class="my-modal-backdrop" v-bind:class="{visible: classVisible}" v-on:show="classVisible = true">
            <div class="my-modal">
                <header class="modal-header">
                    <div class="modal-header-title">
                        <slot name="header">
                            This is the default tile!
                        </slot>
                    </div>
                    <button type="button" class="btn-close" @click="close">
                        x
                    </button>
                </header>

                <section class="modal-body">
                        <slot name="body">
                        I'm the default body!
                    </slot>
                </section>

                <footer class="modal-footer">
                    <slot name="footer">
                        I'm the default footer!
                    </slot>
                </footer>
            </div>
        </div>
    </transition>
</template>

<script>
    export default {
        name: 'modal',

        data() {
            return {
                classVisible : false
            }
        },

        methods: {
            close() {
                this.$emit('close');
            },
            /*toggleClassVisible(): {

            }*/
        },
    };
</script>