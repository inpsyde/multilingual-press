<?php # -*- coding: utf-8 -*-

namespace PHPSTORM_META {

	$STATIC_METHOD_TYPES = [
		new \Inpsyde\MultilingualPress\Service\Container            => [
			'' == '@',

			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.table.content_relations' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table.languages' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table.site_relations' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table_duplicator' instanceof \Inpsyde\MultilingualPress\Database\TableDuplicator,
			'multilingualpress.table_installer' instanceof \Inpsyde\MultilingualPress\Database\TableInstaller,
			'multilingualpress.table_list' instanceof \Inpsyde\MultilingualPress\Database\TableList,
			'multilingualpress.table_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableReplacer,
			'multilingualpress.table_string_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableStringReplacer,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
		],
		\Inpsyde\MultilingualPress\MultilingualPress::resolve( '' ) => [
			'' == '@',

			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.table.content_relations' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table.languages' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table.site_relations' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table_duplicator' instanceof \Inpsyde\MultilingualPress\Database\TableDuplicator,
			'multilingualpress.table_installer' instanceof \Inpsyde\MultilingualPress\Database\TableInstaller,
			'multilingualpress.table_list' instanceof \Inpsyde\MultilingualPress\Database\TableList,
			'multilingualpress.table_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableReplacer,
			'multilingualpress.table_string_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableStringReplacer,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
		],
	];
}
