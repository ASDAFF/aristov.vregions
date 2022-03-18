$(document).on("keyup", ".js-vregions-delivery-location-input", function(event){
	var mask = $(this).val();

	if (mask.length){
		var ajaxPath = $('.js-vregions-delivery-ajax-path').val();

		var ajaxArr = {
			sessid : BX.bitrix_sessid(),
			site_id: BX.message('SITE_ID'),
			action : "get-location-by-mask",
			mask   : mask
		};

		$.ajax({
			url     : ajaxPath,
			data    : ajaxArr,
			method  : "POST",
			dataType: "json",
			success : function(answer){
				$('.js-vregions-delivery-search-results-wrap').html('');
				answer.locations.forEach(function(location){
					$('.js-vregions-delivery-search-results-wrap').append('<a href="#" class="vregions-delivery-calc-form__search-result js-vregions-delivery-search-result" data-code="' + location.CODE + '">' + location.CITY_NAME + '</a>');
				});
			},
			error   : function(){

			}
		});
	}
});

$(document).on("click", ".js-vregions-delivery-search-result", function(){
	var a                  = $(this);
	var locationCode       = a.attr('data-code');
	var productID          = $('.js-vregions-delivery-calc').attr('data-product_id');
	var title              = $('.js-vregions-delivery-calc').attr('data-title');
	var exclude_deliveries = $('.js-vregions-delivery-calc').attr('data-exclude_deliveries');

	if (locationCode){
		var ajaxPath = $('.js-vregions-delivery-ajax-path').val();

		var ajaxArr = {
			sessid            : BX.bitrix_sessid(),
			site_id           : BX.message('SITE_ID'),
			action            : "get-delivery-component-for-location",
			locationCode      : locationCode,
			productID         : productID,
			title             : title,
			exclude_deliveries: exclude_deliveries,
		};

		$.ajax({
			url     : ajaxPath,
			data    : ajaxArr,
			method  : "POST",
			dataType: "html",
			success : function(answer){
				$('.js-vregions-delivery-calc').replaceWith(answer);
			},
			error   : function(){

			}
		});
	}

	return false;
});