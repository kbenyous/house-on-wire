<?php
    echo json_encode(array(
        'status' => 'success',
        'content' => array(
            'electricity' => array(
                'current' => array(
                    'value' => 10,
                    'unit' => 'kW/h'
                ),
                'power' => array(
                    'yesterday' => 8,
                    'beforeYesterday' => 6,
                    'unit' => 'kW'
                ),
                'cost' => array(
                    'yesterday' => 2,
                    'beforeYesterday' => 4,
                    'unit' => '&euro;'
                )
            ),
            'water' => array(
                'current' => array(
                    'value' => 10,
                    'unit' => 'm3'
                )
            ),
            'luminosity' => array(
                'current' => array(
                    'value' => 90,
                    'unit' => '%'
                )
            )
        )
    ));
?>
