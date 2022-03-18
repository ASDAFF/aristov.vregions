<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

$arComponentParameters = array(
    "GROUPS"     => array(),
    "PARAMETERS" => array(
        "COORDINATES"  => Array(
            "PARENT"  => "BASE",
            "NAME"    => GetMessage("ARISTOV_REGION_CENTRE_ON_MAP_PARAM_COORDINATES_NAME"),
            "TYPE"    => "STRING",
            "DEFAULT" => '={$_SESSION["VREGIONS_REGION"]["CENTR_REGIONA"]}'
        ),
        "WIDTH"        => Array(
            "PARENT"  => "BASE",
            "NAME"    => GetMessage("ARISTOV_REGION_CENTRE_ON_MAP_PARAM_WIDTH_NAME"),
            "TYPE"    => "STRING",
            "DEFAULT" => '600'
        ),
        "HEIGHT"       => Array(
            "PARENT"  => "BASE",
            "NAME"    => GetMessage("ARISTOV_REGION_CENTRE_ON_MAP_PARAM_HEIGHT_NAME"),
            "TYPE"    => "STRING",
            "DEFAULT" => '400'
        ),
        "ZOOM"         => Array(
            "PARENT"  => "BASE",
            "NAME"    => GetMessage("ARISTOV_REGION_CENTRE_ON_MAP_PARAM_ZOOM_NAME"),
            "TYPE"    => "STRING",
            'DEFAULT' => '11',
        ),
        "BALLOON_TEXT" => Array(
            "PARENT" => "BASE",
            "NAME"   => GetMessage("ARISTOV_REGION_CENTRE_ON_MAP_PARAM_BALLOON_TEXT_NAME"),
            "TYPE"   => "STRING",
        ),
        'CONTROLS'     => array(
            'NAME'     => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS'),
            'TYPE'     => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES'   => array(
                'ZOOM'        => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS_ZOOM'),
                'SMALLZOOM'   => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS_SMALLZOOM'),
                'MINIMAP'     => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS_MINIMAP'),
                'TYPECONTROL' => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS_TYPECONTROL'),
                'SCALELINE'   => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS_SCALELINE'),
                'SEARCH'      => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_CONTROLS_SEARCH'),
            ),

            'DEFAULT' => array(
                'ZOOM',
                'MINIMAP',
                'TYPECONTROL',
                'SCALELINE'
            ),
            'PARENT'  => 'ADDITIONAL_SETTINGS',
        ),
        'OPTIONS' => array(
            'NAME'     => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_OPTIONS'),
            'TYPE'     => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES'   => array(
                'ENABLE_SCROLL_ZOOM'     => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_OPTIONS_ENABLE_SCROLL_ZOOM'),
                'ENABLE_DBLCLICK_ZOOM'   => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_OPTIONS_ENABLE_DBLCLICK_ZOOM'),
                'ENABLE_RIGHT_MAGNIFIER' => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_OPTIONS_ENABLE_RIGHT_MAGNIFIER'),
                'ENABLE_DRAGGING'        => GetMessage('ARISTOV_REGION_CENTRE_ON_MAP_PARAM_PARAM_OPTIONS_ENABLE_DRAGGING'),
            ),

            'DEFAULT' => array(
                'ENABLE_SCROLL_ZOOM',
                'ENABLE_DBLCLICK_ZOOM',
                'ENABLE_DRAGGING'
            ),
            'PARENT'  => 'ADDITIONAL_SETTINGS',
        ),
    ),
);
?>