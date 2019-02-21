<template>
    <div class="container">
        <div class="card card-default">
            <div class="card-header">Example Component</div>

            <div class="card-body">
                <mapsViewer></mapsViewer>
            </div>
        </div>

    </div>
</template>

<script>
    import MapsViewer from './MapsViewer.vue';

    export default {
        mounted() {
            console.log('Component mounted.')
        },

        data() {
            return {

            }
        },

        components: {
            MapsViewer
        },

        methods: {

        },


    }
</script>
