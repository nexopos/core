<template>
    <ns-spinner></ns-spinner>
</template>
<script>
import nsSpinner from '~/components/ns-spinner.vue';

export default {
    name : "ns-pos-loading-popup",
    components: {
        nsSpinner,
    },
    mounted() {
        console.log( nsSpinner );
    },
    props: [ 'popup' ],
}
</script>