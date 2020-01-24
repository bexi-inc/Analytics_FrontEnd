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