BitlyCB.myStatsCallback = function(data) {
			var results = data.results;
			
			var links = document.getElementsByTagName('a');
			for (var i=0; i < links.length; i++) {
				var a = links[i];
				if (a.href && a.href.match(/^http\:\/\/bit\.ly/)) {
					var hash = BitlyClient.extractBitlyHash(a.href);
					if (results.hash == hash || results.userHash == hash) {
						/*if (results.userClicks) {
							var uc = results.userClicks + " clicks";
						} else {
							var uc = "";
						}*/
						
						uc= "";
						
						
						if (results.clicks) {
							var c = results.clicks;
						} else {
							var c = "0";
						}
						c += " clicks";
						
						
						var sp = BitlyClient.createElement('span', {'text': " (" + uc + c + ") "});
						a.parentNode.insertBefore(sp, a.nextSibling);
					}
				}
				
			};
			
		}

		// wait until page is loaded to call API
		BitlyClient.addPageLoadEvent(function(){
			var links = document.getElementsByTagName('a');
			var fetched = {};
			var hashes = [];
			for (var i=0; i < links.length; i++) {
				var a = links[i];
				if (a.href && a.href.match(/^http\:\/\/bit\.ly/)) {
					if (!fetched[a.href]) {
						BitlyClient.stats(BitlyClient.extractBitlyHash(a.href), 'BitlyCB.myStatsCallback');
						fetched[a.href] = true;
					}
				}
			};
			
			
			
		});
