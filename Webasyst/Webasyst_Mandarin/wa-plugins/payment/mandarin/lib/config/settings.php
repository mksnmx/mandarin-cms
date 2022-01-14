<?php
return array(
    'protocol'       => array(
        'value'        => 'rest',
        'title'        => 'Способ подключения',
        'control_type' => 'select',
        'options'      => array(
            'rest' => 'Протокол Pull (REST)',
        ),
    ),
    'm_id'          => array(
        'value'        => '',
        'title'        => 'ID Мерчанта',
        'placeholder'  => '123',
        'description'  => '',
        'class'        => 'small',
        'control_type' => 'input',
    ),

    'm_secret'      => array(
        'value'        => '',
        'title'        => 'Secret Мерчанта',
        'description'  => '',
        'placeholder'  => '1234567',
        'class'        => 'small',
        'control_type' => 'input',

    ),
);
