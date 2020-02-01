class baw {

	constructor(SiteId, EventType) {
        this.timeRun = true;
        this.addEvents();
        this.SiteId = SiteId;
		this.countTimePage = 0;
		this.Data = {
			"event" : "visit",
			"site_id" : this.SiteId,
			"referer" : document.referrer,
			"location" : window.location.href,
			"path" : window.location.pathname
		};
		
		this.collector = {
            "event":"collector",
            "site_id" : this.SiteId,
            "referer" : document.referrer,
			"location" : window.location.href,
			"path" : window.location.pathname,
            "Data": {
                "time_page":0,
                "scroll_percentage":0,
                "click":0
            }
        }

		this.sendRequest(this.Data);
	}

	  PushEvent(Event)
	  {
	  	this.PushEventValue(Event, 1);
	  }


	  PushEventValue(Event, value)
	  {
        this.sendRequest(this.Data);
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

      addEvents() {

        this.timerscroll = null
        this.visibility_change = new visibilityChange()
        
        document.addEventListener(this.visibility_change._visibilityEvent(), () => {
            if (document[this.visibility_change._hidden()]) 
            this.stopT();
        else
            this.resumeT();
        })

        $(window).on('load', () => {
            this.startTimer();
            this.sendPageTime();
            this.sendCollect();
            Scroll.getmeasurements();
        });

        $(window).on('focus pageshow',() => {
            this.resumeT();
        });
    
        window.addEventListener('blur', () =>{
            this.stopT();
          });

        window.addEventListener("resize", function () {
            Scroll.getmeasurements()
        }, false)

        var pctAux = 0
        window.addEventListener("scroll", () => {
            clearTimeout(this.timerscroll)
            this.timerscroll = setTimeout(() => {
                var pctScrolled = Scroll.amountscrolled()
                if (pctScrolled > pctAux) {
                    pctAux = pctScrolled
                    this.collector.Data.scroll_percentage = pctScrolled;
                    console.log(pctScrolled)
                }
            }, 50)
        }, false)

        $(".bexi_button, .bexi_link").on('click', ()=>{
            this.collector.Data.click++;
        });
    }


    startTimer() {
        this.timer = new Timer(() => {
            this.countTimePage++;
            this.startTimer();
        }, 1000);
        this.timer.start();
    }

    sendPageTime() {
        this.timerPageView = new Timer(() => {
            this.collector.Data.time_page = this.countTimePage;
            this.sendPageTime();
        }, 10000);
        this.timerPageView.start();
    }

    sendCollect() {
        this.timerCollect = new Timer(() => {
            this.sendRequest(this.collector);
            this.sendCollect();
        }, 22000);
        this.timerCollect.start();
    }

    stopT() {
        if (this.timeRun) {
            this.timeRun = false;
            this.timer.pause();
            this.timerPageView.pause();
            this.timerCollect.pause();
        }
    }
    
    resumeT() {
        if (!this.timeRun) {
            this.timeRun = true;
            this.timer.resume();
            this.timerPageView.resume();
            this.timerCollect.resume();
        }
    };
}


class Scroll{
    constructor(){
        this.winheight = 0
        this.docheight = 0
        this.trackLength = 0
        this.pctAux = 0
    }

    static getmeasurements(){
        this.winheight = $(window).height()
        this.docheight = $(document).height()
        this.trackLength = this.docheight - this.winheight
    }

    static amountscrolled(){ //scroll percentage
        var pctScrolled = 0
        var scrollTop = $(window).scrollTop()
        pctScrolled = Math.floor(scrollTop/this.trackLength * 100) // gets percentage scrolled (ie: 80 NaN if tracklength == 0)
        return pctScrolled
    }

}


class Timer{
    constructor(callback, delay){
        this.callback = callback;
        this.timerId, this.start, this.remaining = delay;
    }

    start(){
        this.resume();
    }

    pause() {
        window.clearTimeout(this.timerId);
        this.remaining -= new Date() - this.start;
    };

    resume() {
        this.start = new Date();
        window.clearTimeout(this.timerId);
        this.timerId = window.setTimeout(this.callback, this.remaining);
    };

    reset() {
        clearTimeout(this);
        this.start();
    }
}

class visibilityChange {

    constructor(){
        this.prefix = visibilityChange.getBrowserPrefix()
        this.hidden = visibilityChange.hiddenProperty(this.prefix)
        this.visibilityState = visibilityChange.visibilityState(this.prefix)
        this.visibilityEvent = visibilityChange.visibilityEvent(this.prefix)
    }

    _visibilityEvent(){
        return this.visibilityEvent
    }

    _hidden(){
        return this.hidden
    }

    static getBrowserPrefix() {     
        if ('hidden' in document) {
            return null
        }
        
        var browserPrefixes = ['moz', 'ms', 'o', 'webkit']

        for (var i = 0; i < browserPrefixes.length; i++) {
            var prefix = browserPrefixes[i] + 'Hidden'
            if (prefix in document) {
                return browserPrefixes[i]
            }
        }

        return null
    }

    
    static hiddenProperty(prefix) {
        if (prefix) {
            return prefix + 'Hidden'
        } else {
            return 'hidden'
        }
    }

    
    static visibilityState(prefix) {
        if (prefix) {
            return prefix + 'VisibilityState'
        } else {
            return 'visibilityState'
        }
    }

    
    static visibilityEvent(prefix) {
        if (prefix) {
            return prefix + 'visibilitychange'
        } else {
            return 'visibilitychange'
        }
    }
}