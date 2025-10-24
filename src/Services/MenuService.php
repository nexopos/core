<?php

namespace Ns\Services;

use Illuminate\Support\Facades\Gate;
use Ns\Classes\AsideMenu;
use Ns\Classes\Menu;
use TorMorten\Eventy\Facades\Eventy as Hook;

class MenuService
{
    protected $menus;

    protected $accountMenus     =    [];

    public function buildMenus()
    {
        $this->menus = AsideMenu::wrapper(
            AsideMenu::menu(
                label: __( 'Dashboard' ),
                icon: 'la-home',
                identifier: 'dashboard',
                permissions: [ 'read.dashboard' ],
                childrens: AsideMenu::childrens(
                    AsideMenu::subMenu(
                        label: __( 'Home' ),
                        identifier: 'index',
                        permissions: [ 'read.dashboard' ],
                        href: nsUrl( '/dashboard' )
                    )
                ),
            ),

            AsideMenu::menu(
                label: __( 'Medias' ),
                icon: 'la-photo-video',
                identifier: 'medias',
                permissions: [ 'nexopos.upload.medias', 'nexopos.see.medias' ],
                href: nsUrl( '/dashboard/medias' ),
            ),

            AsideMenu::menu(
                label: __( 'Modules' ),
                icon: 'la-plug',
                identifier: 'modules',
                permissions: [ 'manage.modules' ],
                childrens: AsideMenu::childrens(
                    AsideMenu::subMenu(
                        label: __( 'List' ),
                        identifier: 'modules',
                        href: nsUrl( '/dashboard/modules' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'Upload Module' ),
                        identifier: 'upload-module',
                        href: nsUrl( '/dashboard/modules/upload' )
                    ),
                ),
            ),
            AsideMenu::menu(
                label: __( 'Users' ),
                icon: 'la-users',
                identifier: 'users',
                permissions: [ 'read.users', 'manage.profile', 'create.users' ],
                childrens: AsideMenu::childrens(
                    AsideMenu::subMenu(
                        label: __( 'My Profile' ),
                        identifier: 'profile',
                        permissions: [ 'manage.profile' ],
                        href: nsUrl( '/dashboard/users/profile' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'Users List' ),
                        identifier: 'users',
                        permissions: [ 'read.users' ],
                        href: nsUrl( '/dashboard/users' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'Create User' ),
                        identifier: 'create-user',
                        permissions: [ 'create.users' ],
                        href: nsUrl( '/dashboard/users/create' )
                    ),
                ),
            ),
            AsideMenu::menu(
                label: __( 'Roles' ),
                icon: 'la-shield-alt',
                identifier: 'roles',
                permissions: [ 'read.roles', 'create.roles', 'update.roles' ],
                childrens: AsideMenu::childrens(
                    AsideMenu::subMenu(
                        label: __( 'Roles' ),
                        identifier: 'all-roles',
                        permissions: [ 'read.roles' ],
                        href: nsUrl( '/dashboard/users/roles' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'Create Roles' ),
                        identifier: 'create-role',
                        permissions: [ 'create.roles' ],
                        href: nsUrl( '/dashboard/users/roles/create' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'Permissions Manager' ),
                        identifier: 'permissions',
                        permissions: [ 'update.roles' ],
                        href: nsUrl( '/dashboard/users/roles/permissions-manager' )
                    ),
                ),
            ),
            AsideMenu::menu(
                label: __( 'Settings' ),
                icon: 'la-cogs',
                identifier: 'settings',
                permissions: [ 'manage.options' ],
                childrens: AsideMenu::childrens(
                    AsideMenu::subMenu(
                        label: __( 'General' ),
                        identifier: 'general',
                        href: nsUrl( '/dashboard/settings/general' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'Reset' ),
                        identifier: 'reset',
                        href: nsUrl( '/dashboard/settings/reset' )
                    ),
                    AsideMenu::subMenu(
                        label: __( 'About' ),
                        identifier: 'about',
                        href: nsUrl( '/dashboard/settings/about' )
                    ),
                ),
            ),
        );
    }

    /**
     * returns the list of available menus
     *
     * @return array of menus
     */
    public function getMenus()
    {
        $this->buildMenus();
        $this->menus = Hook::filter( 'ns-dashboard-menus', $this->menus );
        $this->toggleActive();

        return collect( $this->menus )->filter( function ( $menu ) {
            return ( ! isset( $menu[ 'permissions' ] ) || Gate::any( $menu[ 'permissions' ] ) ) && ( ! isset( $menu[ 'show' ] ) || $menu[ 'show' ] === true );
        } )->map( function ( $menu ) {
            $menu[ 'childrens' ] = collect( $menu[ 'childrens' ] ?? [] )->filter( function ( $submenu ) {
                return ! isset( $submenu[ 'permissions' ] ) || Gate::any( $submenu[ 'permissions' ] );
            } )->toArray();

            return $menu;
        } );
    }

    /**
     * Will make sure active menu
     * is toggled
     *
     * @return void
     */
    public function toggleActive()
    {
        foreach ( $this->menus as $identifier => &$menu ) {
            if ( isset( $menu[ 'href' ] ) && $menu[ 'href' ] === url()->current() ) {
                $menu[ 'toggled' ] = true;
            }

            if ( isset( $menu[ 'childrens' ] ) ) {
                foreach ( $menu[ 'childrens' ] as $subidentifier => &$submenu ) {
                    if ( $submenu[ 'href' ] === url()->current() ) {
                        $menu[ 'toggled' ] = true;
                        $submenu[ 'active' ] = true;
                    }
                }
            }
        }
    }
    
    /**
     * Adds an account menu
     *
     * @param string $identifier
     * @param string $label
     * @param string $icon
     * @param string $href
     */
    public function setAccountMenu( $identifier, $label, $icon, $href )
    {
        $this->accountMenus[ $identifier ] = AsideMenu::menu(
            label: $label,
            icon: $icon,
            identifier: $identifier,
            href: $href,
        );
    }

    /**
     * Returns the account menus
     * @return array
     */
    public function getAccountMenus(): array
    {
        $this->accountMenus = Hook::filter( 'ns-account-menus', Menu::wrapper(
            Menu::item(
                label: __( 'Profile' ),
                identifier: 'profile',
                icon: 'la-user-tie',
                href: nsRoute( 'ns.dashboard.users.profile' ),
                permissions: [ 'manage.profile' ],
            ),
            Menu::item(
                label: __( 'Logout' ),
                identifier: 'logout',
                icon: 'la-sign-out-alt',
                href: nsRoute( 'ns.logout' ),
            ),
        ) );

        return collect( $this->accountMenus )->filter( function ( $menu ) {
            return ( ! isset( $menu[ 'permissions' ] ) || Gate::any( $menu[ 'permissions' ] ) ) && ( ! isset( $menu[ 'show' ] ) || $menu[ 'show' ] === true );
        } )->toArray();
    }

    /**
     * Remove an account menu by its identifier
     * @param string $identifier
     */
    public function removeAccountMenu( $identifier )
    {
        if ( isset( $this->accountMenus[ $identifier ] ) ) {
            unset( $this->accountMenus[ $identifier ] );
        }
    }
}
