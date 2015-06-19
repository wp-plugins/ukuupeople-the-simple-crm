jQuery(function ($) {
    // Contribution Dahsboard code End //
    // Column of activity graph on Find Contact page code start //

    jQuery('div.tdata').find('svg').each(function(i, el) {
	var ColumnName=jQuery(this).attr("activityc");
	var row = JSON.parse(ColumnName);
	var a=row['val'];
	var b=row['colorlist'];
	var c=row['contactid'];
	var lineData = [{ "x": a[0][0],  "y": a[0][1]},
			{ "x": a[1][0],  "y": a[1][1]},
		        { "x": a[2][0],  "y": a[2][1]},
		        { "x": a[3][0],  "y": a[3][1]},
		        { "x": a[4][0],  "y": a[4][1]}];
	var vis = d3.select("#visualisation_"+c),
	WIDTH = 150,
	HEIGHT = 50,
	MARGINS = {
	    top: 5,
	    right: 5,
	    bottom: 5,
	    left: 5
	},
	xRange = d3.scale.linear().range([MARGINS.left, WIDTH - MARGINS.right]).domain([d3.min(lineData, function (d) {
            return d.x;
	  }),
	  d3.max(lineData, function (d) {
	    return d.x;
	  })
	]),
	yRange = d3.scale.linear().range([HEIGHT - MARGINS.top, MARGINS.bottom]).domain([d3.min(lineData, function (d) {
          return d.y;
	}), 25
											]),
	xAxis = d3.svg.axis()
	    .scale(xRange)
	    .tickSize(5),
	yAxis = d3.svg.axis()
	    .scale(yRange)
	    .tickSize(5)
	    .orient("left");

	var lineFunc = d3.svg.line()
  	    .x(function (d) {
    		return xRange(d.x);
  	    })
  	    .y(function (d) {
    		return yRange(d.y);
  	    })
  	    .interpolate('linear');

  	vis.append("svg:path")
  	    .attr("d", lineFunc(lineData))
  	    .attr("stroke", b)
  	    .attr("stroke-width", 2)
  	    .attr("fill", "none");

  });
  // Column of activity graph on Find Contact page code End //
});

jQuery(document).ready(function() {
    jQuery('.post-type-wp-type-contacts #wpcf-group-edit-contact-info .inside div[data-wpt-id="wpcf-normal-name"]').hide();
    touchpoint = '#'+jQuery("#touchpoint-types").parent().attr('id');
    addtouchpoint = '#'+jQuery("#types-child-table-wp-type-contacts-wp-type-activity").parent().parent().attr('id');
    jQuery('.simple-fields-meta-box-field-group-wrapper-slug-wp_type_contacts_org_relation .simple-fields-metabox-field-post-select').html('Select Organization');
    jQuery( addtouchpoint ).hide();
    jQuery( touchpoint ).insertBefore("#post-body-content");
    jQuery( "#touchpoint-types" ).removeClass( "postbox" );
    jQuery('.graph-main-container').insertBefore("#posts-filter");
    jQuery('#activity-list').insertAfter("#postbox-container-2 #wpcf-post-relationship");
    jQuery('#wpcf-group-edit_contact_privacy_settings .inside').append('<div class="clear"></div>');
    jQuery('#wpcf-group-edit-contact-info .inside').append('<div class="clear"></div>');
    jQuery('#wpcf-group-edit_contact_address .inside').append('<div class="clear"></div>');
    noteChanges(jQuery('select#touchpoint-list'));

    jQuery('.post-type-wp-type-activity #submitpost #publish').click( function() {
	var arr = ['startdate', 'enddate'];
	jQuery.each(arr, function( index, value ) {
	    var display = jQuery('div[data-wpt-id="wpcf-'+value+'"]').find('input').val();
	    var note = jQuery('select#touchpoint-list').val();
            if ( (display == '' || !display) && (note == 'wp-type-activity-note')) {
		jQuery('.inside div[data-wpt-id="wpcf-'+value+'"] input').datepicker("setDate", new Date());
		jQuery('input[data-wpt-id="wpcf-'+value+'-datepicker"]').val(jQuery('.inside div[data-wpt-id="wpcf-'+value+'"] input').val());
		console.log(jQuery('.inside div[data-wpt-id="wpcf-'+value+'"] input').val());
            }
	});
    });

    jQuery('select#touchpoint-list').on('change', function() {
	noteChanges(this);
    });
    function noteChanges ($this) {
	if (jQuery($this).val() == 'wp-type-activity-note') {
            jQuery('div[data-wpt-id="wpcf-startdate"]').hide();
            jQuery('div[data-wpt-id="wpcf-enddate"]').hide();
            jQuery('div[data-wpt-id="wpcf-status"]').hide();
	} else {
            jQuery('div[data-wpt-id="wpcf-startdate"]').show();
            jQuery('div[data-wpt-id="wpcf-enddate"]').show();
            jQuery('div[data-wpt-id="wpcf-status"]').show();
	}
    }

    jQuery('select[name="wpcf[startdate][hour]"]').hide();
    jQuery('select[name="wpcf[startdate][hour]"]').prev('span').hide();

    jQuery('select[name="wpcf[startdate][minute]"]').hide();
    jQuery('select[name="wpcf[startdate][minute]"]').prev('span').hide();

    jQuery('select[name="wpcf[enddate][hour]"]').hide();
    jQuery('select[name="wpcf[enddate][hour]"]').prev('span').hide();

    jQuery('select[name="wpcf[enddate][minute]"]').hide();
    jQuery('select[name="wpcf[enddate][minute]"]').prev('span').hide();

    function createEle ($this , $i) {
	var option = document.createElement("option");
	option.value = $i;
	option.text = $i;
	jQuery($this).append(option);
    }
    function hourChange ($this , $id) {
	var x = document.getElementById($id).value;
	if (x == "PM") {
	    var y = parseInt(jQuery($this).val()) + 12 ;
	    if (parseInt(y) == 24) y = 0;
	}
	if (x == "AM") {
	    var y = jQuery($this).val();
	}
    }
    function ampmChange ($this , $id) {
	var x = document.getElementById($id).value;
	var y = jQuery($this).val();
	if (y == "PM") {
	    var z = parseInt(x) + 12 ;
	    if (parseInt(z) == 24) z = 0;
	}
	if (y == "AM") {
	    var z = parseInt(x);//jQuery(this).val();
	}
    }
    function appendOption ($this) {
	var array = ["AM","PM"];
	for (var i = 0; i < array.length; i++) {
	    var option = document.createElement("option");
	    option.value = array[i];
	    option.text = array[i];
	    jQuery($this).append(option);
	}
    }
    var startHour = document.createElement("select");
    startHour.id = "startdate-hour";
    jQuery('div[data-wpt-id="wpcf-startdate"] > div > div').append(startHour);
    jQuery('<span class="wpt-form-label">Hour</span>').insertBefore('#startdate-hour');
    //Create and append the options
    for (var i = 0; i <= 12; i++) { 
	createEle (startHour, i);
    }

    var startMinute = document.createElement("select");
    startMinute.id = "startdate-minute";
    jQuery('div[data-wpt-id="wpcf-startdate"] > div > div').append(startMinute);
    jQuery('<span class="wpt-form-label">Minutes</span>').insertBefore('#startdate-minute');

    //Create and append the options
    for (var i = 0; i <= 59; i++) {
	createEle (startMinute, i);
    }
    jQuery('#startdate-minute').val(parseInt(jQuery('select[name="wpcf[startdate][minute]"]').val()));

    var ampm = document.createElement("select");
    ampm.id = "ampm";
    jQuery('div[data-wpt-id="wpcf-startdate"] > div > div').append(ampm);
    
    //Create and append the options
    appendOption (ampm);
    
    if (jQuery('select[name="wpcf[startdate][hour]"]').val() > 12) {
	jQuery('#startdate-hour').val(parseInt(jQuery('select[name="wpcf[startdate][hour]"]').val()) - 12);
	jQuery('#ampm').val('PM');
    }
    else
	jQuery('#startdate-hour').val(parseInt(jQuery('select[name="wpcf[startdate][hour]"]').val()));
    jQuery(startHour).on('change', function() {
	hourChange (this , "ampm");
	jQuery('select[name="wpcf[startdate][hour]"]').val(y);
    });
    jQuery(startMinute).on('change', function() {
	var y = jQuery(this).val();
	jQuery('select[name="wpcf[startdate][minute]"]').val(y);
    });
    jQuery(ampm).on('change', function() {
	ampmChange (this , "startdate-hour");
	jQuery('select[name="wpcf[startdate][hour]"]').val(z);
    });

    var endHour = document.createElement("select");
    endHour.id = "enddate-hour";
    jQuery('div[data-wpt-id="wpcf-enddate"] > div > div').append(endHour);
    jQuery('<span class="wpt-form-label">Hour</span>').insertBefore('#enddate-hour');

    //Create and append the options
    for (var i = 0; i <= 12; i++) {
	createEle (endHour, i);
    }
    var endMinute = document.createElement("select");
    endMinute.id = "enddate-minute";
    jQuery('div[data-wpt-id="wpcf-enddate"] > div > div').append(endMinute);
    jQuery('<span class="wpt-form-label">Minutes</span>').insertBefore('#enddate-minute');

    //Create and append the options
    for (var i = 0; i <= 59; i++) {
	createEle (endMinute, i);
    }
    jQuery('#enddate-minute').val(parseInt(jQuery('select[name="wpcf[enddate][minute]"]').val()));

    var ampmEnd = document.createElement("select");
    ampmEnd.id = "ampm-end";
    jQuery('div[data-wpt-id="wpcf-enddate"] > div > div').append(ampmEnd);

    //Create and append the options
    appendOption (ampmEnd);

    if (jQuery('select[name="wpcf[enddate][hour]"]').val() > 12) {
	jQuery('#enddate-hour').val(parseInt(jQuery('select[name="wpcf[enddate][hour]"]').val()) - 12);
	jQuery('#ampm-end').val('PM');
    }
    else
	jQuery('#enddate-hour').val(parseInt(jQuery('select[name="wpcf[enddate][hour]"]').val()));

    jQuery(endHour).on('change', function() {
	hourChange (this , "ampm-end");
	jQuery('select[name="wpcf[enddate][hour]"]').val(y);
    });
    jQuery(endMinute).on('change', function() {
	var y = jQuery(this).val();
	jQuery('select[name="wpcf[enddate][minute]"]').val(y);
    });
    jQuery(ampmEnd).on('change', function() {
	ampmChange (this , "enddate-hour");
	jQuery('select[name="wpcf[enddate][hour]"]').val(z);
    });
});

//graph on Contact and event list
jQuery( '#graph' ).ready(function() {
    if (jQuery('#chart').attr('type') && jQuery('#chart').attr('color')) {
	var type = JSON.parse(jQuery('#chart').attr('type'));
	var colorSet = JSON.parse(jQuery('#chart').attr('color'));
	var dataset = [];
	var color = [];
	var mainTotal = 0;
	color.push("#ffffff");
	jQuery.each( colorSet , function( i, val ) {
	    color.push(val);
	    color.push("#ffffff");
	});
	jQuery.each( type, function( i, val ) {
	    mainTotal += parseInt(val);
	});
	mainTotal = mainTotal*0.001;

	var data = {};
	var numeric = [[]];
	numeric[0]['yHandler'] = mainTotal;
	data.data = numeric;
	data.name= 0;
	dataset.push(data);
	jQuery.each( type, function( i, val ) {
	    var data = {};
	    var numeric = [[]];
	    numeric[0]['yHandler'] = parseInt(val);
	    data.data = numeric;
	    data.name= i;
            dataset.push(data);
	    var data = {};
	    var numeric = [[]];
	    numeric[0]['yHandler'] = mainTotal;
	    data.data = numeric;
	    data.name= i;
            dataset.push(data);
	});
	drawit(dataset);
    }

    function drawit(dataset) {

	var margins = {
	    top: 0,
	    left: 10,
	    right: 5,
	    bottom: 5
	};

	var dynamic_width = jQuery("#graph").width();
	var width = dynamic_width-25;
	var height = 75;

	var series = dataset.map(function (d) {
	    return d.name;
	});

	var dataset = dataset.map(function (d) {
	    return d.data.map(function (o, i) {
		// Structure it so that your numeric
		// axis (the stacked amount) is y
		return {
		    y: +o.yHandler
		};
	    });
	});

	stack = d3.layout.stack();

	stack(dataset);
	var dataset = dataset.map(function (group) {
	    return group.map(function (d) {
		// Invert the x and y values, and y0 becomes x0
		return {
		    x0: d.y0,
		    x: d.y
		};
	    });
	});

	var svg = d3.select('#graph')
	    .append('svg')
	    .attr('width', width + margins.left + margins.right)
	    .attr('height', height + margins.top + margins.bottom)
	    .attr('transform', 'translate(' + margins.left + ',' + margins.top + ')')
	    .style('margin-left','10')

	var xMax = d3.max(dataset, function (group) {
	    return d3.max(group, function (d) {
		return d.x+d.x0;
	    });
	})

	var xScale = d3.scale.linear()
	    .domain([0, xMax])
	    .range([0, width]);

	var heightGraph = dataset[0].map(function (d) {
	    return d.y;
	});

	var yScale = d3.scale.ordinal()
	    .domain(heightGraph)
	    .rangeRoundBands([0, height], .1);

	var colours = d3.scale.ordinal()
	    .range(color)
	    .domain([0,1,2]);


	var groups = svg.selectAll('g')
	    .data(dataset)
	    .enter()
	    .append('g')
	    .style('fill', function (d, i) {
		return colours(i);
	    });

	var text = groups.selectAll("rect")
	    .data(dataset)
	    .enter()
	    .append("text");

	var textLabels = text
	    .attr("x", function(dataset) {
	        var x;
	        jQuery.each(dataset, function(key, value) {
		    x = value.x0;
		    x1 = value.x;

	        });
	        var rangeBand = x/xMax*width;
	        return rangeBand+10;
	    }

	         )
	    .attr("y", "50")
	    .text( function(dataset) {
	        var x;
	        jQuery.each(dataset, function(key, value) {
		    x = value.x;
	        });
	        if(x == 0 || x == mainTotal){
		    //do nothing
	        } else {
		    return x;
	        }
	    })
	    .attr("font-size", "30px")
	    .attr("font-weight", "bold")
	    .attr("fill", "#FFFFFF")
	    .style("margin-left", "10");

	var rects = groups.selectAll('rect')
	    .data(function (d) {
		return d;
	    })
	    .enter()
	    .append('rect')
	    .attr('x', function (d) {
		return xScale(d.x0);
	    })
	    .attr('y', function (d, i) {
		return yScale(d.y);
	    })
	    .attr('height', function (d) {
		return yScale.rangeBand();
	    })
	    .attr('width', function (d) {
		return xScale(d.x);
	    });

	svg.append('g')
	    .attr('class', 'x axis')
	    .attr('transform', 'translate(0,' + height + ')');

	svg.append('g')
	    .attr('class', 'y axis');
    }
});
