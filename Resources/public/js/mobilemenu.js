if ( $( ".xrowmobilemenu-toggle" ).length ) {
    var query = $( ".xrowmobilemenu-toggle" ).data("query"),
        queryArray = query.split('/'),
        currentLocationId = queryArray[(queryArray.length - 1)],
        queryPart = query.replace(currentLocationId, "");

    $(".xrowmobilemenu-toggle").each(function(){
        if ( $(this).is("[data-query]") ) {
            if ( $( ".xrowmobilemenu" ).length ) {
                $( ".xrowmobilemenu" ).toggle();
            } else {
                $(this).one('click', function(){
                    $("body").append('<div class="xrowmobilemenu"><nav><div class="menu-container"><progress class="progress progress-striped progress-animated"></progress></div></nav></div>');
                    $.ajax({
                        url: $(this).data("query"),
                        type: "GET",
                        dataType: "json",
                        processData: false,
                        beforeSend: function(xhrObj){
                            xhrObj.setRequestHeader("Accept","application/vnd.ez.api.ContentInfo+json");
                        },
                        success:function( json ){
                            initMenu( json, queryPart );
                        }
                    });
                });
                $(this, ".xrowmobilemenu").click(function() {
                    $("body").toggleClass("menu-open");
                    $( ".xrowmobilemenu" ).toggle();
                });
            }
        }
    });
}

function initMenu( inputData, queryPart ){
    var ancestors = inputData.menu.ancestors,
        ancestorHtml = "",
        html = "";
    if ( inputData.menu.children.length > 0 ) {
        html = generateHtmlList( inputData, queryPart, 'children', 'init' );
    } else if ( ancestors[0].hasChildren ) {
        html = generateHtmlList( inputData, queryPart, 'siblings', 'init' );
    } else {
        for( var i = 0; i < ancestors.length; i++ ) {
            if ( ancestors[i].hasChildren ) {
                $.ajax({
                    url: queryPart + ancestors[i]["locationId"],
                    type: "GET",
                    dataType: "json",
                    processData: false,
                    beforeSend: function(xhrObj){
                        xhrObj.setRequestHeader("Accept","application/vnd.ez.api.ContentInfo+json");
                    },
                    success: function( json ){
                        html = generateHtmlList( json, queryPart, 'siblings', 'init' );
                    }
                });
                break;
            }
        }
    }

    var xrowNavWidth = 80, //also change value in your css
        xrowWidthUnit = 'vw',
        startWidth = "-" + parseInt(xrowNavWidth * ancestors.length) + xrowWidthUnit;
    $( ".xrowmobilemenu nav div" ).html(html);

    //init click events
    $( ".xrowmobilemenu" ).on('click', '.forward', function(){
        var parentUlLength = $(this).parents("ul").length;
        $( ".menu-container" ).css({"left": "-" + parseInt( parentUlLength * 100 ) + "%" });
        //Hide neighbours children
        $(this).closest("ul").find("ul").hide();
        //Show own children
        $(this).closest("li").find("ul").show();
    }).on('click', '.back', function(){
        var parentUlLength = $(this).parents("ul").length -2;
        if ( $(this).is("[data-query]") || parentUlLength < 0 ) {
            parentUlLength = 0;
        }
        $( ".menu-container" ).css({"left": "-" + parseInt( parentUlLength * 100 ) + "%" });
    }).on('click', '[data-query]', function(){
        var thisClicked = $(this),
            clickQuery = thisClicked.data('query'),
            clickQueryArray = query.split('/'),
            clickCurrentLocationId = clickQueryArray[(clickQueryArray.length - 1)],
            pasteTarget = thisClicked.closest('ul');
        if( thisClicked.is(".back") ) {
            if ( thisClicked.parents("ul").length <= 1 ) {
                $.ajax({
                    url: clickQuery,
                    type: "GET",
                    dataType: "json",
                    processData: false,
                    beforeSend: function(xhrObj){
                        xhrObj.setRequestHeader("Accept","application/vnd.ez.api.ContentInfo+json");
                    },
                    success: function( json ){
                         var replacementHtml = $( ".xrowmobilemenu nav div" ).html(),
                             clickHtml = generateHtmlList( json, queryPart, 'siblings', 'replaceCurrent' ),
                             newHtml = clickHtml.replace("<current>", replacementHtml);
                         $( ".xrowmobilemenu nav div" ).html(newHtml);
                    }
                });
            }
        } else {
            $.ajax({
                url: clickQuery,
                type: "GET",
                dataType: "json",
                processData: false,
                beforeSend: function(xhrObj){
                    xhrObj.setRequestHeader("Accept","application/vnd.ez.api.ContentInfo+json");
                },
                success: function( json ){
                     var clickHtml = generateHtmlList( json, queryPart, 'children', 'replaceCurrent' );
                     thisClicked.after(clickHtml);
                }
            });
        }
        $(this).removeAttr("data-query");
    });
    $( ".xrowmobilemenu" ).on('click', '.closemenu', function(){
        $( ".xrowmobilemenu" ).toggle();
    });
    
    //init click events

}

function generateHtmlList( json, queryPart, $relation, filterType ) {
    var inputData = json.menu[$relation],
        url = "",
        text = "",
        html = "",
        cssClass = "",
        locationId = "",
        isRootLocation = false;

    if ( json.menu.ancestors.length == 0 || ( json.menu.ancestors.length == 1  && $relation == 'siblings' ) ) {
        isRootLocation = true;
    }
        html = '<ul>';
        //back button start
        if ( isRootLocation == false ) {
            html = html + '<li>';
                html = html + '<span class="back" data-query="'+ queryPart + json.menu.ancestors[0]["locationId"] + '">';
                    html = html + 'Zur&uuml;ck';
                html = html + '</span>';
            html = html + '</li>';
        }
        //back button end

        //parent object start
            if ( $relation == 'children' ) {
                html = html + '<li class="current">';
                html = html + '<a class="' + json.menu.current["class"] +'" href="' + json.menu.current["url"] +'">';
                    html = html + json.menu.current["name"];
                html = html + '</a>';
            } else if ( json.menu.ancestors.length > 0 ) {
                html = html + '<li>';
                html = html + '<a class="' + json.menu.ancestors[0]["class"] +'" href="' + json.menu.ancestors[0]["url"] +'">';
                    html = html + json.menu.ancestors[0]["name"];
                html = html + '</a>';
            }
        html = html + '</li>';
        //parent object end

        //menu items start
        for( var i = 0; i < inputData.length; i++ ) {
            url = "";
            text = "";
            cssClass = "";
            locationId = "";

            url = inputData[i]["url"];
            text = inputData[i]["name"];
            cssClass = inputData[i]["class"];
            locationId = inputData[i]["locationId"];

            if ( json.menu.current.locationId == locationId ) {
                cssClass = cssClass + " current";
            }
            if ( inputData[i]["hasChildren"] ) {
                cssClass = cssClass + " children";
            }

            if ( cssClass != "" ) {
                html = html + '<li class="'+ cssClass +'">';
            } else {
                html = html + '<li>';
            }
                if ( inputData[i]["hasChildren"] ) {
                    if ( json.menu.current.locationId == locationId ) {
                        html = html + '<span class="forward">';
                    } else {
                        html = html + '<span class="forward" data-query="'+ queryPart + locationId + '">';
                    }
                } else {
                    html = html + '<a href="' + url + '" data-query="'+ queryPart + locationId + '">';
                }
                    html = html + text;
                if ( inputData[i]["hasChildren"] ) {
                    html = html + '</span>';
                } else {
                    html = html + '</a>';
                }
                
                if ( filterType == "replaceCurrent" && json.menu.current.locationId == locationId ) {
                    html = html + '<current>';
                }
            html = html + '</li>';

        }
        //menu items end

        //close menu end
        html = html + '<li>';
            html = html + '<span class="closemenu">Schlie&szlig;en</span>';
        html = html + '</li>';
        //close menu end
        html = html + '</ul>';

    return html;
}