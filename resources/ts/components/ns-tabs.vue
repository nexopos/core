<template>
    <div class="tabs flex flex-col flex-auto ns-tab overflow-hidden" :selected-tab="activeComponent.identifier">
        <div class="header ml-4 flex justify-between" style="margin-bottom: -1px;">
            <!-- Mobile: scrollable with fade indicator. Desktop: wrapping -->
            <div class="relative flex-auto min-w-0">
                <div 
                    ref="tabsScroller"
                    @scroll="onScroll"
                    class="tabs-scroller flex md:flex-wrap overflow-x-auto md:overflow-x-visible"
                    style="scrollbar-width: none; -ms-overflow-style: none;">
                    <div 
                        :key="tab.identifier" 
                        v-for="( tab , identifier ) of childrens" 
                        @click="toggle( tab )" 
                        :class="active === tab.identifier ? 'border-b-0 active z-10' : 'inactive'" 
                        class="tab rounded-tl rounded-tr border px-2 py-1 cursor-pointer flex items-center shrink-0 md:shrink" 
                        style="margin-right: -1px">
                            <span>{{ tab.label }}</span>
                            <div v-if="tab.closable" @click="$emit( 'close', tab )" class="ns-inset-button border border-box-edge text-xs hover:border-error-tertiary error rounded-full h-5 w-5 flex items-center justify-center ml-1"><i class="las la-times"></i></div>
                    </div>
                </div>
                <!-- Scroll right indicator (mobile only) -->
                <transition name="fade">
                    <div 
                        v-if="canScrollRight"
                        class="md:hidden absolute top-0 right-0 h-full w-12 pointer-events-none flex items-center justify-end"
                        style="background: linear-gradient(to right, transparent, var(--ns-tab-scroll-hint, rgba(255,255,255,0.85)));">
                        <i class="las la-angle-right text-xs opacity-60 mr-1"></i>
                    </div>
                </transition>
            </div>
            <div class="shrink-0">
                <slot name="extra"></slot>
            </div>
        </div>
        <slot></slot>
    </div>
</template>
<script lang="ts">
import { Subject } from 'rxjs';
import { __ } from '~/libraries/lang';
export default {
    data() {
        return {
            childrens: [],
            tabState: new Subject,
            canScrollRight: false,
            resizeObserver: null as ResizeObserver | null,
        }
    },
    props: [ 'active' ],
    computed: {
        activeComponent() {
            const active    =   this.childrens.filter( tab => tab.active );
            if ( active.length > 0 ) {
                return active[0];
            }
            return false;
        },
    },
    beforeUnmount() {
        this.tabState.unsubscribe();
        if ( this.resizeObserver ) {
            this.resizeObserver.disconnect();
        }
    },
    watch: {
        active( newValue, oldValue ) {
            this.childrens.forEach( children => {
                children.active     =   children.identifier === newValue ? true : false;

                if ( children.active ) {
                    this.toggle( children );
                }
            });
        }
    },    
    mounted() {
        this.buildChildrens( this.active );
        this.$nextTick( () => {
            this.checkScroll();
            this.resizeObserver = new ResizeObserver( () => this.checkScroll() );
            if ( this.$refs.tabsScroller ) {
                this.resizeObserver.observe( this.$refs.tabsScroller as Element );
            }
        });
    },
    methods: {
        __,
        checkScroll() {
            const el = this.$refs.tabsScroller as HTMLElement;
            if ( !el ) return;
            this.canScrollRight = el.scrollWidth > el.clientWidth + el.scrollLeft + 1;
        },
        onScroll() {
            this.checkScroll();
        },
        toggle( tab ) {
            this.$emit( 'active', tab.identifier );
            this.$emit( 'changeTab', tab.identifier );
            this.tabState.next( tab );
        },
        buildChildrens( active ) {
            this.childrens  =   Array.from( this.$el.querySelectorAll( '.ns-tab-item' ) ).map( element => {
                const identifier =  element.getAttribute( 'identifier' ) || undefined;
                
                let visible     =   true;

                if ( element.getAttribute( 'visible' ) ) {
                    visible     =   element.getAttribute( 'visible' ) === 'true' ? true : false;
                }

                return {
                    el: element,
                    active: active && active === identifier ? true : false,
                    identifier,
                    closable: element.getAttribute( 'closable' ) === 'true' ? true : false,
                    initialized: false,
                    visible,
                    label: element.getAttribute( 'label' ) || __( 'Unamed Tab' )
                }
            }).filter( child => child.visible );

            /**
             * if no tabs is selected
             * we need at least to select the 
             * first tab by default.
             */
            const hasActive     =   this.childrens.filter( element => element.active ).length > 0;

            if ( ! hasActive && this.childrens.length > 0 ) {
                this.childrens[0].active    =   true;
            }

            this.childrens.forEach( children => {
                if ( children.active ) {
                    this.toggle( children );
                }
            });
        }
    },
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.tabs-scroller::-webkit-scrollbar { display: none; }
</style>