<?php

namespace Ns\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ns\Exceptions\NotAllowedException;
use Ns\Services\Options;

class SignUpRequest extends FormRequest
{
    public $redirectRoute = 'ns.register';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $options = app()->make( Options::class );

        if ( $options->get( 'ns_registration_enabled' ) !== 'yes' ) {
            throw new NotAllowedException( __( 'Unable to register. The registration is closed.' ) );
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|min:3',
            'email' => 'email',
            'password' => 'required',
            'password_confirm' => 'same:password',
        ];
    }
}
