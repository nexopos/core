/**
 * Will bootstrap time and 
 * start counting
 */
import './shared/time';

import * as baseComponents  from './components/components';

import {
    createApp,
    defineAsyncComponent
}  from 'vue/dist/vue.esm-bundler';

import { NsHotPress }       from './libraries/ns-hotpress';
import VueHtmlToPaper from './libraries/html-printer';

const nsSettings                    =   defineAsyncComponent( () => import( './pages/dashboard/ns-settings.vue' ) );
const nsReset                       =   defineAsyncComponent( () => import( './pages/dashboard/reset.vue' ) );
const nsModules                     =   defineAsyncComponent( () => import( './pages/dashboard/modules.vue' ) );
const nsPermissions                 =   defineAsyncComponent( () => import( './pages/dashboard/ns-permissions.vue' ) );
const nsNotifications               =   defineAsyncComponent( () => import( './pages/dashboard/ns-notifications.vue' ) );
const nsMedia                       =   defineAsyncComponent( () => import( './pages/dashboard/ns-media.vue' ) );
const nsMediaLibrary                =   defineAsyncComponent( () => import( './pages/dashboard/ns-media-library.vue' ) );
const nsDashboard                   =   defineAsyncComponent( () => import( './pages/dashboard/home/ns-dashboard.vue' ) );

declare const window;
declare let nsExtraComponents;   
declare const nsHooks;

const nsState               =   window[ 'nsState' ];
const nsScreen              =   window[ 'nsScreen' ]; 

nsExtraComponents.nsToken       =   defineAsyncComponent( () => import( './pages/dashboard/profile/ns-token.vue' ) );

window.VueHtmlToPaper   =   VueHtmlToPaper;
window.nsHotPress   =   new NsHotPress;

const allComponents    =   Object.assign({
    nsModules,
    nsSettings,
    nsReset,
    nsPermissions,
    nsMedia,
    nsMediaLibrary,
    nsDashboard,

    nsNotifications,
    ...baseComponents
}, nsExtraComponents );

window.nsDashboardAside     =   createApp({
    data() {
        return {
            sidebar: 'visible',
            popups: [],
        }
    },
    components: {
        nsMenu : baseComponents.nsMenu,
        nsSubmenu : baseComponents.nsSubmenu,
    },
    mounted() {
        nsState.subscribe(( state ) => {
            if ( state.sidebar ) {
                this.sidebar    =   state.sidebar;
            }
        });
    }
});

window.nsDashboardOverlay   =   createApp({
    data() {
        return {
            sidebar: null,
            popups: []
        }
    },
    components: allComponents,
    mounted() {
        nsState.subscribe( state => {
            if ( state.sidebar ) {
                this.sidebar    =   state.sidebar;
            }
        });
    },
    methods: {
        /**
         * this is mean to appear only on mobile.
         * If it's clicked, the menu should hide
         */
        closeMenu() {
            nsState.setState({
                sidebar: this.sidebar === 'hidden' ? 'visible' : 'hidden'
            });
        },
    }
});

window.nsDashboardHeader    =   createApp({
    data() {
        return {
            menuToggled: false,
            sidebar: null,
        }
    },
    components: allComponents,
    methods: {
        toggleMenu() {
            this.menuToggled    =   !this.menuToggled;
        },
        toggleSideMenu() {
            if ([ 'lg', 'xl' ].includes( nsScreen.breakpoint ) ) {
                nsState.setState({ sidebar: this.sidebar === 'hidden' ? 'visible': 'hidden' });    
            } else {
                nsState.setState({ sidebar: this.sidebar === 'hidden' ? 'visible': 'hidden' });
            }
        }
    },
    mounted() {
        nsState.subscribe( ( state ) => {
            if ( state.sidebar ) {
                this.sidebar    =   state.sidebar;
            }
        })
    }
});

window.nsDashboardContent   =   createApp({});

/**
 * let's register the component that has
 * a valid name globally
 */
for( let name in allComponents ) {
    window.nsDashboardContent.component( name, allComponents[ name ] );
}

/**
 * let's add the library
 * to the body dashboard content
 */
window.nsDashboardContent.use( VueHtmlToPaper, {
    styles: Object.values( window.ns.cssFiles )
});

window.nsComponents          =   Object.assign( allComponents, baseComponents );
/**
 * If anything has to happen before mounting
 * that will be the place to do it.
 */
nsHooks.doAction( 'ns-before-mount' );

const dashboardAsideElement = document.querySelector('#dashboard-aside');
if (window.nsDashboardAside && dashboardAsideElement) {
    window.nsDashboardAside.mount(dashboardAsideElement);
}

const dashboardOverlayElement = document.querySelector('#dashboard-overlay');
if (window.nsDashboardOverlay && dashboardOverlayElement) {
    window.nsDashboardOverlay.mount(dashboardOverlayElement);
}

const dashboardHeaderElement = document.querySelector('#dashboard-header');
if (window.nsDashboardHeader && dashboardHeaderElement) {
    window.nsDashboardHeader.mount(dashboardHeaderElement);
}

const dashboardContentElement = document.querySelector('#dashboard-content');
if (window.nsDashboardContent && dashboardContentElement) {
    window.nsDashboardContent.mount(dashboardContentElement);
}
