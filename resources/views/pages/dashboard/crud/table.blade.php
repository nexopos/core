<?php
use Ns\Classes\Output;
use Ns\Events\RenderCrudTableFooterEvent;
?>
@extends( 'ns::layout.dashboard' )

@section( 'layout.dashboard.body' )
<div>
    @include( Hook::filter( 'ns-dashboard-header-file', 'ns::common/dashboard-header' ) )
    <div id="dashboard-content" class="px-4">
        @include( 'ns::common.dashboard.title' )
        <ns-crud 
            src="{{ $src }}" 
            :query-params='@json( $queryParams ?? [] )'
            create-url="{{ $createUrl ?? '#' }}">
            <template v-slot:bulk-label>{{ $bulkLabel ?? __( 'Bulk Actions' ) }}</template>
        </ns-crud>
    </div>
</div>
@endsection

@section( 'layout.dashboard.footer' )
    @parent
<?php
$output     =   new Output;

/**
 * We might actually check if the instance has a "getTableFooter" methods
 * If it's the case, we might additionnally call that method.
 */
if ( method_exists( $instance, 'getTableFooter' ) ) {
    $instance->getTableFooter( $output );
}

RenderCrudTableFooterEvent::dispatch( $output, $instance );
echo $output;
?>
@endsection