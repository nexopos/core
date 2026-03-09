<?php
use Ns\Services\DateService;
use Ns\Services\Helper;
use Ns\Services\MenuService;
use Illuminate\Support\Facades\Auth;

/**
 * @var MenuService $menus
 */
$menus  =   app()->make( MenuService::class );

/**
 * @var MenuService $menus
 */
$dateService  =   app()->make( DateService::class );

if ( Auth::check() ) {
    $theme  =   Auth::user()->attribute->theme ?: ns()->option->get( 'ns_default_theme', 'light' );
} else {
    $theme  =   ns()->option->get( 'ns_default_theme', 'light' );
}
?>
<!DOCTYPE html>
<html lang="en" class="{{ $theme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{!! Helper::pageTitle( $title ?? __( 'Unamed Page' ) ) !!}</title>
    @include( 'ns::layout._header-injection' )
    @nsvite([
        'resources/scss/line-awesome/1.3.0/scss/line-awesome.scss',
        'resources/css/animations.css',
        'resources/css/fonts.css',
        'resources/css/grid.css',
        'resources/css/' . $theme . '.css'
    ])
    @yield( 'layout.dashboard.header' )
    @include( 'ns::layout._header-script' )
    @nsvite([ 'resources/ts/lang-loader.ts' ])
</head>
<body <?php echo in_array( app()->getLocale(), config( 'nexopos.rtl-languages' ) ) ? 'dir="rtl"' : "";?>>
    <div class="h-full w-full flex flex-col">
        <div class="overflow-hidden flex flex-auto">
            <div id="dashboard-body" class="flex flex-auto flex-col overflow-hidden">
                <div class="overflow-y-auto flex-auto">
                    @hassection( 'layout.dashboard.body' )
                        @yield( 'layout.dashboard.body' )
                    @endif

                    @hassection( 'layout.dashboard.body.with-header' )
                        @include( 'ns::common.dashboard.with-header' )
                    @endif

                    @hassection( 'layout.dashboard.with-header' )
                        @include( 'ns::common.dashboard.with-header' )
                    @endif

                    @hassection( 'layout.dashboard.body.with-title' )
                        @include( 'ns::common.dashboard.with-title' )
                    @endif

                    @hassection( 'layout.dashboard.with-title' )
                        @include( 'ns::common.dashboard.with-title' )
                    @endif
                </div>
            </div>
        </div>
    </div>
    @section( 'layout.dashboard.footer' )
        @include( 'ns::common.popups' )
        @include( 'ns::common.dashboard-footer' )
        @nsvte([ 'resources/ts/app.ts' ])
    @show
</body>
</html>
