<?php # -*- coding: utf-8 -*-

namespace PHPSTORM_META {

	$STATIC_METHOD_TYPES = [
		new \Inpsyde\MultilingualPress\Service\Container            => [
			'' == '@',

			'multilingualpress.asset_factory' instanceof \Inpsyde\MultilingualPress\Asset\AssetFactory,
			'multilingualpress.asset_manager' instanceof \Inpsyde\MultilingualPress\Asset\AssetManager,
			'multilingualpress.attachment_copier' instanceof \Inpsyde\MultilingualPress\SiteDuplication\AttachmentCopier,
			'multilingualpress.base_path_adapter' instanceof \Inpsyde\MultilingualPress\Common\BasePathAdapter,
			'multilingualpress.content_relations' instanceof \Inpsyde\MultilingualPress\API\ContentRelations,
			'multilingualpress.content_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.installer' instanceof \Inpsyde\MultilingualPress\Installation\Installer,
			'multilingualpress.internal_locations' instanceof \Inpsyde\MultilingualPress\Core\InternalLocations,
			'multilingualpress.languages_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.module_manager' instanceof \Inpsyde\MultilingualPress\Module\ModuleManager,
			'multilingualpress.network_plugin_deactivator' instanceof \Inpsyde\MultilingualPress\Installation\NetworkPluginDeactivator,
			'multilingualpress.properties' instanceof \Inpsyde\MultilingualPress\Common\PluginProperties,
			'multilingualpress.site_duplication_settings_view' instanceof \Inpsyde\MultilingualPress\SiteDuplication\SettingsView,
			'multilingualpress.site_relations' instanceof \Inpsyde\MultilingualPress\API\SiteRelations,
			'multilingualpress.site_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.system_checker' instanceof \Inpsyde\MultilingualPress\Installation\SystemChecker,
			'multilingualpress.table_duplicator' instanceof \Inpsyde\MultilingualPress\Database\TableDuplicator,
			'multilingualpress.table_installer' instanceof \Inpsyde\MultilingualPress\Database\TableInstaller,
			'multilingualpress.table_list' instanceof \Inpsyde\MultilingualPress\Database\TableList,
			'multilingualpress.table_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableReplacer,
			'multilingualpress.table_string_replacer' instanceof \Inpsyde\MultilingualPress\Database\TableStringReplacer,
			'multilingualpress.trasher' instanceof \Inpsyde\MultilingualPress\Module\Trasher\Trasher,
			'multilingualpress.trasher_setting_repository' instanceof \Inpsyde\MultilingualPress\Module\Trasher\TrasherSettingRepository,
			'multilingualpress.trasher_setting_updater' instanceof \Inpsyde\MultilingualPress\Module\Trasher\TrasherSettingUpdater,
			'multilingualpress.trasher_setting_view' instanceof \Inpsyde\MultilingualPress\Module\Trasher\TrasherSettingView,
			'multilingualpress.type_factory' instanceof \Inpsyde\MultilingualPress\Factory\TypeFactory,
			'multilingualpress.updater' instanceof \Inpsyde\MultilingualPress\Installation\Updater,
		],
		\Inpsyde\MultilingualPress\MultilingualPress::resolve( '' ) => [
			'' == '@',

			'multilingualpress.asset_factory' instanceof \Inpsyde\MultilingualPress\Asset\AssetFactory,
			'multilingualpress.asset_manager' instanceof \Inpsyde\MultilingualPress\Asset\AssetManager,
			'multilingualpress.content_relations' instanceof \Inpsyde\MultilingualPress\API\ContentRelations,
			'multilingualpress.content_relations_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.error_factory' instanceof \Inpsyde\MultilingualPress\Common\Factory,
			'multilingualpress.languages_table' instanceof \Inpsyde\MultilingualPress\Database\Table,
			'multilingualpress.network_plugin_deactivator' instanceof \Inpsyde\MultilingualPress\Installation\NetworkPluginDeactivator,
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
