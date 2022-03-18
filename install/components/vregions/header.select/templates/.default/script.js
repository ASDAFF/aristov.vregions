function ChangeVRegion(sender){
	var cookie = sender.getAttribute("data-cookie");
	var href   = sender.getAttribute("data-domain");

	var av = new AristovVregions;
	av.setCookie(
		cookie,
		function(answer){
			if (answer.redirect){
				location.href = href + location.pathname + location.search + location.hash;
			}
			if (answer.reload){
				location.reload();
			}

			OpenVregionsPopUp("close");
		}
	);

	return false;
}

function OpenVregionsPopUp(action, popup_id, sepia_id){
	if (!popup_id){
		popup_id = "vregions-popup";
	}
	var vregions_popup  = document.getElementById(popup_id);
	var vregions_popups = document.getElementsByClassName("vr-popup");

	var sepia           = document.getElementById(sepia_id);
	var vregions_sepias = document.getElementsByClassName('vregions-sepia');

	for(var i = 0; i < vregions_popups.length; i++){
		vregions_popups[i].style.display = "none";
	}

	if (!action || action !== "close"){
		sepia.style.display          = "block";
		vregions_popup.style.display = "block";
		vrAddClass(document.getElementsByTagName('body')[0], 'modal-open');
	}

	if (action === "close"){
		for(var i = 0; i < vregions_popups.length; i++){
			vregions_popups[i].style.display = "none";
		}
		for(var i = 0; i < vregions_sepias.length; i++){
			vregions_sepias[i].style.display = "none";
		}
		vrRemoveClass(document.getElementsByTagName('body')[0], 'modal-open');
	}

	return false;
}

function vrAddClass(o, c){
	var re = new RegExp("(^|\\s)" + c + "(\\s|$)", "g")
	if (re.test(o.className)) return
	o.className = (o.className + " " + c).replace(/\s+/g, " ").replace(/(^ | $)/g, "")
}

function vrRemoveClass(o, c){
	var re      = new RegExp("(^|\\s)" + c + "(\\s|$)", "g")
	o.className = o.className.replace(re, "$1").replace(/\s+/g, " ").replace(/(^ | $)/g, "")
}

$(document).on("keyup", ".js-vregions-search-input", function(event){
	var input = $(this);
	var mask  = input.val();
	var wrap  = input.parents('.js-vregions-search-wrap');

	if (mask.length){
		var av = new AristovVregions;
		av.findRegionByNameMask(
			mask,
			function(answer){
				$('.vregions-suggestions-wrap').remove();
				input.removeClass('with-suggestions')

				if (answer.regions && answer.regions.length){
					input.addClass('with-suggestions')

					wrap.append('<div class="vregions-suggestions-wrap"></div>');

					answer.regions.forEach(function(el, i){
						if (el.NAME && el.HREF){
							wrap.find('.vregions-suggestions-wrap').append('<a href="' + el.HREF + '" data-domain="' + el.HREF + '" class="vregions-suggestion" data-cookie="' + el.CODE + '" onclick="ChangeVRegion(this); return false;">' + el.NAME + '</a>');
						}else{
							if (el.CITY_NAME){
								wrap.find('.vregions-suggestions-wrap').append('<a class="vregions-suggestion" data-loc_id="' + el.ID + '" onclick="ChangeVBitrixLocation(this); return false;">' + el.CITY_NAME + '</a>');
							}
						}
					});
				}
			}
		);
	}
});

$(document).on("blur", ".js-vregions-search-input", function(event){
	var input = $(this);
	var mask  = input.val();

	if (!mask){
		input.parents('.js-vregions-search-wrap').find('.vregions-suggestions-wrap').remove();
		input.removeClass('with-suggestions')
	}
});

function ChangeVBitrixLocation(sender){
	var id = sender.getAttribute("data-loc_id");

	var av = new AristovVregions;
	av.changeBitrixLocation(
		id,
		function(answer){
			if (answer.success){
				location.reload();
			}
		}
	);
}

$(document).on("click", ".vr-popup", function(e){
	if (e.target.className == 'vr-popup' || e.target.className == 'vr-popup vregions-popup-que'){
		OpenVregionsPopUp('close');
	}
});

// фильтрация по областям
window.vrSelectedOblast = '';
$(document).on("change", ".js-vregions-oblast__select", function(e){
	var oblast = $(this).val();

	vrFilterByOblast(oblast);
});
$(document).on("click", ".js-vr-popup__oblast-link", function(e){
	var oblast = $(this).attr('data-oblast');

	if (oblast == window.vrSelectedOblast){
		oblast = '';
	}

	window.vrSelectedOblast = oblast;

	vrFilterByOblast(oblast);

	return false;
});

function vrFilterByOblast(oblast){
	// показываем все регионы и буквы
	$('.js-vr-popup__region-link').show();
	$('.js-vr-popup__regions-letter-heading').show();
	$('.js-vr-popup__regions-letter-block').removeClass('vr-no-padding');

	if (oblast){
		// фильтруем регионы
		$('.js-vr-popup__region-link').each(function(i, el){
			var regionOblast = $(el).attr('data-oblast');
			if (regionOblast != oblast){
				$(el).hide();
			}
		});

		// фильтруем буквы
		$('.js-vr-popup__regions-letter-heading').each(function(i, el){
			var letter = $(el).text().trim();
			// если нет регионов с такой буквой
			if (!$('.js-vr-popup__region-link[data-first_letter=' + letter + ']:visible').length){
				$(el).hide();
				$(el).parent('.js-vr-popup__regions-letter-block').addClass('vr-no-padding');
			}
		});
	}
}

// кнопка моего города нет в списке
$(document).on("click", ".js-another-region-btn", function(e){
	var av = new AristovVregions;
	av.checkLocation(
		'ip',
		function(answer1){
			if (answer1.lat && answer1.lon){
				av.getClosestRegion(
					answer1.lon,
					answer1.lat,
					function(answer2){
						av.saveRegion(
							answer2.region_code,
							function(answer3){
								location.href = answer2.url_without_path + location.pathname + location.search + location.hash;
							})
					});
			}
		}
	);

	return false;
});