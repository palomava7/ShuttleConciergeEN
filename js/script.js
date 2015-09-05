function loadPageVar(sVar) { // MDN xD
    return decodeURIComponent(decodeURI(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + encodeURI(sVar).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"))).replace("+", " ");
}
function randomIntFromInterval(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}
function nextDay(day){  // Función para buscar el siguiente día de la semana (Ej. buscar el siguente viernes)
    var d = new Date();
    (day = (Math.abs(+day || 0) % 7) - d.getDay()) < 0 && (day += 7);
    return day && d.setDate(d.getDate() + day), d;
};
function _normaliseDate(date) {
    if (date) {
        date.setHours(12, 0, 0, 0);
    }
    return date;
}
function addDate(date, amount, period) {
    date=new Date(date);
    if (period == 'd' || period == 'w') {
        this._normaliseDate(date);
        date.setDate(date.getDate() + amount * (period == 'w' ? 7 : 1));
    } else {
        var year = date.getFullYear() + (period == 'y' ? amount : 0);
        var month = date.getMonth() + (period == 'm' ? amount : 0);
        date.setTime(plugin.newDate(year, month + 1,
            Math.min(date.getDate(), this.daysInMonth(year, month + 1))).getTime());
    }
    return date;
}
function setDefaultDate() {
    var defaultDate = nextDay(5);
    defaultDate = addDate(defaultDate, '+7', 'd');
    $("#start").datepicker("setDate", defaultDate);
    defaultDate = addDate(defaultDate, '+2', 'd');
    $("#end").datepicker("setDate", defaultDate);
}
function OnSelectDate(dateSel) {
    var dtId = $(this).attr('id');
    var dateFromInput = $("#start");
    var dateToInput = $("#end");
    var newdate, dateFrom, dateTo;

    if (dtId.indexOf('start') >= 0) {
        dateFrom = dateFromInput.datepicker("getDate");
        dateTo = dateToInput.datepicker("getDate");
        newdate = addDate(dateFrom, '+1', 'd');
        if (dateFrom >= dateTo) {
            dateToInput.datepicker("option", "maxDate", null);
            dateToInput.datepicker("setDate", newdate);
        }
    } else {
        dateFrom = dateFromInput.datepicker("getDate");
        dateTo = dateToInput.datepicker("getDate");
        newdate = addDate(dateTo, '-1', 'd');
        if ( dateTo <= dateFrom ) {
            dateFromInput.datepicker("setDate", newdate);
        }
    }
}
var inputText;
function changeFocus(obj) {
    $(obj).focus(function () {
        inputText = $(this).val();
        $(this).val("");
        $(this).autocomplete("search", "");
    });
    $(obj).blur(function () {
        $(this).val(inputText);
    });
}

////////////////////////////////////////////////
////////////          Main          ////////////
////////////////////////////////////////////////
$(function(){
    $(".header .phone").hover(function(){
        $(".header .phone .phoneList").stop().slideToggle();
    });
    $(".header .phone").click(function(){
        $(".header .phone .phoneList").stop().slideToggle();
    });
    $(".photo-link").fancybox();
    $(".terms-link").fancybox({
        maxWidth: 720,
        type: "iframe"
    });
	$(".banner").cycle({
		timeout: 5000,
        fx: 'fade'
    });
    $(".miniSlide").each(function(index, element) {
        $(element).cycle({
            timeout: randomIntFromInterval(3000,4000),
            fx: 'fade'
        });    
    });
        
    /* Inicia Resbox */
    if ( $("#resboxForm").length ) {
        $("#start").datepicker({
            dateFormat: "dd/mm/yy",
            numberOfMonths: 2,
            showButtonPanel: true,
            minDate: 0,
            maxDate: "+1y",
            onSelect: OnSelectDate
        });

        $("#end").datepicker({
            dateFormat: "dd/mm/yy",
            numberOfMonths: 2,
            showButtonPanel: true,
            minDate: 1,
            maxDate: "+1y +1d",
            onSelect: OnSelectDate
        });

        $("#type").change(function () {
            if ($(this).val() == "round trip") {
                $("#end").datepicker("option", { minDate: 1 });
                $("#end").parent().show();
                $("#start").parent().show();
            }
            if ($(this).val() == "arrival") {
                $("#end").parent().hide();
                $("#start").parent().show();
            }
            if ($(this).val() == "departure") {
                $("#end").datepicker("option", { minDate: 0 });
                $("#end").parent().show();
                $("#start").parent().hide();
            }
        });

        $(".calendar-icon").click(function(){ $($(this).attr("data-target")).datepicker("show") });
        
        setDefaultDate();

        changeFocus("#destination_name");
        $("#destination_name").autocomplete({
            minLength: 3,
            //source: "/ajax/get_destinations.php",
            //source: $.map(destinations, function (item) { return { label: item.destination_name, destination_id: item.destination_id } }),
            source: function(request, response) { 
               $.ajax({
                    url: "/ajax/get_destinations.php",
                    dataType: "json",
                    success: function( data ) {
                        var re = $.ui.autocomplete.escapeRegex(request.term);
                        var matcher = new RegExp( "^" + re, "i" );
                        response($.grep(data, function(item){return matcher.test(item.value)}));
                    }
                });
            },
            select: function( event, ui ) { 
                    $("#destination_id").val(ui.item.destination_id);
                    inputText = ui.item.value;
            }
        });

        $("#search").click(function(){
            if ( $("#destination_id").val() != "" ) {
                $("#resboxForm").submit();
            } else {
                alert("Seleccione un hotel o lugar");
            }
        });

        if (loadPageVar("type")) { 
            $("#type").val(loadPageVar("type")).change(); 
            $("#book_type").val(loadPageVar("type"));
        }
        if (loadPageVar("destination_name")) {
            $("#destination_name").val(loadPageVar("destination_name"));
        } 
        if (loadPageVar("destination_id")) {
            $("#destination_id").val(loadPageVar("destination_id"));
            $("#book_destination_id").val(loadPageVar("destination_id"));
        }
        if (loadPageVar("start")) {
            $("#start").datepicker("setDate", loadPageVar("start"));
            $("#book_start").val(loadPageVar("start"));
        }
        if (loadPageVar("end")) {
            $("#end").datepicker("setDate", loadPageVar("end"));
            $("#book_end").val(loadPageVar("end"));
        }
        if (loadPageVar("pax")) {
            $("#pax").val(loadPageVar("pax"));
            $("#book_pax").val(loadPageVar("pax"));
        }
    }
    /* Termina Resbox */

    /* Inicia Formulario  de Reservacion */
    if ( $("#bookForm").length ) {
        $(".option").click(function(){
            $(".option").removeClass("active");
            $(this).addClass("active");
            $(this).find("input").prop("checked", true);
        });

        $("#bookForm").validate({
            rules: {
                email: { 
                    required: true,
                    email: true,
                    maxlength: 320
                },
                full_name: {
                    required: true,
                    maxlength: 256
                },
                phone: {
                    required: true,
                    maxlength: 256
                },
                payment: { 
                    required: true
                },
                arrival_airline: {
                    required: true,
                    maxlength: 256
                },
                arrival_flight: {
                    required: true,
                    maxlength: 256
                },
                departure_airline: {
                    required: true,
                    maxlength: 256
                },
                departure_flight: {
                    required: true,
                    maxlength: 256
                },
                terms: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Escribe una dirección de correo electrónico válida",
                    email: "Escribe una dirección de correo electrónico válida",
                    maxlength: "La dirección de correo electrónico es muy larga"
                },
                full_name: {
                    required: "Escribe tu nombre completo",
                    maxlength: "El nombre no puede contener más de 256 caracteres"
                },
                phone: {
                    required: "Escribe tu teléfono de contacto",
                    maxlength: "El teléfono no puede contener más de 256 caracteres"
                },
                payment: {
                    required: "Elige un método de pago"
                },
                arrival_airline: {
                    required: "Escribe la aerolínea de llegada",
                    maxlength: "El nombre de la aerolínea no puede contener más de 256 caracteres"
                },
                arrival_flight: {
                    required: "Escribe el número de vuelo de llegada",
                    maxlength: "El número de vuelo no puede contener más de 256 caracteres"
                },
                departure_airline: {
                    required: "Escribe la aerolínea de regreso",
                    maxlength: "El nombre de la aerolínea no puede contener más de 256 caracteres"
                },
                departure_flight: {
                    required: "Escribe el número de vuelo de regreso",
                    maxlength: "El número de vuelo no puede contener más de 256 caracteres"
                },
                terms: {
                    required: "Acepta los términos y condiciones"
                }
            },
            errorPlacement: function(error, element) {
                $("#formErrors").html(error);
            },
            errorLabelContainer: "#formErrors"
        });

        $("#bookButton").click(function(){
            var form = $("#bookForm");
            if (form.valid()){
            	$(".loading-icon").css("display","inline-block");
            	$("#bookButton").attr("disabled","disabled");            	
                $.ajax({
                    type: "GET",
                    url: form.attr( 'action' ),
                    data: form.serialize(),
                    success: function( response ) {
                        window.location.href = response.uri;
                    }
                });
            }
        });
    }
    /* Termina Formulario  de Reservacion */

    /* Formulario de contacto */
    if( $("#contactForm").length ) {
        $("#contactForm").validate({
            rules: {
                full_name: {
                    required: true,
                    maxlength: 256
                },            	
                email: { 
                    required: true,
                    email: true,
                    maxlength: 320
                },
                phone: {
                    required: true,
                    maxlength: 256
                },
                message: { 
                    required: true
                }
            },
            messages: {
				full_name: {
                    required: "Escribe tu nombre completo",
                    maxlength: "El nombre no puede contener más de 256 caracteres"
                },
                email: {
                    required: "Escribe una dirección de correo electrónico válida",
                    email: "Escribe una dirección de correo electrónico válida",
                    maxlength: "La dirección de correo electrónico es muy larga"
                },
                phone: {
                    required: "Escribe tu teléfono de contacto",
                    maxlength: "El teléfono no puede contener más de 256 caracteres"
                },
                message: {
                    required: "Escribe tu mensaje"
                }
            },
            errorPlacement: function(error, element) {
                $("#formErrors").html(error);
            },
            errorLabelContainer: "#formErrors"
        });
		$("#contactButton").click(function(){
            var form = $("#contactForm");
            if (form.valid()){
            	$(".loading-icon").css("display","inline-block");
            	$("#contactButton").attr("disabled","disabled");
                $.ajax({
                    type: "GET",
                    url: form.attr( 'action' ),
                    data: form.serialize(),
                    success: function( response ) {
                    	var parent = $(".loading-icon").parent();
                    	$(".loading-icon").remove();
                    	if (response == true) {
                    		parent.append("<span>Mensaje Enviado</span>");
                    	}
                    	else {
                    		parent.append("<span>Error. Intentelo más tarde</span>");
                    	}
                    }
                });
            }
        });
    }


    // Para activar los links del menu dependiendo de la ruta
    var link = $(".menu").find('[href="'+window.location.pathname.replace("/","")+'"]');
    $(".menu a").removeClass("active");
    link.addClass("active");

    $(".includesLink").fancybox({
        'titleShow': false,
        'transitionIn': 'elastic',
        'transitionOut': 'elastic',
    });

});