<?php

namespace Ns\Crud;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Ns\Casts\GenderCast;
use Ns\Casts\NotDefinedCast;
use Ns\Casts\YesNoBoolCast;
use Ns\Classes\CrudForm;
use Ns\Classes\CrudTable;
use Ns\Classes\FormInput;
use Ns\Classes\JsonResponse;
use Ns\Events\UserAfterActivationSuccessfulEvent;
use Ns\Exceptions\NotAllowedException;
use Ns\Models\Role;
use Ns\Models\User;
use Ns\Models\UserBillingAddress;
use Ns\Models\UserShippingAddress;
use Ns\Services\CrudEntry;
use Ns\Services\CrudService;
use Ns\Services\Helper;
use Ns\Services\Options;
use Ns\Services\UsersService;
use TorMorten\Eventy\Facades\Events as Hook;

class UserCrud extends CrudService
{
    /**
     * Define the autoload status
     */
    const AUTOLOAD = true;

    /**
     * Define the identifier
     */
    const IDENTIFIER = 'ns.users';

    /**
     * define the base table
     */
    protected $table = 'users';

    /**
     * base route name
     */
    protected $mainRoute = 'ns.users';

    /**
     * Define namespace
     *
     * @param  string
     */
    protected $namespace = 'ns.users';

    /**
     * Model Used
     */
    protected $model = User::class;

    /**
     * Determine if the options column should display
     * before the crud columns
     */
    protected $prependOptions = true;

    /**
     * Adding relation
     */
    public $relations = [
        'leftJoin' => [
            [ 'users as author', 'users.author', '=', 'author.id' ],
        ],
    ];

    public $pick = [
        'author' => [ 'username' ],
        'role' => [ 'name' ],
        'group' => [ 'id', 'name' ],
    ];

    protected $permissions = [
        'create' => 'create.users',
        'read' => 'read.users',
        'update' => 'update.users',
        'delete' => 'delete.users',
    ];

    /**
     * Define where statement
     *
     * @var array
     **/
    protected $listWhere = [];

    /**
     * Define where in statement
     *
     * @var array
     */
    protected $whereIn = [];

    /**
     * Fields which will be filled during post/put
     */
    public $fillable = [
        'username',
        'email',
        'password',
        'active',
        'role_id',
        'group_id',
        'first_name',
        'last_name',
        'phone',
        'gender',
        'pobox',
    ];

    protected $tabsRelations = [
        'shipping' => [ UserShippingAddress::class, 'user_id', 'id' ],
        'billing' => [ UserBillingAddress::class, 'user_id', 'id' ],
    ];

    protected $casts = [
        'first_name' => NotDefinedCast::class,
        'last_name' => NotDefinedCast::class,
        'phone' => NotDefinedCast::class,
        'gender' => GenderCast::class,
        'active' => YesNoBoolCast::class,
    ];

    private Options $options;

    private UsersService $userService;

    /**
     * Define Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->userService = app()->make( UsersService::class );
        $this->options = app()->make( Options::class );
    }

    /**
     * Return the label used for the crud
     * instance
     *
     * @return array
     **/
    public function getLabels()
    {
        return [
            'list_title' => __( 'Users List' ),
            'list_description' => __( 'Display all users.' ),
            'no_entry' => __( 'No users has been registered' ),
            'create_new' => __( 'Add a new user' ),
            'create_title' => __( 'Create a new user' ),
            'create_description' => __( 'Register a new user and save it.' ),
            'edit_title' => __( 'Edit user' ),
            'edit_description' => __( 'Modify  User.' ),
            'back_to_list' => __( 'Return to Users' ),
        ];
    }

    /**
     * Check whether a feature is enabled
     *
     **/
    public function isEnabled( $feature ): bool
    {
        return false; // by default
    }

    /**
     * Fields
     *
     * @param  object/null
     * @return array of field
     */
    public function getForm( $entry = null )
    {
        return CrudForm::form(
            main: FormInput::text(
                label: __( 'Username' ),
                name: 'username',
                value: $entry->username ?? '',
                validation: $entry === null ? 'required|unique:users,username' : [
                    'required',
                    Rule::unique( 'users', 'username' )->ignore( $entry->id ),
                ],
                description: __( 'Provide a name to the resource.' ),
            ),
            tabs: CrudForm::tabs(
                CrudForm::tab(
                    identifier: 'general',
                    label: __( 'General' ),
                    fields: CrudForm::fields(
                        FormInput::text(
                            label: __( 'Email' ),
                            name: 'email',
                            value: $entry->email ?? '',
                            validation: $entry === null ? 'required|email|unique:users,email' : [
                                'required',
                                'email',
                                Rule::unique( 'users', 'email' )->ignore( $entry->id ),
                            ],
                            description: __( 'Will be used for various purposes such as email recovery.' ),
                        ),
                        FormInput::text(
                            label: __( 'First Name' ),
                            name: 'first_name',
                            value: $entry?->first_name,
                            description: __( 'Provide the user first name.' ),
                        ),
                        FormInput::text(
                            label: __( 'Last Name' ),
                            name: 'last_name',
                            value: $entry?->last_name,
                            description: __( 'Provide the user last name.' ),
                        ),
                        FormInput::password(
                            label: __( 'Password' ),
                            name: 'password',
                            validation: 'sometimes|min:6',
                            value: null,
                            description: __( 'Make a unique and secure password.' ),
                        ),
                        FormInput::password(
                            label: __( 'Confirm Password' ),
                            name: 'password_confirm',
                            validation: 'sometimes|same:general.password',
                            value: null,
                            description: __( 'Should be the same as the password.' ),
                        ),
                        FormInput::switch(
                            label: __( 'Active' ),
                            name: 'active',
                            options: Helper::kvToJsOptions( [ __( 'No' ), __( 'Yes' ) ] ),
                            value: ( $entry !== null && $entry->active ? 1 : 0 ) ?? 0,
                            description: __( 'Define whether the user can use the application.' ),
                        ),
                        FormInput::multiselect(
                            label: __( 'Roles' ),
                            name: 'roles',
                            options: Helper::toJsOptions( Role::get(), [ 'id', 'name' ] ),
                            value: $entry !== null ? ( $entry->roles()->get()->map( fn( $role ) => $role->id )->toArray() ?? '' ) : [],
                            description: __( 'Define what roles applies to the user' ),
                        ),
                        FormInput::datetime(
                            label: __( 'Birth Date' ),
                            name: 'birth_date',
                            value: $entry instanceof User && $entry->birth_date !== null ? Carbon::parse( $entry->birth_date )->format( 'Y-m-d H:i:s' ) : null,
                            description: __( 'Displays the customer birth date.' ),
                        ),
                        FormInput::text(
                            label: __( 'Credit Limit' ),
                            name: 'credit_limit_amount',
                            value: $entry?->credit_limit_amount,
                            description: __( 'Set the limit that can\'t be exceeded by the user.' ),
                        ),
                        FormInput::select(
                            label: __( 'Gender' ),
                            name: 'gender',
                            options: Helper::kvToJsOptions( [
                                '' => __( 'Not Defined' ),
                                'male' => __( 'Male' ),
                                'female' => __( 'Female' ),
                            ] ),
                            value: $entry?->gender,
                            description: __( 'Select the gender of the user.' ),
                        ),
                        FormInput::text(
                            label: __( 'Phone' ),
                            name: 'phone',
                            value: $entry?->phone,
                            validation: collect( [
                                ns()->option->get( 'ns_customers_force_unique_phone', 'no' ) === 'yes' ? (
                                    $entry instanceof User && ! empty( $entry->phone ) ? Rule::unique( 'users', 'phone' )->ignore( $entry->id ) : Rule::unique( 'users', 'phone' )
                                ) : '',
                            ] )->toArray(),
                            description: __( 'Set the user phone number.' ),
                        ),
                        FormInput::text(
                            label: __( 'PO box' ),
                            name: 'pobox',
                            value: $entry?->pobox,
                            description: __( 'Set the user PO box.' ),
                        )
                    )
                ),
                CrudForm::tab(
                    identifier: 'billing',
                    label: __( 'Billing Address' ),
                    fields: CrudForm::fields(
                        FormInput::text(
                            label: __( 'First Name' ),
                            name: 'first_name',
                            value: $entry->billing->first_name ?? '',
                            description: __( 'Provide the billing First Name.' ),
                        ),
                        FormInput::text(
                            label: __( 'Last name' ),
                            name: 'last_name',
                            value: $entry->billing->last_name ?? '',
                            description: __( 'Provide the billing last name.' ),
                        ),
                        FormInput::text(
                            label: __( 'Phone' ),
                            name: 'phone',
                            value: $entry->billing->phone ?? '',
                            description: __( 'Billing phone number.' ),
                        ),
                        FormInput::text(
                            label: __( 'Address 1' ),
                            name: 'address_1',
                            value: $entry->billing->address_1 ?? '',
                            description: __( 'Billing First Address.' ),
                        ),
                        FormInput::text(
                            label: __( 'Address 2' ),
                            name: 'address_2',
                            value: $entry->billing->address_2 ?? '',
                            description: __( 'Billing Second Address.' ),
                        ),
                        FormInput::text(
                            label: __( 'Country' ),
                            name: 'country',
                            value: $entry->billing->country ?? '',
                            description: __( 'Billing Country.' ),
                        ),
                        FormInput::text(
                            label: __( 'City' ),
                            name: 'city',
                            value: $entry->billing->city ?? '',
                            description: __( 'City' ),
                        ),
                        FormInput::text(
                            label: __( 'PO.Box' ),
                            name: 'pobox',
                            value: $entry->billing->pobox ?? '',
                            description: __( 'Postal Address' ),
                        ),
                        FormInput::text(
                            label: __( 'Company' ),
                            name: 'company',
                            value: $entry->billing->company ?? '',
                            description: __( 'Company' ),
                        ),
                        FormInput::text(
                            label: __( 'Email' ),
                            name: 'email',
                            value: $entry->billing->email ?? '',
                            description: __( 'Email' ),
                        )
                    )
                ),
                CrudForm::tab(
                    identifier: 'shipping',
                    label: __( 'Shipping Address' ),
                    fields: CrudForm::fields(
                        FormInput::text(
                            label: __( 'First Name' ),
                            name: 'first_name',
                            value: $entry->shipping->first_name ?? '',
                            description: __( 'Provide the shipping First Name.' ),
                        ),
                        FormInput::text(
                            label: __( 'Last Name' ),
                            name: 'last_name',
                            value: $entry->shipping->last_name ?? '',
                            description: __( 'Provide the shipping Last Name.' ),
                        ),
                        FormInput::text(
                            label: __( 'Phone' ),
                            name: 'phone',
                            value: $entry->shipping->phone ?? '',
                            description: __( 'Shipping phone number.' ),
                        ),
                        FormInput::text(
                            label: __( 'Address 1' ),
                            name: 'address_1',
                            value: $entry->shipping->address_1 ?? '',
                            description: __( 'Shipping First Address.' ),
                        ),
                        FormInput::text(
                            label: __( 'Address 2' ),
                            name: 'address_2',
                            value: $entry->shipping->address_2 ?? '',
                            description: __( 'Shipping Second Address.' ),
                        ),
                        FormInput::text(
                            label: __( 'Country' ),
                            name: 'country',
                            value: $entry->shipping->country ?? '',
                            description: __( 'Shipping Country.' ),
                        ),
                        FormInput::text(
                            label: __( 'City' ),
                            name: 'city',
                            value: $entry->shipping->city ?? '',
                            description: __( 'City' ),
                        ),
                        FormInput::text(
                            label: __( 'PO.Box' ),
                            name: 'pobox',
                            value: $entry->shipping->pobox ?? '',
                            description: __( 'Postal Address' ),
                        ),
                        FormInput::text(
                            label: __( 'Company' ),
                            name: 'company',
                            value: $entry->shipping->company ?? '',
                            description: __( 'Company' ),
                        ),
                        FormInput::text(
                            label: __( 'Email' ),
                            name: 'email',
                            value: $entry->shipping->email ?? '',
                            description: __( 'Email' ),
                        )
                    )
                )
            )
        );
    }

    /**
     * Filter POST input fields
     *
     * @param  array of fields
     * @return array of fields
     */
    public function filterPostInputs( $inputs )
    {
        unset( $inputs[ 'password_confirm' ] );

        /**
         * if the password is not changed, no
         * need to hash it
         */
        $inputs = collect( $inputs )->filter( fn( $input ) => ! empty( $input ) || $input === 0 )->toArray();

        if ( ! empty( $inputs[ 'password' ] ) ) {
            $inputs[ 'password' ] = Hash::make( $inputs[ 'password' ] );
        }

        return $inputs;
    }

    /**
     * Filter PUT input fields
     *
     * @param  array of fields
     * @return array of fields
     */
    public function filterPutInputs( $inputs, User $entry )
    {
        unset( $inputs[ 'password_confirm' ] );

        /**
         * if the password is not changed, no
         * need to hash it
         */
        $inputs = collect( $inputs )->filter( fn( $input ) => ! empty( $input ) || $input === 0 )->toArray();

        if ( ! empty( $inputs[ 'password' ] ) ) {
            $inputs[ 'password' ] = Hash::make( $inputs[ 'password' ] );
        }

        return $inputs;
    }

    /**
     * After saving a record
     *
     * @param  Request $request
     * @return void
     */
    public function afterPost( $request, User $entry )
    {
        if ( isset( $request[ 'roles'] ) ) {
            $this->userService
                ->setUserRole(
                    $entry,
                    $request[ 'roles' ]
                );

            $this->userService->createAttribute( $entry );

            /**
             * While creating the user, if we set that user as active
             * we'll dispatch the activation successful event.
             */
            if ( $entry->active ) {
                UserAfterActivationSuccessfulEvent::dispatch( $entry );
            }
        }

        return $request;
    }

    /**
     * get
     *
     * @param  string
     * @return mixed
     */
    public function get( $param )
    {
        switch ( $param ) {
            case 'model': return $this->model;
                break;
        }
    }

    /**
     * Before updating a record
     *
     * @param Request $request
     * @param  object entry
     * @return void
     */
    public function beforePut( $request, $entry )
    {
        $this->allowedTo( 'update' );

        return $request;
    }

    /**
     * After updating a record
     *
     * @param Request $request
     * @param  object entry
     * @return void
     */
    public function afterPut( $request, User $entry )
    {
        if ( isset( $request[ 'roles'] ) ) {
            $this->userService
                ->setUserRole(
                    $entry,
                    $request[ 'roles' ]
                );

            $this->userService->createAttribute( $entry );

            /**
             * While creating the user, if we set that user as active
             * we'll dispatch the activation successful event.
             */
            if ( $entry->active ) {
                UserAfterActivationSuccessfulEvent::dispatch( $entry );
            }
        }

        return $request;
    }

    /**
     * Before Delete
     *
     * @return void
     */
    public function beforeDelete( $namespace, int $id, $model )
    {
        if ( $namespace == 'ns.users' ) {
            $this->allowedTo( 'delete' );

            if ( $id === Auth::id() ) {
                throw new NotAllowedException( __( 'You cannot delete your own account.' ) );
            }
        }
    }

    /**
     * Define Columns
     */
    public function getColumns(): array
    {
        return CrudTable::columns(
            CrudTable::column(
                identifier: 'username',
                label: __( 'Username' ),
                attributes: CrudTable::attributes(
                    CrudTable::attribute(
                        column: 'active',
                        label: __( 'Active' )
                    ),
                )
            ),
            CrudTable::column(
                label: __( 'Email' ),
                identifier: 'email',
            ),
            CrudTable::column(
                label: __( 'Roles' ),
                identifier: 'rolesNames',
                sort: false
            ),
            CrudTable::column(
                label: __( 'Created At' ),
                identifier: 'created_at'
            )
        );
    }

    /**
     * Define actions
     */
    public function setActions( CrudEntry $entry ): CrudEntry
    {
        $entry->action(
            identifier: 'edit_customers_group',
            label: __( 'Edit' ),
            permissions: [ 'update.users' ],
            url: nsUrl( 'dashboard/users/edit/' . $entry->id ),
        );

        $entry->action(
            identifier: 'delete',
            label: __( 'Delete' ),
            type: 'DELETE',
            url: nsUrl( '/api/crud/ns.users/' . $entry->id ),
            confirm: [
                'message' => __( 'Would you like to delete this ?' ),
                'title' => __( 'Delete a user' ),
            ],
        );

        $roles = User::find( $entry->id )->roles()->get();
        $entry->rolesNames = $roles->map( fn( $role ) => $role->name )->join( ', ' ) ?: __( 'Not Assigned' );

        return $entry;
    }

    /**
     * Bulk Delete Action
     *
     * @param    object Request with object
     * @return  false/array
     */
    public function bulkAction( Request $request )
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */
        $user = app()->make( UsersService::class );
        if ( ! $user->is( [ 'admin', 'supervisor' ] ) ) {
            return JsonResponse::error(
                message: __( 'You\'re not allowed to do this operation' )
            );
        }

        if ( $request->input( 'action' ) == 'delete_selected' ) {

            $this->allowedTo( 'delete' );

            $status = [
                'success' => 0,
                'error' => 0,
            ];

            foreach ( $request->input( 'entries' ) as $id ) {
                $entity = $this->model::find( $id );
                if ( $entity instanceof User ) {
                    $entity->delete();
                    $status[ 'success' ]++;
                } else {
                    $status[ 'error' ]++;
                }
            }

            return $status;
        }

        return Hook::filter( $this->namespace . '-catch-action', false, $request );
    }

    /**
     * get Links
     *
     * @return array of links
     */
    public function getLinks(): array
    {
        return [
            'list' => nsUrl( 'dashboard/' . 'users' ),
            'create' => nsUrl( 'dashboard/' . 'users/create' ),
            'edit' => nsUrl( 'dashboard/' . 'users/edit/' ),
            'post' => nsUrl( 'api/crud/' . 'ns.users' ),
            'put' => nsUrl( 'api/crud/' . 'ns.users/{id}' . '' ),
        ];
    }

    /**
     * Get Bulk actions
     *
     * @return array of actions
     **/
    public function getBulkActions(): array
    {
        return Hook::filter( $this->namespace . '-bulk', [
            [
                'label' => __( 'Delete Selected Users' ),
                'identifier' => 'delete_selected',
                'url' => nsRoute( 'ns.api.crud-bulk-actions', [
                    'namespace' => $this->namespace,
                ] ),
            ],
        ] );
    }

    /**
     * get exports
     *
     * @return array of export formats
     **/
    public function getExports()
    {
        return [];
    }
}
