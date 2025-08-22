<?php

namespace Ns\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Ns\Events\UserAfterActivationSuccessfulEvent;
use Ns\Models\User;

class SetupService
{
    public Options $options;

    /**
     * Attempt database and save db informations
     *
     * @return void
     */
    public function saveDatabaseSettings( Request $request )
    {
        $databaseDriver = $request->input( 'database_driver' );

        config( [ 'database.connections.test' => [
            'driver' => $request->input( 'database_driver' ) ?: 'mysql',
            'host' => $request->input( 'hostname' ),
            'port' => $request->input( 'database_port' ) ?: env( 'DB_PORT', '3306' ),
            'database' => $request->input( 'database_driver' ) === 'sqlite' ? database_path( 'database.sqlite' ) : $request->input( 'database_name' ),
            'username' => $request->input( 'username' ),
            'password' => $request->input( 'password' ),
            'unix_socket' => env( 'DB_SOCKET', '' ),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => $request->input( 'database_prefix' ),
            'strict' => true,
            'engine' => null,
        ]] );

        try {
            DB::connection( 'test' )->getPdo();
        } catch ( \Exception $e ) {
            switch ( $e->getCode() ) {
                case 2002:
                    $message = [
                        'name' => 'hostname',
                        'message' => __( 'Unable to reach the host' ),
                        'status' => 'error',
                    ];
                    break;
                case 1045:
                    $message = [
                        'name' => 'username',
                        'message' => __( 'Unable to connect to the database using the credentials provided.' ),
                        'status' => 'error',
                    ];
                    break;
                case 1049:
                    $message = [
                        'name' => 'database_name',
                        'message' => __( 'Unable to select the database.' ),
                        'status' => 'error',
                    ];
                    break;
                case 1044:
                    $message = [
                        'name' => 'username',
                        'message' => __( 'Access denied for this user.' ),
                        'status' => 'error',
                    ];
                    break;
                case 1698:
                    $message = [
                        'name' => 'username',
                        'message' => __( 'Incorrect Authentication Plugin Provided.' ),
                        'status' => 'error',
                    ];
                    break;
                default:
                    $message = [
                        'name' => 'hostname',
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ];
                    break;
            }

            return response()->json( $message, 403 );
        }

        // we'll empty the database
        file_put_contents( database_path( 'database.sqlite' ), '' );

        $this->updateAppUrl();
        $this->updateAppDBConfiguration( $request->post() );

        /**
         * Link the resource storage
         */
        Artisan::call( 'storage:link', [ '--force' => true ] );

        return [
            'status' => 'success',
            'message' => __( 'The connexion with the database was successful' ),
        ];
    }

    public function updateAppURL()
    {
        $domain = parse_url( url()->to( '/' ) );

        ns()->envEditor->set( 'APP_URL', url()->to( '/' ) );
        ns()->envEditor->set( 'SESSION_DOMAIN', $domain[ 'host' ] );
        ns()->envEditor->set( 'SANCTUM_STATEFUL_DOMAINS', $domain[ 'host' ] . ( isset( $domain[ 'port' ] ) ? ':' . $domain[ 'port' ] : '' ) );

        ns()->envEditor->set( 'REVERB_APP_ID', 'app-key-' . Str::random( 10 ) );
        ns()->envEditor->set( 'REVERB_APP_KEY', 'app-key-' . Str::random( 10 ) );
        ns()->envEditor->set( 'REVERB_APP_SECRET', Str::uuid() );
    }

    public function updateAppDBConfiguration( $data )
    {
        ns()->envEditor->set( 'DB_CONNECTION', $data[ 'database_driver' ] );

        if ( $data[ 'database_driver' ] === 'sqlite' ) {
            ns()->envEditor->set( 'DB_DATABASE', database_path( 'database.sqlite' ) );
            ns()->envEditor->set( 'DB_PREFIX', $data[   'database_prefix' ] );
        } elseif ( $data[ 'database_driver' ] === 'mysql' ) {
            ns()->envEditor->set( 'DB_HOST', $data[ 'hostname' ] );
            ns()->envEditor->set( 'DB_DATABASE', $data[ 'database_name' ] ?: database_path( 'database.sqlite' ) );
            ns()->envEditor->set( 'DB_USERNAME', $data[ 'username' ] );
            ns()->envEditor->set( 'DB_PASSWORD', $data[ 'password' ] );
            ns()->envEditor->set( 'DB_PREFIX', $data[   'database_prefix' ] );
            ns()->envEditor->set( 'DB_PORT', $data[ 'database_port' ] ?: 3306 );
        }
    }

    /**
     * Run migration
     *
     * @param Http Request
     * @return void
     */
    public function runMigration( $fields )
    {
        /**
         * We assume so far the application is installed
         * then we can launch option service
         */
        $configuredLanguage = $fields[ 'language' ] ?? 'en';

        App::setLocale( $configuredLanguage );

        /**
         * From this moment, new permissions has been created.
         * However Laravel gates aren't aware of them. We'll fix this here.
         */
        ns()->registerGatePermissions();

        $userID = rand( 1, 99 );
        $user = new User;
        $user->id = $userID;
        $user->username = $fields[ 'admin_username' ];
        $user->password = Hash::make( $fields[ 'password' ] );
        $user->email = $fields[ 'admin_email' ];
        $user->author = $userID;
        $user->active = true; // first user active by default;
        $user->save();
        $user->assignRole( 'admin' );

        /**
         * define default user language
         */
        $user->attribute()->create( [
            'language' => $fields[ 'language' ] ?? 'en',
        ] );

        UserAfterActivationSuccessfulEvent::dispatch( $user );

        $this->options = app()->make( Options::class );
        $this->options->setDefault();
        $this->options->set( 'ns_store_language', $configuredLanguage );

        return [
            'status' => 'success',
            'message' => __( 'NexoPOS has been successfully installed.' ),
        ];
    }

    public function testDBConnexion()
    {
        try {
            $DB = DB::connection( env( 'DB_CONNECTION', 'mysql' ) )->getPdo();

            return [
                'status' => 'success',
                'message' => __( 'Database connection was successful.' ),
            ];
        } catch ( \Exception $e ) {
            switch ( $e->getCode() ) {
                case 2002:
                    $message = [
                        'name' => 'hostname',
                        'message' => __( 'Unable to reach the host' ),
                        'status' => 'error',
                    ];
                    break;
                case 1045:
                    $message = [
                        'name' => 'username',
                        'message' => __( 'Unable to connect to the database using the credentials provided.' ),
                        'status' => 'error',
                    ];
                    break;
                case 1049:
                    $message = [
                        'name' => 'database_name',
                        'message' => __( 'Unable to select the database.' ),
                        'status' => 'error',
                    ];
                    break;
                case 1044:
                    $message = [
                        'name' => 'username',
                        'message' => __( 'Access denied for this user.' ),
                        'status' => 'error',
                    ];
                    break;
                case 1698:
                    $message = [
                        'name' => 'username',
                        'message' => __( 'Incorrect Authentication Plugin Provided.' ),
                        'status' => 'error',
                    ];
                    break;
                default:
                    $message = [
                        'name' => 'hostname',
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ];
                    break;
            }

            return response()->json( $message, 403 );
        }
    }

    public function clearRegisteredFileSystem(): void
    {
        $filesystemPath = config_path( 'filesystems.php' );

        if ( ! file_exists( $filesystemPath ) ) {
            throw new \RuntimeException( 'The filesystem configuration file does not exist.' );
        }

        $filesystemContent = file_get_contents( $filesystemPath );

        // Remove content between the opening and closing comments
        $filesystemContent = preg_replace(
            '/\/\* NexoPOS: FileSystem - Start \*\/(.*?)\/\* NexoPOS: FileSystem - End \*\//s',
            '',
            $filesystemContent
        );

        // Save the updated content back to the file
        file_put_contents( $filesystemPath, $filesystemContent );
    }

    /**
     * Ensure a specific filesystem configuration exists in the filesystem.php file.
     *
     * @param string $key   The filesystem configuration key to check.
     * @param array  $value The value to set if the configuration does not exist.
     */
    public function registerFileSystem( string $key, $function, $path ): void
    {
        $filesystemPath = config_path( 'filesystems.php' );

        if ( ! file_exists( $filesystemPath ) ) {
            throw new \RuntimeException( 'The filesystem configuration file does not exist.' );
        }

        $config = include $filesystemPath;

        if ( ! isset( $config['disks'][$key] ) ) {
            $filesystemContent = file_get_contents( $filesystemPath );

            // We'll check if the configuration has a "disks" key
            if ( preg_match( '/\'disks\'\s*=>\s*\[/', $filesystemContent ) ) {
                // If it does, we'll add the new configuration
                $config = View::make( 'ns::setup.filesystem', [
                    'key' => $key,
                    'function' => $function,
                    'path' => $path,
                ] );

                // now we'll check if within the "disks" array we have some opening and closing command starting with "NexoPOS: FileSystem - Start" and ending with "NexoPOS: FileSystem - End"
                // if so, we'll add the new configuration within these lines

                if ( ! preg_match( '/NexoPOS: FileSystem - Start/', $filesystemContent ) && ! preg_match( '/NexoPOS: FileSystem - End/', $filesystemContent ) ) {
                    // if not, we'll create a comment section within the "disks" array
                    $filesystemContent = preg_replace(
                        '/\'disks\'\s*=>\s*\[/',
                        "'disks' => [\n\t\t/* NexoPOS: FileSystem - Start */\n\t\t/* NexoPOS: FileSystem - End */",
                        $filesystemContent
                    );
                }

                // we'll add the new configuration within the comment section
                $filesystemContent = preg_replace(
                    '/(\\/\\* NexoPOS: FileSystem - Start \\*\\/)(.*?)(\\/\\* NexoPOS: FileSystem - End \\*\\/)/s',
                    '$1' . "$2\n\t\t" . $config . "\n\t\t$3",
                    $filesystemContent
                );

                // Save the updated content back to the file
                file_put_contents( $filesystemPath, $filesystemContent );
            } else {
                // As for now, Laravel must have a "disks" key, so probably it's not a laravel project
                // we'll then throw an exception

                throw new \RuntimeException( 'The "disks" key does not exist in the filesystem configuration file.' );
            }
        }
    }
}
