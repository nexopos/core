<?php
use Ns\Classes\Hook;
use Ns\Classes\Output;
use Ns\Events\RenderBeforePasswordRecoveryFormEvent;
use Ns\Events\RenderAfterPasswordRecoveryFormEvent;
use Ns\Events\RenderPasswordLostFooterEvent;
?>

@extends( 'ns::layout.base' )

@section( 'layout.base.body' )
    <div id="page-container" class="h-full w-full flex items-center overflow-y-auto pb-10 bg-gray-300">
        <div class="container mx-auto p-4 md:p-0 flex-auto items-center justify-center flex">
            <div id="sign-in-box" class="w-full md:w-3/5 lg:w-2/5">
                <div class="flex justify-center items-center py-6">
                    <img class="w-32" src="{{ asset( 'vendor/ns/svg/nexopos-variant-1.svg' ) }}" alt="NexoPOS">
                </div>
                {!! Output::dispatch( RenderBeforePasswordRecoveryFormEvent::class ); !!}
                @include( '/common/auth/password-recovery-form' )
                {!! Output::dispatch( RenderAfterPasswordRecoveryFormEvent::class ); !!}
            </div>
        </div>
    </div>
@endsection

@section( 'layout.base.footer' )
    @parent
    {!! Output::dispatch( RenderPasswordLostFooterEvent::class ); !!}
    @nsvite([ 'resources/ts/auth.ts' ])
@endsection