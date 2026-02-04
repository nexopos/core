@include( 'ns::common.footer-options' )
@if( env( 'BROADCAST_DRIVER' ) === 'reverb' )
<script>
document.addEventListener( 'DOMContentLoaded', () => {
    /**
     * We'll start the Echo configuration
     * from here as on the bundled file, it stores envronment details.
     */
    window.Echo = new EchoClass({
        broadcaster: 'reverb',
        key: '<?php echo env( 'REVERB_APP_KEY' );?>',
        wsHost: '<?php echo env( 'REVERB_HOST');?>',
        wsPort: '<?php echo env( 'REVERB_PORT');?>',
        wssPort: '<?php echo env( 'REVERB_PORT');?>',
        forceTLS: <?php echo ( env( 'REVERB_SCHEME' ) ?? 'https') === 'https' ? 'true' : 'false';?>,
        enabledTransports: ['ws'],
    });
});
</script>
@endif
@nsvite([ 'resources/ts/bootstrap.ts' ])
@include( 'ns::layout._footer-injection' )
@yield( 'layout.dashboard.footer.inject' )