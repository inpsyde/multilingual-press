<?php # -*- coding: utf-8 -*-

namespace PHPSTORM_META {

	$STATIC_METHOD_TYPES = [
		new \Inpsyde\MultilingualPress\Service\Container            => [
			'' == '@',

			'multilingualpress.content_relations' instanceof \Inpsyde\MultilingualPress\API\ContentRelations,
			'multilingualpress.content_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.languages_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.module_manager' instanceof \Inpsyde\MultilingualPress\Module\ModuleManager,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.site_relations' instanceof \Inpsyde\MultilingualPress\API\SiteRelations,
			'multilingualpress.site_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table_duplicator' instanceof \Inpsyde\MultilingualPress\Database\TableDuplicator,
			'multilingualpress.table_installer' instanceof \Inpsyde\MultilingualPress\Database\TableInstaller,
			'multilingualpress.table_list' instanceof \Inpsyde\MultilingualPress\Database\TableList,
			'multilingualpress.table_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableReplacer,
			'multilingualpress.table_string_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableStringReplacer,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
		],
		\Inpsyde\MultilingualPress\MultilingualPress::resolve( '' ) => [
			'' == '@',

			'multilingualpress.content_relations' instanceof \Inpsyde\MultilingualPress\API\ContentRelations,
			'multilingualpress.content_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.languages_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.site_relations' instanceof \Inpsyde\MultilingualPress\API\SiteRelations,
			'multilingualpress.site_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.table_duplicator' instanceof \Inpsyde\MultilingualPress\Database\TableDuplicator,
			'multilingualpress.table_installer' instanceof \Inpsyde\MultilingualPress\Database\TableInstaller,
			'multilingualpress.table_list' instanceof \Inpsyde\MultilingualPress\Database\TableList,
			'multilingualpress.table_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableReplacer,
			'multilingualpress.table_string_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableStringReplacer,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
		],
	];
}
