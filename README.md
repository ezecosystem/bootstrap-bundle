# bootstrap-bundle
## config.yml
    imports:
        - { resource: parameters.yml }
        - { resource: security.yml }
    xrowbootstrap:
        rest_menu:
            #use this config for including your classes in /api/ezp/v2/xrow/menu/{nodeID} rest controller
            include_content_types: ['frontpage', 'folder', 'gallery']
            #adds css classes to certain location id's
            css_class_strings:
                - { node_id: 2, class_string: 'icon icon-home' }
    framework:
        esi:             ~
        translator:      { fallback: %locale_fallback% }
        # The secret parameter is used to generate CSRF tokens
        secret:          %secret%
        router:
            resource: "%kernel.root_dir%/config/routing.yml"
            strict_requirements: %kernel.debug%
        form:            ~
        csrf_protection:
            enabled: true
            # Note: changing this will break legacy extensions that rely on the default name to alter AJAX requests
            # See https://jira.ez.no/browse/EZP-20783
            field_name: ezxform_token
        validation:      { enable_annotations: true }
        # Place "eztpl" engine first intentionnally.
        # This is to avoid template name parsing with Twig engine, refusing specific characters
        # which are valid with legacy tpl files.
        templating:      { engines: ['eztpl', 'twig'] } #assets_version: SomeVersionScheme
        trusted_proxies: ~
        trusted_hosts: []
        session:
            save_path: "%kernel.root_dir%/sessions"
            # The session name defined here will be overridden by the one defined in your ezpublish.yml, for your siteaccess.
            # Defaut session name is "eZSESSID{siteaccess_hash}" (unique session name per siteaccess).
            # See ezpublish.yml.example for an example on how to configure this.
        fragments:       ~
        http_method_override: true
    
    # Twig Configuration
    twig:
        debug:            %kernel.debug%
        strict_variables: %kernel.debug%
    
    # Assetic Configuration
    assetic:
        assets:
            bootstrap_css:
                inputs:
                    - %kernel.root_dir%/../web/sass/_bootstrap.scss
                filter: scssphp
                output: css/bootstrap.css
            bootstrap_js:
                inputs:
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/transition.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/alert.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/button.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/carousel.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/collapse.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/dropdown.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/modal.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/tooltip.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/popover.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/scrollspy.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/tab.js
                    - %kernel.root_dir%/../vendor/twbs/bootstrap/js/affix.js
                    - %kernel.root_dir%/../vendor/braincrafted/bootstrap-bundle/Braincrafted/Bundle/BootstrapBundle/Resources/js/bc-bootstrap-collection.js
                output: js/bootstrap.js
            jquery:
                inputs:
                    - %kernel.root_dir%/../vendor/components/jquery/jquery.js
                output: js/jquery.js
        debug:          %kernel.debug%
        use_controller: false
        bundles:        [ xrowbootstrapBundle]
        #bundles:        [ xrowbootstrapBundle ]
        #java: /usr/bin/java
        filters:
            scssphp: ~
            cssrewrite: ~
            #closure:
            #    jar: %kernel.root_dir%/Resources/java/compiler.jar
            #yui_css:
            #    jar: %kernel.root_dir%/Resources/java/yuicompressor-2.4.7.jar
    
    ez_publish_legacy:
        enabled: true
        root_dir: %kernel.root_dir%/../ezpublish_legacy
    
    braincrafted_bootstrap:
        output_dir:
        assets_dir: %kernel.root_dir%/../vendor/twbs/bootstrap
        jquery_path: %kernel.root_dir%/../vendor/components/jquery/jquery.js
        less_filter: sass # "less", "lessphp", "sass" or "none"
        fonts_dir: %kernel.root_dir%/../web/fonts
        auto_configure:
            assetic: false
            twig: true
            knp_menu: false
            knp_paginator: true
        customize:
            variables_file: ~
            bootstrap_output: %kernel.root_dir%/Resources/less/bootstrap.less
            bootstrap_template: BraincraftedBootstrapBundle:Bootstrap:bootstrap.less.twig
    
    # KNP MENU
    knp_menu:
        twig:  # use "twig: false" to disable the Twig extension and the TwigRenderer
            template: BraincraftedBootstrapBundle:Menu:bootstrap.html.twig
        templating: false # if true, enables the helper for PHP templates
        default_renderer: twig # The renderer to use, list is also available by default
