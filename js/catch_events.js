class Scroll{
    constructor(){
        this.winheight = 0
        this.docheight = 0
        this.trackLength = 0
        this.pctAux = 0
    }

    getmeasurements(){
        this.winheight = $(window).height()
        this.docheight = $(document).height()
        this.trackLength = this.docheight - this.winheight
    }

    amountscrolled(){ //scroll percentage
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

    startTimer(){
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
        this.startTimer();
    }
}

class visibilityChange {

    constructor(){
        this.prefix = visibilityChange.getBrowserPrefix()
        this.hidden = visibilityChange.hiddenProperty(this.prefix)
        this.visibilityState = visibilityChange.visibilityState(this.prefix)
        this.visibilityEvent = visibilityChange.visibilityEvent(this.prefix)
    }

    get _visibilityEvent(){
        return this.visibilityEvent
    }

    get _hidden(){
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



var visibility_change = new visibilityChange()
var scroll_change = new Scroll()
var timer = null
var countTimePage = 0
var timerscroll

function startTimer() {
    timer = new Timer(function () {
        countTimePage++
        //console.log(countTimePage)
        startTimer()
    }, 1000);
    timer.startTimer();
}

function sendPageTime() {
    timerPageView = new Timer(function () {
        //save page view time every 20 seconds
        b = new baw("test-1234","timePage",{timePage : countTimePage});   
        sendPageTime()
    }, 20000);
    timerPageView.startTimer();
}

document.addEventListener(visibility_change._visibilityEvent, function (event) {
    if (document[visibility_change.hidden]) {        
        timer.pause()
        timerPageView.pause()
    } else {
        timer.resume()
        timerPageView.resume()
    }
})

$(window).on('load', function() {
    startTimer()
    sendPageTime()
    scroll_change.getmeasurements()
    //console.log(scroll_change.amountscrolled())
});

window.addEventListener("resize", function(){
    scroll_change.getmeasurements()
}, false)

var pctAux = 0, changeScroll = false
window.addEventListener("scroll", function(){
    clearTimeout(timerscroll)
    timerscroll = setTimeout(function(){
        var pctScrolled = scroll_change.amountscrolled()
         if(pctScrolled > pctAux){
            pctAux = pctScrolled
            b = new baw("test-1234","scrollPercentage",{scrollPercentage : pctScrolled});
            console.log(pctScrolled)  
        } 
    }, 50)
}, false)