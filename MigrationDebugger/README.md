Migration Debugging
===================

For extra feedback and resilience when investigating content migration problems
using the [EzMigrationBundle](https://github.com/xrowgmbh/ezmigrationbundle)
or any other CLI tools based on the eZ Publish API.

## Usage

Copy sample config files to `ezpublish/config`:

	cp vendor/xrow/bootstrap-bundle/MigrationDebugger/{config,ezpublish}_cli_debug.yml ezpublish/config

Run your migration command using the 'cli_debug` environment. For example:

    php ezpublish/console kaliop:migration:migrate --path=ezpublish/Migrations/my_migration.yml --ignore-failures --env=cli_debug | tee ~/migrate.log

CAUTION: The resulting database should not be used for production. It may be broken!

## Other useful commands:

To check the status of migrations:

    php ezpublish/console kaliop:migration:status

To delete a (failed) migration:

	php ezpublish/console kaliop:migration:migration my_migration.yml --delete

To check that the kernel classes are correctly overridden in the `cli_debug` environment:

	php ezpublish/console debug:container --env=cli_debug ezpublish.persistence.legacy.content.mapper
	php ezpublish/console debug:container --env=cli_debug ezpublish.persistence.legacy.content_type.content_updater
