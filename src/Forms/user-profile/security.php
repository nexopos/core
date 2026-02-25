<?php

return [
    'label' => __( 'Security' ),
    'identifier' => 'security',
    'fields' => [
        [
            'label' => __( 'Old Password' ),
            'name' => 'old_password',
            'type' => 'password',
            'description' => __( 'Provide the old password.' ),
        ], [
            'label' => __( 'Password' ),
            'name' => 'password',
            'type' => 'password',
            'description' => __( 'Change your password with a better stronger password.' ),
            'validation' => 'sometimes|min:6',
        ], [
            'label' => __( 'Password Confirmation' ),
            'name' => 'password_confirm',
            'type' => 'password',
            'description' => __( 'Change your password with a better stronger password.' ),
            'validation' => 'same:security.password',
        ],
    ],
];
