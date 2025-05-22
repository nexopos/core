<template>
    <div class="flex justify-between items-center flex-shrink-0">
        <span class="hidden md:inline-block px-2">{{ __( 'Howdy, {name}' ).replace( '{name}', displayName ) }}</span>
        <span class="md:hidden px-2">{{ displayName }}</span>
        <div class="px-2">
            <div class="overflow-hidden rounded-full bg-gray-600">
                <ns-avatar-image :url="avatarUrl" :name="displayName"></ns-avatar-image>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';
import NsAvatarImage from './ns-avatar-image.vue';

const __ = window.__;
const props = defineProps<{
    url: string;
    displayName: string;
}>();

const avatarUrl = computed(() => {
    return props.url.length === 0 ? '' : props.url;
});

</script>