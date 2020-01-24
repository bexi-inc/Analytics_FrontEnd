class baw {

	constructor(PageId, EventType, ExtraFields) {
		this.PageId = PageId;
		var ExtraFields = ExtraFields;
		this.Data = {
			"event" : EventType,
			"page_id" : this.PageId,
			"referer" : document.referrer,
			"location" : window.location.href,
			"path" : window.location.pathnam
		};
		this.ExtraData(ExtraFields);
				
		this.sendRequest(this.Data);
	}

	ExtraData(ExtraFields){
		switch (this.Event) {
			case 'timePage':
				this.Data["timePage"] = ExtraFields["timePage"];
				break;
			case 'scrollPercentage':
				this.Data["scrollPercentage"] = ExtraFields["scrollPercentage"];
				break;
			default:
				break;
		}
	}

	  PushEvent(Event)
	  {
	  		this.PushEventValue(Event,1);
	  }


	  PushEventValue(Event, Value)
	  {
	  	var Data = {
	  			"event" : Event,
	  			"page_id" : this.PageId,
	  			"value" : value
	  		};
	  		this.sendRequest(Data);
	  }


	  sendRequest(DataRequest)
	  {
	  	$(function() {

				$.ajax({
					data: DataRequest,
					type: "POST",
				    // Formato de datos que se espera en la respuesta
				    dataType: "json",
					url: 'api/analytics.php',
					success: function(respuesta) {
						console.log(respuesta);
					},
					error: function() {
				        console.log("No se ha podido obtener la información");
				    }
				});

			});
	  }


}

b = new baw("test-1234");
