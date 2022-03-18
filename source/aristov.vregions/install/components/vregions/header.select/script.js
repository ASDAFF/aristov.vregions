window.AristovVregions = (function(){
	this.ajaxPath = '/bitrix/components/vregions/header.select/ajax.php';
});

AristovVregions.prototype.setCookie = function(cookie, callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "set-cookie",
		cookie : cookie
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		method  : "POST",
		dataType: "json",
		success : function(answer){
			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		error   : function(){

		}
	});

	return false;
};

AristovVregions.prototype.isNeedLocationCheck = function(callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "check-auto-geo-ness"
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		method  : "POST",
		dataType: "json",
		success : function(answer){
			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		error   : function(data){
			console.log(data);
		}
	});
};

AristovVregions.prototype.checkLocation = function(method, callback){
	if (method == "google"){
		this.getCoordsByHtml5(
			function(answer){
				if (typeof callback !== 'undefined'){
					callback(answer);
				}
			}
		);
	}
	if (method == "sxgeo" || method == "ip"){
		this.getCoordsByPHP(
			function(answer){
				if (typeof callback !== 'undefined'){
					callback(answer);
				}
			}
		);
	}
};

AristovVregions.prototype.getCoordsByPHP = function(callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "get-php-coords"
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		method  : "POST",
		dataType: "json",
		success : function(answer){
			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		error   : function(){

		}
	});
};

AristovVregions.prototype.getClosestRegion = function(longitude, latitude, callback){
	var ajaxArr = {
		sessid   : BX.bitrix_sessid(),
		site_id  : BX.message('SITE_ID'),
		action   : "get-closest-region",
		longitude: longitude,
		latitude : latitude,
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		method  : "POST",
		dataType: "json",
		success : function(answer){
			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		error   : function(){

		}
	});
};

AristovVregions.prototype.getCoordsByHtml5 = function(callback){
	if (navigator.geolocation){
		navigator.geolocation.getCurrentPosition(
			function(position){
				if (typeof callback !== 'undefined'){
					callback({
						lat: position.coords.latitude,
						lon: position.coords.longitude
					});
				}
			},
			function(positionError){
				console.log(positionError.message);
			}
		);
	}else{
		console.log("Html5 geolocation fail")
	}
};

AristovVregions.prototype.findRegionByNameMask = function(mask, callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "find-region-by-name-mask",
		mask   : mask,
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		method  : "POST",
		dataType: "json",
		success : function(answer){
			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		error   : function(){

		}
	});
};

AristovVregions.prototype.changeBitrixLocation = function(id, callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "change-bitrix-location",
		id     : id
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		type    : "post",
		dataType: "json",
		success : function(answer){
			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		error   : function(){

		}
	});
};

AristovVregions.prototype.getSavedRegion = function(callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "get-saved-region",
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		type    : "post",
		dataType: "json",
		success : function(answer){
			callback(answer.region);
		},
		error   : function(){

		}
	});
}

AristovVregions.prototype.saveRegion = function(region, callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "save-region",
		region : region
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		type    : "post",
		dataType: "json",
		success : function(answer){
			callback(answer);
		},
		error   : function(){

		}
	});
}

AristovVregions.prototype.redirectByRegionCode = function(code){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "prepare-for-redirect-by-region-code",
		code   : code
	};

	$.ajax({
		url     : this.ajaxPath,
		data    : ajaxArr,
		type    : "post",
		dataType: "json",
		success : function(answer){
			if (answer.redirect && answer.domen){
				location.href = answer.domen + location.pathname + location.search + location.hash;
			}
		},
		error   : function(){

		}
	});
};

AristovVregions.prototype.redirectToClosestRegion = function(lat, lon, forceRedirect){
	this.getClosestRegion(
		lon,
		lat,
		function(answer){
			if (answer.redirect || forceRedirect){
				location.href = answer.url_without_path + location.pathname + location.search + location.hash;
			}else{
				if (!answer["ex-cookie"]){
					if (!(answer["dont_show_ask_window_if_already_on_needed_region"] && answer['already_on_region'])){
						if (typeof vrAskRegion !== 'undefined'){
							// todo не у всех эта функция есть
							vrAskRegion(answer.region, answer.region_code, answer.url_without_path);
						}
					}
				}
			}
		}
	);
};

document.addEventListener("DOMContentLoaded", function(){
	var av = new AristovVregions;

	setTimeout(
		function(){
			av.getSavedRegion(
				function(savedRegion){
					if (savedRegion.length){
						av.redirectByRegionCode(savedRegion);
					}else{
						av.isNeedLocationCheck(
							function(answer){
								if (answer.success){
									av.checkLocation(
										answer.method,
										function(answer2){
											if (answer2.lat && answer2.lon){
												av.redirectToClosestRegion(answer2.lat, answer2.lon);
											}
										}
									);
								}
							}
						);
					}
				}
			);
		},
		1000  // задержка нужна, чтобы успел подгрузиться BX.bitrix_sessid()
	)
});