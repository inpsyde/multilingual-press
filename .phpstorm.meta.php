<?php # -*- coding: utf-8 -*-

namespace PHPSTORM_META {

	$STATIC_METHOD_TYPES = [
		new \Inpsyde\MultilingualPress\Service\Container => [
			'' == '@',

			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
		],
		\Inpsyde\MultilingualPress\MultilingualPress::resolve( '' ) => [
			'' == '@',

			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
		],
	];
}
