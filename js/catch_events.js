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
            changeScroll = true
            pctAux = pctScrolled
            b = new baw("test-1234","scrollPercentage",{scrollPercentage : pctScrolled});
            //console.log(pctScrolled)  
        }
        if(changeScroll){
            //console.log("save scroll percentage")
            changeScroll = false
        }   
    }, 50)
}, false)