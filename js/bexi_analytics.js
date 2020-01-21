class baw {

	  constructor(PageId) {
	  		this.PageId = PageId;
	  		var Data = {
	  			"event" : "visit",
	  			"page_id" : this.PageId,
	  			"referer" : document.referrer,
	  			"location" : window.location.href,
	  			"path" : window.location.pathnam
	  		};
	  		this.sendRequest(Data);
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
