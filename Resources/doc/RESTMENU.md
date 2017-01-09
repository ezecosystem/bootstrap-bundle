1. Register and enable the bundle as briefly described in file "INSTALLATION"

2. Set up your list of content types that should be visible in the menu
    Optional: You can add css classes that should be shown per locationId. For example a home icon for your root location
## config.yml
    xrowbootstrap:
        rest_menu:
            #use this config for including your classes in /api/ezp/v2/xrow/menu/{nodeID} rest controller
            include_content_types: ['frontpage', 'folder', 'gallery']
            #adds css classes to certain location id's
            css_class_strings:
                - { node_id: 2, class_string: 'icon icon-home' }

3. Basic example JS and CSS files can be found here, you might want to style it:
  Resources/public/js/mobilemenu.js
  Resources/public/css/mobilemenu.css

4. Add a toggle button that loads the menu when present and shows/hides the menu on demand. Provide it with a "data-query" attribute.
    <button class="xrowmobilemenu-toggle" data-query="/api/ezp/v2/menu/<yourDesiredLocationId>"></button>
    Optional: To focus the menu on your desired node you may use this code.
              When the content type of the provided location is not in your "include_content_types" in config.yml the JS example chooses the closest ancestor that is configured to be visible.
        {% if location.id is defined %}
            {% set locationId = location.id %}
        {% elseif ezpublish.rootLocation.id is defined  %}
            {% set locationId = ezpublish.rootLocation.id %}
        {% endif %}
        {% if locationId is defined%}
            data-query="{{ path('ezpublish_rest_RestMenu', { 'locationID': locationId }) }}"
        {% endif %}