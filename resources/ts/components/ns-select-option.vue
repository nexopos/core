<template>
    <template v-if="isOptgroup">
        <optgroup :disabled="option.disabled || false" :label="option.label">
            <ns-select-option 
                v-for="(subOption, index) in option.value" 
                :key="index"
                :option="subOption"
                :level="level + 1">
            </ns-select-option>
        </optgroup>
    </template>
</template>
<script>
export default {
    name: 'ns-select-option',
    props: ['option', 'level'],
    computed: {
        isOptgroup() {
            return Array.isArray(this.option.value);
        }
    },
    methods: {
        getOptionLabel() {
            // Add indentation for nested options (when not in optgroup)
            const indent = '\u00A0\u00A0'.repeat(this.level);
            return indent + this.option.label;
        },
        getOptionStyle() {
            // Add padding for visual hierarchy
            if (this.level > 0) {
                return `padding-left: ${this.level * 1.5}rem;`;
            }
            return '';
        }
    }
}
</script>